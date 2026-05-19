# Deployment Reference

Step-by-step guide for deploying law firm schema plugins.

## Installation Methods (Choose One Per Site)

### Method 1 — Plugin Upload (Recommended for Production)

**When to use:** Standard deployment to any law firm site. Best balance of safety, portability, and ease.

**Steps:**

1. Build the plugin files in the modular structure.
2. Zip the entire plugin folder (NOT just the PHP files — the folder must be the top level of the zip).
3. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
4. Choose the zip file, click **Install Now**.
5. After install, click **Activate Plugin**.
6. Edit `config/site-config.php` via FTP or the WordPress Plugin File Editor for per-site settings.
7. Clear all caches.
8. Validate.

**Pros:**
- Survives theme changes
- Easy version updates (re-upload zip, choose "Replace current with uploaded")
- Easy to disable (deactivate plugin)
- Clear separation from theme code

**Cons:**
- Requires plugin upload permission (some managed hosts disable this)

### Method 2 — Child Theme functions.php

**When to use:** Single-site deployments, or when plugin uploads are blocked. Best for tightly-controlled sites where you also manage the theme.

**Steps:**

1. Identify the active theme (Appearance → Themes). Confirm it's a child theme (e.g., "Avada Child").
2. Open the child theme's `functions.php` via FTP or cPanel File Manager.
3. Path is typically `/wp-content/themes/[child-theme-name]/functions.php`.
4. Paste the plugin's main function code at the bottom of the file (omit the `<?php` opening tag — the file already has one).
5. Save.
6. Clear all caches.
7. Validate.

**Pros:**
- No plugin upload needed
- Simple for single sites

**Cons:**
- Lost if theme changes (must remember to migrate)
- Less modular (all code in one file)
- Risk of breaking the site with a syntax error

**Critical:** NEVER paste into the PARENT theme. Changes to parent theme `functions.php`:
- Are ignored when a child theme is active (no effect)
- Get wiped on theme updates

### Method 3 — Code Snippets Plugin

**When to use:** Client has WordPress admin but no FTP, and plugin uploads are blocked. Last resort.

**Steps:**

1. Install **Code Snippets** plugin (free, by Code Snippets Pro) or **WPCode**.
2. Snippets → Add New.
3. Paste the schema function code WITHOUT the opening `<?php` tag.
4. Set to "Run snippet everywhere" or "Front-end only."
5. Save and Activate.
6. Clear caches.
7. Validate.

**Pros:**
- No FTP needed
- Client can disable easily

**Cons:**
- Less professional for multi-site rollouts
- Snippets plugin must remain installed for it to keep working

## Pre-Deployment Checklist

Before deploying ANY schema to a law firm site, verify:

- [ ] **Sitewide schema is running.** View Source on any page → search for `LegalService` and `#organization`. If missing, build that first.
- [ ] **Active theme identified.** Admin → Appearance → Themes. Note the active theme name.
- [ ] **Caching plugin identified.** Common: WP Rocket, W3 Total Cache, LiteSpeed Cache, Cloudflare. Know how to purge each one BEFORE deploying.
- [ ] **Multilingual setup confirmed.** Check for Polylang/WPML plugins, or `/es/` in URLs. Document language detection method.
- [ ] **ACF in use?** If yes, get the actual field names for author overrides (Field Group → Field Name, not Field Label).
- [ ] **Custom post types identified.** What post type is "Attorney"? "Practice Area"? Note slugs from `Settings → Permalinks` or `wp post-type list` (WP-CLI).
- [ ] **Existing schema check.** View Source on a few pages. If you find existing `BlogPosting`, `LegalService`, or other JSON-LD blocks, you'll be DUPLICATING schema. Either disable the existing source or skip the deployment.

## Post-Deployment Verification

After deploying, always run this sequence:

### 1. Confirm Schema Appears

Open one page of each schema type in incognito mode. View Source. Search for the relevant `@type`:
- Blog post → search for `"@type":"BlogPosting"`
- Attorney page → search for `"@type":"Person"` with `worksFor`
- Practice area → search for `"@type":"LegalService"` with `provider`

