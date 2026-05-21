# Site Registry

Track per-site configurations, deployment status, and validation results for all law firm sites where the plugin is deployed.

When deploying to a new site, add it here with all relevant details. Reference this file when working on a specific site to avoid re-asking the user for the same info.

---

## Irving Law Firm

**Site URL:** https://www.theirvinglawfirm.com

**Status:** ✅ Deployed (BlogPosting only)

**Active Theme:** Avada Child Theme (parent: Avada)

**Caching:**
- WP Rocket 3.21.3 (active — requires manual purge after deployment)
- No CDN cache identified

**Multilingual Setup:** Bilingual (English + Spanish via `/es/` URL pattern). No Polylang or WPML — uses URL-based detection.

**Configuration values:**
```php
'force_language'        => null,           // Auto-detect via URL
'spanish_url_marker'    => '/es/',
'acf_author_name_field' => 'autor_nombre', // verified
'acf_author_url_field'  => 'autor_url',    // verified
```

**Installation Method:** Currently in `Avada-Child-Theme/functions.php` (v1.x format).

**Recommended Action:** Migrate to plugin format (v2.0.0) at next maintenance window.

**Custom Post Types:** (to be confirmed when expanding beyond BlogPosting)
- Attorney: TBD
- Practice Area: TBD
- Case Result: TBD

**Page Slugs:** (to be confirmed when expanding beyond BlogPosting)
- Contact: TBD
- About: TBD
- FAQ: TBD

**Sitewide Schema Source:** Confirmed running on every page (Yoast? Custom snippet? — verify).

**Enabled Schemas:**
- ✅ BlogPosting
- ⬜ Attorney
- ⬜ Practice Area
- ⬜ Contact Page
- ⬜ About Page
- ⬜ FAQ Page

**Last Validated:** [date of last full validation pass]

**Validation Results:**
- ✅ Google Rich Results Test — clean (BlogPosting + BreadcrumbList detected, zero errors)
- ✅ Schema Markup Validator — clean

**Test URLs:**
- English: https://www.theirvinglawfirm.com/blog/can-back-child-support-be-forgiven-arlington-va/
- Spanish: TBD

**Notes:**
- WP Rocket cache must be manually purged after every plugin update
- Sitewide `#organization` schema referenced successfully (no undefined @id warnings)

---

## Lincoln Goldfinch Law

**Site URL:** https://www.lincolngoldfinch.com

**Status:** 🟡 In Progress (handlers built v2.2.0 — pending deploy + slug confirmation)

**Active Theme:** TBD (confirm on deploy)

**Caching:** TBD (confirm WP Rocket / CDN setup on deploy — purge after every release)

**Multilingual Setup:** Bilingual (English + Spanish via `/es/` URL pattern). No Polylang or WPML — uses URL-based detection.

**Configuration values:**
```php
'force_language'        => null,           // Auto-detect via /es/ marker
'spanish_url_marker'    => '/es/',
'acf_author_name_field' => 'autor_nombre', // TBD — only relevant if WP posts use ACF for author
'acf_author_url_field'  => 'autor_url',    // TBD
```

**Installation Method:** Plugin (firm-legal-schema-suite v2.2.0+).

**Site Structure (CRITICAL — different from CPT-based sites):**

Attorneys and practice areas are **hierarchical WP pages**, NOT custom post types.
- Attorneys: child pages under `/meet-our-team/` (e.g., `/meet-our-team/kate-lincoln-goldfinch/`)
- Practice areas: child pages under `/practice-areas/` (e.g., `/practice-areas/family-immigration/`)

This means `attorney_post_type` / `practice_area_post_type` are **not** used here. Detection is driven by `attorney_parent_pages` and `practice_area_parent_pages` slugs.

**Page Slugs:**
- About: `about-us` (es: TBD — confirm on deploy)
- Contact: `contact-us` (es: TBD)
- Meet Our Team (listing): `meet-our-team` (es: TBD — likely `conoce-nuestro-equipo` or `nuestro-equipo`)
- Practice Areas (listing): `practice-areas` (es: TBD — likely `areas-de-practica`)
- Blog: `blog` (es: `blog`)
- In The Media: `in-the-media` (es: TBD — likely `medios`)
- Jobs: `jobs` (es: TBD — likely `empleos`)

**Sitewide Schema Source:** TBD — verify `#organization`, `#website`, `#logo` are emitted on every page before enabling this plugin's handlers.

**Enabled Schemas:**
- ✅ BlogPosting (single posts)
- ✅ Attorney (Person — hierarchical-page detection)
- ✅ Practice Area (LegalService + OfferCatalog from configured subtopics)
- ✅ Team Listing (CollectionPage + ItemList)
- ✅ Practice Areas Listing (CollectionPage + ItemList)
- ✅ About Page
- ✅ Contact Page
- ✅ Generic Pages (WebPage for /in-the-media/, /jobs/, /blog/ — no fabricated VideoObject / JobPosting)
- ⬜ FAQ Page

**Practice Area Subtopics:** Populate `practice_areas` config block per-area before enabling `practice_area` handler. Subtopics drive `hasOfferCatalog → OfferCatalog → Offer[]` on each LegalService entity.

**Last Validated:** N/A (pending first deploy)

**Validation Results:** N/A (pending first deploy)

**Test URLs (pending):**
- English homepage: https://www.lincolngoldfinch.com/
- English single attorney: https://www.lincolngoldfinch.com/meet-our-team/kate-lincoln-goldfinch/
- English single practice area: https://www.lincolngoldfinch.com/practice-areas/family-immigration/
- English team listing: https://www.lincolngoldfinch.com/meet-our-team/
- English practice areas listing: https://www.lincolngoldfinch.com/practice-areas/
- English blog index: https://www.lincolngoldfinch.com/blog/
- English in-the-media: https://www.lincolngoldfinch.com/in-the-media/
- English jobs: https://www.lincolngoldfinch.com/jobs/
- Spanish equivalents: TBD (confirm slugs on deploy)

**Notes:**
- Confirm all Spanish slugs against the live site on first deploy — placeholders in `site-config.php` are educated guesses
- Populate `practice_areas` config with subtopic lists per practice area before flipping the toggle
- Per `CLAUDE.md`: purge WP Rocket + Cloudflare + browser cache after every plugin update or config change

---

## [Template — Copy Below for New Sites]

## Client Name

**Site URL:**

**Status:** ⬜ Pending / 🟡 In Progress / ✅ Deployed

**Active Theme:**

**Caching:**

**Multilingual Setup:**

**Configuration values:**
```php
'force_language'        => null,
'spanish_url_marker'    => '/es/',
'acf_author_name_field' => 'autor_nombre',
'acf_author_url_field'  => 'autor_url',
```

**Installation Method:**

**Custom Post Types:**

**Page Slugs:**

**Sitewide Schema Source:**

**Enabled Schemas:**
- ⬜ BlogPosting
- ⬜ Attorney
- ⬜ Practice Area
- ⬜ Contact Page
- ⬜ About Page
- ⬜ FAQ Page

**Last Validated:**

**Validation Results:**

**Test URLs:**

**Notes:**

---
