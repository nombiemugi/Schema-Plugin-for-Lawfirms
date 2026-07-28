<?php
/**
 * Schema Base Class
 * =================================================================
 * Abstract base class that all schema handlers extend.
 * Provides shared helpers for:
 *  - Language detection (Polylang, WPML, URL pattern, forced)
 *  - Author resolution (ACF override → native WP fallback)
 *  - Sitewide entity references (@id helpers)
 *  - JSON-LD output with proper encoding flags
 *  - Entity cleaning (strip null/empty fields)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Firm_Legal_Schema_Base {

    /**
     * Per-site configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Current post ID (if available).
     *
     * @var int|null
     */
    protected $post_id;

    /**
     * Current page permalink.
     *
     * @var string
     */
    protected $permalink;

    /**
     * Site home URL with trailing slash.
     *
     * @var string
     */
    protected $home_url;

    /**
     * Detected language code (en-US or es-US).
     *
     * @var string
     */
    protected $lang_code;

    /**
     * Localized breadcrumb root label (Home or Inicio).
     *
     * @var string
     */
    protected $home_label;


    /**
     * Constructor — initialize shared state for the handler.
     *
     * @param array $config Per-site configuration array.
     */
    public function __construct( array $config ) {
        $this->config   = $config;
        $this->home_url = home_url( '/' );

        global $post;
        if ( $post ) {
            $this->post_id   = $post->ID;
            $this->permalink = get_permalink( $post->ID );
        }

        $this->detect_language();
    }


    /**
     * Detect the current page language.
     *
     * Priority order:
     * 1. Forced language from config
     * 2. Polylang plugin
     * 3. WPML plugin
     * 4. URL pattern fallback
     * 5. Default en-US
     */
    protected function detect_language() {

        if ( ! empty( $this->config['force_language'] ) ) {
            $this->lang_code = $this->config['force_language'];
        }
        elseif ( function_exists( 'pll_current_language' ) ) {
            $current_lang    = pll_current_language();
            $this->lang_code = ( $current_lang === 'es' ) ? 'es-US' : 'en-US';
        }
        elseif ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $this->lang_code = ( ICL_LANGUAGE_CODE === 'es' ) ? 'es-US' : 'en-US';
        }
        elseif ( $this->permalink
            && ! empty( $this->config['spanish_url_marker'] )
            && strpos( $this->permalink, $this->config['spanish_url_marker'] ) !== false
        ) {
            $this->lang_code = 'es-US';
        }
        else {
            $this->lang_code = 'en-US';
        }

        $this->home_label = ( $this->lang_code === 'es-US' ) ? 'Inicio' : 'Home';
    }


    /**
     * Resolve the post's author with fixed config → ACF override → WP fallback.
     *
     * @return array {
     *     @type string $name   Author display name
     *     @type string $url    Author profile URL
     *     @type string $anchor Author @id (matches attorney profile page)
     * }
     */
    protected function resolve_author() {
        global $post;

        // Fixed author (personal/attorney page) takes priority — no
        // per-post byline override on this site.
        $author_name = ! empty( $this->config['blog_author']['name'] )
            ? $this->config['blog_author']['name']
            : null;
        $author_url = ! empty( $this->config['blog_author']['url'] )
            ? $this->config['blog_author']['url']
            : null;

        // ACF custom fields next
        if ( empty( $author_name ) && function_exists( 'get_field' ) ) {
            $author_name = get_field( $this->config['acf_author_name_field'], $this->post_id );
            $author_url  = get_field( $this->config['acf_author_url_field'], $this->post_id );
        }

        // Fallback to native WP author
        if ( empty( $author_name ) ) {
            $author_name = get_the_author_meta( 'display_name', $post->post_author );
            $author_url  = get_author_posts_url( $post->post_author );
        }

        // Build attorney anchor matching profile page convention
        $slug   = sanitize_title( remove_accents( $author_name ) );
        $anchor = $this->home_url . '#attorney-' . $slug;

        return array(
            'name'   => $author_name,
            'url'    => $author_url,
            'anchor' => $anchor,
        );
    }


    /**
     * Reference to the sitewide #organization entity.
     *
     * @return array
     */
    protected function org_ref() {
        return array( '@id' => $this->home_url . '#organization' );
    }


    /**
     * Reference to the sitewide #website entity.
     *
     * @return array
     */
    protected function website_ref() {
        return array( '@id' => $this->home_url . '#website' );
    }


    /**
     * Reference to the sitewide #logo entity.
     *
     * @return array
     */
    protected function logo_ref() {
        return array( '@id' => $this->home_url . '#logo' );
    }


    /**
     * Return the slug of the parent page of a given post, or '' when
     * the post has no parent (or the parent isn't reachable).
     *
     * Used by hierarchical-page sites (e.g., attorneys as child pages
     * of /meet-our-team/) to detect what kind of page is being rendered.
     *
     * @param int $post_id
     * @return string
     */
    protected function parent_page_slug( $post_id ) {
        $parent_id = wp_get_post_parent_id( $post_id );
        if ( ! $parent_id ) {
            return '';
        }
        $parent = get_post( $parent_id );
        return $parent ? $parent->post_name : '';
    }


    /**
     * Return published child pages of the page whose slug is $parent_slug,
     * ordered by menu_order. Returns an empty array when the parent page
     * doesn't exist.
     *
     * Used by listing handlers (Team / Practice Areas) to enumerate
     * children when building ItemList entries.
     *
     * @param string $parent_slug
     * @return array Array of WP_Post objects
     */
    protected function child_pages_of( $parent_slug ) {
        if ( empty( $parent_slug ) ) {
            return array();
        }
        $parent = get_page_by_path( $parent_slug );
        if ( ! $parent ) {
            return array();
        }
        $children = get_pages( array(
            'parent'      => $parent->ID,
            'sort_column' => 'menu_order',
            'post_status' => 'publish',
        ) );
        return is_array( $children ) ? $children : array();
    }


    /**
     * Strip null and empty-string values from an entity array.
     * Used to remove optional fields that aren't populated.
     *
     * @param array $entity
     * @return array
     */
    protected function clean_entity( array $entity ) {
        return array_filter( $entity, function ( $v ) {
            return $v !== null && $v !== '';
        } );
    }


    /**
     * Output JSON-LD wrapped in a <script> tag.
     *
     * @param array $graph_or_entity Either a full @graph structure or a single entity.
     */
    protected function output_json_ld( array $data ) {

        // Wrap single entity in @graph if not already structured
        if ( ! isset( $data['@context'] ) ) {
            $data = array(
                '@context' => 'https://schema.org',
                '@graph'   => is_array( reset( $data ) ) && isset( reset( $data )['@type'] )
                    ? $data
                    : array( $data ),
            );
        }

        echo "\n" . '<script type="application/ld+json">'
            . wp_json_encode(
                $data,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            )
            . '</script>' . "\n";
    }


    /**
     * Each handler must implement render() to detect its trigger
     * and output its schema.
     */
    abstract public function render();
}
