<?php
/**
 * Getting Started page and setup wizard
 *
 * @package Online_clothing_store_Whizzie
 * @author Omega Themes
 * @since 1.0.0
 */

/**
 * Setup step ids in the order they are shown.
 */
function online_clothing_store_setup_step_ids() {
	return array( 'plugins', 'demo', 'customize', 'launch' );
}

/**
 * Check whether every plugin registered with TGMPA is active and up to date.
 */
function online_clothing_store_setup_plugins_ready() {
	if ( ! isset( $GLOBALS['tgmpa'] ) ) {
		return true;
	}
	$online_clothing_store_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	foreach ( $online_clothing_store_instance->plugins as $online_clothing_store_slug => $online_clothing_store_plugin ) {
		if ( ! $online_clothing_store_instance->is_plugin_active( $online_clothing_store_slug ) || false !== $online_clothing_store_instance->does_plugin_have_update( $online_clothing_store_slug ) ) {
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
function online_clothing_store_get_setup_progress() {
	$online_clothing_store_saved = (array) get_option( 'online_clothing_store_setup_progress', array() );

	return array(
		'plugins'   => ! empty( $online_clothing_store_saved['plugins'] ) || online_clothing_store_setup_plugins_ready(),
		'demo'      => ! empty( $online_clothing_store_saved['demo'] ),
		'customize' => ! empty( $online_clothing_store_saved['customize'] ),
		'launch'    => ! empty( $online_clothing_store_saved['launch'] ),
	);
}

/**
 * Save a step as done.
 *
 * @param string $online_clothing_store_step Step id.
 */
function online_clothing_store_set_setup_step_done( $online_clothing_store_step ) {
	if ( ! in_array( $online_clothing_store_step, online_clothing_store_setup_step_ids(), true ) ) {
		return;
	}
	$online_clothing_store_saved = (array) get_option( 'online_clothing_store_setup_progress', array() );
	$online_clothing_store_saved[ $online_clothing_store_step ] = true;
	update_option( 'online_clothing_store_setup_progress', $online_clothing_store_saved );
}

/**
 * Whether the theme gives Easy Demo Import for Omega Themes inner pages to import before the homepage.
 * Mirrors the plugin's own check for the theme's setup function.
 */
function online_clothing_store_has_inner_pages_import() {
	$online_clothing_store_function = str_replace( '-', '_', wp_get_theme()->get( 'TextDomain' ) ) . '_setup_widgets';
	if ( class_exists( 'Whizzie' ) && ! is_child_theme() ) {
		return method_exists( 'Whizzie', $online_clothing_store_function );
	}
	return class_exists( 'Child_Whizzie' ) && method_exists( 'Child_Whizzie', $online_clothing_store_function );
}

class Online_clothing_store_Whizzie {

	protected $version = '1.2.0';

	/** @var string Current theme name, used as namespace in actions. */
	protected $online_clothing_store_theme_name = '';
	protected $online_clothing_store_theme_title = '';

	/** @var string Wizard page slug and title. */
	protected $online_clothing_store_page_slug = '';
	protected $online_clothing_store_page_title = '';

	/** @var string Hook suffix returned by add_theme_page(). */
	protected $page_hook = '';

	/** @var array Wizard steps set by user. */
	protected $config_steps = array();

	/**
	 * Relative plugin url for this plugin folder
	 * @since 1.0.0
	 * @var string
	 */
	protected $online_clothing_store_plugin_url = '';

	public $online_clothing_store_plugin_path;
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
	 * @param $online_clothing_store_config	Our config parameters
	 */
	public function __construct( $online_clothing_store_config ) {
		$this->set_vars( $online_clothing_store_config );
		$this->init();
	}

	/**
	 * Set some settings
	 * @since 1.0.0
	 * @param $online_clothing_store_config	Our config parameters
	 */
	public function set_vars( $online_clothing_store_config ) {

		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/class-tgm-plugin-activation.php';
		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/tgm.php';

		if( isset( $online_clothing_store_config['online_clothing_store_page_slug'] ) ) {
			$this->online_clothing_store_page_slug = esc_attr( $online_clothing_store_config['online_clothing_store_page_slug'] );
		}
		if( isset( $online_clothing_store_config['online_clothing_store_page_title'] ) ) {
			$this->online_clothing_store_page_title = esc_attr( $online_clothing_store_config['online_clothing_store_page_title'] );
		}
		if( isset( $online_clothing_store_config['steps'] ) ) {
			$this->config_steps = $online_clothing_store_config['steps'];
		}

		$this->online_clothing_store_plugin_path = trailingslashit( dirname( __FILE__ ) );
		$online_clothing_store_relative_url = str_replace( get_template_directory(), '', $this->online_clothing_store_plugin_path );
		$this->online_clothing_store_plugin_url = trailingslashit( get_template_directory_uri() . $online_clothing_store_relative_url );
		$online_clothing_store_current_theme = wp_get_theme();
		$this->online_clothing_store_theme_title = $online_clothing_store_current_theme->get( 'Name' );
		$this->online_clothing_store_theme_name = strtolower( preg_replace( '#[^a-zA-Z]#', '', $online_clothing_store_current_theme->get( 'Name' ) ) );
		$this->online_clothing_store_page_slug = apply_filters( $this->online_clothing_store_theme_name . '_theme_setup_wizard_online_clothing_store_page_slug', $this->online_clothing_store_theme_name . '-wizard' );
		$this->parent_slug = apply_filters( $this->online_clothing_store_theme_name . '_theme_setup_wizard_parent_slug', '' );

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
		add_action( 'wp_ajax_online_clothing_store_setup_step', array( $this, 'online_clothing_store_setup_step' ) );

	}

	/**
	 * Load assets on the Getting Started page, and wherever the welcome notice is shown.
	 *
	 * @param string $online_clothing_store_hook Current admin page hook suffix.
	 */
	public function enqueue_scripts( $online_clothing_store_hook ) {
		$online_clothing_store_is_setup_page = ( $online_clothing_store_hook === $this->page_hook );
		$online_clothing_store_has_notice    = function_exists( 'online_clothing_store_show_admin_notice' ) && online_clothing_store_show_admin_notice();

		if ( ! $online_clothing_store_is_setup_page && ! $online_clothing_store_has_notice ) {
			return;
		}

		$online_clothing_store_version = wp_get_theme()->get( 'Version' );

		// The bundled Plus Jakarta Sans is Regular only, so load real weights through the local webfont loader.
		require_once get_theme_file_path( 'lib/custom/css/wptt-webfont-loader.php' );
		wp_enqueue_style( 'online-clothing-store-setup-fonts', wptt_get_webfont_url( 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap' ), array(), $online_clothing_store_version );
		// Version the page assets by file time so browsers and caching plugins never serve a stale copy after an update.
		$online_clothing_store_css = 'inc/homepage-setup/assets/css/homepage-setup-style.css';
		$online_clothing_store_js  = 'inc/homepage-setup/assets/js/homepage-setup-script.js';
		wp_enqueue_style( 'online-clothing-store-homepage-setup-style', get_theme_file_uri( $online_clothing_store_css ), array( 'online-clothing-store-setup-fonts' ), $online_clothing_store_version . '.' . filemtime( get_theme_file_path( $online_clothing_store_css ) ) );
		wp_register_script( 'online-clothing-store-homepage-setup-script', get_theme_file_uri( $online_clothing_store_js ), array( 'jquery' ), $online_clothing_store_version . '.' . filemtime( get_theme_file_path( $online_clothing_store_js ) ), true );
		wp_localize_script(
			'online-clothing-store-homepage-setup-script',
			'online_clothing_store_setup',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wpnonce' => wp_create_nonce( 'whizzie_nonce' ),
				'importSteps' => $this->get_import_steps(),
				'importSaves' => false,
				'i18n'    => array(
					'installing' => esc_html__( 'Working…', 'online-clothing-store' ),
					'active'     => esc_html__( 'Active', 'online-clothing-store' ),
					'failed'     => esc_html__( 'Failed', 'online-clothing-store' ),
					'install'    => esc_html__( 'Install & activate', 'online-clothing-store' ),
					'installed'  => esc_html__( 'Plugins ready', 'online-clothing-store' ),
					'pluginsErr' => esc_html__( 'Some plugins could not be installed. Try again, or install them from the plugins screen.', 'online-clothing-store' ),
					'importing'  => esc_html__( 'Importing demo…', 'online-clothing-store' ),
					'imported'   => esc_html__( 'Demo imported', 'online-clothing-store' ),
					'importErr'  => esc_html__( 'The import did not finish. Make sure Elementor and Easy Demo Import for Omega Themes are active, then try again.', 'online-clothing-store' ),
					'copied'     => esc_html__( 'Copied!', 'online-clothing-store' ),
				),
			)
		);
		wp_enqueue_script( 'online-clothing-store-homepage-setup-script' );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function tgmpa_load( $online_clothing_store_status ) {
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
		$online_clothing_store_front = (int) get_option( 'page_on_front' );
		if ( $online_clothing_store_front && $online_clothing_store_front !== $this->front_before && get_post_meta( $online_clothing_store_front, '_elementor_edit_mode', true ) ) {
			online_clothing_store_set_setup_step_done( 'demo' );
		}
	}

	/**
	 * Easy Demo Import for Omega Themes requests, run in order by the import button.
	 */
	public function get_import_steps() {
		$online_clothing_store_steps = array();
		if ( online_clothing_store_has_inner_pages_import() ) {
			$online_clothing_store_steps[] = array( 'action' => 'ediot_import_inner_pages_data', 'nonce' => wp_create_nonce( 'ediot_import_pages_nonce' ) );
		}
		$online_clothing_store_steps[] = array( 'action' => 'ediot_import_demo_content', 'nonce' => wp_create_nonce( 'ediot_import_demo_nonce' ) );
		return $online_clothing_store_steps;
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
	 * Update $tgmpa_menu_slug and $online_clothing_store_tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function set_tgmpa_url() {
		$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug = apply_filters( $this->online_clothing_store_theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );
		$online_clothing_store_tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';
		$this->tgmpa_url = apply_filters( $this->online_clothing_store_theme_name . '_theme_setup_wizard_tgmpa_url', $online_clothing_store_tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );
	}

	/**
	 * Register the Getting Started page under Appearance
	 */
	public function menu_page() {
		$this->page_hook = add_theme_page( esc_html( $this->online_clothing_store_page_title ), esc_html( $this->online_clothing_store_page_title ), 'manage_options', $this->online_clothing_store_page_slug, array( $this, 'wizard_page' ) );
	}

	/**
	 * Small inline SVG icons used on the page
	 *
	 * @param string $online_clothing_store_name Icon name.
	 */
	public function icon( $online_clothing_store_name ) {
		$online_clothing_store_icons = array(
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
		return isset( $online_clothing_store_icons[ $online_clothing_store_name ] ) ? $online_clothing_store_icons[ $online_clothing_store_name ] : '';
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
			die( esc_html__( 'Failed to find TGM', 'online-clothing-store' ) );
		}

		$online_clothing_store_url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );
		$online_clothing_store_method = '';
		$online_clothing_store_fields = array_keys( $_POST );

		if ( false === ( $online_clothing_store_creds = request_filesystem_credentials( esc_url_raw( $online_clothing_store_url ), $online_clothing_store_method, false, false, $online_clothing_store_fields ) ) ) {
			return true;
		}

		if ( ! WP_Filesystem( $online_clothing_store_creds ) ) {
			request_filesystem_credentials( esc_url_raw( $online_clothing_store_url ), $online_clothing_store_method, true, false, $online_clothing_store_fields );
			return true;
		}

		$online_clothing_store_theme = wp_get_theme();
		$online_clothing_store_theme_title = $online_clothing_store_theme->get( 'Name' );
		$online_clothing_store_theme_version = $online_clothing_store_theme->get( 'Version' );
		$online_clothing_store_theme_desc = wp_trim_words( $online_clothing_store_theme->get( 'Description' ), 28 );

		$online_clothing_store_steps = $this->get_steps();
		$online_clothing_store_progress = online_clothing_store_get_setup_progress();
		$online_clothing_store_focus = ! empty( $_GET['omg-focus'] ) && empty( $online_clothing_store_progress['demo'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display flag only.
		$online_clothing_store_done = count( array_filter( $online_clothing_store_progress ) );
		$online_clothing_store_total = count( $online_clothing_store_steps );

		// The first unfinished step starts open.
		$online_clothing_store_open = '';
		foreach ( $online_clothing_store_steps as $online_clothing_store_id => $online_clothing_store_step ) {
			if ( empty( $online_clothing_store_progress[ $online_clothing_store_id ] ) ) {
				$online_clothing_store_open = $online_clothing_store_id;
				break;
			}
		}

		$online_clothing_store_images = get_template_directory_uri() . '/inc/homepage-setup/assets/homepage-setup-images/';
		$online_clothing_store_check = wp_kses( $this->icon( 'check' ), $this->icon_kses() );
		$online_clothing_store_dash  = wp_kses( $this->icon( 'dash' ), $this->icon_kses() );
		$online_clothing_store_arrow = wp_kses( $this->icon( 'arrow' ), $this->icon_kses() );
		$online_clothing_store_crown = wp_kses( $this->icon( 'crown' ), $this->icon_kses() );
		$online_clothing_store_live  = '<span class="omg-live-dot" aria-hidden="true"></span>';
		?>
		<div class="wrap omg-wrap<?php echo $online_clothing_store_focus ? ' is-focus' : ''; ?>">

			<div class="omg-header">
				<div class="omg-header__text">
					<h1 class="omg-title">
						<?php
						/* translators: %s: theme name */
						printf( esc_html__( 'Welcome to %s', 'online-clothing-store' ), esc_html( $online_clothing_store_theme_title ) );
						?>
					</h1>
				</div>
				<div class="omg-header__actions">
					<a class="omg-btn omg-btn--demo" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_DEMO_PRO ); ?>" target="_blank"><?php echo $online_clothing_store_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'Live Demo', 'online-clothing-store' ); ?></a>
					<a class="omg-btn omg-btn--premium" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php echo $online_clothing_store_crown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Go To Premium', 'online-clothing-store' ); ?></a>
				</div>
			</div>
			<hr class="wp-header-end">

			<div class="omg-layout">

				<aside class="omg-card omg-setup<?php echo $online_clothing_store_done >= $online_clothing_store_total ? ' is-complete' : ''; ?>" id="omg-setup" data-total="<?php echo esc_attr( $online_clothing_store_total ); ?>">
					<div class="omg-setup__head">
						<h2 class="omg-setup__title"><?php esc_html_e( 'Site setup', 'online-clothing-store' ); ?></h2>
						<span class="omg-setup__count">
							<?php
							/* translators: 1: steps done, 2: total steps */
							printf( esc_html__( '%1$s of %2$s done', 'online-clothing-store' ), '<b class="omg-setup__done">' . esc_html( $online_clothing_store_done ) . '</b>', esc_html( $online_clothing_store_total ) );
							?>
						</span>
					</div>
					<div class="omg-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $online_clothing_store_total ); ?>" aria-valuenow="<?php echo esc_attr( $online_clothing_store_done ); ?>">
						<span class="omg-progress__bar" style="width: <?php echo esc_attr( max( 3, round( $online_clothing_store_done / $online_clothing_store_total * 100 ) ) ); ?>%"></span>
					</div>
					<p class="omg-setup__intro"><?php esc_html_e( 'Finish these steps to make your store look like the demo in a few minutes.', 'online-clothing-store' ); ?></p>

					<div class="omg-setup__complete">
						<span class="omg-setup__complete-icon"><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></span>
						<div>
							<strong><?php esc_html_e( 'Your store is ready', 'online-clothing-store' ); ?></strong>
							<span><?php esc_html_e( 'All setup steps are done. Time to add your products.', 'online-clothing-store' ); ?></span>
						</div>
					</div>

					<ol class="omg-steps">
						<?php
						$online_clothing_store_number = 1;
						foreach ( $online_clothing_store_steps as $online_clothing_store_id => $online_clothing_store_step ) {
							$online_clothing_store_classes = array( 'omg-step' );
							if ( ! empty( $online_clothing_store_progress[ $online_clothing_store_id ] ) ) {
								$online_clothing_store_classes[] = 'is-done';
							}
							if ( $online_clothing_store_id === $online_clothing_store_open ) {
								$online_clothing_store_classes[] = 'is-open';
							}
							?>
							<li class="<?php echo esc_attr( implode( ' ', $online_clothing_store_classes ) ); ?>" data-step="<?php echo esc_attr( $online_clothing_store_id ); ?>">
								<button type="button" class="omg-step__head" aria-expanded="<?php echo $online_clothing_store_id === $online_clothing_store_open ? 'true' : 'false'; ?>">
									<span class="omg-step__num">
										<span class="omg-step__index"><?php echo esc_html( $online_clothing_store_number ); ?></span>
										<?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
									</span>
									<span class="omg-step__text">
										<span class="omg-step__title"><?php echo esc_html( $online_clothing_store_step['title'] ); ?></span>
										<span class="omg-step__desc"><?php echo esc_html( $online_clothing_store_step['desc'] ); ?></span>
									</span>
								</button>
								<div class="omg-step__body">
									<div class="omg-step__inner">
										<?php call_user_func( array( $this, $online_clothing_store_step['view'] ) ); ?>
									</div>
								</div>
							</li>
							<?php
							$online_clothing_store_number++;
						}
						?>
					</ol>

					<p class="omg-setup__help">
						<?php esc_html_e( 'Stuck on a step?', 'online-clothing-store' ); ?>
						<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_LITE_DOCS_PRO ); ?>" target="_blank"><?php esc_html_e( 'Read the setup guide', 'online-clothing-store' ); ?></a>
						<?php esc_html_e( 'or', 'online-clothing-store' ); ?>
						<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_SUPPORT_FREE ); ?>" target="_blank"><?php esc_html_e( 'ask on the forum', 'online-clothing-store' ); ?></a>.
					</p>
				</aside>

				<?php if ( $online_clothing_store_focus ) : ?>
					<div class="omg-focus-tip" role="dialog" aria-labelledby="omg-focus-tip-title">
						<span class="omg-focus-tip__icon"><?php echo wp_kses( $this->icon( 'download' ), $this->icon_kses() ); ?></span>
						<p class="omg-kicker omg-kicker--blue"><?php esc_html_e( 'Quick setup', 'online-clothing-store' ); ?></p>
						<h3 class="omg-focus-tip__title" id="omg-focus-tip-title"><?php esc_html_e( 'Let’s import your demo', 'online-clothing-store' ); ?></h3>
						<p class="omg-focus-tip__text"><?php esc_html_e( 'Install the essential plugins, then import the demo content. The rest of this page unlocks as soon as the import finishes.', 'online-clothing-store' ); ?></p>
						<div class="omg-focus-tip__actions">
							<button type="button" class="omg-btn omg-btn--primary omg-focus-ok"><?php esc_html_e( 'Got it', 'online-clothing-store' ); ?></button>
							<button type="button" class="omg-skip omg-focus-exit"><?php esc_html_e( 'Skip, show everything', 'online-clothing-store' ); ?></button>
						</div>
					</div>
				<?php endif; ?>

				<div class="omg-main">

					<section class="omg-card omg-hero">
						<div class="omg-hero__content">
							<div class="omg-badges">
								<span class="omg-badge omg-badge--blue"><?php esc_html_e( 'Active theme', 'online-clothing-store' ); ?></span>
								<span class="omg-badge"><?php esc_html_e( 'Free', 'online-clothing-store' ); ?></span>
							</div>
							<h2 class="omg-hero__title"><?php echo esc_html( $online_clothing_store_theme_title ); ?></h2>
							<p class="omg-hero__desc"><?php echo esc_html( $online_clothing_store_theme_desc ); ?></p>
							<div class="omg-hero__actions">
								<a class="omg-btn omg-btn--primary" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize theme', 'online-clothing-store' ); ?></a>
								<a class="omg-btn omg-btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your site', 'online-clothing-store' ); ?></a>
								<a class="omg-btn omg-btn--pink" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php echo $online_clothing_store_crown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Upgrade to Pro', 'online-clothing-store' ); ?></a>
								<a class="omg-btn omg-btn--dark" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Get the Bundle', 'online-clothing-store' ); ?></a>
							</div>
						</div>
						<div class="omg-hero__media">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/screenshot.png' ); ?>" alt="<?php echo esc_attr( $online_clothing_store_theme_title ); ?>">
						</div>
					</section>

					<div class="omg-promos">
						<section class="omg-card omg-pro">
							<span class="omg-pro__ribbon"><?php esc_html_e( '25% Off', 'online-clothing-store' ); ?></span>
							<p class="omg-kicker omg-kicker--pink"><?php esc_html_e( 'Go Pro', 'online-clothing-store' ); ?></p>
							<h3 class="omg-pro__title">
								<?php
								/* translators: %s: theme name */
								printf( esc_html__( 'Unlock %s Pro', 'online-clothing-store' ), esc_html( $online_clothing_store_theme_title ) );
								?>
							</h3>
							<div class="omg-pro__links">
								<?php
								$online_clothing_store_pro_links = array(
									array( ONLINE_CLOTHING_STORE_DEMO_PRO, 'eye', __( 'Live demo', 'online-clothing-store' ), true ),
									array( ONLINE_CLOTHING_STORE_DOCS_PRO, 'book', __( 'Pro docs', 'online-clothing-store' ), true ),
									array( '#omg-compare', 'table', __( 'Free vs Pro', 'online-clothing-store' ), false ),
								);
								foreach ( $online_clothing_store_pro_links as $online_clothing_store_pro_link ) {
									?>
									<a class="omg-pro__link" href="<?php echo esc_url( $online_clothing_store_pro_link[0] ); ?>"<?php echo $online_clothing_store_pro_link[3] ? ' target="_blank"' : ''; ?>>
										<?php echo wp_kses( $this->icon( $online_clothing_store_pro_link[1] ), $this->icon_kses() ); ?>
										<span><?php echo esc_html( $online_clothing_store_pro_link[2] ); ?></span>
									</a>
									<?php
								}
								?>
							</div>
							<ul class="omg-checklist">
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'All premium demo layouts', 'online-clothing-store' ); ?></li>
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Advanced customizer controls', 'online-clothing-store' ); ?></li>
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Priority support & updates', 'online-clothing-store' ); ?></li>
							</ul>
							<div class="omg-code">
								<span class="omg-code__label"><?php esc_html_e( 'Code', 'online-clothing-store' ); ?></span>
								<code class="omg-code__value">NYTHEMES25</code>
								<button type="button" class="omg-code__copy" data-omg-copy="NYTHEMES25"><?php esc_html_e( 'Copy', 'online-clothing-store' ); ?></button>
							</div>
							<p class="omg-pro__price">
								<strong>$39</strong>
								<span><?php esc_html_e( 'Pro license · save 25% with the code above', 'online-clothing-store' ); ?></span>
							</p>
							<a class="omg-btn omg-btn--pink omg-btn--block" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e( 'Upgrade to Pro — just $39', 'online-clothing-store' ); ?></a>
						</section>

						<section class="omg-card omg-bundle omg-glow-border">
							<div class="omg-bundle__head">
								<div>
									<p class="omg-kicker omg-kicker--pink"><?php esc_html_e( 'Best value', 'online-clothing-store' ); ?></p>
									<h3 class="omg-bundle__title"><?php esc_html_e( 'WP Theme Bundle', 'online-clothing-store' ); ?></h3>
								</div>
								<span class="omg-bundle__seal"><small><?php esc_html_e( 'Extra', 'online-clothing-store' ); ?></small><b>15%</b><small><?php esc_html_e( 'Off', 'online-clothing-store' ); ?></small></span>
							</div>
							<div class="omg-bundle__media">
								<img src="<?php echo esc_url( $online_clothing_store_images . 'bundle-banner.png' ); ?>" alt="<?php esc_attr_e( 'Theme bundle collage', 'online-clothing-store' ); ?>">
							</div>
							<p class="omg-bundle__desc"><?php esc_html_e( 'Every Omega WordPress theme in one download.', 'online-clothing-store' ); ?></p>
							<ul class="omg-checklist">
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'All premium Omega themes included', 'online-clothing-store' ); ?></li>
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Updates for every theme in the bundle', 'online-clothing-store' ); ?></li>
								<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Priority support on all themes', 'online-clothing-store' ); ?></li>
							</ul>
							<p class="omg-price"><del>$2795</del><strong>$69</strong></p>
							<div class="omg-code omg-code--light">
								<span class="omg-code__label"><?php esc_html_e( 'Code', 'online-clothing-store' ); ?></span>
								<code class="omg-code__value">BNDL15OFF</code>
								<button type="button" class="omg-code__copy" data-omg-copy="BNDL15OFF"><?php esc_html_e( 'Copy', 'online-clothing-store' ); ?></button>
							</div>
							<a class="omg-btn omg-btn--dark omg-btn--block" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_BUNDLE_BUTTON ); ?>" target="_blank"><?php esc_html_e( 'Shop the bundle', 'online-clothing-store' ); ?></a>
						</section>
					</div>

					<div class="omg-info">
						<section class="omg-card omg-compare" id="omg-compare">
							<h3 class="omg-card__title"><?php esc_html_e( 'Free vs Pro', 'online-clothing-store' ); ?></h3>
							<table class="omg-compare__table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Feature', 'online-clothing-store' ); ?></th>
										<th><?php esc_html_e( 'Free', 'online-clothing-store' ); ?></th>
										<th class="omg-compare__pro"><?php esc_html_e( 'Pro', 'online-clothing-store' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$online_clothing_store_features = array(
										array( __( 'One-click demo import', 'online-clothing-store' ), true ),
										array( __( 'WooCommerce shop pages', 'online-clothing-store' ), true ),
										array( __( 'Responsive & RTL ready', 'online-clothing-store' ), true ),
										array( __( 'Premium demo layouts', 'online-clothing-store' ), false ),
										array( __( 'Advanced customizer options', 'online-clothing-store' ), false ),
										array( __( 'Priority support', 'online-clothing-store' ), false ),
									);
									foreach ( $online_clothing_store_features as $online_clothing_store_feature ) {
										?>
										<tr>
											<td><?php echo esc_html( $online_clothing_store_feature[0] ); ?></td>
											<td class="<?php echo $online_clothing_store_feature[1] ? 'omg-yes' : 'omg-no'; ?>"><?php echo $online_clothing_store_feature[1] ? $online_clothing_store_check : $online_clothing_store_dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></td>
											<td class="omg-yes"><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
							<a class="omg-btn omg-btn--demo omg-btn--block omg-compare__demo" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_DEMO_PRO ); ?>" target="_blank"><?php echo $online_clothing_store_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'See the Pro demo', 'online-clothing-store' ); ?> <?php echo $online_clothing_store_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></a>
						</section>

						<section class="omg-card omg-links">
							<h3 class="omg-card__title"><?php esc_html_e( 'Important links', 'online-clothing-store' ); ?></h3>
							<div class="omg-links__grid">
								<?php
								$online_clothing_store_links = array(
									array( ONLINE_CLOTHING_STORE_LITE_DOCS_PRO, __( 'Free documentation', 'online-clothing-store' ), __( 'Step-by-step setup guides', 'online-clothing-store' ), '' ),
									array( ONLINE_CLOTHING_STORE_SUPPORT_FREE, __( 'Support forum', 'online-clothing-store' ), __( 'Ask the community & our team', 'online-clothing-store' ), '' ),
									array( ONLINE_CLOTHING_STORE_DEMO_PRO, __( 'Premium demo', 'online-clothing-store' ), __( 'See every Pro layout live', 'online-clothing-store' ), 'demo' ),
									array( ONLINE_CLOTHING_STORE_BUY_NOW, __( 'Get Premium', 'online-clothing-store' ), __( 'Compare plans & pricing', 'online-clothing-store' ), 'premium' ),
								);
								foreach ( $online_clothing_store_links as $online_clothing_store_link ) {
									?>
									<a class="omg-links__item<?php echo $online_clothing_store_link[3] ? ' omg-links__item--' . esc_attr( $online_clothing_store_link[3] ) : ''; ?>" href="<?php echo esc_url( $online_clothing_store_link[0] ); ?>" target="_blank">
										<strong>
											<?php echo esc_html( $online_clothing_store_link[1] ); ?>
											<?php if ( 'demo' === $online_clothing_store_link[3] ) : ?>
												<em class="omg-tag omg-tag--live"><?php echo $online_clothing_store_live; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?><?php esc_html_e( 'Live', 'online-clothing-store' ); ?></em>
											<?php elseif ( 'premium' === $online_clothing_store_link[3] ) : ?>
												<em class="omg-tag omg-tag--pro"><?php esc_html_e( 'Pro', 'online-clothing-store' ); ?></em>
											<?php endif; ?>
										</strong>
										<span><?php echo esc_html( $online_clothing_store_link[2] ); ?></span>
									</a>
									<?php
								}
								?>
							</div>
							<div class="omg-expert">
								<div class="omg-expert__content">
									<h3 class="omg-expert__title"><?php esc_html_e( 'Want us to set it up for you?', 'online-clothing-store' ); ?></h3>
									<p class="omg-expert__desc"><?php esc_html_e( 'Our team can install the theme, add your products and match your brand.', 'online-clothing-store' ); ?></p>
									<ul class="omg-expert__list">
										<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Theme & plugin setup', 'online-clothing-store' ); ?></li>
										<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Demo content import', 'online-clothing-store' ); ?></li>
										<li><?php echo $online_clothing_store_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?><?php esc_html_e( 'Product upload', 'online-clothing-store' ); ?></li>
									</ul>
								</div>
								<a class="omg-btn omg-btn--primary omg-expert__btn" href="<?php echo esc_url( ONLINE_CLOTHING_STORE_HIRE_EXPERT ); ?>" target="_blank"><?php esc_html_e( 'Hire an expert', 'online-clothing-store' ); ?></a>
							</div>
						</section>
					</div>

				</div> <!-- .omg-main -->
			</div> <!-- .omg-layout -->

			<div class="omg-footer">
				<p>
					<?php esc_html_e( 'Enjoying the theme?', 'online-clothing-store' ); ?>
					<a href="<?php echo esc_url( ONLINE_CLOTHING_STORE_REVIEW_FREE ); ?>" target="_blank"><?php esc_html_e( 'Leave a ★★★★★ review', 'online-clothing-store' ); ?></a>
				</p>
				<p>
					<?php
					/* translators: %s: theme version */
					printf( esc_html__( 'Version %s', 'online-clothing-store' ), esc_html( $online_clothing_store_theme_version ) );
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
		$online_clothing_store_dev_steps = $this->config_steps;
		$online_clothing_store_steps = array(
			'plugins' => array(
				'id'    => 'plugins',
				'title' => __( 'Install essential plugins', 'online-clothing-store' ),
				'desc'  => __( 'Elementor, the demo importer and add-ons', 'online-clothing-store' ),
				'view'  => 'get_step_plugins',
			),
			'demo' => array(
				'id'    => 'demo',
				'title' => __( 'Import demo content', 'online-clothing-store' ),
				'desc'  => __( 'Pages, products and the Elementor homepage', 'online-clothing-store' ),
				'view'  => 'get_step_demo',
			),
			'customize' => array(
				'id'    => 'customize',
				'title' => __( 'Make it yours', 'online-clothing-store' ),
				'desc'  => __( 'Logo, colors, header and homepage', 'online-clothing-store' ),
				'view'  => 'get_step_customize',
			),
			'launch' => array(
				'id'    => 'launch',
				'title' => __( 'Launch your store', 'online-clothing-store' ),
				'desc'  => __( 'Preview and go live', 'online-clothing-store' ),
				'view'  => 'get_step_launch',
			),
		);

		// Iterate through each step and replace with dev config values
		if( $online_clothing_store_dev_steps ) {
			// Configurable elements - these are the only ones the dev can update from homepage-setup-settings.php
			$online_clothing_store_can_config = array( 'title', 'desc' );
			foreach( $online_clothing_store_dev_steps as $online_clothing_store_dev_step ) {
				// We can only proceed if an ID exists and matches one of our IDs
				if( isset( $online_clothing_store_dev_step['id'] ) ) {
					$online_clothing_store_id = $online_clothing_store_dev_step['id'];
					if( isset( $online_clothing_store_steps[$online_clothing_store_id] ) ) {
						foreach( $online_clothing_store_can_config as $online_clothing_store_element ) {
							if( isset( $online_clothing_store_dev_step[$online_clothing_store_element] ) ) {
								$online_clothing_store_steps[$online_clothing_store_id][$online_clothing_store_element] = $online_clothing_store_dev_step[$online_clothing_store_element];
							}
						}
					}
				}
			}
		}
		return $online_clothing_store_steps;
	}

	/**
	 * Print the content for the plugins step
	 */
	public function get_step_plugins() {
		$online_clothing_store_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$online_clothing_store_pending = 0;

		// Elementor and WooCommerce go first: the add-ons need them active before they can activate.
		$online_clothing_store_plugins = $online_clothing_store_instance->plugins;
		foreach ( array( 'woocommerce', 'elementor' ) as $online_clothing_store_first ) {
			if ( isset( $online_clothing_store_plugins[ $online_clothing_store_first ] ) ) {
				$online_clothing_store_plugins = array( $online_clothing_store_first => $online_clothing_store_plugins[ $online_clothing_store_first ] ) + $online_clothing_store_plugins;
			}
		}
		?>
		<ul class="omg-plugins">
			<?php
			foreach ( $online_clothing_store_plugins as $online_clothing_store_slug => $online_clothing_store_plugin ) {
				$online_clothing_store_is_ready = $online_clothing_store_instance->is_plugin_active( $online_clothing_store_slug ) && false === $online_clothing_store_instance->does_plugin_have_update( $online_clothing_store_slug );
				// The demo import needs Elementor and Easy Demo Import for Omega Themes.
			$online_clothing_store_is_required = ! empty( $online_clothing_store_plugin['required'] ) || in_array( $online_clothing_store_slug, array( 'elementor', 'easy-demo-import-for-omega-themes' ), true );
				if ( ! $online_clothing_store_is_ready ) {
					$online_clothing_store_pending++;
				}
				if ( $online_clothing_store_is_ready ) {
					$online_clothing_store_badge = __( 'Active', 'online-clothing-store' );
				} elseif ( $online_clothing_store_is_required ) {
					$online_clothing_store_badge = __( 'Required', 'online-clothing-store' );
				} else {
					$online_clothing_store_badge = __( 'Optional', 'online-clothing-store' );
				}
				?>
				<li class="omg-plugin<?php echo $online_clothing_store_is_ready ? ' is-ready' : ''; ?>" data-slug="<?php echo esc_attr( $online_clothing_store_slug ); ?>">
					<label class="omg-plugin__label">
						<input type="checkbox" class="omg-plugin__check" value="<?php echo esc_attr( $online_clothing_store_slug ); ?>" checked <?php disabled( $online_clothing_store_is_ready || $online_clothing_store_is_required ); ?>>
						<span class="omg-plugin__name"><?php echo esc_html( $online_clothing_store_plugin['name'] ); ?></span>
					</label>
					<span class="omg-plugin__status omg-plugin__status--<?php echo $online_clothing_store_is_ready ? 'active' : ( $online_clothing_store_is_required ? 'required' : 'optional' ); ?>"><?php echo esc_html( $online_clothing_store_badge ); ?></span>
				</li>
				<?php
			}
			?>
		</ul>
		<p class="omg-step__error" hidden></p>
		<?php if ( $online_clothing_store_pending ) : ?>
			<button type="button" class="omg-btn omg-btn--primary omg-btn--block omg-install-plugins">
				<span class="omg-btn__label"><?php esc_html_e( 'Install & activate', 'online-clothing-store' ); ?></span>
				<span class="omg-btn__count">(<?php echo esc_html( $online_clothing_store_pending ); ?>)</span>
			</button>
			<button type="button" class="omg-skip" data-omg-complete="plugins"><?php esc_html_e( 'Skip this step', 'online-clothing-store' ); ?></button>
		<?php else : ?>
			<p class="omg-step__note"><?php esc_html_e( 'All recommended plugins are installed and active.', 'online-clothing-store' ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Print the content for the demo import step
	 */
	public function get_step_demo() {
		$online_clothing_store_ready = defined( 'ELEMENTOR_VERSION' ) && class_exists( 'EDIOT_Section_Uploader' );
		?>
		<p class="omg-step__note">
			<?php
			if ( online_clothing_store_has_inner_pages_import() ) {
				esc_html_e( 'Imports the pages, products and menu, then the Elementor homepage design, with Easy Demo Import for Omega Themes.', 'online-clothing-store' );
			} else {
				esc_html_e( 'Imports the Elementor homepage design with Easy Demo Import for Omega Themes and sets it as your front page.', 'online-clothing-store' );
			}
			?>
		</p>
		<?php if ( ! $online_clothing_store_ready ) : ?>
			<p class="omg-step__note"><?php esc_html_e( 'This needs Elementor and Easy Demo Import for Omega Themes active. Install them in step 1 first.', 'online-clothing-store' ); ?></p>
		<?php endif; ?>
		<p class="omg-step__error" hidden></p>
		<button type="button" class="omg-btn omg-btn--primary omg-btn--block omg-import-demo">
			<span class="omg-btn__label"><?php esc_html_e( 'Import demo content', 'online-clothing-store' ); ?></span>
		</button>
		<?php
	}

	/**
	 * Print the content for the customize step
	 */
	public function get_step_customize() {
		$online_clothing_store_customize = admin_url( 'customize.php' );
		$online_clothing_store_targets = array(
			array( add_query_arg( 'autofocus[section]', 'title_tagline', $online_clothing_store_customize ), __( 'Logo & title', 'online-clothing-store' ) ),
			array( add_query_arg( 'autofocus[panel]', 'online_clothing_store_theme_colors_panel', $online_clothing_store_customize ), __( 'Colors', 'online-clothing-store' ) ),
			array( add_query_arg( 'autofocus[section]', 'online_clothing_store_button_header_setting', $online_clothing_store_customize ), __( 'Header', 'online-clothing-store' ) ),
		);
		// The demo homepage is built with Elementor, so it is edited there.
		$online_clothing_store_front = (int) get_option( 'page_on_front' );
		if ( $online_clothing_store_front && defined( 'ELEMENTOR_VERSION' ) && get_post_meta( $online_clothing_store_front, '_elementor_edit_mode', true ) ) {
			$online_clothing_store_targets[] = array( add_query_arg( array( 'post' => $online_clothing_store_front, 'action' => 'elementor' ), admin_url( 'post.php' ) ), __( 'Edit homepage', 'online-clothing-store' ) );
		} else {
			$online_clothing_store_targets[] = array( add_query_arg( 'autofocus[panel]', 'online_clothing_store_theme_home_pannel', $online_clothing_store_customize ), __( 'Homepage', 'online-clothing-store' ) );
		}
		?>
		<div class="omg-chips">
			<?php foreach ( $online_clothing_store_targets as $online_clothing_store_target ) : ?>
				<a class="omg-chip" href="<?php echo esc_url( $online_clothing_store_target[0] ); ?>" target="_blank"><?php echo esc_html( $online_clothing_store_target[1] ); ?></a>
			<?php endforeach; ?>
		</div>
		<button type="button" class="omg-btn omg-btn--primary omg-btn--block" data-omg-complete="customize"><?php esc_html_e( 'Mark as done', 'online-clothing-store' ); ?></button>
		<?php
	}

	/**
	 * Print the content for the final step
	 */
	public function get_step_launch() {
		?>
		<p class="omg-step__note"><?php esc_html_e( 'Check your store on desktop and mobile, then share it with your customers.', 'online-clothing-store' ); ?></p>
		<a class="omg-btn omg-btn--primary omg-btn--block" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" data-omg-complete="launch"><?php esc_html_e( 'View your site', 'online-clothing-store' ); ?></a>
		<?php
	}

	/**
	 * Get the plugins registered with TGMPA
	 */
	public function get_plugins() {
		$online_clothing_store_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$online_clothing_store_plugins = array(
			'all' 		=> array(),
			'install'	=> array(),
			'update'	=> array(),
			'activate'	=> array()
		);
		foreach( $online_clothing_store_instance->plugins as $online_clothing_store_slug=>$online_clothing_store_plugin ) {
			if( $online_clothing_store_instance->is_plugin_active( $online_clothing_store_slug ) && false === $online_clothing_store_instance->does_plugin_have_update( $online_clothing_store_slug ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$online_clothing_store_plugins['all'][$online_clothing_store_slug] = $online_clothing_store_plugin;
				if( ! $online_clothing_store_instance->is_plugin_installed( $online_clothing_store_slug ) ) {
					$online_clothing_store_plugins['install'][$online_clothing_store_slug] = $online_clothing_store_plugin;
				} else {
					if( false !== $online_clothing_store_instance->does_plugin_have_update( $online_clothing_store_slug ) ) {
						$online_clothing_store_plugins['update'][$online_clothing_store_slug] = $online_clothing_store_plugin;
					}
					if( $online_clothing_store_instance->can_plugin_activate( $online_clothing_store_slug ) ) {
						$online_clothing_store_plugins['activate'][$online_clothing_store_slug] = $online_clothing_store_plugin;
					}
				}
			}
		}
		return $online_clothing_store_plugins;
	}

	/**
	 * Mark a setup step as done
	 */
	public function online_clothing_store_setup_step() {
		check_ajax_referer( 'whizzie_nonce', 'wpnonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$online_clothing_store_step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		online_clothing_store_set_setup_step_done( $online_clothing_store_step );
		wp_send_json_success( online_clothing_store_get_setup_progress() );
	}

	public function setup_plugins() {
		if ( ! check_ajax_referer( 'whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) || ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found','online-clothing-store' ) ) );
		}
		$online_clothing_store_json = array();
		// send back some json we use to hit up TGM
		$online_clothing_store_plugins = $this->get_plugins();

		// what are we doing with this plugin?
		foreach ( $online_clothing_store_plugins['activate'] as $online_clothing_store_slug => $online_clothing_store_plugin ) {
			if ( $_POST['slug'] == $online_clothing_store_slug ) {
				$online_clothing_store_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $online_clothing_store_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating…','online-clothing-store' ),
				);
				break;
			}
		}
		foreach ( $online_clothing_store_plugins['update'] as $online_clothing_store_slug => $online_clothing_store_plugin ) {
			if ( $_POST['slug'] == $online_clothing_store_slug ) {
				$online_clothing_store_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $online_clothing_store_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating…','online-clothing-store' ),
				);
				break;
			}
		}
		foreach ( $online_clothing_store_plugins['install'] as $online_clothing_store_slug => $online_clothing_store_plugin ) {
			if ( $_POST['slug'] == $online_clothing_store_slug ) {
				$online_clothing_store_json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $online_clothing_store_slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing…','online-clothing-store' ),
				);
				break;
			}
		}
		if ( $online_clothing_store_json ) {
			$online_clothing_store_json['hash'] = md5( serialize( $online_clothing_store_json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $online_clothing_store_json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Active','online-clothing-store' ) ) );
		}
		exit;
	}
}
