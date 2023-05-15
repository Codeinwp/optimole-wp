<?php
/**
 * Admin class.
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      19/07/2018
 *
 * @soundtrack Somewhere Else - Marillion
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Admin
 */
class Optml_Admin {
	use Optml_Normalizer;
	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	public $settings;

	/**
	 * Optml_Admin constructor.
	 */
	public function __construct() {
		$this->settings = new Optml_Settings();
		add_action( 'plugin_action_links_' . plugin_basename( OPTML_BASEFILE ), [ $this, 'add_action_links' ] );
		add_action( 'admin_menu', [ $this, 'add_dashboard_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], PHP_INT_MIN );
		add_action( 'admin_notices', [ $this, 'add_notice' ] );
		add_action( 'admin_notices', [ $this, 'add_notice_upgrade' ] );
		add_action( 'optml_daily_sync', [ $this, 'daily_sync' ] );

		if ( $this->settings->is_connected() ) {
			add_action( 'init', [$this, 'check_domain_change'] );
		}
		add_action( 'admin_init', [ $this, 'maybe_redirect' ] );
		add_action( 'admin_init', [ $this, 'init_no_script' ] );
		if ( ! is_admin() && $this->settings->is_connected() && ! wp_next_scheduled( 'optml_daily_sync' ) ) {
			wp_schedule_event( time() + 10, 'daily', 'optml_daily_sync', [] );
		}
		add_action( 'optml_after_setup', [ $this, 'register_public_actions' ], 999999 );

		if ( ! function_exists( 'is_wpcom_vip' ) ) {
			add_filter( 'upload_mimes', [ $this, 'allow_meme_types' ] ); // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.upload_mimes
		}
	}

	/**
	 * Init no_script setup value based on whether the user is connected or not.
	 */
	public function init_no_script() {
		if ( $this->settings->is_connected() ) {
			$raw_settings = $this->settings->get_raw_settings();
			if ( ! isset( $raw_settings['no_script'] ) ) {
				$this->settings->update( 'no_script', 'enabled' );
			}
		}
	}
	/**
	 * Checks if domain has changed
	 */
	public function check_domain_change() {
		$previous_domain = get_option( 'optml_current_domain', 0 );
		$site_url = $this->to_domain_hash( get_home_url() );

		if ( $site_url !== $previous_domain ) {
			update_option( 'optml_current_domain', $site_url );
			if ( $previous_domain !== 0 ) {
				$this->daily_sync();
			}
		}
	}
	/**
	 * Adds Optimole tag to admin bar
	 */
	public function add_report_menu() {
		global $wp_admin_bar;

		$wp_admin_bar->add_node(
			[
				'id'    => 'optml_report_script',
				'href'  => '#',
				'title' => '<span class="ab-icon"></span>Optimole ' . __( 'debugger', 'optimole-wp' ),
			]
		);
		$wp_admin_bar->add_menu(
			[
				'id'     => 'optml_status',
				'title'  => __( 'Troubleshoot this page', 'optimole-wp' ),
				'parent' => 'optml_report_script',
			]
		);
	}

	/**
	 * Adds Optimole css to admin bar
	 */
	public function print_report_css() {
		?>
		<style type="text/css">
			li#wp-admin-bar-optml_report_script > div :hover {
				cursor: pointer;
				color: #00b9eb !important;
				text-decoration: underline;
			}

			#wpadminbar #wp-admin-bar-optml_report_script .ab-icon:before {
				content: "\f227";
				top: 3px;
			}

			/* The Modal (background) */
			.optml-modal {
				display: none; /* Hidden by default */
				position: fixed; /* Stay in place */
				z-index: 2147483641; /* Sit on top */
				padding-top: 100px; /* Location of the box */
				left: 0;
				top: 0;
				width: 100%; /* Full width */
				height: 100%; /* Full height */
				overflow: auto; /* Enable scroll if needed */
				background-color: rgb(0, 0, 0); /* Fallback color */
				background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
			}

			/* Modal Content */
			.optml-modal-content {
				background-color: #fefefe;
				margin: auto;
				padding: 20px;
				border: 1px solid #888;
				width: 80%;
			}

			/* The Close Button */
			.optml-close {
				color: #aaaaaa;
				float: right;
				font-size: 28px;
				font-weight: bold;
			}

			.optml-modal-content ul {
				list-style: none;
				font-size: 80%;
				margin-top: 50px;
			}

			.optml-close:hover,
			.optml-close:focus {
				color: #000;
				text-decoration: none;
				cursor: pointer;
			}
		</style>
		<?php
	}

