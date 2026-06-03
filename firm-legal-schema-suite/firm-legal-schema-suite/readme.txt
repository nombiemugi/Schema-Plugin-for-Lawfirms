=== Law Firm Legal Schema Suite ===
Contributors: Andres from Almost Illegal Ads
Tags: schema, json-ld, structured-data, seo, legal, law-firm
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.2.0
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
* Lightweight author entities matching attorney profile page `@id` for entity continuity
* Localized breadcrumbs (Home/Inicio)
* ACF integration for custom author overrides with graceful WordPress fallback
* Optional managing-attorney attribution — pin every blog post's author (name, url, and @id) to one configured attorney's dedicated profile page
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
3. Edit `config/site-config.php` to match your site's settings (language, ACF fields, page detection)
4. Clear all caches (WP Rocket, W3 Total Cache, Cloudflare, etc.)
5. Validate output using Google Rich Results Test and Schema Markup Validator

== Configuration ==

All site-specific settings live in `config/site-config.php`. See the inline comments in that file for documentation of each option.

= Common configurations =

* English-only site: set `force_language` to `'en-US'`
* Spanish-only site: set `force_language` to `'es-US'`
* Bilingual site with `/es/` URLs: leave `force_language` as `null` (auto-detection)
* Different ACF field names: update `acf_author_name_field` and `acf_author_url_field`
* Attribute every blog post to one managing attorney: set `managing_attorney` `name` + `url` (their dedicated profile page) in `config/site-config.php`; leave `name` empty to keep per-post authors

== Changelog ==

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

= 2.2.0 =
Adds Attorney, Practice Area, Team listing, Practice Areas listing, and Generic page handlers, hierarchical-page detection, and optional managing-attorney attribution for blog posts. Backward compatible — leave the new `managing_attorney` config block empty to keep the existing per-post author behavior.

= 2.1.0 =
Adds AboutPage, ContactPage, Testimonials, Video Library, Blog index, and Policy Page handlers, plus bilingual slug detection. The config shape was extended (new `pages` and `policy_pages` arrays); legacy flat `*_page_slug` keys still resolve as a fallback so v2.0.0 deployments keep working until migrated.

= 2.0.0 =
First public release of the modular suite. Replaces the single-file BlogPosting plugin (v1.x).
