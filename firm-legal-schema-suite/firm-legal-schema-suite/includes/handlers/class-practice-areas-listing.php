<?php
/**
 * Practice Areas Listing Handler
 * =================================================================
 * Outputs JSON-LD for the "Practice Areas" listing page on
 * hierarchical-page sites (the page whose child pages are individual
 * practice areas).
 *
 * Graph includes:
 *  - CollectionPage  — main entity for the listing page
 *  - ItemList        — itemListElement[] pointing at each practice
 *                      area child page; the full LegalService entity
 *                      lives on the child page
 *  - BreadcrumbList  — Home → Practice Areas
 *
 * Child enumeration uses child_pages_of() against the configured
 * practice_area_parent_pages slug for the current language.
 *
 * References sitewide #website and #organization by @id —
 * never redefines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Practice_Areas_Listing extends Firm_Legal_Schema_Base {

    public function render() {

        $page_id = $this->post_id;
        if ( ! $page_id || ! is_page() ) {
            return;
        }

        $children = $this->collect_children();

        $breadcrumbs = new Firm_Legal_Breadcrumbs(
            $this->config,
            $this->lang_code,
            $this->home_label
        );

        $collection = $this->build_collection_page();
        $list       = $this->build_item_list( $children );
        $crumbs     = $breadcrumbs->build_for_page( $page_id, $this->permalink );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $collection, $list, $crumbs ),
        ) );
    }


    /**
     * Build the CollectionPage entity.
     *
     * @return array
     */
    protected function build_collection_page() {

        $entity = array(
            '@type'      => 'CollectionPage',
            '@id'        => $this->permalink . '#webpage',
            'url'        => $this->permalink,
            'name'       => get_the_title( $this->post_id ),
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
            'mainEntity' => array( '@id' => $this->permalink . '#itemlist' ),
            'breadcrumb' => array( '@id' => $this->permalink . '#breadcrumb' ),
            'inLanguage' => $this->lang_code,
        );

        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Build the ItemList from the supplied child pages.
     *
     * @param array $children Array of WP_Post objects
     * @return array
     */
    protected function build_item_list( array $children ) {

        $elements = array();
        $position = 1;

        foreach ( $children as $child ) {
            $url = get_permalink( $child->ID );
            if ( ! $url ) {
                continue;
            }

            $elements[] = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'url'      => $url,
                'name'     => get_the_title( $child->ID ),
            );
            $position++;
        }

        $list_name = ( $this->lang_code === 'es-US' )
            ? 'Áreas de Práctica'
            : 'Practice Areas';

        return array(
            '@type'           => 'ItemList',
            '@id'             => $this->permalink . '#itemlist',
            'name'            => $list_name,
            'itemListOrder'   => 'https://schema.org/ItemListUnordered',
            'numberOfItems'   => count( $elements ),
            'itemListElement' => $elements,
        );
    }


    /**
     * Collect practice area child pages. Tries the current page's
     * children first; falls back to a lookup against the configured
     * practice_area_parent_pages slugs.
     *
     * @return array
     */
    protected function collect_children() {

        $direct = get_pages( array(
            'parent'      => $this->post_id,
            'sort_column' => 'menu_order',
            'post_status' => 'publish',
        ) );

        if ( ! empty( $direct ) && is_array( $direct ) ) {
            return $direct;
        }

        $slugs = $this->configured_parent_slugs();
        foreach ( $slugs as $slug ) {
            $children = $this->child_pages_of( $slug );
            if ( ! empty( $children ) ) {
                return $children;
            }
        }

        return array();
    }


    /**
     * Configured parent slugs for the practice area listing (bilingual).
     *
     * @return array
     */
    protected function configured_parent_slugs() {
        if ( empty( $this->config['practice_area_parent_pages']['slugs'] )
            || ! is_array( $this->config['practice_area_parent_pages']['slugs'] )
        ) {
            return array();
        }
        return array_values( array_filter(
            $this->config['practice_area_parent_pages']['slugs'],
            function ( $s ) { return ! empty( $s ); }
        ) );
    }
}
