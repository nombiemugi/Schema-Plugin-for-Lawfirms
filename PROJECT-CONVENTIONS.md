# Project Conventions

The non-negotiable rules for all law firm schema work. Mirrors the conventions document in Claude Projects.

## Entity Type Conventions

### Publisher: Always LegalService

The firm is always represented as `LegalService`, never `Organization` or `LocalBusiness`.

**Why:** `LegalService` is a subclass of both `LocalBusiness` and `Service`, so it inherits all properties of both while being the most specific Schema.org type for legal practices. It signals to Google that this is specifically a legal service provider, which can influence local pack results and knowledge panels.

### Author: Person (Lightweight on Blog Posts)

On blog posts, the `Person` entity for the author should be MINIMAL:
- `@type`, `@id`, `name`, `url`, `worksFor` only

Full attorney details (bio, credentials, awards, `sameAs`) live ONLY on the attorney's dedicated profile page. This avoids:
- Bloated JSON-LD on every blog post
- Inconsistency when attorney details change (update one place, not 500 blog posts)
- Duplicate content signals

### Attorney Profile: Person with Legal Properties

Attorney profile pages use `Person` (not the deprecated `Attorney` type) with rich legal-specific properties: `knowsAbout`, `memberOf`, `alumniOf`, `hasCredential`, `sameAs`.

### Practice Area: LegalService with serviceType

Each practice area page is its own `LegalService` entity with:
- `name` matching the practice area
- `serviceType` (e.g., "Family Law", "Personal Injury")
- `provider` referencing `#organization`
- `areaServed` listing geographic coverage

This creates a hierarchy: the firm is the parent `LegalService`, each practice area is a child `LegalService` provided by the firm.

## ID Pattern Conventions

All `@id` values follow these patterns:

| Entity | Pattern | Example |
|---|---|---|
| Organization | `{home}/#organization` | `https://example.com/#organization` |
| Website | `{home}/#website` | `https://example.com/#website` |
| Logo | `{home}/#logo` | `https://example.com/#logo` |
| Attorney | `{home}/#attorney-firstname-lastname` | `https://example.com/#attorney-jane-doe` |
| Page-level entities | `{permalink}#{type}` | `https://example.com/blog/post/#blogposting` |
| Breadcrumb | `{permalink}#breadcrumb` | `https://example.com/blog/post/#breadcrumb` |

**Critical:** The attorney `@id` derived from a blog post's author MUST match the `@id` on the attorney's profile page. This is entity continuity. If author "Jane Doe" appears on the blog as `#attorney-jane-doe`, her profile page must declare the `Person` entity with the same `@id`.

Author slug generation:
```php
$slug = sanitize_title( remove_accents( $author_name ) );
// "José García" → "jose-garcia"
// "Mary O'Neill" → "mary-oneill"
```

## Language Conventions

### Always Use Full Locale Codes
- `en-US` for English (US)
- `es-US` for Spanish (US)

Never use bare `en` or `es`. Google and Schema.org prefer the full locale.

### Language Detection Priority

1. Per-site `$force_language` config (overrides everything)
2. Polylang `pll_current_language()`
3. WPML `ICL_LANGUAGE_CODE` constant
4. URL pattern (default `/es/` marker)
5. Default `en-US`

### Breadcrumb Localization
- English: "Home"
- Spanish: "Inicio"

Other breadcrumb labels (category names, post titles) come from the post itself in its native language.

### Never Mix Languages in One Entity

A Spanish post's `BlogPosting`:
- `headline` in Spanish
- `description` in Spanish
- `inLanguage` = `es-US`
- Breadcrumb root = "Inicio"
- Category name = whatever the category's Spanish translation is

Don't have "Inicio > Family Law > Article in Spanish" — that's mixed languages.

## Architectural Conventions

### Sitewide Entities Are Sacred

