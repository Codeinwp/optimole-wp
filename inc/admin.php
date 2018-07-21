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
	 * Optml_Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_traffic_node' ), 9999 );
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
	public function render_dashboard_page() { ?>
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
		wp_register_script( OPTML_NAMESPACE . '-admin', OPTML_URL . 'assets/js/bundle.min.js', array(), OPTML_VERSION );
		wp_localize_script( OPTML_NAMESPACE . '-admin', 'optimoleDashboardApp', $this->localize_dashboard_app() );
		wp_enqueue_script( OPTML_NAMESPACE . '-admin' );
	}

	/**
	 * Localize the dashboard app.
	 *
	 * @return array
	 */
	private function localize_dashboard_app() {
		$settings       = new Optml_Settings();
		$api_key        = $settings->get( 'api_key' );
		$service_data   = $settings->get( 'service_data' );
		$admin_bar_item = $settings->get( 'admin_bar_item' );

		$args = array(
			'strings'           => $this->get_dashboard_strings(),
			'assets_url'        => OPTML_URL . 'assets/',
			'connection_status' => empty( $service_data ) ? false : true,
			'api_key'           => $api_key,
			'root'              => rest_url( OPTML_NAMESPACE . '/v1' ),
			'nonce'             => wp_create_nonce( 'wp_rest' ),
			'user_data'         => $service_data,
			'admin_bar_item'    => $admin_bar_item,
		);

		return $args;
	}

	private function get_dashboard_strings() {
		return array(
			'optimole'            => __( 'Optimole', 'optimole-wp' ),
			'image_cdn'           => __( 'Image CDN', 'optimole-wp' ),
			'connect_btn'         => __( 'Connect to OptiMole Service', 'optimole-wp' ),
			'disconnect_btn'      => __( 'Disconnect', 'optimole-wp' ),
			'api_key_placeholder' => __( 'API Key', 'optimole-wp' ),
			'toggle_ab_item'      => __( 'Admin bar status', 'optimole-wp' ),
			'invalid_key'         => __( 'Invalid API Key', 'optimole-wp' ),
			'status'              => __( 'Status', 'optimole-wp' ),
			'connected'           => __( 'Connected', 'optimole-wp' ),
			'not_connected'       => __( 'Not connected', 'optimole-wp' ),
			'usage'               => __( 'Monthly Usage', 'optimole-wp' ),
			'quota'               => __( 'Monthly Quota', 'optimole-wp' ),
			'logged_in_as'        => __( 'Logged in as', 'optimole-wp' ),
			'private_cdn_url'     => __( 'Private CDN url', 'optimole-wp' ),
			'show'                => __( 'Show', 'optimole-wp' ),
			'hide'                => __( 'Hide', 'optimole-wp' ),
			'account_needed'      => sprintf(
				__( 'In order to get access to free image optimization service you will need an account on %s. You will get access to our image optimization and CDN service for free in the limit of 1GB traffic per month.', 'optimole-wp' ),
				' <a href="https://dashboard.optimole.com/register" target="_blank">optimole.com</a>'
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
		$service_data = $settings->get( 'service_data' );
		if( empty ($service_data) ) {
			return;
		}
		$should_load = $settings->get( 'admin_bar_item' );
		if ( $should_load !== 'enabled' ) {
			return;
		}

		$args         = array(
			'id'    => 'optml_image_quota',
			'title' => 'Optimole' . __( ' Image Traffic', 'optimole-wp' ) . ': ' . number_format( floatval( ( $service_data['usage'] / 1000 ) ), 3 ) . ' / ' . number_format( floatval( ( $service_data['quota'] / 1000 ) ), 0 ) . 'GB',
			'href'  => 'https://dashboard.optimole.com/',
			'meta'  => array( 'target' => '_blank' )
		);
		$wp_admin_bar->add_node( $args );
	}
}