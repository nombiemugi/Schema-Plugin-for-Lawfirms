# Law Firm Schema Plugin Suite — Setup Guide

This bundle contains everything you need to set up a Claude Project and a Claude Skill for building WordPress schema plugins for law firm sites.

## What's in This Bundle

```
deliverables/
├── law-firm-schema-plugin-builder.zip   ← The skill (upload to Claude)
├── firm-legal-schema-suite.zip          ← The refactored modular plugin (deploy to WordPress)
└── project-files/                       ← Files to upload to your Claude Project
    ├── PROJECT-INSTRUCTIONS.md          ← Paste into Claude Project "Instructions"
    ├── PROJECT-CONVENTIONS.md           ← Upload as project knowledge
    └── SITE-REGISTRY.md                 ← Upload as project knowledge (edit as you add sites)
```

## Setup — Three Steps

### Step 1 — Install the Skill

1. Download `law-firm-schema-plugin-builder.zip`
2. Go to Claude Settings → Capabilities → Skills
3. Click "Upload skill" and select the zip file
4. The skill is now active across all your chats — it triggers automatically for law firm schema requests

The skill encodes:
- Project conventions (LegalService publisher, @id patterns, language handling)
- Plugin architecture (router + handlers + config)
- Schema type templates (BlogPosting, Attorney, LegalService, ContactPage, AboutPage, FAQPage, Location)
- Deployment and validation workflows
- Common pitfalls and fixes

### Step 2 — Create the Claude Project

1. In Claude, go to Projects → Create New Project
2. Name it: "Law Firm Schema Plugin Suite"
3. Copy the entire contents of `project-files/PROJECT-INSTRUCTIONS.md` into the project's "Custom Instructions" field
4. Upload these files as project knowledge:
   - `PROJECT-CONVENTIONS.md`
   - `SITE-REGISTRY.md`
   - The full plugin codebase (or the `firm-legal-schema-suite.zip` for reference)
   - Any client-specific configs as you build them
5. Save

Now every chat in this project automatically loads the conventions and site registry, and the skill triggers for schema work.

### Step 3 — Deploy the Plugin to a Site

The `firm-legal-schema-suite.zip` is ready to upload to any WordPress law firm site:

