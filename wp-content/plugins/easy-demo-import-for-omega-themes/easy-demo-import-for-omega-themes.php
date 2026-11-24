<?php
 /**
 * Plugin Name:       Easy Demo Import for Omega Themes
 * Plugin URI:        
 * Description:       A lightweight One-Click Demo Import plugin built specifically for Omega Themes. Easily import demo content, widgets, and settings with a single click.
 * Version:           1.1.7
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            omegathemes
 * Author URI:        https://www.omegathemes.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       easy-demo-import-for-omega-themes
 * Requires Plugins:  
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('EDIOT_FILE', __FILE__);
define('EDIOT_DIR', plugin_dir_path(EDIOT_FILE));
define('EDIOT_URL', plugins_url('/', EDIOT_FILE));
define('EDIOT_VERSION', '1.1.7');

require_once plugin_dir_path(__FILE__) . 'core/class-easy-demo-import-for-omega-themes.php';
require_once plugin_dir_path(__FILE__) . 'core/setup-wizard-skip.php';

define( 'EDIOT_SHOPIFY_LICENCE_ENDPOINT', 'https://license.omegathemes.com/api/public/' );
define( 'EDIOT_MAIN_URL', "https://layout.omegathemes.com/" );
define( 'EDIOT_WEB_URL', "https://www.omegathemes.com/" );

define('EDIOT_THEME_DOCUMENTATION', '');
define('EDIOT_LIVE_PREVIEW', '');

function ediot_init() {
    $importer = new EDIOT_Section_Uploader();
    $importer->init();
}
add_action('plugins_loaded', 'ediot_init');