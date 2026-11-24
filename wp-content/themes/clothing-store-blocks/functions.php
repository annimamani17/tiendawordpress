<?php
/**
 * Clothing Store Blocks functions and definitions
 *
 * @package Clothing Store Blocks
 */

if ( ! function_exists( 'clothing_store_blocks_setup' ) ) :
function clothing_store_blocks_setup() {
	
	if ( ! isset( $content_width ) )
		$content_width = 640; /* pixels */

    load_theme_textdomain( 'clothing-store-blocks', get_template_directory() . '/languages' );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );
	
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	
	// Add support for Block Styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );
			
	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );

    add_theme_support('woocommerce');

	// Enqueue editor styles.
	add_editor_style( array( 'assets/css/editor-style.css' ) );

	if (! defined('CLOTHING_STORE_BLOCKS_VERSION')) {
		// Replace the version number of the theme on each release.
		define('CLOTHING_STORE_BLOCKS_VERSION', wp_get_theme()->get('Version'));
	}

    require get_parent_theme_file_path( '/inc/dashboard/dashboard.php' );

	require get_parent_theme_file_path( '/inc/admin/theme-upsell.php' );

    require get_parent_theme_file_path( '/inc/customizer/customizer.php' );
	
}
endif; // clothing_store_blocks_setup
add_action( 'after_setup_theme', 'clothing_store_blocks_setup' );

function clothing_store_blocks_scripts() {
	wp_enqueue_style( 'clothing-store-blocks-basic-style', get_stylesheet_uri() );

	$clothing_store_blocks_enable_animations = get_option( 'clothing_store_blocks_enable_animations', true );

    if ( $clothing_store_blocks_enable_animations ) {
        //animation
		wp_enqueue_script( 'wow-js', get_theme_file_uri( '/assets/js/wow.js' ), array( 'jquery' ), true );

		wp_enqueue_style( 'animate-css', get_template_directory_uri().'/assets/css/animate.css' );
    }

	//font-awesome
	wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/assets/font-awesome/css/all.css', array(), '5.15.3' );

    wp_style_add_data( 'basic-style', 'rtl', 'replace' );

    //homepage slider
	wp_enqueue_style('swiper-bundle-css', get_template_directory_uri() . '/assets/css/swiper-bundle.css', array(), CLOTHING_STORE_BLOCKS_VERSION);

	wp_enqueue_script('swiper-bundle-js', get_template_directory_uri() . '/assets/js/swiper-bundle.js', array('jquery'), CLOTHING_STORE_BLOCKS_VERSION, true);

	// script.js
	wp_enqueue_script('clothing-store-blocks-main-script', get_template_directory_uri() . '/assets/js/script.js', array('jquery'), CLOTHING_STORE_BLOCKS_VERSION, true);
}
add_action( 'wp_enqueue_scripts', 'clothing_store_blocks_scripts' );

function clothing_store_blocks_enqueue_admin_script($hook) {
    // Enqueue admin JS for notices
    wp_enqueue_script('clothing-store-blocks-welcome-notice', get_template_directory_uri() . '/inc/dashboard/clothing-store-blocks-welcome-notice.js', array('jquery'), '', true);
    
    // Localize script to pass data to JavaScript
    wp_localize_script('clothing-store-blocks-welcome-notice', 'clothing_store_blocks_localize', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('clothing_store_blocks_welcome_nonce'),
        'dismiss_nonce' => wp_create_nonce('clothing_store_blocks_welcome_nonce'), // Nonce for dismissal
        'redirect_url' => admin_url('themes.php?page=clothing-store-blocks-guide-page')
    ));
}
add_action('admin_enqueue_scripts', 'clothing_store_blocks_enqueue_admin_script');

function clothing_store_blocks_admin_theme_style() {
   wp_enqueue_style('clothing-store-blocks-custom-admin-style', esc_url(get_template_directory_uri()) . '/inc/dashboard/dashboard.css');
   wp_enqueue_style('clothing-store-blocks-admin-upsell-style', esc_url(get_template_directory_uri()) . '/assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'clothing_store_blocks_admin_theme_style');

// Block Patterns.
require get_template_directory() . '/block-patterns.php';
require get_template_directory() .'/inc/TGM/tgm.php';
require get_template_directory() . '/custom-setting.php';
require_once get_template_directory() . '/inc/dashboard/welcome-notice.php';

/**
 * Redirect to Pro upsell page after theme activation
 */
add_action('after_switch_theme', 'clothing_store_blocks_redirect_after_activation');
function clothing_store_blocks_redirect_after_activation() {
    if ( ! get_option( 'clothing_store_blocks_activation_redirect', false ) ) {
        update_option( 'clothing_store_blocks_activation_redirect', true );
        wp_safe_redirect( admin_url( 'themes.php?page=clothing-store-blocks-pro' ) );
        exit;
    }
}

//woocommerce plugin skip 
add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_true' );

add_action( 'admin_bar_menu', 'clothing_store_blocks_add_upgrade_button', 100 );

function clothing_store_blocks_add_upgrade_button( $clothing_store_blocks_wp_admin_bar ) {

    $clothing_store_blocks_theme_name = wp_get_theme()->get( 'Name' );

    $clothing_store_blocks_args = array(
        'id'    => 'clothing_store_blocks_upgrade',
        'title' => '<span style="color:#fff;font-weight:600;">
            🚀 Upgrade to ' . esc_html( $clothing_store_blocks_theme_name ) . ' Pro - 20% OFF 
            <span style="background:#ff5722;color:#fff;padding:2px 8px;border-radius:3px;margin-left:6px;">
                Buy Now
            </span>
        </span>',
        'href'  => esc_url( CLOTHING_STORE_BLOCKS_BUY_PRO ),
        'meta'  => array(
            'class'  => 'clothing-store-blocks-upgrade-btn',
            'title'  => 'Upgrade to Pro',
            'target' => '_blank'
        )
    );

    $clothing_store_blocks_wp_admin_bar->add_node( $clothing_store_blocks_args );
}