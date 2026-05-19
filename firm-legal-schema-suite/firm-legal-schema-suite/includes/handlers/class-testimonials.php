<?php
/**
 * Testimonials Page Handler
 * =================================================================
 * Outputs JSON-LD for the firm's Testimonials page.
 *
 * IMPORTANT: This handler intentionally emits ONLY a plain WebPage +
 * BreadcrumbList. It does NOT emit Review or AggregateRating entities.
 *
 * Why: per project conventions, we never emit Review/AggregateRating
 * unless every entry has a real attributable author and verified text.
 * Unsourced marketing quotes do not qualify. If a site genuinely has
 * structured, attributable reviews, build a separate handler that reads
 * from a verified data source — don't repurpose this one.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Testimonials extends Firm_Legal_Schema_Base {

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
