<?php
/**
 * Clothing Store Lite functions and definitions
 * @package Clothing Store Lite
 */

// Include custom functions from child theme
require get_stylesheet_directory() . '/inc/custom-functions.php';
require get_stylesheet_directory() . '/inc/homepage-setup/whizzie.php';

if ( ! function_exists( 'clothing_store_lite_after_theme_support' ) ) :

	function clothing_store_lite_after_theme_support() {

		require get_stylesheet_directory() . '/inc/homepage-setup/homepage-setup-settings.php';
		
		add_theme_support( 'automatic-feed-links' );

		add_theme_support(
			'custom-background',
			array(
				'default-color' => 'ffffff',
			)
		);

		$GLOBALS['content_width'] = apply_filters( 'clothing_store_lite_content_width', 1140 );
		
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

        load_theme_textdomain( 'clothing-store-lite', get_template_directory() . '/languages' );

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

		if (! defined( 'ONLINE_CLOTHING_STORE_DOCS_PRO' ) ){
		define('ONLINE_CLOTHING_STORE_DOCS_PRO',__('https://layout.omegathemes.com/steps/online-clothing-store-pro/','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_BUY_NOW' ) ){
		define('ONLINE_CLOTHING_STORE_BUY_NOW',__('https://www.omegathemes.com/products/clothing-store-WordPress-theme','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_SUPPORT_FREE' ) ){
		define('ONLINE_CLOTHING_STORE_SUPPORT_FREE',__('https://WordPress.org/support/theme/clothing-store-lite/','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_REVIEW_FREE' ) ){
		define('ONLINE_CLOTHING_STORE_REVIEW_FREE',__('https://WordPress.org/support/theme/clothing-store-lite/reviews/#new-post/','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_DEMO_PRO' ) ){
		define('ONLINE_CLOTHING_STORE_DEMO_PRO',__('https://layout.omegathemes.com/online-clothing-store/','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_LITE_DOCS_PRO' ) ){
		define('ONLINE_CLOTHING_STORE_LITE_DOCS_PRO',__('https://layout.omegathemes.com/steps/clothing-store-lite/','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_BUNDLE_BUTTON' ) ){
			define('ONLINE_CLOTHING_STORE_BUNDLE_BUTTON',__('https://www.omegathemes.com/products/wp-theme-bundle','clothing-store-lite'));
		}
		if (! defined( 'ONLINE_CLOTHING_STORE_HIRE_EXPERT' ) ){
			define('ONLINE_CLOTHING_STORE_HIRE_EXPERT',__('https://www.omegathemes.com/pages/contact','clothing-store-lite'));
		}

	}

endif;

add_action( 'after_setup_theme', 'clothing_store_lite_after_theme_support' );

/**
 * Register and Enqueue Styles.
 */
function clothing_store_lite_register_styles() {

	wp_enqueue_style( 'dashicons' );

	$clothing_store_lite_css = '';

	if ( get_header_image() ) :

		$clothing_store_lite_css .=  '
			.header-navbar{
				background-image: url('.esc_url(get_header_image()).');
				-webkit-background-size: cover !important;
				-moz-background-size: cover !important;
				-o-background-size: cover !important;
				background-size: cover !important;
			}';

	endif;

	wp_add_inline_style( 'clothing-store-lite-style', $clothing_store_lite_css );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'imagesloaded' );
    wp_enqueue_script( 'masonry' );
    wp_enqueue_script( 'owl.carousel', get_template_directory_uri() . '/lib/custom/js/owl.carousel.js', array('jquery'), '', 1);
	$clothing_store_lite_parentcss = 'online-clothing-store-style';
    $clothing_store_lite_theme = wp_get_theme(); 
    wp_enqueue_style( $clothing_store_lite_parentcss, get_template_directory_uri() . '/style.css', array(), $clothing_store_lite_theme->parent()->get('Version'));
    wp_enqueue_style( 'clothing-store-lite-style', get_stylesheet_uri(), array( $clothing_store_lite_parentcss ), $clothing_store_lite_theme->get('Version'));
}

add_action( 'wp_enqueue_scripts', 'clothing_store_lite_register_styles' );

function clothing_store_lite_admin_enqueue_scripts_callback() {
    if ( ! did_action( 'wp_enqueue_media' ) ) {
    wp_enqueue_media();
    }
    wp_enqueue_script('clothing-store-lite-uploaderjs', get_stylesheet_directory_uri() . '/lib/custom/js/uploader.js', array(), "1.0", true);
}
add_action( 'admin_enqueue_scripts', 'clothing_store_lite_admin_enqueue_scripts_callback' );

// NOTICE FUNCTION

/**
 * Whether the welcome notice should be shown on the current admin screen.
 */
function clothing_store_lite_show_admin_notice() {
    if ( get_option( 'clothing_store_lite_admin_notice' ) || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    $clothing_store_lite_current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( $clothing_store_lite_current_screen && 'appearance_page_clothingstorelite-wizard' === $clothing_store_lite_current_screen->base ) {
        return false;
    }

    // Once every setup step is done the notice only stays on the Dashboard.
    if ( clothing_store_lite_setup_is_complete() ) {
        return $clothing_store_lite_current_screen && 'dashboard' === $clothing_store_lite_current_screen->base;
    }

    return true;
}

/**
 * Whether every Getting Started step is done.
 */
function clothing_store_lite_setup_is_complete() {
    return function_exists( 'clothing_store_lite_get_setup_progress' ) && ! in_array( false, clothing_store_lite_get_setup_progress(), true );
}

function clothing_store_lite_admin_notice() {
    if ( ! clothing_store_lite_show_admin_notice() ) {
        return;
    }

    $clothing_store_lite_theme_args = wp_get_theme();
    $clothing_store_lite_name = $clothing_store_lite_theme_args->get( 'Name' );
    $clothing_store_lite_progress = function_exists( 'clothing_store_lite_get_setup_progress' ) ? clothing_store_lite_get_setup_progress() : array();
    $clothing_store_lite_done = count( array_filter( $clothing_store_lite_progress ) );
    $clothing_store_lite_total = max( 1, count( $clothing_store_lite_progress ) );
    $clothing_store_lite_complete = clothing_store_lite_setup_is_complete();
    $clothing_store_lite_dismiss_url = wp_nonce_url( add_query_arg( 'clothing_store_lite_admin_notice', '1' ), 'clothing_store_lite_dismiss_notice' );
    ?>
    <div class="notice omg-notice<?php echo $clothing_store_lite_complete ? ' omg-notice--complete' : ''; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'clothing_store_lite_dismiss_notice' ) ); ?>">
        <div class="omg-notice__media">
            <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/screenshot.png' ); ?>" alt="<?php echo esc_attr( $clothing_store_lite_name ); ?>">
        </div>
        <div class="omg-notice__body">
            <p class="omg-notice__eyebrow">
                <?php
                if ( $clothing_store_lite_complete ) {
                    /* translators: %s: theme name */
                    printf( esc_html__( '%s setup complete', 'clothing-store-lite' ), esc_html( $clothing_store_lite_name ) );
                } else {
                    /* translators: %s: theme name */
                    printf( esc_html__( 'Thanks for choosing %s', 'clothing-store-lite' ), esc_html( $clothing_store_lite_name ) );
                }
                ?>
            </p>
            <?php if ( $clothing_store_lite_complete ) : ?>
                <h2 class="omg-notice__title"><?php esc_html_e( 'Your store is ready', 'clothing-store-lite' ); ?></h2>
                <p class="omg-notice__text"><?php esc_html_e( 'Every setup step is done. Add your products, fine-tune the design in the Customizer, or unlock more layouts with Pro.', 'clothing-store-lite' ); ?></p>
            <?php else : ?>
                <h2 class="omg-notice__title"><?php esc_html_e( 'Let’s set up your store in a few minutes', 'clothing-store-lite' ); ?></h2>
                <p class="omg-notice__text"><?php esc_html_e( 'Install the recommended plugins, import the demo and make it yours, all from one Getting Started page.', 'clothing-store-lite' ); ?></p>
            <?php endif; ?>
            <div class="omg-notice__progress">
                <span class="omg-progress"><span class="omg-progress__bar" style="width: <?php echo esc_attr( max( 3, round( $clothing_store_lite_done / $clothing_store_lite_total * 100 ) ) ); ?>%"></span></span>
                <span class="omg-notice__count">
                    <?php
                    /* translators: 1: steps done, 2: total steps */
                    printf( esc_html__( '%1$s of %2$s steps done', 'clothing-store-lite' ), esc_html( $clothing_store_lite_done ), esc_html( $clothing_store_lite_total ) );
                    ?>
                </span>
            </div>
            <div class="omg-notice__actions">
                <?php if ( $clothing_store_lite_complete ) : ?>
                    <a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'themes.php?page=clothingstorelite-wizard' ) ); ?>"><?php esc_html_e( 'Getting Started', 'clothing-store-lite' ); ?></a>
                    <a class="omg-btn omg-btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your site', 'clothing-store-lite' ); ?></a>
                    <a class="omg-btn omg-btn--outline" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'clothing-store-lite' ); ?></a>
                <?php else : ?>
                    <a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'themes.php?page=clothingstorelite-wizard&omg-focus=1' ) ); ?>"><?php echo $clothing_store_lite_done ? esc_html__( 'Continue setup', 'clothing-store-lite' ) : esc_html__( 'Get started', 'clothing-store-lite' ); ?></a>
                <?php endif; ?>
                <a class="omg-btn omg-btn--premium" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e( 'Go To Premium', 'clothing-store-lite' ); ?></a>
                <a class="omg-btn omg-btn--outline-pink" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Get all WP themes for $69', 'clothing-store-lite' ); ?></a>
            </div>
        </div>
        <a class="omg-notice__close" href="<?php echo esc_url( $clothing_store_lite_dismiss_url ); ?>" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'clothing-store-lite' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
    </div>
    <?php
}
add_action( 'admin_notices', 'clothing_store_lite_admin_notice' );

if ( ! function_exists( 'clothing_store_lite_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss (link fallback when JS is off)
 */
function clothing_store_lite_update_admin_notice() {
    if ( isset( $_GET['clothing_store_lite_admin_notice'] ) && '1' === $_GET['clothing_store_lite_admin_notice'] && current_user_can( 'manage_options' ) ) {
        check_admin_referer( 'clothing_store_lite_dismiss_notice' );
        update_option( 'clothing_store_lite_admin_notice', true );
        wp_safe_redirect( remove_query_arg( array( 'clothing_store_lite_admin_notice', '_wpnonce' ) ) );
        exit;
    }
}
endif;
add_action( 'admin_init', 'clothing_store_lite_update_admin_notice' );

/**
 * Dismiss the welcome notice over AJAX
 */
function clothing_store_lite_dismiss_admin_notice() {
    check_ajax_referer( 'clothing_store_lite_dismiss_notice', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }
    update_option( 'clothing_store_lite_admin_notice', true );
    wp_send_json_success();
}
add_action( 'wp_ajax_clothing_store_lite_dismiss_notice', 'clothing_store_lite_dismiss_admin_notice' );

// After Switch theme function
add_action( 'after_switch_theme', 'clothing_store_lite_getstart_setup_options' );
function clothing_store_lite_getstart_setup_options() {
    update_option( 'clothing_store_lite_admin_notice', false );
}


function clothing_store_lite_remove_my_action() {
    remove_action( 'admin_notices', 'online_clothing_store_admin_notice' );
}
add_action( 'after_setup_theme', 'clothing_store_lite_remove_my_action');

function clothing_store_lite_remove_parent_theme_hooks() {
    remove_action('init', 'clothing_store_lite_remove_my_action');
}
add_action('after_setup_theme', 'clothing_store_lite_remove_parent_theme_hooks', 20);

add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );

add_action( 'init', function() {
    update_option( 'elementor_onboard_wizard_completed', 'yes' );
});