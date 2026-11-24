<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$active_theme = wp_get_theme();
$theme_name = $active_theme->get('Name');
$theme_screenshot = $active_theme->get_screenshot();
$theme_author = $active_theme->get('Author'); // get author
?>

<?php if ($theme_screenshot): ?>
  <img src="<?php echo esc_url($theme_screenshot); ?>" 
       alt="<?php echo esc_attr($theme_name); ?> Screenshot"
       style="max-width: 100%; height: auto; margin-bottom: 10px; border-radius: 10px;">
<?php endif; ?>

<h3><?php echo esc_html($theme_name); ?></h3>

<div class="ediot-btn-outer-box">
  <?php if (EDIOT_THEME_DOCUMENTATION): ?>
    <a href="<?php echo EDIOT_THEME_DOCUMENTATION ?>" class="ediot-btn" target="_blank">Theme Documentation</a>
  <?php endif; ?>

  <?php if (EDIOT_LIVE_PREVIEW): ?>
    <a href="<?php echo EDIOT_LIVE_PREVIEW ?>" class="ediot-btn" target="_blank">Live Preview</a>
  <?php endif; ?>

  <?php if (strip_tags($theme_author) === 'Omega Themes'): ?>
    <a href="<?php echo EDIOT_WEB_URL ?>products/wp-theme-bundle" class="ediot-btn" target="_blank" rel="noopener noreferrer">Get Bundle At $69</a>
    <a href="<?php echo EDIOT_WEB_URL ?>collections/responsive-wordpress-themes" class="ediot-btn" target="_blank" rel="noopener noreferrer">View All Our Themes</a>
  <?php endif; ?>
</div>