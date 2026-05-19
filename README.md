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
5. Clear ALL caches (WP Rocket, Cloudflare, browser)
6. Test a blog post URL in:
   - https://search.google.com/test/rich-results
   - https://validator.schema.org/
7. Document the deployment in `SITE-REGISTRY.md`

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
