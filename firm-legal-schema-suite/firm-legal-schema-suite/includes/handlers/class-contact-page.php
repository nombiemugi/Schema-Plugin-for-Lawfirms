<?php
/**
 * ContactPage Handler
 * =================================================================
 * Outputs JSON-LD for the firm's Contact page (also matches localized
 * slug variants like /es/contactanos/).
 *
 * Graph includes:
 *  - BreadcrumbList
 *  - ContactPage              — mainEntity → #organization
 *  - ContactPoint (optional)  — emitted only when config supplies a
 *                               dedicated telephone for this page; for
 *                               firms where the contact page just
 *                               restates the sitewide phone, this is
 *                               left off to avoid duplicate signals.
 *
 * References sitewide #organization and #website by @id — never redefines.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Contact_Page extends Firm_Legal_Schema_Base {

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
            $this->build_contact_page(),
        );

        $contact_point = $this->build_contact_point();
        if ( $contact_point ) {
            $nodes[] = $contact_point;
        }

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ) );
    }


    /**
     * Build the ContactPage entity.
     *
     * @return array
     */
    protected function build_contact_page() {
        $entity = array(
            '@type'      => 'ContactPage',
            '@id'        => $this->permalink . '#contactpage',
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

        return $this->clean_entity( $entity );
    }


    /**
     * Build a ContactPoint when the page-specific config supplies a phone.
     * If the contact page only restates the sitewide phone, return null so
     * we don't duplicate the signal already on #organization.
     *
     * @return array|null
     */
    protected function build_contact_point() {
        $page_cfg  = isset( $this->config['pages']['contact_page'] ) ? $this->config['pages']['contact_page'] : array();
        $telephone = isset( $page_cfg['telephone'] ) ? trim( (string) $page_cfg['telephone'] ) : '';

        if ( $telephone === '' ) {
            return null;
        }

        $contact_type = ! empty( $page_cfg['contact_type'] )
            ? $page_cfg['contact_type']
            : 'customer support';

        $language_name = ( $this->lang_code === 'es-US' ) ? 'Spanish' : 'English';

        return $this->clean_entity( array(
            '@type'             => 'ContactPoint',
            '@id'               => $this->permalink . '#contactpoint',
            'contactType'       => $contact_type,
            'telephone'         => $telephone,
            'url'               => $this->permalink,
            'availableLanguage' => array(
                array(
                    '@type' => 'Language',
                    'name'  => $language_name,
                ),
            ),
        ) );
    }
}
