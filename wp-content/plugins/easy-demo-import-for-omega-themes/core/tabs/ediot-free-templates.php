<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function ediot_importer_free_themes()
{

    $current_page = isset($_GET['theme_page']) ? intval($_GET['theme_page']) : 1;
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    if ($current_page < 1)
        $current_page = 1;
    $url = 'https://api.wordpress.org/themes/info/1.2/';
    $args = [
        'action' => 'query_themes',
        'request[author]' => 'omegathemes',
        'request[per_page]' => 12,
        'request[page]' => $current_page,
    ];

    if (!empty($search)) {
        $args['request[search]'] = $search;
    }

    $full_url = add_query_arg($args, $url);

    // Make GET request
    $response = wp_remote_get($full_url);

    if (is_wp_error($response)) {
        echo '<div class="notice notice-error"><p>Error fetching themes: ' . esc_html($response->get_error_message()) . '</p></div>';
        return;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($code !== 200 || empty($body)) {
        echo '<p>Error finding themes.</p>';
        return;
    }

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<p>Error finding themes.</p>';
        return;
    }

    $themes = !empty($data['themes']) ? $data['themes'] : [];
    $total = isset($data['info']['results']) ? intval($data['info']['results']) : 0;
    $total_pages = isset($data['info']['pages']) ? intval($data['info']['pages']) : 0;

    ?>
    <div id="templates-loader-overlay" style="display: none;">
        <div class="loader-container">
            <img src="<?php echo esc_url(EDIOT_URL . 'core/loader/loader5.gif'); ?>" alt="Loading templates..." />
        </div>
    </div>
    <div class="ediot-search-wrapper">
        <input type="text" id="ediot-theme-search" placeholder="Search themes...">
    </div>
    <!-- new add search -->

   
    <!-- end -->
    <div class="main-grid-card-parent">
        <div class="main-grid-card row theme-templates">
            <?php
            if ($themes) {
                foreach ($themes as $theme) {
                    $screenshot = !empty($theme['screenshot_url']) ? esc_url($theme['screenshot_url']) : '';
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
                                <img class="main-grid-card-parent-inner-image" src="<?php echo esc_url($screenshot); ?>" width="100"
                                    height="100" alt="<?php echo esc_url($name); ?>">
                            </div>
                            <div class="main-grid-card-parent-inner-description">
                                <h3><?php echo esc_html($name); ?></h3>
                                <h6>Version: <strong><?php echo esc_html($version); ?></strong></h6>
                                <div class="main-grid-card-parent-inner-button">
                                    <?php if ($is_active): ?>
                                        <span
                                            class="main-grid-card-parent-inner-button-buy installed-btn"><?php echo esc_html('Activated'); ?></span>
                                    <?php elseif ($is_installed): ?>
                                        <a target="_blank" href="#" data-theme="<?php echo $slug; ?>"
                                            class="main-grid-card-parent-inner-button-buy grid-install-free"><?php echo esc_html('Activate'); ?></a>
                                    <?php else: ?>
                                        <a target="_blank" href="#" data-theme="<?php echo $slug; ?>"
                                            class="main-grid-card-parent-inner-button-buy grid-install-free"><?php echo esc_html('Install'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
            }
            ?>
        </div>
    </div>


    <?php if ($total_pages > 1): ?>
        <div class="main-grid-card-pagination text-center my-2">
            <button class="button pagination-previous-btn" data-page="<?php echo $current_page - 1; ?>" <?php echo ($current_page <= 1) ? 'style="display:none;"' : ''; ?>>
                Previous
            </button>

            <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

            <button class="button pagination-next-btn" data-page="<?php echo $current_page + 1; ?>" <?php echo ($current_page >= $total_pages) ? 'style="display:none;"' : ''; ?>>
                Next
            </button>
        </div>
    <?php endif; ?>
<?php }


ediot_importer_free_themes();

