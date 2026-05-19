<?php
/**
 * Breadcrumbs Builder
 * =================================================================
 * Shared helper that builds BreadcrumbList entities for any page type.
 * Used by handlers via composition (not inheritance) so each handler
 * builds the breadcrumb appropriate to its page type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Breadcrumbs {

    /**
     * Per-site configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Detected language code.
     *
     * @var string
     */
    protected $lang_code;

    /**
     * Localized root label (Home or Inicio).
     *
     * @var string
     */
    protected $home_label;

    /**
     * Site home URL.
     *
     * @var string
     */
    protected $home_url;


    /**
     * @param array  $config     Per-site configuration array.
     * @param string $lang_code  Detected language code from handler.
     * @param string $home_label Localized root label from handler.
     */
    public function __construct( array $config, $lang_code, $home_label ) {
        $this->config     = $config;
        $this->lang_code  = $lang_code;
        $this->home_label = $home_label;
        $this->home_url   = home_url( '/' );
    }


    /**
     * Build breadcrumb for a blog post.
     * Hierarchy: Home → Category → Post Title
     *
     * @param int    $post_id
     * @param string $permalink
     * @return array BreadcrumbList entity
     */
    public function build_for_post( $post_id, $permalink ) {

        $items = array(
            $this->item( 1, $this->home_label, $this->home_url ),
        );

        $categories = get_the_category( $post_id );
        if ( ! empty( $categories ) ) {
            $items[] = $this->item(
                2,
                $categories[0]->name,
                get_category_link( $categories[0]->term_id )
            );
            $position = 3;
        } else {
            $position = 2;
        }

        $items[] = $this->item( $position, get_the_title( $post_id ), $permalink );

        return $this->breadcrumb_list( $permalink, $items );
    }


    /**
     * Build breadcrumb for a custom post type single (attorney, practice_area, case_result).
     * Hierarchy: Home → Archive (if exists) → Post Title
     *
     * @param int    $post_id
     * @param string $permalink
     * @param string $post_type
     * @param string $archive_label Optional archive page label (e.g., "Our Team")
     * @return array BreadcrumbList entity
     */
    public function build_for_cpt( $post_id, $permalink, $post_type, $archive_label = null ) {

        $items = array(
            $this->item( 1, $this->home_label, $this->home_url ),
        );

        $position = 2;

        $archive_url = get_post_type_archive_link( $post_type );
        if ( $archive_url && $archive_label ) {
            $items[] = $this->item( $position, $archive_label, $archive_url );
            $position++;
        }

        $items[] = $this->item( $position, get_the_title( $post_id ), $permalink );

        return $this->breadcrumb_list( $permalink, $items );
    }


    /**
     * Build breadcrumb for a static page.
     * Hierarchy: Home → Parent (if any) → Page Title
     *
     * @param int    $page_id
     * @param string $permalink
     * @return array BreadcrumbList entity
     */
    public function build_for_page( $page_id, $permalink ) {

        $items = array(
            $this->item( 1, $this->home_label, $this->home_url ),
        );

        $position = 2;

        // Walk up parent chain
        $page    = get_post( $page_id );
        $parents = array();
        while ( $page && $page->post_parent ) {
            $page      = get_post( $page->post_parent );
            $parents[] = $page;
        }
        $parents = array_reverse( $parents );

        foreach ( $parents as $parent ) {
            $items[] = $this->item(
                $position,
                get_the_title( $parent->ID ),
                get_permalink( $parent->ID )
            );
            $position++;
        }

        $items[] = $this->item( $position, get_the_title( $page_id ), $permalink );

        return $this->breadcrumb_list( $permalink, $items );
    }


    /**
     * Single ListItem helper.
     */
    protected function item( $position, $name, $url ) {
        return array(
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $name,
            'item'     => $url,
        );
    }


    /**
     * Wrap items in a BreadcrumbList entity.
     */
    protected function breadcrumb_list( $permalink, array $items ) {
        return array(
            '@type'           => 'BreadcrumbList',
            '@id'             => $permalink . '#breadcrumb',
            'itemListElement' => $items,
        );
    }
}
