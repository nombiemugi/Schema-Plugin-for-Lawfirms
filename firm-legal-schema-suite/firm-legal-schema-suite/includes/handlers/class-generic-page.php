<?php
/**
 * Generic Page Handler
 * =================================================================
 * Emits a minimal WebPage + BreadcrumbList entity for pages that don't
 * warrant a richer schema type (e.g., a blog index, an "In the Media"
 * page with only YouTube embeds, a Jobs page with no real openings).
 *
 * Use this handler instead of inventing fields we don't have. Google's
 * specialized schema types (VideoObject, JobPosting, Blog) require
 * concrete metadata (durations, employment types, etc.) that this
 * plugin will NOT fabricate.
 *
 * The router decides when this handler fires; see
 * generic_pages config block in site-config.php.
 *
 * References sitewide #website by @id — never redefines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Generic_Page extends Firm_Legal_Schema_Base {

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

        $webpage = $this->build_webpage();
        $crumbs  = $breadcrumbs->build_for_page( $page_id, $this->permalink );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $webpage, $crumbs ),
        ) );
    }


    /**
     * Build the WebPage entity.
     *
     * @return array
     */
    protected function build_webpage() {

        $entity = array(
            '@type'      => 'WebPage',
            '@id'        => $this->permalink . '#webpage',
            'url'        => $this->permalink,
            'name'       => get_the_title( $this->post_id ),
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
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
