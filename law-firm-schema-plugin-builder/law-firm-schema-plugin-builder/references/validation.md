# Validation Reference

How to interpret schema validator results and fix common errors.

## The Two Validators (Use Both)

### Google Rich Results Test
**URL:** https://search.google.com/test/rich-results

**What it tests:** Whether the schema qualifies for Google rich results (carousels, featured snippets, knowledge panels, etc.). Only validates schema types Google supports for rich results.

**Strengths:**
- Shows exactly what Google sees
- Identifies schema types eligible for rich results
- Highlights field-specific errors with line numbers
- Tests live URLs and renders any JavaScript

**Limitations:**
- Doesn't validate types Google doesn't use (e.g., it may ignore `AboutPage`, `ContactPage`)
- Doesn't catch all Schema.org spec violations
- May mark valid schema as "non-eligible" simply because Google doesn't use it

### Schema Markup Validator
**URL:** https://validator.schema.org/

**What it tests:** Full Schema.org spec compliance. Catches everything Rich Results misses.

**Strengths:**
- Catches type mismatches (e.g., using a string where a `Date` is required)
- Validates ALL Schema.org types, not just rich-results-eligible ones
- Shows the full parsed JSON-LD structure
- Flags unrecognized properties

**Limitations:**
- Doesn't tell you if Google will use the schema for rich results
- Less actionable error messages

### Workflow: Use Both, In This Order

1. **Schema Markup Validator first** — verify spec correctness
2. **Google Rich Results Test second** — verify Google-specific eligibility
3. Both should be clean before marking deployment complete

## Common Errors and Fixes

### "Undefined @id reference: https://www.example.com/#organization"

**Cause:** Page-level schema references `#organization` via `@id`, but no entity with that `@id` exists on the page.

**Fix:** The sitewide schema isn't running. Verify:
1. View Source on the same page
2. Search for `"@id":"https://www.example.com/#organization"`
3. If not found, the sitewide schema plugin/snippet is missing or disabled

**Don't fix by:** Inlining a full `LegalService` definition in the page-level schema. That defeats the architecture and creates duplicate entities.

### "The value provided for headline must be a string but is null"

**Cause:** A required string field is empty.

**Fix:** Check the source. For BlogPosting headline:
- Is `get_the_title()` returning empty? (Untitled post in WordPress)
- Is there a typo in the function call?
- Use `wp_strip_all_tags()` to remove any HTML before output

### "Missing field 'image' in BlogPosting"

**Cause:** No featured image on the post.

**Fix:** Two options:
1. **Per-post fix:** Add a featured image in WordPress
2. **Plugin fix:** Use a default fallback image:
   ```php
   $image = get_the_post_thumbnail_url( $post_id, 'full' );
   if ( ! $image ) {
       $image = $this->config['default_image_url']; // sitewide default
   }
   ```

Note: Per current conventions, we OMIT the image field if there's no featured image (cleaner). But Google sometimes flags BlogPosting without image — discuss with the user before adding a default.

### "datePublished is in an invalid format"

**Cause:** Date isn't in ISO 8601 format with timezone.

**Fix:** Use `get_the_date( 'c', $post_id )` — the `c` format is ISO 8601. Don't use `date()` or `get_the_date( 'Y-m-d' )` — those are missing the time and timezone.

### "Property author should be an instance of Person, Organization"

**Cause:** Author is referenced via `@id` but the actual `Person` entity isn't on the page.

**Fix:** Ensure the `Person` entity is included in the same `@graph` as the `BlogPosting`. The `@id` reference must resolve within the same JSON-LD block.

### "Property knowsAbout expected a Thing or Text"

**Cause:** `knowsAbout` should be either string array or `Thing` array, not mixed.

**Fix:** Pick one format and stick with it:
```php
// Strings (simpler)
"knowsAbout" => array( "Family Law", "Divorce", "Child Custody" )

// OR Thing references (more structured)
"knowsAbout" => array(
    array( "@type" => "Thing", "name" => "Family Law" ),
    array( "@type" => "Thing", "name" => "Divorce" )
)
```

### "Duplicate field telephone"

**Cause:** The JSON has two `telephone` keys at the same level.

**Fix:** Look for PHP array duplication. Often happens when an array_merge combines arrays with overlapping keys.

### "Schema type not recognized: Attorney"

**Cause:** `Attorney` is deprecated as a Schema.org type — it's now an alias for `LegalService`.

**Fix:** Use `Person` with rich legal properties:
```php
array(
    "@type" => "Person",
    "additionalType" => "https://schema.org/Attorney",  // optional, signals the role
    "jobTitle" => "Attorney",
    "knowsAbout" => array(...),
    // ...
)
```

### "Value should be a valid URL"

**Cause:** A URL field has a malformed URL — often missing protocol, or has Markdown link syntax.

**Fix:** Verify URL is fully qualified (`https://...`), no extra characters. The most common cause: someone wrapping a URL in Markdown link syntax like `[https://example.com](https://example.com)`. Strip that immediately.

### "BreadcrumbList items must be in order"

**Cause:** `position` values don't increment correctly (skipped position, or unordered).

**Fix:** Verify positions are 1, 2, 3, ... with no gaps. When a post has no category, the post itself goes at position 2 (not 3).

## Validator-Specific Quirks

### Rich Results Test Quirks

- **Caches results aggressively.** If you fix an error and re-test the same URL, results may be 5–10 minutes stale. Add `?cachebust=1` to the URL to force a fresh fetch.
- **Renders JavaScript.** Schema added via JS will appear here but NOT in Schema Markup Validator (which doesn't render JS).
- **Strict about image dimensions.** Logo images should be at least 112x112 px for some rich result types.

### Schema Markup Validator Quirks

- **Doesn't render JavaScript.** If schema is injected client-side, this validator won't see it.
- **Strict about types.** Even `Attorney` (which Google ignores quietly) gets flagged here.
- **Treats warnings as low-priority.** Pay more attention to errors than warnings.

## Interpreting Warnings vs Errors

**Errors:** Must be fixed. The schema won't work as intended.

**Warnings:** Should be reviewed but may be acceptable. Common warning types:
- "Recommended field missing" → consider adding if the data exists
- "Type may not be eligible for rich results" → may be intentional (non-rich-result schema is still valuable)
- "Property X is deprecated" → replace with the current preferred property

If you're not sure whether a warning matters, ask the user. Don't auto-fix warnings that might require content changes.

## Manual Verification (When Validators Conflict)

If validators give conflicting results:

1. **Check the raw JSON-LD output.** View Source, find the `<script type="application/ld+json">` block, copy the JSON. Run it through https://jsonlint.com to verify valid JSON.

2. **Test the JSON directly.** Both validators accept "Code" input — paste the JSON instead of a URL to isolate JSON issues from rendering issues.

3. **Check for invisible characters.** Sometimes BOM or zero-width spaces break parsing. View the raw response with `curl -s URL | head -100` and look for anomalies before the script tag.

4. **Verify Schema.org documentation.** When in doubt, check https://schema.org/[TypeName] for the canonical field definitions.
