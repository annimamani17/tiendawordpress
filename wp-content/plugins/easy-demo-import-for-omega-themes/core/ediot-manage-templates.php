<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="row ediot-manage-templates-main-box">

    <div class="ediot-manage-templates-wrapper">
        <ul class="nav nav-pills mb-3" id="ediot-pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ediot-pills-theme-info-tab" data-bs-toggle="pill"
                    data-bs-target="#ediot-pills-theme-info" type="button" role="tab"
                    aria-controls="ediot-pills-theme-info"
                    aria-selected="true"><?php esc_html_e('Theme Info', 'easy-demo-import-for-omega-themes'); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ediot-pills-premium-templates-tab" data-bs-toggle="pill"
                    data-bs-target="#ediot-pills-premium-templates" type="button" role="tab"
                    aria-controls="ediot-pills-premium-templates"
                    aria-selected="false"><?php esc_html_e('Explore Free Templates', 'easy-demo-import-for-omega-themes'); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ediot-pills-theme-bundle-tab" data-bs-toggle="pill"
                    data-bs-target="#ediot-pills-theme-bundle" type="button" role="tab"
                    aria-controls="ediot-pills-theme-bundle"
                    aria-selected="false"><?php esc_html_e('Theme Bundle', 'easy-demo-import-for-omega-themes'); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ediot-pills-support-tab" data-bs-toggle="pill"
                    data-bs-target="#ediot-pills-support" type="button" role="tab" aria-controls="ediot-pills-support"
                    aria-selected="false"><?php esc_html_e('Support', 'easy-demo-import-for-omega-themes'); ?></button>
            </li>
        </ul>
        <div class="tab-content" id="ediot-pills-tabContent">

            <div class="tab-pane fade show active" id="ediot-pills-theme-info" role="tabpanel"
                aria-labelledby="ediot-pills-theme-info-tab">
                <div class="ediot-tab-two-column">
                    <div class="ediot-tab-left">
                        <?php include plugin_dir_path(__FILE__) . 'tabs/ediot-theme-info.php'; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="ediot-pills-premium-templates" role="tabpanel"
                aria-labelledby="ediot-pills-premium-templates-tab">
                <?php include plugin_dir_path(__FILE__) . 'tabs/ediot-free-templates.php'; ?>
            </div>

            <div class="tab-pane fade" id="ediot-pills-theme-bundle" role="tabpanel"
                aria-labelledby="ediot-pills-theme-bundle-tab">
                <div class="ediot-tab-two-column">
                    <div class="ediot-tab-left">
                        <?php include plugin_dir_path(__FILE__) . 'tabs/ediot-theme-bundle.php'; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="ediot-pills-support" role="tabpanel"
                aria-labelledby="ediot-pills-support-tab">
                <div class="ediot-tab-two-column">
                    <div class="ediot-tab-left">
                        <?php include plugin_dir_path(__FILE__) . 'tabs/ediot-support.php'; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- static right tab content -->
    <div class="ediot-right-column">
        <div class="ediot-tab-right">
            <?php include plugin_dir_path(__FILE__) . 'tabs/ediot-tab-right-block.php'; ?>
        </div>
    </div>



</div>