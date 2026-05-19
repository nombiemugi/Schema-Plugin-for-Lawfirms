# Schema Types Reference

JSON-LD examples and field requirements for each law firm page type.

## BlogPosting

For individual blog posts and articles.

### Required + Recommended Structure

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Person",
      "@id": "https://www.example.com/#attorney-jane-doe",
      "name": "Jane Doe",
      "url": "https://www.example.com/attorneys/jane-doe/",
      "worksFor": { "@id": "https://www.example.com/#organization" }
    },
    {
      "@type": "BlogPosting",
      "@id": "https://www.example.com/blog/post-slug/#blogposting",
      "headline": "Post Title",
      "description": "Post excerpt or meta description",
      "image": "https://www.example.com/wp-content/uploads/featured.jpg",
      "datePublished": "2025-01-15T10:00:00-05:00",
      "dateModified": "2025-01-20T14:30:00-05:00",
      "inLanguage": "en-US",
      "author": { "@id": "https://www.example.com/#attorney-jane-doe" },
      "publisher": { "@id": "https://www.example.com/#organization" },
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://www.example.com/blog/post-slug/"
      },
      "keywords": "child support, family law, Virginia"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://www.example.com/blog/post-slug/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.example.com/" },
        { "@type": "ListItem", "position": 2, "name": "Blog", "item": "https://www.example.com/blog/" },
        { "@type": "ListItem", "position": 3, "name": "Post Title", "item": "https://www.example.com/blog/post-slug/" }
      ]
    }
  ]
}
```

## Attorney (Person subtype)

For attorney bio pages. Note: Schema.org has `Attorney` as a deprecated alias for `LegalService`, so we use `Person` with rich legal-specific properties.

### Structure

```json
{
  "@type": "Person",
  "@id": "https://www.example.com/#attorney-jane-doe",
  "name": "Jane Doe",
  "honorificSuffix": "Esq.",
  "jobTitle": "Senior Partner",
  "image": "https://www.example.com/wp-content/uploads/jane-doe.jpg",
  "url": "https://www.example.com/attorneys/jane-doe/",
  "telephone": "+1-555-123-4567",
  "email": "jane@example.com",
  "description": "Jane Doe is a senior partner specializing in family law...",
  "worksFor": { "@id": "https://www.example.com/#organization" },
  "knowsAbout": [
    "Family Law",
    "Divorce",
    "Child Custody",
    "Child Support"
  ],
  "alumniOf": [
    {
      "@type": "CollegeOrUniversity",
      "name": "Harvard Law School"
    }
  ],
  "memberOf": [
    {
      "@type": "Organization",
      "name": "Virginia State Bar"
    }
  ],
  "hasCredential": [
    {
      "@type": "EducationalOccupationalCredential",
      "credentialCategory": "license",
      "name": "Virginia Bar Admission",
      "dateCreated": "2010"
    }
  ],
  "sameAs": [
    "https://www.linkedin.com/in/janedoe",
    "https://www.avvo.com/attorneys/jane-doe.html"
  ]
}
```

### Field Notes
- `knowsAbout` should match practice areas the attorney handles
- `sameAs` should only include verified profiles (LinkedIn, Avvo, Justia, Martindale)
- Don't fabricate awards, certifications, or memberships — only include verified data
- `image` should be a professional headshot URL, full-size

## LegalService (Practice Area)

For practice area pages (Family Law, Personal Injury, etc.).

### Structure

```json
{
  "@type": "LegalService",
  "@id": "https://www.example.com/practice-areas/family-law/#legalservice",
  "name": "Family Law Services",
  "description": "Comprehensive family law representation including divorce, custody, and support matters.",
  "url": "https://www.example.com/practice-areas/family-law/",
  "provider": { "@id": "https://www.example.com/#organization" },
  "areaServed": [
    {
      "@type": "State",
      "name": "Virginia"
    },
    {
      "@type": "City",
      "name": "Arlington"
    }
  ],
  "serviceType": "Family Law",
  "audience": {
    "@type": "Audience",
    "audienceType": "Individuals seeking family law representation"
  }
}
```

### Field Notes
- Use `provider` (not `publisher`) for services
- `serviceType` should be a recognized practice area name
- `areaServed` should list geographic service areas
- Avoid `hasOfferCatalog` unless there's actual pricing — empty offers harm validation

## ContactPage

For the firm's contact page.

### Structure

```json
{
  "@type": "ContactPage",
  "@id": "https://www.example.com/contact/#contactpage",
  "url": "https://www.example.com/contact/",
  "name": "Contact Our Firm",
  "description": "Get in touch with our legal team for a free consultation.",
  "inLanguage": "en-US",
  "isPartOf": { "@id": "https://www.example.com/#website" },
  "mainEntity": { "@id": "https://www.example.com/#organization" },
  "breadcrumb": { "@id": "https://www.example.com/contact/#breadcrumb" }
}
```

## AboutPage

For the firm's about page.

### Structure

```json
{
  "@type": "AboutPage",
  "@id": "https://www.example.com/about/#aboutpage",
  "url": "https://www.example.com/about/",
  "name": "About Our Firm",
  "description": "Learn about our firm's history, values, and commitment to clients.",
  "inLanguage": "en-US",
  "isPartOf": { "@id": "https://www.example.com/#website" },
  "mainEntity": { "@id": "https://www.example.com/#organization" }
}
```

## FAQPage

For dedicated FAQ pages. Each Q&A becomes a `Question` entity.

### Structure

```json
{
  "@type": "FAQPage",
  "@id": "https://www.example.com/faq/#faqpage",
  "url": "https://www.example.com/faq/",
  "inLanguage": "en-US",
  "isPartOf": { "@id": "https://www.example.com/#website" },
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How much does a consultation cost?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Initial consultations are free of charge and last approximately 30 minutes."
      }
    },
    {
      "@type": "Question",
      "name": "Do you handle cases outside Virginia?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Our practice is limited to Virginia state and federal courts."
      }
    }
  ]
}
```

### Field Notes
- Only use `FAQPage` on pages that ARE genuinely FAQ pages — not blog posts with one Q&A
- Each `Question` must have exactly one `acceptedAnswer`
- Don't use `FAQPage` for client-specific or paid-only content
- Each answer should be a complete, useful response

## Location (Multi-Office Firms)

For individual office location pages. Uses `LegalService` since law firms inherit from `LocalBusiness`.

### Structure

```json
{
  "@type": "LegalService",
  "@id": "https://www.example.com/offices/arlington/#location",
  "name": "Example Law Firm — Arlington Office",
  "url": "https://www.example.com/offices/arlington/",
  "image": "https://www.example.com/wp-content/uploads/arlington-office.jpg",
  "telephone": "+1-703-555-1234",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "1234 Example Boulevard, Suite 500",
    "addressLocality": "Arlington",
    "addressRegion": "VA",
    "postalCode": "22201",
    "addressCountry": "US"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 38.880,
    "longitude": -77.108
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "09:00",
      "closes": "17:00"
    }
  ],
  "parentOrganization": { "@id": "https://www.example.com/#organization" },
  "areaServed": {
    "@type": "City",
    "name": "Arlington"
  }
}
```

### Field Notes
- Multi-office firms should output sitewide `#organization` as the main `LegalService`, then individual office locations as separate `LegalService` entities with `parentOrganization` pointing to the main one
- `geo` coordinates should be from Google Maps for accuracy
- `telephone` must be in E.164 format (`+1-555-123-4567` or `+15551234567`)

