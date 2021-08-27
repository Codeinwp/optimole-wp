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
	 * Optimole upload api root url.
	 *
	 * @var string Api root.
	 */
	private $upload_api_root = 'https://generateurls-prod.i.optimole.com/upload';
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
		if ( defined( 'OPTIML_API_ROOT' ) && constant( 'OPTIML_API_ROOT' ) ) {
			$this->api_root = constant( 'OPTIML_API_ROOT' );
		}
		if ( defined( 'OPTIML_UPLOAD_API_ROOT' ) && constant( 'OPTIML_UPLOAD_API_ROOT' ) ) {
			$this->upload_api_root = constant( 'OPTIML_UPLOAD_API_ROOT' );
		}
	}

	/**
	 * Connect to the service.
	 *
	 * @param string $api_key Api key.
	 *
	 * @return array|bool
	 */
	public function connect( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v2/account/connect', 'POST' );
	}

	/**
	 * Get user data from service.
	 *
	 * @return array|bool User data.
	 */
	public function get_user_data( $api_key = '', $application = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v2/account/details', 'POST', [ 'application' => $application ] );
	}

	/**
	 * Get cache token from service.
	 *
	 * @return array|bool User data.
	 */
	public function get_cache_token( $token = '', $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}
		$lock = get_transient( 'optml_cache_lock' );
		if ( $lock === 'yes' ) {
			return new WP_Error( 'cache_throttle', __( 'You can clear cache only once per 5 minutes.', 'optimole-wp' ) );
		}
		return $this->request( '/optml/v1/cache/tokens', 'POST', [ 'token' => $token ] );
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
	private function request( $path, $method = 'GET', $params = [], $extra_headers = [] ) {

		$headers = [
			'Optml-Site' => get_home_url(),
		];
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
		}
		if ( ! empty( $headers ) && is_array( $headers ) ) {
			$headers = array_merge( $headers, $extra_headers );
		}
		$url  = trailingslashit( $this->api_root ) . ltrim( $path, '/' );
		// If there is a extra, add that as a url var.
		if ( 'GET' === $method && ! empty( $params ) ) {
			foreach ( $params as $key => $val ) {
				$url = add_query_arg( [ $key => $val ], $url );
			}
		}
		$args = $this->build_args( $method, $url, $headers, $params );

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
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
			if ( $path === 'optml/v1/user/register-remote'
				&& isset( $response['error'] )
				&& $response['error'] === 'ERROR: This email is already registered, please choose another one.' ) {
				return 'email_registered';
			}
			if ( $path === '/optml/v2/account/details'
				&& isset( $response['code'] ) && $response['code'] === 'not_allowed' ) {
				return 'disconnect';
			}
			return false;
		}

		return $response['data'];

	}

	/**
	 * Builds Request arguments array.
	 *
	 * @param string       $method Request method (GET | POST | PUT | UPDATE | DELETE).
	 * @param string       $url Request URL.
	 * @param array        $headers Headers Array.
	 * @param array|string $params Additional params for the Request.
	 *
	 * @return array
	 */
	private function build_args( $method, $url, $headers, $params ) {
		$args = [
			'method'     => $method,
			'timeout'    => 45,
			'user-agent' => 'Optimle WP (v' . OPTML_VERSION . ') ',
			'sslverify'  => false,
			'headers'    => $headers,
		];
		if ( $method !== 'GET' ) {
			$args['body'] = $params;
		}

		return $args;
	}

	/**
	 * Upload image to our servers using the generated signed url.
	 *
	 * @param string $upload_url The signed to url to upload the image to.
	 * @param string $content_type Image mime type, it must match the actual mime type of the image.
	 * @param string $image Image data from file_get_contents.
	 * @return mixed
	 */
	public function upload_image( $upload_url, $content_type, $image ) {
		$args = $this->build_args( 'PUT', '', ['content-type' => $content_type], $image );
		return wp_remote_request( $upload_url, $args );
	}

	/**
	 * Check if the optimized url is available.
	 *
	 * @param string $url The optimized url to check.
	 * @return bool Whether or not the url is valid.
	 */
	public function check_optimized_url( $url ) {
		$response = wp_remote_get( $url, ['timeout' => 30] );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}
		return true;
	}

	/**
	 * Get options for the signed urls api call.
	 *
	 * @param string $original_url Image original url.
	 * @param string $delete Whether to delete a bucket object or not(ie. generate signed upload url).
	 * @param string $table_id Remote id used on our servers.
	 * @param string $update_table False or success.
	 * @param string $get_url Whether to return a get url or not.
	 * @param string $width Original image width.
	 * @param string $height Original image height.
	 * @param int    $file_size Original file size.
	 * @return array
	 */
	public function call_upload_api( $original_url = '', $delete = 'false', $table_id = '', $update_table = 'false', $get_url = 'false', $width = 'auto', $height = 'auto', $file_size = 0 ) {
		$body = [
			'secret' => Optml_Config::$secret,
			'userKey' => Optml_Config::$key,
			'originalUrl' => $original_url,
			'deleteUrl' => $delete,
			'id' => $table_id,
			'updateDynamo' => $update_table,
			'getUrl' => $get_url,
			'width' => $width,
			'height' => $height,
			'originalFileSize' => $file_size,
		];
		$body = wp_json_encode( $body );

		$options = [
			'body'        => $body,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'timeout'     => 60,
			'blocking'    => true,
			'sslverify'   => false,
			'data_format' => 'body',
		];
		return wp_remote_post( $this->upload_api_root, $options );
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
			[
				'email'   => $email,
				'version' => OPTML_VERSION,
				'site'    => get_home_url(),
			]
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

		return $this->request( '/optml/v1/settings/watermark', 'DELETE', [ 'watermark' => $post_id ] );
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
	 * Call the images endpoint.
	 *
	 * @param integer $page Page used to advance the search.
	 * @param array   $domains Domains to filter by.
	 * @param string  $search The string to search inside the originURL.
	 * @return mixed The decoded json response from the api.
	 */
	public function get_cloud_images( $page = 0, $domains = [], $search = '' ) {

		$params = ['key' => Optml_Config::$key ];
		$params['page'] = $page;
		$params['size'] = 40;
		if ( $search !== '' ) {
			$params['search'] = $search;
		}

		if ( ! empty( $domains ) ) {
			$params['domains'] = implode( ',', $domains );
		}
		return $this->request( 'optml/v2/media/browser', 'GET', $params );
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}
}
