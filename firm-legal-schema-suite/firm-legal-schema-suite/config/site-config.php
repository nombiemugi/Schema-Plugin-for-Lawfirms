<?php
/**
 * Per-Site Configuration
 * =================================================================
 * This is the ONLY file you should edit when deploying to a new site.
 *
 * Returns an array of configuration values consumed by the plugin.
 * Each section is documented below — change values to match the
 * specific site you're deploying to.
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
     * Force a specific language code (overrides auto-detection).
     * - Set to 'en-US' for English-only sites
     * - Set to 'es-US' for Spanish-only sites
     * - Set to null (default) for bilingual sites — auto-detection applies
     */
    'force_language' => null,

    /**
     * URL marker for Spanish-language fallback detection.
     * Only used when force_language is null AND no multilingual plugin is active.
     * Examples: '/es/', '/espanol/', '/spanish/', '//es.', '.es/'
     */
    'spanish_url_marker' => '/es/',


    // -----------------------------------------------------------------
    // AUTHOR HANDLING (ACF)
    // -----------------------------------------------------------------

    /**
     * Advanced Custom Fields field names for custom author overrides.
     * If ACF is not installed or these fields are empty, the plugin falls
     * back to the native WordPress post author.
     */
    'acf_author_name_field' => 'autor_nombre',
    'acf_author_url_field'  => 'autor_url',


    // -----------------------------------------------------------------
    // MANAGING ATTORNEY (blog post author attribution)
    // -----------------------------------------------------------------

    /**
     * When 'name' is set, EVERY blog post is attributed to this managing
     * attorney — the author Person's name, url, and @id all reflect this
     * person, regardless of the WordPress post author. This overrides the
     * ACF / native WP author resolution above.
     *
     * The @id is built as:
     *   home_url + '#attorney-' + sanitize_title( remove_accents( name ) )
     * and MUST match the @id declared on the attorney's dedicated profile
     * page for entity continuity.
     *
     * 'url' is the FULL URL of that dedicated profile page (not the WP
     * author archive).
     *
     * Leave 'name' empty to disable the override — the plugin then falls
     * back to the per-post ACF author / native WP author.
     *
     * PLACEHOLDERS below — replace per site.
     */
    'managing_attorney' => array(
        'name' => '',  // e.g. 'Jane Doe'  (required to enable the override)
        'url'  => '',  // e.g. 'https://example.com/attorneys/jane-doe/'
    ),


    // -----------------------------------------------------------------
    // CUSTOM POST TYPES
    // -----------------------------------------------------------------

    /**
     * Used when attorneys / practice areas / case results are registered as
     * Custom Post Types. Leave empty (or unused) on sites that organize
     * these as hierarchical WP pages — in that case configure
     * attorney_parent_pages and practice_area_parent_pages below instead.
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
     * Example: on lincolngoldfinch.com, /meet-our-team/firstname-lastname/
     * pages are attorneys, so the en slug is 'meet-our-team'. The Spanish
     * equivalent should match the actual published Spanish page slug; the
     * value below is a placeholder.
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
     * The Spanish slugs below are placeholders; override per-site to match
     * the actual published URLs (e.g., austinbankruptcylawyers.com uses
     * /es/sobre-nosotros/ for the Spanish About page).
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
             * The attorney @id is built as:
             *   home_url + '#attorney-' + sanitize_title( remove_accents( name ) )
             * It MUST match the @id used on the dedicated attorney
             * profile page so the graph stays consistent.
             */
            'primary_attorney' => array(
                // Display name, e.g. 'Michael Cindrich'. Required for emission.
                'name'          => '',

                // e.g. 'Attorney', 'Founding Partner', 'Managing Attorney', 'CEO'.
                'job_title'     => 'Attorney',

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
     * Per-attorney overrides keyed by the attorney page slug. Currently
     * only 'job_title' is honored — the rest of the Person entity is
     * built from native WP data (title, content, featured image).
     */
    'attorneys' => array(
        // 'kate-lincoln-goldfinch' => array(
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
     * Enable or disable individual schema types for this site.
     * Only enable schemas the site actually needs and has handlers built for.
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
