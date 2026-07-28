<?php
/**
 * Blog Index Handler
 * =================================================================
 * Outputs JSON-LD for the /blog/ index page (not individual posts —
 * those are handled by class-blog-posting.php).
 *
 * Graph includes:
 *  - BreadcrumbList
 *  - WebPage    — about → #organization, author → attorney anchor,
 *                 breadcrumb → ref
 *
 * Intentionally does NOT emit a Blog entity or enumerate recent posts.
 * Crawlers already follow links from the page; the schema's job here is
 * to identify the page type and tie it to the firm's identity graph.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Blog_Index extends Firm_Legal_Schema_Base {

    public function render() {

        $page_id = $this->post_id;
        if ( ! $page_id ) {
            return;
        }

        $breadcrumbs = new Firm_Legal_Breadcrumbs(
            $this->config,
            $this->lang_code,
            $this->home_label
        );

        $nodes = array(
            $breadcrumbs->build_for_page( $page_id, $this->permalink ),
            $this->build_webpage(),
        );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ) );
    }


    /**
     * Build the WebPage entity for the blog index.
     *
     * @return array
     */
    protected function build_webpage() {
        $author = $this->resolve_author();

        $entity = array(
            '@type'      => 'WebPage',
            '@id'        => $this->permalink . '#webpage',
            'url'        => $this->permalink,
            'name'       => get_the_title( $this->post_id ),
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
            'author'     => array( '@id' => $author['anchor'] ),
            'breadcrumb' => array( '@id' => $this->permalink . '#breadcrumb' ),
            'inLanguage' => $this->lang_code,
        );

        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        return $this->clean_entity( $entity );
    }
}