`#organization`, `#website`, and `#logo` are defined ONCE per site, output on EVERY page. Page-level plugins ONLY reference them via `@id`.

Never redefine these on individual pages. The whole point of `@id` is that the entity is declared once and reused. Multiple `LegalService` declarations with the same `@id` on different pages is technically valid but signals inconsistency to crawlers.

### Page-Specific Entities Use Permalink-Anchored IDs

`BlogPosting`, `BreadcrumbList`, `FAQPage` etc. use `{permalink}#{type}` because they're specific to that page. The page URL acts as the namespace.

### One Plugin, Many Schemas

Avoid building separate plugins for each schema type. The modular architecture (router + handlers) allows one plugin to handle all page types on a site. This:
- Reduces plugin count (fewer activation/update overhead)
- Centralizes per-site config
- Makes auditing easier
- Simplifies version control

## Code Style Conventions

### Naming
- Plugin name: `firm-legal-schema-suite`
- Class prefix: `Firm_Legal_*`
- Function prefix: `firm_legal_*` (only when not in a class)
- Hook prefix: `firm_legal_*`
- Filter/action names: `firm_legal_before_schema_output`, `firm_legal_after_schema_output`

### Formatting
- WordPress coding standards
- 4-space indentation
- Yoda conditionals where applicable
- Spaces inside parentheses: `if ( $var ) {`
- No closing `?>` tag at end of PHP files

### Comments
- Plugin header at the top with full metadata
- Class-level docblock explaining purpose
- Method docblock with `@param` and `@return`
- Inline comments for non-obvious logic only
- All comments in English (even on Spanish sites)

### JSON Output Flags
Always use these flags with `wp_json_encode()`:
```php
JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
```

- `UNESCAPED_SLASHES` — URLs don't get escaped (`/` instead of `\/`)
- `UNESCAPED_UNICODE` — Spanish characters (ñ, á, é, í, ó, ú) display correctly
- `PRETTY_PRINT` — Human-readable JSON for debugging via View Source

## Validation Conventions

### Both Validators, Every Deployment
- Schema Markup Validator first (spec compliance)
- Google Rich Results Test second (Google eligibility)
- Both must be clean before marking complete

### Test One Page Per Schema Type
After adding a new schema (e.g., Attorney), test ONE attorney page in both validators before considering it done.

### Multilingual Sites: Test Both Languages
Test one English page and one Spanish page (if applicable). Verify `inLanguage` and breadcrumb root match.

## What We Don't Do

These are explicit anti-patterns:

### Don't fabricate trust signals
- No invented `AggregateRating` if there are no real reviews
- No fake `Review` entities
- No imagined awards or certifications
- If the user requests these without data, REFUSE and explain

### Don't use deprecated types
- Not `Attorney` (use `Person` with legal properties)
- Not `Service` alone (use `LegalService`)
- Not `LocalBusiness` alone (use `LegalService` which inherits it)

### Don't mix sitewide and page-level concerns
- Sitewide schema is for the firm/website/logo. Period.
- Page-level schema is for content on that specific page.
- Don't redefine `#organization` in the BlogPosting handler.

### Don't ignore caching
- Every deployment requires a cache purge step
- If schema "isn't working" after install, caching is the first suspect

### Don't deploy without validation
- Both validators must be clean
- "Looks right in View Source" is not validation
- "I tested it on one page" is partial validation — test edge cases too

## Site-Specific Decisions Document

For each client site, maintain a record of:
- Active theme name (parent + child)
- Caching plugin(s) in use
- Multilingual setup (Polylang/WPML/URL/single-language)
- ACF field names for author overrides (if applicable)
- Custom post types and their slugs (attorney, practice_area, case_result, etc.)
- Page slugs for contact, about, FAQ
- Sitewide schema source (which plugin emits `#organization`?)
- Validator results per schema type
- Any deviations from these conventions (and why)

Track these in the project's `SITE-REGISTRY.md` file.