1. Verify the sitewide `#organization` schema is running on the site (View Source → search for `"@type":"LegalService"` with `@id` `#organization`)
2. WordPress admin → Plugins → Add New → Upload Plugin
3. Select `firm-legal-schema-suite.zip` → Install Now → Activate Plugin
4. Edit `config/site-config.php` via FTP, cPanel, or Plugin File Editor:
   - Set `force_language` if it's a single-language site
   - Update `acf_author_name_field` / `acf_author_url_field` if the site uses different field names
   - Update post type slugs and page slugs when adding more schema types later
   - To credit every blog post to one person, fill in the `blog_author` block — see [Setting the Fixed Blog Author](#setting-the-fixed-blog-author-exact-profile-url) below
5. Clear ALL caches (WP Rocket, Cloudflare, browser)
6. Test a blog post URL in:
   - https://search.google.com/test/rich-results
   - https://validator.schema.org/
7. Document the deployment in `SITE-REGISTRY.md`

### Setting the Fixed Blog Author (exact profile URL)

Use this to attribute **every** blog post to one person (the managing attorney, the business owner, the sole blog author) instead of the per-post WordPress/ACF author. The BlogPosting handler never changes between sites — only config does, which is what keeps it reusable.

> **Note:** older versions of these docs described a `managing_attorney` block. That key is **inert** — the plugin has always read `blog_author`. If a deployed config still sets `managing_attorney`, its attribution is not being applied.

1. Open the person's **dedicated profile page** in a browser and copy its full, exact URL — e.g. `https://example.com/meet-the-team/jane-doe/`. Use the real profile page, **not** the WordPress author archive (`/author/...`).
2. **View source on that page** and find the `Person` in its JSON-LD. Copy its `@id` verbatim — you need it in step 4. This is the step people skip, and skipping it is why authors silently split into two entities.
3. In `config/site-config.php`, fill in the `blog_author` block:
   ```php
   'blog_author' => array(
       'name' => 'Jane Doe',
       'url'  => 'https://example.com/meet-the-team/jane-doe/',
   ),
   ```
   - `name` is **required** to enable the override.
   - `url` is the exact profile-page URL from step 1 — it becomes the author `Person`'s `url`.
4. Set `person_id` to the preset that reproduces the `@id` from step 2 **character for character**:
   ```php
   // Preset A — law-firm convention: https://example.com/#attorney-jane-doe
   'person_id' => array( 'base' => 'home', 'fragment' => 'attorney', 'append_slug' => true ),

   // Preset B — anchored to the person's own bio page:
   //            https://example.com/meet-the-team/jane-doe/#person
   'person_id' => array( 'base' => 'author_url', 'fragment' => 'person', 'append_slug' => false ),

   // Preset C — profile at a fixed URL the plugin can't derive:
   'person_id' => array( 'base' => 'https://example.com/team/jane/', 'fragment' => 'person', 'append_slug' => false ),
   ```
   A mismatch here passes **both validators cleanly** while Google reads two unrelated people. Nothing in the output looks wrong, so verify it by eye against step 2.
5. Under preset A the `@id` derives from `name`, so spell it identically in both places (the slug is `sanitize_title( remove_accents( name ) )`). Under presets B and C the `@id` derives from the **URL**, so `name` doesn't affect it — meaning a wrong `name` yields a correct-looking `@id` attached to the wrong person.
6. To switch back to per-post WordPress/ACF authors, set `name` to an empty string (`''`); `url` is then ignored.
7. **Reuse on another site:** copy the plugin, then edit only `blog_author` + `person_id` for that site. No handler edits — ever.
8. Clear ALL caches (WP Rocket, Cloudflare, browser) and re-run both validators on an English and (if bilingual) a Spanish post.

The resulting author block on every blog post, using preset B:

```json
{
  "@type": "Person",
  "@id": "https://example.com/meet-the-team/jane-doe/#person",
  "name": "Jane Doe",
  "url": "https://example.com/meet-the-team/jane-doe/",
  "worksFor": { "@id": "https://example.com/#organization" }
}
```

## How to Use This Project Going Forward

### Adding a New Client Site

1. Open the project in Claude
2. Start a chat: "I'm deploying to a new client site, [URL]"
3. Claude will walk you through the pre-deployment checklist (theme, caching, multilingual, ACF, post types)
4. Once configured, Claude generates the per-site `site-config.php`
5. Deploy following the standard workflow
6. Update `SITE-REGISTRY.md`

### Adding a New Schema Type

When you want to add Attorney, Practice Area, FAQ, etc.:

1. Open the project in Claude
2. Say: "Add Attorney schema to the plugin"
3. Claude will ask about the site's attorney post type, content sources, and any ACF fields
4. Generates the new handler file (`includes/handlers/class-attorney.php`)
5. Updates the router, site-config, and main plugin file
6. Bumps the version number
7. Repackages the plugin for deployment

### Debugging a Live Site

If a deployment isn't working:

1. Open the project in Claude
2. Describe the symptom (schema not appearing, validator errors, etc.)
3. Claude follows the troubleshooting workflow in the skill:
   - Cache check
   - Theme check
   - Activation check
   - Prerequisites check
   - Conditional check
4. Walks you through the fix

## Tips for Best Results

- **Be specific about which site you're working on.** The site registry has different configs per site.
- **Don't ask Claude to "just guess" the site setup.** When in doubt, Claude will ask — answer the questions for accurate output.
- **Validate after every change.** Both validators, every time.
- **Keep `SITE-REGISTRY.md` up to date.** It's the source of truth for which sites have which versions.
- **Version bump on every plugin change.** Even small fixes — bumping `1.0.0` to `1.0.1` makes update tracking easier.

## Future Work

The plugin's modular architecture supports these schemas, ready to be built:

- Attorney (Person with legal properties)
- Practice Area (LegalService with serviceType)
- Contact Page (ContactPage)
- About Page (AboutPage)
- FAQ Page (FAQPage with Question/Answer pairs)
- Location (LegalService for multi-office firms)
- Case Result (Article variant)

Each is a new file in `includes/handlers/`, plus a new branch in the router and a toggle in site-config.

## Questions?

When starting a new chat in this project, the skill and project conventions are both loaded automatically. Just describe what you need:

- "Add Attorney schema for [client]"
- "Why is the schema not showing on [site URL]?"
- "Deploy the plugin to a new client"
- "Audit the existing schema on [site URL]"

Claude will know the conventions, the architecture, and the per-site configs — and ask clarifying questions where they matter.
