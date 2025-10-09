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
	 * Optimole onboard api root url.
	 *
	 * @var string Api root.
	 */
	private $onboard_api_root = 'https://onboard.i.optimole.com/onboard_api/';
	/**
	 * Optimole offload conflicts api root url.
	 *
	 * @var string Api root.
	 */
	private $upload_conflicts_api = 'https://conflicts.i.optimole.com/offload_api/';
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
		if ( defined( 'OPTIML_API_ROOT' ) ) {
			$this->api_root = constant( 'OPTIML_API_ROOT' );
		}
		if ( defined( 'OPTIML_ONBOARD_API_ROOT' ) ) {
			$this->onboard_api_root = constant( 'OPTIML_ONBOARD_API_ROOT' );
		}
		if ( defined( 'OPTIML_UPLOAD_CONFLICTS_API_ROOT' ) ) {
			$this->upload_conflicts_api = constant( 'OPTIML_UPLOAD_CONFLICTS_API_ROOT' );
		}
	}

	/**
	 * Connect to the service.
	 *
	 * @param string $api_key Api key.
	 *
	 * @return array|bool|WP_Error
	 */
	public function connect( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v2/account/connect', 'POST', [ 'sample_image' => $this->get_sample_image() ] );
	}

	/**
	 * Get sample image.
	 *
	 * @return string
	 */
	private function get_sample_image() {
		$accepted_mimes     = [ 'image/jpeg', 'image/png', 'image/webp' ];
		$args               = [
			'post_type'           => 'attachment',
			'post_status'         => 'any',
			'number'              => '1',
			'no_found_rows'       => true,
			'fields'              => 'ids',
			'post_mime_type'      => $accepted_mimes,
			'post_parent__not_in' => [ 0 ],
		];
		$image_result       = new WP_Query( $args );
		$original_image_url = 'none';
		if ( ! empty( $image_result->posts ) ) {
			$original_image_url = wp_get_attachment_image_url( $image_result->posts[0], 'full' );
		}

		return $original_image_url;
	}
	/**
	 * Get user data from service.
	 *
	 * @return array|string|bool|WP_Error User data.
	 */
	public function get_user_data( $api_key = '', $application = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v2/account/details', 'POST', [ 'application' => $application ] );
	}

	/**
	 * Toggle the extra visits.
	 *
	 * @param string $api_key Api key.
	 * @param string $status Status of the visits toggle.
	 *
	 * @return array|bool|string
	 */
	public function update_extra_visits( $api_key = '', $status = 'enabled', $application = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->request( '/optml/v2/account/extra_visits', 'POST', [ 'extra_visits' => $status, 'application' => $application ] );
	}

	/**
	 * Get cache token from service.
	 *
	 * @return array|bool|WP_Error User data.
	 */
	public function get_cache_token( $token = '', $type = '', $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}
		$lock = '';
		if ( empty( $type ) || $type === 'images' ) {
			$lock = get_transient( 'optml_cache_lock' );
		} elseif ( $type === 'assets' ) {
			$lock = get_transient( 'optml_cache_lock_assets' );
		} else {
			$type = 'images';
		}

		if ( $lock === 'yes' ) {
			return new WP_Error( 'cache_throttle', __( 'You can clear cache only once per 5 minutes.', 'optimole-wp' ) );
		}
		return $this->request( '/optml/v1/cache/tokens', 'POST', [ 'token' => $token, 'type' => $type ] );
	}

	/**
	 * Request constructor.
	 *
	 * @param string       $path The request url.
	 * @param string       $method The request method type.
	 * @param array|string $params The request method type.
	 *
	 * @return array|string|boolean|WP_Error Api data.
	 */
	public function request( $path, $method = 'GET', $params = [], $extra_headers = [] ) {

		$headers = [
			'Optml-Site' => get_home_url(),
		];
		update_option( 'optimole_wp_logger_flag', 'yes' );
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
		}
		$headers = array_merge( $headers, $extra_headers );
		$url  = trailingslashit( $this->api_root ) . ltrim( $path, '/' );
		// If there is a extra, add that as a url var.
		if ( 'GET' === $method && ! empty( $params ) ) {
			foreach ( $params as $key => $val ) {
				$url = add_query_arg( [ $key => $val ], $url );
			}
		}
		$url = tsdk_translate_link( $url, 'query' );

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
			if ( isset( $response['error'] ) && $response['error'] === 'domain_not_accessible' ) {
				return new WP_Error( 'domain_not_accessible', sprintf( /* translators: 1 start of the italic tag, 2 is the end of italic tag,  3 is starting anchor tag, 4 is the ending anchor tag. */ __( 'It seems Optimole is having trouble reaching your website. This issue often occurs if your website is private, local, or protected by a firewall. But don\'t stress – it\'s an easy fix! Ensure your website is live and accessible to the public. If a firewall is in place, just tweak the settings to allow the %1$sOptimole(1.0)%2$s user agent access to your website. %3$sLearn More%4$s', 'optimole-wp' ), '<i>', '</i>', '<a href="https://docs.optimole.com/article/1976-resolving-optimole-access-to-your-website" target="_blank">', '</a>' ) );
			}
			if ( $path === 'optml/v2/account/complete_register_remote' && isset( $response['error'] ) ) {
				if ( strpos( $response['error'], 'This email address is already registered.' ) !== false ) {
					return 'email_registered';
				}

				if ( $response['error'] === 'ERROR: Site already whitelisted.' ) {
					return 'site_exists';
				}
			}

			if ( $path === '/optml/v2/account/details'
				&& isset( $response['code'] ) && $response['code'] === 'not_allowed' ) {
				return 'disconnect';
			}

			if ( $path === '/optml/v2/account/details'
				&& isset( $response['error'] ) && $response['error'] === 'whitelist_limit_reached' ) {
				return 'disconnect';
			}

			return isset( $response['error'] ) ? new WP_Error(
				'api_error',
				wp_kses(
					$response['error'],
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				)
			) : false;
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
	 * Check if the optimized url is available.
	 *
	 * @param string $url The optimized url to check.
	 * @return bool Whether or not the url is valid.
	 */
	public function check_optimized_url( $url ) {
		$response = wp_remote_get( $url, [ 'timeout' => 30 ] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 || ! empty( wp_remote_retrieve_header( $response, 'x-not-found-o' ) ) ) {
			$this->log_offload_error( $response );
			return false;
		}
		return true;
	}

	/**
	 * Send a list of images to upload.
	 *
	 * @param array $images List of Images.
	 * @return array
	 */
	public function call_onboard_api( $images = [] ) {
		$settings     = new Optml_Settings();
		$token_images = $settings->get( 'cache_buster_images' );

		$body = [
			'secret'       => Optml_Config::$secret,
			'userKey'      => Optml_Config::$key,
			'images'       => $images,
			'cache_buster' => $token_images,
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
		return wp_remote_post( $this->onboard_api_root, $options );
	}


	/**
	 * Send a list of images with alt/title values to update.
	 *
	 * @param array $images List of images.
	 * @return array
	 */
	public function call_data_enrich_api( $images = [] ) {
		return $this->request( 'optml/v2/media/add_data', 'POST', [ 'images' => $images, 'key' => Optml_Config::$key ] );
	}
	/**
	 * Register user remotely on optimole.com.
	 *
	 * @param string $email User email.
	 *
	 * @return array|bool|string|WP_Error Api response.
	 */
	public function create_account( $email ) {
		return $this->request(
			'optml/v2/account/complete_register_remote',
			'POST',
			[
				'email'   => $email,
				'version' => OPTML_VERSION,
				'sample_image' => $this->get_sample_image(),
				'site'    => get_home_url(),
				'reference' => get_option( 'optimole_reference_key', '' ),
			]
		);
	}

	/**
	 * Get the optimized images from API.
	 *
	 * @param string $api_key the api key.
	 *
	 * @return array|bool|WP_Error
	 */
	public function get_optimized_images( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}
		$app_key = '';
		$settings = new Optml_Settings();
		$service_data = $settings->get( 'service_data' );
		if ( isset( $service_data['cdn_key'] ) ) {
			$app_key = $service_data['cdn_key'];
		}
		return $this->request( '/optml/v1/stats/images', 'GET', [], [ 'application' => $app_key ] );
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

		$params = [ 'key' => Optml_Config::$key ];
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
	 * Get offload conflicts.
	 *
	 * @return array The decoded conflicts list.
	 */
	public function get_offload_conflicts() {
		$conflicts_list = wp_remote_retrieve_body( wp_remote_get( $this->upload_conflicts_api ) );
		return json_decode( $conflicts_list, true );
	}
	/**
	 * Get offload conflicts.
	 *
	 * @param array $error_response The error to send as a string.
	 */
	public function log_offload_error( $error_response ) {

		$headers = wp_remote_retrieve_headers( $error_response );
		$body = wp_remote_retrieve_body( $error_response );

		$headers_to_log = 'no_headers_returned';
		if ( ! empty( $headers ) ) {
			$headers_to_log = wp_json_encode( $headers->getAll() );
		}
		wp_remote_post(
			$this->upload_conflicts_api,
			[
				'headers' => [ 'Content-Type' => 'application/json' ],
				'timeout'     => 15,
				'blocking'    => true,
				'sslverify'   => false,
				'data_format' => 'body',
				'body' => [
					'error_body' => wp_json_encode( $body ),
					'error_headers' => $headers_to_log,
					'error_site' => wp_json_encode( get_home_url() ),
				],
			]
		);
	}

	/**
	 * Send Offloading Logs
	 *
	 * @param string $type Type of log (offload/rollback).
	 * @param string $message Log message.
	 *
	 * @return mixed
	 */
	public function send_log( $type, $message ) {
		return $this->request(
			'optml/v2/logs',
			'POST',
			[
				'type' => $type,
				'message' => $message,
				'site' => get_home_url(),
			]
		);
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
