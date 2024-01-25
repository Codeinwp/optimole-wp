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

	const IMAGE_DATA_COLLECTED = 'optml_image_data_collected';

	const IMAGE_DATA_COLLECTED_BATCH = 100;
	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	public $settings;

	/**
	 * Hold the plugin conflict object.
	 *
	 * @var Optml_Conflicting_Plugins Settings object.
	 */
	public $conflicting_plugins;

	const NEW_USER_DEFAULTS_UPDATED = 'optml_defaults_updated';
	const OLD_USER_ENABLED_LD = 'optml_enabled_limit_dimensions';
	const OLD_USER_ENABLED_CL = 'optml_enabled_cloud_sites';

	/**
	 * Optml_Admin constructor.
	 */
	public function __construct() {
		$this->settings = new Optml_Settings();
		$this->conflicting_plugins = new Optml_Conflicting_Plugins();

		add_filter( 'plugin_action_links_' . plugin_basename( OPTML_BASEFILE ), [ $this, 'add_action_links' ] );
		add_action( 'admin_menu', [ $this, 'add_dashboard_page' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_subpage' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'menu_icon_style' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], PHP_INT_MIN );
		add_action( 'admin_notices', [ $this, 'add_notice' ] );
		add_action( 'admin_notices', [ $this, 'add_notice_upgrade' ] );
		add_action( 'admin_notices', [ $this, 'add_notice_conflicts' ] );
		add_action( 'optml_daily_sync', [ $this, 'daily_sync' ] );
		add_action( 'admin_init', [ $this, 'redirect_old_dashboard' ] );

		if ( $this->settings->is_connected() ) {
			add_action( 'init', [$this, 'check_domain_change'] );
			add_action( 'optml_pull_image_data_init', [$this, 'pull_image_data_init'] );
			add_action( 'optml_pull_image_data', [$this, 'pull_image_data'] );

			// Backwards compatibility for older versions of WordPress < 6.0.0 requiring 3 parameters for this specific filter.
			$below_6_0_0 = version_compare( get_bloginfo( 'version' ), '6.0.0', '<' );
			if ( $below_6_0_0 ) {
				add_filter( 'wp_insert_attachment_data', [$this, 'legacy_detect_image_title_changes'], 10, 3 );
			} else {
				add_filter( 'wp_insert_attachment_data', [$this, 'detect_image_title_changes'], 10, 4 );
			}

			add_action( 'updated_post_meta', [$this, 'detect_image_alt_change' ], 10, 4 );
			add_action( 'added_post_meta', [$this, 'detect_image_alt_change'], 10, 4 );
			add_action( 'init', [ $this, 'schedule_data_enhance_cron' ] );
		}
		add_action( 'init', [ $this, 'update_default_settings' ] );
		add_action( 'init', [ $this, 'update_limit_dimensions' ] );
		add_action( 'init', [ $this, 'update_cloud_sites_default' ] );
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
	 * Schedules the hourly cron that starts the querying for images alt/title attributes
	 *
	 * @uses action: init
	 */
	public function schedule_data_enhance_cron() {
		if ( ! wp_next_scheduled( 'optml_pull_image_data_init' ) ) {
			wp_schedule_event( time(), 'hourly', 'optml_pull_image_data_init' );
		}
	}

	/**
	 * Query the database for images and extract the alt/title to send them to the API
	 *
	 * @uses action: optml_pull_image_data
	 */
	public function pull_image_data() {
		// Get all image attachments that are not processed
		$args = [
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => self::IMAGE_DATA_COLLECTED_BATCH,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => self::IMAGE_DATA_COLLECTED,
					'compare' => 'NOT EXISTS',
				],
			],
		];
		$attachments = get_posts( $args );

		$image_data = [];

		foreach ( $attachments as $attachment ) {
			// Get image URL, alt, and title
			$image_url = wp_get_attachment_url( $attachment->ID );
			$image_alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			$image_title = $attachment->post_title;
			$image_data[ $image_url ] = [];

			if ( ! empty( $image_alt ) ) {
				$image_data[ $image_url ]['alt'] = $image_alt;
			}
			if ( ! empty( $image_title ) ) {
				$image_data[ $image_url ]['title'] = $image_title;
			}
			if ( empty( $image_data[ $image_url ] ) ) {
				unset( $image_data[ $image_url ] );
			}

			// Mark the image as processed
			update_post_meta( $attachment->ID, self::IMAGE_DATA_COLLECTED, 'yes' );
		}

		if ( ! empty( $image_data ) ) {
			$api = new Optml_Api();
			$api->call_data_enrich_api( $image_data );
		}
		if ( ! empty( $attachments ) ) {
			wp_schedule_single_event( time() + 5, 'optml_pull_image_data' );
		}
	}

	/**
	 * Schedule the event to pull image alt/title
	 *
	 * @uses action: optml_pull_image_data_init
	 */
	public function pull_image_data_init() {
		if ( ! wp_next_scheduled( 'optml_pull_image_data' ) ) {
			wp_schedule_single_event( time() + 5, 'optml_pull_image_data' );
		}
	}

	/**
	 * Delete the processed meta from an image when the alt text is changed
	 *
	 * @uses action: updated_post_meta, added_post_meta
	 */
	public function detect_image_alt_change( $meta_id, $post_id, $meta_key, $meta_value ) {

		// Check if the updated metadata is for alt text and it's an attachment
		if ( $meta_key === '_wp_attachment_image_alt' && get_post_type( $post_id ) === 'attachment' ) {
			delete_post_meta( $post_id, self::IMAGE_DATA_COLLECTED );
		}
	}
	/**
	 * Delete the processed meta from an image when the title is changed
	 *
	 * @uses filter: wp_insert_attachment_data
	 */
	public function detect_image_title_changes( $data, $postarr, $unsanitized_postarr, $update ) {

		// Check if it's an attachment being updated
		if ( $data['post_type'] !== 'attachment' ) {
			return $data;
		}
		if ( ! isset( $postarr['ID'] ) ) {
			return $data;
		}
		if ( isset( $postarr['post_title'] ) && isset( $postarr['original_post_title'] ) && $postarr['post_title'] !== $postarr['original_post_title'] ) {
			delete_post_meta( $postarr['ID'], self::IMAGE_DATA_COLLECTED );
		}

		return $data;
	}
	/**
	 * Delete the processed meta from an image when the title is changed
	 *
	 * @uses filter: wp_insert_attachment_data
	 */
	public function legacy_detect_image_title_changes( $data, $postarr, $unsanitized_postarr ) {
		return $this->detect_image_title_changes( $data, $postarr, $unsanitized_postarr, true );
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
	 * Update the limit dimensions setting to enabled if the user is new.
	 */
	public function update_default_settings() {
		if ( get_option( self::NEW_USER_DEFAULTS_UPDATED ) === 'yes' ) {
			return;
		}

		if ( $this->settings->is_connected() ) {
			update_option( self::NEW_USER_DEFAULTS_UPDATED, 'yes' );
			return;
		}

		$this->settings->update( 'limit_dimensions', 'enabled' );
		$this->settings->update( 'lazyload', 'enabled' );
		$this->settings->update( 'best_format', 'disabled' );
		$this->settings->update( 'skip_lazyload_images', '2' );
		$this->settings->update( 'avif', 'disabled' );

		update_option( self::NEW_USER_DEFAULTS_UPDATED, 'yes' );
	}
	/**
	 * Enable limit dimensions for old users after removing the Resize option.
	 *
	 * @return void
	 */
	public function update_limit_dimensions() {
		if ( get_option( self::OLD_USER_ENABLED_LD ) === 'yes' ) {
			return;
		}

		// New users already have this enabled as we changed defaults.
		if ( ! $this->settings->is_connected() ) {
			return;
		}

		$this->settings->update( 'limit_dimensions', 'enabled' );

		update_option( self::OLD_USER_ENABLED_LD, 'yes' );
	}

	/**
	 * Enable cloud sites and add the current site to the whitelist.
	 *
	 * @return void
	 */
	public function update_cloud_sites_default() {
		if ( get_option( self::OLD_USER_ENABLED_CL ) === 'yes' ) {
			return;
		}

		if ( ! $this->settings->is_connected() ) {
			return;
		}

		// This is already enabled. Don't alter it.
		if ( $this->settings->get( 'cloud_images' ) === 'enabled' ) {
			update_option( self::OLD_USER_ENABLED_CL, 'yes' );

			return;
		}

		$this->settings->update( 'cloud_images', 'enabled' );
		$this->settings->update( 'cloud_sites', $this->settings->get_cloud_sites_whitelist_default() );

		update_option( self::OLD_USER_ENABLED_CL, 'yes' );
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
		if ( ! $this->settings->use_lazyload()
			|| ( $this->settings->get( 'native_lazyload' ) === 'enabled'
				&& $this->settings->get( 'video_lazyload' ) === 'disabled'
				&& $this->settings->get( 'bg_replacer' ) === 'disabled' ) ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
		add_action( 'wp_head', [ $this, 'inline_bootstrap_script' ] );

		add_filter( 'optml_additional_html_classes', [ $this, 'add_no_js_class_to_html_tag' ], 10 );
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
		$limit_dimensions      = $this->settings->get( 'limit_dimensions' ) === 'enabled';
		$limit_width           = $limit_dimensions ? $this->settings->get( 'limit_width' ) : 0;
		$limit_height          = $limit_dimensions ? $this->settings->get( 'limit_height' ) : 0;
		$retina_ready          = $limit_dimensions ||
								 ! ( $this->settings->get( 'retina_images' ) === 'enabled' );
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
								quality: %d,
								maxWidth: %d,
								maxHeight: %d,
							}
						}(window, document));
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
			$this->settings->get_numeric_quality(),
			$limit_width,
			$limit_height
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
				'<a href="' . admin_url( 'admin.php?page=optimole' ) . '">' . __( 'Settings', 'optimole-wp' ) . '</a>',
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
		if ( $current_screen->base !== 'upload' ) {
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
	 * CSS styles for Notice.
	 */
	public static function notice_styles() {
		?>
		<style>
			.optml-notice-optin:not(.has-dismiss) {
				background: url(" <?php echo esc_attr( OPTML_URL . '/assets/img/disconnected.svg' ); ?> ") #fff 100% 0 no-repeat;
				position: relative;
				padding: 0;
			}

			.optml-notice-optin.has-dismiss {
				position: relative;
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
		<?php
	}

	/**
	 * JS for Notice.
	 */
	public static function notice_js( $action ) {
		?>
		<script>
			jQuery(document).ready(function($) {
				// AJAX request to update the option value
				$( '.optml-notice-optin button.notice-dismiss' ).click(function(e) {
					e.preventDefault();

					var notice = $(this).closest( '.optml-notice-optin' );
					var nonce = '<?php echo esc_attr( wp_create_nonce( $action ) ); ?>';

					$.ajax({
						url: window.ajaxurl,
						type: 'POST',
						data: {
							action: '<?php echo esc_attr( $action ); ?>',
							nonce
						},
						complete() {
							notice.remove();
						}
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Adds opt in notice.
	 */
	public function add_notice() {
		if ( ! $this->should_show_notice() ) {
			return;
		}

		self::notice_styles();
		?>
		<div class="notice notice-info optml-notice-optin">
			<div class="content">
				<img src="<?php echo OPTML_URL . '/assets/img/logo.svg'; ?>" alt="<?php echo esc_attr__( 'Logo', 'optimole-wp' ); ?>"/>

				<div>
					<p class="notice-title"> <?php echo esc_html__( 'Finish setting up!', 'optimole-wp' ); ?></p>
					<p class="description"> <?php printf( __( 'Welcome to %1$sOptiMole%2$s, the easiest way to optimize your website images. Your users will enjoy a %3$sfaster%4$s website after you connect it with our service.', 'optimole-wp' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
					<div class="actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=optimole' ) ); ?>"
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
	 * Adds conflicts notice.
	 */
	public function add_notice_conflicts() {
		if ( $this->settings->is_connected() || ! $this->conflicting_plugins->should_show_notice() ) {
			return;
		}

		$plugins = $this->conflicting_plugins->get_conflicting_plugins();
		$names   = [];

		foreach ( $plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
			$names[]     = $plugin_data['Name'];
		}

		$names = implode( ', ', $names );

		self::notice_styles();
		self::notice_js( 'optml_dismiss_conflict_notice' );
		?>
		<div class="notice notice-info optml-notice-optin has-dismiss">
			<div class="content">
				<img src="<?php echo OPTML_URL . '/assets/img/logo.svg'; ?>" alt="<?php echo esc_attr__( 'Logo', 'optimole-wp' ); ?>"/>

				<div>
					<p class="notice-title"><strong><?php echo esc_html__( 'Oops... Multiple image optimization plugins active', 'optimole-wp' ); ?></strong></p>
					<p class="description"> <?php printf( __( 'We noticed multiple image optimization plugins active on your site, which may cause issues in Optimole. We recommend using only one image optimization plugin on your site for the best results. The following plugins may cause issues in Optimole: %2$s%1$s%3$s.', 'optimole-wp' ), $names, '<strong>', '</strong>' ); ?></p>
					<div class="actions">
						<a href="<?php echo esc_url( admin_url( 'plugins.php?optimole_conflicts' ) ); ?>"
						   class="button button-primary button-hero"><?php _e( 'Manage Plugins', 'optimole-wp' ); ?>
						</a>
					</div>
				</div>
			</div>

			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'optimole-wp' ); ?></span>
			</button>
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

		wp_safe_redirect( admin_url( 'admin.php?page=optimole' ) );
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

		if ( isset( $data['extra_visits'] ) ) {
			$this->settings->update_frontend_banner_from_remote( $data['extra_visits'] );
		}

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

		add_menu_page(
			'Optimole',
			'Optimole',
			'manage_options',
			'optimole',
			[ $this, 'render_dashboard_page' ],
			OPTML_URL . 'assets/img/logo.svg',
			11
		);
	}

	/**
	 * Add menu icon style.
	 *
	 * @return void
	 */
	public function menu_icon_style() {
		echo '<style>#toplevel_page_optimole img{ max-width:22px;padding-top:6px!important;opacity:.9!important;} #toplevel_page_optimole li.wp-first-item{ display:none }</style>';
	}

	/**
	 * Add the settings page.
	 *
	 * We do it with another priority as it goes above the cloud library otherwise.
	 */
	public function add_settings_subpage() {
		if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
			return;
		}

		if ( ! $this->settings->is_connected() ) {
			return;
		}

		add_submenu_page(
			'optimole',
			__( 'Settings', 'optimole-wp' ),
			__( 'Settings', 'optimole-wp' ),
			'manage_options',
			'optimole#settings',
			[ $this, 'render_dashboard_page' ]
		);
	}


	/**
	 * Redirect old dashboard.
	 *
	 * @return void
	 */
	public function redirect_old_dashboard() {
		if ( ! is_admin() ) {
			return;
		}

		global $pagenow;

		if ( $pagenow !== 'upload.php' ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( $_GET['page'] !== 'optimole' ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=optimole' ) );

		exit;
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		if ( get_option( 'optml_notice_optin', 'no' ) !== 'yes' ) {
			update_option( 'optml_notice_optin', 'yes' );
		}
		?>
			<style >
				.notice:not(.optml-notice-optin){
					display: none !important;
				}
			</style>
		<div id="optimole-app"></div>
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

		if ( $current_screen->id !== 'toplevel_page_optimole' ) {
			return;
		}

		$asset_file = include OPTML_PATH . 'assets/build/dashboard/index.asset.php';

		wp_register_script(
			OPTML_NAMESPACE . '-admin',
			OPTML_URL . 'assets/build/dashboard/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script( OPTML_NAMESPACE . '-admin', 'optimoleDashboardApp', $this->localize_dashboard_app() );
		wp_enqueue_script( OPTML_NAMESPACE . '-admin' );

		wp_enqueue_style(
			OPTML_NAMESPACE . '-admin',
			OPTML_URL . 'assets/build/dashboard/style-index.css',
			[
				'wp-components',
			],
			$asset_file['version']
		);
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
				$routes[ $route ] = OPTML_NAMESPACE . '/v1/' . $route;
			}
		}

		$cron_disabled = apply_filters( 'optml_offload_wp_cron_disabled', defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' ) === true );

		return [
			'strings'                    => $this->get_dashboard_strings(),
			'assets_url'                 => OPTML_URL . 'assets/',
			'dam_url'                    => 'admin.php?page=optimole-dam',
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
			'offload_limit'              => $this->settings->get( 'offload_limit' ),
			'home_url'                   => home_url(),
			'days_since_install' => round( ( time() - get_option( 'optimole_wp_install', 0 ) ) / DAY_IN_SECONDS ),
			'is_offload_media_available' => $is_offload_media_available,
			'auto_connect'               => $auto_connect,
			'cron_disabled' => $cron_disabled && ! function_exists( 'as_schedule_single_action' ),
			'submenu_links' => [
				[
					'href' => 'admin.php?page=optimole#settings',
					'text' => __( 'Settings', 'optimole-wp' ),
					'hash' => '#settings',
				],
			],
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
			'optimole'                         => 'Optimole',
			'version'                          => OPTML_VERSION,
			'terms_menu'                       => __( 'Terms', 'optimole-wp' ),
			'privacy_menu'                     => __( 'Privacy', 'optimole-wp' ),
			'testdrive_menu'                   => __( 'Test Optimole', 'optimole-wp' ),
			'service_details'                  => __( 'Image optimization service', 'optimole-wp' ),
			'connect_btn'                      => __( 'Connect to Optimole', 'optimole-wp' ),
			'disconnect_btn'                   => __( 'Disconnect', 'optimole-wp' ),
			'select'                           => __( 'Select', 'optimole-wp' ),
			'your_domain'                      => __( 'your domain', 'optimole-wp' ),
			'add_api'                          => __( 'Add your API Key', 'optimole-wp' ),
			'your_api_key'                     => __( 'Your API Key', 'optimole-wp' ),
			'looking_for_api_key'              => __( 'LOOKING FOR YOUR API KEY?', 'optimole-wp' ),
			'refresh_stats_cta'                => __( 'Refresh Stats', 'optimole-wp' ),
			'updating_stats_cta'               => __( 'UPDATING STATS', 'optimole-wp' ),
			'api_key_placeholder'              => __( 'API Key', 'optimole-wp' ),
			'account_needed_heading'           => __( 'Sign-up for API key', 'optimole-wp' ),
			'invalid_key'                      => __( 'Invalid API Key', 'optimole-wp' ),
			'keep_connected'                   => __( 'Ok, keep me connected', 'optimole-wp' ),
			'cloud_library'                    => __( 'Cloud Library', 'optimole-wp' ),
			'image_storage'                    => __( 'Image Storage', 'optimole-wp' ),
			'disconnect_title'                 => __( 'You are about to disconnect from the Optimole API', 'optimole-wp' ),
			'disconnect_desc'                  => __(
				'Please note that disconnecting your site from the Optimole API will impact your website performance.
If you still want to disconnect click the button below.',
				'optimole-wp'
			),
			'email_address_label'              => __( 'Your email address', 'optimole-wp' ),
			'steps_connect_api_title'          => __( 'Connect your account', 'optimole-wp' ),
			'register_btn'                     => __( 'Create & connect your account ', 'optimole-wp' ),
			'step_one_api_title'               => __( 'Enter your API key.', 'optimole-wp' ),
			'optml_dashboard'                  => sprintf( __( 'Get it from the %1$s Optimole Dashboard%2$s.', 'optimole-wp' ), '<a  style="white-space:nowrap; text-decoration: underline !important;" href="https://dashboard.optimole.com/" target="_blank"> ', '<span style="text-decoration:none; font-size:15px; margin-top:2px;" class="dashicons dashicons-external"></span></a>' ),
			'steps_connect_api_desc'           => sprintf( __( 'Copy the API Key you have received via email or you can get it from %1$s Optimole dashboard%2$s. If your account has multiple domains select the one you want to use. <br/>', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/" target="_blank"> ', '</a>' ),
			'api_exists'                       => __( 'I already have an API key.', 'optimole-wp' ),
			'back_to_register'                 => __( 'Register account', 'optimole-wp' ),
			'back_to_connect'                  => __( 'Go to previous step', 'optimole-wp' ),
			'error_register'                   => sprintf( __( 'Error registering account. You can try again %1$shere%2$s ', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/register" target="_blank"> ', '</a>' ),
			'invalid_email'                    => __( 'Please use a valid email address.', 'optimole-wp' ),
			'connected'                        => __( 'CONNECTED', 'optimole-wp' ),
			'connecting'                       => __( 'CONNECTING', 'optimole-wp' ),
			'not_connected'                    => __( 'NOT CONNECTED', 'optimole-wp' ),
			'usage'                            => __( 'Monthly Usage', 'optimole-wp' ),
			'quota'                            => __( 'Monthly visits quota', 'optimole-wp' ),
			'logged_in_as'                     => __( 'LOGGED IN AS', 'optimole-wp' ),
			'private_cdn_url'                  => __( 'IMAGES DOMAIN', 'optimole-wp' ),
			'existing_user'                    => __( 'Existing user?', 'optimole-wp' ),
			'notification_message_register'    => __( 'We sent you the API Key in the email. Add it below to connect to Optimole.', 'optimole-wp' ),
			'premium_support'                  => __( 'Access our Premium Support', 'optimole-wp' ),
			'account_needed_title'             => sprintf(
				__( 'In order to get access to free image optimization service you will need an API key from %s.', 'optimole-wp' ),
				' <a href="https://dashboard.optimole.com/register" target="_blank">optimole.com</a>'
			),
			'account_needed_subtitle_1'        => sprintf(
				__( 'You will get access to our %1$simage optimization service for FREE%2$s in the limit of %3$s5k%4$s %5$svisitors%6$s per month. ', 'optimole-wp' ),
				'<strong>',
				'</strong>',
				'<strong>',
				'</strong>',
				'<a href="https://docs.optimole.com/article/1134-how-optimole-counts-the-number-of-visitors" target="_blank">',
				'</a>'
			),
			'account_needed_subtitle_3'        => sprintf(
				__( 'Need help? %1$sGetting Started with Optimole%2$s', 'optimole-wp' ),
				'<a target="_blank" href="https://docs.optimole.com/article/1173-how-to-get-started-with-optimole-in-just-3-steps">',
				'</a>'
			),
			'account_needed_subtitle_2'        => sprintf(
				__(
					'Bonus, if you dont use a CDN, we got you covered, %1$swe will serve the images using CloudFront CDN%2$s from 450+ locations.',
					'optimole-wp'
				),
				'<strong>',
				'</strong>'
			),
			'account_needed_footer'            => sprintf( __( 'Trusted by more than %1$s happy users', 'optimole-wp' ), number_format_i18n( 200000, 0 ) ),
			'account_connecting_title'         => __( 'Connecting to Optimole', 'optimole-wp' ),
			'account_connecting_subtitle'      => __( 'Sit tight while we connect you to the Dashboard', 'optimole-wp' ),
			'notice_just_activated'            => ! $this->settings->is_connected() ?
					sprintf( __( '%1$sImage optimisation is currently running.%2$s <br/> Your visitors will now view the best image for their device automatically, all served from the Optimole Cloud Service on the fly. You might see for the very first image request being redirected to the original URL while we do the optimization in the background. You can relax, we\'ll take it from here.', 'optimole-wp' ), '<strong>', '</strong>' )
					: '',
			'notice_api_not_working'           => __(
				'It seems there is an issue with your WordPress configuration and the core REST API functionality is not available. This is crucial as Optimole relies on this functionality in order to work.<br/>
The root cause might be either a security plugin which blocks this feature or some faulty server configuration which constrain this WordPress feature.You can try to disable any of the security plugins that you use in order to see if the issue persists or ask the hosting company to further investigate.',
				'optimole-wp'
			),
			'notice_disabled_account'          => sprintf( __( '%3$sYour account has been disabled due to exceeding quota.%4$s All images are being redirected to the original unoptimized URL. %5$sPlease %1$supgrade%2$s to re-activate the account.', 'optimole-wp' ), '<b><a href="https://optimole.com/pricing">', '</a></b>', '<b>', '</b>', '<br>' ),
			'signup_terms'                   => sprintf( __( 'By signing up, you agree to our  %1$sTerms of Service %3$s and  %2$sPrivacy Policy %3$s.', 'optimole-wp' ), '<a href="https://optimole.com/terms/" target="_blank" >', '<a href="https://optimole.com/privacy-policy/" target="_blank">', '</a>' ),
			'dashboard_menu_item'              => __( 'Dashboard', 'optimole-wp' ),
			'settings_menu_item'               => __( 'Settings', 'optimole-wp' ),
			'help_menu_item'                   => __( 'Help', 'optimole-wp' ),
			'settings_exclusions_menu_item'    => __( 'Exclusions', 'optimole-wp' ),
			'settings_resize_menu_item'        => __( 'Resize', 'optimole-wp' ),
			'settings_compression_menu_item'   => __( 'Compression', 'optimole-wp' ),
			'advanced_settings_menu_item'      => __( 'Advanced', 'optimole-wp' ),
			'general_settings_menu_item'       => __( 'General', 'optimole-wp' ),
			'lazyload_settings_menu_item'      => __( 'Lazyload', 'optimole-wp' ),
			'watermarks_menu_item'             => __( 'Watermark', 'optimole-wp' ),
			'conflicts_menu_item'              => __( 'Possible Issues', 'optimole-wp' ),
			'conflicts'                        => [
				'title'              => __( 'We might have some possible conflicts with the plugins that you use. In order to benefit from Optimole\'s full potential you will need to address this issues.', 'optimole-wp' ),
				'message'            => __( 'Details', 'optimole-wp' ),
				'conflict_close'     => __( 'I\'ve done this.', 'optimole-wp' ),
				'no_conflicts_found' => __( 'No conflicts found. We are all peachy now. 🍑', 'optimole-wp' ),
			],
			'upgrade'                          => [
				'title'      => __( 'Upgrade', 'optimole-wp' ),
				'title_long' => __( 'Upgrade to Optimole Pro', 'optimole-wp' ),
				'reason_1'   => __( 'Priority & Live Chat support', 'optimole-wp' ),
				'reason_2'   => __( 'Extend visits limit', 'optimole-wp' ),
				'reason_3'   => __( 'Custom domain', 'optimole-wp' ),
				'reason_4'   => __( 'Site audit', 'optimole-wp' ),
				'cta'        => __( 'View plans', 'optimole-wp' ),
			],
			'neve'                             => [
				'is_active' => defined( 'NEVE_VERSION' ) ? 'yes' : 'no',
				'byline'    => __( 'Fast, perfomance built-in WordPress theme.', 'optimole-wp' ),
				'reason_1'  => __( 'Lightweight, 25kB in page-weight.', 'optimole-wp' ),
				'reason_2'  => __( '100+ Starter Sites available.', 'optimole-wp' ),
				'reason_3'  => __( 'AMP/Mobile ready.', 'optimole-wp' ),
				'reason_4'  => __( 'Lots of customizations options.', 'optimole-wp' ),
				'reason_5'  => __( 'Fully compatible with Optimole.', 'optimole-wp' ),
			],
			'metrics'                          => [
				'metricsTitle1'    => __( 'Images optimized', 'optimole-wp' ),
				'metricsSubtitle1' => __( 'Since plugin activation', 'optimole-wp' ),
				'metricsTitle2'    => __( 'Saved file size', 'optimole-wp' ),
				'metricsSubtitle2' => __( 'For the latest 10 images', 'optimole-wp' ),
				'metricsTitle3'    => __( 'Average compression', 'optimole-wp' ),
				'metricsSubtitle3' => __( 'During last month', 'optimole-wp' ),
				'metricsTitle4'    => __( 'Traffic', 'optimole-wp' ),
				'metricsSubtitle4' => __( 'During last month', 'optimole-wp' ),
			],
			'options_strings'                  => [
				'best_format_title'                 => __( 'Automatic Best Image Format Selection', 'optimole-wp' ),
				'best_format_desc'                  => sprintf( __( 'When enabled, Optimole picks the ideal format for your images, balancing quality and speed. It tests different formats, like AVIF and WebP, ensuring images look good and load quickly. %1$sLearn more%2$s.', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1942-best-format">', '</a>' ),
				'add_filter'                        => __( 'Add filter', 'optimole-wp' ),
				'add_site'                          => __( 'Add site', 'optimole-wp' ),
				'admin_bar_desc'                    => __( 'Show in the WordPress admin bar the available quota from Optimole service.', 'optimole-wp' ),
				'auto_q_title'                      => __( 'Auto', 'optimole-wp' ),
				'cache_desc'                        => sprintf( __( 'Clears all Optimole’s cached resources (images, JS, CSS). Useful if you made changes to your images and don\'t see those applying on your site. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1941-clear-cached-resources">', '</a>' ),
				'cache_title'                       => __( 'Clear Cached Resources', 'optimole-wp' ),
				'clear_cache_notice'                => __( 'Clearing cached resources will re-optimize the images and might affect the site performance for a few minutes.', 'optimole-wp' ),
				'image_size_notice'                 => sprintf( __( 'Use this option if you notice images are not cropped correctly after using Optimole. Add the affected image sizes here to automatically adjust and correct their cropping for optimal display. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1947-add-new-image-crop-size">', '</a>' ),
				'clear_cache_images'                => __( 'Clear cached images', 'optimole-wp' ),
				'clear_cache_assets'                => __( 'Clear cached CSS & JS', 'optimole-wp' ),
				'connect_step_0'                    => __( 'Connecting your site to the Optimole service.', 'optimole-wp' ),
				'connect_step_1'                    => __( 'Checking for possible conflicts.', 'optimole-wp' ),
				'connect_step_2'                    => __( 'Inspecting the images from your site.', 'optimole-wp' ),
				'connect_step_3'                    => __( 'All done, Optimole is currently optimizing your site.', 'optimole-wp' ),
				'disabled'                          => __( 'Disabled', 'optimole-wp' ),
				'enable_avif_title'                 => __( 'AVIF Image Support', 'optimole-wp' ),
				'enable_avif_desc'                  => sprintf( __( 'Enable this to automatically convert images to the AVIF format on browsers that support it. This format provides quality images with reduced file sizes, and faster webpage loading. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1943-avif-conversion">', '</a>' ),
				'enable_bg_lazyload_desc'           => sprintf( __( 'Enable this to lazy-load images set as CSS backgrounds. If Optimole misses any, you can directly target specific CSS selectors to ensure all background images are optimized. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1169-how-to-enable-the-background-lazyload-feature-for-certain-background-images">', '</a>' ),
				'enable_bg_lazyload_title'          => __( 'CSS Background Lazy Load', 'optimole-wp' ),
				'enable_video_lazyload_desc'        => sprintf( __( 'By default, lazy loading does not work for embedded videos and iframes. Enable this option to activate the lazy-load on these elements. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1952-lazy-loading-for-embedded-videos-and-iframes">', '</a>' ),
				'enable_video_lazyload_title'       => __( 'Lazy Loading for Embedded Videos and Iframes', 'optimole-wp' ),
				'enable_noscript_desc'              => sprintf( __( 'Enables fallback images for browsers that can\'t handle JavaScript-based lazy loading or related features. Disabling it may resolve conflicts with other plugins or configurations and decrease HTML page size. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1959-noscript-tag">', '</a>' ),
				'enable_noscript_title'             => __( 'Noscript Tag', 'optimole-wp' ),
				'enable_gif_replace_title'          => __( 'GIF to Video Conversion', 'optimole-wp' ),
				'enable_report_title'               => __( 'Enable Error Diagnosis Tool', 'optimole-wp' ),
				'enable_report_desc'                => sprintf( __( 'Activates the Optimole debugging tool in the admin bar for reports on Optimole-related website issues using the built-in diagnostic feature. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1390-how-does-the-error-diagnosis-tool-work">', '</a>' ),
				'enable_offload_media_title'        => __( 'Store Your Images in Optimole Cloud', 'optimole-wp' ),
				'enable_offload_media_desc'         => sprintf( __( 'Free up space on your server by transferring your images to Optimole Cloud; you can transfer them back anytime. Once moved, the images will still be visible in the Media Library and can be used as before. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1967-store-your-images-in-optimole-cloud">', '</a>' ),
				'enable_cloud_images_title'         => __( 'Unified Image Access', 'optimole-wp' ),
				'enable_cloud_images_desc'          => sprintf( __( 'Enable this setting to access all your Optimole images, including those from other websites connected to your Optimole account, directly on this site. They will be available for browsing in the Cloud Library tab. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1323-cloud-library-browsing">', '</a>' ),
				'enable_image_replace'              => __( 'Enable Optimole Image Handling', 'optimole-wp' ),
				'enable_lazyload_placeholder_desc'  => sprintf( __( 'Enable this to use a generic transparent placeholder instead of the blurry images during lazy loading. Enhance the visual experience by selecting a custom color for the placeholder. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1192-lazy-load-generic-placeholder">', '</a>' ),
				'enable_lazyload_placeholder_title' => __( 'Lazy Load with Generic Placeholder', 'optimole-wp' ),
				'enable_network_opt_desc'           => sprintf( __( 'When enabled, Optimole will automatically reduce the image quality when it detects a slower network, making your images load faster on low-speed internet connections. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1945-network-based-optimizations">', '</a>' ),
				'enable_network_opt_title'          => __( 'Network-based Optimizations', 'optimole-wp' ),
				'enable_resize_smart_desc'          => sprintf( __( 'When enabled, Optimole automatically detects the most interesting or important part of your images. When pictures are resized or cropped, this feature ensures the focus stays on the most interesting part of the picture. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1871-how-to-use-the-smart-cropping-in-optimole">', '</a>' ),
				'enable_resize_smart_title'         => __( 'Smart Cropping', 'optimole-wp' ),
				'enable_retina_desc'                => sprintf( __( 'Enable this feature to optimize your images for Retina displays. Retina-ready images are optimized to look sharp on screens with higher pixel density, offering viewers enhanced visual quality. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1391-what-is-a-retina-image">', '</a>' ),
				'enable_retina_title'               => __( 'Retina Quality', 'optimole-wp' ),
				'enable_limit_dimensions_desc'      => sprintf( __( 'Define the max width or height limits for images on your website. Larger images will be automatically adjusted to fit within these parameters while preserving their original aspect ratio. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1946-limit-image-sizes">', '</a>' ),
				'enable_limit_dimensions_title'     => __( 'Limit Image Sizes', 'optimole-wp' ),
				'enable_limit_dimensions_notice'    => __( 'When you enable this feature to define a max width or height for image resizing, please note that DPR (retina) images will be disabled. This is done to ensure consistency in image dimensions across your website. Although this may result in slightly lower image quality for high-resolution displays, it will help maintain uniform image sizes, improving your website\'s overall layout and potentially boosting performance. ', 'optimole-wp' ),
				'enable_badge_title'                => __( 'Enable Optimole Badge', 'optimole-wp' ),
				'enable_badge_description'          => sprintf( __( 'Get 20.000 more visits for free by enabling the Optimole badge on your websites. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1940-optimole-badge">', '</a>' ),
				'image_sizes_title'                 => __( 'Your cropped image sizes', 'optimole-wp' ),
				'enabled'                           => __( 'Enabled', 'optimole-wp' ),
				'exclude_class_desc'                => sprintf( __( '%1$sImage tag%2$s contains class', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_ext_desc'                  => sprintf( __( '%1$sImage extension%2$s is', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_filename_desc'             => sprintf( __( '%1$sImage filename%2$s contains', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_desc_optimize'             => sprintf( __( 'Here you can define exceptions, in case you don\'t want some images to be optimised. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1191-exclude-from-optimizing-or-lazy-loading">', '</a>' ),
				'exclude_title_lazyload'            => __( 'Don\'t lazy-load images if', 'optimole-wp' ),
				'exclude_desc_lazyload'             => sprintf( __( 'Define exceptions, in case you don\'t want the lazy-load to be active on certain images.  %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1191-exclude-from-optimizing-or-lazy-loading">', '</a>' ),
				'exclude_title_optimize'            => __( 'Don\'t optimize images if', 'optimole-wp' ),
				'exclude_url_desc'                  => sprintf( __( '%1$sPage url%2$s contains', 'optimole-wp' ), '<strong>', '</strong>' ),
				'name'                              => sprintf( __( '%1$sName: %2$s', 'optimole-wp' ), '<strong>', '</strong>' ),
				'cropped'                           => __( 'cropped', 'optimole-wp' ),
				'exclude_url_match_desc'            => sprintf( __( '%1$sPage url%2$s matches', 'optimole-wp' ), '<strong>', '</strong>' ),
				'exclude_first'                     => __( 'Exclude first', 'optimole-wp' ),
				'images'                            => __( 'images', 'optimole-wp' ),
				'exclude_first_images_title'        => __( 'Bypass Lazy Load for First Images', 'optimole-wp' ),
				'exclude_first_images_desc'         => sprintf( __( 'Indicate how many images at the top of each page should bypass lazy loading, ensuring they’re instantly visible. Enter 0 to not exclude any images from the lazy loading process. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1948-bypass-lazy-load-for-first-images">', '</a>' ),
				'filter_class'                      => __( 'Image class', 'optimole-wp' ),
				'filter_ext'                        => __( 'Image extension', 'optimole-wp' ),
				'filter_filename'                   => __( 'Image filename', 'optimole-wp' ),
				'filter_operator_contains'          => __( 'contains', 'optimole-wp' ),
				'filter_operator_matches'           => __( 'matches', 'optimole-wp' ),
				'filter_operator_is'                => __( 'is', 'optimole-wp' ),
				'filter_url'                        => __( 'Page URL', 'optimole-wp' ),
				'filter_helper'                     => __( 'For homepage use `home` keyword.', 'optimole-wp' ),
				'gif_replacer_desc'                 => sprintf( __( 'Enable this to automatically convert GIF images to Video files (MP4 and WebM). %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1171-gif-to-video-conversion">', '</a>' ),
				'height_field'                      => __( 'Height', 'optimole-wp' ),
				'add_image_size_button'             => __( 'Add size', 'optimole-wp' ),
				'add_image_size_desc'               => __( 'Add New Image Crop Size', 'optimole-wp' ),
				'here'                              => __( ' here.', 'optimole-wp' ),
				'hide'                              => __( 'Hide', 'optimole-wp' ),
				'high_q_title'                      => __( 'High', 'optimole-wp' ),
				'image_1_label'                     => __( 'Original', 'optimole-wp' ),
				'image_2_label'                     => __( 'Optimized', 'optimole-wp' ),
				'lazyload_desc'                     => sprintf( __( 'Scales large images to fit their display space, ensuring your website runs fast. With lazy loading, images appear when needed while scrolling, making navigation smoother. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1939-scale-images-lazy-load">', '</a>' ),
				'filter_length_error'               => __( 'The filter should be at least 3 characters long.', 'optimole-wp' ),
				'scale_desc'                        => sprintf( __( 'Enable this to allow Optimole to resize lazy-loaded images for optimal display on your screen. Keep it disabled to retain the original image size, though it may result in slower page loads. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1950-image-scaling">', '</a>' ),
				'low_q_title'                       => __( 'Low', 'optimole-wp' ),
				'medium_q_title'                    => __( 'Medium', 'optimole-wp' ),
				'no_images_found'                   => __( 'You dont have any images in your Media Library. Add one and check how the Optimole will perform.', 'optimole-wp' ),
				'native_desc'                       => sprintf( __( 'Enable to use the browser\'s built-in lazy loading feature. Enabling this will disable the auto scale feature, meaning images will not be automatically resized to fit the screen dimensions. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1949-browser-native-lazy-load">', '</a>' ),
				'option_saved'                      => __( 'Option saved.', 'optimole-wp' ),
				'ml_quality_desc'                   => sprintf( __( 'Optimole ML algorithms will predict the optimal image quality to get the smallest possible size with minimum perceived quality losses. When disabled, you can control the quality manually. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1016-what-is-the-difference-between-the-auto-high-medium-low-compression-levels">', '</a>' ),
				'quality_desc'                      => __( 'Lower image quality might boost your loading speed by lowering the size. However, the low image quality may negatively impact the visual appearance of the images. Try experimenting with the setting, then click the View sample image link to see what option works best for you.', 'optimole-wp' ),
				'quality_selected_value'            => __( 'Selected value', 'optimole-wp' ),
				'quality_slider_desc'               => __( 'See one sample image which will help you choose the right quality of the compression.', 'optimole-wp' ),
				'quality_title'                     => __( 'Auto Quality Powered by ML(Machine Learning)', 'optimole-wp' ),
				'strip_meta_title'                  => __( 'Strip Image Metadata', 'optimole-wp' ),
				'strip_meta_desc'                   => sprintf( __( 'Removes extra information from images, including EXIF and IPTC data (like camera settings and copyright info). This makes the pictures lighter and helps your website load faster. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1944-strip-image-metadata">', '</a>' ),
				'replacer_desc'                     => sprintf( __( 'When enabled, Optimole will manage, optimize, and serve all the images on your website. If disabled, optimization, lazy loading, and other features will no longer be available. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1218-how-to-turn-off-image-replacement-in-optimoles-wordpress-plugin">', '</a>' ),
				'sample_image_loading'              => __( 'Loading a sample image. ', 'optimole-wp' ),
				'save_changes'                      => __( 'Save changes', 'optimole-wp' ),
				'show'                              => __( 'Show', 'optimole-wp' ),
				'selected_sites_title'              => __( 'CURRENTLY SHOWING IMAGES FROM', 'optimole-wp' ),
				'selected_sites_desc'               => __( 'Site: ', 'optimole-wp' ),
				'selected_all_sites_desc'           => __( 'Currently viewing images from all sites ', 'optimole-wp' ),
				'select_all_sites_desc'             => __( 'View images from all sites ', 'optimole-wp' ),
				'select_site'                       => __( 'Select a website', 'optimole-wp' ),
				'cloud_site_title'                  => __( 'Show images only from these sites: ', 'optimole-wp' ),
				'cloud_site_desc'                   => __( 'Browse images only from the specified websites. Otherwise, images from all websites will appear in the library.', 'optimole-wp' ),
				'toggle_ab_item'                    => __( 'Admin bar status', 'optimole-wp' ),
				'toggle_lazyload'                   => __( 'Scale Images & Lazy loading', 'optimole-wp' ),
				'toggle_scale'                      => __( 'Image Scaling', 'optimole-wp' ),
				'toggle_native'                     => __( 'Browser Native Lazy Load', 'optimole-wp' ),
				'on_toggle'                         => __( 'On', 'optimole-wp' ),
				'off_toggle'                        => __( 'Off', 'optimole-wp' ),
				'view_sample_image'                 => __( 'View sample image', 'optimole-wp' ),
				'watch_placeholder_lazyload'        => __( 'Add each CSS selector on a new line or separated by comma(,)', 'optimole-wp' ),
				'watch_desc_lazyload'               => sprintf( __( 'Enter CSS selectors for any background images not covered by the default lazy loading. This ensures those images also benefit from the optimized loading process. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1169-how-to-enable-the-background-lazyload-feature-for-certain-background-images">', '</a>' ),
				'watch_title_lazyload'              => __( 'Extend CSS Background Lazy Loading', 'optimole-wp' ),
				'width_field'                       => __( 'Width', 'optimole-wp' ),
				'crop'                              => __( 'crop', 'optimole-wp' ),
				'toggle_cdn'                        => __( 'Serve CSS & JS Through Optimole', 'optimole-wp' ),
				'cdn_desc'                          => sprintf( __( 'When enabled, Optimole will optimize your CSS and JS files and, if they contain images, the images as well, then deliver them via the CDN for faster webpage loading. %1$sLearn more%2$s', 'optimole-wp' ), '<a class="inline-block text-purple-gray underline" target=”_blank” href="https://docs.optimole.com/article/1966-serve-css-js-through-optimole">', '</a>' ),
				'enable_css_minify_title'           => __( 'Minify CSS files', 'optimole-wp' ),
				'css_minify_desc'                   => __( 'Once Optimole will serve your CSS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
				'enable_js_minify_title'            => __( 'Minify JS files', 'optimole-wp' ),
				'js_minify_desc'                    => __( 'Once Optimole will serve your JS files, it will also minify the files and serve them via CDN.', 'optimole-wp' ),
				'sync_title'                        => __( 'Offload Existing Images', 'optimole-wp' ),
				'rollback_title'                    => __( 'Restore Offloaded Images', 'optimole-wp' ),
				'sync_desc'                         => __( 'Right now all the new images uploaded to your site are moved automatically to Optimole Cloud. In order to offload the existing ones, please click Sync images and wait for the process to finish. You can rollback anytime.', 'optimole-wp' ),
				'rollback_desc'                     => __( 'Pull all the offloaded images to Optimole Cloud back to your server.', 'optimole-wp' ),
				'sync_media'                        => __( 'Sync images', 'optimole-wp' ),
				'rollback_media'                    => __( 'Rollback images', 'optimole-wp' ),
				'sync_media_progress'               => __( 'Moving your images to Optimole...', 'optimole-wp' ),
				'estimated_time'                    => __( 'Estimated time remaining', 'optimole-wp' ),
				'calculating_estimated_time'        => __( 'We are currently calculating the estimated time for this job...', 'optimole-wp' ),
				'images_processing'                 => __( 'We are currently processing your images in the background. Leaving the page won\'t stop the process.', 'optimole-wp' ),
				'active_optimize_exclusions'        => __( 'Active Optimizing Exclusions', 'optimole-wp' ),
				'active_lazyload_exclusions'        => __( 'Active Lazy-loading Exclusions', 'optimole-wp' ),
				'minutes'                           => __( 'minutes', 'optimole-wp' ),
				'stop'                              => __( 'Stop', 'optimole-wp' ),
				'show_logs'                         => __( 'Show Logs', 'optimole-wp' ),
				'hide_logs'                         => __( 'Hide Logs', 'optimole-wp' ),
				'view_logs'                         => __( 'View Full Logs', 'optimole-wp' ),
				'rollback_media_progress'           => __( 'Moving images into your media library...', 'optimole-wp' ),
				'rollback_media_error'              => __( 'An unexpected error occured while pulling the offloaded back to your site', 'optimole-wp' ),
				'rollback_media_error_desc'         => __( 'You can try again to pull back the rest of the images.', 'optimole-wp' ),
				'remove_notice'                     => __( 'Remove notice', 'optimole-wp' ),
				'sync_media_error'                  => __( 'An unexpected error occured while offloading all existing images from your site to Optimole ', 'optimole-wp' ),
				'sync_media_link'                   => __( 'The selected images have been offloaded to our servers, you can check them', 'optimole-wp' ),
				'rollback_media_link'               => __( 'The selected images have been restored to your server, you can check them', 'optimole-wp' ),
				'sync_media_error_desc'             => __( 'You can try again to offload the rest of the images to Optimole.', 'optimole-wp' ),
				'offload_disable_warning_title'     => __( 'Important! Please read carefully', 'optimole-wp' ),
				'offload_disable_warning_desc'      => __( 'If you turn off this option, you will not be able to see the images in the Media Library without restoring the images first. Do you want to restore the images to your site upon turning off the option?', 'optimole-wp' ),
				'offload_enable_info_desc'          => sprintf( __( 'You are not required to use the offload functionality for the plugin to work, use it if you want to save on hosting space. %1$s More details%2$s', 'optimole-wp' ), '<a style="white-space: nowrap;" target=”_blank” href="https://docs.optimole.com/article/1323-cloud-library-browsing">', '<span style="font-size:15px; margin-top:2px;" class="dashicons dashicons-external"></span></a>' ),
				'offload_conflicts_part_1'          => __( 'We have detected the following plugins that conflict with the offload features: ', 'optimole-wp' ),
				'offload_conflicts_part_2'          => __( 'Please disable those plugins temporarily in order for Optimole to rollback the images to your site.', 'optimole-wp' ),
				'offloading_success'                => sprintf( __( '%s Your images are now stored in Optimole Cloud.', 'optimole-wp' ), '<strong>' . __( 'Transfer Complete.', 'optimole-wp' ) . '</strong>' ),
				'rollback_success'                  => sprintf( __( '%s Your images have been restored to your website.', 'optimole-wp' ), '<strong>' . __( 'Transfer Complete.', 'optimole-wp' ) . '</strong>' ),
				'offloading_radio_legend'           => __( 'Where your images are stored', 'optimole-wp' ),
				'offload_radio_option_rollback_title' => __( 'Optimole Cloud  and your website', 'optimole-wp' ),
				'offload_radio_option_rollback_desc'  => __( 'Images are stored in both the local WordPress media library and Optimole Cloud.', 'optimole-wp' ),
				'offload_radio_option_offload_title'  => __( 'Optimole Cloud only', 'optimole-wp' ),
				'offload_radio_option_offload_desc'   => __( 'Images are stored only in Optimole Cloud, allowing you to save space on your server. When enabled, any new images you upload in the Media Library will be automatically transferred to Optimole Cloud.', 'optimole-wp' ),
				'offload_limit_reached'             => sprintf( __( 'You have reached the maximum offloading limit of %s images. To increase the offload limit and for more information, contact our support.', 'optimole-wp' ), '<strong>#offload_limit#</strong>' ),
				'select'                            => __( 'Please select one ...', 'optimole-wp' ),
				'yes'                               => __( 'Restore images after disabling', 'optimole-wp' ),
				'no'                                => __( 'Do not restore images after disabling', 'optimole-wp' ),
				'lazyload_placeholder_color'        => __( 'Placeholder Color', 'optimole-wp' ),
				'clear'                             => __( 'Clear', 'optimole-wp' ),
				'settings_saved'                    => __( 'Settings saved', 'optimole-wp' ),
				'settings_saved_error'              => __( 'Error saving settings. Please reload the page and try again.', 'optimole-wp' ),
				'cache_cleared'                     => __( 'Cache cleared', 'optimole-wp' ),
				'cache_cleared_error'               => __( 'Error clearing cache. Please reload the page and try again.', 'optimole-wp' ),
				'offloading_start_title'            => __( 'Transfer your images to Optimole', 'optimole-wp' ),
				'offloading_start_description'      => __( 'This process will transfer and store your images in Optimole Cloud and may take a while, depending on the number of images.', 'optimole-wp' ),
				'offloading_start_action'           => __( 'Transfer to Optimole Cloud', 'optimole-wp' ),
				'offloading_stop_title'             => __( 'Are you sure?', 'optimole-wp' ),
				'offloading_stop_description'       => __( 'This will halt the ongoing process. To retrieve images transferred from the Optimole Cloud, use the Rollback option.', 'optimole-wp' ),
				'offloading_stop_action'            => __( 'Cancel the transfer to Optimole', 'optimole-wp' ),
				'rollback_start_title'              => __( 'Transfer back all images to your site', 'optimole-wp' ),
				'rollback_start_description'        => __( 'This process will transfer back all images from Optimole to your website and may take a while, depending on the number of images.', 'optimole-wp' ),
				'rollback_start_action'             => __( 'Transfer back from Optimole', 'optimole-wp' ),
				'rollback_stop_title'               => __( 'Are you sure?', 'optimole-wp' ),
				'rollback_stop_description'         => __( 'Canceling will halt the ongoing process, and any remaining images will stay in the Optimole Cloud. To transfer images to the Optimole Cloud, use the Offloading option.', 'optimole-wp' ),
				'rollback_stop_action'              => __( 'Cancel the transfer from Optimole', 'optimole-wp' ),
			],
			'help'                             => [
				'section_one_title'           => __( 'Help and Support', 'optimole-wp' ),
				'section_two_title'           => __( 'Documentation', 'optimole-wp' ),
				'section_two_sub'             => __( 'Docs Page', 'optimole-wp' ),
				'get_support_title'           => __( 'Get Support', 'optimole-wp' ),
				'get_support_desc'            => __( 'Need help or got a question? Submit a ticket and we\'ll get back to you.', 'optimole-wp' ),
				'get_support_cta'             => __( 'Contact Support', 'optimole-wp' ),
				'feat_request_title'          => __( 'Have a feature request?', 'optimole-wp' ),
				'feat_request_desc'           => __( 'Help us improve Optimole by sharing feedback and ideas for new features.', 'optimole-wp' ),
				'feat_request_cta'            => __( 'Submit a Feature Request', 'optimole-wp' ),
				'feedback_title'              => __( 'Changelog', 'optimole-wp' ),
				'feedback_desc'               => __( 'Check our changelog to see latest fixes and features implemented.', 'optimole-wp' ),
				'feedback_cta'                => __( 'View Changelog', 'optimole-wp' ),
				'account_title'               => __( 'Account', 'optimole-wp' ),
				'account_item_one'            => __( 'How Optimole counts the visitors?', 'optimole-wp' ),
				'account_item_two'            => __( 'What happens if I exceed plan limits?', 'optimole-wp' ),
				'account_item_three'          => __( 'Visits based plan', 'optimole-wp' ),
				'image_processing_title'      => __( 'Image Processing', 'optimole-wp' ),
				'image_processing_item_one'   => __( 'Getting Started With Optimole', 'optimole-wp' ),
				'image_processing_item_two'   => __( 'How Optimole can serve WebP images', 'optimole-wp' ),
				'image_processing_item_three' => __( 'Adding Watermarks to your images', 'optimole-wp' ),
				'api_title'                   => __( 'API', 'optimole-wp' ),
				'api_item_one'                => __( 'Cloud Library Browsing', 'optimole-wp' ),
				'api_item_two'                => __( 'Exclude from Optimizing or Lazy Loading', 'optimole-wp' ),
				'api_item_three'              => __( 'Custom Integration', 'optimole-wp' ),
			],
			'watermarks'                       => [
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
			'latest_images'                    => [
				'image'                 => __( 'Image', 'optimole-wp' ),
				'no_images_found'       => sprintf( __( 'We are currently optimizing your images. Meanwhile you can visit your %1$shomepage%2$s and check how our plugin performs. ', 'optimole-wp' ), '<a href="' . esc_url( home_url() ) . '" target="_blank" >', '</a>' ),
				'compression'           => __( 'Optimization', 'optimole-wp' ),
				'loading_latest_images' => __( 'Loading your optimized images...', 'optimole-wp' ),
				'last'                  => __( 'Last', 'optimole-wp' ),
				'saved'                 => __( 'Saved', 'optimole-wp' ),
				'smaller'               => __( 'smaller', 'optimole-wp' ),
				'optimized_images'      => __( 'optimized images', 'optimole-wp' ),
				'same_size'             => __( '🙉 We couldn\'t do better, this image is already optimized at maximum. ', 'optimole-wp' ),
				'small_optimization'    => __( '😬 Not that much, just <strong>{ratio}</strong> smaller.', 'optimole-wp' ),
				'medium_optimization'   => __( '🤓 We are on the right track, <strong>{ratio}</strong> squeezed.', 'optimole-wp' ),
				'big_optimization'      => __( '❤️❤️❤️ Our moles just nailed it, this one is <strong>{ratio}</strong> smaller.  ', 'optimole-wp' ),
			],
			'csat'                             => [
				'title'                => __( 'Your opinion matters', 'optimole-wp' ),
				'close'                => __( 'Close', 'optimole-wp' ),
				'heading_one'          => __( 'How easy did you find to get started using Optimole, on a scale of 1 to 5?', 'optimole-wp' ),
				'heading_two'          => __( 'Any specific feedback you would like to add?', 'optimole-wp' ),
				'heading_three'        => __( 'Thank you!', 'optimole-wp' ),
				'low'                  => __( 'Very Poor', 'optimole-wp' ),
				'high'                 => __( 'Excellent', 'optimole-wp' ),
				'feedback_placeholder' => __( 'Add your feedback here (optional)', 'optimole-wp' ),
				'skip'                 => __( 'Skip', 'optimole-wp' ),
				'submit'               => __( 'Submit', 'optimole-wp' ),
				'thank_you'            => __( 'Your input is highly appreciated and helps us shape a better experience in Optimole.', 'optimole-wp' ),
			],
			'cron_error'                                                => sprintf( __( 'It seems that you have the %1$s constant defined as %2$s. The offloading process uses cron events to offload the images in the background. Please remove the constant from your wp-config.php file in order for the offloading process to work.', 'optimole-wp' ), '<code>DISABLE_WP_CRON</code>', '<code>true</code>' ),
			'cancel'                                                    => __( 'Cancel', 'optimole-wp' ),
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
