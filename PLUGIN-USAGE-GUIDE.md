# Plugin Usage Guide — Law Firm Legal Schema Suite

End-to-end walkthrough for packaging, installing, configuring, updating, and testing the plugin. Written for a deployment to a WordPress site hosted under DirectAdmin (e.g., `example.com`).

---

## Part 1 — What to Zip

The plugin source on this machine lives at:

```
a:\AIA\Development\plugin-builder-schema\firm-legal-schema-suite\firm-legal-schema-suite\
```

The path is doubled on purpose: the outer `firm-legal-schema-suite\` is just a wrapper around the actual plugin folder. **You must zip the inner folder, with the folder itself as the top entry of the zip** — not the files inside it, and not the wrapper folder.

### Verify the correct folder

The inner folder should contain exactly these items:

```
firm-legal-schema-suite/
├── firm-legal-schema-suite.php
├── readme.txt
├── config/
│   └── site-config.php
└── includes/
    ├── class-breadcrumbs.php
    ├── class-schema-base.php
    ├── class-schema-router.php
    └── handlers/
        ├── class-about-page.php
        ├── class-blog-index.php
        ├── class-blog-posting.php
        ├── class-contact-page.php
        ├── class-policy-page.php
        ├── class-testimonials.php
        └── class-video-library.php
```

If your folder looks different (missing handlers, extra files, no `config/` folder), stop and fix that before zipping.

### How to zip on Windows

**Option A — File Explorer (easiest)**

1. Open File Explorer.
2. Navigate to `a:\AIA\Development\plugin-builder-schema\firm-legal-schema-suite\`.
3. Right-click on the inner folder named `firm-legal-schema-suite`.
4. Choose **Send to → Compressed (zipped) folder**.
5. Windows creates `firm-legal-schema-suite.zip` in the same location.

**Option B — PowerShell**

```powershell
Compress-Archive `
  -Path "a:\AIA\Development\plugin-builder-schema\firm-legal-schema-suite\firm-legal-schema-suite" `
  -DestinationPath "a:\AIA\Development\plugin-builder-schema\firm-legal-schema-suite.zip" `
  -Force
```

### Verify the zip is correct

After zipping:

1. Right-click `firm-legal-schema-suite.zip` → **Open** (do not extract — just peek).
2. You should see exactly one item at the top: a folder named `firm-legal-schema-suite`.
3. Open it. You should see `firm-legal-schema-suite.php`, `readme.txt`, `config/`, `includes/`.

If you instead see `firm-legal-schema-suite.php` and `config/` at the top of the zip with no wrapping folder, **WordPress will reject the upload**. Re-zip starting from one level up.

---

## Part 2 — Install on a Fresh WordPress Site

### Prerequisite check

Before uploading, confirm the sitewide `LegalService #organization` schema is running. Without it, this plugin's output will have unresolved `@id` references and fail validation.

1. Open any page on the site (e.g., the homepage).
2. View Source (Ctrl+U).
3. Ctrl+F and search for `"@type":"LegalService"` and `"#organization"`.
4. If both appear in a `<script type="application/ld+json">` block — good, continue.
5. If they don't appear — stop. The sitewide schema must be added first (separate plugin or theme snippet). This plugin only emits page-level schema and references the sitewide entities.

### Upload via WordPress admin

1. Log in to WordPress admin: `https://www.example.com/wp-admin/`.
2. Go to **Plugins → Add New Plugin**.
3. Click **Upload Plugin** at the top of the page.
4. Click **Choose File** and select `firm-legal-schema-suite.zip` from your computer.
5. Click **Install Now**.
6. After install completes, click **Activate Plugin**.

**At this point the plugin is loaded but mostly dormant.** Only `blog_posting` is enabled by default in the shipped config. Nothing breaks, but you need to configure it for the site before the new schema types appear.

---

## Part 3 — Configure for the Site (via DirectAdmin File Manager)

All site-specific configuration lives in **one file**: `config/site-config.php`. There is no admin UI — edits are made directly to the file.

### Step 3.1 — Open DirectAdmin File Manager

1. Log in to DirectAdmin (URL provided by your host, often `https://yourserver:2222/`).
2. From the dashboard, open **System Info & Files → File Manager** (the menu wording varies by skin — look for "File Manager").

### Step 3.2 — Navigate to the plugin folder

The path depends on the host's layout, but it's typically one of:

```
domains/example.com/public_html/wp-content/plugins/firm-legal-schema-suite/
```

or sometimes:

```
public_html/wp-content/plugins/firm-legal-schema-suite/
```

