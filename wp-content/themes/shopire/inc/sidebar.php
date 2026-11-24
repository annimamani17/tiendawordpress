<?php
/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */

function shopire_widgets_init() {	
	if ( class_exists( 'WooCommerce' ) ) {
		register_sidebar( array(
			'name' => __( 'WooCommerce Widget Area', 'shopire' ),
			'id' => 'shopire-woocommerce-sidebar',
			'description' => __( 'This Widget area for WooCommerce Widget', 'shopire' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h5 class="widget-title">',
			'after_title' => '</h5>',
		) );
	}
	
	register_sidebar( array(
		'name' => __( 'Sidebar Widget Area', 'shopire' ),
		'id' => 'shopire-sidebar-primary',
		'description' => __( 'The Primary Widget Area', 'shopire' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h5 class="widget-title"><span></span>',
		'after_title' => '</h5>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Header Top Bar Widget Area', 'shopire' ),
		'id' => 'shopire-header-top-sidebar',
		'description' => __( 'Header Top Bar Widget Area', 'shopire' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>'
	) );
	
	register_sidebar( array(
		'name' => __( 'Header Side Docker Area', 'shopire' ),
		'id' => 'shopire-header-docker-sidebar',
		'description' => __( 'Header Side Docker Area', 'shopire' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h5 class="widget-title"><span></span>',
		'after_title' => '</h5>',
	) );
	
	$shopire_footer_widget_column = get_theme_mod('shopire_footer_widget_column','4');
	for ($i=1; $i<=$shopire_footer_widget_column; $i++) {
		register_sidebar( array(
			'name' => __( 'Footer  ', 'shopire' )  . $i,
			'id' => 'shopire-footer-widget-' . $i,
			'description' => __( 'The Footer Widget Area', 'shopire' )  . $i,
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h5 class="widget-title">',
			'after_title' => '</h5>',
		) );
	}
}
add_action( 'widgets_init', 'shopire_widgets_init' );