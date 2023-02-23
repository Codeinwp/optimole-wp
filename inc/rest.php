<?php
/**
 * Optimole Rest related actions.
 *
 * @package     Optimole/Inc
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Optml_Rest
 *
 * @codeCoverageIgnore
 */
class Optml_Rest {

	/**
	 * Rest api namespace.
	 *
	 * @var string Namespace.
	 */
	private $namespace;

	/**
	 * Upload conflicts api.
	 *
	 * @var string upload_conflicts_api.
	 */
	public static $rest_routes = [
		'service_routes' => ['update_option' => 'POST', 'request_update' => 'GET', 'check_redirects' => 'POST_PUT_PATCH',
			'connect' => [ 'POST', 'args'  => [
						'api_key' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			'select_application' => [ 'POST', 'args'  => [
					'api_key' => [
						'type'     => 'string',
						'required' => true,
					],
					'application' => [
						'type'     => 'string',
						'required' => true,
					],
			],
			],
			'register_service' => [ 'POST', 'args' => [
					'email' => [
						'type'     => 'string',
						'required' => true,
					],
				],

			],
			'disconnect' => 'GET',
		],
		'image_routes' => [
			'poll_optimized_images' => 'GET',
			'get_sample_rate' => 'POST',
			'upload_onboard_images' => [ 'POST', 'args'  => [
					'offset' => [
						'type'     => 'number',
						'required' => false,
						'default'  => 0,
					],
				],
			],
		],
		'media_cloud_routes' => [
			'offload_images' => 'POST',
			'update_content' => 'POST',
			'rollback_images' => 'POST',
			'update_page' => 'POST',
			'upload_rollback_images' => 'POST',
			'number_of_images_and_pages' => 'POST',
			'get_offload_conflicts' => 'GET',
		],
		'watermark_routes' => [
			'poll_watermarks' => 'GET',
			'add_watermark'   => 'POST',
			'remove_watermark' => 'POST',
		],
		'conflict_routes' => [
			'poll_conflicts' => 'GET',
			'dismiss_conflict' => 'POST',
		],
		'cache_routes' => [
			'clear_cache_request' => 'POST',
		],
	];

	/**
	 * Optml_Rest constructor.
	 */
	public function __construct() {
		$this->namespace = OPTML_NAMESPACE . '/v1';

		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Method to register a specific rest route.
	 *
	 * @param string $route The route name.
	 * @param string $method The route access method GET, POST, POST_PUT_PATCH.
	 * @param array  $args Optional argument to include required args.
	 */
	private function reqister_route( $route, $method = 'GET', $args = [] ) {
		$wp_method_constant = false;
		if ( $method === 'GET' ) {
			$wp_method_constant = \WP_REST_Server::READABLE;
		}
		if ( $method === 'POST' ) {
			$wp_method_constant = \WP_REST_Server::CREATABLE;
		}
		if ( $method === 'POST_PUT_PATCH' ) {
			$wp_method_constant = \WP_REST_Server::EDITABLE;
		}
		if ( $wp_method_constant !== false ) {
			$params = [
				'methods'             => $wp_method_constant,
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => [ $this, $route ],
			];
			if ( ! empty( $args ) ) {
				$params['args'] = $args;
			}
			register_rest_route(
				$this->namespace,
				'/' . $route,
				[
					$params,
				]
			);
		}

	}

	/**
	 * Register rest routes.
	 */
	public function register() {

		$this->register_service_routes();

		$this->register_image_routes();
		$this->register_watermark_routes();
		$this->register_conflict_routes();
		$this->register_cache_routes();
		$this->register_media_offload_routes();
	}

	/**
	 * Method to register service specific routes.
	 */
	public function register_service_routes() {
		foreach ( self::$rest_routes['service_routes'] as $route => $details ) {
			if ( is_array( $details ) ) {
				$this->reqister_route( $route, $details[0], $details['args'] );
			} else {
				$this->reqister_route( $route, $details );
			}
		}
	}

	/**
	 * Method to register image specific routes.
	 */
	public function register_image_routes() {
		foreach ( self::$rest_routes['image_routes'] as $route => $details ) {
			if ( is_array( $details ) ) {
				$this->reqister_route( $route, $details[0], $details['args'] );
			} else {
				$this->reqister_route( $route, $details );
			}
		}

	}
	/**
	 * Method to register media offload specific routes.
	 */
	public function register_media_offload_routes() {
		foreach ( self::$rest_routes['media_cloud_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details );
		}
	}

	/**
	 * Method to register watermark specific routes.
	 */
	public function register_watermark_routes() {
		foreach ( self::$rest_routes['watermark_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details );
		}
	}

	/**
	 * Method to register conflicts specific routes.
	 */
	public function register_conflict_routes() {
		foreach ( self::$rest_routes['conflict_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details );
		}
	}

	/**
	 * Method to register cache specific routes.
	 */
	public function register_cache_routes() {
		foreach ( self::$rest_routes['cache_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details );
		}
	}

	/**
	 * Clear Cache request.
	 *
	 * @param WP_REST_Request $request clear cache rest request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function clear_cache_request( WP_REST_Request $request ) {
		$settings = new Optml_Settings();
		$type = $request->get_param( 'type' );
		$token = $settings->get( 'cache_buster' );
		$token_images = $settings->get( 'cache_buster_images' );

		if ( ! empty( $token_images ) ) {
			$token = $token_images;
		}
		if ( ! empty( $type ) && $type === 'assets' ) {
			$token = $settings->get( 'cache_buster_assets' );
		}
		$request  = new Optml_Api();
		$data     = $request->get_cache_token( $token, $type );
		if ( $data === false || is_wp_error( $data ) || empty( $data ) || ! isset( $data['token'] ) ) {
			$extra = '';
			if ( is_wp_error( $data ) ) {
				/**
				 * Error from api.
				 *
				 * @var WP_Error $data Error object.
				 */
				$extra = sprintf( __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
			}
			wp_send_json_error( __( 'Can not get new token from Optimole service', 'optimole-wp' ) . $extra );
		}

		if ( ! empty( $type ) && $type === 'assets' ) {
			set_transient( 'optml_cache_lock_assets', 'yes', 5 * MINUTE_IN_SECONDS );
			$settings->update( 'cache_buster_assets', $data['token'] );
		} else {
			set_transient( 'optml_cache_lock', 'yes', 5 * MINUTE_IN_SECONDS );
			$settings->update( 'cache_buster_images', $data['token'] );
		}

		return $this->response( $data['token'], '200' );
	}

	/**
	 * Connect to optimole service.
	 *
	 * @param WP_REST_Request $request connect rest request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function connect( WP_REST_Request $request ) {
		$api_key = $request->get_param( 'api_key' );
		$original_request = $request;
		$request = new Optml_Api();
		$data    = $request->connect( $api_key );

		if ( $data === false || is_wp_error( $data ) ) {
			$extra = '';
			if ( is_wp_error( $data ) ) {
				/**
				 * Error from api.
				 *
				 * @var WP_Error $data Error object.
				 */
				$extra = sprintf( __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
			}
			return $this->response( __( 'Can not connect to Optimole service', 'optimole-wp' ) . $extra, 400 );
		}
		$settings = new Optml_Settings();
		$settings->update( 'api_key', $api_key );
		if ( $data['app_count'] === 1 ) {
			return $this->select_application( $original_request );
		}
		return $this->response( $data );
	}

	/**
	 * Select application.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function select_application( WP_REST_Request $request ) {
		$api_key = $request->get_param( 'api_key' );
		$application = $request->get_param( 'application' );
		$request = new Optml_Api();
		$data    = $request->get_user_data( $api_key, $application );
		if ( $data === false || is_wp_error( $data ) ) {
			$extra = '';
			if ( is_wp_error( $data ) ) {
				/**
				 * Error from api.
				 *
				 * @var WP_Error $data Error object.
				 */
				$extra = sprintf( __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
			}
			wp_send_json_error( __( 'Can not connect to Optimole service', 'optimole-wp' ) . $extra );
		}
		$settings = new Optml_Settings();
		$settings->update( 'service_data', $data );
		return $this->response( $data );
	}

	/**
	 * Wrapper for api response.
	 *
	 * @param mixed $data data from api.
	 *
	 * @return WP_REST_Response
	 */
	private function response( $data, $code = 'success' ) {
		return new WP_REST_Response( [ 'data' => $data, 'code' => $code ], 200 );
	}

	/**
	 * Connect to optimole service.
	 *
	 * @param WP_REST_Request $request connect rest request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function register_service( WP_REST_Request $request ) {
		$email = $request->get_param( 'email' );
		$api   = new Optml_Api();

		$user  = $api->create_account( $email );

		$auto_connect = $request->get_param( 'auto_connect' );

		if ( ! empty( $auto_connect ) && $auto_connect === 'true' ) {
			delete_option( Optml_Settings::OPTML_USER_EMAIL );
		}

		if ( $user === false || is_wp_error( $user ) ) {
			return new WP_REST_Response(
				[
					'data'    => null,
					'message' => __( 'Error creating account.', 'optimole-wp' ),
					'code'    => 'error',
				],
				200
			);
		}
		if ( $user === 'email_registered' ) {
			return new WP_REST_Response(
				[
					'data'    => null,
					'message' => __( 'Error: This email is already registered. Please choose another one.', 'optimole-wp' ),
					'code'    => 'email_registered',
				],
				200
			);

		}
		$user_data = $user['res'];

		$settings = new Optml_Settings();
		$settings->update( 'api_key', $user_data['api_key'] );

		if ( $user_data['app_count'] === 1 ) {
			$settings->update( 'service_data', $user_data );
		}

		return $this->response( $user_data );
	}

	/**
	 * Return image samples.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response Image urls.
	 */
	public function get_sample_rate( WP_REST_Request $request ) {

		add_filter( 'optml_dont_replace_url', '__return_true' );
		$image_sample = get_transient( 'optimole_sample_image' );
		if ( $image_sample === false || $request->get_param( 'force' ) === 'yes' ) {
			$image_sample = $this->fetch_sample_image();
			set_transient( 'optimole_sample_image', $image_sample );
		}
		$image = [ 'id' => $image_sample['id'] ];

		$image['original'] = $image_sample['url'];

		remove_filter( 'optml_dont_replace_url', '__return_true' );

		$image['optimized'] = apply_filters(
			'optml_replace_image',
			$image['original'],
			[
				'width'   => $image_sample['width'],
				'height'  => $image_sample['height'],
				'quality' => $request->get_param( 'quality' ),
			]
		);

		$optimized = wp_remote_get(
			$image['optimized'],
			[
				'timeout' => 10,
				'headers' => [
					'Accept' => 'text/html,application/xhtml+xml,image/webp,image/apng ',
				],
			]
		);

		$original = wp_remote_get( $image['original'] );

		$image['optimized_size'] = (int) wp_remote_retrieve_header( $optimized, 'content-length' );
		$image['original_size']  = (int) wp_remote_retrieve_header( $original, 'content-length' );

		return $this->response( $image );
	}

	/**
	 * Crawl & upload initial load.
	 *
	 * @return WP_REST_Response If there are more posts left to receive.
	 */
	public function upload_onboard_images( WP_REST_Request $request ) {
		$offset = absint( $request->get_param( 'offset' ) );

		// Arguments for get_posts function
		$args = [
			'post_type'              => 'attachment',
			'post_mime_type'         => 'image',
			'post_status'            => 'inherit',
			'posts_per_page'         => 50,
			'offset'                 => $offset,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => [
				'parent' => 'DESC',
			],
		];

		// Initialize an array to store the image URLs
		$image_urls = [];

		add_filter( 'optml_dont_replace_url', '__return_true' );

		// If site has a logo, get the logo url if offset is 0, ie first request
		if ( $offset === 0 ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );

			if ( $custom_logo_id ) {
				$image_urls[] = wp_get_attachment_url( $custom_logo_id );
			}
		}

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			$image_ids = $query->get_posts();
			$image_urls = array_map( 'wp_get_attachment_url', $image_ids );
		}
		remove_filter( 'optml_dont_replace_url', '__return_true' );