## CollectionPage (Practice Areas Index)

For the top-level `/practice-areas/` index page that lists every service the firm offers. Pairs `CollectionPage` with an `ItemList` of `Service` entities.

### Structure

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "@id": "https://www.example.com/practice-areas/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.example.com/" },
        { "@type": "ListItem", "position": 2, "name": "Practice Areas", "item": "https://www.example.com/practice-areas/" }
      ]
    },
    {
      "@type": "CollectionPage",
      "@id": "https://www.example.com/practice-areas/#webpage",
      "url": "https://www.example.com/practice-areas/",
      "name": "Practice Areas | Example Law Firm",
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "about": { "@id": "https://www.example.com/#organization" },
      "mainEntity": { "@id": "https://www.example.com/practice-areas/#practice-area-list" },
      "breadcrumb": { "@id": "https://www.example.com/practice-areas/#breadcrumb" },
      "inLanguage": "en-US"
    },
    {
      "@type": "ItemList",
      "@id": "https://www.example.com/practice-areas/#practice-area-list",
      "name": "Practice Areas",
      "itemListOrder": "https://schema.org/ItemListOrderAscending",
      "numberOfItems": 3,
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@type": "Service",
            "@id": "https://www.example.com/practice-areas/appeals/#service",
            "name": "Appeals",
            "description": "Short, plain-language description of the service.",
            "url": "https://www.example.com/practice-areas/appeals/",
            "provider": { "@id": "https://www.example.com/#organization" },
            "areaServed": { "@type": "City", "name": "Los Angeles" }
          }
        }
      ]
    }
  ]
}
```

### Field Notes
- Use `Service` (not `LegalService`) on the child items in the `ItemList` — `Service` is the parent type, leaving room for non-litigation offerings (mediation, case assessment) that aren't strictly legal services.
- Each child `@id` should match the `@id` declared on that practice area's own detail page — entity continuity across the index and the detail page.
- `itemListOrder` is alphabetical here; switch to `ItemListUnordered` if alphabetical ordering doesn't apply.
- `numberOfItems` must match the actual count in `itemListElement`.

## Service / LegalService Detail + FAQPage Combo

For an individual practice area detail page (e.g., `/practice-areas/litigation/`). Combines a `Service` (the practice area itself), an optional `FAQPage` of common questions, and the breadcrumb in one graph.

### Structure

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "@id": "https://www.example.com/practice-areas/litigation/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.example.com/" },
        { "@type": "ListItem", "position": 2, "name": "Practice Areas", "item": "https://www.example.com/practice-areas/" },
        { "@type": "ListItem", "position": 3, "name": "Litigation", "item": "https://www.example.com/practice-areas/litigation/" }
      ]
    },
    {
      "@type": "WebPage",
      "@id": "https://www.example.com/practice-areas/litigation/#webpage",
      "url": "https://www.example.com/practice-areas/litigation/",
      "name": "Los Angeles Litigation Lawyer | Example Law Firm",
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "about": { "@id": "https://www.example.com/practice-areas/litigation/#service" },
      "mainEntity": { "@id": "https://www.example.com/practice-areas/litigation/#service" },
      "breadcrumb": { "@id": "https://www.example.com/practice-areas/litigation/#breadcrumb" },
      "inLanguage": "en-US"
    },
    {
      "@type": "Service",
      "@id": "https://www.example.com/practice-areas/litigation/#service",
      "name": "Litigation",
      "serviceType": "Civil Litigation",
      "url": "https://www.example.com/practice-areas/litigation/",
      "provider": { "@id": "https://www.example.com/#organization" },
      "areaServed": [
        { "@type": "City", "name": "Los Angeles" },
        { "@type": "State", "name": "California" }
      ]
    },
    {
      "@type": "FAQPage",
      "@id": "https://www.example.com/practice-areas/litigation/#faq",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "What does a litigation lawyer do?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Plain-language answer drawn from the page content."
          }
        }
      ]
    }
  ]
}
```

