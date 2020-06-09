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
		add_action( 'plugin_action_links_' . plugin_basename( OPTML_BASEFILE ), array( $this, 'add_action_links' ) );
		add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), PHP_INT_MIN );
		add_action( 'admin_notices', array( $this, 'add_notice' ) );
		add_action( 'admin_notices', array( $this, 'add_notice_upgrade' ) );
		add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );
		add_action( 'optml_daily_sync', array( $this, 'daily_sync' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		if ( ! is_admin() && $this->settings->is_connected() && ! wp_next_scheduled( 'optml_daily_sync' ) ) {
			wp_schedule_event( time() + 10, 'daily', 'optml_daily_sync', array() );
		}
		add_action( 'optml_after_setup', array( $this, 'register_public_actions' ), 999999 );

	}

	/**
	 * Register public actions.
	 */
	public function register_public_actions() {
		add_action( 'wp_head', array( $this, 'generator' ) );
		add_filter( 'wp_resource_hints', array( $this, 'add_dns_prefetch' ), 10, 2 );

		if ( ! $this->settings->use_lazyload() ) {
			return;
		}
		if ( Optml_Manager::should_ignore_image_tags() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_filter( 'body_class', array( $this, 'adds_body_classes' ) );
		add_action( 'wp_head', array( $this, 'inline_bootstrap_script' ) );

	}

	/**
	 * Adds script for lazyload/js replacement.
	 */
	public function inline_bootstrap_script() {

		$domain = 'https://' . OPTML_JS_CDN;

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
		
		</style>
		<script type="application/javascript">
					document.documentElement.className += " optimole_has_js";
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
							scalingDisabled: %s,
							watchClasses: [%s],
							backgroundLazySelectors: "%s",
							network_optimizations: %s,
							ignoreDpr: %s,
							quality: %d
						}
						
					}(window, document));
					
					document.addEventListener( "DOMContentLoaded", function() { document.body.className = document.body.className.replace("optimole-no-script",""); } );
		</script>',
			esc_url( $domain ),
			$min,
			$bgclasses,
			$scale_is_disabled ? 'true' : 'false',
			$watcher_classes,
			addcslashes( $lazyload_bg_selectors, '"' ),
			defined( 'OPTML_NETWORK_ON' ) && constant( 'OPTML_NETWORK_ON' ) ? ( OPTML_NETWORK_ON ? 'true' : 'false' ) : ( $default_network ? 'true' : 'false' ),
			$retina_ready ? 'true' : 'false',
			$this->settings->get_numeric_quality()
		);
		echo $output;
	}

	/**
	 * Adds body class  for no-js.
	 *
	 * @param array $classes No js class.
	 *
	 * @return array
	 */
	public function adds_body_classes( $classes ) {
		$classes[] = 'optimole-no-script';

		return $classes;
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
			array(
				'<a href="' . admin_url( 'upload.php?page=optimole' ) . '">' . __( 'Settings', 'optimole-wp' ) . '</a>',
			)
		);
	}

	/**
	 * Adds optimole optin class.
	 *
	 * @return string Optimole class.
	 */
	public function add_body_class( $classes ) {

		if ( ! $this->should_show_notice() ) {
			return $classes;
		}

		return $classes . ' optimole-optin-show ';
	}

	/**
	 * Check if we should show the notice.
	 *
	 * @return bool Should show?
	 */
	public function should_show_notice() {

		$current_screen = get_current_screen();
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
			 is_network_admin() ||
			 $this->settings->is_connected() ||
			 empty( $current_screen )
		) {
			return false;
		}

		static $allowed_base = array(
			'plugins'                               => true,
			'upload'                                => true,
			'media'                                 => true,
			'themes'                                => true,
			'appearance_page_tgmpa-install-plugins' => true,
		);

		$screen_slug = '';
		if ( isset( $current_screen->base ) ) {
			$screen_slug = $current_screen->base;
		}

		if ( isset( $current_screen->parent_base ) ) {
			$screen_slug = $current_screen->parent_base;
		}

		if ( empty( $screen_slug ) ||
			 ( ! isset( $allowed_base[ $screen_slug ] ) ) ||
			 ! current_user_can( 'manage_options' ) ||
			 ( get_option( 'optml_notice_optin', 'no' ) === 'yes' )
		) {
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
		<div class="notice notice-warning optml-notice-optin">
			<p> <?php printf( __( 'It seems your are close to the %1$s5.0000%2$s visits limit with %3$sOptiMole%4$s for this month. You might want to check the upgrade plans for a larger quota. %5$s %6$s What happens if i exceed the quota ?%7$s We will need to deliver back your original %8$sun-optimized%9$s images which might decrease your site speed perfomance.', 'optimole-wp' ), '<strong>', '</strong>', '<strong>', '</strong>', '<br/><br/>', '<i>', '</i >', '<strong>', '</strong>' ); ?></p>
			<p>
				<a href="https://optimole.com/pricing" target="_blank" class="button button-primary"><span
							class="dashicons dashicons-external"></span><?php _e( 'Check upgrade plans', 'optimole-wp' ); ?>
				</a>
				<a class="button"
				   href="<?php echo wp_nonce_url( add_query_arg( array( 'optml_hide_upg' => 'yes' ) ), 'hide_nonce', 'optml_nonce' ); ?>"><?php _e( 'I\'ve done this', 'optimole-wp' ); ?></a>
			</p>
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
		<div class="notice notice-success optml-notice-optin">
			<p> <?php printf( __( 'Welcome to %1$sOptiMole%2$s, the easiest way to optimize your website images. Your users will enjoy a %3$sfaster%4$s website after you connect it with our service.', 'optimole-wp' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'upload.php?page=optimole' ) ); ?>"
				   class="button button-primary"><?php _e( 'Connect to OptiMole', 'optimole-wp' ); ?></a>
				<a class="button"
				   href="<?php echo wp_nonce_url( add_query_arg( array( 'optml_hide_optin' => 'yes' ) ), 'hide_nonce', 'optml_nonce' ); ?>"><?php _e( 'I will do it later', 'optimole-wp' ); ?></a>
			</p>
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
			$css[] = 'html.optimole_has_js ' . $selector . ':not(.optml-bg-lazyloaded)';
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
		wp_add_inline_style( 'optm_lazyload_noscript_style', ".optimole-no-script img[data-opt-src] { display: none !important; } \n " . $bg_css );
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
		if ( empty( $api_key ) ) {
			return;
		}

		$request = new Optml_Api();
		$data    = $request->get_user_data( $api_key );
		if ( $data === false || is_wp_error( $data ) ) {
			return;
		}

		$this->settings->update( 'service_data', $data );

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

		if ( ! $this->settings->use_lazyload() && ! Optml_Manager::should_ignore_image_tags() ) {
			$hints[] = sprintf( 'https://%s', OPTML_JS_CDN );
		}

		return $hints;
	}

	/**
	 * Add the dashboard page.
	 */
	public function add_dashboard_page() {
		if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
			return;
		}
		add_media_page( 'Optimole', 'Optimole', 'manage_options', 'optimole', array( $this, 'render_dashboard_page' ) );
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
		if ( $current_screen->id != 'media_page_optimole' ) {
			return;
		}
		wp_register_script( OPTML_NAMESPACE . '-admin', OPTML_URL . 'assets/js/bundle' . ( ! OPTML_DEBUG ? '.min' : '' ) . '.js', array(), OPTML_VERSION );
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
		$api_key      = $this->settings->get( 'api_key' );
		$service_data = $this->settings->get( 'service_data' );
		$user         = get_userdata( get_current_user_id() );

		return array(
			'strings'              => $this->get_dashboard_strings(),
			'assets_url'           => OPTML_URL . 'assets/',
			'connection_status'    => empty( $service_data ) ? 'no' : 'yes',
			'user_status'          => isset( $service_data['status'] ) && $service_data['status'] === 'inactive' ? 'inactive' : 'active',
			'api_key'              => $api_key,
			'root'                 => untrailingslashit( rest_url( OPTML_NAMESPACE . '/v1' ) ),
			'nonce'                => wp_create_nonce( 'wp_rest' ),
			'user_data'            => $service_data,
			'remove_latest_images' => defined( 'OPTML_REMOVE_LATEST_IMAGES' ) && constant( 'OPTML_REMOVE_LATEST_IMAGES' ) ? ( OPTML_REMOVE_LATEST_IMAGES ? 'yes' : 'no' ) : 'no',
			'current_user'         => array(
				'email' => $user->user_email,
			),
			'site_settings'        => $this->settings->get_site_settings(),
			'home_url'             => home_url(),
		);
	}

	/**
	 * Get all dashboard strings.
	 *
	 * @codeCoverageIgnore
	 * @return array
	 */
	private function get_dashboard_strings() {
		return array(
			'optimole'                       => 'Optimole',
			'version'                        => OPTML_VERSION,
			'terms_menu'                     => __( 'Terms', 'optimole-wp' ),
			'privacy_menu'                   => __( 'Privacy', 'optimole-wp' ),
			'testdrive_menu'                 => __( 'Test Optimole', 'optimole-wp' ),
			'service_details'                => __( 'Image optimization service', 'optimole-wp' ),
			'connect_btn'                    => __( 'Connect to OptiMole Service', 'optimole-wp' ),
			'disconnect_btn'                 => __( 'Disconnect', 'optimole-wp' ),
			'refresh_stats_cta'              => __( 'Refresh stats', 'optimole-wp' ),
			'updating_stats_cta'             => __( 'Updating stats', 'optimole-wp' ),
			'api_key_placeholder'            => __( 'API Key', 'optimole-wp' ),
			'account_needed_heading'         => __( 'Sign-up for API key', 'optimole-wp' ),
			'invalid_key'                    => __( 'Invalid API Key', 'optimole-wp' ),
			'status'                         => __( 'Status', 'optimole-wp' ),
			'email_address_label'            => __( 'Your email address', 'optimole-wp' ),
			'register_btn'                   => __( 'Register & Email API key', 'optimole-wp' ),
			'step_one_api_title'             => __( 'Enter your API key.', 'optimole-wp' ),
			'step_one_api_desc'              => sprintf( __( 'Copy the API key you have received via email or you can get it from %1$s Optimole dashboard%2$s. <br/>', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/" target="_blank"> ', '</a>' ),
			'step_two_api_title'             => __( 'Connect to Optimole.', 'optimole-wp' ),
			'step_two_api_desc'              => __( ' Fill in the upper API key field and connect to Optimole service.', 'optimole-wp' ),
			'api_exists'                     => __( 'I already have an API key.', 'optimole-wp' ),
			'back_to_register'               => __( 'Register account', 'optimole-wp' ),
			'back_to_connect'                => __( 'Connect account', 'optimole-wp' ),
			'error_register'                 => sprintf( __( 'Error registering account. You can try again %1$shere%2$s ', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/register" target="_blank"> ', '</a>' ),
			'connected'                      => __( 'Connected', 'optimole-wp' ),
			'not_connected'                  => __( 'Not connected', 'optimole-wp' ),
			'usage'                          => __( 'Monthly Usage', 'optimole-wp' ),
			'quota'                          => __( 'Monthly Quota', 'optimole-wp' ),
			'logged_in_as'                   => __( 'Logged in as', 'optimole-wp' ),
			'private_cdn_url'                => __( 'Images domain', 'optimole-wp' ),
			'notification_message_register'  => __( 'We have sent you an email with the API key. Please copy and paste the key in the field below.', 'optimole-wp' ),
			'account_needed_title'           => sprintf(
				__( 'In order to get access to free image optimization service you will need an API key from %s.', 'optimole-wp' ),
				' <a href="https://dashboard.optimole.com/register" target="_blank">optimole.com</a>'
			),
			'account_needed_subtitle_1'      => sprintf(
				__( 'You will get access to our image optimization service for %1$sFREE%2$s in the limit of %3$s5k%4$s %5$svisitors%6$s per month. ', 'optimole-wp' ),
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
				__( 'Bonus, if you dont use a CDN, we got you covered, we will serve the images using CloudFront CD from 200 locations.', 'optimole-wp' )
			),
			'notice_just_activated'          => ! $this->settings->is_connected() ?
				sprintf( __( '%1$sImage optimisation is currently running.%2$s <br/> Your visitors will now view the best image for their device automatically, all served from the Optimole Cloud Service on the fly. You might see for the very first image request being redirected to the original URL while we do the optimization in the background.<br/> You can relax, we\'ll take it from here.', 'optimole-wp' ), '<strong>', '</strong>' )
				: '',
			'notice_api_not_working'         => __(
				'It seems there is an issue with your WordPress configuration and the core REST API functionality is not available. This is crucial as Optimole relies on this functionality in order to work.<br/>
The root cause might be either a security plugin which blocks this feature or some faulty server configuration which constrain this WordPress feature.You can try to disable any of the security plugins that you use in order to see if the issue persists or ask the hosting company to further investigate.',
				'optimole-wp'
			),
			'notice_disabled_account'        => sprintf( __( 'Your account has been disabled due to exceeding quota. All images are being redirected to the original unoptimized URL. Please %1$supgrade%2$s to re-activate the account.', 'optimole-wp' ), '<a href="https://optimole.com/pricing">', '</a>' ),
			'dashboard_menu_item'            => __( 'Dashboard', 'optimole-wp' ),
			'settings_menu_item'             => __( 'Settings', 'optimole-wp' ),
			'settings_exclusions_menu_item'  => __( 'Exclusions', 'optimole-wp' ),
			'settings_resize_menu_item'      => __( 'Resize', 'optimole-wp' ),
			'settings_compression_menu_item' => __( 'Compression', 'optimole-wp' ),
			'advanced_settings_menu_item'    => __( 'Advanced', 'optimole-wp' ),
			'general_settings_menu_item'     => __( 'General', 'optimole-wp' ),
			'lazyload_settings_menu_item'    => __( 'Lazyload', 'optimole-wp' ),
			'watermarks_menu_item'           => __( 'Watermark', 'optimole-wp' ),
			'conflicts_menu_item'            => __( 'Possible issues', 'optimole-wp' ),
			'conflicts'                      => array(
				'title'              => __( 'We might have some possible conflicts with the plugins that you use. In order to benefit from Optimole\'s full potential you will need to address this issues.', 'optimole-wp' ),
				'message'            => __( 'Details', 'optimole-wp' ),
				'conflict_close'     => __( 'I\'ve done this.', 'optimole-wp' ),
				'no_conflicts_found' => __( 'No conflicts found. We are all peachy now. 🍑', 'optimole-wp' ),
			),
			'upgrade'                        => array(
				'title'    => __( 'Upgrade to Pro', 'optimole-wp' ),
				'reason_1' => __( 'Priority & Live Chat support', 'optimole-wp' ),
				'reason_2' => __( 'Extend visits limit', 'optimole-wp' ),
				'reason_3' => __( 'Custom domain', 'optimole-wp' ),
				'reason_4' => __( 'Site audit', 'optimole-wp' ),
				'cta'      => __( 'View plans', 'optimole-wp' ),
			),
			'options_strings'                => array(
				'add_filter'                        => __( 'Add filter', 'optimole-wp' ),
				'admin_bar_desc'                    => __( 'Show in the WordPress admin bar the available quota from Optimole service.', 'optimole-wp' ),
				'auto_q_title'                      => __( 'Auto', 'optimole-wp' ),
				'cache_desc'                        => __( 'Clear all cached resources(images,js,css) by Optimole from this site. Useful if you updated them and Optimole shows the old version.', 'optimole-wp' ),
				'cache_title'                       => __( 'Clear cached resources', 'optimole-wp' ),
				'clear_cache'                       => __( 'Clear cached resources', 'optimole-wp' ),
				'connect_step_0'                    => __( 'Connecting your site to the Optimole service.', 'optimole-wp' ),
				'connect_step_1'                    => __( 'Checking for possible conflicts.', 'optimole-wp' ),
				'connect_step_2'                    => __( 'Inspecting the images from your site.', 'optimole-wp' ),
				'connect_step_3'                    => __( 'All done, Optimole is currently optimizing your site.', 'optimole-wp' ),
				'disabled'                          => __( 'Disabled', 'optimole-wp' ),
				'enable_bg_lazyload_desc'           => __( 'Lazyload images used as CSS backgrounds.', 'optimole-wp' ),
				'enable_bg_lazyload_title'          => __( 'Enable lazyload for background images', 'optimole-wp' ),
				'enable_gif_replace_title'          => __( 'Enable Gif to Video conversion', 'optimole-wp' ),
				'enable_image_replace'              => __( 'Enable image replacement', 'optimole-wp' ),
				'enable_lazyload_placeholder_desc'  => __( 'Enabling this might affect the user experience in some cases, however it will reduce the number of total requests and page weight. Try it out and see how works best for you!', 'optimole-wp' ),
				'enable_lazyload_placeholder_title' => __( 'Enable generic lazyload placeholder', 'optimole-wp' ),
				'enable_network_opt_desc'           => __( 'Optimole provides an option to automatically downgrade the image quality when it detects a slower network.', 'optimole-wp' ),
				'enable_network_opt_title'          => __( 'Enable network based optimizations', 'optimole-wp' ),
				'enable_resize_smart_desc'          => __( 'Detects the most interesting section of the image and considers it as the center of the resulting image.', 'optimole-wp' ),
				'enable_resize_smart_title'         => __( 'Enable Smart Cropping', 'optimole-wp' ),
				'enable_retina_desc'                => __( 'Deliver retina ready images to your visitors', 'optimole-wp' ),
				'enable_retina_title'               => __( 'Enable Retina images', 'optimole-wp' ),
				'enabled'                           => __( 'Enabled', 'optimole-wp' ),
				'exclude_class_desc'                => __( 'Image tag contains class', 'optimole-wp' ),
				'exclude_ext_desc'                  => __( 'Image extension is', 'optimole-wp' ),
				'exclude_filename_desc'             => __( 'Image filename contains', 'optimole-wp' ),
				'exclude_title_lazyload'            => __( 'Don\'t lazyload images if', 'optimole-wp' ),
				'exclude_title_optimize'            => __( 'Don\'t optimize images if', 'optimole-wp' ),
				'exclude_url_desc'                  => __( 'Page url contains', 'optimole-wp' ),
				'filter_class'                      => __( 'Image class', 'optimole-wp' ),
				'filter_ext'                        => __( 'Image extension', 'optimole-wp' ),
				'filter_filename'                   => __( 'Image filename', 'optimole-wp' ),
				'filter_operator_contains'          => __( 'contains', 'optimole-wp' ),
				'filter_operator_is'                => __( 'is', 'optimole-wp' ),
				'filter_url'                        => __( 'Page URL', 'optimole-wp' ),
				'gif_replacer_desc'                 => __( 'Automatically convert GIF images to Video files(MP4 and WebM)', 'optimole-wp' ),
				'height_field'                      => __( 'Height', 'optimole-wp' ),
				'hide'                              => __( 'Hide', 'optimole-wp' ),
				'high_q_title'                      => __( 'High', 'optimole-wp' ),
				'image_1_label'                     => __( 'Original', 'optimole-wp' ),
				'image_2_label'                     => __( 'Optimized', 'optimole-wp' ),
				'lazyload_desc'                     => __( 'We will generate images size based on your visitor\'s screen using javascript and render them without blocking the page execution via lazyload .', 'optimole-wp' ),
				'scale_desc'                        => __( 'When this option is off, we disable the automatic scaling of images on lazyload.', 'optimole-wp' ),
				'low_q_title'                       => __( 'Low', 'optimole-wp' ),
				'medium_q_title'                    => __( 'Medium', 'optimole-wp' ),
				'no_images_found'                   => __( 'You dont have any images in your Media Library. Add one and check how the Optimole will perform.', 'optimole-wp' ),
				'option_saved'                      => __( 'Option saved.', 'optimole-wp' ),
				'quality_desc'                      => __( 'Lower image quality might not always be perceived by users and would result in a boost of your loading speed by lowering the page size. Try experimenting with the setting, then click the View sample image link to see what option works best for you.', 'optimole-wp' ),
				'quality_selected_value'            => __( 'Selected value', 'optimole-wp' ),
				'quality_slider_desc'               => __( 'See one sample image which will help you choose the right quality of the compression.', 'optimole-wp' ),
				'quality_title'                     => __( 'Image quality', 'optimole-wp' ),
				'replacer_desc'                     => __( 'Replace all the image urls from your website with the ones optimized by Optimole.', 'optimole-wp' ),
				'sample_image_loading'              => __( 'Loading a sample image. ', 'optimole-wp' ),
				'save_changes'                      => __( 'Save changes', 'optimole-wp' ),
				'show'                              => __( 'Show', 'optimole-wp' ),
				'size_desc'                         => __( 'We will resize all images with sizes greater than these values.', 'optimole-wp' ),
				'size_title'                        => __( 'Resize large images.', 'optimole-wp' ),
				'toggle_ab_item'                    => __( 'Admin bar status', 'optimole-wp' ),
				'toggle_lazyload'                   => __( 'Scale images & Lazy load', 'optimole-wp' ),
				'toggle_scale'                      => __( 'Scale Images', 'optimole-wp' ),
				'on_toggle'                         => __( 'On', 'optimole-wp' ),
				'off_toggle'                        => __( 'Off', 'optimole-wp' ),
				'view_sample_image'                 => __( 'View sample image', 'optimole-wp' ),
				'watch_desc_lazyload'               => __( 'You can add each CSS selector on a new line or separated by comma(,).', 'optimole-wp' ),
				'watch_title_lazyload'              => __( 'Lazyload background images for selectors:', 'optimole-wp' ),
				'width_field'                       => __( 'Width', 'optimole-wp' ),
				'toggle_cdn'                        => __( 'Serve CSS & JS through Optimole', 'optimole-wp' ),
				'cdn_desc'                          => __( 'Useful when you have images into CSS/JS files. Optimole wil optimize the images from them and serve the CSS/JS through the CDN.', 'optimole-wp' ),
				'enable_css_minify_title'           => __( 'Minify CSS files', 'optimole-wp' ),
				'css_minify_desc'                   => __( 'Once Optimole will serve your CSS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
				'enable_js_minify_title'            => __( 'Minify JS files', 'optimole-wp' ),
				'js_minify_desc'                    => __( 'Once Optimole will serve your JS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
			),
			'watermarks'                     => array(
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
			),
			'latest_images'                  => array(
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
			),
		);
	}

}