	/**
	 * Register public actions.
	 */
	public function register_public_actions() {
		add_action( 'wp_head', [ $this, 'generator' ] );
		add_filter( 'wp_resource_hints', [ $this, 'add_dns_prefetch' ], 10, 2 );

		if ( Optml_Manager::should_ignore_image_tags() ) {
			return;
		}
		if ( ! is_admin() && $this->settings->get( 'report_script' ) === 'enabled' && current_user_can( 'manage_options' ) ) {

			add_action( 'wp_head', [ $this, 'print_report_css' ] );
			add_action( 'wp_before_admin_bar_render', [ $this, 'add_report_menu' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'add_diagnosis_script' ] );
		}
		if ( ! $this->settings->use_lazyload() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
		add_action( 'wp_head', [ $this, 'inline_bootstrap_script' ] );

		add_filter( 'optml_additional_html_classes', [ $this, 'add_no_js_class_to_html_tag' ], 10, 1 );
	}

	/**
	 * Use filter to add additional class to html tag.
	 *
	 * @param array $classes The classes to be added.
	 *
	 * @return array
	 */
	public function add_no_js_class_to_html_tag( $classes ) {
		// we need the space padding since some plugins might not target correctly with js there own classes
		// this causes some issues if they concat directly, this way we can protect against that since no matter what
		// there will be an extra space padding so that classes can be easily identified
		return array_merge( $classes, [ ' optml_no_js ' ] );
	}

	/**
	 * Adds script for lazyload/js replacement.
	 */
	public function inline_bootstrap_script() {
		$domain = 'https://' . $this->settings->get_cdn_url() . '/js-lib';

		if ( defined( 'OPTML_JS_CDN' ) && constant( 'OPTML_JS_CDN' ) ) {
			$domain = 'https://' . constant( 'OPTML_JS_CDN' ) . '/js-lib';
		}

		$min                   = ! OPTML_DEBUG ? '.min' : '';
		$bgclasses             = Optml_Lazyload_Replacer::get_lazyload_bg_classes();
		$watcher_classes       = Optml_Lazyload_Replacer::get_watcher_lz_classes();
		$lazyload_bg_selectors = Optml_Lazyload_Replacer::get_background_lazyload_selectors();
		foreach ( $bgclasses as $key ) {
			$lazyload_bg_selectors[] = '.' . $key;
		}
		$lazyload_bg_selectors = empty( $lazyload_bg_selectors ) ? '' : sprintf( '%s', implode( ', ', (array) $lazyload_bg_selectors ) );
		$bgclasses             = empty( $bgclasses ) ? '' : sprintf( '"%s"', implode( '","', (array) $bgclasses ) );
		$watcher_classes       = empty( $watcher_classes ) ? '' : sprintf( '"%s"', implode( '","', (array) $watcher_classes ) );
		$default_network       = ( $this->settings->get( 'network_optimization' ) === 'enabled' );
		$retina_ready          = ! ( $this->settings->get( 'retina_images' ) === 'enabled' );
		$scale_is_disabled     = ( $this->settings->get( 'scale' ) === 'enabled' );
		$native_lazy_enabled   = ( $this->settings->get( 'native_lazyload' ) === 'enabled' );
		$output                = sprintf(
			'
		<style type="text/css">
			img[data-opt-src]:not([data-opt-lazy-loaded]) {
				transition: .2s filter linear, .2s opacity linear, .2s border-radius linear;
				-webkit-transition: .2s filter linear, .2s opacity linear, .2s border-radius linear;
				-moz-transition: .2s filter linear, .2s opacity linear, .2s border-radius linear;
				-o-transition: .2s filter linear, .2s opacity linear, .2s border-radius linear;
			}
			img[data-opt-src]:not([data-opt-lazy-loaded]) {
					opacity: .75;
					-webkit-filter: blur(8px);
					-moz-filter: blur(8px);
					-o-filter: blur(8px);
					-ms-filter: blur(8px);
					filter: blur(8px);
					transform: scale(1.04);
					animation: 0.1s ease-in;
					-webkit-transform: translate3d(0, 0, 0);
			}
			%s
		</style>
		<script type="application/javascript">
					document.documentElement.className = document.documentElement.className.replace(/\boptml_no_js\b/g, "");
						(function(w, d){
							var b = d.getElementsByTagName("head")[0];
							var s = d.createElement("script");
							var v = ("IntersectionObserver" in w && "isIntersecting" in w.IntersectionObserverEntry.prototype) ? "_no_poly" : "";
							s.async = true;
							s.src = "%s/v2/latest/optimole_lib" + v  + "%s.js";
							b.appendChild(s);
							w.optimoleData = {
								lazyloadOnly: "optimole-lazy-only",
								backgroundReplaceClasses: [%s],
								nativeLazyload : %s,
								scalingDisabled: %s,
								watchClasses: [%s],
								backgroundLazySelectors: "%s",
								network_optimizations: %s,
								ignoreDpr: %s,
								quality: %d
							}
						}(window, document));
					document.addEventListener( "DOMContentLoaded", function() {
																		
																		if ( "loading" in HTMLImageElement.prototype && Object.prototype.hasOwnProperty.call( optimoleData, "nativeLazyload" ) && optimoleData.nativeLazyload === true ) {
																			const images = document.querySelectorAll(\'img[loading="lazy"]\');
																					images.forEach( function (img) {
																						if ( !img.dataset.optSrc) {
																							return;
																						}
																						img.src = img.dataset.optSrc;
																						delete img.dataset.optSrc;
																					 });
																		}
																	} );
		</script>',
			Optml_Lazyload_Replacer::IFRAME_TEMP_COMMENT,
			esc_url( $domain ),
			$min,
			wp_strip_all_tags( $bgclasses ),
			$native_lazy_enabled ? 'true' : 'false',
			$scale_is_disabled ? 'true' : 'false',
			wp_strip_all_tags( $watcher_classes ),
			addcslashes( wp_strip_all_tags( $lazyload_bg_selectors ), '"' ),
			defined( 'OPTML_NETWORK_ON' ) && constant( 'OPTML_NETWORK_ON' ) ? ( OPTML_NETWORK_ON ? 'true' : 'false' ) : ( $default_network ? 'true' : 'false' ),
			$retina_ready ? 'true' : 'false',
			$this->settings->get_numeric_quality()
		);
		echo $output;
	}

	/**
	 * Adds script for lazyload/js replacement.
	 */
	public function add_diagnosis_script() {

		wp_enqueue_script( 'optml-report', OPTML_URL . 'assets/js/report_script.js' );
		$ignored_domains = [ 'gravatar.com', 'instagram.com', 'fbcdn' ];
		$report_script  = [
			'optmlCdn'       => $this->settings->get_cdn_url(),
			'restUrl'        => untrailingslashit( rest_url( OPTML_NAMESPACE . '/v1' ) ) . '/check_redirects',
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'ignoredDomains' => $ignored_domains,
			'wait'           => __( 'We are checking the current page for any issues with optimized images ...', 'optimole-wp' ),
			'description'    => __( 'Optimole page analyzer', 'optimole-wp' ),
		];
		wp_localize_script( 'optml-report', 'reportScript', $report_script );
	}

	/**
	 * Add settings links in the plugin listing page.
	 *
	 * @param array $links Old plugin links.
	 *
	 * @return array Altered links.
	 */
	function add_action_links( $links ) {
		if ( ! is_array( $links ) ) {
			return $links;
		}

		return array_merge(
			$links,
			[
				'<a href="' . admin_url( 'upload.php?page=optimole' ) . '">' . __( 'Settings', 'optimole-wp' ) . '</a>',
			]
		);
	}

	/**
	 * Check if we should show the notice.
	 *
	 * @return bool Should show?
	 */
	public function should_show_notice() {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return false;
		}

		if ( is_network_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( $this->settings->is_connected() ) {
			return false;
		}

		$current_screen = get_current_screen();

		if ( empty( $current_screen ) ) {
			return false;
		}

		if ( ( get_option( 'optml_notice_optin', 'no' ) === 'yes' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Show upgrade notice.
	 */
	public function add_notice_upgrade() {
		if ( ! $this->should_show_upgrade() ) {
			return;
		}
		?>
		<div class="notice optml-notice-optin" style="background-color: #577BF9; color:white; border: none !important; display: flex;">
	  <div style="margin: 1% 2%;">
		<img src='<?php echo OPTML_URL . 'assets/img/upgrade_icon.png'; ?>'>
	  </div>
	  <div style="margin-top: 0.7%;">
			<p style="font-size: 16px !important;"> <?php printf( __( '%1$sIt seems your are close to the 5.0000 visits limit with %3$sOptiMole%4$s for this month.%2$s %5$s For a larger quota you may want to check the upgrade plans. If you exceed the quota we will need to deliver back your original, un-optimized images, which might decrease your site speed performance.', 'optimole-wp' ), '<strong>', '</strong>', '<strong>', '</strong>', '<br/><br/>', '<i>', '</i >', '<strong>', '</strong>' ); ?></p>
			<p style="margin: 1.5% 0;">
				<a href="https://optimole.com/pricing" target="_blank" style="border-radius: 4px;padding: 9px 10px;border: 2px solid #FFF;color: white;text-decoration: none;"><?php _e( 'Check upgrade plans', 'optimole-wp' ); ?>
				</a>
				<a style="padding: 2%; color: white;"
					 href="<?php echo wp_nonce_url( add_query_arg( [ 'optml_hide_upg' => 'yes' ] ), 'hide_nonce', 'optml_nonce' ); ?>"><?php _e( 'I have already done this', 'optimole-wp' ); ?></a>
			</p>
	  </div>
		</div>
		<?php
	}

	/**
	 * Check if we should show the upgrade notice to users.
	 *
	 * @return bool Should we show it?
	 */
	public function should_show_upgrade() {
		$current_screen = get_current_screen();
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
			 is_network_admin() ||
			 ! current_user_can( 'manage_options' ) ||
			 ! $this->settings->is_connected() ||
			 empty( $current_screen )
		) {
			return false;
		}
		if ( get_option( 'optml_notice_hide_upg', 'no' ) === 'yes' ) {
			return false;
		}
		if ( isset( $current_screen->base ) && $current_screen->base !== 'upload' ) {
			return false;
		}
		$service_data = $this->settings->get( 'service_data' );
		if ( ! isset( $service_data['plan'] ) ) {
			return false;
		}
		if ( $service_data['plan'] !== 'free' ) {
			return false;
		}
		$visitors_limit = isset( $service_data['visitors_limit'] ) ? (int) $service_data['visitors_limit'] : 0;
		$visitors_left  = isset( $service_data['visitors_left'] ) ? (int) $service_data['visitors_left'] : 0;
		if ( $visitors_limit === 0 ) {
			return false;
		}
		if ( $visitors_left > 2000 ) {
			return false;
		}

		return true;
	}

	/**
	 * Adds opt in notice.
	 */
	public function add_notice() {
		if ( ! $this->should_show_notice() ) {
			return;
		}
		?>
		<style>
			.optml-notice-optin {
				background: url(" <?php echo esc_attr( OPTML_URL . '/assets/img/disconnected.svg' ); ?> ") #fff 100% 0 no-repeat;
				position: relative;
				padding: 0;
			}

			.optml-notice-optin .content {
				background: rgba(255, 255, 255, 0.75);
				display: flex;
				align-items: center;
				padding: 20px;
			}

			.optml-notice-optin img {
				max-width: 100px;
				margin-right: 20px;
				display: none;
			}

			.optml-notice-optin .description {
				font-size: 14px;
				margin-bottom: 20px;
				color: #000;
			}

			.optml-notice-optin .actions {
				margin-top: auto;
				display: flex;
				gap: 20px;
			}

			@media screen and (min-width: 768px) {
				.optml-notice-optin img {
					display: block;
				}
			}
		</style>
		<div class="notice notice-info optml-notice-optin">
			<div class="content">
				<img src="<?php echo OPTML_URL . '/assets/img/logo.svg'; ?>" alt="<?php echo esc_attr__( 'Logo', 'optimole-wp' ); ?>"/>

				<div>
					<p class="notice-title"> <?php echo esc_html__( 'Finish setting up!', 'optimole-wp' ); ?></p>
					<p class="description"> <?php printf( __( 'Welcome to %1$sOptiMole%2$s, the easiest way to optimize your website images. Your users will enjoy a %3$sfaster%4$s website after you connect it with our service.', 'optimole-wp' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
					<div class="actions">
						<a href="<?php echo esc_url( admin_url( 'upload.php?page=optimole' ) ); ?>"
						   class="button button-primary button-hero"><?php _e( 'Connect to OptiMole', 'optimole-wp' ); ?>
						</a>
						<a class="button button-secondary button-hero"
						   href="<?php echo wp_nonce_url( add_query_arg( [ 'optml_hide_optin' => 'yes' ] ), 'hide_nonce', 'optml_nonce' ); ?>"><?php _e( 'I will do it later', 'optimole-wp' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add style classes for lazy loading background images.
	 */
	protected function get_background_lazy_css() {

		$watchers = Optml_Lazyload_Replacer::get_background_lazyload_selectors();

		$css = [];
		foreach ( $watchers as $selector ) {
			$css[] = 'html ' . $selector . ':not(.optml-bg-lazyloaded)';
		}
		if ( empty( $css ) ) {
			return '';
		}
		$css = implode( ",\n", $css ) . ' { background-image: none !important; } ';

		return strip_tags( $css );
	}

	/**
	 * Enqueue frontend scripts.
	 */
	public function frontend_scripts() {

		$bg_css = $this->get_background_lazy_css();

		wp_register_style( 'optm_lazyload_noscript_style', false );
		wp_enqueue_style( 'optm_lazyload_noscript_style' );
		wp_add_inline_style( 'optm_lazyload_noscript_style', "html.optml_no_js img[data-opt-src] { display: none !important; } \n " . $bg_css );

		if ( $this->settings->use_lazyload() === true ) {
			wp_register_script( 'optml-print', false );
			wp_enqueue_script( 'optml-print' );
			$script = '
			(function(w, d){
					w.addEventListener("beforeprint", function(){
						let images = d.getElementsByTagName( "img" );
							for (let img of images) {
								if ( !img.dataset.optSrc) {
									continue;
								}
								img.src = img.dataset.optSrc;
								delete img.dataset.optSrc;
							}
					});
			
			}(window, document));
								 ';
			wp_add_inline_script( 'optml-print', $script );
		}

	}

	/**
	 * Maybe redirect to dashboard page.
	 */
	public function maybe_redirect() {
		if ( isset( $_GET['optml_nonce'] ) && isset( $_GET['optml_hide_optin'] ) && $_GET['optml_hide_optin'] === 'yes' && wp_verify_nonce( $_GET['optml_nonce'], 'hide_nonce' ) ) {
			update_option( 'optml_notice_optin', 'yes' );
		}

		if ( isset( $_GET['optml_nonce'] ) && isset( $_GET['optml_hide_upg'] ) && $_GET['optml_hide_upg'] === 'yes' && wp_verify_nonce( $_GET['optml_nonce'], 'hide_nonce' ) ) {
			update_option( 'optml_notice_hide_upg', 'yes' );
		}

		if ( ! get_transient( 'optml_fresh_install' ) ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		delete_transient( 'optml_fresh_install' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		if ( $this->settings->is_connected() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'upload.php?page=optimole' ) );
		exit;
	}

	/**
	 * Output Generator tag.
	 */
	public function generator() {
		if ( ! $this->settings->is_connected() ) {
			return;
		}
		if ( ! $this->settings->is_enabled() ) {
			return;
		}
		echo '<meta name="generator" content="Optimole ' . esc_attr( OPTML_VERSION ) . '">';
	}

	/**
	 * Update daily the quota routine.
	 */
	function daily_sync() {

		$api_key = $this->settings->get( 'api_key' );
		$service_data = $this->settings->get( 'service_data' );
		$application = '';

		if ( isset( $service_data['cdn_key'] ) ) {
			$application = $service_data['cdn_key'];
		}

		if ( empty( $api_key ) ) {
			return;
		}

		$request = new Optml_Api();
		$data    = $request->get_user_data( $api_key, $application );
		if ( $data === false || is_wp_error( $data ) ) {
			return;
		}
		if ( $data === 'disconnect' ) {
			$settings = new Optml_Settings();
			$settings->reset();
			return;
		}

		add_filter( 'optml_dont_trigger_settings_updated', '__return_true' );
		$this->settings->update( 'service_data', $data );
		remove_filter( 'optml_dont_trigger_settings_updated', '__return_true' );

	}

	/**
	 * Adds cdn url for prefetch.
	 *
	 * @param array  $hints Hints array.
	 * @param string $relation_type Type of relation.
	 *
	 * @return array Altered hints array.
	 */
	public function add_dns_prefetch( $hints, $relation_type ) {
		if ( 'dns-prefetch' !== $relation_type &&
			 'preconnect' !== $relation_type
		) {
			return $hints;
		}
		if ( ! $this->settings->is_connected() ) {
			return $hints;
		}
		if ( ! $this->settings->is_enabled() ) {
			return $hints;
		}
		$hints[] = sprintf( 'https://%s', $this->settings->get_cdn_url() );

		return $hints;
	}

	/**
	 * Add the dashboard page.
	 */
	public function add_dashboard_page() {
		if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
			return;
		}
		add_media_page( 'Optimole', 'Optimole', 'manage_options', 'optimole', [ $this, 'render_dashboard_page' ] );
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		if ( get_option( 'optml_notice_optin', 'no' ) !== 'yes' ) {
			update_option( 'optml_notice_optin', 'yes' );
		}
		?>
		<div id="optimole-app">
			<app></app>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts needed for admin functionality.
	 *
	 * @codeCoverageIgnore
	 */
	public function enqueue() {

		$current_screen = get_current_screen();
		if ( ! isset( $current_screen->id ) ) {
			return;
		}
		if ( $current_screen->id !== 'media_page_optimole' ) {
			return;
		}
		wp_register_script( OPTML_NAMESPACE . '-admin', OPTML_URL . 'assets/js/bundle' . ( ! OPTML_DEBUG ? '.min' : '' ) . '.js', [], OPTML_VERSION );
		wp_localize_script( OPTML_NAMESPACE . '-admin', 'optimoleDashboardApp', $this->localize_dashboard_app() );
		wp_enqueue_script( OPTML_NAMESPACE . '-admin' );
	}

	/**
	 * Localize the dashboard app.
	 *
	 * @codeCoverageIgnore
	 * @return array
	 */
	private function localize_dashboard_app() {

		global $wp_version;
		$is_offload_media_available = 'no';
		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			$is_offload_media_available = 'yes';
		}
		$api_key      = $this->settings->get( 'api_key' );
		$service_data = $this->settings->get( 'service_data' );
		$user         = get_userdata( get_current_user_id() );
		$user_status = 'inactive';
		$auto_connect = get_option( Optml_Settings::OPTML_USER_EMAIL, 'no' );
		$available_apps = isset( $service_data['available_apps'] ) ? $service_data['available_apps'] : null;
		if ( isset( $service_data['cdn_key'] ) && $available_apps !== null ) {
			foreach ( $service_data['available_apps'] as $app ) {
				if ( isset( $app['key'] ) && $app['key'] === $service_data['cdn_key'] && isset( $app['status'] ) && $app['status'] === 'active' ) {
					$user_status = 'active';
				}
			}
		}
		$routes = [];
		foreach ( Optml_Rest::$rest_routes as $route_type ) {
			foreach ( $route_type as $route => $details ) {
				$routes[ $route ] = rest_url( OPTML_NAMESPACE . '/v1/' . $route );
			}
		}

		return [
			'strings'                    => $this->get_dashboard_strings(),
			'assets_url'                 => OPTML_URL . 'assets/',
			'connection_status'          => empty( $service_data ) ? 'no' : 'yes',
			'has_application'            => isset( $service_data['app_count'] ) && $service_data['app_count'] >= 1 ? 'yes' : 'no',
			'user_status'                => $user_status,
			'available_apps'             => $available_apps,
			'api_key'                    => $api_key,
			'routes'                     => $routes,
			'nonce'                      => wp_create_nonce( 'wp_rest' ),
			'user_data'                  => $service_data,
			'remove_latest_images'       => defined( 'OPTML_REMOVE_LATEST_IMAGES' ) && constant( 'OPTML_REMOVE_LATEST_IMAGES' ) ? ( OPTML_REMOVE_LATEST_IMAGES ? 'yes' : 'no' ) : 'no',
			'current_user'               => [
				'email' => $user->user_email,
			],
			'site_settings'              => $this->settings->get_site_settings(),
			'home_url'                   => home_url(),
			'is_offload_media_available' => $is_offload_media_available,
			'auto_connect'               => $auto_connect,
		];
	}

	/**
	 * Get all dashboard strings.
	 *
	 * @codeCoverageIgnore
	 * @return array
	 */
	private function get_dashboard_strings() {
		return [
			'optimole'                       => 'Optimole',
			'version'                        => OPTML_VERSION,
			'terms_menu'                     => __( 'Terms', 'optimole-wp' ),
			'privacy_menu'                   => __( 'Privacy', 'optimole-wp' ),
			'testdrive_menu'                 => __( 'Test Optimole', 'optimole-wp' ),
			'service_details'                => __( 'Image optimization service', 'optimole-wp' ),
			'connect_btn'                    => __( 'Connect to Optimole', 'optimole-wp' ),
			'disconnect_btn'                 => __( 'Disconnect', 'optimole-wp' ),
			'select'                         => __( 'Select', 'optimole-wp' ),
			'your_domain'                    => __( 'your domain', 'optimole-wp' ),
			'add_api'                        => __( 'Add your API Key', 'optimole-wp' ),
			'your_api_key'                   => __( 'Your API Key', 'optimole-wp' ),
			'looking_for_api_key'            => __( 'LOOKING FOR YOUR API KEY?', 'optimole-wp' ),
			'refresh_stats_cta'              => __( 'REFRESH STATS', 'optimole-wp' ),
			'updating_stats_cta'             => __( 'UPDATING STATS', 'optimole-wp' ),
			'api_key_placeholder'            => __( 'API Key', 'optimole-wp' ),
			'account_needed_heading'         => __( 'Sign-up for API key', 'optimole-wp' ),
			'invalid_key'                    => __( 'Invalid API Key', 'optimole-wp' ),
			'keep_connected'                 => __( 'Ok, keep me connected', 'optimole-wp' ),
			'disconnect_title'               => __( 'You are about to disconnect from the Optimole API', 'optimole-wp' ),
			'disconnect_desc'                => __(
				'Please note that disconnecting your site from the Optimole API will impact your website performance.
If you still want to disconnect click the button below.',
				'optimole-wp'
			),
			'email_address_label'            => __( 'Your email address', 'optimole-wp' ),
			'steps_connect_api_title'        => __( 'Connect your account', 'optimole-wp' ),
			'register_btn'                   => __( 'Create & connect your account ', 'optimole-wp' ),
			'step_one_api_title'             => __( 'Enter your API key.', 'optimole-wp' ),
			'optml_dashboard'                => sprintf( __( 'Get it from the %1$s Optimole Dashboard%2$s.', 'optimole-wp' ), '<a  style="white-space:nowrap; text-decoration: underline !important;" href="https://dashboard.optimole.com/" target="_blank"> ', '<span style="text-decoration:none; font-size:15px; margin-top:2px;" class="dashicons dashicons-external"></span></a>' ),
			'steps_connect_api_desc'         => sprintf( __( 'Copy the API Key you have received via email or you can get it from %1$s Optimole dashboard%2$s. If your account has multiple domains select the one you want to use. <br/>', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/" target="_blank"> ', '</a>' ),
			'api_exists'                     => __( 'I already have an API key.', 'optimole-wp' ),
			'back_to_register'               => __( 'Register account', 'optimole-wp' ),
			'back_to_connect'                => __( 'Go to previous step', 'optimole-wp' ),
			'error_register'                 => sprintf( __( 'Error registering account. You can try again %1$shere%2$s ', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/register" target="_blank"> ', '</a>' ),
			'connected'                      => __( 'CONNECTED', 'optimole-wp' ),
			'not_connected'                  => __( 'NOT CONNECTED', 'optimole-wp' ),
			'usage'                          => __( 'Monthly Usage', 'optimole-wp' ),
			'quota'                          => __( 'Monthly visits quota', 'optimole-wp' ),
			'logged_in_as'                   => __( 'LOGGED IN AS', 'optimole-wp' ),
			'private_cdn_url'                => __( 'IMAGES DOMAIN', 'optimole-wp' ),
			'existing_user'                  => __( 'Existing user', 'optimole-wp' ),
			'notification_message_register'  => __( 'We sent you the API Key in the email. Add it below to connect to Optimole.', 'optimole-wp' ),
			'account_needed_title'           => sprintf(
				__( 'In order to get access to free image optimization service you will need an API key from %s.', 'optimole-wp' ),
				' <a href="https://dashboard.optimole.com/register" target="_blank">optimole.com</a>'
			),
			'account_needed_subtitle_1'      => sprintf(
				__( 'You will get access to our %1$simage optimization service for FREE%2$s in the limit of %3$s5k%4$s %5$svisitors%6$s per month. ', 'optimole-wp' ),
				'<strong>',
				'</strong>',
				'<strong>',
				'</strong>',
				'<a href="https://docs.optimole.com/article/1134-how-optimole-counts-the-number-of-visitors" target="_blank">',
				'</a>'
			),
			'account_needed_subtitle_3'      => sprintf(
				__( 'Here’s %1$show%2$s to install Optimole on your WordPress site in 3 steps.', 'optimole-wp' ),
				'<a target="_blank" href="https://docs.optimole.com/article/1173-how-to-get-started-with-optimole-in-just-3-steps">',
				'</a>'
			),
			'account_needed_subtitle_2'      => sprintf(
				__(
					'Bonus, if you dont use a CDN, we got you covered, %1$swe will serve the images using CloudFront CDN%2$s from 450+ locations.',
					'optimole-wp'
				),
				'<strong>',
				'</strong>'
			),
			'notice_just_activated'          => ! $this->settings->is_connected() ?
				sprintf( __( '%1$sImage optimisation is currently running.%2$s <br/> Your visitors will now view the best image for their device automatically, all served from the Optimole Cloud Service on the fly. You might see for the very first image request being redirected to the original URL while we do the optimization in the background. You can relax, we\'ll take it from here.', 'optimole-wp' ), '<strong>', '</strong>' )
				: '',
			'notice_api_not_working'         => __(
				'It seems there is an issue with your WordPress configuration and the core REST API functionality is not available. This is crucial as Optimole relies on this functionality in order to work.<br/>
The root cause might be either a security plugin which blocks this feature or some faulty server configuration which constrain this WordPress feature.You can try to disable any of the security plugins that you use in order to see if the issue persists or ask the hosting company to further investigate.',
				'optimole-wp'
			),
			'notice_disabled_account'        => sprintf( __( '%3$sYour account has been disabled due to exceeding quota.%4$s All images are being redirected to the original unoptimized URL. %5$sPlease %1$supgrade%2$s to re-activate the account.', 'optimole-wp' ), '<b><a href="https://optimole.com/pricing">', '</a></b>', '<b>', '</b>', '<br>' ),
			'dashboard_menu_item'            => __( 'Dashboard', 'optimole-wp' ),
			'settings_menu_item'             => __( 'Settings', 'optimole-wp' ),
			'settings_exclusions_menu_item'  => __( 'Exclusions', 'optimole-wp' ),
			'settings_resize_menu_item'      => __( 'Resize', 'optimole-wp' ),
			'settings_compression_menu_item' => __( 'Compression', 'optimole-wp' ),
			'advanced_settings_menu_item'    => __( 'Advanced', 'optimole-wp' ),
			'general_settings_menu_item'     => __( 'General', 'optimole-wp' ),
			'for_latest_optimized_images'    => __( 'For the latest 10 images', 'optimole-wp' ),
			'lazyload_settings_menu_item'    => __( 'Lazyload', 'optimole-wp' ),
			'offload_media_settings_menu_item'    => __( 'Cloud Integration', 'optimole-wp' ),
			'watermarks_menu_item'           => __( 'Watermark', 'optimole-wp' ),
			'conflicts_menu_item'            => __( 'Possible issues', 'optimole-wp' ),
			'conflicts'                      => [
				'title'              => __( 'We might have some possible conflicts with the plugins that you use. In order to benefit from Optimole\'s full potential you will need to address this issues.', 'optimole-wp' ),
				'message'            => __( 'Details', 'optimole-wp' ),
				'conflict_close'     => __( 'I\'ve done this.', 'optimole-wp' ),
				'no_conflicts_found' => __( 'No conflicts found. We are all peachy now. 🍑', 'optimole-wp' ),
			],
			'upgrade'                        => [
				'title'    => __( 'Upgrade', 'optimole-wp' ),
				'title_long'  => __( 'Upgrade to Optimole Pro', 'optimole-wp' ),
				'reason_1' => __( 'Priority & Live Chat support', 'optimole-wp' ),
				'reason_2' => __( 'Extend visits limit', 'optimole-wp' ),
				'reason_3' => __( 'Custom domain', 'optimole-wp' ),
				'reason_4' => __( 'Site audit', 'optimole-wp' ),
				'cta'      => __( 'View plans', 'optimole-wp' ),
			],
			'neve'                        => [
				'is_active' => defined( 'NEVE_VERSION' ) ? 'yes' : 'no',
				'byline'    => __( 'Fast, perfomance built-in WordPress theme.', 'optimole-wp' ),
				'reason_1'  => __( 'Lightweight, 25kB in page-weight.', 'optimole-wp' ),
				'reason_2'  => __( '100+ Starter Sites available.', 'optimole-wp' ),
				'reason_3'  => __( 'AMP/Mobile ready.', 'optimole-wp' ),
				'reason_4'  => __( 'Lots of customizations options.', 'optimole-wp' ),
				'reason_5'  => __( 'Fully compatible with Optimole.', 'optimole-wp' ),
			],
			'options_strings'                => [
				'add_filter'                        => __( 'Add filter', 'optimole-wp' ),
				'add_site'                          => __( 'Add site', 'optimole-wp' ),
				'admin_bar_desc'                    => __( 'Show in the WordPress admin bar the available quota from Optimole service.', 'optimole-wp' ),
				'auto_q_title'                      => __( 'Auto', 'optimole-wp' ),
				'cache_desc'                        => __( 'Clear all cached resources(images,js,css) by Optimole from this site. Useful if you updated them and Optimole shows the old version.', 'optimole-wp' ),
				'cache_title'                       => __( 'Clear cached resources', 'optimole-wp' ),
				'clear_cache_notice'                => __( 'Clearing cached resources will re-optimize the images and might affect for a few minutes the site performance.', 'optimole-wp' ),
				'image_size_notice'                  => __( 'If you have images that are no longer cropped after optimization you should add those images sizes here.', 'optimole-wp' ),
				'clear_cache_images'                => __( 'Clear cached images', 'optimole-wp' ),
				'clear_cache_assets'                => __( 'Clear cached CSS & JS', 'optimole-wp' ),
				'connect_step_0'                    => __( 'Connecting your site to the Optimole service.', 'optimole-wp' ),
				'connect_step_1'                    => __( 'Checking for possible conflicts.', 'optimole-wp' ),
				'connect_step_2'                    => __( 'Inspecting the images from your site.', 'optimole-wp' ),
				'connect_step_3'                    => __( 'All done, Optimole is currently optimizing your site.', 'optimole-wp' ),
				'disabled'                          => __( 'Disabled', 'optimole-wp' ),
				'enable_avif_title'                 => __( 'Enable avif conversion', 'optimole-wp' ),
				'enable_avif_desc'                  => __( 'Automatically convert images to avif format, if the browser supports avif', 'optimole-wp' ),
				'enable_bg_lazyload_desc'           => __( 'Lazyload images used as CSS backgrounds.', 'optimole-wp' ),
				'enable_bg_lazyload_title'          => __( 'Enable lazyload for background images', 'optimole-wp' ),
				'enable_video_lazyload_desc'        => __( 'Lazyload iframes/videos', 'optimole-wp' ),
				'enable_video_lazyload_title'       => __( 'Enable lazyload for embedded videos and iframes.', 'optimole-wp' ),
				'enable_noscript_desc'              => __( 'The noscript tag offers fallback images for browsers that can\'t handle JavaScript-based lazy loading or related features. Disabling it may resolve conflicts with other plugins or configurations and decrease HTML page size.', 'optimole-wp' ),
				'enable_noscript_title'             => __( 'Enable noscript tag', 'optimole-wp' ),
				'enable_gif_replace_title'          => __( 'Enable Gif to Video conversion', 'optimole-wp' ),
				'enable_report_title'               => __( 'Enable error diagnosis tool', 'optimole-wp' ),
				'enable_report_desc'                => __( 'Provides a troubleshooting mechanism which should help you identify any possible issues with your site using Optimole.', 'optimole-wp' ),
				'enable_offload_media_title'        => __( 'Enable offloading images', 'optimole-wp' ),
				'enable_offload_media_desc'         => __( 'Offload your new images automatically to Optimole Cloud. You will no longer store them on your server and you can restore them back anytime.', 'optimole-wp' ),
				'enable_cloud_images_title'         => __( 'Enable cloud library browsing', 'optimole-wp' ),
				'enable_cloud_images_desc'          => sprintf( __( 'Allow access from this site to all images from your Optimole account. %1$s More details here%2$s', 'optimole-wp' ), '<a style="white-space: nowrap;" target=”_blank” href="https://docs.optimole.com/article/1323-cloud-library-browsing">', '<span style="font-size:15px; margin-top:2px;" class="dashicons dashicons-external"></span></a>' ),
				'enable_image_replace'              => __( 'Enable image replacement', 'optimole-wp' ),
				'enable_lazyload_placeholder_desc'  => __( 'Enabling this might affect the user experience in some cases, however it will reduce the number of total requests and page weight. Try it out and see how works best for you!', 'optimole-wp' ),
				'enable_lazyload_placeholder_title' => __( 'Enable generic lazyload placeholder', 'optimole-wp' ),
				'enable_network_opt_desc'           => __( 'Optimole provides an option to automatically downgrade the image quality when it detects a slower network.', 'optimole-wp' ),
				'enable_network_opt_title'          => __( 'Enable network based optimizations', 'optimole-wp' ),
				'enable_resize_smart_desc'          => __( 'Detects the most interesting section of the image and considers it as the center of the resulting image.', 'optimole-wp' ),
				'enable_resize_smart_title'         => __( 'Enable Smart Cropping', 'optimole-wp' ),
				'enable_retina_desc'                => __( 'Deliver retina ready images to your visitors', 'optimole-wp' ),
				'enable_retina_title'               => __( 'Enable Retina images', 'optimole-wp' ),
				'image_sizes_title'                 => __( 'Your cropped image sizes', 'optimole-wp' ),
				'enabled'                           => __( 'Enabled', 'optimole-wp' ),
				'exclude_class_desc'                => sprintf( __( '%1$sImage tag%2$s contains class', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_ext_desc'                  => sprintf( __( '%1$sImage extension%2$s is', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_filename_desc'             => sprintf( __( '%1$sImage filename%2$s contains', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_title_lazyload'            => __( 'Don\'t lazyload images if', 'optimole-wp' ),
				'exclude_title_optimize'            => __( 'Don\'t optimize images if', 'optimole-wp' ),
				'exclude_url_desc'                  => sprintf( __( '%1$sPage url%2$s contains', 'optimole-wp' ), '<strong>', '</strong>' ),
				'name'                              => sprintf( __( '%1$sName: %2$s', 'optimole-wp' ), '<strong>', '</strong>' ),
				'cropped'                           => __( 'cropped', 'optimole-wp' ),
				'exclude_url_match_desc'            => sprintf( __( '%1$sPage url%2$s matches', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_first'                     => __( 'Exclude first', 'optimole-wp' ),
				'images'                            => __( 'images', 'optimole-wp' ),
				'exclude_first_images_title'        => __( 'Exclude the first X images from lazyload', 'optimole-wp' ),
				'exclude_first_images_desc'         => __( 'Exclude the first <number> images from lazyload on every page to avoid lazy load on above the fold images. Use 0 to disable this.', 'optimole-wp' ),
				'filter_class'                      => __( 'Image class', 'optimole-wp' ),
				'filter_ext'                        => __( 'Image extension', 'optimole-wp' ),
				'filter_filename'                   => __( 'Image filename', 'optimole-wp' ),
				'filter_operator_contains'          => __( 'contains', 'optimole-wp' ),
				'filter_operator_matches'           => __( 'matches', 'optimole-wp' ),
				'filter_operator_is'                => __( 'is', 'optimole-wp' ),
				'filter_url'                        => __( 'Page URL', 'optimole-wp' ),
				'gif_replacer_desc'                 => __( 'Automatically convert GIF images to Video files(MP4 and WebM)', 'optimole-wp' ),
				'height_field'                      => __( 'Height', 'optimole-wp' ),
				'add_image_size_button'             => __( 'Add size', 'optimole-wp' ),
				'add_image_size_desc'               => __( 'Add a new crop image size', 'optimole-wp' ),
				'here'                              => __( ' here.', 'optimole-wp' ),
				'hide'                              => __( 'Hide', 'optimole-wp' ),
				'high_q_title'                      => __( 'High', 'optimole-wp' ),
				'image_1_label'                     => __( 'Original', 'optimole-wp' ),
				'image_2_label'                     => __( 'Optimized', 'optimole-wp' ),
				'lazyload_desc'                     => __( 'We will generate images size based on your visitor\'s screen using javascript and render them without blocking the page execution via lazyload .', 'optimole-wp' ),
				'scale_desc'                        => __( 'When this option is off, we disable the automatic scaling of images on lazyload.', 'optimole-wp' ),
				'low_q_title'                       => __( 'Low', 'optimole-wp' ),
				'medium_q_title'                    => __( 'Medium', 'optimole-wp' ),
				'no_images_found'                   => __( 'You dont have any images in your Media Library. Add one and check how the Optimole will perform.', 'optimole-wp' ),
				'native_desc'                       => __( 'Use browser native lazyload if supported, fallback to classic lazyload otherwise. When using browser native lazyload the auto scale feature is disabled', 'optimole-wp' ),
				'option_saved'                      => __( 'Option saved.', 'optimole-wp' ),
				'ml_quality_desc' => 'Optimole ML algorithms will predict the right quality for your image in order to get the smallest possible size with minimum perceived quality losses. Turning this off will allow you to control manually the quality.',
				'quality_desc'                      => __( 'Lower image quality might not always be perceived by users and would result in a boost of your loading speed by lowering the page size. Try experimenting with the setting, then click the View sample image link to see what option works best for you.', 'optimole-wp' ),
				'quality_selected_value'            => __( 'Selected value', 'optimole-wp' ),
				'quality_slider_desc'               => __( 'See one sample image which will help you choose the right quality of the compression.', 'optimole-wp' ),
				'quality_title'                     => __( 'Enable Auto Quality powered by ML(Machine Learning)', 'optimole-wp' ),
				'strip_meta_title'                  => __( 'Strip Image Metadata', 'optimole-wp' ),
				'strip_meta_desc'                   => __( 'When enabled, Optimole will strip the metadata (EXIF, IPTC, etc.) from output images.', 'optimole-wp' ),
				'replacer_desc'                     => __( 'Replace all the image urls from your website with the ones optimized by Optimole.', 'optimole-wp' ),
				'sample_image_loading'              => __( 'Loading a sample image. ', 'optimole-wp' ),
				'save_changes'                      => __( 'Save changes', 'optimole-wp' ),
				'show'                              => __( 'Show', 'optimole-wp' ),
				'selected_sites_title'              => __( 'CURRENTLY SHOWING IMAGES FROM', 'optimole-wp' ),
				'selected_sites_desc'               => __( 'Site: ', 'optimole-wp' ),
				'selected_all_sites_desc'           => __( 'Currently viewing images from all sites ', 'optimole-wp' ),
				'select_all_sites_desc'             => __( 'View images from all sites ', 'optimole-wp' ),
				'size_desc'                         => __( 'We resize all images with sizes greater than the values defined here. Changing this option is not recommended unless large images are not being processed correctly. This does NOT affect the scaling of images on the frontend.', 'optimole-wp' ),
				'size_title'                        => __( 'Resize large images original source.', 'optimole-wp' ),
				'select_site'                       => __( 'Select a website', 'optimole-wp' ),
				'cloud_site_title'                  => __( 'Show images only from these sites: ', 'optimole-wp' ),
				'cloud_site_desc'                   => __( 'Only the images from the selected sites will be displayed on this site.', 'optimole-wp' ),
				'toggle_ab_item'                    => __( 'Admin bar status', 'optimole-wp' ),
				'toggle_lazyload'                   => __( 'Scale images & Lazy load', 'optimole-wp' ),
				'toggle_scale'                      => __( 'Scale Images', 'optimole-wp' ),
				'toggle_native'                     => __( 'Use native lazyload', 'optimole-wp' ),
				'on_toggle'                         => __( 'On', 'optimole-wp' ),
				'off_toggle'                        => __( 'Off', 'optimole-wp' ),
				'view_sample_image'                 => __( 'View sample image', 'optimole-wp' ),
				'watch_desc_lazyload'               => __( 'You can add each CSS selector on a new line or separated by comma(,).', 'optimole-wp' ),
				'watch_title_lazyload'              => __( 'Lazyload background images for selectors:', 'optimole-wp' ),
				'width_field'                       => __( 'Width', 'optimole-wp' ),
				'crop'                              => __( 'crop', 'optimole-wp' ),
				'toggle_cdn'                        => __( 'Serve CSS & JS through Optimole', 'optimole-wp' ),
				'cdn_desc'                          => __( 'Useful when you have images into CSS/JS files. Optimole will optimize the images from them and serve the CSS/JS through the CDN.', 'optimole-wp' ),
				'enable_css_minify_title'           => __( 'Minify CSS files', 'optimole-wp' ),
				'css_minify_desc'                   => __( 'Once Optimole will serve your CSS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
				'enable_js_minify_title'            => __( 'Minify JS files', 'optimole-wp' ),
				'js_minify_desc'                    => __( 'Once Optimole will serve your JS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
				'sync_title'                        => __( 'Offload existing images', 'optimole-wp' ),
				'rollback_title'                    => __( 'Restore offloaded images', 'optimole-wp' ),
				'sync_desc'                         => __( 'Right now all the new images uploaded to your site are moved automatically to Optimole Cloud. In order to offload the existing ones, please click Sync images and wait for the process to finish. You can rollback anytime.', 'optimole-wp' ),
				'rollback_desc'                     => __( 'Pull all the offloaded images to Optimole Cloud back to your server.', 'optimole-wp' ),
				'sync_media'                        => __( 'Sync images', 'optimole-wp' ),
				'rollback_media'                    => __( 'Rollback images', 'optimole-wp' ),
				'sync_media_progress'               => __( 'Moving your images to Optimole...', 'optimole-wp' ),
				'estimated_time'                    => __( 'Estimated time remaining', 'optimole-wp' ),
				'calculating_estimated_time'        => __( 'We are currently calculating the estimated time for this job...', 'optimole-wp' ),
				'active_exclusions'                 => __( 'ACTIVE EXCLUSIONS', 'optimole-wp' ),
				'minutes'                           => __( 'minutes', 'optimole-wp' ),
				'rollback_media_progress'           => __( 'Moving images into your media library...', 'optimole-wp' ),
				'rollback_media_error'              => __( 'An unexpected error occured while pulling the offloaded back to your site', 'optimole-wp' ),
				'rollback_media_error_desc'         => __( 'You can try again to pull back the rest of the images.', 'optimole-wp' ),
				'remove_notice'                     => __( 'Remove notice', 'optimole-wp' ),
				'sync_media_error'                  => __( 'An unexpected error occured while offloading all existing images from your site to Optimole ', 'optimole-wp' ),
				'sync_media_link'                   => __( 'The selected images have been offloaded to our servers, you can check them', 'optimole-wp' ),
				'rollback_media_link'               => __( 'The selected images have been restored to your server, you can check them', 'optimole-wp' ),
				'sync_media_error_desc'             => __( 'You can try again to offload the rest of the images to Optimole.', 'optimole-wp' ),
				'offload_disable_warning_title'     => __( 'Important! Please read carefully', 'optimole-wp' ),
				'offload_disable_warning_desc'      => __( 'If you disable this option you will not be able to see the images in the media library without restoring the images first, do you want to restore the images to your site upon disabling the option ?', 'optimole-wp' ),
				'offload_enable_info_desc'          => sprintf( __( 'You are not required to use the offload functionality for the plugin to work, use it if you want to save on hosting space. %1$s More details%2$s', 'optimole-wp' ), '<a style="white-space: nowrap;" target=”_blank” href="https://docs.optimole.com/article/1323-cloud-library-browsing">', '<span style="font-size:15px; margin-top:2px;" class="dashicons dashicons-external"></span></a>' ),
				'offload_conflicts_part_1'          => __( 'We have detected the following plugins that conflict with the offload features: ', 'optimole-wp' ),
				'offload_conflicts_part_2'          => __( 'Please disable those plugins temporarily in order for Optimole to rollback the images to your site.', 'optimole-wp' ),
				'select'                            => __( 'Please select one ...', 'optimole-wp' ),
				'yes'                               => __( 'Restore images after disabling', 'optimole-wp' ),
				'no'                                => __( 'Do not restore images after disabling', 'optimole-wp' ),

			],
			'watermarks'                     => [
				'image'                    => __( 'Image', 'optimole-wp' ),
				'loading_remove_watermark' => __( 'Removing watermark resource ...', 'optimole-wp' ),
				'max_allowed'              => __( 'You are allowed to save maximum 5 images.', 'optimole-wp' ),
				'list_header'              => __( 'Possible watermarks', 'optimole-wp' ),
				'settings_header'          => __( 'Watermarks position settings', 'optimole-wp' ),
				'no_images_found'          => __( 'No images available for watermark. Please upload one.', 'optimole-wp' ),
				'id'                       => __( 'ID', 'optimole-wp' ),
				'name'                     => __( 'Name', 'optimole-wp' ),
				'type'                     => __( 'Type', 'optimole-wp' ),
				'action'                   => __( 'Action', 'optimole-wp' ),
				'upload'                   => __( 'Upload', 'optimole-wp' ),
				'add_desc'                 => __( 'Add new watermark', 'optimole-wp' ),
				'wm_title'                 => __( 'Active watermark', 'optimole-wp' ),
				'wm_desc'                  => __( 'The active watermark to use from the list of uploaded watermarks.', 'optimole-wp' ),
				'opacity_field'            => __( 'Opacity', 'optimole-wp' ),
				'opacity_title'            => __( 'Watermark opacity', 'optimole-wp' ),
				'opacity_desc'             => __( 'A value between 0 and 100 for the opacity level. If set to 0 it will disable the watermark.', 'optimole-wp' ),
				'position_title'           => __( 'Watermark position', 'optimole-wp' ),
				'position_desc'            => __( 'The place relative to the image where the watermark should be placed.', 'optimole-wp' ),
				'pos_nowe_title'           => __( 'North-West', 'optimole-wp' ),
				'pos_no_title'             => __( 'North', 'optimole-wp' ),
				'pos_noea_title'           => __( 'North-East', 'optimole-wp' ),
				'pos_we_title'             => __( 'West', 'optimole-wp' ),
				'pos_ce_title'             => __( 'Center', 'optimole-wp' ),
				'pos_ea_title'             => __( 'East', 'optimole-wp' ),
				'pos_sowe_title'           => __( 'South-West', 'optimole-wp' ),
				'pos_so_title'             => __( 'South', 'optimole-wp' ),
				'pos_soea_title'           => __( 'South-East', 'optimole-wp' ),
				'offset_x_field'           => __( 'Offset X', 'optimole-wp' ),
				'offset_y_field'           => __( 'Offset Y', 'optimole-wp' ),
				'offset_title'             => __( 'Watermark offset', 'optimole-wp' ),
				'offset_desc'              => __( 'Offset the watermark from set position on X and Y axis. Values can be positive or negative.', 'optimole-wp' ),
				'scale_field'              => __( 'Scale', 'optimole-wp' ),
				'scale_title'              => __( 'Watermark scale', 'optimole-wp' ),
				'scale_desc'               => __( 'A value between 0 and 300 for the scale of the watermark (100 is the original size and 300 is 3x the size) relative to the resulting image size. If set to 0 it will default to the original size.', 'optimole-wp' ),
				'save_changes'             => __( 'Save changes', 'optimole-wp' ),
			],
			'latest_images'                  => [
				'image'                 => __( 'Image', 'optimole-wp' ),
				'no_images_found'       => sprintf( __( 'We are currently optimizing your images. Meanwhile you can visit your %1$shomepage%2$s and check how our plugin performs. ', 'optimole-wp' ), '<a href="' . esc_url( home_url() ) . '" target="_blank" >', '</a>' ),
				'compression'           => __( 'Optimization', 'optimole-wp' ),
				'loading_latest_images' => __( 'Loading your optimized images...', 'optimole-wp' ),
				'last'                  => __( 'Last', 'optimole-wp' ),
				'optimized_images'      => __( 'optimized images', 'optimole-wp' ),
				'same_size'             => __( '🙉 We couldn\'t do better, this image is already optimized at maximum. ', 'optimole-wp' ),
				'small_optimization'    => __( '😬 Not that much, just <strong>{ratio}</strong> smaller.', 'optimole-wp' ),
				'medium_optimization'   => __( '🤓 We are on the right track, <strong>{ratio}</strong> squeezed.', 'optimole-wp' ),
				'big_optimization'      => __( '❤️❤️❤️ Our moles just nailed it, this one is <strong>{ratio}</strong> smaller.  ', 'optimole-wp' ),
			],
		];
	}

	/**
	 * Allow SVG uploads
	 *
	 * @param array $mimes Supported mimes.
	 *
	 * @return array
	 * @access public
	 * @uses filter:upload_mimes
	 */
	public function allow_meme_types( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		return $mimes;
	}
}