### Field Notes
- The `WebPage.about` and `WebPage.mainEntity` both point at the `Service` `@id` — this signals that the page is *about* the service entity.
- Only include the `FAQPage` node when the page actually contains a Q&A block — don't synthesize questions.
- Use `Service` for general practice areas and `LegalService` only when the offering is strictly a legal service. The two are compatible (`LegalService` is a `Service` subclass).

## ContactPage with ContactPoint

Extension of the basic `ContactPage` pattern — add a `ContactPoint` sub-entity when the page surfaces a phone number, dedicated channel, or language coverage that's specific to that contact route (vs. the firm's generic phone on `#organization`).

### Structure

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "ContactPage",
      "@id": "https://www.example.com/contact-us/#webpage",
      "url": "https://www.example.com/contact-us/",
      "name": "Contact Us | Example Law Firm",
      "description": "Request a free case evaluation.",
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "about": { "@id": "https://www.example.com/#organization" },
      "mainEntity": { "@id": "https://www.example.com/#organization" },
      "breadcrumb": { "@id": "https://www.example.com/contact-us/#breadcrumb" },
      "inLanguage": "en-US"
    },
    {
      "@type": "ContactPoint",
      "@id": "https://www.example.com/contact-us/#contactpoint",
      "contactType": "customer support",
      "telephone": "+1-213-232-1313",
      "url": "https://www.example.com/contact-us/",
      "availableLanguage": [
        { "@type": "Language", "name": "English" }
      ],
      "areaServed": { "@type": "City", "name": "Los Angeles" }
    }
  ]
}
```

### Field Notes
- `contactType` values from the Schema.org vocabulary: `"customer support"`, `"sales"`, `"billing support"`, `"technical support"`, `"emergency"`. For law firms, `"customer support"` covers prospective-client intake.
- `telephone` must be E.164 format (`+1-213-232-1313`).
- Add `availableLanguage` entries when the contact route is staffed for that language — don't claim bilingual support that doesn't exist.
- Omit the `ContactPoint` entirely if the page only restates the firm-wide phone on `#organization`.