Click through the folders one at a time:

1. `domains/` (skip if your layout starts at `public_html/`)
2. `example.com/`
3. `public_html/`
4. `wp-content/`
5. `plugins/`
6. `firm-legal-schema-suite/`
7. `config/`

You should now see `site-config.php` in the right-hand pane.

### Step 3.3 — Open the file for editing

1. Click on `site-config.php` (single click selects it).
2. In the action toolbar, click **Edit** (or right-click → **Edit**).
3. DirectAdmin opens a web-based text editor.

If your DirectAdmin version doesn't have an inline editor, use the **Download** option, edit the file locally in VS Code or Notepad++, then upload it back with the same filename (overwrite).

### Step 3.4 — Edit the config

The file is heavily commented. The keys you most often change for a new site:

#### `force_language`

For a single-language site, set this and skip the URL/plugin detection:

```php
'force_language' => 'en-US',   // English-only site
// or
'force_language' => 'es-US',   // Spanish-only site
// or
'force_language' => null,      // Bilingual site (auto-detect — leave as null)
```

For Austin Bankruptcy (bilingual `/es/`): leave as `null`.

#### `pages` — slugs per schema type

Each entry has `'slugs' => array( 'en' => ..., 'es' => ... )`. Replace the placeholder Spanish slugs with the **actual** slugs of the Spanish pages on the site.

Example for Austin Bankruptcy:

```php
'about_page' => array(
    'slugs' => array(
        'en' => 'about-us',
        'es' => 'sobre-nosotros',   // Verify in WP admin → Pages
    ),

    // See "About page — primary attorney" below.
    'primary_attorney' => array(
        'name'          => 'Jane Doe',
        'job_title'     => 'Founding Attorney',
        'image_url'     => 'https://www.example.com/wp-content/uploads/.../jane-doe.webp',
        'image_caption' => '',                // auto: "Jane Doe of Austin Bankruptcy Lawyers"
        'same_as'       => array(
            'https://www.avvo.com/attorneys/...',
            'https://www.linkedin.com/in/...',
        ),
    ),
),
'contact_page' => array(
    'slugs' => array(
        'en' => 'contact-us',
        'es' => 'contactanos',      // Verify in WP admin → Pages
    ),
    'telephone'    => '',           // Set only if the page has a dedicated phone
    'contact_type' => 'customer support',
),
```

**To find the actual slug** of any page: WP admin → **Pages** → hover over the page → look at the URL preview. The slug is the last path segment before the trailing slash. For `https://www.example.com/es/sobre-nosotros/`, the slug is `sobre-nosotros`.

#### About page — primary attorney

