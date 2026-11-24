<?php 

function trendy_fashion_outfits_add_admin_menu() {
    add_menu_page(
        'Theme Settings', // Page title
        'Theme Settings', // Menu title
        'manage_options', // Capability
        'trendy-fashion-outfits-theme-settings', // Menu slug
        'trendy_fashion_outfits_settings_page' // Function to display the page
    );
}
add_action( 'admin_menu', 'trendy_fashion_outfits_add_admin_menu' );

function trendy_fashion_outfits_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Theme Settings', 'trendy-fashion-outfits' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'trendy_fashion_outfits_settings_group' );
            do_settings_sections( 'trendy-fashion-outfits-theme-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function trendy_fashion_outfits_register_settings() {
    register_setting( 'trendy_fashion_outfits_settings_group', 'trendy_fashion_outfits_enable_animations' );

    add_settings_section(
        'trendy_fashion_outfits_settings_section',
        __( 'Animation Settings', 'trendy-fashion-outfits' ),
        null,
        'trendy-fashion-outfits-theme-settings'
    );

    add_settings_field(
        'trendy_fashion_outfits_enable_animations',
        __( 'Enable Animations', 'trendy-fashion-outfits' ),
        'trendy_fashion_outfits_enable_animations_callback',
        'trendy-fashion-outfits-theme-settings',
        'trendy_fashion_outfits_settings_section'
    );
}
add_action( 'admin_init', 'trendy_fashion_outfits_register_settings' );

function trendy_fashion_outfits_enable_animations_callback() {
    $checked = get_option( 'trendy_fashion_outfits_enable_animations', true );
    ?>
    <input type="checkbox" name="trendy_fashion_outfits_enable_animations" value="1" <?php checked( 1, $checked ); ?> />
    <?php
}