## AboutPage with Attorney Mention

Extension of the basic `AboutPage` — use the `mentions` property to declare that the page references a specific attorney whose entity lives on their dedicated profile page. The `Person` entity is included in the same graph so the `@id` resolves on this page too.

### Structure

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "AboutPage",
      "@id": "https://www.example.com/about-us/#webpage",
      "url": "https://www.example.com/about-us/",
      "name": "About Us | Example Law Firm",
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "about": { "@id": "https://www.example.com/#organization" },
      "mainEntity": { "@id": "https://www.example.com/#organization" },
      "mentions": { "@id": "https://www.example.com/#attorney-jane-doe" },
      "breadcrumb": { "@id": "https://www.example.com/about-us/#breadcrumb" },
      "primaryImageOfPage": { "@id": "https://www.example.com/about-us/#primaryimage" },
      "inLanguage": "en-US"
    },
    {
      "@type": "ImageObject",
      "@id": "https://www.example.com/about-us/#primaryimage",
      "url": "https://www.example.com/wp-content/uploads/about-team.webp",
      "contentUrl": "https://www.example.com/wp-content/uploads/about-team.webp",
      "caption": "Jane Doe of Example Law Firm"
    },
    {
      "@type": "Person",
      "@id": "https://www.example.com/#attorney-jane-doe",
      "name": "Jane Doe",
      "jobTitle": "Attorney",
      "url": "https://www.example.com/about-us/",
      "image": { "@id": "https://www.example.com/about-us/#primaryimage" },
      "worksFor": { "@id": "https://www.example.com/#organization" },
      "sameAs": [
        "https://www.avvo.com/attorneys/jane-doe.html"
      ]
    }
  ]
}
```

### Field Notes
- The `Person`'s `@id` MUST match the canonical attorney `@id` used on the dedicated profile page and on any BlogPosting authorship — this is the entity-continuity rule.
- `primaryImageOfPage` and the `Person.image` can reference the same `ImageObject` when the About page's hero image is a portrait of the attorney.
- For multi-attorney firms, include each attorney as a separate `Person` node and pass an array to `mentions`: `"mentions": [ { "@id": "..." }, { "@id": "..." } ]`.

## Policy Pages (Privacy Policy / Terms of Use / Legal Disclaimers)

For standard legal/footer pages. Pattern: `WebPage` wrapping a `CreativeWork` as `mainEntity` (the policy document itself).

### Structure (Privacy Policy example — same shape for Terms of Use and Legal Disclaimers)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "@id": "https://www.example.com/privacy-policy/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://www.example.com/" },
        { "@type": "ListItem", "position": 2, "name": "Privacy Policy", "item": "https://www.example.com/privacy-policy/" }
      ]
    },
    {
      "@type": "WebPage",
      "@id": "https://www.example.com/privacy-policy/#webpage",
      "url": "https://www.example.com/privacy-policy/",
      "name": "Privacy Policy | Example Law Firm",
      "description": "Privacy practices for visitors to example.com, including cookies, ad remarketing, IP address handling, email information, data protection, policy changes, and children's privacy.",
      "isPartOf": { "@id": "https://www.example.com/#website" },
      "about": { "@id": "https://www.example.com/#organization" },
      "mainEntity": {
        "@type": "CreativeWork",
        "@id": "https://www.example.com/privacy-policy/#privacy-policy",
        "name": "Privacy Policy",
        "url": "https://www.example.com/privacy-policy/",
        "publisher": { "@id": "https://www.example.com/#organization" },
        "inLanguage": "en-US"
      },
      "breadcrumb": { "@id": "https://www.example.com/privacy-policy/#breadcrumb" },
      "inLanguage": "en-US"
    }
  ]
}
```

