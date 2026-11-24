<?php
/**
 * Getting Started page and setup wizard
 *
 * @package Clothing_Store_Lite_Whizzie
 * @author Omega Themes
 * @since 1.0.0
 */

/**
 * Setup step ids in the order they are shown.
 */
function clothing_store_lite_setup_step_ids() {
	return array( 'plugins', 'demo', 'customize', 'launch' );
}

/**
 * Check whether every plugin registered with TGMPA is active and up to date.
 */
function clothing_store_lite_setup_plugins_ready() {
	if ( ! isset( $GLOBALS['tgmpa'] ) ) {
		return true;
	}
	$clothing_store_lite_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	foreach ( $clothing_store_lite_instance->plugins as $clothing_store_lite_slug => $clothing_store_lite_plugin ) {
		if ( ! $clothing_store_lite_instance->is_plugin_active( $clothing_store_lite_slug ) || false !== $clothing_store_lite_instance->does_plugin_have_update( $clothing_store_lite_slug ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Get the completion state of each setup step.
 *
 * @return array step id => bool
 */
function clothing_store_lite_get_setup_progress() {
	$clothing_store_lite_saved = (array) get_option( 'clothing_store_lite_setup_progress', array() );

	return array(
		'plugins'   => ! empty( $clothing_store_lite_saved['plugins'] ) || clothing_store_lite_setup_plugins_ready(),
		'demo'      => ! empty( $clothing_store_lite_saved['demo'] ),
		'customize' => ! empty( $clothing_store_lite_saved['customize'] ),
		'launch'    => ! empty( $clothing_store_lite_saved['launch'] ),
	);
}

/**
 * Save a step as done.
 *
 * @param string $clothing_store_lite_step Step id.
 */
function clothing_store_lite_set_setup_step_done( $clothing_store_lite_step ) {
	if ( ! in_array( $clothing_store_lite_step, clothing_store_lite_setup_step_ids(), true ) ) {
		return;
	}
	$clothing_store_lite_saved = (array) get_option( 'clothing_store_lite_setup_progress', array() );
	$clothing_store_lite_saved[ $clothing_store_lite_step ] = true;
	update_option( 'clothing_store_lite_setup_progress', $clothing_store_lite_saved );
}

/**
 * Whether the theme gives Easy Demo Import for Omega Themes inner pages to import before the homepage.
 * Mirrors the plugin's own check for the theme's setup function.
 */
function clothing_store_lite_has_inner_pages_import() {
	$clothing_store_lite_function = str_replace( '-', '_', wp_get_theme()->get( 'TextDomain' ) ) . '_setup_widgets';
	if ( class_exists( 'Whizzie' ) && ! is_child_theme() ) {
		return method_exists( 'Whizzie', $clothing_store_lite_function );
	}
	return class_exists( 'Child_Whizzie' ) && method_exists( 'Child_Whizzie', $clothing_store_lite_function );
}

class Clothing_Store_Lite_Whizzie {

	protected $version = '1.2.0';

	/** @var string Current theme name, used as namespace in actions. */
	protected $clothing_store_lite_theme_name = '';
	protected $clothing_store_lite_theme_title = '';

	/** @var string Wizard page slug and title. */
	protected $clothing_store_lite_page_slug = '';
	protected $clothing_store_lite_page_title = '';

	/** @var string Hook suffix returned by add_theme_page(). */
	protected $page_hook = '';

	/** @var array Wizard steps set by user. */
	protected $config_steps = array();

	/**
	 * Relative plugin url for this plugin folder
	 * @since 1.0.0
	 * @var string
	 */
	protected $clothing_store_lite_plugin_url = '';

	public $clothing_store_lite_plugin_path;
	public $parent_slug;

	/**
	 * TGMPA instance storage
	 *
	 * @var object
	 */
	protected $tgmpa_instance;

	/**
	 * TGMPA Menu slug
	 *
	 * @var string
	 */
	protected $tgmpa_menu_slug = 'tgmpa-install-plugins';

	/**
	 * TGMPA Menu url
	 *
	 * @var string
	 */
	protected $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

	/** @var int Front page before a demo import request. */
	protected $front_before = 0;

	/**
	 * Constructor
	 *
	 * @param $clothing_store_lite_config	Our config parameters
	 */
	public function __construct( $clothing_store_lite_config ) {
		$this->set_vars( $clothing_store_lite_config );
		$this->init();
	}

	/**
	 * Set some settings
	 * @since 1.0.0
	 * @param $clothing_store_lite_config	Our config parameters
	 */
	public function set_vars( $clothing_store_lite_config ) {

		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/class-tgm-plugin-activation.php';
		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/tgm.php';

		if( isset( $clothing_store_lite_config['clothing_store_lite_page_slug'] ) ) {
			$this->clothing_store_lite_page_slug = esc_attr( $clothing_store_lite_config['clothing_store_lite_page_slug'] );
		}
		if( isset( $clothing_store_lite_config['clothing_store_lite_page_title'] ) ) {
			$this->clothing_store_lite_page_title = esc_attr( $clothing_store_lite_config['clothing_store_lite_page_title'] );
		}
		if( isset( $clothing_store_lite_config['steps'] ) ) {
			$this->config_steps = $clothing_store_lite_config['steps'];
		}

		$this->clothing_store_lite_plugin_path = trailingslashit( dirname( __FILE__ ) );
		$clothing_store_lite_relative_url = str_replace( get_stylesheet_directory(), '', $this->clothing_store_lite_plugin_path );
		$this->clothing_store_lite_plugin_url = trailingslashit( get_stylesheet_directory_uri() . $clothing_store_lite_relative_url );
		$clothing_store_lite_current_theme = wp_get_theme();
		$this->clothing_store_lite_theme_title = $clothing_store_lite_current_theme->get( 'Name' );
		$this->clothing_store_lite_theme_name = strtolower( preg_replace( '#[^a-zA-Z]#', '', $clothing_store_lite_current_theme->get( 'Name' ) ) );
		$this->clothing_store_lite_page_slug = apply_filters( $this->clothing_store_lite_theme_name . '_theme_setup_wizard_clothing_store_lite_page_slug', $this->clothing_store_lite_theme_name . '-wizard' );
		$this->parent_slug = apply_filters( $this->clothing_store_lite_theme_name . '_theme_setup_wizard_parent_slug', '' );

	}

	/**
	 * Hooks and filters
	 * @since 1.0.0
	 */
	public function init() {

		if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
			add_action( 'init', array( $this, 'get_tgmpa_instance' ), 30 );
			add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_init', array( $this, 'get_plugins' ), 30 );
		add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
		add_action( 'wp_ajax_setup_plugins', array( $this, 'setup_plugins' ) );
		add_action( 'wp_ajax_ediot_import_demo_content', array( $this, 'watch_demo_import' ), 1 );
		add_action( 'admin_init', array( $this, 'skip_elementor_redirect' ), 1 );
		add_action( 'wp_ajax_clothing_store_lite_setup_step', array( $this, 'clothing_store_lite_setup_step' ) );

	}

	/**
	 * Load assets on the Getting Started page, and wherever the welcome notice is shown.
	 *
	 * @param string $clothing_store_lite_hook Current admin page hook suffix.
	 */
	public function enqueue_scripts( $clothing_store_lite_hook ) {
		$clothing_store_lite_is_setup_page = ( $clothing_store_lite_hook === $this->page_hook );
		$clothing_store_lite_has_notice    = function_exists( 'clothing_store_lite_show_admin_notice' ) && clothing_store_lite_show_admin_notice();

		if ( ! $clothing_store_lite_is_setup_page && ! $clothing_store_lite_has_notice ) {
			return;
		}

		$clothing_store_lite_version = wp_get_theme()->get( 'Version' );

		// The bundled Plus Jakarta Sans is Regular only, so load real weights through the local webfont loader.
		require_once get_theme_file_path( 'lib/custom/css/wptt-webfont-loader.php' );
		wp_enqueue_style( 'clothing-store-lite-setup-fonts', wptt_get_webfont_url( 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap' ), array(), $clothing_store_lite_version );
		// Version the page assets by file time so browsers and caching plugins never serve a stale copy after an update.
		$clothing_store_lite_css = 'inc/homepage-setup/assets/css/homepage-setup-style.css';
		$clothing_store_lite_js  = 'inc/homepage-setup/assets/js/homepage-setup-script.js';
		wp_enqueue_style( 'clothing-store-lite-homepage-setup-style', get_theme_file_uri( $clothing_store_lite_css ), array( 'clothing-store-lite-setup-fonts' ), $clothing_store_lite_version . '.' . filemtime( get_theme_file_path( $clothing_store_lite_css ) ) );
		wp_register_script( 'clothing-store-lite-homepage-setup-script', get_theme_file_uri( $clothing_store_lite_js ), array( 'jquery' ), $clothing_store_lite_version . '.' . filemtime( get_theme_file_path( $clothing_store_lite_js ) ), true );
		wp_localize_script(
			'clothing-store-lite-homepage-setup-script',
			'clothing_store_lite_setup',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wpnonce' => wp_create_nonce( 'whizzie_nonce' ),
				'importSteps' => $this->get_import_steps(),
				'importSaves' => false,
				'i18n'    => array(
					'installing' => esc_html__( 'Working…', 'clothing-store-lite' ),
					'active'     => esc_html__( 'Active', 'clothing-store-lite' ),
					'failed'     => esc_html__( 'Failed', 'clothing-store-lite' ),
					'install'    => esc_html__( 'Install & activate', 'clothing-store-lite' ),
					'installed'  => esc_html__( 'Plugins ready', 'clothing-store-lite' ),
					'pluginsErr' => esc_html__( 'Some plugins could not be installed. Try again, or install them from the plugins screen.', 'clothing-store-lite' ),
					'importing'  => esc_html__( 'Importing demo…', 'clothing-store-lite' ),
					'imported'   => esc_html__( 'Demo imported', 'clothing-store-lite' ),
					'importErr'  => esc_html__( 'The import did not finish. Make sure Elementor and Easy Demo Import for Omega Themes are active, then try again.', 'clothing-store-lite' ),
					'copied'     => esc_html__( 'Copied!', 'clothing-store-lite' ),
				),
			)
		);
		wp_enqueue_script( 'clothing-store-lite-homepage-setup-script' );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function tgmpa_load( $clothing_store_lite_status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Elementor opens its own onboarding after activation; keep users on the Getting Started page.
	 */
	public function skip_elementor_redirect() {
		delete_transient( 'elementor_activation_redirect' );
	}

	/**
	 * Note the front page before Easy Demo Import for Omega Themes imports the homepage,
	 * so the demo step is saved however the import was started.
	 */
	public function watch_demo_import() {
		$this->front_before = (int) get_option( 'page_on_front' );
		add_action( 'shutdown', array( $this, 'record_demo_import' ) );
	}

	/**
	 * Save the demo step once the import has made an Elementor page the front page.
	 */
	public function record_demo_import() {
		$clothing_store_lite_front = (int) get_option( 'page_on_front' );
		if ( $clothing_store_lite_front && $clothing_store_lite_front !== $this->front_before && get_post_meta( $clothing_store_lite_front, '_elementor_edit_mode', true ) ) {
			clothing_store_lite_set_setup_step_done( 'demo' );
		}
	}

	/**
	 * Easy Demo Import for Omega Themes requests, run in order by the import button.
	 */
	public function get_import_steps() {
		$clothing_store_lite_steps = array();
		if ( clothing_store_lite_has_inner_pages_import() ) {
			$clothing_store_lite_steps[] = array( 'action' => 'ediot_import_inner_pages_data', 'nonce' => wp_create_nonce( 'ediot_import_pages_nonce' ) );
		}
		$clothing_store_lite_steps[] = array( 'action' => 'ediot_import_demo_content', 'nonce' => wp_create_nonce( 'ediot_import_demo_nonce' ) );
		return $clothing_store_lite_steps;
	}

	/**
	 * Get configured TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function get_tgmpa_instance() {
		$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	}

	/**
	 * Update $tgmpa_menu_slug and $clothing_store_lite_tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function set_tgmpa_url() {
		$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug = apply_filters( $this->clothing_store_lite_theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );
		$clothing_store_lite_tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';
		$this->tgmpa_url = apply_filters( $this->clothing_store_lite_theme_name . '_theme_setup_wizard_tgmpa_url', $clothing_store_lite_tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );
	}

	/**
	 * Register the Getting Started page under Appearance
	 */
	public function menu_page() {
		$this->page_hook = add_theme_page( esc_html( $this->clothing_store_lite_page_title ), esc_html( $this->clothing_store_lite_page_title ), 'manage_options', $this->clothing_store_lite_page_slug, array( $this, 'wizard_page' ) );
	}

	/**
	 * Small inline SVG icons used on the page
	 *
	 * @param string $clothing_store_lite_name Icon name.
	 */
	public function icon( $clothing_store_lite_name ) {
		$clothing_store_lite_icons = array(
			'check' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'arrow' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'close' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
			'crown' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5L3 8z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
			'download' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'dash'  => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 12h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
			'eye'   => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 9.2a2.8 2.8 0 1 0 0 5.6 2.8 2.8 0 0 0 0-5.6z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
			'book'  => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15zM4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
			'table' => '<svg class="omg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 10h16M10 10v9" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
		);
		return isset( $clothing_store_lite_icons[ $clothing_store_lite_name ] ) ? $clothing_store_lite_icons[ $clothing_store_lite_name ] : '';
	}

	/**
	 * Allowed tags for printing the inline icons
	 */
	public function icon_kses() {
		return array(
			'svg'  => array( 'class' => true, 'viewbox' => true, 'aria-hidden' => true ),
			'path' => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
		);
	}

	/**
	 * Make an interface for the Getting Started page
	 */
	public function wizard_page() {
		tgmpa_load_bulk_installer();

		if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
			die( esc_html__( 'Failed to find TGM', 'clothing-store-lite' ) );
		}

		$clothing_store_lite_url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );
		$clothing_store_lite_method = '';
		$clothing_store_lite_fields = array_keys( $_POST );

		if ( false === ( $clothing_store_lite_creds = request_filesystem_credentials( esc_url_raw( $clothing_store_lite_url ), $clothing_store_lite_method, false, false, $clothing_store_lite_fields ) ) ) {
			return true;
		}

		if ( ! WP_Filesystem( $clothing_store_lite_creds ) ) {
			request_filesystem_credentials( esc_url_raw( $clothing_store_lite_url ), $clothing_store_lite_method, true, false, $clothing_store_lite_fields );
			return true;
		}

		$clothing_store_lite_theme = wp_get_theme();
		$clothing_store_lite_theme_title = $clothing_store_lite_theme->get( 'Name' );
		$clothing_store_lite_theme_version = $clothing_store_lite_theme->get( 'Version' );
		$clothing_store_lite_theme_desc = wp_trim_words( $clothing_store_lite_theme->get( 'Description' ), 28 );

		$clothing_store_lite_steps = $this->get_steps();
		$clothing_store_lite_progress = clothing_store_lite_get_setup_progress();
		$clothing_store_lite_focus = ! empty( $_GET['omg-focus'] ) && empty( $clothing_store_lite_progress['demo'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display flag only.
		$clothing_store_lite_done = count( array_filter( $clothing_store_lite_progress ) );
		$clothing_store_lite_total = count( $clothing_store_lite_steps );

		// The first unfinished step starts open.
		$clothing_store_lite_open = '';
		foreach ( $clothing_store_lite_steps as $clothing_store_lite_id => $clothing_store_lite_step ) {
			if ( empty( $clothing_store_lite_progress[ $clothing_store_lite_id ] ) ) {
				$clothing_store_lite_open = $clothing_store_lite_id;
				break;
			}
		}

		$clothing_store_lite_images = get_stylesheet_directory_uri() . '/inc/homepage-setup/assets/homepage-setup-images/';
		$clothing_store_lite_check = wp_kses( $this->icon( 'check' ), $this->icon_kses() );
		$clothing_store_lite_dash  = wp_kses( $this->icon( 'dash' ), $this->icon_kses() );
		$clothing_store_lite_arrow = wp_kses( $this->icon( 'arrow' ), $this->icon_kses() );
		$clothing_store_lite_crown = wp_kses( $this->icon( 'crown' ), $this->icon_kses() );
		$clothing_store_lite_live  = '<span class="omg-live-dot" aria-hidden="true"></span>';
		?>
		<div class="wrap omg-wrap<?php echo $clothing_store_lite_focus ? ' is-focus' : ''; ?>">

			<div class="omg-header">
				<div class="omg-header__text">
					<h1 class="omg-title">
						<?php
						/* translators: %s: theme name */
						printf( esc_html__( 'Welcome to %s', 'clothing-store-lite' ), esc_html( $clothing_store_lite_theme_title ) );
						?>
					</h1>
				</div>
				<div class="omg-header__actions">
					<a class="omg-btn omg-btn--demo" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_DEMO_PRO ); ?>" target="_blank"><?php echo $clothing_store_lite_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'Live Demo', 'clothing-store-lite' ); ?></a>
					<a class="omg-btn omg-btn--premium" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php echo $clothing_store_lite_crown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Go To Premium', 'clothing-store-lite' ); ?></a>
				</div>
			</div>
			<hr class="wp-header-end">

			<div class="omg-layout">

				<aside class="omg-card omg-setup<?php echo $clothing_store_lite_done >= $clothing_store_lite_total ? ' is-complete' : ''; ?>" id="omg-setup" data-total="<?php echo esc_attr( $clothing_store_lite_total ); ?>">
					<div class="omg-setup__head">
						<h2 class="omg-setup__title"><?php esc_html_e( 'Site setup', 'clothing-store-lite' ); ?></h2>
						<span class="omg-setup__count">
							<?php
							/* translators: 1: steps done, 2: total steps */
							printf( esc_html__( '%1$s of %2$s done', 'clothing-store-lite' ), '<b class="omg-setup__done">' . esc_html( $clothing_store_lite_done ) . '</b>', esc_html( $clothing_store_lite_total ) );
							?>
						</span>
					</div>
					<div class="omg-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $clothing_store_lite_total ); ?>" aria-valuenow="<?php echo esc_attr( $clothing_store_lite_done ); ?>">
						<span class="omg-progress__bar" style="width: <?php echo esc_attr( max( 3, round( $clothing_store_lite_done / $clothing_store_lite_total * 100 ) ) ); ?>%"></span>
					</div>
					<p class="omg-setup__intro"><?php esc_html_e( 'Finish these steps to make your store look like the demo in a few minutes.', 'clothing-store-lite' ); ?></p>

					<div class="omg-setup__complete">
						<span class="omg-setup__complete-icon"><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></span>
						<div>
							<strong><?php esc_html_e( 'Your store is ready', 'clothing-store-lite' ); ?></strong>
							<span><?php esc_html_e( 'All setup steps are done. Time to add your products.', 'clothing-store-lite' ); ?></span>
						</div>
					</div>

					<ol class="omg-steps">
						<?php
						$clothing_store_lite_number = 1;
						foreach ( $clothing_store_lite_steps as $clothing_store_lite_id => $clothing_store_lite_step ) {
							$clothing_store_lite_classes = array( 'omg-step' );
							if ( ! empty( $clothing_store_lite_progress[ $clothing_store_lite_id ] ) ) {
								$clothing_store_lite_classes[] = 'is-done';
							}
							if ( $clothing_store_lite_id === $clothing_store_lite_open ) {
								$clothing_store_lite_classes[] = 'is-open';
							}
							?>
							<li class="<?php echo esc_attr( implode( ' ', $clothing_store_lite_classes ) ); ?>" data-step="<?php echo esc_attr( $clothing_store_lite_id ); ?>">
								<button type="button" class="omg-step__head" aria-expanded="<?php echo $clothing_store_lite_id === $clothing_store_lite_open ? 'true' : 'false'; ?>">
									<span class="omg-step__num">
										<span class="omg-step__index"><?php echo esc_html( $clothing_store_lite_number ); ?></span>
										<?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
									</span>
									<span class="omg-step__text">
										<span class="omg-step__title"><?php echo esc_html( $clothing_store_lite_step['title'] ); ?></span>
										<span class="omg-step__desc"><?php echo esc_html( $clothing_store_lite_step['desc'] ); ?></span>
									</span>
								</button>
								<div class="omg-step__body">
									<div class="omg-step__inner">
										<?php call_user_func( array( $this, $clothing_store_lite_step['view'] ) ); ?>
									</div>
								</div>
							</li>
							<?php
							$clothing_store_lite_number++;
						}
						?>
					</ol>

					<p class="omg-setup__help">
						<?php esc_html_e( 'Stuck on a step?', 'clothing-store-lite' ); ?>
						<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_LITE_DOCS_PRO ); ?>" target="_blank"><?php esc_html_e( 'Read the setup guide', 'clothing-store-lite' ); ?></a>
						<?php esc_html_e( 'or', 'clothing-store-lite' ); ?>
						<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_SUPPORT_FREE ); ?>" target="_blank"><?php esc_html_e( 'ask on the forum', 'clothing-store-lite' ); ?></a>.
					</p>
				</aside>

				<?php if ( $clothing_store_lite_focus ) : ?>
					<div class="omg-focus-tip" role="dialog" aria-labelledby="omg-focus-tip-title">
						<span class="omg-focus-tip__icon"><?php echo wp_kses( $this->icon( 'download' ), $this->icon_kses() ); ?></span>
						<p class="omg-kicker omg-kicker--blue"><?php esc_html_e( 'Quick setup', 'clothing-store-lite' ); ?></p>
						<h3 class="omg-focus-tip__title" id="omg-focus-tip-title"><?php esc_html_e( 'Let’s import your demo', 'clothing-store-lite' ); ?></h3>
						<p class="omg-focus-tip__text"><?php esc_html_e( 'Install the essential plugins, then import the demo content. The rest of this page unlocks as soon as the import finishes.', 'clothing-store-lite' ); ?></p>
						<div class="omg-focus-tip__actions">
							<button type="button" class="omg-btn omg-btn--primary omg-focus-ok"><?php esc_html_e( 'Got it', 'clothing-store-lite' ); ?></button>
							<button type="button" class="omg-skip omg-focus-exit"><?php esc_html_e( 'Skip, show everything', 'clothing-store-lite' ); ?></button>
						</div>
					</div>
				<?php endif; ?>

				<div class="omg-main">

					<section class="omg-card omg-hero">
						<div class="omg-hero__content">
							<div class="omg-badges">
								<span class="omg-badge omg-badge--blue"><?php esc_html_e( 'Active theme', 'clothing-store-lite' ); ?></span>
								<span class="omg-badge"><?php esc_html_e( 'Free', 'clothing-store-lite' ); ?></span>
							</div>
							<h2 class="omg-hero__title"><?php echo esc_html( $clothing_store_lite_theme_title ); ?></h2>
							<p class="omg-hero__desc"><?php echo esc_html( $clothing_store_lite_theme_desc ); ?></p>
							<div class="omg-hero__actions">
								<a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize theme', 'clothing-store-lite' ); ?></a>
								<a class="omg-btn omg-btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your site', 'clothing-store-lite' ); ?></a>
								<a class="omg-btn omg-btn--pink" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php echo $clothing_store_lite_crown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Upgrade to Pro', 'clothing-store-lite' ); ?></a>
								<a class="omg-btn omg-btn--dark" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Get the Bundle', 'clothing-store-lite' ); ?></a>
							</div>
						</div>
						<div class="omg-hero__media">
							<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/screenshot.png' ); ?>" alt="<?php echo esc_attr( $clothing_store_lite_theme_title ); ?>">
						</div>
					</section>

					<div class="omg-promos">
						<section class="omg-card omg-pro">
							<span class="omg-pro__ribbon"><?php esc_html_e( '25% Off', 'clothing-store-lite' ); ?></span>
							<p class="omg-kicker omg-kicker--pink"><?php esc_html_e( 'Go Pro', 'clothing-store-lite' ); ?></p>
							<h3 class="omg-pro__title">
								<?php
								/* translators: %s: theme name */
								printf( esc_html__( 'Unlock %s Pro', 'clothing-store-lite' ), esc_html( $clothing_store_lite_theme_title ) );
								?>
							</h3>
							<div class="omg-pro__links">
								<?php
								$clothing_store_lite_pro_links = array(
									array( ONLINE_CLOTHING_STORE_DEMO_PRO, 'eye', __( 'Live demo', 'clothing-store-lite' ), true ),
									array( ONLINE_CLOTHING_STORE_DOCS_PRO, 'book', __( 'Pro docs', 'clothing-store-lite' ), true ),
									array( '#omg-compare', 'table', __( 'Free vs Pro', 'clothing-store-lite' ), false ),
								);
								foreach ( $clothing_store_lite_pro_links as $clothing_store_lite_pro_link ) {
									?>
									<a class="omg-pro__link" href="<?php echo esc_url( $clothing_store_lite_pro_link[0] ); ?>"<?php echo $clothing_store_lite_pro_link[3] ? ' target="_blank"' : ''; ?>>
										<?php echo wp_kses( $this->icon( $clothing_store_lite_pro_link[1] ), $this->icon_kses() ); ?>
										<span><?php echo esc_html( $clothing_store_lite_pro_link[2] ); ?></span>
									</a>
									<?php
								}
								?>
							</div>
							<ul class="omg-checklist">
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'All premium demo layouts', 'clothing-store-lite' ); ?></li>
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Advanced customizer controls', 'clothing-store-lite' ); ?></li>
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Priority support & updates', 'clothing-store-lite' ); ?></li>
							</ul>
							<div class="omg-code">
								<span class="omg-code__label"><?php esc_html_e( 'Code', 'clothing-store-lite' ); ?></span>
								<code class="omg-code__value">NYTHEMES25</code>
								<button type="button" class="omg-code__copy" data-omg-copy="NYTHEMES25"><?php esc_html_e( 'Copy', 'clothing-store-lite' ); ?></button>
							</div>
							<p class="omg-pro__price">
								<strong>$39</strong>
								<span><?php esc_html_e( 'Pro license · save 25% with the code above', 'clothing-store-lite' ); ?></span>
							</p>
							<a class="omg-btn omg-btn--pink omg-btn--block" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e( 'Upgrade to Pro — just $39', 'clothing-store-lite' ); ?></a>
						</section>

						<section class="omg-card omg-bundle omg-glow-border">
							<div class="omg-bundle__head">
								<div>
									<p class="omg-kicker omg-kicker--pink"><?php esc_html_e( 'Best value', 'clothing-store-lite' ); ?></p>
									<h3 class="omg-bundle__title"><?php esc_html_e( 'WP Theme Bundle', 'clothing-store-lite' ); ?></h3>
								</div>
								<span class="omg-bundle__seal"><small><?php esc_html_e( 'Extra', 'clothing-store-lite' ); ?></small><b>15%</b><small><?php esc_html_e( 'Off', 'clothing-store-lite' ); ?></small></span>
							</div>
							<div class="omg-bundle__media">
								<img src="<?php echo esc_url( $clothing_store_lite_images . 'bundle-banner.png' ); ?>" alt="<?php esc_attr_e( 'Theme bundle collage', 'clothing-store-lite' ); ?>">
							</div>
							<p class="omg-bundle__desc"><?php esc_html_e( 'Every Omega WordPress theme in one download.', 'clothing-store-lite' ); ?></p>
							<ul class="omg-checklist">
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'All premium Omega themes included', 'clothing-store-lite' ); ?></li>
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Updates for every theme in the bundle', 'clothing-store-lite' ); ?></li>
								<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Priority support on all themes', 'clothing-store-lite' ); ?></li>
							</ul>
							<p class="omg-price"><del>$2795</del><strong>$69</strong></p>
							<div class="omg-code omg-code--light">
								<span class="omg-code__label"><?php esc_html_e( 'Code', 'clothing-store-lite' ); ?></span>
								<code class="omg-code__value">BNDL15OFF</code>
								<button type="button" class="omg-code__copy" data-omg-copy="BNDL15OFF"><?php esc_html_e( 'Copy', 'clothing-store-lite' ); ?></button>
							</div>
							<a class="omg-btn omg-btn--dark omg-btn--block" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Shop the bundle', 'clothing-store-lite' ); ?></a>
						</section>
					</div>

					<div class="omg-info">
						<section class="omg-card omg-compare" id="omg-compare">
							<h3 class="omg-card__title"><?php esc_html_e( 'Free vs Pro', 'clothing-store-lite' ); ?></h3>
							<table class="omg-compare__table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Feature', 'clothing-store-lite' ); ?></th>
										<th><?php esc_html_e( 'Free', 'clothing-store-lite' ); ?></th>
										<th class="omg-compare__pro"><?php esc_html_e( 'Pro', 'clothing-store-lite' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$clothing_store_lite_features = array(
										array( __( 'One-click demo import', 'clothing-store-lite' ), true ),
										array( __( 'WooCommerce shop pages', 'clothing-store-lite' ), true ),
										array( __( 'Responsive & RTL ready', 'clothing-store-lite' ), true ),
										array( __( 'Premium demo layouts', 'clothing-store-lite' ), false ),
										array( __( 'Advanced customizer options', 'clothing-store-lite' ), false ),
										array( __( 'Priority support', 'clothing-store-lite' ), false ),
									);
									foreach ( $clothing_store_lite_features as $clothing_store_lite_feature ) {
										?>
										<tr>
											<td><?php echo esc_html( $clothing_store_lite_feature[0] ); ?></td>
											<td class="<?php echo $clothing_store_lite_feature[1] ? 'omg-yes' : 'omg-no'; ?>"><?php echo $clothing_store_lite_feature[1] ? $clothing_store_lite_check : $clothing_store_lite_dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></td>
											<td class="omg-yes"><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
							<a class="omg-btn omg-btn--demo omg-btn--block omg-compare__demo" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_DEMO_PRO ); ?>" target="_blank"><?php echo $clothing_store_lite_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'See the Pro demo', 'clothing-store-lite' ); ?> <?php echo $clothing_store_lite_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></a>
						</section>

						<section class="omg-card omg-links">
							<h3 class="omg-card__title"><?php esc_html_e( 'Important links', 'clothing-store-lite' ); ?></h3>
							<div class="omg-links__grid">
								<?php
								$clothing_store_lite_links = array(
									array( ONLINE_CLOTHING_STORE_LITE_DOCS_PRO, __( 'Free documentation', 'clothing-store-lite' ), __( 'Step-by-step setup guides', 'clothing-store-lite' ), '' ),
									array( ONLINE_CLOTHING_STORE_SUPPORT_FREE, __( 'Support forum', 'clothing-store-lite' ), __( 'Ask the community & our team', 'clothing-store-lite' ), '' ),
									array( ONLINE_CLOTHING_STORE_DEMO_PRO, __( 'Premium demo', 'clothing-store-lite' ), __( 'See every Pro layout live', 'clothing-store-lite' ), 'demo' ),
									array( ONLINE_CLOTHING_STORE_BUY_NOW, __( 'Get Premium', 'clothing-store-lite' ), __( 'Compare plans & pricing', 'clothing-store-lite' ), 'premium' ),
								);
								foreach ( $clothing_store_lite_links as $clothing_store_lite_link ) {
									?>
									<a class="omg-links__item<?php echo $clothing_store_lite_link[3] ? ' omg-links__item--' . esc_attr( $clothing_store_lite_link[3] ) : ''; ?>" href="<?php echo esc_url( $clothing_store_lite_link[0] ); ?>" target="_blank">
										<strong>
											<?php echo esc_html( $clothing_store_lite_link[1] ); ?>
											<?php if ( 'demo' === $clothing_store_lite_link[3] ) : ?>
												<em class="omg-tag omg-tag--live"><?php echo $clothing_store_lite_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'Live', 'clothing-store-lite' ); ?></em>
											<?php elseif ( 'premium' === $clothing_store_lite_link[3] ) : ?>
												<em class="omg-tag omg-tag--pro"><?php esc_html_e( 'Pro', 'clothing-store-lite' ); ?></em>
											<?php endif; ?>
										</strong>
										<span><?php echo esc_html( $clothing_store_lite_link[2] ); ?></span>
									</a>
									<?php
								}
								?>
							</div>
							<div class="omg-expert">
								<div class="omg-expert__content">
									<h3 class="omg-expert__title"><?php esc_html_e( 'Want us to set it up for you?', 'clothing-store-lite' ); ?></h3>
									<p class="omg-expert__desc"><?php esc_html_e( 'Our team can install the theme, add your products and match your brand.', 'clothing-store-lite' ); ?></p>
									<ul class="omg-expert__list">
										<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Theme & plugin setup', 'clothing-store-lite' ); ?></li>
										<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Demo content import', 'clothing-store-lite' ); ?></li>
										<li><?php echo $clothing_store_lite_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Product upload', 'clothing-store-lite' ); ?></li>
									</ul>
								</div>
								<a class="omg-btn omg-btn--primary omg-expert__btn" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_HIRE_EXPERT ); ?>" target="_blank"><?php esc_html_e( 'Hire an expert', 'clothing-store-lite' ); ?></a>
							</div>
						</section>
					</div>

				</div> <!-- .omg-main -->
			</div> <!-- .omg-layout -->

			<div class="omg-footer">
				<p>
					<?php esc_html_e( 'Enjoying the theme?', 'clothing-store-lite' ); ?>
					<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_REVIEW_FREE ); ?>" target="_blank"><?php esc_html_e( 'Leave a ★★★★★ review', 'clothing-store-lite' ); ?></a>
				</p>
				<p>
					<?php
					/* translators: %s: theme version */
					printf( esc_html__( 'Version %s', 'clothing-store-lite' ), esc_html( $clothing_store_lite_theme_version ) );
					?>
				</p>
			</div>
		</div> <!-- .omg-wrap -->
		<?php
	}


	/**
	 * Set options for the steps
	 * Incorporate any options set by the theme dev
	 * Return the array for the steps
	 * @return Array
	 */
	public function get_steps() {
		$clothing_store_lite_dev_steps = $this->config_steps;
		$clothing_store_lite_steps = array(
			'plugins' => array(
				'id'    => 'plugins',
				'title' => __( 'Install essential plugins', 'clothing-store-lite' ),
				'desc'  => __( 'Elementor, the demo importer and add-ons', 'clothing-store-lite' ),
				'view'  => 'get_step_plugins',
			),
			'demo' => array(
				'id'    => 'demo',
				'title' => __( 'Import demo content', 'clothing-store-lite' ),
				'desc'  => __( 'Pages, products and the Elementor homepage', 'clothing-store-lite' ),
				'view'  => 'get_step_demo',
			),
			'customize' => array(
				'id'    => 'customize',
				'title' => __( 'Make it yours', 'clothing-store-lite' ),
				'desc'  => __( 'Logo, colors, header and homepage', 'clothing-store-lite' ),
				'view'  => 'get_step_customize',
			),
			'launch' => array(
				'id'    => 'launch',
				'title' => __( 'Launch your store', 'clothing-store-lite' ),
				'desc'  => __( 'Preview and go live', 'clothing-store-lite' ),
				'view'  => 'get_step_launch',
			),
		);

		// Iterate through each step and replace with dev config values
		if( $clothing_store_lite_dev_steps ) {
			// Configurable elements - these are the only ones the dev can update from homepage-setup-settings.php
			$clothing_store_lite_can_config = array( 'title', 'desc' );
			foreach( $clothing_store_lite_dev_steps as $clothing_store_lite_dev_step ) {
				// We can only proceed if an ID exists and matches one of our IDs
				if( isset( $clothing_store_lite_dev_step['id'] ) ) {
					$clothing_store_lite_id = $clothing_store_lite_dev_step['id'];
					if( isset( $clothing_store_lite_steps[$clothing_store_lite_id] ) ) {
						foreach( $clothing_store_lite_can_config as $clothing_store_lite_element ) {
							if( isset( $clothing_store_lite_dev_step[$clothing_store_lite_element] ) ) {
								$clothing_store_lite_steps[$clothing_store_lite_id][$clothing_store_lite_element] = $clothing_store_lite_dev_step[$clothing_store_lite_element];
							}
						}
					}
				}
			}
		}
		return $clothing_store_lite_steps;
	}

	/**
	 * Print the content for the plugins step
	 */
	public function get_step_plugins() {
		$clothing_store_lite_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$clothing_store_lite_pending = 0;

		// Elementor and WooCommerce go first: the add-ons need them active before they can activate.
		$clothing_store_lite_plugins = $clothing_store_lite_instance->plugins;
		foreach ( array( 'woocommerce', 'elementor' ) as $clothing_store_lite_first ) {
			if ( isset( $clothing_store_lite_plugins[ $clothing_store_lite_first ] ) ) {
				$clothing_store_lite_plugins = array( $clothing_store_lite_first => $clothing_store_lite_plugins[ $clothing_store_lite_first ] ) + $clothing_store_lite_plugins;
			}
		}
		?>
		<ul class="omg-plugins">
			<?php
			foreach ( $clothing_store_lite_plugins as $clothing_store_lite_slug => $clothing_store_lite_plugin ) {
				$clothing_store_lite_is_ready = $clothing_store_lite_instance->is_plugin_active( $clothing_store_lite_slug ) && false === $clothing_store_lite_instance->does_plugin_have_update( $clothing_store_lite_slug );
				// The demo import needs Elementor and Easy Demo Import for Omega Themes.
			$clothing_store_lite_is_required = ! empty( $clothing_store_lite_plugin['required'] ) || in_array( $clothing_store_lite_slug, array( 'elementor', 'easy-demo-import-for-omega-themes' ), true );
				if ( ! $clothing_store_lite_is_ready ) {
					$clothing_store_lite_pending++;
				}
				if ( $clothing_store_lite_is_ready ) {
					$clothing_store_lite_badge = __( 'Active', 'clothing-store-lite' );
				} elseif ( $clothing_store_lite_is_required ) {
					$clothing_store_lite_badge = __( 'Required', 'clothing-store-lite' );
				} else {
					$clothing_store_lite_badge = __( 'Optional', 'clothing-store-lite' );
				}
				?>
				<li class="omg-plugin<?php echo $clothing_store_lite_is_ready ? ' is-ready' : ''; ?>" data-slug="<?php echo esc_attr( $clothing_store_lite_slug ); ?>">
					<label class="omg-plugin__label">
						<input type="checkbox" class="omg-plugin__check" value="<?php echo esc_attr( $clothing_store_lite_slug ); ?>" checked <?php disabled( $clothing_store_lite_is_ready || $clothing_store_lite_is_required ); ?>>
						<span class="omg-plugin__name"><?php echo esc_html( $clothing_store_lite_plugin['name'] ); ?></span>
					</label>
					<span class="omg-plugin__status omg-plugin__status--<?php echo $clothing_store_lite_is_ready ? 'active' : ( $clothing_store_lite_is_required ? 'required' : 'optional' ); ?>"><?php echo esc_html( $clothing_store_lite_badge ); ?></span>
				</li>
				<?php
			}
			?>
		</ul>
		<p class="omg-step__error" hidden></p>
		<?php if ( $clothing_store_lite_pending ) : ?>
			<button type="button" class="omg-btn omg-btn--primary omg-btn--block omg-install-plugins">
				<span class="omg-btn__label"><?php esc_html_e( 'Install & activate', 'clothing-store-lite' ); ?></span>
				<span class="omg-btn__count">(<?php echo esc_html( $clothing_store_lite_pending ); ?>)</span>
			</button>
			<button type="button" class="omg-skip" data-omg-complete="plugins"><?php esc_html_e( 'Skip this step', 'clothing-store-lite' ); ?></button>
		<?php else : ?>
			<p class="omg-step__note"><?php esc_html_e( 'All recommended plugins are installed and active.', 'clothing-store-lite' ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Print the content for the demo import step
	 */
	public function get_step_demo() {
		$clothing_store_lite_ready = defined( 'ELEMENTOR_VERSION' ) && class_exists( 'EDIOT_Section_Uploader' );
		?>
		<p class="omg-step__note">
			<?php
			if ( clothing_store_lite_has_inner_pages_import() ) {
				esc_html_e( 'Imports the pages, products and menu, then the Elementor homepage design, with Easy Demo Import for Omega Themes.', 'clothing-store-lite' );
			} else {
				esc_html_e( 'Imports the Elementor homepage design with Easy Demo Import for Omega Themes and sets it as your front page.', 'clothing-store-lite' );
			}
			?>
		</p>
		<?php if ( ! $clothing_store_lite_ready ) : ?>
			<p class="omg-step__note"><?php esc_html_e( 'This needs Elementor and Easy Demo Import for Omega Themes active. Install them in step 1 first.', 'clothing-store-lite' ); ?></p>
		<?php endif; ?>
		<p class="omg-step__error" hidden></p>
		<button type="button" class="omg-btn omg-btn--primary omg-btn--block omg-import-demo">
			<span class="omg-btn__label"><?php esc_html_e( 'Import demo content', 'clothing-store-lite' ); ?></span>
		</button>
		<?php
	}

	/**
	 * Print the content for the customize step
	 */
	public function get_step_customize() {
		$clothing_store_lite_customize = admin_url( 'customize.php' );
		$clothing_store_lite_targets = array(
			array( add_query_arg( 'autofocus[section]', 'title_tagline', $clothing_store_lite_customize ), __( 'Logo & title', 'clothing-store-lite' ) ),
			array( add_query_arg( 'autofocus[panel]', 'online_clothing_store_theme_colors_panel', $clothing_store_lite_customize ), __( 'Colors', 'clothing-store-lite' ) ),
			array( add_query_arg( 'autofocus[section]', 'online_clothing_store_button_header_setting', $clothing_store_lite_customize ), __( 'Header', 'clothing-store-lite' ) ),
		);
		// The demo homepage is built with Elementor, so it is edited there.
		$clothing_store_lite_front = (int) get_option( 'page_on_front' );
		if ( $clothing_store_lite_front && defined( 'ELEMENTOR_VERSION' ) && get_post_meta( $clothing_store_lite_front, '_elementor_edit_mode', true ) ) {
			$clothing_store_lite_targets[] = array( add_query_arg( array( 'post' => $clothing_store_lite_front, 'action' => 'elementor' ), admin_url( 'post.php' ) ), __( 'Edit homepage', 'clothing-store-lite' ) );
		} else {
			$clothing_store_lite_targets[] = array( add_query_arg( 'autofocus[panel]', 'online_clothing_store_theme_home_pannel', $clothing_store_lite_customize ), __( 'Homepage', 'clothing-store-lite' ) );
		}
		?>
		<div class="omg-chips">
			<?php foreach ( $clothing_store_lite_targets as $clothing_store_lite_target ) : ?>
				<a class="omg-chip" href="<?php echo esc_url( $clothing_store_lite_target[0] ); ?>" target="_blank"><?php echo esc_html( $clothing_store_lite_target[1] ); ?></a>
			<?php endforeach; ?>
		</div>
		<button type="button" class="omg-btn omg-btn--primary omg-btn--block" data-omg-complete="customize"><?php esc_html_e( 'Mark as done', 'clothing-store-lite' ); ?></button>
		<?php
	}

	/**
	 * Print the content for the final step
	 */
	public function get_step_launch() {
		?>
		<p class="omg-step__note"><?php esc_html_e( 'Check your store on desktop and mobile, then share it with your customers.', 'clothing-store-lite' ); ?></p>
		<a class="omg-btn omg-btn--primary omg-btn--block" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" data-omg-complete="launch"><?php esc_html_e( 'View your site', 'clothing-store-lite' ); ?></a>
		<?php
	}

	/**
	 * Get the plugins registered with TGMPA
	 */
	public function get_plugins() {
		$clothing_store_lite_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$clothing_store_lite_plugins = array(
			'all' 		=> array(),
			'install'	=> array(),
			'update'	=> array(),
			'activate'	=> array()
		);
		foreach( $clothing_store_lite_instance->plugins as $clothing_store_lite_slug=>$clothing_store_lite_plugin ) {
			if( $clothing_store_lite_instance->is_plugin_active( $clothing_store_lite_slug ) && false === $clothing_store_lite_instance->does_plugin_have_update( $clothing_store_lite_slug ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$clothing_store_lite_plugins['all'][$clothing_store_lite_slug] = $clothing_store_lite_plugin;
				if( ! $clothing_store_lite_instance->is_plugin_installed( $clothing_store_lite_slug ) ) {
					$clothing_store_lite_plugins['install'][$clothing_store_lite_slug] = $clothing_store_lite_plugin;
				} else {
					if( false !== $clothing_store_lite_instance->does_plugin_have_update( $clothing_store_lite_slug ) ) {
						$clothing_store_lite_plugins['update'][$clothing_store_lite_slug] = $clothing_store_lite_plugin;
					}
					if( $clothing_store_lite_instance->can_plugin_activate( $clothing_store_lite_slug ) ) {
						$clothing_store_lite_plugins['activate'][$clothing_store_lite_slug] = $clothing_store_lite_plugin;
					}
				}
			}
		}
		return $clothing_store_lite_plugins;
	}

	/**
	 * Mark a setup step as done
	 */
	public function clothing_store_lite_setup_step() {
		check_ajax_referer( 'whizzie_nonce', 'wpnonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$clothing_store_lite_step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		clothing_store_lite_set_setup_step_done( $clothing_store_lite_step );
		wp_send_json_success( clothing_store_lite_get_setup_progress() );
	}

	public function setup_plugins() {
		if ( ! check_ajax_referer( 'whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) || ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found','clothing-store-lite' ) ) );
		}
		$clothing_store_lite_json = array();
		// send back some json we use to hit up TGM
		$clothing_store_lite_plugins = $this->get_plugins();

		// what are we doing with this plugin?
		foreach ( $clothing_store_lite_plugins['activate'] as $clothing_store_lite_slug => $clothing_store_lite_plugin ) {
			if ( $_POST['slug'] == $clothing_store_lite_slug ) {
				$clothing_store_lite_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $clothing_store_lite_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating…','clothing-store-lite' ),
				);
				break;
			}
		}
		foreach ( $clothing_store_lite_plugins['update'] as $clothing_store_lite_slug => $clothing_store_lite_plugin ) {
			if ( $_POST['slug'] == $clothing_store_lite_slug ) {
				$clothing_store_lite_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $clothing_store_lite_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating…','clothing-store-lite' ),
				);
				break;
			}
		}
		foreach ( $clothing_store_lite_plugins['install'] as $clothing_store_lite_slug => $clothing_store_lite_plugin ) {
			if ( $_POST['slug'] == $clothing_store_lite_slug ) {
				$clothing_store_lite_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $clothing_store_lite_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing…','clothing-store-lite' ),
				);
				break;
			}
		}
		if ( $clothing_store_lite_json ) {
			$clothing_store_lite_json['hash'] = md5( serialize( $clothing_store_lite_json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $clothing_store_lite_json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Active','clothing-store-lite' ) ) );
		}
		exit;
	}
}
