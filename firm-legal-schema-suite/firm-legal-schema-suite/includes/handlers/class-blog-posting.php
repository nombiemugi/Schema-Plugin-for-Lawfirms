<?php
/**
 * BlogPosting Handler
 * =================================================================
 * Outputs JSON-LD for individual blog posts.
 *
 * Graph includes:
 *  - Person (author) — lightweight, referenced by @id
 *  - BlogPosting    — main entity
 *  - BreadcrumbList — Home → Category → Post
 *
 * References sitewide #organization, #website, and #logo by @id —
 * never redefines them.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Blog_Posting extends Firm_Legal_Schema_Base {

    /**
     * Render the BlogPosting JSON-LD graph.
     */
    public function render() {

        // Defensive check — router should have already verified this
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        $author      = $this->resolve_author();
        $breadcrumbs = new Firm_Legal_Breadcrumbs(
            $this->config,
            $this->lang_code,
            $this->home_label
        );

        $person      = $this->build_person( $author );
        $blogposting = $this->build_blog_posting( $author );
        $crumbs      = $breadcrumbs->build_for_post( $this->post_id, $this->permalink );

        $graph = array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $person, $blogposting, $crumbs ),
        );

        $this->output_json_ld( $graph );
    }


    /**
     * Build the lightweight Person entity for the author.
     *
     * @param array $author Resolved author data from resolve_author().
     * @return array Person entity.
     */
    protected function build_person( array $author ) {
        return array(
            '@type'    => 'Person',
            '@id'      => $author['anchor'],
            'name'     => $author['name'],
            'url'      => $author['url'],
            'worksFor' => $this->org_ref(),
        );
    }


    /**
     * Build the BlogPosting entity with all required + optional fields.
     *
     * @param array $author Resolved author data from resolve_author().
     * @return array BlogPosting entity.
     */
    protected function build_blog_posting( array $author ) {

        $featured_image = get_the_post_thumbnail_url( $this->post_id, 'full' );

        // Fallback to default if configured
        if ( ! $featured_image && ! empty( $this->config['default_image_url'] ) ) {
            $featured_image = $this->config['default_image_url'];
        }

        $entity = array(
            '@type'            => 'BlogPosting',
            '@id'              => $this->permalink . '#blogposting',
            'headline'         => get_the_title( $this->post_id ),
            'image'            => $featured_image ?: null,
            'datePublished'    => get_the_date( 'c', $this->post_id ),
            'dateModified'     => get_the_modified_date( 'c', $this->post_id ),
            'inLanguage'       => $this->lang_code,
            'author'           => array( '@id' => $author['anchor'] ),
            'publisher'        => $this->org_ref(),
            'isPartOf'         => $this->website_ref(),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => $this->permalink,
            ),
        );

        // Optional: description from excerpt
        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        // Optional: keywords from post tags
        $tags = get_the_tags( $this->post_id );
        if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
            $tag_names         = wp_list_pluck( $tags, 'name' );
            $entity['keywords'] = implode( ', ', $tag_names );
        }

        return $this->clean_entity( $entity );
    }
}
