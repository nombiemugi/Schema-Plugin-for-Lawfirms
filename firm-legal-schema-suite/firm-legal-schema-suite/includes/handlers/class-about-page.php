<?php
/**
 * AboutPage Handler
 * =================================================================
 * Outputs JSON-LD for the firm's About page (also matches localized
 * slug variants like /es/sobre-nosotros/).
 *
 * Graph includes:
 *  - BreadcrumbList
 *  - AboutPage           — mainEntity → #organization
 *  - ImageObject         — primaryImageOfPage (when a featured image exists)
 *  - Person (optional)   — declared via AboutPage.mentions when the page
 *                          highlights a specific attorney whose @id lives
 *                          on the dedicated profile page
 *
 * References sitewide #organization and #website by @id — never redefines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_About_Page extends Firm_Legal_Schema_Base {

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

        $crumbs   = $breadcrumbs->build_for_page( $page_id, $this->permalink );
        $about    = $this->build_about_page();
        $image    = $this->build_primary_image();
        $person   = $this->build_mentioned_person();

        $nodes = array( $crumbs, $about );

        if ( $image ) {
            $nodes[] = $image;
        }
        if ( $person ) {
            $nodes[] = $person;
        }

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ) );
    }


    /**
     * Build the AboutPage entity.
     *
     * @return array
     */
    protected function build_about_page() {
        $entity = array(
            '@type'      => 'AboutPage',
            '@id'        => $this->permalink . '#aboutpage',
            'url'        => $this->permalink,
            'name'       => get_the_title( $this->post_id ),
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
            'mainEntity' => $this->org_ref(),
            'breadcrumb' => array( '@id' => $this->permalink . '#breadcrumb' ),
            'inLanguage' => $this->lang_code,
        );

        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        if ( has_post_thumbnail( $this->post_id ) ) {
            $entity['primaryImageOfPage'] = array(
                '@id' => $this->permalink . '#primaryimage',
            );
        }

        $person_id = $this->resolve_mentioned_person_id();
        if ( $person_id ) {
            $entity['mentions'] = array( '@id' => $person_id );
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Build a primary image ImageObject when the page has a featured image.
     *
     * @return array|null
     */
    protected function build_primary_image() {
        if ( ! has_post_thumbnail( $this->post_id ) ) {
            return null;
        }

        $image_url = get_the_post_thumbnail_url( $this->post_id, 'full' );
        if ( empty( $image_url ) ) {
            return null;
        }

        $caption = get_the_post_thumbnail_caption( $this->post_id );

        return $this->clean_entity( array(
            '@type'      => 'ImageObject',
            '@id'        => $this->permalink . '#primaryimage',
            'url'        => $image_url,
            'contentUrl' => $image_url,
            'caption'    => ! empty( $caption ) ? $caption : null,
        ) );
    }


    /**
     * Build a lightweight Person entity for an attorney mentioned on the
     * About page. The @id is the canonical sitewide attorney anchor — the
     * full bio still lives on the dedicated profile page.
     *
     * Returns null when no mention is configured or resolvable.
     *
     * @return array|null
     */
    protected function build_mentioned_person() {
        $person_id = $this->resolve_mentioned_person_id();
        if ( ! $person_id ) {
            return null;
        }

        $name    = $this->resolve_mentioned_person_name();
        $sameAs  = $this->resolve_mentioned_person_sameas();
        $image   = has_post_thumbnail( $this->post_id )
            ? array( '@id' => $this->permalink . '#primaryimage' )
            : null;

        $person = array(
            '@type'    => 'Person',
            '@id'      => $person_id,
            'name'     => $name,
            'jobTitle' => 'Attorney',
            'url'      => $this->permalink,
            'image'    => $image,
            'worksFor' => $this->org_ref(),
            'sameAs'   => ! empty( $sameAs ) ? $sameAs : null,
        );

        return $this->clean_entity( $person );
    }


    /**
     * Look for an ACF field that names the attorney mentioned on the page.
     * Falls back to no mention if ACF isn't installed or the field is blank.
     *
     * @return string|null Canonical attorney @id, or null.
     */
    protected function resolve_mentioned_person_id() {
        $name = $this->resolve_mentioned_person_name();
        if ( empty( $name ) ) {
            return null;
        }

        $slug = sanitize_title( remove_accents( $name ) );
        return $this->home_url . '#attorney-' . $slug;
    }


    /**
     * @return string|null
     */
    protected function resolve_mentioned_person_name() {
        if ( ! function_exists( 'get_field' ) ) {
            return null;
        }

        $name = get_field( 'about_page_mentioned_attorney', $this->post_id );
        return ! empty( $name ) ? $name : null;
    }


    /**
     * @return array Sanitized sameAs URLs from optional ACF field.
     */
    protected function resolve_mentioned_person_sameas() {
        if ( ! function_exists( 'get_field' ) ) {
            return array();
        }

        $value = get_field( 'about_page_mentioned_attorney_sameas', $this->post_id );
        if ( empty( $value ) ) {
            return array();
        }

        // Accept either a comma/newline-separated string or an array of URLs.
        if ( is_string( $value ) ) {
            $value = preg_split( '/[\r\n,]+/', $value );
        }

        $clean = array();
        foreach ( (array) $value as $url ) {
            $url = is_array( $url ) && isset( $url['url'] ) ? $url['url'] : $url;
            $url = trim( (string) $url );
            if ( $url !== '' && filter_var( $url, FILTER_VALIDATE_URL ) ) {
                $clean[] = $url;
            }
        }

        return $clean;
    }
}
