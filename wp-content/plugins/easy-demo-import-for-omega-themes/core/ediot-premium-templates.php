<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="ediot-mainwrap">
    <div id="oedi-block-tab" style="display: flex; gap: 20px;">
        <div class="search-wrapper-outer" style="flex: 1;">
            <h5 style="font-weight: 700; margin-bottom: 10px;text-align:center;">Search By Categories</h5>
            <div id="categories-loader" style="display: none; text-align: center; padding: 20px;">
                <img src="<?php echo EDIOT_URL . 'core/loader/loader5.gif'; ?>" alt="Loading..."
                    style="max-width: 50px;" />
            </div>
            <ul id="api-response-list" class="list-group my-3" style="max-height: 400px; overflow-y: auto;">
                <!-- Categories will be inserted here -->
            </ul>
            <div id="ediot-theme-bundle"
                style="margin-top: 20px; background: #fff; border-radius: 10px; padding: 10px; text-align: center;">
                <h4 style="font-weight: 700; margin-bottom: 10px;">WordPress Theme Bundle</h4>


                <a href="<?php echo EDIOT_WEB_URL ?>products/wp-theme-bundle" target="_blank">
                    <img src="<?php echo EDIOT_URL . 'resources/images/ediot-premium-template.png'; ?>"
                        alt="Theme Bundle" style="max-width: 100%; border-radius: 10px; margin-bottom: 10px;" />
                </a>


                <a class="ediot-bundle-button" href="<?php echo EDIOT_WEB_URL ?>products/wp-theme-bundle"
                    target="_blank">
                    Get Bundle At $69
                </a>
            </div>
        </div>
        <div id="product-cards-outer-box" style="flex: 3;">

            <div class="search-container position-relative" style="margin-bottom: 15px;">
                <img src="<?php echo EDIOT_URL . 'resources/images/omega-search.png'; ?>" alt="Theme Bundle" />

                <input type="text" id="search-box" class="form-control" placeholder="Search products..."
                    style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #ccc; font-size: 16px;" />
            </div>
            <div style="position: relative;">
                <div id="products-loader"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); display: none; z-index: 10; text-align: center; border-radius: 10px;">
                    <img src="<?php echo EDIOT_URL . 'core/loader/loader5.gif'; ?>" alt="Loading..."
                        style="max-width: 50px; margin-top: 200px;" />
                </div>
                <div id="product-cards" class="row" style="overflow-y: auto; max-height: 860px;">
                    <!-- Product cards will be inserted here -->
                </div>
            </div>
        </div>

    </div>
    <div class="ediot-load-button mt-5">
        <button id="load-more" class="btn btn-primary" style="display:none;">Load More</button>
    </div>

</div>