<?php
/**
 * Plugin Name:       Law Firm Legal Schema Suite
 * Plugin URI:        https://www.almostillegalads.com
 * Description:       Outputs Schema.org structured data (JSON-LD) for law firm WordPress sites. Modular architecture supports BlogPosting, AboutPage, ContactPage, Attorney (Person), Practice Area (LegalService + OfferCatalog), Team listing, Practice Areas listing, Testimonials, Video Library, Blog index, policy pages (Privacy/Terms/Disclaimers), and generic listing pages. Supports both Custom Post Type and hierarchical-page site structures. References sitewide #organization, #website, and #logo by @id — never redefines them. Bilingual slug detection (English + Spanish) with auto language resolution via Polylang, WPML, or URL pattern.
 * Version:           2.3.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Andrés Soler from AIA
 * Author URI:        https://www.almostillegalads.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       firm-legal-schema-suite
 *
 * PREREQUISITE:
 * Sitewide schema (LegalService #organization, #website, #logo) must
 * already be emitted in wp_head by another plugin or snippet.
 *
 * PER-SITE CONFIGURATION:
 * All site-specific settings live in /config/site-config.php — that's
 * the only file you should edit when deploying to a new site. Slugs
 * for static pages are declared bilingually (en + es) so the same
 * config works for English, Spanish, and bilingual sites.
 *
 * ADDING NEW SCHEMA TYPES:
 * 1. Create a handler in /includes/handlers/class-{type}.php extending Firm_Legal_Schema_Base
 * 2. Add detection logic to /includes/class-schema-router.php
 * 3. Add the schema toggle to /config/site-config.php (enabled_schemas array)
 * 4. Load the handler conditionally in this file (see "Conditional handler loading" section below)
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'FIRM_LEGAL_SCHEMA_VERSION', '2.3.0' );
define( 'FIRM_LEGAL_SCHEMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'FIRM_LEGAL_SCHEMA_URL', plugin_dir_url( __FILE__ ) );

// Load per-site configuration
$firm_legal_schema_config = require FIRM_LEGAL_SCHEMA_PATH . 'config/site-config.php';

// Load shared base class and breadcrumbs (always loaded)
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-schema-base.php';
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-breadcrumbs.php';

// ============================================================
// Conditional handler loading
// Only load handlers for schemas enabled in site-config.php
// ============================================================

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['blog_posting'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-blog-posting.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['about_page'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-about-page.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['contact_page'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-contact-page.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['testimonials'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-testimonials.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['video_library'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-video-library.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['blog_index'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-blog-index.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['policy_pages'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-policy-page.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['attorney'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-attorney.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['practice_area'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-practice-area.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['team_listing'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-team-listing.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['practice_areas_listing'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-practice-areas-listing.php';
}

if ( ! empty( $firm_legal_schema_config['enabled_schemas']['generic_pages'] ) ) {
    require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-generic-page.php';
}

// Future handlers — uncomment as they're built:
// if ( ! empty( $firm_legal_schema_config['enabled_schemas']['faq_page'] ) ) {
//     require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/handlers/class-faq-page.php';
// }

// Load router and dispatch on wp_head
require_once FIRM_LEGAL_SCHEMA_PATH . 'includes/class-schema-router.php';

add_action( 'wp_head', function () use ( $firm_legal_schema_config ) {
    $router = new Firm_Legal_Schema_Router( $firm_legal_schema_config );
    $router->dispatch();
}, 20 );
