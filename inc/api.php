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
	private $api_root = 'https://dashboard.optimole.com/api/optml/v1/';

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

		return $this->request( '/image/details', 'POST' );
	}

	/**
	 * Builds Request arguments array.
	 *
	 * @param string $method Request method (GET | POST | PUT | UPDATE | DELETE).
	 * @param string $url    Request URL.
	 * @param array  $headers Headers Array.
	 * @param array  $params  Additional params for the Request.
	 *
	 * @return array
	 */
	private function build_args( $method, $url, $headers, $params ) {
		$args = array(
			'url'        => $url,
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
	 * Request constructor.
	 *
	 * @param string $path The request url.
	 * @param string $method The request method type.
	 * @param array  $params The request method type.
	 *
	 * @return array|boolean Api data.
	 */
	private function request( $path, $method = 'GET', $params = array() ) {

		// Grab the url to which we'll be making the request.
		$url     = $this->api_root;
		$headers = array(
			'Optml-Site' => get_site_url(),
		);
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
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

		if ( ! $response['code'] ) {
			return false;
		}
		if ( intval( $response['code'] ) !== 200 ) {
			return false;
		}

		return $response['data'];

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
			'user/register-remote',
			'POST',
			array(
				'email' => $email,
				'site'  => get_site_url(),
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

		return $this->request( '/stats/images' );
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
		return $this->request( '/settings/watermark' );
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
		return $this->request( '/settings/watermark', 'DELETE', array( 'watermark' => $post_id ) );
	}

	/**
	 * Add watermark.
	 *
	 * @param array  $file The file to be uploaded.
	 * @param string $api_key The API key.
	 *
	 * @return array|bool|mixed|object
	 */
	public function add_watermark( $file, $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}
		// Grab the url to which we'll be making the request.
		$url     = str_replace( 'optml/v1/', 'wp/v2/', $this->api_root ) . 'media';
		$headers = array(
			'Optml-Site' => get_site_url(),
		);
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
			$headers['Content-Disposition'] = 'attachment; filename=' . $file['file']['name'];
		}
		$args = array(
			'url'        => $url,
			'method'     => 'POST',
			'timeout'    => 45,
			'user-agent' => 'Optimle WP (v' . OPTML_VERSION . ') ',
			'sslverify'  => false,
			'headers'    => $headers,
			'body'       => file_get_contents( $file['file']['tmp_name'] ),
		);
		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		return json_decode( wp_remote_retrieve_body( $response ), true );
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
