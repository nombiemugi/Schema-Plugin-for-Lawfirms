---
name: law-firm-schema-plugin-builder
description: Build, modify, audit, and deploy WordPress plugins that output Schema.org structured data (JSON-LD) for U.S. law firm websites. Use this skill whenever the user asks to create or update a schema plugin, generate JSON-LD for legal pages (BlogPosting, Attorney, LegalService, ContactPage, AboutPage, FAQPage, Location), refactor existing schema code into a plugin, debug rich results validation errors on a law firm site, set up sitewide #organization/#website schema, configure per-site schema settings (language, ACF fields, page detection), or audit existing law firm schema markup. Trigger this even for partial requests like "add Attorney schema to my plugin," "fix my BlogPosting validation error," or "make this work on a Spanish-only site." The user works with WordPress law firm sites using Avada and child themes; output should be production-ready PHP following the modular architecture and conventions documented in this skill.
---

# Law Firm Schema Plugin Builder

A skill for building production-grade WordPress plugins that output Schema.org structured data on U.S. law firm websites. This skill encodes hard-won conventions about entity relationships, language handling, deployment, and validation specific to the legal industry.

## When to Use This Skill

Trigger this skill whenever the user asks to:

- Build a new schema plugin for a law firm site
- Add a new schema type (Attorney, LegalService, FAQPage, etc.) to an existing plugin
- Audit or fix validation errors on an existing law firm schema
- Set up sitewide `#organization` / `#website` / `#logo` schema
- Configure a plugin for a new site (language, ACF fields, page detection)
- Refactor inline `functions.php` schema code into a proper plugin
- Generate JSON-LD for specific law firm page types
- Debug why schema isn't appearing on a site (caching, theme, prerequisites)

Apply this skill even when the user doesn't explicitly mention "law firm" — if they reference attorneys, practice areas, case results, or legal services, it's almost certainly in scope.

## Core Conventions (Non-Negotiable)

These conventions are mandatory across all schemas generated for law firm sites. Deviation requires explicit user confirmation.

### Entity Architecture

1. **Publisher is always `LegalService`** — never `Organization` or `LocalBusiness`. The firm is a legal service provider, and `LegalService` is the most specific Schema.org type.

2. **Sitewide entities are defined once, referenced everywhere.** The plugin should NEVER redefine these on individual pages — only reference them by `@id`:
   - `#organization` — the firm as `LegalService`
   - `#website` — the `WebSite` entity
   - `#logo` — the firm's `ImageObject` logo

3. **Canonical `@id` patterns:**
   - Organization: `https://www.example.com/#organization`
   - Website: `https://www.example.com/#website`
   - Logo: `https://www.example.com/#logo`
   - Attorney: `https://www.example.com/#attorney-firstname-lastname` — **this is the default, not the only option.** The Person `@id` is configurable via the `person_id` block (`base` / `fragment` / `append_slug`) and built by `Firm_Legal_Schema_Base::build_person_id()`. Non-law-firm and single-owner sites typically use `{profile-url}/#person` instead. Always match whatever the person's profile page already declares.
   - Page-specific entities: `{permalink}#{type}` (e.g., `{permalink}#blogposting`, `{permalink}#breadcrumb`)

4. **Author Person entities are lightweight on blog posts.** They include only `name`, `url`, and `worksFor` — full bio, credentials, and `sameAs` live on the attorney profile page, NOT on every blog post. The `@id` must match the profile page for entity continuity.

5. **No fabricated trust signals.** Never invent `AggregateRating`, `Review`, awards, or credentials. If the user requests these without supplying real data, refuse and explain why.

### Language Handling

1. **Per-post language detection in this priority order:**
   - Polylang (`pll_current_language()`)
   - WPML (`ICL_LANGUAGE_CODE` constant)
   - URL pattern fallback (configurable, default `/es/`)
   - Forced language via per-site config (`$force_language`)
   - Default: `en-US`

2. **Language codes are always `en-US` or `es-US`** — never just `en` or `es`. Schema.org and Google prefer the full locale.

3. **Breadcrumb root label translates per language:**
   - English: `"Home"`
   - Spanish: `"Inicio"`

4. **Never mix languages within a single entity.** A post's `headline`, `description`, and breadcrumb labels must all match `inLanguage`.

### Validation Workflow

After ANY schema deployment, the user must validate using BOTH:
1. Google Rich Results Test — https://search.google.com/test/rich-results
2. Schema Markup Validator — https://validator.schema.org/

Test at minimum one English page and one Spanish page (if applicable). Watch for "undefined @id reference" warnings — those mean the sitewide schema isn't running.

## Plugin Architecture (Production Standard)

Build all plugins using this modular structure:

