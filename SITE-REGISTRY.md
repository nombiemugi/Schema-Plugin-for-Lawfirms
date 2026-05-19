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
