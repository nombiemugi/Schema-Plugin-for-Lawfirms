<?php
/**
 * Video Library Handler
 * =================================================================
 * Outputs JSON-LD for the firm's Video Library page.
 *
 * Graph includes:
 *  - BreadcrumbList
 *  - CollectionPage           — mainEntity → ItemList
 *  - ItemList of VideoObject  — one per row in the ACF repeater on the
 *                               page. Rows without a URL are skipped to
 *                               avoid emitting partial VideoObject entities
 *                               (which fail Rich Results validation).
 *
 * Source of truth: ACF repeater field on the Video Library page. The
 * repeater field name and its subfield names are configurable via
 * config/site-config.php → pages.video_library.acf.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Firm_Legal_Video_Library extends Firm_Legal_Schema_Base {

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

        $videos = $this->collect_videos();

        $nodes = array(
            $breadcrumbs->build_for_page( $page_id, $this->permalink ),
            $this->build_collection_page( ! empty( $videos ) ),
        );

        if ( ! empty( $videos ) ) {
            $nodes[] = $this->build_item_list( $videos );
        }

        $this->output_json_ld( array(
            '@context' => 'https://schema.org',
            '@graph'   => $nodes,
        ) );
    }


    /**
     * Build the CollectionPage entity.
     *
     * @param bool $has_videos Whether an ItemList will be present.
     * @return array
     */
    protected function build_collection_page( $has_videos ) {
        $entity = array(
            '@type'      => 'CollectionPage',
            '@id'        => $this->permalink . '#webpage',
            'url'        => $this->permalink,
            'name'       => get_the_title( $this->post_id ),
            'isPartOf'   => $this->website_ref(),
            'about'      => $this->org_ref(),
            'breadcrumb' => array( '@id' => $this->permalink . '#breadcrumb' ),
            'inLanguage' => $this->lang_code,
        );

        if ( $has_videos ) {
            $entity['mainEntity'] = array( '@id' => $this->permalink . '#video-list' );
        }

        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }

        return $this->clean_entity( $entity );
    }


    /**
     * Build the ItemList wrapping the VideoObject entries.
     *
     * @param array $videos Already-built VideoObject entities.
     * @return array
     */
    protected function build_item_list( array $videos ) {
        $items = array();
        foreach ( $videos as $position => $video ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'item'     => $video,
            );
        }

        return array(
            '@type'           => 'ItemList',
            '@id'             => $this->permalink . '#video-list',
            'name'            => get_the_title( $this->post_id ),
            'numberOfItems'   => count( $items ),
            'itemListElement' => $items,
        );
    }


    /**
     * Read the ACF repeater and build VideoObject entities, one per row
     * with a valid URL.
     *
     * @return array
     */
    protected function collect_videos() {
        if ( ! function_exists( 'get_field' ) ) {
            return array();
        }

        $acf_cfg = isset( $this->config['pages']['video_library']['acf'] )
            ? $this->config['pages']['video_library']['acf']
            : array();

        $repeater_field = ! empty( $acf_cfg['repeater'] ) ? $acf_cfg['repeater'] : 'videos';
        $rows           = get_field( $repeater_field, $this->post_id );

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            return array();
        }

        $fields = array(
            'title'       => ! empty( $acf_cfg['title'] )       ? $acf_cfg['title']       : 'title',
            'url'         => ! empty( $acf_cfg['url'] )         ? $acf_cfg['url']         : 'url',
            'description' => ! empty( $acf_cfg['description'] ) ? $acf_cfg['description'] : 'description',
            'thumbnail'   => ! empty( $acf_cfg['thumbnail'] )   ? $acf_cfg['thumbnail']   : 'thumbnail',
            'upload_date' => ! empty( $acf_cfg['upload_date'] ) ? $acf_cfg['upload_date'] : 'upload_date',
            'duration'    => ! empty( $acf_cfg['duration'] )    ? $acf_cfg['duration']    : 'duration',
        );

        $videos = array();
        $index  = 0;

        foreach ( $rows as $row ) {
            $url = isset( $row[ $fields['url'] ] ) ? trim( (string) $row[ $fields['url'] ] ) : '';
            if ( $url === '' ) {
                continue;
            }

            $video = $this->build_video_object( $row, $fields, $index );
            if ( $video ) {
                $videos[] = $video;
                $index++;
            }
        }

        return $videos;
    }


    /**
     * Build a single VideoObject from one ACF repeater row.
     *
     * @param array $row    ACF row.
     * @param array $fields Resolved subfield names.
     * @param int   $index  Zero-based row index, used for the @id fallback.
     * @return array|null
     */
    protected function build_video_object( array $row, array $fields, $index ) {
        $url         = isset( $row[ $fields['url'] ] )         ? trim( (string) $row[ $fields['url'] ] ) : '';
        $title       = isset( $row[ $fields['title'] ] )       ? (string) $row[ $fields['title'] ]       : '';
        $description = isset( $row[ $fields['description'] ] ) ? (string) $row[ $fields['description'] ] : '';
        $thumbnail   = isset( $row[ $fields['thumbnail'] ] )   ? $row[ $fields['thumbnail'] ]            : null;
        $upload_date = isset( $row[ $fields['upload_date'] ] ) ? (string) $row[ $fields['upload_date'] ] : '';
        $duration    = isset( $row[ $fields['duration'] ] )    ? (string) $row[ $fields['duration'] ]    : '';

        $thumbnail_url = $this->extract_image_url( $thumbnail );
        $embed_or_url  = $this->normalize_video_url( $url );

        $video = array(
            '@type'        => 'VideoObject',
            '@id'          => $this->permalink . '#video-' . ( $index + 1 ),
            'name'         => $title !== '' ? $title : null,
            'description'  => $description !== '' ? wp_strip_all_tags( $description ) : null,
            'thumbnailUrl' => $thumbnail_url,
            'uploadDate'   => $upload_date !== '' ? $upload_date : null,
            'duration'     => $duration !== '' ? $duration : null,
            'embedUrl'     => $embed_or_url['embed'],
            'contentUrl'   => $embed_or_url['content'],
        );

        return $this->clean_entity( $video );
    }


    /**
     * Accept various ACF image return formats (array, ID, URL string) and
     * produce a single image URL string.
     *
     * @param mixed $value
     * @return string|null
     */
    protected function extract_image_url( $value ) {
        if ( empty( $value ) ) {
            return null;
        }

        if ( is_string( $value ) ) {
            return $value;
        }

        if ( is_array( $value ) ) {
            if ( ! empty( $value['url'] ) ) {
                return $value['url'];
            }
            if ( ! empty( $value['sizes']['large'] ) ) {
                return $value['sizes']['large'];
            }
        }

        if ( is_numeric( $value ) ) {
            $src = wp_get_attachment_image_src( (int) $value, 'full' );
            if ( ! empty( $src[0] ) ) {
                return $src[0];
            }
        }

        return null;
    }


    /**
     * Normalize a YouTube or Vimeo URL into both an embed URL and a
     * canonical content URL. Falls back to using the input for both
     * fields when the host isn't recognized.
     *
     * @param string $url
     * @return array { embed: string|null, content: string }
     */
    protected function normalize_video_url( $url ) {
        $url = trim( $url );
        $out = array( 'embed' => null, 'content' => $url );

        // YouTube
        if ( preg_match( '~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $m ) ) {
            $id            = $m[1];
            $out['embed']   = 'https://www.youtube.com/embed/' . $id;
            $out['content'] = 'https://www.youtube.com/watch?v=' . $id;
            return $out;
        }

        // Vimeo
        if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~', $url, $m ) ) {
            $id            = $m[1];
            $out['embed']   = 'https://player.vimeo.com/video/' . $id;
            $out['content'] = 'https://vimeo.com/' . $id;
            return $out;
        }

        return $out;
    }
}
