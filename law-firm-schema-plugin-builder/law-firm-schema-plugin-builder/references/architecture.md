# Plugin Architecture Reference

Detailed class structure and code patterns for the modular law firm schema plugin.

## File Structure Overview

```
firm-legal-schema-suite/
├── firm-legal-schema-suite.php       Main file (plugin header + bootstrap)
├── config/
│   └── site-config.php               Per-site configuration array (returned)
├── includes/
│   ├── class-schema-router.php       Page-type detection and handler dispatch
│   ├── class-schema-base.php         Abstract base class with shared helpers
│   ├── class-breadcrumbs.php         Shared breadcrumb builder
│   └── handlers/
│       └── class-blog-posting.php    Each handler extends Schema_Base
└── readme.txt                        Standard WordPress plugin readme
```

## Main Plugin File Pattern

```php
<?php
/**
 * Plugin Name: Law Firm Legal Schema Suite
 * Description: ...
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FIRM_LEGAL_SCHEMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'FIRM_LEGAL_SCHEMA_URL', plugin_dir_url( __FILE__ ) );
define( 'FIRM_LEGAL_SCHEMA_VERSION', '1.0.0' );

// Load configuration
$firm_legal_schema_config = require FIRM_LEGAL_SCHEMA_PATH . 'config/site-config.php';

// Load base class first
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-schema-base.php';
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-breadcrumbs.php';

// Load handlers based on enabled schemas
if ( ! empty( $firm_legal_schema_config['enabled_schemas']['blog_posting'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-blog-posting.php';
}
// (additional handlers added here as they're built)

// Load and initialize router
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-schema-router.php';

add_action( 'wp_head', function() use ( $firm_legal_schema_config ) {
    $router = new Firm_Legal_Schema_Router( $firm_legal_schema_config );
    $router->dispatch();
}, 20 );
```

## Base Class Pattern

```php
<?php
abstract class Firm_Legal_Schema_Base {
    
    protected $config;
    protected $post_id;
    protected $permalink;
    protected $home_url;
    protected $lang_code;
    protected $home_label;
    
    public function __construct( array $config ) {
        $this->config    = $config;
        $this->home_url  = home_url( '/' );
        
        global $post;
        if ( $post ) {
            $this->post_id   = $post->ID;
            $this->permalink = get_permalink( $post->ID );
        }
        
        $this->detect_language();
    }
    
    /**
     * Language detection — priority order:
     * 1. Forced language from config
     * 2. Polylang
     * 3. WPML
     * 4. URL pattern fallback
     * 5. Default en-US
     */
    protected function detect_language() {
        if ( ! empty( $this->config['force_language'] ) ) {
            $this->lang_code = $this->config['force_language'];
        }
        elseif ( function_exists( 'pll_current_language' ) ) {
            $this->lang_code = ( pll_current_language() === 'es' ) ? 'es-US' : 'en-US';
        }
        elseif ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $this->lang_code = ( ICL_LANGUAGE_CODE === 'es' ) ? 'es-US' : 'en-US';
        }
        elseif ( $this->permalink && strpos( $this->permalink, $this->config['spanish_url_marker'] ) !== false ) {
            $this->lang_code = 'es-US';
        }
        else {
            $this->lang_code = 'en-US';
        }
        
        $this->home_label = ( $this->lang_code === 'es-US' ) ? 'Inicio' : 'Home';
    }
    
    /**
     * Author resolution: ACF custom fields first, then native WP author.
     * Returns array with name, url, and @id anchor.
     */
    protected function resolve_author() {
        global $post;
        $author_name = null;
        $author_url  = null;
        
        if ( function_exists( 'get_field' ) ) {
            $author_name = get_field( $this->config['acf_author_name_field'], $this->post_id );
            $author_url  = get_field( $this->config['acf_author_url_field'], $this->post_id );
        }
        
        if ( empty( $author_name ) ) {
            $author_name = get_the_author_meta( 'display_name', $post->post_author );
            $author_url  = get_author_posts_url( $post->post_author );
        }
        
        $slug   = sanitize_title( remove_accents( $author_name ) );
        $anchor = $this->home_url . '#attorney-' . $slug;
        
        return array(
            'name'   => $author_name,
            'url'    => $author_url,
            'anchor' => $anchor,
        );
    }
    
    /**
     * Output JSON-LD wrapped in a <script> tag, stripping null/empty values.
     */
    protected function output_json_ld( array $graph_or_entity ) {
        if ( ! isset( $graph_or_entity['@context'] ) ) {
            $graph_or_entity = array(
                '@context' => 'https://schema.org',
                '@graph'   => is_array( reset( $graph_or_entity ) ) ? $graph_or_entity : array( $graph_or_entity ),
            );
        }
        
        echo "\n" . '<script type="application/ld+json">'
            . wp_json_encode(
                $graph_or_entity,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            )
            . '</script>' . "\n";
    }
    
    /**
     * Strip null and empty-string values from an entity array.
     */
    protected function clean_entity( array $entity ) {
        return array_filter( $entity, function ( $v ) {
            return $v !== null && $v !== '';
        } );
    }
    
    /**
     * Get common references to sitewide entities.
     */
    protected function org_ref() {
        return array( '@id' => $this->home_url . '#organization' );
    }
    
    protected function website_ref() {
        return array( '@id' => $this->home_url . '#website' );
    }
    
    /**
     * Each handler must implement render() to output its schema.
     */
    abstract public function render();
}
```