		if ( count( $image_urls ) === 0 ) {
			return $this->response( true );
		}

		$api = new Optml_Api();
		$api->call_onboard_api( $image_urls );

		// Check if image_url array has at least 50 items, if not, we have reached the end of the images
		return $this->response( count( $image_urls ) < 50 ? true : false );
	}

	/**
	 * Return sample image data.
	 *
	 * @return array Image data.
	 */
	private function fetch_sample_image() {
		$accepted_mimes = [ 'image/jpeg' ];
		$args           = [
			'post_type'           => 'attachment',
			'post_status'         => 'any',
			'number'              => '5',
			'no_found_rows'       => true,
			'fields'              => 'ids',
			'post_mime_type'      => $accepted_mimes,
			'post_parent__not_in' => [ 0 ],
		];
		$image_result   = new WP_Query( $args );
		if ( empty( $image_result->posts ) ) {
			$rand_id            = rand( 1, 3 );
			$original_image_url = OPTML_URL . 'assets/img/' . $rand_id . '.jpg';

			return [
				'url'    => $original_image_url,
				'width'  => '700',
				'height' => '465',
				'id'     => - 1,
			];
		}
		$attachment_id = $image_result->posts[ array_rand( $image_result->posts, 1 ) ];

		$original_image_url = wp_get_attachment_image_url( $attachment_id, 'full' );

		$metadata = wp_get_attachment_metadata( $attachment_id );

		$width  = 'auto';
		$height = 'auto';
		$size   = 'full';
		if ( isset( $metadata['sizes'] ) && isset( $metadata['sizes'][ $size ] ) ) {
			$width  = $metadata['sizes'][ $size ]['width'];
			$height = $metadata['sizes'][ $size ]['height'];
		}

		return [
			'url'    => $original_image_url,
			'id'     => $attachment_id,
			'width'  => $width,
			'height' => $height,
		];
	}

	/**
	 * Disconnect from optimole service.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param WP_REST_Request $request disconnect rest request.
	 */
	public function disconnect( WP_REST_Request $request ) {
		$settings = new Optml_Settings();
		$settings->reset();
		wp_send_json_success( 'Disconnected' );
	}

	/**
	 * Get optimized images from API.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function poll_optimized_images( WP_REST_Request $request ) {
		$api_key = $request->get_param( 'api_key' );
		$request = new Optml_Api();
		$images  = $request->get_optimized_images( $api_key );
		if ( ! isset( $images['list'] ) || empty( $images['list'] ) ) {
			return $this->response( [] );
		}

		$final_images = array_splice( $images['list'], 0, 10 );

		foreach ( $final_images as $index => $value ) {
			$final_images[ $index ]['url'] = Optml_Media_Offload::instance()->get_media_optimized_url(
				$value['url'],
				$value['key'],
				140,
				140,
				[
					'type'    => Optml_Resize::RESIZE_FILL,
					'enlarge' => false,
					'gravity' => Optml_Resize::GRAVITY_CENTER,
				]
			);
			unset( $final_images[ $index ]['key'] );
		}

		return $this->response( $final_images );
	}

	/**
	 * Get watermarks from API.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function poll_watermarks( WP_REST_Request $request ) {
		$api_key    = $request->get_param( 'api_key' );
		$request    = new Optml_Api();
		$watermarks = $request->get_watermarks( $api_key );
		if ( ! isset( $watermarks['watermarks'] ) || empty( $watermarks['watermarks'] ) ) {
			return $this->response( [] );
		}
		$final_images = array_splice( $watermarks['watermarks'], 0, 10 );

		return $this->response( $final_images );
	}

	/**
	 * Add watermark.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function add_watermark( WP_REST_Request $request ) {
		$file     = $request->get_file_params();
		$request  = new Optml_Api();
		$response = $request->add_watermark( $file );
		if ( $response === false ) {
			return $this->response( __( 'Error uploading image. Please try again.', 'optimole-wp' ), 'error' );
		}

		return $this->response( __( 'Watermark image uploaded succesfully ! ', 'optimole-wp' ) );
	}

	/**
	 * Remove watermark.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function remove_watermark( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'postID' );
		$api_key = $request->get_param( 'api_key' );
		$request = new Optml_Api();

		return $this->response( $request->remove_watermark( $post_id, $api_key ) );
	}

	/**
	 * Get conflicts from API.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function poll_conflicts( WP_REST_Request $request ) {
		$conflicts_to_register = apply_filters( 'optml_register_conflicts', [] );
		$manager               = new Optml_Conflict_Manager( $conflicts_to_register );

		return $this->response(
			[
				'count'     => $manager->get_conflict_count(),
				'conflicts' => $manager->get_conflict_list(),
			]
		);
	}

	/**
	 * Dismiss conflict.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_conflict( WP_REST_Request $request ) {
		$conflict_id           = $request->get_param( 'conflictID' );
		$conflicts_to_register = apply_filters( 'optml_register_conflicts', [] );
		$manager               = new Optml_Conflict_Manager( $conflicts_to_register );
		$manager->dismiss_conflict( $conflict_id );

		return $this->response(
			[
				'count'     => $manager->get_conflict_count(),
				'conflicts' => $manager->get_conflict_list(),
			]
		);
	}

	/**
	 * Request stats update for app.
	 *
	 * @param WP_REST_Request $request rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function request_update( WP_REST_Request $request ) {
		do_action( 'optml_daily_sync' );
		$settings = new Optml_Settings();
		$service_data = $settings->get( 'service_data' );
		if ( empty( $service_data ) ) {
			return $this->response( '', 'disconnected' );
		}
		return $this->response( $service_data );
	}

	/**
	 * Update options method.
	 *
	 * @param WP_REST_Request $request option update rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function update_option( WP_REST_Request $request ) {
		$new_settings = $request->get_param( 'settings' );

		if ( empty( $new_settings ) ) {
			wp_send_json_error( 'No option key set.' );
		}

		$settings  = new Optml_Settings();
		$sanitized = $settings->parse_settings( $new_settings );

		return $this->response( $sanitized );
	}

	/**
	 * Update options method.
	 *
	 * @param WP_REST_Request $request option update rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function check_redirects( WP_REST_Request $request ) {
		if ( empty( $request->get_param( 'images' ) ) ) {
			return $this->response( __( 'No images available on the current page.' ), 'noImagesFound' );
		}
		// 'ok' if no issues found, 'log' is there are issues we need to notify, 'deactivated' if the user's account is disabled
		$status = 'ok';
		$result = '';
		foreach ( $request->get_param( 'images' ) as $domain => $value ) {
			$args             = [
				'method'      => 'GET',
				'redirection' => 0,
			];
			$processed_images = 0;
			if ( isset( $value['src'] ) ) {
				$processed_images = count( $value['src'] );
			}
			if ( isset( $value['ignoredUrls'] ) && $value['ignoredUrls'] > $processed_images ) {
				$result .= '<li>❌ ' . sprintf( __( 'The images from: %1$s are not optimized by Optimole. If you would like to do so, you can follow this: %2$sWhy Optimole does not optimize all the images from my site?%3$s.', 'optimole-wp' ), $domain, '<a target="_blank" href="https://docs.optimole.com/article/1290-how-to-optimize-images-using-optimole-from-my-domain">', '</a>' ) . '</li>';
				$status = 'log';
				continue;
			}

			if ( $processed_images > 0 ) {
				$response = wp_remote_get( $value['src'][ rand( 0, $processed_images - 1 ) ], $args );
				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$headers     = $response['headers']; // array of http header lines
					$status_code = $response['response']['code'];
					if ( $status_code === 301 ) {
						$status = 'deactivated';
						$result = '<li>❌ ' . sprintf( __( 'Your account is currently disabled due to exceeding quota and Optimole is no longer able to optimize the images. In order to fix this you will need to %1$supgrade%2$s.', 'optimole-wp' ), '<a target="_blank" href="https://optimole.com/pricing">', '</a>' ) . '</li>';
						break;
					}
					if ( $status_code === 302 ) {
						if ( isset( $headers['x-redirect-o'] ) ) {
							$optimole_code = (int) $headers['x-redirect-o'];
							if ( $optimole_code === 1 ) {
								$status = 'log';
								$result .= '<li>❌ ' . sprintf( __( 'The domain: %1$s is not allowed to optimize images using your Optimole account. You can add this to the allowed list %2$shere%3$s.', 'optimole-wp' ), '<b>' . $domain . '</b>', '<a target="_blank" href="https://dashboard.optimole.com/whitelist">', '</a>' ) . '</li>';
							}
							if ( $optimole_code === 4 ) {
								$status = 'log';
								$result .= '<li>❌ ' . sprintf( __( 'We are not able to download the images from %1$s. Please check %2$sthis%3$s document for a more advanced guide on how to solve this. ', 'optimole-wp' ), '<b>' . $domain . '</b>', '<a target="_blank" href="https://docs.optimole.com/article/1291-why-optimole-is-not-able-to-download-the-images-from-my-site">', '</a>' ) . '<br />' . '</li>';
							}
						}
					}
				}
			}
		}
		if ( $result === '' ) {
			$result = __( 'No issues detected, everything is running smoothly.', 'optimole-wp' );
		}

		return $this->response( '<ul>' . $result . '</ul>', $status );
	}

	/**
	 * Push existing image our servers.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function offload_images( WP_REST_Request $request ) {
		$batch = 1;
		if ( ! empty( $request->get_param( 'batch' ) ) ) {
			$batch = $request->get_param( 'batch' );
		}
		$images = [];
		if ( ! empty( $request->get_param( 'images' ) ) ) {
			$images = $request->get_param( 'images' );
		}
		$media_response = Optml_Media_Offload::instance()->upload_images( $batch, $images );
		$media_response['nonce'] = wp_create_nonce( 'wp_rest' );
		return $this->response( $media_response );
	}
	/**
	 * Update posts content.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function update_content( WP_REST_Request $request ) {
		$page = 1;
		if ( ! empty( $request->get_param( 'page' ) ) ) {
			$page = $request->get_param( 'page' );
		}
		$job = '';
		if ( ! empty( $request->get_param( 'job' ) ) ) {
			$job = $request->get_param( 'job' );
		}
		$batch = 1;
		if ( ! empty( $request->get_param( 'batch' ) ) ) {
			$batch = $request->get_param( 'batch' );
		}
		$media_response = Optml_Media_Offload::instance()->update_content( $page, $job, $batch );
		$media_response['nonce'] = wp_create_nonce( 'wp_rest' );
		return $this->response( $media_response );
	}
	/**
	 * Rollback images to media library.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function rollback_images( WP_REST_Request $request ) {
		$batch = 1;
		if ( ! empty( $request->get_param( 'batch' ) ) ) {
			$batch = $request->get_param( 'batch' );
		}
		$images = [];
		if ( ! empty( $request->get_param( 'images' ) ) ) {
			$images = $request->get_param( 'images' );
		}
		$media_response = Optml_Media_Offload::instance()->rollback_images( $batch, $images );
		$media_response['nonce'] = wp_create_nonce( 'wp_rest' );
		return $this->response( $media_response );
	}
	/**
	 * Update page to replace the image urls.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function update_page( WP_REST_Request $request ) {
		if ( empty( $request->get_param( 'post_id' ) ) ) {
			return false;
		}
		$post_id = $request->get_param( 'post_id' );
		return $this->response( Optml_Media_Offload::instance()->update_page( $post_id ) );
	}
	/**
	 * Sync or rollback images with the given ids.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function upload_rollback_images( WP_REST_Request $request ) {
		$job = false;
		if ( ! empty( $request->get_param( 'job' ) ) ) {
			$job = $request->get_param( 'job' );
		}
		$image_ids = [];
		if ( ! empty( $request->get_param( 'image_ids' ) ) ) {
			$image_ids = $request->get_param( 'image_ids' );
		}
		if ( $job === 'offload_images' ) {
			return $this->response( Optml_Media_Offload::instance()->upload_and_update_existing_images( $image_ids ) );
		}
		if ( $job === 'rollback_images' ) {
			return $this->response( Optml_Media_Offload::instance()->rollback_and_update_images( $image_ids ) );
		}
	}
	/**
	 * Get total number of images.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function number_of_images_and_pages( WP_REST_Request $request ) {
		$action = 'offload_images';
		if ( ! empty( $request->get_param( 'action' ) ) ) {
			$action = $request->get_param( 'action' );
		}
		return $this->response( Optml_Media_Offload::number_of_images_and_pages( $action ) );
	}
	/**
	 * Get conflicts list.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_offload_conflicts( WP_REST_Request $request ) {
		$request  = new Optml_Api();
		$decoded_list     = $request->get_offload_conflicts();
		$active_conflicts = [];
		if ( isset( $decoded_list['plugins'] ) ) {
			foreach ( $decoded_list['plugins'] as $slug ) {
				if ( is_plugin_active( $slug ) ) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $slug );
					if ( ! empty( $plugin_data['Name'] ) ) {
						$active_conflicts[] = $plugin_data['Name'];
					}
				}
			}
		}

		return $this->response( $active_conflicts );
	}
}
