<?php
/**
 * Practice Area Handler
 * =================================================================
 * Outputs JSON-LD for an individual practice area page.
 *
 * Detection: hierarchical-page sites where each practice area lives
 * as a child page under a parent (e.g., /practice-areas/family-immigration/).
 * The router resolves the match; this handler defends against being
 * instantiated on the wrong page.
 *
 * Graph includes:
 *  - LegalService    — main entity, sub-topics surfaced via
 *                      hasOfferCatalog → OfferCatalog → Offer[]
 *  - BreadcrumbList  — Home → Parent (e.g., Practice Areas) → Title
 *
 * Sub-topic data source:
 *   $config['practice_areas'][ <page_slug> ]['subtopics'] (array of strings)
 *
 * If no subtopics are configured for the current page, hasOfferCatalog
 * is omitted (handler still emits a clean LegalService).
 *
 * References sitewide #organization by @id — never redefines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Practice_Area extends Firm_Legal_Schema_Base {

    public function render() {

        $page_id = $this->post_id;
        if ( ! $page_id || ! is_page() ) {
            return;
        }

        $breadcrumbs = new Firm_Legal_Breadcrumbs(
            $this->config,
            $this->lang_code,
            $this->home_label
        );

        $service = $this->build_legal_service();
        $crumbs  = $breadcrumbs->build_for_page( $page_id, $this->permalink );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $service, $crumbs ),
        ) );
    }


    /**
     * Build the LegalService entity for this practice area.
     *
     * @return array
     */
    protected function build_legal_service() {

        $name        = get_the_title( $this->post_id );
        $description = $this->resolve_description();
        $page_slug   = $this->post_name();
        $area_config = $this->practice_area_config( $page_slug );

        $entity = array(
            '@type'       => 'LegalService',
            '@id'         => $this->permalink . '#legalservice',
            'name'        => $name,
            'url'         => $this->permalink,
            'description' => $description,
            'provider'    => $this->org_ref(),
            'serviceType' => $name,
            'areaServed'  => $this->build_area_served( $area_config ),
            'inLanguage'  => $this->lang_code,
        );

        $catalog = $this->build_offer_catalog( $name, $area_config );
        if ( $catalog ) {
            $entity['hasOfferCatalog'] = $catalog;
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Build OfferCatalog → Offer[] from configured sub-topics.
     * Returns null when no subtopics are configured.
     *
     * @param string $service_name e.g. "Family Immigration"
     * @param array  $area_config  Config entry for this practice area
     * @return array|null
     */
    protected function build_offer_catalog( $service_name, array $area_config ) {

        if ( empty( $area_config['subtopics'] ) || ! is_array( $area_config['subtopics'] ) ) {
            return null;
        }

        $offers = array();
        $position = 1;

        foreach ( $area_config['subtopics'] as $subtopic ) {
            $subtopic = trim( (string) $subtopic );
            if ( $subtopic === '' ) {
                continue;
            }

            $fragment = sanitize_title( remove_accents( $subtopic ) );

            $offers[] = array(
                '@type'       => 'Offer',
                'position'    => $position,
                'itemOffered' => array(
                    '@type'    => 'Service',
                    '@id'      => $this->permalink . '#' . $fragment,
                    'name'     => $subtopic,
                    'url'      => $this->permalink . '#' . $fragment,
                    'provider' => $this->org_ref(),
                ),
            );

            $position++;
        }

        if ( empty( $offers ) ) {
            return null;
        }

        $catalog_label = ( $this->lang_code === 'es-US' )
            ? $service_name . ' — Servicios'
            : $service_name . ' Services';

        return array(
            '@type'             => 'OfferCatalog',
            '@id'               => $this->permalink . '#offercatalog',
            'name'              => $catalog_label,
            'itemListElement'   => $offers,
        );
    }


    /**
     * Build areaServed from config. Falls back to omitting the field
     * (returns null) when no value is configured — the global
     * #organization usually carries this signal already.
     *
     * @param array $area_config
     * @return mixed
     */
    protected function build_area_served( array $area_config ) {
        if ( empty( $area_config['area_served'] ) ) {
            return null;
        }

        $values = array_values(
            array_filter(
                array_map( 'trim', (array) $area_config['area_served'] ),
                function ( $v ) { return $v !== ''; }
            )
        );

        if ( empty( $values ) ) {
            return null;
        }
        if ( count( $values ) === 1 ) {
            return $values[0];
        }
        return $values;
    }


    /**
     * Look up the config entry for the current practice area page.
     *
     * @param string $page_slug
     * @return array
     */
    protected function practice_area_config( $page_slug ) {
        if ( empty( $page_slug )
            || empty( $this->config['practice_areas'][ $page_slug ] )
            || ! is_array( $this->config['practice_areas'][ $page_slug ] )
        ) {
            return array();
        }
        return $this->config['practice_areas'][ $page_slug ];
    }


    /**
     * @return string
     */
    protected function post_name() {
        $post = get_post( $this->post_id );
        return $post ? $post->post_name : '';
    }


    /**
     * Excerpt → falls back to first ~250 chars of stripped content.
     *
     * @return string|null
     */
    protected function resolve_description() {
        $excerpt = get_the_excerpt( $this->post_id );
        $excerpt = trim( wp_strip_all_tags( (string) $excerpt ) );

        if ( $excerpt !== '' ) {
            return $excerpt;
        }

        $post = get_post( $this->post_id );
        if ( ! $post ) {
            return null;
        }

        $content = wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) );
        $content = preg_replace( '/\s+/', ' ', $content );
        $content = trim( $content );

        if ( $content === '' ) {
            return null;
        }

        if ( function_exists( 'mb_substr' ) ) {
            $snippet = mb_substr( $content, 0, 250 );
        } else {
            $snippet = substr( $content, 0, 250 );
        }

        return trim( $snippet );
    }
}