```
firm-legal-schema-suite/
├── firm-legal-schema-suite.php       Plugin header + bootstrap
├── config/
│   └── site-config.php               Per-site settings (the only file edited per site)
├── includes/
│   ├── class-schema-router.php       Detects page type, dispatches to handler
│   ├── class-schema-base.php         Shared helpers (language, author, JSON output)
│   ├── class-breadcrumbs.php         Shared breadcrumb builder
│   └── handlers/
│       ├── class-blog-posting.php
│       ├── class-attorney.php        (add when needed)
│       ├── class-practice-area.php   (add when needed)
│       └── ...                       (one file per schema type)
└── readme.txt                        WordPress plugin readme
```

**Why this structure:**
- Adding a new schema type = new file, no existing code changes
- Per-site config lives in one place (`config/site-config.php`)
- Shared logic (language detection, author resolution, breadcrumbs) lives in the base class
- Router uses WordPress conditional tags to dispatch (`is_singular()`, `is_page()`, custom post type checks)

See `references/architecture.md` for the full class structure and code patterns.

## Per-Site Configuration

Every plugin should have a `config/site-config.php` file with these settings:

```php
return array(
    // Language handling
    'force_language'       => null,           // 'en-US' | 'es-US' | null (auto-detect)
    'spanish_url_marker'   => '/es/',         // URL pattern for fallback detection

    // ACF field names (only if site uses ACF author overrides)
    'acf_author_name_field' => 'autor_nombre',
    'acf_author_url_field'  => 'autor_url',

    // Page type detection (customize per site's post types and page slugs)
    'attorney_post_type'    => 'attorney',    // or 'team', 'lawyer', etc.
    'practice_area_post_type' => 'practice_area',
    'contact_page_slug'     => 'contact',
    'about_page_slug'       => 'about',
    'faq_page_slug'         => 'faq',

    // Schema toggles (enable/disable per site)
    'enabled_schemas' => array(
        'blog_posting'  => true,
        'attorney'      => false,             // enable when handler is added
        'practice_area' => false,
        'contact_page'  => false,
        'about_page'    => false,
        'faq_page'      => false,
    ),
);
```

## Page Type → Schema Type Mapping

When a user asks for schema on a specific page type, use this mapping:

| User says... | WordPress detection | Schema output |
|---|---|---|
| Blog post / article | `is_singular('post')` | `BlogPosting` + `Person` (author) + `BreadcrumbList` |
| Attorney bio / lawyer profile | `is_singular('attorney')` | `Attorney` (Person subtype) + `BreadcrumbList` |
| Practice area / service | `is_singular('practice_area')` | `LegalService` (with `serviceType`) + `BreadcrumbList` |
| Case result / case study | `is_singular('case_result')` | `Article` (with custom properties) + `BreadcrumbList` |
| Contact page | `is_page('contact')` | `ContactPage` + `BreadcrumbList` |
| About / firm page | `is_page('about')` | `AboutPage` + `BreadcrumbList` |
| FAQ page | `is_page('faq')` or template check | `FAQPage` (with Question/Answer pairs) + `BreadcrumbList` |
| Location / office page | `is_page_template('location.php')` | `LegalService` + `Place` + `BreadcrumbList` |
| Homepage | `is_front_page()` | Sitewide entities only (no extra) |

For schema-specific field requirements and code templates, see `references/schema-types.md`.

## Required Fields by Schema Type

### BlogPosting (required + recommended)
- `@type`, `@id`, `headline`, `image`, `datePublished`, `dateModified`, `author` (referenced), `publisher` (referenced), `mainEntityOfPage`, `inLanguage`, `isPartOf`, `description`
- Optional: `keywords` (from tags), `articleSection` (from category), `wordCount`

### Attorney (Person subtype)
- `@type: "Attorney"` (or `"Person"` with `additionalType: "Attorney"`), `@id`, `name`, `url`, `image`, `jobTitle`, `worksFor` (referenced), `telephone`, `email`, `description`, `knowsAbout` (areas of practice), `alumniOf` (law school)
- Optional: `award`, `memberOf` (bar associations), `hasCredential` (admissions, certifications), `sameAs` (LinkedIn, Avvo, etc.)

### LegalService (Practice Area)
- `@type: "LegalService"`, `@id`, `name`, `description`, `provider` (referenced to firm `#organization`), `areaServed`, `serviceType`, `url`
- Optional: `hasOfferCatalog`, `audience`

### ContactPage
- `@type: "ContactPage"`, `@id`, `url`, `name`, `description`, `mainEntity` (referenced to `#organization`)

### AboutPage
- `@type: "AboutPage"`, `@id`, `url`, `name`, `description`, `mainEntity` (referenced to `#organization`)

