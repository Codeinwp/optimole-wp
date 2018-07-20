<?php

/**
 * The class defines way of connecting this user to the Optimole Dashboard.
 *
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Api {

	/**
	 * Optimole root api url.
	 *
	 * @var string Api root.
	 */
	private $api_root = 'https://dashboard.optimole.com/api/optml/v1/';
	/**
	 * Hold the user api key.
	 *
	 * @var string Api key.
	 */
	private $api_key;

	public function __construct() {
		$settings      = new Optml_Settings();
		$this->api_key = $settings->get( 'api_key' );
	}

	/**
	 * Get user data from service.
	 *
	 * @return array|bool User data.
	 */
	public function get_user_data( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/image/details', 'POST' );
	}

	/**
	 * Request constructor.
	 *
	 * @param string $path   The request url.
	 * @param string $method The request method type.
	 * @param array  $params The request method type.
	 *
	 * @return array|boolean Api data.
	 */
	private function request( $path, $method = 'GET', $params = array() ) {

		// Grab the url to which we'll be making the request.
		$url = $this->api_root;

		if ( empty( $this->api_key ) ) {
			return false;
		}
		// If there is a extra, add that as a url var.
		if ( 'GET' === $method && ! empty( $params ) ) {
			foreach ( $params as $key => $val ) {
				$url = add_query_arg( array( $key => $val ), $url );
			}
		}
		$url  = untrailingslashit( $this->api_root ) . $path;
		$args = array(
			'url'         => $url,
			'method'      => $method,
			'timeout'     => 45,
			'httpversion' => 'Optimle WP (v' . OPTML_VERSION . ') ',
			'sslverify'   => false,
			'headers'     => array(
				'Authorization' => 'Bearer ' . $this->api_key,
			),
		);
		if ( $method !== 'GET' ) {
			$args['body'] = $params;
		}
		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$response = wp_remote_retrieve_body( $response );

		if ( empty( $response ) ) {
			return false;
		}

		$response = json_decode( $response, true );

		if ( ! $response['code'] ) {
			return false;
		}
		if ( intval( $response['code'] ) !== 200 ) {
			return false;
		}

		return $response['data'];

	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}
}
