=== Law Firm Legal Schema Suite ===
Contributors: Andres from Almost Illegal Ads
Tags: schema, json-ld, structured-data, seo, legal, law-firm
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.3.0
Requires PHP: 7.2
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modular Schema.org structured data plugin for U.S. law firm websites. Auto-detects language, references sitewide entities, supports multiple page types.

== Description ==

The Law Firm Legal Schema Suite outputs Schema.org structured data (JSON-LD) on law firm WordPress sites. Built with a modular architecture, it can handle multiple page types — blog posts, attorney bios, practice areas, FAQ pages, contact pages, and more — all from a single plugin.

= Key Features =

* Modular handler architecture — add new schema types without touching existing code
* Auto-detects language via Polylang, WPML, or URL pattern
* Per-site `force_language` override for single-language sites
* References sitewide `#organization`, `#website`, and `#logo` by `@id` — never duplicates them
* Lightweight author entities matching the profile page `@id` for entity continuity
* Configurable Person `@id` shape (`person_id`) — one setting governs the blog author, the Attorney handler, and the About page mention, so they can never drift apart
* Works beyond law firms — the `@id` can be anchored to the person's own bio page (`{profile-url}/#person`) instead of the law-firm `{home}/#attorney-{slug}` convention
* Localized breadcrumbs (Home/Inicio)
* ACF integration for custom author overrides with graceful WordPress fallback
* Optional fixed-author attribution — pin every blog post's author (name, url, and @id) to one configured person's dedicated profile page
* Drops null/empty fields automatically for cleaner output

= Schema Types Supported =

* BlogPosting (single blog posts, with Person author and BreadcrumbList)
* AboutPage (with optional Person mention via ACF and primaryImageOfPage)
* ContactPage (with optional ContactPoint when a dedicated phone is configured)
* Testimonials (plain WebPage — no Review/AggregateRating, per anti-fabrication policy)
* Video Library (CollectionPage + ItemList of VideoObject from an ACF repeater; auto-normalizes YouTube and Vimeo URLs)
* Blog index (plain WebPage for /blog/)
* Policy pages — Privacy Policy, Terms of Use, Legal Disclaimers (WebPage + CreativeWork mainEntity, one handler driven by config)
* Attorney/Person (handler stub — not yet built)
* LegalService for practice areas (handler stub — not yet built)
* FAQPage (handler stub — not yet built)

= Bilingual Slug Detection =

Each static page type in `config/site-config.php` declares English and Spanish slug variants. The router matches the current WP page against all configured slugs simultaneously, so one config handles English-only, Spanish-only, and bilingual sites. Language detection (en-US / es-US) still flows through Polylang, WPML, the `/es/` URL marker, or the `force_language` override.

= Prerequisite =

Sitewide schema (LegalService `#organization`, `#website`, `#logo`) must already be emitted on every page by another plugin or snippet. This plugin only adds page-level schema and references those sitewide entities by `@id`.

== Installation ==

1. Upload the `firm-legal-schema-suite` folder to `/wp-content/plugins/`, OR upload the zip via Plugins → Add New → Upload Plugin
2. Activate the plugin through the WordPress 'Plugins' menu
3. Edit `config/site-config.php` to match your site — it ships as a template, so work through the MINIMUM CHECKLIST in its header comment
4. Clear all caches (WP Rocket, W3 Total Cache, Cloudflare, etc.)
5. Validate output using Google Rich Results Test and Schema Markup Validator

== Configuration ==

All site-specific settings live in `config/site-config.php`. It ships as a TEMPLATE — no real site's data is committed to it, and every value is either a safe default or a placeholder to replace. The inline comments document each option and use three markers:

* `[PER-SITE]` — you must set this; a wrong or stale value produces wrong schema with no error
* `[DEFAULT]` — safe to leave alone; change only if the site differs
* `[EXAMPLE]` — illustration in a comment, never a live value

The file header lists a five-step minimum checklist for a new site.

= Common configurations =

* English-only site: set `force_language` to `'en-US'`
* Spanish-only site: set `force_language` to `'es-US'`
* Bilingual site with `/es/` URLs: leave `force_language` as `null` (auto-detection)
* Different ACF field names: update `acf_author_name_field` and `acf_author_url_field`
* Attribute every blog post to one person: set `blog_author` `name` + `url` (their dedicated profile page); leave `name` empty to keep per-post authors

= Person @id presets =

`person_id` decides the shape of the `@id` for every `Person` the plugin emits. It MUST reproduce, character for character, the `@id` that the person's own profile page already declares — otherwise Google reads two unrelated people and nothing in the validators looks wrong. Open that page, view source, find the `Person` in its JSON-LD, and copy its `@id` before choosing a preset.

* **A — law-firm convention** (shipped default): `base` `'home'`, `fragment` `'attorney'`, `append_slug` `true` → `https://example.com/#attorney-jane-doe`
* **B — anchored to the person's own bio page**, correct for most non-law-firm sites: `base` `'author_url'`, `fragment` `'person'`, `append_slug` `false` → `https://example.com/meet-the-team/jane-doe/#person`
* **C — profile at a fixed URL** the plugin can't derive: `base` `'https://example.com/team/jane/'`, `fragment` `'person'`, `append_slug` `false` → `https://example.com/team/jane/#person`

= Silent failure modes =

These produce no error, just missing or wrong output — check them first when schema "isn't showing":

