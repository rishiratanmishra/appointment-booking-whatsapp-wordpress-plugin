<?php
/**
 * Purze Cleaning Services Theme functions and definitions
 *
 * @package PurzeTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Constants.
if ( ! defined( 'PURZE_THEME_VERSION' ) ) {
	define( 'PURZE_THEME_VERSION', '1.0.0' );
}
if ( ! defined( 'PURZE_THEME_TEXTDOMAIN' ) ) {
	define( 'PURZE_THEME_TEXTDOMAIN', 'purze-theme' );
}
if ( ! defined( 'PURZE_THEME_DIR' ) ) {
	define( 'PURZE_THEME_DIR', get_stylesheet_directory() );
}
if ( ! defined( 'PURZE_THEME_URI' ) ) {
	define( 'PURZE_THEME_URI', get_stylesheet_directory_uri() );
}

// Autoload includes.
require_once PURZE_THEME_DIR . '/inc/helpers.php';
require_once PURZE_THEME_DIR . '/inc/class-theme-settings.php';
require_once PURZE_THEME_DIR . '/inc/class-hero-form.php';

/**
 * Theme setup
 */
function purze_theme_setup() {
	load_theme_textdomain( PURZE_THEME_TEXTDOMAIN, get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', [ 'height' => 60, 'width' => 200, 'flex-width' => true, 'flex-height' => true ] );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );
	add_theme_support( 'custom-header', [ 'width' => 1600, 'height' => 400, 'flex-height' => true, 'flex-width' => true ] );

	register_nav_menus(
		[
			'primary' => __( 'Primary Menu', PURZE_THEME_TEXTDOMAIN ),
		]
	);

	// Editor color palette & font sizes.
	add_theme_support(
		'editor-color-palette',
		[
			[ 'name' => __( 'Primary', PURZE_THEME_TEXTDOMAIN ), 'slug' => 'primary', 'color' => '#FF6A00' ],
			[ 'name' => __( 'Secondary', PURZE_THEME_TEXTDOMAIN ), 'slug' => 'secondary', 'color' => '#0F172A' ],
			[ 'name' => __( 'Accent', PURZE_THEME_TEXTDOMAIN ), 'slug' => 'accent', 'color' => '#F59E0B' ],
			[ 'name' => __( 'Neutral', PURZE_THEME_TEXTDOMAIN ), 'slug' => 'neutral', 'color' => '#F8FAFC' ],
		]
	);
	add_theme_support(
		'editor-font-sizes',
		[
			[ 'name' => __( 'Small', PURZE_THEME_TEXTDOMAIN ), 'size' => 14, 'slug' => 'small' ],
			[ 'name' => __( 'Normal', PURZE_THEME_TEXTDOMAIN ), 'size' => 16, 'slug' => 'normal' ],
			[ 'name' => __( 'Large', PURZE_THEME_TEXTDOMAIN ), 'size' => 20, 'slug' => 'large' ],
			[ 'name' => __( 'XL', PURZE_THEME_TEXTDOMAIN ), 'size' => 28, 'slug' => 'xl' ],
		]
	);
}
add_action( 'after_setup_theme', 'purze_theme_setup' );

/**
 * Enqueue assets
 */
function purze_enqueue_assets() {
	$ver = PURZE_THEME_VERSION;

	// Google Fonts.
	wp_enqueue_style(
		'purze-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@600;700&display=swap',
		[],
		null
	);

	// Main CSS.
	$main_css = 'assets/css/main.min.css';
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$main_css = 'assets/css/main.css';
	}
	wp_enqueue_style( 'purze-main', PURZE_THEME_URI . '/' . $main_css, [ 'purze-fonts' ], $ver );

	// Hero form JS.
	$hero_js = 'assets/js/hero-form.min.js';
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$hero_js = 'assets/js/hero-form.js';
	}
	wp_enqueue_script( 'purze-hero', PURZE_THEME_URI . '/' . $hero_js, [], $ver, true );
	wp_script_add_data( 'purze-hero', 'defer', true );

	wp_localize_script( 'purze-hero', 'purzeAjax', [
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'purze_ajax_nonce' ),
	] );
}
add_action( 'wp_enqueue_scripts', 'purze_enqueue_assets' );

/**
 * Register shortcodes
 */
function purze_register_shortcodes() {
	add_shortcode( 'theme_hero_form', [ 'Purze\\Hero_Form', 'shortcode_hero_form' ] );
	add_shortcode( 'theme_contact_form', [ 'Purze\\Hero_Form', 'shortcode_contact_form' ] );
}
add_action( 'init', 'purze_register_shortcodes' );

/**
 * AJAX endpoints
 */
add_action( 'wp_ajax_purze_submit_lead', [ 'Purze\\Hero_Form', 'handle_submit_lead' ] );
add_action( 'wp_ajax_nopriv_purze_submit_lead', [ 'Purze\\Hero_Form', 'handle_submit_lead' ] );
add_action( 'wp_ajax_purze_submit_contact', [ 'Purze\\Hero_Form', 'handle_submit_contact' ] );
add_action( 'wp_ajax_nopriv_purze_submit_contact', [ 'Purze\\Hero_Form', 'handle_submit_contact' ] );

/**
 * DB install on theme switch
 */
function purze_after_switch_theme() {
	Purze\Hero_Form::maybe_install_db();
}
add_action( 'after_switch_theme', 'purze_after_switch_theme' );

/**
 * Output JSON-LD schema
 */
function purze_output_schema() {
	$logo_id = get_theme_mod( 'custom_logo' );
	$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
	$logo_url = $logo ? $logo[0] : ( get_site_icon_url() ?: '' );
	$site_name = get_bloginfo( 'name' );
	$site_url = home_url( '/' );

	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => $site_name,
		'url'      => $site_url,
		'logo'     => $logo_url,
		'sameAs'   => [ 'https://facebook.com/', 'https://instagram.com/' ],
	];

	$website = [
		'@context' => 'https://schema.org',
		'@type'    => 'WebSite',
		'name'     => $site_name,
		'url'      => $site_url,
		'potentialAction' => [
			'@type'       => 'SearchAction',
			'target'      => $site_url . '?s={search_term_string}',
			'query-input' => 'required name=search_term_string',
		],
	];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
	echo '<script type="application/ld+json">' . wp_json_encode( $website ) . '</script>' . "\n";
}
add_action( 'wp_head', 'purze_output_schema', 5 );

/**
 * Meta tags hook (basic SEO)
 */
function purze_meta_tags() {
	echo '<meta name="theme-color" content="#FF6A00" />' . "\n";
}
add_action( 'wp_head', 'purze_meta_tags', 1 );