## Router Pattern

```php
<?php
class Firm_Legal_Schema_Router {
    
    protected $config;
    
    public function __construct( array $config ) {
        $this->config = $config;
    }
    
    public function dispatch() {
        $handler = $this->select_handler();
        
        if ( $handler ) {
            $handler->render();
        }
    }
    
    protected function select_handler() {
        $enabled = $this->config['enabled_schemas'];
        
        // Blog posts
        if ( ! empty( $enabled['blog_posting'] ) && is_singular( 'post' ) ) {
            return new Firm_Legal_Blog_Posting( $this->config );
        }
        
        // Attorney bios
        if ( ! empty( $enabled['attorney'] ) && is_singular( $this->config['attorney_post_type'] ) ) {
            return new Firm_Legal_Attorney( $this->config );
        }
        
        // Practice areas
        if ( ! empty( $enabled['practice_area'] ) && is_singular( $this->config['practice_area_post_type'] ) ) {
            return new Firm_Legal_Practice_Area( $this->config );
        }
        
        // Contact page
        if ( ! empty( $enabled['contact_page'] ) && is_page( $this->config['contact_page_slug'] ) ) {
            return new Firm_Legal_Contact_Page( $this->config );
        }
        
        // About page
        if ( ! empty( $enabled['about_page'] ) && is_page( $this->config['about_page_slug'] ) ) {
            return new Firm_Legal_About_Page( $this->config );
        }
        
        // FAQ page
        if ( ! empty( $enabled['faq_page'] ) && is_page( $this->config['faq_page_slug'] ) ) {
            return new Firm_Legal_FAQ_Page( $this->config );
        }
        
        return null;
    }
}
```

## Handler Pattern (Example: BlogPosting)

```php
<?php
class Firm_Legal_Blog_Posting extends Firm_Legal_Schema_Base {
    
    public function render() {
        if ( ! is_singular( 'post' ) ) return;
        
        $author      = $this->resolve_author();
        $breadcrumbs = new Firm_Legal_Breadcrumbs( $this->config, $this->lang_code, $this->home_label );
        
        $blogposting = $this->build_blog_posting( $author );
        $person      = $this->build_person( $author );
        $crumbs      = $breadcrumbs->build_for_post( $this->post_id, $this->permalink );
        
        $graph = array(
            '@context' => 'https://schema.org',
            '@graph'   => array( $person, $blogposting, $crumbs ),
        );
        
        $this->output_json_ld( $graph );
    }
    
    protected function build_blog_posting( $author ) {
        $entity = array(
            '@type'            => 'BlogPosting',
            '@id'              => $this->permalink . '#blogposting',
            'headline'         => get_the_title( $this->post_id ),
            'image'            => get_the_post_thumbnail_url( $this->post_id, 'full' ) ?: null,
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
        
        $excerpt = get_the_excerpt( $this->post_id );
        if ( ! empty( $excerpt ) ) {
            $entity['description'] = wp_strip_all_tags( $excerpt );
        }
        
        $tags = get_the_tags( $this->post_id );
        if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
            $entity['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
        }
        
        return $this->clean_entity( $entity );
    }
    
    protected function build_person( $author ) {
        return array(
            '@type'    => 'Person',
            '@id'      => $author['anchor'],
            'name'     => $author['name'],
            'url'      => $author['url'],
            'worksFor' => $this->org_ref(),
        );
    }
}
```

## Adding a New Handler (Workflow)

When the user requests a new schema type:

1. **Confirm site-specific details** — what post type, what page slug, what content lives there.
2. **Create the handler file** in `includes/handlers/class-{schema-type}.php`.
3. **Extend `Firm_Legal_Schema_Base`** — get language detection and shared helpers for free.
4. **Implement `render()`** — check the appropriate WordPress conditional, build the entity, output JSON-LD.
5. **Register in the router** — add a new `if` branch in `Firm_Legal_Schema_Router::select_handler()`.
6. **Update `site-config.php`** — add to `enabled_schemas` array, plus any new config keys needed.
7. **Load conditionally in main file** — only `require_once` the handler if enabled.
8. **Update `readme.txt`** — note the new schema type and version bump.

## Class Naming Conventions

- All classes prefixed `Firm_Legal_` to avoid collisions
- Schema handlers named after their Schema.org type: `Firm_Legal_Blog_Posting`, `Firm_Legal_Attorney`, `Firm_Legal_Practice_Area`
- One class per file, file named in lowercase with hyphens: `class-blog-posting.php`
- Filenames match WordPress core conventions
