<?php
/**
 * Online clothing store functions and definitions
 * @package Online clothing store
 */

if ( ! function_exists( 'online_clothing_store_after_theme_support' ) ) :

	function online_clothing_store_after_theme_support() {
		
		add_theme_support( 'automatic-feed-links' );

		add_theme_support(
			'custom-background',
			array(
				'default-color' => 'ffffff',
			)
		);

		$GLOBALS['content_width'] = apply_filters( 'online_clothing_store_content_width', 1140 );
		
		add_theme_support( 'post-thumbnails' );

		add_theme_support(
			'custom-logo',
			array(
				'height'      => 270,
				'width'       => 90,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		
		add_theme_support( 'title-tag' );

        load_theme_textdomain( 'online-clothing-store', get_template_directory() . '/languages' );

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			)
		);

		add_theme_support( 'post-formats', array(
			'video',  
			'audio',  
			'gallery',
			'quote',  
			'image',  
			'link',   
			'status', 
			'aside',  
			'chat',   
		) );
		
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
        add_theme_support( 'woocommerce' );
		add_theme_support( 'wp-block-styles' );

		require get_template_directory() . '/inc/metabox.php';
		require get_template_directory() . '/inc/homepage-setup/homepage-setup-settings.php';

		if (! defined( 'ONLINE_CLOTHING_STORE_BUY_NOW' ) ){
		define('ONLINE_CLOTHING_STORE_BUY_NOW',__('https://www.omegathemes.com/products/clothing-store-WordPress-theme','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_SUPPORT_FREE' ) ){
		define('ONLINE_CLOTHING_STORE_SUPPORT_FREE',__('https://WordPress.org/support/theme/online-clothing-store/','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_DEMO_PRO' ) ){
		define('ONLINE_CLOTHING_STORE_DEMO_PRO',__('https://layout.omegathemes.com/online-clothing-store/','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_LITE_DOCS_PRO' ) ){
		define('ONLINE_CLOTHING_STORE_LITE_DOCS_PRO',__('https://layout.omegathemes.com/steps/free-online-clothing-store/','online-clothing-store'));
		}
		if (! defined('ONLINE_CLOTHING_STORE_BUNDLE_BUTTON') ){
			define('ONLINE_CLOTHING_STORE_BUNDLE_BUTTON',__('https://www.omegathemes.com/products/wp-theme-bundle','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_REVIEW_FREE' ) ){
			define('ONLINE_CLOTHING_STORE_REVIEW_FREE',__('https://WordPress.org/support/theme/online-clothing-store/reviews/#new-post','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_DOCS_PRO' ) ){
			define('ONLINE_CLOTHING_STORE_DOCS_PRO',__('https://layout.omegathemes.com/steps/online-clothing-store-pro/','online-clothing-store'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_HIRE_EXPERT' ) ){
			define('ONLINE_CLOTHING_STORE_HIRE_EXPERT',__('https://www.omegathemes.com/pages/contact','online-clothing-store'));
		}
	}

endif;

add_action( 'after_setup_theme', 'online_clothing_store_after_theme_support' );

/**
 * Register and Enqueue Styles.
 */
function online_clothing_store_register_styles() {

	wp_enqueue_style( 'dashicons' );

    $online_clothing_store_theme_version = wp_get_theme()->get( 'Version' );
	$online_clothing_store_fonts_url = online_clothing_store_fonts_url();
    if( $online_clothing_store_fonts_url ){
    	require get_theme_file_path( 'lib/custom/css/wptt-webfont-loader.php' );
        wp_enqueue_style(
			'online-clothing-store-google-fonts',
			wptt_get_webfont_url( $online_clothing_store_fonts_url ),
			array(),
			$online_clothing_store_theme_version
		);
    }
    wp_enqueue_style( 'owl.carousel', get_template_directory_uri() . '/lib/custom/css/owl.carousel.min.css');
	wp_enqueue_style( 'online-clothing-store-style', get_stylesheet_uri(), array(), $online_clothing_store_theme_version );

	wp_enqueue_style( 'online-clothing-store-style', get_stylesheet_uri() );
	require get_parent_theme_file_path( '/custom_css.php' );
	wp_add_inline_style( 'online-clothing-store-style',$online_clothing_store_custom_css );

	$online_clothing_store_css = '';

	if ( get_header_image() ) :

		$online_clothing_store_css .=  '
			.header-navbar{
				background-image: url('.esc_url(get_header_image()).');
				-webkit-background-size: cover !important;
				-moz-background-size: cover !important;
				-o-background-size: cover !important;
				background-size: cover !important;
			}';

	endif;

	wp_add_inline_style( 'online-clothing-store-style', $online_clothing_store_css );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}	

	wp_enqueue_script( 'imagesloaded' );
    wp_enqueue_script( 'masonry' );
    wp_enqueue_script( 'owl.carousel', get_template_directory_uri() . '/lib/custom/js/owl.carousel.js', array('jquery'), '', 1);
	wp_enqueue_script( 'online-clothing-store-custom', get_template_directory_uri() . '/lib/custom/js/theme-custom-script.js', array('jquery'), '', 1);
	wp_localize_script(
        'online-clothing-store-custom',
        'ajax_obj',
        array(
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );

    // Global Query
    if( is_front_page() ){

    	$online_clothing_store_posts_per_page = absint( get_option('posts_per_page') );
        $online_clothing_store_c_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
        $online_clothing_store_posts_args = array(
            'posts_per_page'        => $online_clothing_store_posts_per_page,
            'paged'                 => $online_clothing_store_c_paged,
        );
        $posts_qry = new WP_Query( $online_clothing_store_posts_args );
        $max = $posts_qry->max_num_pages;

    }else{
        global $wp_query;
        $max = $wp_query->max_num_pages;
        $online_clothing_store_c_paged = ( get_query_var( 'paged' ) > 1 ) ? get_query_var( 'paged' ) : 1;
    }

    $online_clothing_store_default = online_clothing_store_get_default_theme_options();
    $online_clothing_store_pagination_layout = get_theme_mod( 'online_clothing_store_pagination_layout',$online_clothing_store_default['online_clothing_store_pagination_layout'] );
}

add_action( 'wp_enqueue_scripts', 'online_clothing_store_register_styles',200 );

function online_clothing_store_admin_enqueue_scripts_callback() {
    if ( ! did_action( 'wp_enqueue_media' ) ) {
    wp_enqueue_media();
    }
    wp_enqueue_script('online-clothing-store-uploaderjs', get_stylesheet_directory_uri() . '/lib/custom/js/uploader.js', array(), "1.0", true);
}
add_action( 'admin_enqueue_scripts', 'online_clothing_store_admin_enqueue_scripts_callback' );

/**
 * Register navigation menus uses wp_nav_menu in five places.
 */
function online_clothing_store_menus() {

	$online_clothing_store_locations = array(
		'online-clothing-store-primary-menu'  => esc_html__( 'Primary Menu', 'online-clothing-store' ),
	);

	register_nav_menus( $online_clothing_store_locations );
}

add_action( 'init', 'online_clothing_store_menus' );

add_filter('loop_shop_columns', 'online_clothing_store_loop_columns');
if (!function_exists('online_clothing_store_loop_columns')) {
	function online_clothing_store_loop_columns() {
		$online_clothing_store_columns = get_theme_mod( 'online_clothing_store_per_columns', 3 );
		return $online_clothing_store_columns;
	}
}

add_filter( 'loop_shop_per_page', 'online_clothing_store_per_page', 20 );
function online_clothing_store_per_page( $online_clothing_store_cols ) {
  	$online_clothing_store_cols = get_theme_mod( 'online_clothing_store_product_per_page', 9 );
	return $online_clothing_store_cols;
}

function online_clothing_store_products_args( $online_clothing_store_args ) {

    $online_clothing_store_args['posts_per_page'] = get_theme_mod( 'online_clothing_store_custom_related_products_number', 6 );

    $online_clothing_store_args['columns'] = get_theme_mod( 'online_clothing_store_custom_related_products_number_per_row', 3 );

    return $online_clothing_store_args;
}

require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/classes/class-svg-icons.php';
require get_template_directory() . '/classes/class-walker-menu.php';
require get_template_directory() . '/inc/customizer/customizer.php';
require get_template_directory() . '/inc/custom-functions.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/classes/body-classes.php';
require get_template_directory() . '/inc/widgets/widgets.php';
require get_template_directory() . '/inc/pagination.php';
require get_template_directory() . '/lib/breadcrumbs/breadcrumbs.php';
require get_template_directory() . '/lib/custom/css/dynamic-style.php';
require get_template_directory() . '/inc/general.php';
require get_template_directory() . '/inc/homepage-setup/whizzie.php';

function online_clothing_store_remove_customize_register() {
    global $wp_customize;

    $wp_customize->remove_setting( 'display_header_text' );
    $wp_customize->remove_control( 'display_header_text' );

}

add_action( 'customize_register', 'online_clothing_store_remove_customize_register', 11 );

function online_clothing_store_radio_sanitize(  $online_clothing_store_input, $online_clothing_store_setting  ) {
	$online_clothing_store_input = sanitize_key( $online_clothing_store_input );
	$online_clothing_store_choices = $online_clothing_store_setting->manager->get_control( $online_clothing_store_setting->id )->choices;
	return ( array_key_exists( $online_clothing_store_input, $online_clothing_store_choices ) ? $online_clothing_store_input : $online_clothing_store_setting->default );
}

// NOTICE FUNCTION

/**
 * Whether the welcome notice should be shown on the current admin screen.
 */
function online_clothing_store_show_admin_notice() {
    if ( get_option( 'online_clothing_store_admin_notice' ) || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    $online_clothing_store_current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( $online_clothing_store_current_screen && 'appearance_page_onlineclothingstore-wizard' === $online_clothing_store_current_screen->base ) {
        return false;
    }

    // Once every setup step is done the notice only stays on the Dashboard.
    if ( online_clothing_store_setup_is_complete() ) {
        return $online_clothing_store_current_screen && 'dashboard' === $online_clothing_store_current_screen->base;
    }

    return true;
}

/**
 * Whether every Getting Started step is done.
 */
function online_clothing_store_setup_is_complete() {
    return function_exists( 'online_clothing_store_get_setup_progress' ) && ! in_array( false, online_clothing_store_get_setup_progress(), true );
}

function online_clothing_store_admin_notice() {
    if ( ! online_clothing_store_show_admin_notice() ) {
        return;
    }

    $online_clothing_store_theme_args = wp_get_theme();
    $online_clothing_store_name = $online_clothing_store_theme_args->get( 'Name' );
    $online_clothing_store_progress = function_exists( 'online_clothing_store_get_setup_progress' ) ? online_clothing_store_get_setup_progress() : array();
    $online_clothing_store_done = count( array_filter( $online_clothing_store_progress ) );
    $online_clothing_store_total = max( 1, count( $online_clothing_store_progress ) );
    $online_clothing_store_complete = online_clothing_store_setup_is_complete();
    $online_clothing_store_dismiss_url = wp_nonce_url( add_query_arg( 'online_clothing_store_admin_notice', '1' ), 'online_clothing_store_dismiss_notice' );
    ?>
    <div class="notice omg-notice<?php echo $online_clothing_store_complete ? ' omg-notice--complete' : ''; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'online_clothing_store_dismiss_notice' ) ); ?>">
        <div class="omg-notice__media">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/screenshot.png' ); ?>" alt="<?php echo esc_attr( $online_clothing_store_name ); ?>">
        </div>
        <div class="omg-notice__body">
            <p class="omg-notice__eyebrow">
                <?php
                if ( $online_clothing_store_complete ) {
                    /* translators: %s: theme name */
                    printf( esc_html__( '%s setup complete', 'online-clothing-store' ), esc_html( $online_clothing_store_name ) );
                } else {
                    /* translators: %s: theme name */
                    printf( esc_html__( 'Thanks for choosing %s', 'online-clothing-store' ), esc_html( $online_clothing_store_name ) );
                }
                ?>
            </p>
            <?php if ( $online_clothing_store_complete ) : ?>
                <h2 class="omg-notice__title"><?php esc_html_e( 'Your store is ready', 'online-clothing-store' ); ?></h2>
                <p class="omg-notice__text"><?php esc_html_e( 'Every setup step is done. Add your products, fine-tune the design in the Customizer, or unlock more layouts with Pro.', 'online-clothing-store' ); ?></p>
            <?php else : ?>
                <h2 class="omg-notice__title"><?php esc_html_e( 'Let’s set up your store in a few minutes', 'online-clothing-store' ); ?></h2>
                <p class="omg-notice__text"><?php esc_html_e( 'Install the recommended plugins, import the demo and make it yours, all from one Getting Started page.', 'online-clothing-store' ); ?></p>
            <?php endif; ?>
            <div class="omg-notice__progress">
                <span class="omg-progress"><span class="omg-progress__bar" style="width: <?php echo esc_attr( max( 3, round( $online_clothing_store_done / $online_clothing_store_total * 100 ) ) ); ?>%"></span></span>
                <span class="omg-notice__count">
                    <?php
                    /* translators: 1: steps done, 2: total steps */
                    printf( esc_html__( '%1$s of %2$s steps done', 'online-clothing-store' ), esc_html( $online_clothing_store_done ), esc_html( $online_clothing_store_total ) );
                    ?>
                </span>
            </div>
            <div class="omg-notice__actions">
                <?php if ( $online_clothing_store_complete ) : ?>
                    <a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'themes.php?page=onlineclothingstore-wizard' ) ); ?>"><?php esc_html_e( 'Getting Started', 'online-clothing-store' ); ?></a>
                    <a class="omg-btn omg-btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your site', 'online-clothing-store' ); ?></a>
                    <a class="omg-btn omg-btn--outline" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'online-clothing-store' ); ?></a>
                <?php else : ?>
                    <a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'themes.php?page=onlineclothingstore-wizard&omg-focus=1' ) ); ?>"><?php echo $online_clothing_store_done ? esc_html__( 'Continue setup', 'online-clothing-store' ) : esc_html__( 'Get started', 'online-clothing-store' ); ?></a>
                <?php endif; ?>
                <a class="omg-btn omg-btn--premium" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e( 'Go To Premium', 'online-clothing-store' ); ?></a>
                <a class="omg-btn omg-btn--outline-pink" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Get all WP themes for $69', 'online-clothing-store' ); ?></a>
            </div>
        </div>
        <a class="omg-notice__close" href="<?php echo esc_url( $online_clothing_store_dismiss_url ); ?>" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'online-clothing-store' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
    </div>
    <?php
}
add_action( 'admin_notices', 'online_clothing_store_admin_notice' );

if ( ! function_exists( 'online_clothing_store_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss (link fallback when JS is off)
 */
function online_clothing_store_update_admin_notice() {
    if ( isset( $_GET['online_clothing_store_admin_notice'] ) && '1' === $_GET['online_clothing_store_admin_notice'] && current_user_can( 'manage_options' ) ) {
        check_admin_referer( 'online_clothing_store_dismiss_notice' );
        update_option( 'online_clothing_store_admin_notice', true );
        wp_safe_redirect( remove_query_arg( array( 'online_clothing_store_admin_notice', '_wpnonce' ) ) );
        exit;
    }
}
endif;
add_action( 'admin_init', 'online_clothing_store_update_admin_notice' );

/**
 * Dismiss the welcome notice over AJAX
 */
function online_clothing_store_dismiss_admin_notice() {
    check_ajax_referer( 'online_clothing_store_dismiss_notice', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }
    update_option( 'online_clothing_store_admin_notice', true );
    wp_send_json_success();
}
add_action( 'wp_ajax_online_clothing_store_dismiss_notice', 'online_clothing_store_dismiss_admin_notice' );

// After Switch theme function
add_action( 'after_switch_theme', 'online_clothing_store_getstart_setup_options' );
function online_clothing_store_getstart_setup_options() {
    update_option( 'online_clothing_store_admin_notice', false );
}


add_action( 'wp_ajax_get_product_rating', 'online_clothing_store_get_product_rating' );
add_action( 'wp_ajax_nopriv_get_product_rating', 'online_clothing_store_get_product_rating' );

function online_clothing_store_get_product_rating() {
    if ( ! isset($_POST['product_id']) ) {
        wp_send_json_error( [ 'message' => 'No product ID given' ] );
    }

    $product_id = intval( $_POST['product_id'] );
    $product    = wc_get_product( $product_id );

    if ( ! $product ) {
        wp_send_json_error( [ 'message' => 'Invalid product' ] );
    }

    $average      = $product->get_average_rating();
    $review_count = $product->get_review_count();

    wp_send_json_success( [
        'average'      => $average,
        'review_count' => $review_count,
    ] );
}

add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );

add_action( 'init', function() {
    update_option( 'elementor_onboard_wizard_completed', 'yes' );
});