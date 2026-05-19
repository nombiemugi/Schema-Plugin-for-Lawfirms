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
    // CUSTOM POST TYPES
    // -----------------------------------------------------------------

    'attorney_post_type'      => 'attorney',
    'practice_area_post_type' => 'practice_area',
    'case_result_post_type'   => 'case_result',


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
        'blog_posting'   => true,
        'attorney'       => false,
        'practice_area'  => false,
        'contact_page'   => false,
        'about_page'     => false,
        'testimonials'   => false,
        'video_library'  => false,
        'blog_index'     => false,
        'faq_page'       => false,
        'policy_pages'   => false,
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
