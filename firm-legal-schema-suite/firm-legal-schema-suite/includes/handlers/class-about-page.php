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
 *  - ImageObject         — primaryImageOfPage (from config image_url
 *                          when supplied, else the WP featured image)
 *  - Person (optional)   — declared via AboutPage.mentions when a
 *                          primary attorney is configured in
 *                          site-config.php under
 *                          pages.about_page.primary_attorney
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

        if ( $this->has_primary_image() ) {
            $entity['primaryImageOfPage'] = array(
                '@id' => $this->permalink . '#primaryimage',
            );
        }

        $attorney_id = $this->resolve_attorney_id();
        if ( $attorney_id ) {
            $entity['mentions'] = array( '@id' => $attorney_id );
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Build the #primaryimage ImageObject.
     *
     * Precedence:
     *  1. primary_attorney.image_url from config
     *  2. WP featured image on the About page
     *  3. null (no ImageObject emitted)
     *
     * @return array|null
     */
    protected function build_primary_image() {
        $attorney = $this->get_primary_attorney();

        if ( $attorney && ! empty( $attorney['image_url'] ) ) {
            $caption = ! empty( $attorney['image_caption'] )
                ? $attorney['image_caption']
                : $attorney['name'] . ' of ' . get_bloginfo( 'name' );

            return $this->clean_entity( array(
                '@type'      => 'ImageObject',
                '@id'        => $this->permalink . '#primaryimage',
                'url'        => $attorney['image_url'],
                'contentUrl' => $attorney['image_url'],
                'caption'    => $caption,
            ) );
        }

        if ( has_post_thumbnail( $this->post_id ) ) {
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

        return null;
    }


    /**
     * Build the Person entity for the primary attorney mentioned on the
     * page. The @id matches the canonical sitewide attorney anchor used
     * on the dedicated profile page.
     *
     * Returns null when no primary_attorney is configured.
     *
     * @return array|null
     */
    protected function build_mentioned_person() {
        $attorney = $this->get_primary_attorney();
        if ( ! $attorney ) {
            return null;
        }

        $image = $this->has_primary_image()
            ? array( '@id' => $this->permalink . '#primaryimage' )
            : null;

        $person = array(
            '@type'    => 'Person',
            '@id'      => $this->build_attorney_id( $attorney['name'] ),
            'name'     => $attorney['name'],
            'jobTitle' => ! empty( $attorney['job_title'] ) ? $attorney['job_title'] : 'Attorney',
            'url'      => $this->attorney_profile_url(),
            'image'    => $image,
            'worksFor' => array(
                '@type' => array( 'LegalService' ),
                '@id'   => $this->home_url . '#organization',
            ),
            'sameAs'   => ! empty( $attorney['same_as'] ) ? $attorney['same_as'] : null,
        );

        return $this->clean_entity( $person );
    }


    /**
     * Whether a #primaryimage ImageObject will be emitted for this page.
     * Mirrors the precedence used in build_primary_image().
     *
     * @return bool
     */
    protected function has_primary_image() {
        $attorney = $this->get_primary_attorney();
        if ( $attorney && ! empty( $attorney['image_url'] ) ) {
            return true;
        }
        return has_post_thumbnail( $this->post_id );
    }


    /**
     * Resolve the @id of the mentioned attorney, or null when no
     * primary_attorney name is configured.
     *
     * @return string|null
     */
    protected function resolve_attorney_id() {
        $attorney = $this->get_primary_attorney();
        if ( ! $attorney ) {
            return null;
        }
        return $this->build_attorney_id( $attorney['name'] );
    }


    /**
     * Build the canonical attorney @id from a display name.
     * Delegates to the shared builder so the 'person_id' config block
     * governs this handler exactly as it governs the blog author and the
     * dedicated Attorney handler.
     *
     * @param string $name
     * @return string
     */
    protected function build_attorney_id( $name ) {
        return $this->build_person_id( $name, $this->attorney_profile_url() );
    }


    /**
     * The URL that represents the attorney as a person. Defaults to the
     * About page itself; a configured primary_attorney.profile_url wins
     * when the bio actually lives on its own page.
     *
     * @return string
     */
    protected function attorney_profile_url() {
        $attorney = $this->get_primary_attorney();

        if ( $attorney && ! empty( $attorney['profile_url'] ) ) {
            return $attorney['profile_url'];
        }

        return $this->permalink;
    }


    /**
     * Read pages.about_page.primary_attorney from config and normalize.
     * Returns null when no name is configured (suppresses Person + mention).
     *
     * @return array|null
     */
    protected function get_primary_attorney() {
        if ( empty( $this->config['pages']['about_page']['primary_attorney'] ) ) {
            return null;
        }

        $raw  = $this->config['pages']['about_page']['primary_attorney'];
        $name = isset( $raw['name'] ) ? trim( (string) $raw['name'] ) : '';
        if ( $name === '' ) {
            return null;
        }

        return array(
            'name'          => $name,
            'job_title'     => isset( $raw['job_title'] ) ? trim( (string) $raw['job_title'] ) : '',
            'profile_url'   => isset( $raw['profile_url'] ) ? trim( (string) $raw['profile_url'] ) : '',
            'image_url'     => isset( $raw['image_url'] ) ? trim( (string) $raw['image_url'] ) : '',
            'image_caption' => isset( $raw['image_caption'] ) ? trim( (string) $raw['image_caption'] ) : '',
            'same_as'       => isset( $raw['same_as'] ) ? $this->sanitize_same_as( $raw['same_as'] ) : array(),
        );
    }


    /**
     * Validate and normalize a sameAs list. Accepts an array of URL strings.
     *
     * @param mixed $value
     * @return array
     */
    protected function sanitize_same_as( $value ) {
        if ( empty( $value ) ) {
            return array();
        }

        $clean = array();
        foreach ( (array) $value as $url ) {
            $url = trim( (string) $url );
            if ( $url !== '' && filter_var( $url, FILTER_VALIDATE_URL ) ) {
                $clean[] = $url;
            }
        }
        return $clean;
    }
}
