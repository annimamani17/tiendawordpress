<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$active_theme = wp_get_theme();
$theme_name = $active_theme->get('Name');
$theme_version = $active_theme->get('Version');
$theme_author = $active_theme->get('Author');
$theme_description = $active_theme->get('Description');
?>

<div class="ediot-theme-info" style="padding: 10px;">
    <p><strong><?php esc_html_e('Theme Name:', 'easy-demo-import-for-omega-themes'); ?></strong> <?php echo esc_html($theme_name); ?></p>
    <p><strong><?php esc_html_e('Version:', 'easy-demo-import-for-omega-themes'); ?></strong> <?php echo esc_html($theme_version); ?></p>
    <p><strong><?php esc_html_e('Author:', 'easy-demo-import-for-omega-themes'); ?></strong> <?php echo wp_kses_post($theme_author); ?></p>
    <p><strong><?php esc_html_e('Description:', 'easy-demo-import-for-omega-themes'); ?></strong> <?php echo esc_html($theme_description); ?></p>
</div>
