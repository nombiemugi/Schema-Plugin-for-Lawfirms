<?php
/**
 * Attorney Handler
 * =================================================================
 * Outputs JSON-LD for an individual attorney page.
 *
 * Detection: hierarchical-page sites where each attorney lives as a
 * child page under a parent (e.g., /meet-our-team/firstname-lastname/).
 * The router resolves the match; this handler still defends against
 * being instantiated on the wrong page.
 *
 * Graph includes:
 *  - Person          — main entity, @id matches the canonical sitewide
 *                      attorney anchor used by BlogPosting authors and
 *                      AboutPage.mentions
 *  - BreadcrumbList  — Home → Parent (e.g., Meet Our Team) → Name
 *
 * References sitewide #organization by @id — never redefines.
 *
 * Data source: native WP only — post title, content, featured image.
 * No ACF read; sites with structured attorney data should extend or
 * replace this handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Attorney extends Firm_Legal_Schema_Base {

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

        $person = $this->build_person();
        $crumbs = $breadcrumbs->build_for_page( $page_id, $this->permalink );

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $person, $crumbs ),
        ) );
    }


    /**
     * Build the Person entity for the attorney.
     *
     * @return array
     */
    protected function build_person() {

        $name        = get_the_title( $this->post_id );
        $description = $this->resolve_description();
        $image       = get_the_post_thumbnail_url( $this->post_id, 'full' );
        $job_title   = $this->resolve_job_title( $this->post_name() );

        $entity = array(
            '@type'       => 'Person',
            '@id'         => $this->build_person_id( $name, $this->permalink ),
            'name'        => $name,
            'url'         => $this->permalink,
            'image'       => $image ?: null,
            'jobTitle'    => $job_title,
            'description' => $description,
            'worksFor'    => array(
                '@type' => array( 'LegalService' ),
                '@id'   => $this->home_url . '#organization',
            ),
            'inLanguage'  => $this->lang_code,
        );

        return $this->clean_entity( $entity );
    }


    /**
     * Read an optional per-attorney job-title override from config.
     * Falls back to "Attorney".
     *
     * Config shape:
     *   'attorneys' => array(
     *       'jane-doe' => array(
     *           'job_title' => 'Founding Partner',
     *       ),
     *   ),
     *
     * @param string $page_slug
     * @return string
     */
    protected function resolve_job_title( $page_slug ) {
        if ( ! empty( $this->config['attorneys'][ $page_slug ]['job_title'] ) ) {
            return (string) $this->config['attorneys'][ $page_slug ]['job_title'];
        }
        return ( $this->lang_code === 'es-US' ) ? 'Abogado' : 'Attorney';
    }


    /**
     * Return the current post's slug. Cheaper than re-querying.
     *
     * @return string
     */
    protected function post_name() {
        $post = get_post( $this->post_id );
        return $post ? $post->post_name : '';
    }


    /**
     * Resolve a textual description: excerpt first, then first ~160 chars
     * of post content with tags stripped. Returns null when nothing usable.
     *
     * @return string|null
     */
    protected function resolve_description() {
        $excerpt = get_the_excerpt( $this->post_id );
        $excerpt = wp_strip_all_tags( (string) $excerpt );
        $excerpt = trim( $excerpt );

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
            $snippet = mb_substr( $content, 0, 160 );
        } else {
            $snippet = substr( $content, 0, 160 );
        }

        return trim( $snippet );
    }
}
