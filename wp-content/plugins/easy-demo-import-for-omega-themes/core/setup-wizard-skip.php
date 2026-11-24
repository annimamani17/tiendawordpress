<?php
defined( 'ABSPATH' ) || exit;

add_action('init', function() {
    update_option('elementor_onboarded', true);
});
// skip wizard of Royal Elementor Addons
add_filter( 'fs_redirect_on_activation_wpr-addons', '__return_false' );

add_action( 'activated_plugin', function( $plugin ) {
    if ( $plugin === 'royal-elementor-addons/wpr-addons.php' ) {
        if ( function_exists( 'wpr_fs' ) ) {
            wpr_fs()->opt_in( false, false, false, false, false );
        }
        delete_transient( 'wpr_plugin_do_activation_redirect' );
    }
} );

add_action( 'admin_init', function() {
    delete_transient( 'wpr_plugin_do_activation_redirect' );
    if ( function_exists( 'wpr_fs' ) ) {
        if ( ! wpr_fs()->is_registered() ) {
            wpr_fs()->opt_in( false, false, false, false, false );
        }
    }
}, 1 );


//Skip Header Footer Elementor setup wizard (elementskit)lite
add_action( 'activated_plugin', function( $plugins ) {
    if ( $plugins === 'header-footer-elementor/header-footer-elementor.php' ) {
        delete_option( 'hfe_start_onboarding' );
        update_option( 'hfe_onboarding_triggered', 'yes' );
    }
} );

add_action( 'admin_init', function() {
    delete_option( 'hfe_start_onboarding' );
    update_option( 'hfe_onboarding_triggered', 'yes' );
}, 1 );


//Skip Setupwizard Smash Balloon Social Photo Feed
add_action( 'activated_plugin', function( $plugin ) {
    if ( $plugin === 'instagram-feed/instagram-feed.php' ) {
        $sbi_statuses = get_option( 'sbi_statuses', array() );
        $sbi_statuses['wizard_dismissed'] = true;
        update_option( 'sbi_statuses', $sbi_statuses );

        delete_option( 'sbi_plugin_do_activation_redirect' );
    }
} );
  

add_action('admin_menu', function() {
    remove_submenu_page('sb-instagram-feed', 'sbi-setup');
}, 1);
  

add_action( 'admin_init', function() {
    $sbi_statuses = get_option( 'sbi_statuses', array() );
    $sbi_statuses['wizard_dismissed'] = true;
    update_option( 'sbi_statuses', $sbi_statuses );
  
    delete_option( 'sbi_plugin_do_activation_redirect' );
}, 1 );
  

add_action('admin_menu', function() {
    remove_submenu_page('sb-instagram-feed', 'sbi-setup');
}, 999);

// Easy property listing Setuwizard skip 
add_action( 'admin_init', 'skip_epl_wizard', 1 );
function skip_epl_wizard() {
    delete_transient( '_epl_activation_redirect' );
}


// Skip Charitable wizard 
add_action( 'activated_plugin', function( $plugin ) {
    if ( $plugin === 'charitable/charitable.php' ) {
        delete_transient( 'charitable_activation_redirect' );
        delete_transient( 'charitable_install' );
        delete_option( 'charitable_started_onboarding' );
    }
} );

add_action( 'admin_init', function() {
    delete_transient( 'charitable_activation_redirect' );
    delete_option( 'charitable_started_onboarding' );
}, 1 );


//Skip SetupWizard Wocommerce 
add_filter('woocommerce_prevent_automatic_wizard_redirect', '__return_true');