If you don't see it:
1. Clear ALL caches (WP Rocket, Cloudflare, etc.)
2. Test again in incognito
3. Verify the plugin is activated (Plugins → Installed Plugins)
4. Check error log if still missing

### 2. Run Validators

For each schema type, test ONE page in BOTH validators:

**Google Rich Results Test** (https://search.google.com/test/rich-results):
- Paste URL
- Expected: Detection of schema type, zero errors
- Watch for: "undefined @id reference" warnings (indicates missing sitewide schema)

**Schema Markup Validator** (https://validator.schema.org/):
- Paste URL
- Expected: All entities parsed, zero errors
- Watch for: Type mismatches, missing required properties

### 3. Test Edge Cases

- A post without a featured image (image field should be absent, not null)
- A post without tags (keywords field should be absent)
- A post without a category (breadcrumb skips category, post is at position 2)
- A post in the secondary language (inLanguage and breadcrumb root should match)
- An attorney page with minimal data (should still validate cleanly)

### 4. Spot-Check Production Pages

After validating individual pages, browse 5–10 pages of each type in incognito mode to confirm:
- No PHP errors visible in HTML
- No duplicate JSON-LD blocks
- Page renders normally (schema injection didn't break anything)
- Page load time is unaffected

## Cache Clearing — Critical Step

**Caching is the #1 reason deployed schema appears not to work.**

### Common Caching Plugins (Clear These)

- **WP Rocket** — Admin top bar → "WP Rocket" → "Clear Cache"
- **W3 Total Cache** — Performance → Dashboard → "Empty All Caches"
- **WP Super Cache** — Settings → WP Super Cache → "Delete Cache"
- **LiteSpeed Cache** — LiteSpeed Cache → "Purge All"
- **Cache Enabler** — Settings → Cache Enabler → "Clear Cache"
- **Autoptimize** — Settings → Autoptimize → "Delete Cache"

### CDN Caches (Don't Forget)

- **Cloudflare** — Dashboard → Caching → Configuration → "Purge Everything"
- **KeyCDN, BunnyCDN, etc.** — Use their dashboard's purge function
- **Pantheon/WP Engine** — Use the host's cache clear tool

### Browser Cache

After clearing server-side caches, ALWAYS test in incognito/private mode. Browser cache can persist for hours.

## Updating Plugins

When pushing a new version:

1. Update the `Version:` field in the plugin header (e.g., `1.0.0` → `1.1.0`)
2. Re-zip the plugin folder
3. On each deployed site: Plugins → Add New → Upload Plugin → Choose new zip
4. WordPress prompts: "Replace current with uploaded?" → click "Replace current with uploaded"
5. Plugin updates without losing activation state or per-site config
6. Clear caches
7. Re-validate

Some plugins use a `firm_legal_schema_loaded` action hook to trigger admin notices on update — useful for reminding the client to verify after an upgrade.

## Rollback Plan

If deployment breaks the site:

1. **Via WordPress admin:** Plugins → Installed Plugins → Deactivate the schema plugin
2. **Via FTP:** Rename the plugin folder to `firm-legal-schema-suite-DISABLED`. WordPress will deactivate it on the next admin load.
3. **Via FTP (theme method):** Rename `functions.php` to `functions.php.broken` and upload a backup
4. **Worst case:** Rename the child theme folder to force fallback to parent theme

Always have FTP access ready before deploying via `functions.php`. A missing semicolon takes the whole site down.

## Multi-Site Rollout

For agencies managing many law firm sites:

1. **Canonical plugin version** — keep one version of the plugin in version control (Git)
2. **Per-site config docs** — maintain a spreadsheet/doc tracking each site's config (language, ACF fields, etc.)
3. **Standardize naming** — same plugin folder name on every site for easier bulk operations
4. **Bulk deploy** — use FTP scripting, WP-CLI, or a deployment tool like InfiniteWP/ManageWP
5. **Staggered rollout** — deploy to test sites first, validate, then push to production sites
6. **Validation tracking** — log Rich Results Test results per site to confirm post-deployment health