### FAQPage
- `@type: "FAQPage"`, `@id`, `url`, `mainEntity` (array of `Question` with `acceptedAnswer`)
- Each Question needs: `@type: "Question"`, `name` (the question), `acceptedAnswer` (with `@type: "Answer"` and `text`)

### Location (multi-office firms)
- `@type: "LegalService"` (since it inherits LocalBusiness properties), `@id`, `name`, `address` (with full PostalAddress), `geo` (with GeoCoordinates), `telephone`, `openingHoursSpecification`, `parentOrganization` (referenced to main `#organization`)

## Deployment Workflow

Always walk the user through this exact sequence:

1. **Verify prerequisites** — confirm sitewide `#organization` schema is running on the site (View Source on any page, search for the `LegalService` `@id`)
2. **Choose installation method:**
   - **Plugin upload** (recommended for production) — zip and upload via Plugins → Add New → Upload Plugin
   - **Child theme `functions.php`** — only for single sites where a plugin is overkill
   - **Code Snippets plugin** — only when the user has no FTP and no plugin upload permission
3. **Configure `site-config.php`** for the specific site (language, ACF fields, page detection)
4. **Activate the plugin**
5. **Clear ALL caches** — WP Rocket, W3 Total Cache, LiteSpeed, Cloudflare, etc. This step is mandatory; caching is the #1 reason deployed schema appears not to work.
6. **Validate** in both Rich Results Test and Schema Markup Validator
7. **Document per-site config changes** for future reference

## Common Pitfalls (Watch For These)

1. **Editing parent theme instead of child theme.** Always check `Appearance → Themes` for the active theme name. Avada parent theme edits are wiped on update and ignored when Avada Child is active.

2. **Caching masking the deployment.** If schema doesn't appear after install, clear caches BEFORE diagnosing further. WP Rocket is especially common on Avada sites.

3. **Spanish-only or English-only sites defaulting to wrong language.** Single-language sites have no Polylang/WPML and no `/es/` URL marker, so the URL fallback fails. Use `$force_language` in config.

4. **Undefined `@id` references.** If validators warn about missing `#organization`, the sitewide schema isn't running. The post-level plugin will not work without that prerequisite — fix the sitewide schema first.

5. **Double-output JSON-LD.** If two plugins or snippets both inject schema, you'll get duplicate entities. Check for existing schema before adding new code.

6. **`<?php` tag confusion when pasting.** PHP files already start with `<?php` — pasting another opens a syntax error. When pasting into `functions.php`, omit the opening `<?php` from the snippet.

7. **Using `is_page()` for custom post types.** `is_page()` only matches WordPress Pages. For custom post types like "Practice Area," use `is_singular('practice_area')`.

## Decision Framework

When the user asks for a new schema type or modification, ask yourself:

1. **What page type does this apply to?** (Check the Page Type Mapping table above.)
2. **Does the site have the necessary content?** (E.g., FAQPage needs Q&A content; LegalService needs practice area descriptions.)
3. **Is there a sitewide entity to reference?** (Author → attorney profile; service → practice area page.)
4. **What's the per-site config impact?** (New post type? New page slug? New language source?)
5. **What validators will flag, and how do we test?** (Rich Results vs Schema Markup Validator behave slightly differently.)

If any answer is unclear, ASK the user before generating code. Do not assume site structure.

## Reference Files

For deeper context, load these files as needed:

- `references/architecture.md` — Full plugin class structure with code templates
- `references/schema-types.md` — Detailed JSON-LD examples for each schema type
- `references/deployment.md` — Step-by-step deployment guide (plugin, child theme, Code Snippets)
- `references/validation.md` — How to interpret validator results, common errors and fixes
- `references/conventions.md` — Full project conventions document (mirrors what's in Claude Projects)

## Output Format

When producing plugin code:

- **PHP 7.2+ compatible** (Avada and most law firm hosts support this minimum)
- **WordPress coding standards** for indentation, spacing, function naming
- **Use `wp_json_encode()` with `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT`** for readable output that handles Spanish characters correctly
- **Prefix all functions and classes** with `firm_legal_` to avoid collisions
- **Include inline comments** explaining non-obvious logic
- **Always include the plugin header** with version, description, and author

When producing deployment guides:

- Step-by-step numbered instructions
- Specify exact file paths (`/wp-content/themes/Avada-Child-Theme/functions.php`)
- Include verification steps (View Source, validator URLs)
- Call out cache clearing as a mandatory step

When auditing existing schema:

- Quote the problematic field with `@id` context
- Explain why it violates conventions
- Provide the corrected version
- Recommend validator re-test after fix
