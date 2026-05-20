# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Purpose

This is a **distribution bundle**, not a runtime project. It packages two artifacts that ship together:

- **`firm-legal-schema-suite/`** — a WordPress plugin (PHP) that emits Schema.org JSON-LD for U.S. law firm sites. This is the deployable artifact.
- **`law-firm-schema-plugin-builder/`** — a Claude Skill (Markdown only) that teaches Claude how to extend, audit, and deploy the plugin.

The three top-level `.md` files (`PROJECT-INSTRUCTIONS.md`, `PROJECT-CONVENTIONS.md`, `SITE-REGISTRY.md`) are uploaded into a Claude Project as custom instructions and knowledge. `README.md` documents the end-user setup flow.

Both nested directories are doubled (`firm-legal-schema-suite/firm-legal-schema-suite/...`) because each is intended to be zipped as a self-contained folder for upload.

## Authoritative References

Read these before writing code — they encode rules that aren't derivable from the source:

- [PROJECT-CONVENTIONS.md](PROJECT-CONVENTIONS.md) — entity types, `@id` patterns, language rules, naming, JSON flags, anti-patterns
- [PROJECT-INSTRUCTIONS.md](PROJECT-INSTRUCTIONS.md) — role, hard constraints, deployment + troubleshooting workflows
- [law-firm-schema-plugin-builder/law-firm-schema-plugin-builder/SKILL.md](law-firm-schema-plugin-builder/law-firm-schema-plugin-builder/SKILL.md) — the same conventions in skill form, plus the page-type → schema-type mapping table and required-field lists per schema type
- [law-firm-schema-plugin-builder/law-firm-schema-plugin-builder/references/](law-firm-schema-plugin-builder/law-firm-schema-plugin-builder/references/) — deeper reference docs:
  - `architecture.md` — class templates (closely mirrors the actual code)
  - `schema-types.md` — JSON-LD examples for every supported page type
  - `deployment.md` — install methods (plugin upload / child theme / Code Snippets), pre-deploy checklist, rollback
  - `validation.md` — validator quirks and a catalog of common errors with fixes
  - `conventions.md` — verbatim duplicate of top-level `PROJECT-CONVENTIONS.md` (read one, not both)

When `SKILL.md` / `PROJECT-CONVENTIONS.md` and code disagree, the docs win — the code is being built toward them.

Reference deployment environment (from `SITE-REGISTRY.md`): **Avada Child Theme + WP Rocket** is the canonical setup. WP Rocket requires a manual purge after every deploy.

## Plugin Architecture

The plugin uses a **router + handlers + per-site config** pattern. Adding a new schema type means adding one handler file, one router branch, and one config toggle — no edits to existing handlers.

Bootstrap flow ([firm-legal-schema-suite.php](firm-legal-schema-suite/firm-legal-schema-suite/firm-legal-schema-suite.php)):
1. Loads `config/site-config.php` (the **only** file edited per deployment)
2. Always loads `includes/class-schema-base.php` (abstract base with shared helpers) and `includes/class-breadcrumbs.php`
3. Conditionally loads handlers from `includes/handlers/` based on `enabled_schemas` toggles
4. On `wp_head` (priority 20), instantiates `Firm_Legal_Schema_Router` and calls `dispatch()`

`Firm_Legal_Schema_Router::select_handler()` ([class-schema-router.php](firm-legal-schema-suite/firm-legal-schema-suite/includes/class-schema-router.php)) inspects the current WP request with conditional tags (`is_singular('post')`, `is_singular($config['attorney_post_type'])`, `is_page($config['contact_page_slug'])`, …) and returns the matching handler instance, or `null` (no output).

`Firm_Legal_Breadcrumbs` ([class-breadcrumbs.php](firm-legal-schema-suite/firm-legal-schema-suite/includes/class-breadcrumbs.php)) is used by **composition**, not inheritance — handlers instantiate it with `($config, $lang_code, $home_label)` and call `build_for_post($post_id, $permalink)` / `build_for_cpt($post_id, $permalink, $post_type, $archive_label)` / `build_for_page($page_id, $permalink)` (the page variant walks the parent chain). All variants emit `@id = {permalink}#breadcrumb`.

