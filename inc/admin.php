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

		add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_traffic_node' ), 9999 );
		add_filter( 'wp_resource_hints', array( $this, 'add_dns_prefetch' ), 10, 2 );
		add_action( 'optml_daily_sync', array( $this, 'daily_sync' ) );
		add_action( 'wp_head', array( $this, 'generator' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		if ( ! is_admin() && $this->settings->is_connected() ) {
			if ( ! wp_next_scheduled( 'optml_daily_sync' ) ) {
				wp_schedule_event( time() + 10, 'daily', 'optml_daily_sync', array() );
			}
		}
	}

	/**
	 * Maybe redirect to dashboard page.
	 */
	public function maybe_redirect() {
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

		if ( ! current_theme_supports( 'custom-logo' ) ) {
			return;
		}
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$image          = wp_get_attachment_image_src( $custom_logo_id, 'full' );
		if ( empty( $image ) ) {
			return;
		}
		echo '<link rel="preload" href="' . esc_url( $image[0] ) . '" as="image">';
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
		if ( $data === false ) {
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
		if ( 'dns-prefetch' !== $relation_type ) {
			return $hints;
		}
		if ( ! $this->settings->is_connected() ) {
			return $hints;
		}
		if ( ! $this->settings->is_enabled() ) {
			return $hints;
		}
		$hints[] = sprintf( '//%s', $this->settings->get_cdn_url() );

		return $hints;
	}

	/**
	 * Add the dashboard page.
	 */
	public function add_dashboard_page() {
		add_media_page( 'Optimole', 'Optimole', 'manage_options', 'optimole', array( $this, 'render_dashboard_page' ) );
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		?>
		<div id="optimole-app">
			<app></app>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts needed for admin functionality.
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
	 * @return array
	 */
	private function localize_dashboard_app() {
		$api_key      = $this->settings->get( 'api_key' );
		$service_data = $this->settings->get( 'service_data' );
		$user         = get_userdata( get_current_user_id() );
		$args         = array(
			'strings'           => $this->get_dashboard_strings(),
			'assets_url'        => OPTML_URL . 'assets/',
			'connection_status' => empty( $service_data ) ? 'no' : 'yes',
			'api_key'           => $api_key,
			'root'              => rest_url( OPTML_NAMESPACE . '/v1' ),
			'nonce'             => wp_create_nonce( 'wp_rest' ),
			'user_data'         => $service_data,
			'current_user'      => array(
				'email' => $user->user_email,
			),
			'site_settings'     => $this->settings->get_site_settings(),
			'home_url'          => home_url(),
		);

		return $args;
	}

	/**
	 * Get all dashboard strings.
	 *
	 * @return array
	 */
	private function get_dashboard_strings() {
		return array(
			'optimole'                      => 'Optimole',
			'version'                       => OPTML_VERSION,
			'terms_menu'                    => __( 'Terms', 'optimole-wp' ),
			'privacy_menu'                  => __( 'Privacy', 'optimole-wp' ),
			'testdrive_menu'                => __( 'Test Optimole', 'optimole-wp' ),
			'service_details'               => __( 'Image optimization service', 'optimole-wp' ),
			'connect_btn'                   => __( 'Connect to OptiMole Service', 'optimole-wp' ),
			'disconnect_btn'                => __( 'Disconnect', 'optimole-wp' ),
			'api_key_placeholder'           => __( 'API Key', 'optimole-wp' ),
			'account_needed_heading'        => __( 'Sign-up for API key', 'optimole-wp' ),
			'invalid_key'                   => __( 'Invalid API Key', 'optimole-wp' ),
			'status'                        => __( 'Status', 'optimole-wp' ),
			'email_address_label'           => __( 'Your email address', 'optimole-wp' ),
			'register_btn'                  => __( 'Register & Email API key', 'optimole-wp' ),
			'step_one_api_title'            => __( 'Enter your API key.', 'optimole-wp' ),
			'step_one_api_desc'             => sprintf( __( 'Copy the API key you have received via email or you can get it from %1$s Optimole dashboard%2$s. <br/>', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/" target="_blank"> ', '</a>' ),
			'step_two_api_title'            => __( 'Connect to Optimole.', 'optimole-wp' ),
			'step_two_api_desc'             => __( ' Fill in the upper API key field and connect to Optimole service.', 'optimole-wp' ),
			'api_exists'                    => __( 'I already have an API key.', 'optimole-wp' ),
			'back_to_register'              => __( 'Register account', 'optimole-wp' ),
			'back_to_connect'               => __( 'Connect account', 'optimole-wp' ),
			'error_register'                => sprintf( __( 'Error registering account. You can try again %1$shere%2$s ', 'optimole-wp' ), '<a href="https://dashboard.optimole.com/register" target="_blank"> ', '</a>' ),
			'connected'                     => __( 'Connected', 'optimole-wp' ),
			'not_connected'                 => __( 'Not connected', 'optimole-wp' ),
			'usage'                         => __( 'Monthly Usage', 'optimole-wp' ),
			'quota'                         => __( 'Monthly Quota', 'optimole-wp' ),
			'logged_in_as'                  => __( 'Logged in as', 'optimole-wp' ),
			'private_cdn_url'               => __( 'Private CDN url', 'optimole-wp' ),
			'options'                       => __( 'Options', 'optimole-wp' ),
			'notification_message_register' => __( 'We have sent you an email with the API key. Please copy and paste the key in the field below.', 'optimole-wp' ),
			'account_needed_title'          => sprintf(
				__( 'In order to get access to free image optimization service you will need an API key from %s.', 'optimole-wp' ),
				' <a href="https://dashboard.optimole.com/register" target="_blank">optimole.com</a>'
			),
			'account_needed_subtitle_1'     => sprintf(
				__( 'You will get access to our image optimization service for free in the limit of 1GB traffic per month. ', 'optimole-wp' )
			),
			'account_needed_subtitle_2'     => sprintf(
				__( 'Bonus, if you dont use a CDN, we got you covered, we will serve the images using our default CDN.', 'optimole-wp' )
			),
			'dashboard_menu_item'           => __( 'Dashboard', 'optimole-wp' ),
			'settings_menu_item'            => __( 'Settings', 'optimole-wp' ),
			'options_strings'               => array(
				'toggle_ab_item'       => __( 'Admin bar status', 'optimole-wp' ),
				'enable_image_replace' => __( 'Enable image replacement', 'optimole-wp' ),
				'show'                 => __( 'Show', 'optimole-wp' ),
				'hide'                 => __( 'Hide', 'optimole-wp' ),
				'high_q_title'         => __( 'High', 'optimole-wp' ),
				'medium_q_title'       => __( 'Medium', 'optimole-wp' ),
				'size_title'           => __( 'Resize large images.', 'optimole-wp' ),
				'size_desc'            => __( 'We will resize all images with sizes greater than this values.', 'optimole-wp' ),
				'width_field'          => __( 'Width', 'optimole-wp' ),
				'height_field'         => __( 'Height', 'optimole-wp' ),
				'low_q_title'          => __( 'Low', 'optimole-wp' ),
				'auto_q_title'         => __( 'Auto', 'optimole-wp' ),
				'quality_title'        => __( 'Compression quality', 'optimole-wp' ),
				'quality_desc'         => __( 'Select how much would like to compress the images.', 'optimole-wp' ),
				'enabled'              => __( 'Enabled', 'optimole-wp' ),
				'option_saved'         => __( 'Option saved.', 'optimole-wp' ),
				'disabled'             => __( 'Disabled', 'optimole-wp' ),
				'image_1_label'        => __( 'Original', 'optimole-wp' ),
				'image_2_label'        => __( 'Optimized', 'optimole-wp' ),
				'save_changes'         => __( 'Save changes', 'optimole-wp' ),
				'sample_image_loading' => __( ' Loading a sample image. ', 'optimole-wp' ),
				'no_images_found'      => __( 'You dont have any images in your Media Library. Add one and check how the Optimole will perform.', 'optimole-wp' ),
				'quality_slider_desc'  => __( ' See one sample image which will help you choose the right quality of the compression.', 'optimole-wp' ),
				'replacer_desc'        => __( 'Replace all the image urls from your website with the ones optimized by Optimole.', 'optimole-wp' ),
				'admin_bar_desc'       => __( 'Show in the WordPress admin bar the available quota from Optimole service.', 'optimole-wp' ),
			),
			'latest_images'                 => array(
				'image'                 => __( 'Image', 'optimole-wp' ),
				'no_images_found'       => sprintf( __( 'We might have a delay finding optimized images. Meanwhile you can visit your %1$shomepage%2$s and check how our plugin performs. ', 'optimole-wp' ), '<a href="' . esc_url( home_url() ) . '" target="_blank" >', '</a>' ),
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

	/**
	 * Add top admin bar notice of traffic quota/usage.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar resource.
	 */
	public function add_traffic_node( $wp_admin_bar ) {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$settings = new Optml_Settings();
		if ( ! $settings->is_connected() ) {
			return;
		}
		$should_load = $settings->get( 'admin_bar_item' );

		$service_data = $this->settings->get( 'service_data' );
		if ( empty( $service_data ) ) {
			return;
		}
		$args = array(
			'id'    => 'optml_image_quota',
			'title' => 'Optimole' . __( ' Image Traffic', 'optimole-wp' ) . ': ' . number_format( floatval( ( $service_data['usage'] / 1000 ) ), 3 ) . ' / ' . number_format( floatval( ( $service_data['quota'] / 1000 ) ), 0 ) . 'GB',
			'href'  => 'https://dashboard.optimole.com/',
			'meta'  => array(
				'target' => '_blank',
				'class'  => $should_load !== 'enabled' ? 'hidden' : '',
			),
		);
		$wp_admin_bar->add_node( $args );
	}
}
