<?php
/**
 * Settings for demo import
 *
 */

// A child theme with its own Getting Started page replaces this one.
if ( is_child_theme() && file_exists( get_stylesheet_directory() . '/inc/homepage-setup/homepage-setup-contents.php' ) ) {
	return;
}

/**
 * Define constants
 **/
if ( ! defined( 'WHIZZIE_DIR' ) ) {
	define( 'WHIZZIE_DIR', dirname( __FILE__ ) );
}
require trailingslashit( WHIZZIE_DIR ) . 'homepage-setup-contents.php';
$online_clothing_store_current_theme = wp_get_theme();
$online_clothing_store_theme_title = $online_clothing_store_current_theme->get( 'Name' );


/**
 * Make changes below
 **/

// Change the title and slug of your wizard page
$online_clothing_store_config['online_clothing_store_page_slug'] 	= 'online-clothing-store';
$online_clothing_store_config['online_clothing_store_page_title']	= __( 'Getting Started', 'online-clothing-store' );

// Only 'title' and 'desc' can be changed here
$online_clothing_store_config['steps'] = array(
	'plugins' => array(
		'id'	=> 'plugins',
		'title'	=> __( 'Install essential plugins', 'online-clothing-store' ),
		'desc'	=> __( 'Elementor, the demo importer and add-ons', 'online-clothing-store' ),
	),
	'demo' => array(
		'id'	=> 'demo',
		'title'	=> __( 'Import demo content', 'online-clothing-store' ),
		'desc'	=> __( 'Pages, products and the Elementor homepage', 'online-clothing-store' ),
	),
	'customize' => array(
		'id'	=> 'customize',
		'title'	=> __( 'Make it yours', 'online-clothing-store' ),
		'desc'	=> __( 'Logo, colors, header and homepage', 'online-clothing-store' ),
	),
	'launch' => array(
		'id'	=> 'launch',
		'title'	=> __( 'Launch your store', 'online-clothing-store' ),
		'desc'	=> __( 'Preview and go live', 'online-clothing-store' ),
	)
);

/**
 * This kicks off the wizard
 **/
if( class_exists( 'Online_clothing_store_Whizzie' ) ) {
	$Online_clothing_store_Whizzie = new Online_clothing_store_Whizzie( $online_clothing_store_config );
}