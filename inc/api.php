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
	 * Connect the current site to the account behind the API key.
	 *
	 * Talks to the modern connection endpoint and shapes its answer like the legacy
	 * connect payload (app_count, extra_visits, available_apps) the plugin UI consumes.
	 *
	 * @param string $api_key Api key.
	 *
	 * @return array<string, mixed>|false|WP_Error
	 */
	public function connect( $api_key = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		$response = $this->modern_request(
			'integrations/wordpress/connections',
			'POST',
			[
				'domain'       => get_home_url(),
				'sample_image' => $this->get_sample_image(),
			]
		);

		if ( is_wp_error( $response ) ) {
			if ( $response->get_error_code() === 'domain_not_accessible' ) {
				return $this->domain_not_accessible_error();
			}

			return $response;
		}

		if ( ! isset( $response['application'] ) || ! is_array( $response['application'] ) ) {
			return false;
		}

		$application    = $response['application'];
		$custom_domains = isset( $response['custom_domains'] ) && is_array( $response['custom_domains'] ) ? $response['custom_domains'] : [];
		$domains_limit  = isset( $application['limits']['custom_domains'] ) ? (int) $application['limits']['custom_domains'] : 0;

		return [
			'app_count'      => $domains_limit > 0 ? $domains_limit : 1,
			'extra_visits'   => ! empty( $response['extra_visits'] ),
			'available_apps' => $this->build_available_apps( $application, $custom_domains ),
		];
	}

	/**
	 * Shape the modern connection response into the application list the dashboard UI expects.
	 *
	 * Mirrors the legacy "available_apps" payload: one entry per active custom domain, or the
	 * default Optimole domain when the plan has none.
	 *
	 * @param array<string, mixed>             $application    The application resource.
	 * @param array<int, array<string, mixed>> $custom_domains The active custom domains.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function build_available_apps( array $application, array $custom_domains ) {
		$default_app = [
			'key'               => isset( $application['key'] ) ? (string) $application['key'] : '',
			'status'            => ( $application['status'] ?? '' ) === 'active' ? 'active' : 'inactive',
			'domain'            => (string) ( $application['default_domain'] ?? ( $application['domain'] ?? '' ) ),
			'is_cname_assigned' => 'no',
			'cf_ssl_registered' => 'no',
			'certificate_arn'   => '',
			'limit_wl_sites'    => isset( $application['limits']['sites'] ) ? (int) $application['limits']['sites'] : 0,
		];

		if ( empty( $application['capabilities']['custom_domains'] ) ) {
			return [ $default_app ];
		}

		$apps = [];
		foreach ( $custom_domains as $custom_domain ) {
			if ( ! is_array( $custom_domain ) || empty( $custom_domain['domain'] ) ) {
				continue;
			}
			$apps[] = array_merge(
				$default_app,
				[
					'domain'            => (string) $custom_domain['domain'],
					'is_cname_assigned' => 'yes',
					'cf_ssl_registered' => 'yes',
				]
			);
		}

		return empty( $apps ) ? [ $default_app ] : $apps;
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
	 * @param string $application Unused, kept for callers passing the application key.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function update_extra_visits( $api_key = '', $status = 'enabled', $application = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		return $this->modern_request( 'application/extra-visits', 'PUT', [ 'enabled' => 'enabled' === $status ] );
	}

	/**
	 * Get a new cache-busting token from the service.
	 *
	 * @return array<string, mixed>|WP_Error The token payload, or a throttle/API error.
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
		return $this->modern_request( 'cache-tokens', 'POST' );
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
				return $this->domain_not_accessible_error();
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
	 * The error the connect flow returns when Optimole cannot reach the site.
	 *
	 * @return WP_Error
	 */
	private function domain_not_accessible_error() {
		return new WP_Error( 'domain_not_accessible', sprintf( /* translators: 1 start of the italic tag, 2 is the end of italic tag,  3 is starting anchor tag, 4 is the ending anchor tag. */ __( 'It seems Optimole is having trouble reaching your website. This issue often occurs if your website is private, local, or protected by a firewall. But don\'t stress – it\'s an easy fix! Ensure your website is live and accessible to the public. If a firewall is in place, just tweak the settings to allow the %1$sOptimole(1.0)%2$s user agent access to your website. %3$sLearn More%4$s', 'optimole-wp' ), '<i>', '</i>', '<a href="https://docs.optimole.com/article/1976-resolving-optimole-access-to-your-website" target="_blank">', '</a>' ) );
	}

	/**
	 * Call the current dashboard API (the /api/* routes).
	 *
	 * Unlike the legacy optml/v2 routes, these return the resource directly and report
	 * failures through the HTTP status, with a JSON body carrying "message" and, for
	 * known failures, a "code" the caller can branch on.
	 *
	 * @param string               $path   Route path, relative to the API root.
	 * @param string               $method HTTP method.
	 * @param array<string, mixed> $params JSON body for writes, query string for GET.
	 *
	 * @return array<string, mixed>|WP_Error The decoded body (empty for 204), or an error carrying the API error code.
	 */
	private function modern_request( $path, $method = 'GET', $params = [] ) {
		$url = trailingslashit( $this->api_root ) . ltrim( $path, '/' );
		if ( 'GET' === $method && ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}
		$url = tsdk_translate_link( $url, 'query' );

		$headers = [
			'Optml-Site' => get_home_url(),
			'Accept'     => 'application/json',
		];
		if ( ! empty( $this->api_key ) ) {
			$headers['Authorization'] = 'Bearer ' . $this->api_key;
		}

		$args = [
			'method'     => $method,
			'timeout'    => 45,
			'user-agent' => 'Optimle WP (v' . OPTML_VERSION . ') ',
			'sslverify'  => false,
			'headers'    => $headers,
		];
		if ( 'GET' !== $method ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $params );
		}

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );
		$body   = is_array( $body ) ? $body : [];

		if ( $status >= 200 && $status < 300 ) {
			return $body;
		}

		$message = isset( $body['message'] ) && is_string( $body['message'] ) ? $body['message'] : '';
		if ( '' === $message && ! empty( $body['errors'] ) && is_array( $body['errors'] ) ) {
			$first_error = reset( $body['errors'] );
			$first_error = is_array( $first_error ) ? reset( $first_error ) : $first_error;
			$message     = is_scalar( $first_error ) ? (string) $first_error : '';
		}
		if ( '' === $message ) {
			$message = sprintf( /* translators: %d is the HTTP status code. */ __( 'Unexpected response from the Optimole service (HTTP %d).', 'optimole-wp' ), $status );
		}
		$code = isset( $body['code'] ) && is_string( $body['code'] ) && '' !== $body['code'] ? $body['code'] : 'api_error';

		return new WP_Error(
			$code,
			wp_kses(
				$message,
				[
					'a' => [
						'href'   => [],
						'target' => [],
					],
				]
			)
		);
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
	 * @param array<string, array<string, mixed>> $images Alt/title data keyed by image URL.
	 * @return true|WP_Error
	 */
	public function call_data_enrich_api( $images = [] ) {
		$assets = [];
		foreach ( $images as $url => $data ) {
			if ( ! is_string( $url ) || '' === $url || ! is_array( $data ) ) {
				continue;
			}
			$asset = [ 'url' => $url ];
			foreach ( [ 'title', 'alt' ] as $attribute ) {
				if ( ! empty( $data[ $attribute ] ) && is_scalar( $data[ $attribute ] ) ) {
					$asset[ $attribute ] = mb_substr( (string) $data[ $attribute ], 0, 255 );
				}
			}
			if ( count( $asset ) === 1 ) {
				continue;
			}
			$assets[] = $asset;
		}

		if ( empty( $assets ) ) {
			return true;
		}

		// The dashboard accepts at most 100 assets per call.
		foreach ( array_chunk( $assets, 100 ) as $chunk ) {
			$response = $this->modern_request( 'assets', 'PATCH', [ 'assets' => $chunk ] );
			if ( is_wp_error( $response ) ) {
				return $response;
			}
		}

		return true;
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
