<?php
/**
 * Per-Site Configuration — TEMPLATE
 * =================================================================
 * This is the ONLY file you should edit when deploying to a new site.
 * It ships as a TEMPLATE: no real site's data belongs here. Every
 * value below is either a safe default or a placeholder to replace.
 *
 * MARKER CONVENTION
 * -----------------------------------------------------------------
 *   [PER-SITE]  You must set this. Wrong or stale values produce
 *               wrong schema — no error, just bad output.
 *   [DEFAULT]   Safe to leave alone. Change only if the site differs.
 *   [EXAMPLE]   Illustration in a comment, never a live value.
 *
 * MINIMUM CHECKLIST FOR A NEW SITE
 * -----------------------------------------------------------------
 *   1. 'force_language'            — pin it on single-language sites
 *   2. 'blog_author'               — who the posts are attributed to
 *   3. 'person_id'                 — the shape of the Person @id;
 *                                    MUST match the profile page's @id
 *   4. 'enabled_schemas'           — turn on only what has a handler
 *   5. Slugs + post types          — match the site's real URLs
 *
 * Step 3 is the one that silently breaks things. Before deploying,
 * open the person's profile page, view source, find the Person in its
 * JSON-LD, and copy its @id. Shape 'person_id' to reproduce that
 * string EXACTLY. If it doesn't match, Google reads two unrelated
 * people instead of one, and nothing in the output looks wrong.
 *
 * Bilingual slugs: each page-typed entry under 'pages' takes a 'slugs'
 * map keyed by language ('en', 'es'). The router matches the current
 * WP page against ALL configured slugs for that type, so the same
 * config works for English, Spanish, and bilingual sites.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(

    // -----------------------------------------------------------------
    // LANGUAGE HANDLING
    // -----------------------------------------------------------------

    /**
     * [PER-SITE] Force a specific language code (overrides auto-detection).
     * - 'en-US' for English-only sites
     * - 'es-US' for Spanish-only sites
     * - null for bilingual sites — auto-detection applies
     *
     * Pin this on single-language sites. Left null with no Polylang/WPML
     * installed, detection falls through to en-US by accident rather than
     * by decision — correct on an English site, silently wrong on a
     * Spanish one.
     */
    'force_language' => null,

    /**
     * [DEFAULT] URL marker for Spanish-language fallback detection.
     * Only used when force_language is null AND no multilingual plugin is
     * active. [EXAMPLE] '/es/', '/espanol/', '/spanish/', '//es.', '.es/'
     */
    'spanish_url_marker' => '/es/',


    // -----------------------------------------------------------------
    // AUTHOR HANDLING
    // -----------------------------------------------------------------

    /**
     * [PER-SITE] Fixed author attributed to ALL blog content (BlogPosting +
     * blog index), pointing at that person's own profile page rather than a
     * generic WP author archive. Typically the firm's primary member, the
     * business owner, or the sole author of the blog.
     *
     * When 'name' is set, it takes priority over the ACF / native WP author
     * lookup below for every post — no per-post byline overrides.
     *
     * Leave 'name' EMPTY to fall back to ACF fields, then the native WP
     * post author. That is the right choice on multi-author sites.
     *
     * 'url' must be the exact profile-page URL, trailing slash included.
     * Under person_id base 'author_url' it is ALSO the base of the Person
     * @id, so a typo here silently splits the entity in two.
     *
     * [EXAMPLE] A single-owner site:
     *     'name' => 'Jane Doe',
     *     'url'  => 'https://example.com/meet-the-team/jane-doe/',
     */
    'blog_author' => array(
        'name' => '',
        'url'  => '',
    ),

    /**
     * [PER-SITE] Shape of the @id for EVERY Person this plugin emits — the
     * blog author, the attorney/bio page, and the About page mention. All
     * three route through Firm_Legal_Schema_Base::build_person_id(), so
     * this one block keeps them pointing at a single entity.
     *
     * VERIFY THIS AGAINST THE LIVE SITE BEFORE DEPLOYING. The @id must
     * match, character for character, the @id that the person's own profile
     * page already declares (usually emitted by the theme or an SEO
     * plugin). A mismatch is invisible in the validators — the schema
     * passes, but Google reads two unrelated people.
     *
     * FIELDS
     *   'base' — what the fragment is appended to:
     *       'home'        → home_url
     *       'author_url'  → the person's own profile URL (blog_author.url,
     *                       the ACF author URL, the attorney page permalink,
     *                       or primary_attorney.profile_url)
     *       'https://…'   → any literal URL, for a profile hosted elsewhere
     *   'fragment'    — fragment name, without the '#'
     *   'append_slug' — when true, appends '-{name-slug}' to the fragment;
     *                   turn OFF for a bare '#person' anchor
     *
     * PRESETS — copy the one that reproduces the site's existing @id.
     *
     *   A. Law-firm convention, one anchor per attorney on the home URL.
     *      This is the shipped default.
     *          'base' => 'home', 'fragment' => 'attorney', 'append_slug' => true
     *      [EXAMPLE] https://example.com/#attorney-jane-doe
     *
     *   B. @id anchored to the person's own bio page. Correct for most
     *      non-law-firm sites, and for law firms whose theme anchors the
     *      Person to its own page.
     *          'base' => 'author_url', 'fragment' => 'person', 'append_slug' => false
     *      [EXAMPLE] https://example.com/meet-the-team/jane-doe/#person
     *
     *   C. Profile hosted at a fixed URL that the plugin can't derive.
     *          'base' => 'https://example.com/team/jane/', 'fragment' => 'person', 'append_slug' => false
     *      [EXAMPLE] https://example.com/team/jane/#person
     */
    'person_id' => array(
        'base'        => 'home',
        'fragment'    => 'attorney',
        'append_slug' => true,
    ),

    /**
     * [PER-SITE] Advanced Custom Fields field NAMES (not labels) for
     * per-post author overrides. Find them in WP admin → Custom Fields.
     * Only consulted when 'blog_author' above is left empty. If ACF isn't
     * installed or these fields are empty, the plugin falls back to the
     * native WordPress post author — so wrong names here degrade quietly
     * rather than erroring.
     */
    'acf_author_name_field' => 'autor_nombre',
    'acf_author_url_field'  => 'autor_url',


    // -----------------------------------------------------------------
    // MANAGING ATTORNEY — NOT READ BY THE CODE
    // -----------------------------------------------------------------

    /**
     * DEPRECATED / INERT. Firm_Legal_Schema_Base::resolve_author() reads
     * 'blog_author' above, never this block. Editing these values has no
     * effect on the output. Kept only so existing per-site configs don't
     * break on load; set 'blog_author' instead.
     */
    'managing_attorney' => array(
        'name' => '',
        'url'  => '',
    ),


    // -----------------------------------------------------------------
    // CUSTOM POST TYPES
    // -----------------------------------------------------------------

    /**
     * [PER-SITE] Used when attorneys / practice areas / case results are
     * registered as Custom Post Types. Leave empty (or unused) on sites
     * that organize these as hierarchical WP pages — in that case
     * configure attorney_parent_pages / practice_area_parent_pages below
     * instead. The values here are placeholder CPT slugs.
     */
    'attorney_post_type'      => 'attorney',
    'practice_area_post_type' => 'practice_area',
    'case_result_post_type'   => 'case_result',


    // -----------------------------------------------------------------
    // HIERARCHICAL-PAGE STRUCTURE
    // -----------------------------------------------------------------

    /**
     * For sites that organize attorneys and practice areas as
     * hierarchical WP pages (NOT custom post types). The router will
     * treat any child page of one of these parent slugs as an
     * attorney / practice area page respectively.
     *
     * Leave 'slugs' empty (or this whole block out) on CPT-based sites.
     *
     * [EXAMPLE] When /meet-the-team/firstname-lastname/ pages are the
     * attorney bios, the en slug is 'meet-the-team'. Both values below are
     * [PER-SITE] placeholders — match the site's real published slugs, in
     * both languages.
     */
    'attorney_parent_pages' => array(
        'slugs' => array(
            'en' => 'meet-our-team',
            'es' => 'conoce-nuestro-equipo',
        ),
    ),

    'practice_area_parent_pages' => array(
        'slugs' => array(
            'en' => 'practice-areas',
            'es' => 'areas-de-practica',
        ),
    ),


    // -----------------------------------------------------------------
    // PAGE SLUGS (BILINGUAL)
    // -----------------------------------------------------------------

    /**
     * Slugs for static pages keyed by schema type, with language variants.
     *
     * The router calls is_page( array_values( $slugs ) ) — WordPress matches
     * the current page if its slug matches ANY entry. Leave a slug empty if
     * the page doesn't exist in that language on this site.
     *
     * [PER-SITE] Every slug below is a placeholder. Override each to match
     * the site's actual published URLs before enabling its handler — a
     * stale slug means the handler simply never fires, with no error.
     */
    'pages' => array(

        'about_page' => array(
            'slugs' => array(
                'en' => 'about-us',
                'es' => 'sobre-nosotros',
            ),

            /**
             * Primary attorney mentioned on the About page. Drives the
             * Person entity, the AboutPage.mentions reference, and
             * (optionally) the primary image.
             *
             * Leave 'name' empty to suppress the Person/mention block
             * entirely — the AboutPage will still render with the
             * BreadcrumbList and the WP featured image (if any).
             *
             * The attorney @id is shaped by the 'person_id' block above,
             * and MUST match the @id used on the dedicated attorney
             * profile page so the graph stays consistent.
             */
            'primary_attorney' => array(
                // [PER-SITE] Display name, e.g. 'Jane Doe'. Required for emission.
                'name'          => '',

                // e.g. 'Attorney', 'Founding Partner', 'Managing Attorney', 'CEO'.
                'job_title'     => 'Attorney',

                // Optional URL of the person's own bio page, when it isn't
                // the About page itself. Becomes the Person's 'url' and —
                // under person_id base 'author_url' — the @id base too.
                // Leave empty to use the About page permalink.
                'profile_url'   => '',

                // Optional direct URL to the attorney's headshot. When set,
                // it takes precedence over the About page's WP featured image
                // for the #primaryimage ImageObject.
                'image_url'     => '',

                // Optional caption for the primary image. When blank and an
                // image_url is supplied, auto-built as "{name} of {site name}".
                'image_caption' => '',

                // Profile URLs (Avvo, LinkedIn, State Bar listing, etc.).
                'same_as'       => array(
                    // 'https://www.avvo.com/attorneys/...',
                ),
            ),
        ),

        'contact_page' => array(
            'slugs' => array(
                'en' => 'contact-us',
                'es' => 'contactanos',
            ),
            /**
             * Optional dedicated phone for ContactPoint. When set, the handler
             * emits a ContactPoint sub-entity. Use E.164 format.
             * Leave empty to fall back to the sitewide #organization phone.
             */
            'telephone'   => '',
            'contact_type' => 'customer support',
        ),

        'testimonials' => array(
            'slugs' => array(
                'en' => 'testimonials',
                'es' => 'testimonios',
            ),
        ),

        'video_library' => array(
            'slugs' => array(
                'en' => 'video-library',
                'es' => 'biblioteca-de-videos',
            ),
            /**
             * ACF repeater field on the Video Library page. Each row should
             * expose these subfields. Empty rows or rows missing a URL are
             * skipped.
             */
            'acf' => array(
                'repeater'    => 'videos',
                'title'       => 'title',
                'url'         => 'url',
                'description' => 'description',
                'thumbnail'   => 'thumbnail',
                'upload_date' => 'upload_date',
                'duration'    => 'duration',
            ),
        ),

        'blog_index' => array(
            'slugs' => array(
                'en' => 'blog',
                'es' => 'blog',
            ),
        ),

        'faq_page' => array(
            'slugs' => array(
                'en' => 'faq',
                'es' => 'preguntas-frecuentes',
            ),
        ),

        /**
         * Listing pages for hierarchical-page sites. These typically share
         * the slug with attorney_parent_pages / practice_area_parent_pages
         * (the parent page IS the listing page) — the router falls back
         * to those slugs when these blocks are left empty.
         */
        'meet_our_team' => array(
            'slugs' => array(
                'en' => 'meet-our-team',
                'es' => 'conoce-nuestro-equipo',
            ),
        ),

        'practice_areas_index' => array(
            'slugs' => array(
                'en' => 'practice-areas',
                'es' => 'areas-de-practica',
            ),
        ),

    ),


    // -----------------------------------------------------------------
    // GENERIC PAGES (single handler emits minimal WebPage + breadcrumb)
    // -----------------------------------------------------------------

    /**
     * Pages that don't warrant a richer schema type — e.g. an
     * "In the Media" page that only contains YouTube embeds, a Jobs
     * page with no real openings, or a Blog index where listing
     * the latest BlogPostings isn't valuable. Each entry produces a
     * WebPage + BreadcrumbList when the current slug matches.
     *
     * Refusing to fabricate VideoObject or JobPosting metadata is
     * intentional — both schemas have strict Google requirements
     * (duration, employment type, etc.) that we can't honestly fill
     * for these pages.
     */
    'generic_pages' => array(
        array(
            'key'   => 'in_the_media',
            'slugs' => array( 'en' => 'in-the-media', 'es' => 'medios' ),
        ),
        array(
            'key'   => 'jobs_page',
            'slugs' => array( 'en' => 'jobs', 'es' => 'empleos' ),
        ),
        array(
            'key'   => 'blog_index',
            'slugs' => array( 'en' => 'blog', 'es' => 'blog' ),
        ),
    ),


    // -----------------------------------------------------------------
    // PRACTICE AREA SUBTOPICS (drives LegalService.hasOfferCatalog)
    // -----------------------------------------------------------------

    /**
     * Per-practice-area sub-topic lists, keyed by the page slug. When
     * a practice area page renders, the handler looks up its slug here
     * and emits hasOfferCatalog → OfferCatalog → Offer[] entries.
     *
     * Leave the array empty (or omit a slug) to suppress hasOfferCatalog
     * for that page — the LegalService entity still renders cleanly.
     *
     * 'area_served' is optional; omit to inherit from the sitewide
     * #organization signal.
     */
    'practice_areas' => array(
        // Example shape — override per site:
        // 'family-immigration' => array(
        //     'subtopics'   => array(
        //         'Family-Based Green Cards',
        //         'Fiancé(e) Visas',
        //         'Adjustment of Status',
        //         'Consular Processing',
        //     ),
        //     'area_served' => array( 'Texas', 'United States' ),
        // ),
    ),


    // -----------------------------------------------------------------
    // ATTORNEY OVERRIDES (optional per-attorney config)
    // -----------------------------------------------------------------

    /**
     * [PER-SITE] Per-attorney overrides keyed by the attorney page slug.
     * Currently only 'job_title' is honored — the rest of the Person entity
     * is built from native WP data (title, content, featured image).
     * Optional: an empty array is valid, everyone falls back to "Attorney".
     */
    'attorneys' => array(
        // [EXAMPLE]
        // 'jane-doe' => array(
        //     'job_title' => 'Founding Partner',
        // ),
    ),


    // -----------------------------------------------------------------
    // POLICY PAGES (single handler drives all of these)
    // -----------------------------------------------------------------

    /**
     * Each entry produces a WebPage + CreativeWork mainEntity when the
     * current page slug matches. Add or remove rows as the site needs.
     * 'fragment' is the @id suffix on the CreativeWork (#privacy-policy).
     */
    'policy_pages' => array(
        array(
            'key'      => 'privacy_policy',
            'slugs'    => array( 'en' => 'privacy-policy', 'es' => 'politica-de-privacidad' ),
            'name'     => array( 'en' => 'Privacy Policy', 'es' => 'Política de Privacidad' ),
            'fragment' => 'privacy-policy',
        ),
        array(
            'key'      => 'terms_of_use',
            'slugs'    => array( 'en' => 'terms-of-use', 'es' => 'terminos-de-uso' ),
            'name'     => array( 'en' => 'Terms of Use', 'es' => 'Términos de Uso' ),
            'fragment' => 'terms-of-use',
        ),
        array(
            'key'      => 'legal_disclaimers',
            'slugs'    => array( 'en' => 'legal-disclaimers', 'es' => 'descargos-legales' ),
            'name'     => array( 'en' => 'Legal Disclaimers', 'es' => 'Descargos Legales' ),
            'fragment' => 'legal-disclaimers',
        ),
    ),


    // -----------------------------------------------------------------
    // SCHEMA TOGGLES
    // -----------------------------------------------------------------

    /**
     * [PER-SITE] Enable or disable individual schema types for this site.
     * Only enable schemas the site actually needs AND has handlers built
     * for. Enabling a type whose slugs above are still placeholders means
     * the handler never fires — the toggle looks on, the output is absent.
     */
    'enabled_schemas' => array(
        'blog_posting'           => true,
        'attorney'               => false,
        'practice_area'          => false,
        'team_listing'           => false,
        'practice_areas_listing' => false,
        'contact_page'           => false,
        'about_page'             => false,
        'testimonials'           => false,
        'video_library'          => false,
        'blog_index'             => false,
        'faq_page'               => false,
        'policy_pages'           => false,
        'generic_pages'          => false,
    ),


    // -----------------------------------------------------------------
    // OPTIONAL DEFAULTS
    // -----------------------------------------------------------------

    /**
     * Default featured image URL for posts without one.
     * Leave empty to omit the image field when missing (current convention).
     */
    'default_image_url' => '',

);
