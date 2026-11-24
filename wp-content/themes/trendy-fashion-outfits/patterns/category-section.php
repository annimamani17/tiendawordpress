<?php
/**
 * Title: Category Section
 * Slug: trendy-fashion-outfits/category-section
 * Categories: trendy-fashion-outfits
 */
?>

<!-- wp:group {"tagName":"main","align":"full","className":"wp-block-section","style":{"spacing":{"padding":{"top":"0px"},"margin":{"top":"0","bottom":"50px"}},"border":{"radius":{"topLeft":"20px","topRight":"20px"}}},"layout":{"inherit":true,"type":"constrained","contentSize":"80%"}} -->
<main class="wp-block-group alignfull wp-block-section"
  style="border-top-left-radius:20px;border-top-right-radius:20px;margin-top:0;margin-bottom:50px;padding-top:0px">
  <!-- wp:columns {"className":"product-section","style":{"spacing":{"blockGap":{"left":"0px"},"padding":{"top":"50px"}}}} -->
  <div class="wp-block-columns product-section" style="padding-top:50px">
    <!-- wp:column {"width":"100%","className":"wow zoomIn"} -->
    <div class="wp-block-column wow zoomIn" style="flex-basis:100%">
      <!-- wp:group {"className":"property-heading","layout":{"type":"constrained","contentSize":"100%"}} -->
      <div class="wp-block-group property-heading">
        <!-- wp:heading {"textAlign":"center","level":4,"className":"short-heading","style":{"elements":{"link":{"color":{"text":"#3a3a3a"}}},"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"400","textTransform":"uppercase"},"spacing":{"margin":{"top":"0","bottom":"0"}},"color":{"text":"#3a3a3a"}},"fontFamily":"libre-baskerville"} -->
        <h4
          class="wp-block-heading has-text-align-center short-heading has-text-color has-link-color has-libre-baskerville-font-family"
          style="color:#3a3a3a;margin-top:0;margin-bottom:0;font-size:25px;font-style:normal;font-weight:400;text-transform:uppercase">
          <strong><?php esc_html_e('Shop By', 'trendy-fashion-outfits'); ?>
          </strong><em><?php esc_html_e('Category', 'trendy-fashion-outfits'); ?></em></h4>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->

      <!-- wp:columns {"verticalAlignment":"center","className":"product-box-main wow zoomIn","style":{"spacing":{"blockGap":{"left":"30px"}}}} -->
      <div class="wp-block-columns are-vertically-aligned-center product-box-main wow zoomIn">
        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category1.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Mens', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category2.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Womans', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category3.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Shoes', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category4.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Accessories', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category5.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Dresses', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"33.34%","style":{"border":{"width":"0px","style":"none"}}} -->
        <div class="wp-block-column is-vertically-aligned-center"
          style="border-style:none;border-width:0px;flex-basis:33.34%">
          <!-- wp:group {"className":"product-box-caregories","style":{"border":{"color":"#020204","width":"4px","radius":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"35px","right":"35px"}}},"layout":{"type":"constrained"}} -->
          <div class="wp-block-group product-box-caregories has-border-color"
            style="border-color:#020204;border-width:4px;border-radius:0px;padding-top:20px;padding-right:35px;padding-bottom:20px;padding-left:35px">
            <!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","className":"product-image","style":{"border":{"radius":"0px"}}} -->
            <figure class="wp-block-image size-full has-custom-border product-image"><img
                src="<?php echo get_parent_theme_file_uri('/images/Category6.png'); ?>" alt="" class="wp-image-126"
                style="border-radius:0px" /></figure>
            <!-- /wp:image -->

            <!-- wp:group {"style":{"spacing":{"padding":{"top":"10px","bottom":"10px","left":"10px","right":"10px"},"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"
              style="margin-top:0px;margin-bottom:0px;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px">
              <!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"25px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"top":"5px"}},"color":{"text":"#3a3a3a"},"elements":{"link":{"color":{"text":"#3a3a3a"}}}},"fontFamily":"libre-baskerville"} -->
              <h3
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family"
                style="color:#3a3a3a;margin-top:5px;font-size:25px;font-style:normal;font-weight:700"><a
                  href="#"><?php esc_html_e('Bags', 'trendy-fashion-outfits'); ?></a></h3>
              <!-- /wp:heading -->

              <!-- wp:heading {"textAlign":"center","level":6,"style":{"elements":{"link":{"color":{"text":"#686a72"}}},"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"},"padding":{"top":"10px","bottom":"0"}},"color":{"text":"#686a72"}},"fontSize":"small","fontFamily":"libre-baskerville"} -->
              <h6
                class="wp-block-heading has-text-align-center has-text-color has-link-color has-libre-baskerville-font-family has-small-font-size"
                style="color:#686a72;margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;padding-top:10px;padding-bottom:0;font-style:normal;font-weight:400">
                <?php esc_html_e('Lorem ipsum dolor sit amet.', 'trendy-fashion-outfits'); ?></h6>
              <!-- /wp:heading -->

              <!-- wp:buttons {"className":"product-btn","style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
              <div class="wp-block-buttons product-btn" style="margin-top:20px">
                <!-- wp:button {"backgroundColor":"background","textColor":"foreground","style":{"border":{"radius":"10px","width":"1px"},"spacing":{"padding":{"left":"35px","right":"35px","top":"9px","bottom":"9px"}},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}},"typography":{"letterSpacing":"0px"}},"fontSize":"tiny","fontFamily":"libre-baskerville","borderColor":"foreground"} -->
                <div class="wp-block-button"><a
                    class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background has-link-color has-border-color has-foreground-border-color has-libre-baskerville-font-family has-tiny-font-size has-custom-font-size wp-element-button"
                    style="border-width:1px;border-radius:10px;padding-top:9px;padding-right:35px;padding-bottom:9px;padding-left:35px;letter-spacing:0px"><?php esc_html_e('Shop Now', 'trendy-fashion-outfits'); ?></a>
                </div>
                <!-- /wp:button -->
              </div>
              <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->
          </div>
          <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
      </div>
      <!-- /wp:columns -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</main>
<!-- /wp:group -->