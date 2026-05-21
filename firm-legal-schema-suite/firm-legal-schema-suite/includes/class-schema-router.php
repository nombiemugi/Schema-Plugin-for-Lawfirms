<?php
/**
 * Schema Router
 * =================================================================
 * Inspects the current page and dispatches to the appropriate
 * handler. Each handler is responsible for outputting its own schema
 * (BlogPosting, Attorney, LegalService, etc.).
 *
 * To add a new schema type:
 * 1. Build the handler class (extends Firm_Legal_Schema_Base)
 * 2. Add a detection branch to select_handler() below
 * 3. Enable in config/site-config.php → enabled_schemas
 * 4. Conditionally require_once the handler in the main plugin file
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Schema_Router {

    /**
     * Per-site configuration.
     *
     * @var array
     */
    protected $config;


    /**
     * @param array $config Per-site configuration array.
     */
    public function __construct( array $config ) {
        $this->config = $config;
    }


    /**
     * Detect the current page type and run the matching handler.
     */
    public function dispatch() {
        $handler = $this->select_handler();

        if ( $handler ) {
            $handler->render();
        }
    }


    /**
     * Select the appropriate handler for the current page.
     * Returns null if no handler matches (no schema is output).
     *
     * Order matters: more specific checks before more general ones.
     *
     * @return Firm_Legal_Schema_Base|null
     */
    protected function select_handler() {
        $enabled = isset( $this->config['enabled_schemas'] ) ? $this->config['enabled_schemas'] : array();

        // -------- Blog post (singular) -------- //
        if ( ! empty( $enabled['blog_posting'] )
            && class_exists( 'Firm_Legal_Blog_Posting' )
            && is_singular( 'post' )
        ) {
            return new Firm_Legal_Blog_Posting( $this->config );
        }

        // -------- Attorney bio (CPT-based site) -------- //
        if ( ! empty( $enabled['attorney'] )
            && class_exists( 'Firm_Legal_Attorney' )
            && ! empty( $this->config['attorney_post_type'] )
            && is_singular( $this->config['attorney_post_type'] )
        ) {
            return new Firm_Legal_Attorney( $this->config );
        }

        // -------- Practice area (CPT-based site) -------- //
        if ( ! empty( $enabled['practice_area'] )
            && class_exists( 'Firm_Legal_Practice_Area' )
            && ! empty( $this->config['practice_area_post_type'] )
            && is_singular( $this->config['practice_area_post_type'] )
        ) {
            return new Firm_Legal_Practice_Area( $this->config );
        }

        // -------- Attorney bio (hierarchical-page site) -------- //
        if ( ! empty( $enabled['attorney'] )
            && class_exists( 'Firm_Legal_Attorney' )
            && $this->attorney_page_matches()
        ) {
            return new Firm_Legal_Attorney( $this->config );
        }

        // -------- Practice area (hierarchical-page site) -------- //
        if ( ! empty( $enabled['practice_area'] )
            && class_exists( 'Firm_Legal_Practice_Area' )
            && $this->practice_area_page_matches()
        ) {
            return new Firm_Legal_Practice_Area( $this->config );
        }

        // -------- Team listing page (e.g., /meet-our-team/) -------- //
        if ( ! empty( $enabled['team_listing'] )
            && class_exists( 'Firm_Legal_Team_Listing' )
            && $this->team_listing_matches()
        ) {
            return new Firm_Legal_Team_Listing( $this->config );
        }

        // -------- Practice areas listing page (e.g., /practice-areas/) -------- //
        if ( ! empty( $enabled['practice_areas_listing'] )
            && class_exists( 'Firm_Legal_Practice_Areas_Listing' )
            && $this->practice_areas_listing_matches()
        ) {
            return new Firm_Legal_Practice_Areas_Listing( $this->config );
        }

        // -------- About page -------- //
        if ( ! empty( $enabled['about_page'] )
            && class_exists( 'Firm_Legal_About_Page' )
            && $this->page_matches( 'about_page' )
        ) {
            return new Firm_Legal_About_Page( $this->config );
        }

        // -------- Contact page -------- //
        if ( ! empty( $enabled['contact_page'] )
            && class_exists( 'Firm_Legal_Contact_Page' )
            && $this->page_matches( 'contact_page' )
        ) {
            return new Firm_Legal_Contact_Page( $this->config );
        }

        // -------- Testimonials page -------- //
        if ( ! empty( $enabled['testimonials'] )
            && class_exists( 'Firm_Legal_Testimonials' )
            && $this->page_matches( 'testimonials' )
        ) {
            return new Firm_Legal_Testimonials( $this->config );
        }

        // -------- Video library page -------- //
        if ( ! empty( $enabled['video_library'] )
            && class_exists( 'Firm_Legal_Video_Library' )
            && $this->page_matches( 'video_library' )
        ) {
            return new Firm_Legal_Video_Library( $this->config );
        }

        // -------- Blog index page -------- //
        if ( ! empty( $enabled['blog_index'] )
            && class_exists( 'Firm_Legal_Blog_Index' )
            && $this->page_matches( 'blog_index' )
        ) {
            return new Firm_Legal_Blog_Index( $this->config );
        }

        // -------- FAQ page -------- //
        if ( ! empty( $enabled['faq_page'] )
            && class_exists( 'Firm_Legal_FAQ_Page' )
            && $this->page_matches( 'faq_page' )
        ) {
            return new Firm_Legal_FAQ_Page( $this->config );
        }

        // -------- Policy pages (privacy / terms / disclaimers / ...) -------- //
        if ( ! empty( $enabled['policy_pages'] )
            && class_exists( 'Firm_Legal_Policy_Page' )
            && $this->any_policy_page_matches()
        ) {
            return new Firm_Legal_Policy_Page( $this->config );
        }

        // -------- Generic pages (in-the-media, jobs, blog index, ...) -------- //
        if ( ! empty( $enabled['generic_pages'] )
            && class_exists( 'Firm_Legal_Generic_Page' )
            && $this->any_generic_page_matches()
        ) {
            return new Firm_Legal_Generic_Page( $this->config );
        }

        return null;
    }


    /**
     * True when the current request is a page whose parent slug matches
     * one of the configured attorney_parent_pages slugs.
     *
     * @return bool
     */
    protected function attorney_page_matches() {
        return $this->is_child_of_configured_parent( 'attorney_parent_pages' );
    }


    /**
     * True when the current request is a page whose parent slug matches
     * one of the configured practice_area_parent_pages slugs.
     *
     * @return bool
     */
    protected function practice_area_page_matches() {
        return $this->is_child_of_configured_parent( 'practice_area_parent_pages' );
    }


    /**
     * True when the current page slug matches one of the team listing
     * slugs (typically the parent of attorney pages, e.g. /meet-our-team/).
     *
     * Pulls slugs from pages.meet_our_team first, falling back to
     * attorney_parent_pages.slugs so a single config block can drive both.
     *
     * @return bool
     */
    protected function team_listing_matches() {
        $slugs = $this->page_slugs( 'meet_our_team' );
        if ( empty( $slugs ) ) {
            $slugs = $this->parent_slugs( 'attorney_parent_pages' );
        }
        if ( empty( $slugs ) ) {
            return false;
        }
        return is_page( $slugs );
    }


    /**
     * True when the current page slug matches one of the practice areas
     * listing slugs.
     *
     * Pulls slugs from pages.practice_areas_index first, falling back
     * to practice_area_parent_pages.slugs.
     *
     * @return bool
     */
    protected function practice_areas_listing_matches() {
        $slugs = $this->page_slugs( 'practice_areas_index' );
        if ( empty( $slugs ) ) {
            $slugs = $this->parent_slugs( 'practice_area_parent_pages' );
        }
        if ( empty( $slugs ) ) {
            return false;
        }
        return is_page( $slugs );
    }


    /**
     * Shared implementation for parent-slug detection.
     * Looks up the current post's parent page slug and checks it
     * against the slugs in $config[$config_key]['slugs'].
     *
     * @param string $config_key
     * @return bool
     */
    protected function is_child_of_configured_parent( $config_key ) {
        if ( ! is_page() ) {
            return false;
        }

        $parent_slugs = $this->parent_slugs( $config_key );
        if ( empty( $parent_slugs ) ) {
            return false;
        }

        $post_id = get_queried_object_id();
        if ( ! $post_id ) {
            return false;
        }

        $parent_id = wp_get_post_parent_id( $post_id );
        if ( ! $parent_id ) {
            return false;
        }

        $parent = get_post( $parent_id );
        if ( ! $parent ) {
            return false;
        }

        return in_array( $parent->post_name, $parent_slugs, true );
    }


    /**
     * Extract the slugs array from a top-level parent-pages config block.
     *
     * @param string $config_key e.g., 'attorney_parent_pages'
     * @return array
     */
    protected function parent_slugs( $config_key ) {
        if ( empty( $this->config[ $config_key ]['slugs'] )
            || ! is_array( $this->config[ $config_key ]['slugs'] )
        ) {
            return array();
        }
        return array_values( array_filter(
            $this->config[ $config_key ]['slugs'],
            function ( $s ) { return ! empty( $s ); }
        ) );
    }


    /**
     * Check whether the current page slug matches any slug in any
     * generic_pages entry.
     *
     * @return bool
     */
    protected function any_generic_page_matches() {
        if ( empty( $this->config['generic_pages'] ) || ! is_array( $this->config['generic_pages'] ) ) {
            return false;
        }

        $all_slugs = array();
        foreach ( $this->config['generic_pages'] as $entry ) {
            if ( ! empty( $entry['slugs'] ) && is_array( $entry['slugs'] ) ) {
                foreach ( $entry['slugs'] as $slug ) {
                    if ( ! empty( $slug ) ) {
                        $all_slugs[] = $slug;
                    }
                }
            }
        }

        if ( empty( $all_slugs ) ) {
            return false;
        }

        return is_page( $all_slugs );
    }


    /**
     * Collect every configured slug for a page key (across all languages)
     * and ask WordPress whether the current request matches any of them.
     *
     * Backwards-compat: if the new 'pages' structure is missing, falls
     * back to the legacy flat key (e.g., 'about_page_slug').
     *
     * @param string $key Page key, e.g., 'about_page'.
     * @return bool
     */
    protected function page_matches( $key ) {
        $slugs = $this->page_slugs( $key );

        if ( empty( $slugs ) ) {
            return false;
        }

        return is_page( $slugs );
    }


    /**
     * Return the list of slugs configured for a page key (across languages).
     * Filters out empty strings.
     *
     * @param string $key
     * @return array
     */
    protected function page_slugs( $key ) {
        if ( ! empty( $this->config['pages'][ $key ]['slugs'] )
            && is_array( $this->config['pages'][ $key ]['slugs'] )
        ) {
            return array_values(
                array_filter(
                    $this->config['pages'][ $key ]['slugs'],
                    function ( $s ) { return ! empty( $s ); }
                )
            );
        }

        // Legacy flat key fallback (e.g., 'about_page_slug')
        $legacy = $key . '_slug';
        if ( ! empty( $this->config[ $legacy ] ) ) {
            return array( $this->config[ $legacy ] );
        }

        return array();
    }


    /**
     * Check whether the current page matches any slug in any policy_pages
     * entry. Used to decide whether the policy handler should run at all.
     *
     * @return bool
     */
    protected function any_policy_page_matches() {
        if ( empty( $this->config['policy_pages'] ) || ! is_array( $this->config['policy_pages'] ) ) {
            return false;
        }

        $all_slugs = array();
        foreach ( $this->config['policy_pages'] as $policy ) {
            if ( ! empty( $policy['slugs'] ) && is_array( $policy['slugs'] ) ) {
                foreach ( $policy['slugs'] as $slug ) {
                    if ( ! empty( $slug ) ) {
                        $all_slugs[] = $slug;
                    }
                }
            }
        }

        if ( empty( $all_slugs ) ) {
            return false;
        }

        return is_page( $all_slugs );
    }
}
