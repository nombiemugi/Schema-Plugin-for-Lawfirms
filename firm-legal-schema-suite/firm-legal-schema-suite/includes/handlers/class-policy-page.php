<?php
/**
 * Policy Page Handler
 * =================================================================
 * Single handler that covers Privacy Policy, Terms of Use, Legal
 * Disclaimers, and any other footer-style legal pages. Driven entirely
 * by the policy_pages array in config/site-config.php.
 *
 * Graph includes:
 *  - BreadcrumbList
 *  - WebPage              — mainEntity → CreativeWork (below)
 *  - CreativeWork         — the policy document itself, with a config-
 *                           supplied canonical name and fragment.
 *
 * To add a new policy page (e.g., Accessibility Statement), add an
 * entry to config['policy_pages'] — no new handler class needed.
 *
 * Note on validators: these pages are typically not Rich-Results
 * eligible. Schema Markup Validator should pass cleanly; Rich Results
 * Test will report "not eligible" — that's expected, not an error.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Policy_Page extends Firm_Legal_Schema_Base {

    public function render() {

        $page_id = $this->post_id;
        if ( ! $page_id ) {
            return;
        }

        $policy = $this->resolve_matching_policy();
        if ( ! $policy ) {
            return;
        }

        $breadcrumbs = new Firm_Legal_Breadcrumbs(
            $this->config,
            $this->lang_code,
            $this->home_label
        );

        $nodes = array(
            $breadcrumbs->build_for_page( $page_id, $this->permalink ),
            $this->build_webpage( $policy ),
        );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ) );
    }


    /**
     * Walk config['policy_pages'] and return the entry whose slugs include
     * the current page's slug. Returns null if no entry matches (the
     * router should have prevented that, but the handler is defensive).
     *
     * @return array|null
     */
    protected function resolve_matching_policy() {
        if ( empty( $this->config['policy_pages'] ) || ! is_array( $this->config['policy_pages'] ) ) {
            return null;
        }

        $current = get_post_field( 'post_name', $this->post_id );
        if ( empty( $current ) ) {
            return null;
        }

        foreach ( $this->config['policy_pages'] as $policy ) {
            if ( empty( $policy['slugs'] ) || ! is_array( $policy['slugs'] ) ) {
                continue;
            }
            foreach ( $policy['slugs'] as $slug ) {
                if ( $slug === $current ) {
                    return $policy;
                }
            }
        }

        return null;
    }


    /**
     * Build the WebPage wrapping the CreativeWork mainEntity.
     *
     * @param array $policy The matched policy_pages entry.
     * @return array
     */
    protected function build_webpage( array $policy ) {
        $fragment        = ! empty( $policy['fragment'] ) ? $policy['fragment'] : 'policy';
        $canonical_name  = $this->localize_name( $policy );
        $page_title      = get_the_title( $this->post_id );
        $excerpt         = get_the_excerpt( $this->post_id );

        $creative_work = array(
            '@type'      => 'CreativeWork',
            '@id'        => $this->permalink . '#' . $fragment,
            'name'       => $canonical_name,
            'url'        => $this->permalink,
            'publisher'  => $this->org_ref(),
            'inLanguage' => $this->lang_code,
        );

        $entity = array(
            '@type'      => 'WebPage',
            '@id'        => $this->permalink . '#webpage',
            'url'        => $this->permalink,
            'name'       => $page_title !== '' ? $page_title : $canonical_name,
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
            'mainEntity' => $creative_work,
            'breadcrumb' => array( '@id' => $this->permalink . '#breadcrumb' ),
            'inLanguage' => $this->lang_code,
        );

        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Pick the canonical name for the policy in the current page language.
     * Falls back to the English name, then to a humanized key.
     *
     * @param array $policy
     * @return string
     */
    protected function localize_name( array $policy ) {
        if ( ! empty( $policy['name'] ) && is_array( $policy['name'] ) ) {
            $lang_key = ( $this->lang_code === 'es-US' ) ? 'es' : 'en';
            if ( ! empty( $policy['name'][ $lang_key ] ) ) {
                return $policy['name'][ $lang_key ];
            }
            if ( ! empty( $policy['name']['en'] ) ) {
                return $policy['name']['en'];
            }
        }

        if ( ! empty( $policy['name'] ) && is_string( $policy['name'] ) ) {
            return $policy['name'];
        }

        if ( ! empty( $policy['key'] ) ) {
            return ucwords( str_replace( '_', ' ', $policy['key'] ) );
        }

        return 'Policy';
    }
}