`Firm_Legal_Schema_Base` ([class-schema-base.php](firm-legal-schema-suite/firm-legal-schema-suite/includes/class-schema-base.php)) provides:
- `detect_language()` — `force_language` → Polylang → WPML → URL marker → `en-US` default; sets `$lang_code` and `$home_label` ("Home"/"Inicio")
- `resolve_author()` — ACF override (`acf_author_name_field`/`acf_author_url_field`) → native WP author fallback; builds the `#attorney-firstname-lastname` `@id` that **must** match the attorney profile page
- `org_ref()` / `website_ref()` / `logo_ref()` — returns `['@id' => …]` references to the sitewide entities (the plugin only references; it never defines them)
- `clean_entity()` — strips null/empty fields
- `output_json_ld()` — wraps in `@graph`, encodes with `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT`

Only `class-blog-posting.php` is currently implemented; the other six handler types (Attorney, Practice Area, Contact, About, FAQ, Location) are stubs awaiting implementation — both the `require_once` (commented in the main file) and the router branch already exist.

## Workflow for Adding a Schema Type

1. Create `includes/handlers/class-{type}.php` extending `Firm_Legal_Schema_Base`, implementing `render()`
2. Uncomment the matching `require_once` block in the main plugin file
3. Add (or uncomment) the detection branch in `Firm_Legal_Schema_Router::select_handler()` — **order matters: more specific checks before more general ones**
4. Flip the `enabled_schemas` toggle in `config/site-config.php` to `true`
5. Bump `Version:` in the plugin header **and** the `FIRM_LEGAL_SCHEMA_VERSION` constant
6. Update `readme.txt` and `SITE-REGISTRY.md`

## Hard Constraints (Do Not Violate Without Explicit User Confirmation)

Summarized from `PROJECT-INSTRUCTIONS.md` — apply to every code change:

- **Publisher type is `LegalService`** — never `Organization` or `LocalBusiness`
- **Sitewide entities (`#organization`, `#website`, `#logo`) are referenced by `@id`, never redefined** in handlers. If they're missing on the site, fix the sitewide schema first — don't paper over it inside this plugin.
- **Author Person on BlogPosting is lightweight** — only `@type`, `@id`, `name`, `url`, `worksFor`. Full bios live on the attorney profile page.
- **Attorney `@id` = `{home}/#attorney-{slug}`** where slug is `sanitize_title( remove_accents( $name ) )` — this must match the attorney profile page exactly.
- **Language codes are full locales** (`en-US`, `es-US`), never bare `en`/`es`. Never mix languages within a single entity.
- **Never fabricate trust signals** — no invented `AggregateRating`, `Review`, awards, credentials, or `sameAs` links. Refuse and explain.
- **Always use `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT`** with `wp_json_encode()`.

## Code Style

- WordPress coding standards: 4-space indent, Yoda conditionals, spaces inside parens (`if ( $var ) {`), no closing `?>` tag
- Class prefix `Firm_Legal_*`, function prefix `firm_legal_*`, hook prefix `firm_legal_*`
- PHP 7.2+ compatible (matches Avada hosting baseline)
- Comments in English even on Spanish-output sites; inline comments only for non-obvious logic
- Each PHP file begins with `if ( ! defined( 'ABSPATH' ) ) { exit; }`

## Build / Test / Validate

There is no build system, no automated tests, and no linter wired into this repo. "Validation" means deploying to a real WordPress site and running the JSON-LD output through both validators on every change:

1. https://validator.schema.org/ (Schema Markup Validator) — spec compliance, run first
2. https://search.google.com/test/rich-results (Google Rich Results Test) — Google eligibility, run second

Both must be clean, on at least one English page and one Spanish page if the site is multilingual. After deploying, **clear ALL caches** (WP Rocket, Cloudflare, browser) — this is the #1 cause of "schema isn't showing" reports.

## Per-Site Deployment Touchpoints

The only file edited per site is [site-config.php](firm-legal-schema-suite/firm-legal-schema-suite/config/site-config.php). It controls:

- `force_language` — set for single-language sites (Polylang/WPML/URL detection fails when nothing's there)
- `spanish_url_marker` — for multilingual sites without Polylang/WPML
- `acf_author_name_field` / `acf_author_url_field` — ACF field *names* (not labels), found in WP admin → Custom Fields
- `*_post_type` and `*_page_slug` — site-specific CPT slugs and page slugs
- `pages.about_page.primary_attorney` — `name`, `job_title`, `image_url`, `image_caption`, `same_as[]` for the attorney mentioned on the About page. Drives the `Person` + `mentions` + (optionally) `#primaryimage` ImageObject. Leaving `name` blank suppresses the Person/mention silently.
- `enabled_schemas` — feature flags per schema type
- `default_image_url` — fallback featured image (empty = omit the field)

Track deployments in [SITE-REGISTRY.md](SITE-REGISTRY.md).
