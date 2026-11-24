<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class EDIOT_Section_Uploader
{

    public function init()
    {
        add_action('wp_ajax_ediot_verify_license', array($this, 'ediot_verify_license'));
        add_action('wp_ajax_ediot_deactivate_license', array($this, 'ediot_deactivate_license'));
        add_action('admin_menu', array($this, 'ediot_add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'ediot_enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'ediot_enqueue_frontend_assets'));
        add_action('wp_ajax_ediot_import_demo_content', array($this, 'ediot_import_demo_content'));
        add_action('wp_ajax_ediot_import_menu_data', array($this, 'ediot_import_menu_data'));
        add_action('wp_ajax_ediot_get_required_plugins', array($this, 'ediot_get_required_plugins'));
        add_action('wp_ajax_ediot_install_single_plugin', array($this, 'ediot_install_single_plugin'));
        add_action('wp_ajax_ediot_import_inner_pages_data', array($this, 'ediot_import_inner_pages_data'));
        add_action('admin_head', array($this, 'ediot_hide_admin_notices'));
        add_action('admin_notices', [$this, 'omega_importer_show_license_status']); //new
        add_action('admin_init', array($this, 'ediot_handle_free_theme_redirect'));
        add_action('wp_ajax_ediot_install_elementor', array($this, 'ediot_install_elementor_ajax'));
        add_action('wp_ajax_ediot_free_themes_ajax', array($this, 'ediot_free_themes_ajax_callback'));
        add_action('wp_ajax_nopriv_ediot_free_themes_ajax', array($this, 'ediot_free_themes_ajax_callback'));
        add_action('wp_ajax_ediot_install_free_theme', array($this, 'ediot_install_and_activate_free_theme'));
    }

    public function ediot_add_admin_menu()
    {
        // Main menu
        add_menu_page(
            'Omega Importer',
            'Omega Importer',
            'manage_options',
            'ediot-template-importer',
            array($this, 'ediot_display_import_page'),
            'dashicons-forms',
            20
        );
    
        add_submenu_page(
            'ediot-template-importer',             
            'Import Templates',                   
            'Import Templates',                   
            'manage_options',                     
            'ediot-template-importer',              
            array($this, 'ediot_display_import_page') 
        );
    
        add_submenu_page(
            'ediot-template-importer',              
            'Manage Templates',                   
            'Manage Templates',                   
            'manage_options',                     
            'ediot-manage-templates',               
            array($this, 'ediot_display_manage_templates_page') 
        );

        add_submenu_page(
            'ediot-template-importer',
            'Premium Templates', 
            'Premium Templates', 
            'manage_options',    
            'ediot-premium-templates', 
            array($this, 'ediot_display_premium_templates_page') 
        );
    }

    public function ediot_display_manage_templates_page()
    {
        $file = plugin_dir_path(__FILE__) . 'ediot-manage-templates.php';
    
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<p>Error: Template management file not found.</p>';
        }
        echo '</div>';
    }


    public function ediot_display_premium_templates_page()
    {
        $file = plugin_dir_path(__FILE__) . 'ediot-premium-templates.php';
    
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<p>Error: Template management file not found.</p>';
        }
        echo '</div>';
    }

    public function ediot_hide_admin_notices()
    {
        $screen = get_current_screen();
        $ediot_screens = array(
            'toplevel_page_ediot-template-importer',
            'omega-importer_page_ediot-manage-templates',
            'omega-importer_page_ediot-premium-templates',
        );
        if ($screen && in_array($screen->id, $ediot_screens, true)) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    public static function get_theme_key() {
        return get_option('ediot_plugin_license_key', '');
    }

    public static function set_theme_key($key) {
        update_option('ediot_plugin_license_key', $key);
    }
    
    public static function remove_theme_key() {
        delete_option('ediot_plugin_license_key');
    }

    public static function set_validation_status($status) {
        update_option('ediot_plugin_license_validation_status', $status);
    }

    public static function get_validation_status() {
        return get_option('ediot_plugin_license_validation_status', 'false');
    }

    public static function set_suspension_status($status) {
        update_option('ediot_importer_theme_suspension_status', $status);
    }

    public static function get_suspension_status() {
        return get_option('ediot_importer_theme_suspension_status', 'false');
    }

    public function ediot_has_setup_widgets_function() {
        $active_theme = wp_get_theme();
        $active_theme_textdomain = $active_theme->get('TextDomain');
        $setup_widgets_function = str_replace('-', '_', $active_theme_textdomain) . '_setup_widgets';

        $target_class = '';
        if (class_exists('Whizzie') && !is_child_theme()) {
            $target_class = 'Whizzie';      
        } elseif (class_exists('Child_Whizzie')) {
            $target_class = 'Child_Whizzie';
        }

        if ($target_class === '') {
            return false;
        }
        
        return (class_exists($target_class) && method_exists($target_class, $setup_widgets_function));
    }

    public function omega_importer_show_license_status() {
        $this->omega_importer_check_license_status(); // Safe here
    
        $validation_status = EDIOT_Section_Uploader::get_validation_status();
        $display_string = get_option('omega_importer_display_string', '');
    
        if ($validation_status === 'false') {
            echo '<div class="notice notice-error"><p>License not validated!</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>License Validated Successfully.</p></div>';
        }
    
        if (!empty($display_string)) {
            echo '<div class="notice notice-info"><p>' . esc_html($display_string) . '</p></div>';
        }
    }

    public function ediot_install_elementor_ajax()
    {
         check_ajax_referer('ediot_quick_setup_nonce', 'nonce');

        $install_result = $this->ediot_install_and_activate_elementor();

        if (is_wp_error($install_result)) {
            wp_send_json_error($install_result->get_error_message());
        } else {
            wp_send_json_success();
        }
    }

    private function ediot_install_and_activate_elementor()
    {
        $plugin_slug = 'elementor/elementor.php';
        $plugin_url = 'https://downloads.wordpress.org/plugin/elementor.latest-stable.zip';
        $plugin_dir = WP_PLUGIN_DIR . '/elementor';

        if (!is_plugin_active($plugin_slug)) {
            if (file_exists($plugin_dir)) {
                $activate_result = activate_plugin($plugin_slug);
                if (is_wp_error($activate_result)) {
                    wp_send_json_error(__('Plugin activation failed: ', 'templix-template-importer') . $activate_result->get_error_message());
                }
                wp_send_json_success();
            }

            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            $upgrader = new Plugin_Upgrader();

            $install_result = $upgrader->install($plugin_url);
            if (is_wp_error($install_result)) {
                wp_send_json_error(__('Plugin installation failed: ', 'templix-template-importer') . $install_result->get_error_message());
            }

            $activate_result = activate_plugin($plugin_slug);
            if (is_wp_error($activate_result)) {
                wp_send_json_error(__('Plugin activation failed: ', 'templix-template-importer') . $activate_result->get_error_message());
            }

            wp_send_json_success();
        } else {
            wp_send_json_success();
        }
    }

    public function ediot_activate_elementor_ajax()
    {
         check_ajax_referer('ediot_quick_setup_nonce', 'nonce');
        $plugin_slug = 'elementor/elementor.php';
        $activate_result = activate_plugin($plugin_slug);

        if (is_wp_error($activate_result)) {
            wp_send_json_error($activate_result->get_error_message());
        } else {
            wp_send_json_success();
        }
    }

    public function ediot_verify_license() {
        if (defined('GET_PREMIUM_THEME')) {
            $license_key = sanitize_text_field($_POST['ediot_license_key']);
            $endpoint = EDIOT_SHOPIFY_LICENCE_ENDPOINT . 'validate-theme';
    
            $body = json_encode([
                'theme_license_key' => $license_key,
                'site_url' => site_url(),
                'theme_text_domain' => wp_get_theme()->get('TextDomain'),
            ]);
    
            $response = wp_remote_post($endpoint, [
                'body'    => $body,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
    
            if (is_wp_error($response)) {
                EDIOT_Section_Uploader::remove_theme_key();
                EDIOT_Section_Uploader::set_validation_status('false');
    
                wp_send_json([
                    'status' => false,
                    'msg'    => 'Something went wrong!',
                ]);
                exit;
            }
    
            $response_body = json_decode(wp_remote_retrieve_body($response));
    
            // Set suspension status
            if (isset($response_body->is_suspended) && $response_body->is_suspended == 1) {
                EDIOT_Section_Uploader::set_suspension_status('true');
            } else {
                EDIOT_Section_Uploader::set_suspension_status('false');
            }
    
            if (isset($response_body->status) && $response_body->status === false) {
                EDIOT_Section_Uploader::remove_theme_key();
                EDIOT_Section_Uploader::set_validation_status('false');
    
                $error_msg = isset($response_body->msg) ? $response_body->msg : 'License activation failed.';
                wp_send_json([
                    'status' => false,
                    'msg'    => $error_msg,
                ]);
                exit;
            } else {
                EDIOT_Section_Uploader::set_validation_status('true');
                EDIOT_Section_Uploader::set_theme_key($license_key);
    
                wp_send_json([
                    'status' => true,
                    'msg'    => 'Theme Activated Successfully!',
                ]);
                exit;
            }
        }
    }

    // end 

    public function ediot_deactivate_license()
    {
        delete_option('ediot_plugin_license_key');
        delete_option('ediot_plugin_license_validation_status');

        wp_send_json_success(['message' => 'License deactivated successfully.']);
    }


    public function omega_importer_check_license_status() {
        $license_key = EDIOT_Section_Uploader::get_theme_key();
    
        $body = [
            'theme_license_key' => $license_key,
            'site_url' => site_url(),
            'theme_text_domain' => wp_get_theme()->get('TextDomain')
        ];
    
        $response = wp_remote_post(EDIOT_SHOPIFY_LICENCE_ENDPOINT.'license-status', [
            'body'    => wp_json_encode($body),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    
        if (is_wp_error($response)) {
            return;
        }
    
        $response_body = json_decode(wp_remote_retrieve_body($response));
    
        if (isset($response_body->is_suspended) && $response_body->is_suspended == 1) {
            EDIOT_Section_Uploader::set_suspension_status('true');
        } else {
            EDIOT_Section_Uploader::set_suspension_status('false');
        }
    
        if (isset($response_body->status) && $response_body->status === false) {
            EDIOT_Section_Uploader::set_validation_status('false');
        } else {
            EDIOT_Section_Uploader::set_validation_status('true');
        }
    
        if (!empty($response_body->display_string)) {
            $message = str_replace("[THEME_NAME]", "Omega Importer", $response_body->display_string);
            update_option('omega_importer_display_string', $message);
        }
    }

    // end 
    
    public function ediot_import_inner_pages_data()
    {
        ob_start();
    
        check_ajax_referer('ediot_import_pages_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to import pages data.', 'easy-demo-import-for-omega-themes'));
        }
    
        $active_theme   = wp_get_theme();
        $textdomain     = $active_theme->get('TextDomain');
        $setup_function = str_replace('-', '_', $textdomain) . '_setup_widgets';
    
        if (class_exists('Whizzie') && !is_child_theme()) {
            $target_class = 'Whizzie';      
        } elseif (class_exists('Child_Whizzie')) {
            $target_class = 'Child_Whizzie';            
        } else {
            ob_clean();
            wp_send_json_error(__('Neither Child_Whizzie nor Whizzie class is available.', 'easy-demo-import-for-omega-themes'));
        }
    
        if (!method_exists($target_class, $setup_function)) {
            ob_clean();
            wp_send_json_error(__('The required setup function does not exist in the importer class.', 'easy-demo-import-for-omega-themes'));
        }
    
        try {
            $result = $target_class::$setup_function();
    
            if ($result !== false) {
                ob_clean();
                wp_send_json_success(__('Inner pages data imported successfully!', 'easy-demo-import-for-omega-themes'));
            } else {
                ob_clean();
                wp_send_json_error(__('Error occurred while importing inner pages data. Please check the logs.', 'easy-demo-import-for-omega-themes'));
            }
    
        } catch (Exception $e) {
            ob_clean();
            wp_send_json_error(sprintf(__('An error occurred: %s', 'easy-demo-import-for-omega-themes'), $e->getMessage()));
        }
    
        ob_clean();
        wp_die();
    }

    //end


    // import menu data 
    public function ediot_import_menu_data()
    {
        ob_start();

         check_ajax_referer('ediot_import_menu_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to import pages data.', 'easy-demo-import-for-omega-themes'));
        }

        $active_theme = wp_get_theme();
        $active_theme_textdomain = $active_theme->get('TextDomain');
        $setup_menu_function = str_replace('-', '_', $active_theme_textdomain) . '_setup_menu';

        if (class_exists('Whizzie') && !is_child_theme()) {
            $target_class = 'Whizzie';      
        } elseif (class_exists('Child_Whizzie')) {
            $target_class = 'Child_Whizzie';            
        } else {
            ob_clean();
            wp_send_json_error(__('Neither Child_Whizzie nor Whizzie class is available.', 'easy-demo-import-for-omega-themes'));
        }

        if (class_exists($target_class) && method_exists($target_class, $setup_menu_function)) {
            try {
                $result = $target_class::$setup_menu_function();
                if ($result !== false) {
                    ob_clean();
                    wp_send_json_success(__('All Menu imported successfully!', 'easy-demo-import-for-omega-themes'));
                } else {
                    ob_clean();
                    wp_send_json_error(__('Error occurred while importing menu data. Please check the logs.', 'easy-demo-import-for-omega-themes'));
                }
            } catch (Exception $e) {
                ob_clean();
                // translators: %s: Error message.
                $error_message = sprintf(__('An error occurred: %s', 'easy-demo-import-for-omega-themes'), $e->getMessage());

            }
        } else {
            ob_clean();
            wp_send_json_error(__('The setup function for the theme could not be found.', 'easy-demo-import-for-omega-themes'));
        }
        ob_clean();
        wp_die();
    }
    // end 


    // Get required plugins
    public function ediot_get_required_plugins()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => 'You do not have permission to install plugins.']);
        }

        // Initialize WP_Filesystem API
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            WP_Filesystem();
        }

        $plugins_file = get_template_directory() . '/inc/plugins.json';

        if ($wp_filesystem->exists($plugins_file)) {
            $plugins_json = $wp_filesystem->get_contents($plugins_file);


            if (empty($plugins_json)) {
                wp_send_json_error(['message' => 'Plugins configuration file is empty.']);
            }

            $plugins = json_decode($plugins_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(['message' => 'Invalid JSON format in plugins configuration file.']);
            }

            foreach ($plugins as $key => $plugin) {
                $main_file = !empty($plugin['main_file']) ? $plugin['main_file'] : $plugin['slug'] . '.php';
            
                $plugin_file = $plugin['slug'] . '/' . $main_file;
            
                $plugin_abs_path = plugin_dir_path(dirname(__FILE__)) . $plugin_file;
                if (!file_exists($plugin_abs_path)) {
                    $plugin_abs_path = trailingslashit(WP_PLUGIN_DIR) . $plugin_file; // Fallback
                }
            
                // Check if the plugin is installed and active
                $plugins[$key]['status'] = file_exists($plugin_abs_path) && is_plugin_active($plugin_file) ? 'installed' : 'required';
                
            }

            wp_send_json_success($plugins);
        } else {
            wp_send_json_error(['message' => 'Plugins configuration file not found.']);
        }
    }

    

    // Install and activate a single plugin
    public function ediot_install_single_plugin()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => 'You do not have permission to install plugins.']);
        }

        check_ajax_referer('ediot_quick_setup_nonce', '_ajax_nonce');

        $plugin_slug = sanitize_text_field($_POST['plugin_slug']);

        // Initialize WP_Filesystem API
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            WP_Filesystem();
        }

        $plugins_file = get_template_directory() . '/inc/plugins.json';
        $plugins_json = $wp_filesystem->exists($plugins_file) ? json_decode($wp_filesystem->get_contents($plugins_file), true) : [];
        $plugin_details = null;

        foreach ($plugins_json as $plugin) {
            if ($plugin['slug'] === $plugin_slug) {
                $plugin_details = $plugin;
                break;
            }
        }

        if (!$plugin_details) {
            wp_send_json_error(['message' => 'Plugin details not found in configuration.']);
        }

        $main_file = !empty($plugin_details['main_file']) ? $plugin_details['main_file'] : $plugin_slug . '.php';
        $plugin_file = $plugin_slug . '/' . $main_file;

        if (empty($plugin_slug)) {
            wp_send_json_error(['message' => 'Plugin slug not provided.']);
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        $upgrader = new Plugin_Upgrader();

        // If the plugin is not installed
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            $source = get_template_directory() . '/inc/plugins/' . $plugin_slug . '.zip';
            $install_result = $wp_filesystem->exists($source) ? $upgrader->install($source) : $upgrader->install('https://downloads.wordpress.org/plugin/' . $plugin_slug . '.zip');

            if (is_wp_error($install_result)) {
                wp_send_json_error(['message' => 'Installation error: ' . $install_result->get_error_message()]);
            }

            if ($upgrader->result && !is_wp_error($upgrader->result)) {
                if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $main_file)) {
                    // Activate the plugin
                    $activation = activate_plugin($plugin_file);
                    if (is_wp_error($activation)) {
                        wp_send_json_error(['message' => 'Activation error: ' . $activation->get_error_message()]);
                    }
                    wp_send_json_success(['message' => 'success.']);
                } else {
                    wp_send_json_error(['message' => 'failed.']);
                }
            } else {
                wp_send_json_error(['message' => 'failed.']);
            }
        } else {
            if (!is_plugin_active($plugin_file)) {
                $activation = activate_plugin($plugin_file);
                if (is_wp_error($activation)) {
                    wp_send_json_error(['message' => 'Activation error: ' . $activation->get_error_message()]);
                }
            }
            wp_send_json_success(['message' => 'Plugin is already installed and activated.']);
        }
    }

    public function ediot_display_import_page()
    {
         $license_key = EDIOT_Section_Uploader::get_theme_key();
         $validation_status = EDIOT_Section_Uploader::get_validation_status();
         $is_premium_theme = defined('GET_PREMIUM_THEME');
         $plugins_file = get_template_directory() . '/inc/plugins.json';
         $has_plugins_json = file_exists($plugins_file);
         $has_setup_widgets = $this->ediot_has_setup_widgets_function();
        ?>
        <div class="ediot-all-steps">
            <div class="ediot-card-all">

                <div class="ediot-card">
                    <!-- Step Navigation -->
                 <div class="ediot-activation-steps">
                    <div class="ediot-card-header">
                        <h2><?php esc_html_e('Setup Wizard', 'easy-demo-import-for-omega-themes'); ?></h2>

                    </div>
                    <!-- Step 1: Introduction -->
                    <div class="ediot-card-content" id="card-1" style="display: none;">
                        <p><?php esc_html_e('Follow these simple steps to configure your website.', 'easy-demo-import-for-omega-themes'); ?>
                        </p>
                        <button id="ediot-start-now"
                            class="button button-primary"><?php esc_html_e('Start Now', 'easy-demo-import-for-omega-themes'); ?></button>
                    </div>
                    <!-- Step 2: Check Elementor -->
                    <div class="ediot-card-content" id="card-2" style="display: none;">
                        <?php if (!is_plugin_active('elementor/elementor.php')): ?>
                            <p><?php esc_html_e('Install Elementor plugin from below', 'easy-demo-import-for-omega-themes'); ?></p>
                            <button id="ediot-install-elementor"
                                class="button button-primary"><?php esc_html_e('Install Elementor', 'easy-demo-import-for-omega-themes'); ?></button>
                        <?php else: ?>
                            <p><?php esc_html_e('Elementor is already activated.', 'easy-demo-import-for-omega-themes'); ?></p>
                            <button id="ediot-skip-button"
                                class="button button-secondary"><?php esc_html_e('Skip to Next Step', 'easy-demo-import-for-omega-themes'); ?></button>
                        <?php endif; ?>
                    </div>

                    <!-- Step 3: License Activation (Only for Premium) -->
                    <?php if ($is_premium_theme): ?>
                        <div class="ediot-card-content" id="card-3">
                            <form id="ediot_license_form">
                                <label><?php esc_html_e('Enter Your License Key:', 'easy-demo-import-for-omega-themes'); ?></label>
                                <input type="text" name="ediot_license_key" value="<?php echo esc_attr($license_key); ?>" 
                               <?php if ($validation_status === 'true') echo 'disabled'; ?> required placeholder="License Key" />
                                <div class="ediot-from-btn-wrap">
                                    <button class="button" type="submit" <?php if ($validation_status === 'true')
                                        echo 'disabled'; ?>>
                                        <?php echo ($validation_status === 'true') ? esc_html__('Activated', 'easy-demo-import-for-omega-themes') : esc_html__('Activate', 'easy-demo-import-for-omega-themes'); ?>
                                    </button>
                                    <?php if ($validation_status === 'true'): ?>
                                        <button id="ediot-change-key"
                                            class="button"><?php esc_html_e('Change Key', 'easy-demo-import-for-omega-themes'); ?></button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>    
                    <?php if ($has_plugins_json): ?>
                        <!-- Step 4: Install Plugins -->
                        <div class="ediot-card-content" id="card-4" style="display: none;">
                            <p><?php esc_html_e('Ensure required plugins are installed', 'easy-demo-import-for-omega-themes'); ?>
                            </p>
                            <div id="required-plugins-list">
                                <!-- Plugins will be dynamically inserted here -->
                            </div>
                            <button id="ediot-install-plugins"
                                class="button button-primary"><?php esc_html_e('Install Plugins', 'easy-demo-import-for-omega-themes'); ?></button>
                            <button id="ediot-skip-plugins"
                                class="button button-primary"><?php esc_html_e('Next', 'easy-demo-import-for-omega-themes'); ?></button>    
                        </div>
                    <?php endif; ?>

                    <!-- Step 5: Import Demo Content -->
                    <div class="ediot-card-content" id="card-5" style="display: none;">
                        <p><?php esc_html_e('Ready to import demo content.', 'easy-demo-import-for-omega-themes'); ?></p>
                        <?php esc_html_e(' Importing the demo content not only saves you time but also provides inspiration for customizing your own site.', 'easy-demo-import-for-omega-themes'); ?>
                        <?php esc_html_e(' Get ready to enhance your website and make it truly your own!', 'easy-demo-import-for-omega-themes'); ?><br>

                         <input type="hidden" id="ediot_import_pages_nonce_field"
                            value="<?php echo esc_attr(wp_create_nonce('ediot_import_pages_nonce')); ?>"> 
                           
                        <button id="ediot-import-pages-data"
                            class="button button-primary"><?php esc_html_e('Import Demo Content', 'easy-demo-import-for-omega-themes'); ?></button>

                        <div id="ediot-message" style="display: none; margin-top: 20px;"></div>

                        <div class="ediot-btn-wrapper">
                            <?php if ($is_premium_theme || $has_setup_widgets): ?>
                                <button id="ediot-next-card-6" class="button button-secondary"
                                    style="display:none;"><?php esc_html_e('Next', 'easy-demo-import-for-omega-themes'); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card 6: Import Pages Content -->
                    <div class="ediot-card-content" id="card-6" style="display: none;">
                        <p><?php esc_html_e('You can now import additional inner pages', 'easy-demo-import-for-omega-themes'); ?>
                        </p>

                        <ul id="ediot-page-list">
                            <?php

                            if ($is_premium_theme):
                                global $wp_filesystem;
                                if (empty($wp_filesystem)) {
                                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                                    WP_Filesystem();
                                }

                                $page_json_path = get_template_directory() . '/inc/page.json';

                                if ($wp_filesystem->exists($page_json_path)) {
                                    $inner_page_json = $wp_filesystem->get_contents($page_json_path);

                                    if (empty($inner_page_json)) {
                                        echo '<li>' . esc_html__('No pages found.', 'easy-demo-import-for-omega-themes') . '</li>';
                                    } else {
                                        $inner_page_json_decoded = json_decode($inner_page_json, true);

                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            echo '<li>' . esc_html__('Invalid JSON format in pages configuration file.', 'easy-demo-import-for-omega-themes') . '</li>';
                                        } else {
                                            foreach ($inner_page_json_decoded as $page) {
                                                echo '<li>' . esc_html($page['name']) . ' (' . esc_html($page['posttype']) . ')</li>';
                                            }
                                        }
                                    }
                                } else {
                                    echo '<li>' . esc_html__('No pages found.', 'easy-demo-import-for-omega-themes') . '</li>';
                                }
                            endif;
                            ?>
                        </ul>
                        <input type="hidden" id="ediot_import_demo_nonce_field"
                            value="<?php echo esc_attr(wp_create_nonce('ediot_import_demo_nonce')); ?>">
                        <div class="ediot-btn-wrapper">
                            <button id="ediot-start-import"
                                class="button button-primary"><?php esc_html_e('Import Pages Data', 'easy-demo-import-for-omega-themes'); ?></button>
                        </div>
                        <div id="ediot-message-pages" style="display: none; margin-top: 20px;"></div>
                        <div id="ediot-message-import" style="display: none; margin-top: 20px;"></div>
                        <?php
                          $url = home_url();
                        ?>
                        
                       <?php if ($is_premium_theme): ?>
                           <div class="ediot-btn-wrapper">
                             <button id="ediot-next-menu" class="button button-secondary" style="display:none;">
                                 <?php esc_html_e('Next', 'easy-demo-import-for-omega-themes'); ?>
                               </button>
                           </div>
                       <?php else: ?>
                           <div class="ediot-btn-wrapper">
                             <a href="<?php echo esc_url($url); ?>" target="_blank">
                               <button id="ediot-view-site-final" class="button button-secondary" style="display:none;">
                                 <?php esc_html_e('View Site', 'easy-demo-import-for-omega-themes'); ?>
                               </button>
                             </a>
                             <a href="<?php echo esc_url( admin_url() . 'admin.php?page=ediot-premium-templates' ); ?>" target="_blank">
                              <button id="ediot-view-template-final" class="button button-secondary" style="display:none;">
                                <?php esc_html_e('View Templates', 'easy-demo-import-for-omega-themes'); ?>
                              </button>
                            </a>
                           </div>
                       <?php endif; ?>

                    </div>

                    <!-- Card 7: Import Menus-->
                    <?php if ($is_premium_theme): ?>
                        <div class="ediot-card-content" id="card-7" style="display: none;">
                            <p><?php esc_html_e('You can now import navigation menus', 'easy-demo-import-for-omega-themes'); ?>
                            </p>
                            
                            <input type="hidden" id="ediot_import_menu_nonce_field"
                                value="<?php echo esc_attr(wp_create_nonce('ediot_import_menu_nonce')); ?>">
                            <div class="ediot-btn-wrapper">
                                <button id="ediot-import-menus"
                                    class="button button-primary"><?php esc_html_e('Import Menus', 'easy-demo-import-for-omega-themes'); ?></button>
                            </div>
                            <div id="ediot-message-menus" style="display: none; margin-top: 20px;"></div>
                            
                            <?php
                              $url = home_url();
                            ?>
                            
                            <a href="<?php echo esc_url($url); ?>" target="_blank">
                              <button id="ediot-view-site-final" class="button button-secondary" style="display:none;">
                                <?php esc_html_e('View Site', 'easy-demo-import-for-omega-themes'); ?>
                              </button>
                            </a>
                            <a href="<?php echo esc_url( admin_url() . 'admin.php?page=ediot-premium-templates' ); ?>" target="_blank">
                              <button id="ediot-view-template-final" class="button button-secondary" style="display:none;">
                                <?php esc_html_e('View Templates', 'easy-demo-import-for-omega-themes'); ?>
                              </button>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php
    }

    public function ediot_enqueue_frontend_assets()
    {

        wp_enqueue_style('ediot-default-styles', EDIOT_URL . '/resources/css/ediot-default.css', array(), EDIOT_VERSION);
    }

    // admin assets
    public function ediot_enqueue_admin_assets()
    {
        $screen = get_current_screen();
        $ediot_screens = array(
            'toplevel_page_ediot-template-importer',
            'omega-importer_page_ediot-manage-templates',
            'omega-importer_page_ediot-premium-templates',
        );
        if ($screen && in_array($screen->id, $ediot_screens, true))  {
            wp_enqueue_style('ediot-bootstrap-css', EDIOT_URL . 'resources/lib/bootstrap.min.css', array(), EDIOT_VERSION);
            wp_enqueue_script('ediot-bootstrap-js', EDIOT_URL . 'resources/lib/bootstrap.bundle.min.js', array('jquery'), EDIOT_VERSION, true);

            wp_enqueue_style('ediot-admin-styles', EDIOT_URL . 'resources/css/ediot-style.css', array(), EDIOT_VERSION);
            wp_enqueue_script('ediot-admin-scripts', EDIOT_URL . 'resources/js/ediot-script.js', array('jquery'), EDIOT_VERSION, true);

            wp_localize_script('ediot-admin-scripts', 'ediot_ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'ediot_nonce' => wp_create_nonce('ediot_quick_setup_nonce'),
                'plugin_url' => plugins_url('', __FILE__),
                'is_premium_theme'   => defined('GET_PREMIUM_THEME'),
                'has_setup_widgets'  => $this->ediot_has_setup_widgets_function(),
                'has_plugins_json' => file_exists(get_template_directory() . '/inc/plugins.json'),
                'ediot_nonce_obj' => array(
                    'wpnonce' => wp_create_nonce('whizzie_nonce')
                ),

            ));

            if (isset($_GET['page']) && $_GET['page'] === 'ediot-premium-templates') {
                wp_enqueue_script('ediot-template-scripts', EDIOT_URL . 'resources/js/ediot-premium-templates.js', array('jquery'), EDIOT_VERSION, true);
                wp_localize_script('ediot-template-scripts' , 'ediot_ajax_template_object', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'wpnonce' => wp_create_nonce('whizzie_nonce'),
                    'plugin_url' => plugins_url('', __FILE__),
                ));
            }
        }

        wp_enqueue_style('ediot-admin-banner-style', EDIOT_URL . 'resources/css/ediot-admin-banner-style.css', array(), EDIOT_VERSION);
 
    }


    //end

    public function ediot_import_demo_content()
    {
        check_ajax_referer('ediot_import_demo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to import demo content.', 'easy-demo-import-for-omega-themes'));
        }

        if (!is_plugin_active('elementor/elementor.php')) {
            wp_send_json_error(__('Elementor must be installed and activated before importing demo content.', 'easy-demo-import-for-omega-themes'));
        }
        $import_success = $this->ediot_elementor_importer_setup_pages();

        if ($import_success) {
            wp_send_json_success(__('Imported Successfully!', 'easy-demo-import-for-omega-themes'));
        } else {
            wp_send_json_error(__('Error occurred during the import process.', 'easy-demo-import-for-omega-themes'));
        }

        wp_die();
    }

    public function ediot_elementor_importer_setup_pages()
    {
        $active_theme = wp_get_theme();
        $active_theme_textdomain = $active_theme->get('TextDomain');

        $wpei_free_text_domain = $this->ediot_get_free_themes_text_domain();

        $theme_data = $this->ediot_get_theme_data();
        $pages_arr = [];
        $imported_pages = 0;

        if ($theme_data['status'] == 200 && !empty($theme_data['data'])) {
            foreach ($theme_data['data'] as $single_theme) {
                if ($single_theme->theme_text_domain === $active_theme_textdomain) {
                    $pages_arr[] = [
                        'title' => $single_theme->theme_page_title,
                        'ishome' => 1,
                        'post_type' => 'page',
                        'source' => $single_theme->theme_url,
                        'type' => ''
                    ];
                    break;
                }
            }
        }

        // new addd Fallback
        if (empty($pages_arr)) {
            $pages_arr[] = array(
                'title' => 'Elemento Business',
                'ishome' => 1,
                'post_type' => 'page',
                'type' => '',
                'source' => EDIOT_MAIN_URL . "json-documents/primary-template/primary-template.json"
            );
        }
        // new end

        if (file_exists(get_template_directory() . '/inc/page.json')) {
            $inner_page_json = $this->ediot_fetch_remote_content(get_template_directory_uri() . '/inc/page.json');
            $inner_page_json_decoded = json_decode($inner_page_json, true);
            foreach ($inner_page_json_decoded as $page) {
                $pages_arr[] = [
                    'title' => $page['name'],
                    'ishome' => 0, // Not the homepage
                    'post_type' => $page['posttype'],
                    'source' => $page['source'],
                    'type' => isset($page['type']) ? $page['type'] : ''
                ];
            }
        }

        foreach ($pages_arr as $page) {
            $page_content_response = wp_remote_get($page['source']);

            if (is_wp_error($page_content_response) || wp_remote_retrieve_response_code($page_content_response) !== 200) {
                continue; // Skip pages with errors
            }

            $page_content_arr = json_decode(wp_remote_retrieve_body($page_content_response), true);
            if ($page_content_arr === null || !isset($page_content_arr['content'])) {
                continue; // Skip invalid or missing content
            }

            $post_data = [
                'post_title' => $page['title'],
                'post_type' => $page['post_type'],
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_content' => '',
            ];

            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                continue; // Skip posts that fail to insert
            }

            update_post_meta($post_id, '_elementor_data', wp_slash(json_encode($page_content_arr['content'])));
            update_post_meta($post_id, '_elementor_edit_mode', 'builder');
            update_post_meta($post_id, '_elementor_template_type', $page['post_type']);

            if($page['post_type'] == 'elementskit_template'){
                update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );
                update_post_meta( $post_id, 'elementskit_template_activation', 'yes' );
                update_post_meta( $post_id, 'elementskit_template_type', $page['type'] );
                update_post_meta( $post_id, 'elementskit_template_condition_a', 'entire_site' );
            } 

            if ($page['ishome'] === 1) {
                update_option('page_on_front', $post_id);
                update_option('show_on_front', 'page');

                if (in_array($active_theme_textdomain, $wpei_free_text_domain)) {
                    add_post_meta($post_id, '_wp_page_template', 'templates/frontpage.php');
                }
            }

            $imported_pages++;
        }

        return $imported_pages > 0;
    }


    private function ediot_get_free_themes_text_domain()
    {
        $api_url = EDIOT_SHOPIFY_LICENCE_ENDPOINT . 'fetch_omega_theme_domains';
        $options = ['headers' => ['Content-Type' => 'application/json']];
        $response = wp_remote_get($api_url, $options);
        $wpei_free_text_domain = [];
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
            $json = json_decode(wp_remote_retrieve_body($response), true);

            if (is_array($json)) {
                foreach ($json as $value) {
                    if (is_array($value)) {
                        foreach ($value as $values) {
                            if (is_array($values) && isset($values['theme_domain_array'])) {
                                $get_all_domains = $values['theme_domain_array'];
                                array_push($wpei_free_text_domain, $get_all_domains);
                            } elseif (is_object($values) && isset($values->theme_domain_array)) {
                                $get_all_domains = $values->theme_domain_array;
                                array_push($wpei_free_text_domain, $get_all_domains);
                            }
                        }
                    }
                }
            }
        }

        return $wpei_free_text_domain;
    }

    // Function to fetch theme data from API
    private function ediot_get_theme_data()
    {
        $endpoint = EDIOT_SHOPIFY_LICENCE_ENDPOINT . 'retrieve_omega_themes';
        $options = ['headers' => ['Content-Type' => 'application/json']];
        $response = wp_remote_get($endpoint, $options);

        if (is_wp_error($response)) {
            return ['status' => 100, 'msg' => 'Something went wrong!', 'data' => []];
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body);
            return ['status' => 200, 'msg' => 'Theme data retrieved', 'data' => $response_data->data];
        }
    }

    // Function to fetch remote content
    private function ediot_fetch_remote_content($url)
    {
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        return wp_remote_retrieve_body($response);
    }


    // free theme 
    public function ediot_free_themes_ajax_callback() {

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        if ($page < 1) $page = 1;
    
        $url = 'https://api.wordpress.org/themes/info/1.2/';

        $args = [
            'action' => 'query_themes',
            'request[author]' => 'omegathemes',
            'request[per_page]' => 12,
            'request[page]' => $page,
        ];
        
        if (!empty($search)) {
            $args['request[search]'] = $search;
        }
    
        $full_url = add_query_arg($args, $url);
        $response = wp_remote_get($full_url);
    
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => 'Error decoding JSON']);
        }
    
        $themes = !empty($data['themes']) ? $data['themes'] : [];
        $total_pages = isset($data['info']['pages']) ? intval($data['info']['pages']) : 0;
    
        ob_start();
    
        if ($themes) {
            foreach ($themes as $theme) {
                $screenshot = esc_url($theme['screenshot_url']);
                $name = esc_html($theme['name']);
                $version = esc_html($theme['version']);
                $slug = esc_attr($theme['slug']);
                $theme_obj = wp_get_theme($slug);
                $is_installed = $theme_obj->exists();
                $is_active = ($is_installed && $theme_obj->get_stylesheet() === get_stylesheet());
                ?>
                <div class="main-grid-card-parent col-xxl-4 col-xl-6 col-lg-12 col-md-12 col-sm-6 col-12 mb-lg-5 mb-3">
                    <div class="main-grid-card-parent-inner">
                        <div class="main-grid-card-parent-inner-image-head">
                            <img class="main-grid-card-parent-inner-image" src="<?php echo $screenshot; ?>" alt="<?php echo $name; ?>">
                        </div>
                        <div class="main-grid-card-parent-inner-description">
                            <h3><?php echo $name; ?></h3>
                            <h6>Version: <strong><?php echo $version; ?></strong></h6>
                            <div class="main-grid-card-parent-inner-button">
                                <?php if ($is_active): ?>
                                    <span class="main-grid-card-parent-inner-button-buy installed-btn">Activated</span>
                                <?php elseif ($is_installed): ?>
                                    <a href="#" data-theme="<?php echo $slug; ?>"
                                        class="main-grid-card-parent-inner-button-buy grid-install-free">Activate</a>
                                <?php else: ?>
                                    <a href="#" data-theme="<?php echo $slug; ?>"
                                        class="main-grid-card-parent-inner-button-buy grid-install-free">Install</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    
        $html = ob_get_clean();
    
        wp_send_json_success([
            'html' => $html,
            'total_pages' => $total_pages
        ]);
    }


    public function ediot_handle_free_theme_redirect() {
    if ( get_transient('ediot_free_theme_activation_redirect') ) {
        delete_transient('ediot_free_theme_activation_redirect');

        $active_theme = wp_get_theme();
        $theme_name = $active_theme->get('Name');
        $slug = strtolower( $theme_name );            
        $slug = preg_replace('/[^a-z0-9]+/', '', $slug); 
        $slug .= '-wizard';                          

        wp_redirect( admin_url('admin.php?page=' . $slug) );
        exit;
    }
    }


    public function ediot_install_and_activate_free_theme()
    {
      check_ajax_referer('whizzie_nonce', '_wpnonce');
  
      if (!current_user_can('install_themes') || !isset($_POST['theme_domain'])) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions to install themes.'));
      }
  
      $theme_slug = sanitize_text_field($_POST['theme_domain']);
  
      include_once ABSPATH . 'wp-admin/includes/theme.php';
      include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
  
      $installed_themes = wp_get_themes(array('errors' => true));
      if (array_key_exists($theme_slug, $installed_themes)) {
        $current_theme = wp_get_theme();
        if ($current_theme->get('TextDomain') === $theme_slug) {
          set_transient('ediot_free_theme_activation_redirect', true, 30);
          wp_send_json_success();
        }

        switch_theme($theme_slug);
        set_transient('ediot_free_theme_activation_redirect', true, 30);
        wp_send_json_success();
      }
  
      $api = themes_api('theme_information', array(
        'slug' => $theme_slug,
        'fields' => array('sections' => false),
      ));
  
      if (is_wp_error($api)) {
        wp_send_json_error(array('message' => 'Theme not found.'));
      }
  
      $upgrader = new Theme_Upgrader();
      ob_start();
      $install_result = $upgrader->install($api->download_link);
      ob_end_clean();
  
      if (is_wp_error($install_result)) {
        wp_send_json_error(array('message' => 'Theme installation failed.'));
      }

      switch_theme($theme_slug);

      set_transient('ediot_free_theme_activation_redirect', true, 30);
      wp_send_json_success();
    }
    // end


}