### Field Notes
- Swap the fragment slug per page: `#privacy-policy`, `#terms-of-use`, `#legal-disclaimers`. Keep `#webpage` consistent on the outer `WebPage` node.
- `description` should summarize the actual sections of the policy — don't auto-generate. The example above mirrors what's actually in the page body.
- These pages are typically not rich-results eligible, but the markup still helps crawlers understand site structure and entity relationships. Schema Markup Validator should pass cleanly; Rich Results Test will report "not eligible" — that's expected, not an error.
- `CreativeWork` is intentionally generic; do not upgrade to `Article` or `WebContent` — these are static legal documents, not editorial content.

## Multi-Typed Organization Reference

Some implementations declare the firm as `["LegalService", "Organization"]` (array of types) instead of bare `LegalService`. This is valid Schema.org syntax and remains compatible with the project convention (publisher is `LegalService`), because `LegalService` is still asserted as a type. Use this form only when:

- A third party's schema tooling (e.g., AI overviews, knowledge graph extractors) demonstrably benefits from the explicit `Organization` declaration
- The sitewide schema source already emits this form, in which case page-level references should match for consistency

Otherwise, bare `LegalService` is preferred — it's more specific and `LegalService` already inherits from `Organization` transitively (`LegalService` → `LocalBusiness` → `Organization`).

## Sitewide Schema (Prerequisite — Often Already Exists)

Every law firm site must emit these on EVERY page. The post-level plugins reference them by `@id`.

### Structure (loaded once per page)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "LegalService",
      "@id": "https://www.example.com/#organization",
      "name": "Example Law Firm",
      "url": "https://www.example.com/",
      "logo": { "@id": "https://www.example.com/#logo" },
      "image": { "@id": "https://www.example.com/#logo" },
      "telephone": "+1-555-123-4567",
      "email": "info@example.com",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "1234 Example Blvd",
        "addressLocality": "Arlington",
        "addressRegion": "VA",
        "postalCode": "22201",
        "addressCountry": "US"
      },
      "sameAs": [
        "https://www.facebook.com/examplelaw",
        "https://www.linkedin.com/company/example-law"
      ],
      "priceRange": "$$"
    },
    {
      "@type": "ImageObject",
      "@id": "https://www.example.com/#logo",
      "url": "https://www.example.com/wp-content/uploads/logo.png",
      "width": 600,
      "height": 60,
      "caption": "Example Law Firm"
    },
    {
      "@type": "WebSite",
      "@id": "https://www.example.com/#website",
      "url": "https://www.example.com/",
      "name": "Example Law Firm",
      "publisher": { "@id": "https://www.example.com/#organization" },
      "inLanguage": "en-US"
    }
  ]
}
```

If a site doesn't have this running, BUILD IT FIRST before any page-level plugins. Page-level schemas with undefined `@id` references will fail validation.
