<?php

/**
 * The class defines way of connecting this user to the Optimole Dashboard.
 *
 * @codeCoverageIgnore
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Api {

	/**
	 * Optimole root api url.
	 *
	 * @var string Api root.
	 */
	private $api_root = 'https://dashboard.optimole.com/api/';
	/**
	 * Hold the user api key.
	 *
	 * @var string Api key.
	 */
	private $api_key;

	/**
	 * Optml_Api constructor.
	 */
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

		return $this->request( '/optml/v1/image/details', 'POST' );
	}

	/**
	 * Request constructor.
	 *
	 * @param string $path The request url.
	 * @param string $method The request method type.
	 * @param array  $params The request method type.
	 *
	 * @return array|boolean Api data.
	 */
	private function request( $path, $method = 'GET', $params = array(), $extra_headers = [] ) {

		// Grab the url to which we'll be making the request.
		$url     = $this->api_root;
		$headers = array(
			'Optml-Site' => get_home_url(),
		);
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
		}
		if ( ! empty( $headers ) && is_array( $headers ) ) {
			$headers = array_merge( $headers, $extra_headers );
		}

		// If there is a extra, add that as a url var.
		if ( 'GET' === $method && ! empty( $params ) ) {
			foreach ( $params as $key => $val ) {
				$url = add_query_arg( array( $key => $val ), $url );
			}
		}
		$url  = trailingslashit( $this->api_root ) . ltrim( $path, '/' );
		$args = $this->build_args( $method, $url, $headers, $params );

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$response = wp_remote_retrieve_body( $response );

		if ( empty( $response ) ) {
			return false;
		}
		$response = json_decode( $response, true );

		if ( isset( $response['id'] ) && is_numeric( $response['id'] ) ) {
			return true;
		}
		if ( ! isset( $response['code'] ) ) {
			return false;
		}
		if ( intval( $response['code'] ) !== 200 ) {
			return false;
		}

		return $response['data'];

	}

	/**
	 * Builds Request arguments array.
	 *
	 * @param string $method Request method (GET | POST | PUT | UPDATE | DELETE).
	 * @param string $url Request URL.
	 * @param array  $headers Headers Array.
	 * @param array  $params Additional params for the Request.
	 *
	 * @return array
	 */
	private function build_args( $method, $url, $headers, $params ) {
		$args = array(
			'method'     => $method,
			'timeout'    => 45,
			'user-agent' => 'Optimle WP (v' . OPTML_VERSION . ') ',
			'sslverify'  => false,
			'headers'    => $headers,
		);
		if ( $method !== 'GET' ) {
			$args['body'] = $params;
		}

		return $args;
	}

	/**
	 * Register user remotely on optimole.com.
	 *
	 * @param string $email User email.
	 *
	 * @return array|bool Api response.
	 */
	public function create_account( $email ) {
		return $this->request(
			'optml/v1/user/register-remote',
			'POST',
			array(
				'email' => $email,
				'site'  => get_home_url(),
			)
		);
	}

	/**
	 * Get the optimized images from API.
	 *
	 * @param string $api_key the api key.
	 *
	 * @return array|bool
	 */
	public function get_optimized_images( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v1/stats/images' );
	}

	/**
	 * Get the watermarks from API.
	 *
	 * @param string $api_key The API key.
	 *
	 * @return array|bool
	 */
	public function get_watermarks( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v1/settings/watermark' );
	}

	/**
	 * Remove the watermark from the API.
	 *
	 * @param integer $post_id The watermark post ID.
	 * @param string  $api_key The API key.
	 *
	 * @return array|bool
	 */
	public function remove_watermark( $post_id, $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v1/settings/watermark', 'DELETE', array( 'watermark' => $post_id ) );
	}

	/**
	 * Add watermark.
	 *
	 * @param array $file The file to be uploaded.
	 *
	 * @return array|bool|mixed|object
	 */
	public function add_watermark( $file ) {

		$headers = [
			'Content-Disposition' => 'attachment; filename=' . $file['file']['name'],
		];

		$response = $this->request( 'wp/v2/media', 'POST', file_get_contents( $file['file']['tmp_name'] ), $headers );

		if ( $response === false ) {
			return false;
		}

		return $response;
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
