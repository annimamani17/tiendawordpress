<?php
/**
 * Settings for demo import
 *
 */

/**
 * Define constants
 **/
if ( ! defined( 'WHIZZIE_DIR' ) ) {
	define( 'WHIZZIE_DIR', dirname( __FILE__ ) );
}
require trailingslashit( WHIZZIE_DIR ) . 'homepage-setup-contents.php';
$clothing_store_lite_current_theme = wp_get_theme();
$clothing_store_lite_theme_title = $clothing_store_lite_current_theme->get( 'Name' );


/**
 * Make changes below
 **/

// Change the title and slug of your wizard page
$clothing_store_lite_config['clothing_store_lite_page_slug'] 	= 'clothing-store-lite';
$clothing_store_lite_config['clothing_store_lite_page_title']	= __( 'Getting Started', 'clothing-store-lite' );

// Only 'title' and 'desc' can be changed here
$clothing_store_lite_config['steps'] = array(
	'plugins' => array(
		'id'	=> 'plugins',
		'title'	=> __( 'Install essential plugins', 'clothing-store-lite' ),
		'desc'	=> __( 'Elementor, the demo importer and add-ons', 'clothing-store-lite' ),
	),
	'demo' => array(
		'id'	=> 'demo',
		'title'	=> __( 'Import demo content', 'clothing-store-lite' ),
		'desc'	=> __( 'Pages, products and the Elementor homepage', 'clothing-store-lite' ),
	),
	'customize' => array(
		'id'	=> 'customize',
		'title'	=> __( 'Make it yours', 'clothing-store-lite' ),
		'desc'	=> __( 'Logo, colors, header and homepage', 'clothing-store-lite' ),
	),
	'launch' => array(
		'id'	=> 'launch',
		'title'	=> __( 'Launch your store', 'clothing-store-lite' ),
		'desc'	=> __( 'Preview and go live', 'clothing-store-lite' ),
	)
);

/**
 * This kicks off the wizard
 **/
if( class_exists( 'Clothing_Store_Lite_Whizzie' ) ) {
	$Clothing_Store_Lite_Whizzie = new Clothing_Store_Lite_Whizzie( $clothing_store_lite_config );
}