* A stale page slug means the handler never fires, even with its `enabled_schemas` toggle on
* Wrong ACF field names degrade to the native WordPress author instead of erroring
* `force_language` left `null` with no Polylang/WPML installed falls through to `en-US` by accident rather than by decision
* A typo in `blog_author` `url` silently splits the author into a second entity under `person_id` base `'author_url'`
* Caches not cleared after deploying — the most common cause of all

== Changelog ==

= 2.3.0 =
* The Person `@id` is now configurable via a new `person_id` block in `config/site-config.php` (`base`, `fragment`, `append_slug`) instead of being hardcoded to `{home}/#attorney-{slug}`. `base` accepts `'home'`, `'author_url'` (the person's own profile URL), or any literal URL — so non-law-firm sites can emit `{profile-url}/#person`, and profiles hosted on another URL are supported
* All three Person emitters (blog author, Attorney handler, About page mention) now route through one shared `Firm_Legal_Schema_Base::build_person_id()`, so a single config change keeps them pointing at the same entity — previously the pattern was duplicated in three files and could drift
* Added optional `pages.about_page.primary_attorney.profile_url` — when the bio lives on its own page rather than the About page, it becomes the Person's `url` and the `@id` base
* Marked the `managing_attorney` config block DEPRECATED / INERT: `resolve_author()` has always read `blog_author`, never `managing_attorney`, so editing it never affected output. Values blanked to stop it reading as live config; use `blog_author` instead
* Defaults preserve 2.2.1 output exactly (`base` `'home'`, `fragment` `'attorney'`, `append_slug` `true`) — existing law-firm deployments are unaffected
* `config/site-config.php` is now an explicit TEMPLATE: all real site data removed (no live deployment's domains, names, or profile URLs), and every option marked `[PER-SITE]`, `[DEFAULT]`, or `[EXAMPLE]` so it's unambiguous what must be changed when deploying
* Added a five-step minimum checklist to the config file header, and documented the silent failure modes (stale slugs, wrong ACF field names, unpinned `force_language`, mistyped author URL) that produce wrong output without an error
* Example values throughout the config and handler docblocks are now generic (`example.com`, `Jane Doe`) instead of referencing real deployments

= 2.2.1 =
* Setup + docs: added a step-by-step for managing-attorney attribution — set `managing_attorney` `name` + the attorney's EXACT dedicated profile-page `url` in `config/site-config.php`. The author `@id` is derived from the name (`#attorney-{slug}`) and must match the attorney's profile page for graph continuity
* Reference deployment (Lincoln Goldfinch) `managing_attorney` config pinned to Kate Lincoln-Goldfinch's exact profile URL
* No handler code changes — behavior is identical to 2.2.0; the BlogPosting handler stays fully config-driven and reusable across sites (edit only `config/site-config.php` per site)

= 2.2.0 =
* Added handlers for Attorney (Person), Practice Area (LegalService + OfferCatalog), Team listing (CollectionPage + ItemList), Practice Areas listing, and Generic pages (plain WebPage for media/jobs/blog-index pages that don't warrant a richer schema type)
* Added hierarchical-page detection — attorneys and practice areas can be child WP pages under configured parent slugs, not only custom post types (supports the Lincoln Goldfinch site structure)
* Added optional managing-attorney attribution for blog posts: when `managing_attorney` (`name` + `url`) is set in `config/site-config.php`, every BlogPosting author `Person` uses that name, dedicated profile-page URL, and matching `#attorney-{slug}` `@id`, overriding the per-post ACF / native WP author
* Backward compatible — leaving `managing_attorney` `name` empty preserves the existing ACF → WordPress author behavior

= 2.1.0 =
* Added AboutPage, ContactPage, Testimonials, Video Library, Blog index, and Policy Page handlers
* Bilingual slug matching: each page type takes en/es slug variants in a single config entry
* Router now uses a slug-array helper (`is_page( array_values( $slugs ) )`) so the same config works on English-only, Spanish-only, and bilingual sites
* Single generic Policy Page handler covers Privacy Policy, Terms of Use, Legal Disclaimers (extensible via config)
* Video Library handler reads from an ACF repeater and auto-normalizes YouTube/Vimeo URLs into both `embedUrl` and `contentUrl`
* Testimonials handler intentionally emits no Review/AggregateRating — enforces the no-fabricated-trust-signals policy
* Config shape changed: legacy `*_page_slug` keys still work as a fallback, but new deployments should use the structured `pages` array

= 2.0.0 =
* Initial release of the modular architecture
* BlogPosting handler with full feature set
* Router + base class + breadcrumbs builder ready for future schema types
* Per-site configuration file pattern

== Upgrade Notice ==

= 2.2.1 =
Documentation/config release clarifying how to set the managing-attorney's exact profile URL and the matching `#attorney-{slug}` `@id`. No code changes — a safe drop-in over 2.2.0.

= 2.2.0 =
Adds Attorney, Practice Area, Team listing, Practice Areas listing, and Generic page handlers, hierarchical-page detection, and optional managing-attorney attribution for blog posts. Backward compatible — leave the new `managing_attorney` config block empty to keep the existing per-post author behavior.

= 2.1.0 =
Adds AboutPage, ContactPage, Testimonials, Video Library, Blog index, and Policy Page handlers, plus bilingual slug detection. The config shape was extended (new `pages` and `policy_pages` arrays); legacy flat `*_page_slug` keys still resolve as a fallback so v2.0.0 deployments keep working until migrated.

= 2.0.0 =
First public release of the modular suite. Replaces the single-file BlogPosting plugin (v1.x).