The About page emits a `Person` entity (the firm's main attorney / founding partner / CEO) and a `mentions` reference back to that Person from the `AboutPage`. The data lives entirely in `site-config.php` — there are no ACF fields to set in WP admin.

Fields under `pages.about_page.primary_attorney`:

| Field | Required | Notes |
|---|---|---|
| `name` | Yes — if blank, the Person + mention are silently omitted | Display name. The attorney `@id` is built as `home_url + '#attorney-' + sanitize_title(remove_accents(name))` and must match the dedicated attorney profile page. |
| `job_title` | No (default `'Attorney'`) | E.g. `'Founding Partner'`, `'Managing Attorney'`, `'CEO'`. |
| `image_url` | No | Direct URL to the attorney's headshot. When set, takes precedence over the About page's WP featured image for the `#primaryimage` ImageObject. |
| `image_caption` | No | Caption text for the primary image. When blank and `image_url` is supplied, auto-built as `"{name} of {site name}"`. |
| `same_as` | No | Array of profile URLs (Avvo, LinkedIn, State Bar listing, etc.). Invalid URLs are dropped silently. |

**Behavior when `name` is blank:** the AboutPage still renders cleanly with the BreadcrumbList and (if the page has a WP featured image) an ImageObject — but the Person node and the `mentions` field are omitted. Use this for sites that don't profile a single attorney on the About page.

**Behavior when `image_url` is blank:** the handler falls back to the About page's WP featured image (if any), reusing its caption.

#### `policy_pages` — Privacy / Terms / Disclaimers

Same idea — fill in the actual Spanish slugs if the site has translated versions, and adjust the canonical names if needed:

```php
array(
    'key'      => 'privacy_policy',
    'slugs'    => array( 'en' => 'privacy-policy', 'es' => 'politica-de-privacidad' ),
    'name'     => array( 'en' => 'Privacy Policy', 'es' => 'Política de Privacidad' ),
    'fragment' => 'privacy-policy',
),
```

If a particular policy page doesn't exist in Spanish, set its `'es'` slug to an empty string `''` — the router will simply skip it for Spanish pages.

#### `enabled_schemas` — turn handlers on

This is the master switch. **Nothing happens for a schema type until you flip it to `true` here.** For the Austin Bankruptcy setup:

```php
'enabled_schemas' => array(
    'blog_posting'   => true,
    'attorney'       => false,   // Not built yet
    'practice_area'  => false,   // Not built yet
    'contact_page'   => true,
    'about_page'     => true,
    'testimonials'   => true,
    'video_library'  => true,    // Only if the page has the ACF repeater configured
    'blog_index'     => true,
    'faq_page'       => false,   // Not built yet
    'policy_pages'   => true,
),
```

#### Video Library — ACF setup (only if enabling)

If you're enabling `video_library`, the page also needs an ACF repeater field. Default field name is `videos`, with subfields `title`, `url`, `description`, `thumbnail`, `upload_date`, `duration`. If the site already has different ACF field names, override them in:

```php
'video_library' => array(
    'slugs' => array( 'en' => 'video-library', 'es' => 'biblioteca-de-videos' ),
    'acf'   => array(
        'repeater'    => 'videos',          // Change to match the ACF field name on the page
        'title'       => 'title',
        'url'         => 'url',
        'description' => 'description',
        'thumbnail'   => 'thumbnail',
        'upload_date' => 'upload_date',
        'duration'    => 'duration',
    ),
),
```

If video library doesn't have an ACF repeater set up yet, leave `video_library` set to `false` in `enabled_schemas` — turning it on without the ACF source makes the handler emit a `CollectionPage` with no items, which is harmless but pointless.

### Step 3.5 — Save the file

1. Click **Save** in the DirectAdmin editor.
2. Do NOT click anything that says "Save As" — that creates a renamed copy and leaves the original unchanged.

---

## Part 4 — Clear ALL Caches (Mandatory)

**Caching is the #1 reason new schema doesn't appear after deploy.** Do not skip this.

### 4.1 — WordPress page cache

Depending on what's installed:

- **WP Rocket** — top admin bar → **WP Rocket → Clear Cache** (or **Purge Cache** in newer versions)
- **W3 Total Cache** — **Performance → Dashboard → Empty All Caches**
- **LiteSpeed Cache** — **LiteSpeed Cache → Purge All**
- **WP Super Cache** — **Settings → WP Super Cache → Delete Cache**

### 4.2 — CDN

If Cloudflare is in front of the site:

1. Log in to Cloudflare.
2. Select the domain.
3. **Caching → Configuration → Purge Everything**.
4. Confirm.

### 4.3 — Browser

Test in **Incognito / Private** mode. Browser cache can hold the old (schema-less) HTML for hours otherwise.

---

## Part 5 — Verify the Schema is Live

### 5.1 — View Source

1. Open the configured page in incognito (e.g., `https://www.example.com/about-us/`).
2. Ctrl+U to view source.
3. Ctrl+F → search for `"@type":"AboutPage"`.

You should see a `<script type="application/ld+json">` block with the AboutPage entity. If not:
- Cache wasn't cleared. Clear it again.
- The slug in `site-config.php` doesn't match the actual page slug.
- The schema type isn't enabled in `enabled_schemas`.
- The plugin isn't activated. Re-check in **Plugins → Installed Plugins**.

### 5.2 — Run both validators

For **every** page type you enabled, run one example page through BOTH:

1. **Schema Markup Validator** — https://validator.schema.org/
   - Paste the URL → **Run Test**.
   - Expect: All entities parsed, zero errors.
   - If you see "undefined @id reference" for `#organization`, the sitewide schema isn't running — see Prerequisite check above.

2. **Google Rich Results Test** — https://search.google.com/test/rich-results
   - Paste the URL → **Test URL**.
   - Expect: Schema type detected, zero errors.
   - For policy pages (Privacy / Terms / Disclaimers), this validator will report "not eligible for rich results" — that's expected, not an error.

Run validators for at minimum:
- One blog post (English) → expect `BlogPosting` + `Person` + `BreadcrumbList`
- One blog post (Spanish, e.g., `/es/blog/…`) → same, with `inLanguage: "es-US"` and breadcrumb root "Inicio"
- About page (both languages if bilingual)
- Contact page
- One policy page

---

## Part 6 — Updating the Plugin (New Version)

When a new plugin version is built (`a:\AIA\Development\plugin-builder-schema\`):

### Path A — Re-upload via WordPress admin (recommended)

1. Rebuild the zip (Part 1).
2. WP admin → **Plugins → Add New Plugin → Upload Plugin**.
3. Choose the new zip → **Install Now**.
4. WordPress will say "*A plugin with the same name is already installed*" and offer:
   - **Replace current with uploaded** ← click this.
5. The plugin updates without losing its activated state.
6. **Your `site-config.php` is preserved** because the WordPress upgrader replaces files in place — but **verify** by opening the file in DirectAdmin after the update and checking your settings are still there. If they were wiped (rare, depends on host), restore from a backup.
7. Clear all caches (Part 4).
8. Re-run validators (Part 5).

### Path B — Manual replace via DirectAdmin

Use only if WordPress admin upload is blocked.

1. In DirectAdmin File Manager, navigate to `wp-content/plugins/`.
2. **Back up `site-config.php`** first: open `firm-legal-schema-suite/config/site-config.php`, copy the contents, paste into a local text file. (Skip this and you risk losing your config.)
3. Rename the existing `firm-legal-schema-suite/` folder to `firm-legal-schema-suite-OLD/` (DirectAdmin: select folder → Rename).
4. Upload the new zip into `wp-content/plugins/` (DirectAdmin: Upload button).
5. Right-click the uploaded zip → **Extract**. This creates a fresh `firm-legal-schema-suite/` folder.
6. Open `firm-legal-schema-suite/config/site-config.php`, paste back your config from step 2, save.
7. Delete the old folder `firm-legal-schema-suite-OLD/`.
8. Delete the uploaded zip.
9. Clear all caches.
10. Re-run validators.

---

## Part 7 — Editing Just the Config (No Plugin Update Needed)

When you only need to change site-specific values (e.g., enable an additional schema type, fix a Spanish slug):

1. DirectAdmin → File Manager.
2. Navigate to `wp-content/plugins/firm-legal-schema-suite/config/site-config.php`.
3. **Edit** the file inline.
4. Save.
5. Clear all caches (Part 4).
6. Re-run validators on the affected page(s).

No plugin re-upload required. No WP admin work required.

---

## Part 8 — Rollback (If Something Breaks)

If the site shows PHP errors or breaks after deploy:

### Via WP admin (preferred)

1. **Plugins → Installed Plugins**.
2. Find "Law Firm Legal Schema Suite".
3. Click **Deactivate**.
4. Site returns to its pre-plugin state immediately.

### Via DirectAdmin (when WP admin is also broken)

1. DirectAdmin → File Manager → `wp-content/plugins/`.
2. Rename `firm-legal-schema-suite/` to `firm-legal-schema-suite-DISABLED/`.
3. WordPress auto-deactivates plugins whose folder it can't find. Next admin page load will succeed.

### Common breakage causes

- **PHP syntax error in `site-config.php`** — usually a missing comma after an array entry or an unbalanced quote. Open the file in DirectAdmin, look near the line number reported in the WordPress error message.
- **Sitewide schema missing** — plugin still loads but validators flag undefined `@id` references. Not a "breakage," just a validation failure.
- **Caching showing old content** — cache wasn't cleared. Re-purge everything in Part 4.

---

## Part 9 — Quick Reference: First-Time Deploy Checklist

For Austin Bankruptcy or any new site, in order:

- [ ] Confirm sitewide `#organization` schema is running (View Source on homepage).
- [ ] Zip the inner `firm-legal-schema-suite/` folder (Part 1).
- [ ] Upload zip via WP admin (Part 2).
- [ ] Activate the plugin.
- [ ] Open `wp-content/plugins/firm-legal-schema-suite/config/site-config.php` in DirectAdmin.
- [ ] Set `force_language` if single-language.
- [ ] Update Spanish slugs under `pages` to match the actual page slugs.
- [ ] Update `policy_pages` Spanish slugs (or set to `''` if no translated version).
- [ ] Flip the relevant `enabled_schemas` toggles to `true`.
- [ ] If enabling Video Library: confirm the ACF repeater field exists on the page and matches `acf.repeater`.
- [ ] Save the config file.
- [ ] Clear ALL caches (WP cache, Cloudflare, browser).
- [ ] View source on each enabled page type — confirm the `<script type="application/ld+json">` block is present.
- [ ] Run both validators (Schema Markup Validator + Google Rich Results Test) on one example page per schema type, in both languages where applicable.
- [ ] Record the deployment in `SITE-REGISTRY.md`.
