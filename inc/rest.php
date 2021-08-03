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
	 * Optml_Rest constructor.
	 */
	public function __construct() {
		$this->namespace = OPTML_NAMESPACE . '/v1';
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Register rest routes.
	 */
	public function register() {

		$this->register_service_routes();

		register_rest_route(
			$this->namespace,
			'/update_option',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'update_option' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/request_update',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'request_update' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/check_redirects',
			[
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'check_redirects' ],
				],
			]
		);

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
		register_rest_route(
			$this->namespace,
			'/connect',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'connect' ],
					'args'                => [
						'api_key' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/select',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'select_application' ],
					'args'                => [
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
			]
		);
		register_rest_route(
			$this->namespace,
			'/register',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'register_service' ],
					'args'                => [
						'email' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/disconnect',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'disconnect' ],
				],
			]
		);
	}

	/**
	 * Method to register image specific routes.
	 */
	public function register_image_routes() {
		register_rest_route(
			$this->namespace,
			'/poll_optimized_images',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'poll_optimized_images' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/images-sample-rate',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'get_sample_rate' ],
				],
			]
		);
	}
	/**
	 * Method to register media offload specific routes.
	 */
	public function register_media_offload_routes() {
		register_rest_route(
			$this->namespace,
			'/offload_images',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'offload_images' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/rollback_images',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'rollback_images' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/number_of_library_images',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'number_of_library_images' ],
				],
			]
		);
	}

	/**
	 * Method to register watermark specific routes.
	 */
	public function register_watermark_routes() {
		register_rest_route(
			$this->namespace,
			'/poll_watermarks',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'poll_watermarks' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/add_watermark',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'add_watermark' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/remove_watermark',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'remove_watermark' ],
				],
			]
		);
	}

	/**
	 * Method to register conflicts specific routes.
	 */
	public function register_conflict_routes() {
		register_rest_route(
			$this->namespace,
			'/poll_conflicts',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'poll_conflicts' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/dismiss_conflict',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'dismiss_conflict' ],
				],
			]
		);
	}

	/**
	 * Method to register cache specific routes.
	 */
	public function register_cache_routes() {
		register_rest_route(
			$this->namespace,
			'/clear_cache',
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => [ $this, 'clear_cache_request' ],
				],
			]
		);
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
		$token    = $settings->get( 'cache_buster' );
		$request  = new Optml_Api();
		$data     = $request->get_cache_token( $token );
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

		set_transient( 'optml_cache_lock', 'yes', 5 * MINUTE_IN_SECONDS );
		$settings->update( 'cache_buster', $data['token'] );

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
		if ( $user === false ) {
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

		return $this->response( $user );
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
		$media = new Optml_Media_Offload();

		foreach ( $final_images as $index => $value ) {
			$final_images[ $index ]['url'] = $media->get_media_optimized_url( $value['url'], $value['key'] );
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

		return $this->response( $settings->get( 'service_data' ) );
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
		$batch = 300;
		if ( ! empty( $request->get_param( 'batch' ) ) ) {
			$batch = $request->get_param( 'batch' );
		}
		return $this->response( Optml_Media_Offload::upload_images( $batch ) );
	}
	/**
	 * Rollback images to media library.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function rollback_images( WP_REST_Request $request ) {
		$batch = 300;
		if ( ! empty( $request->get_param( 'batch' ) ) ) {
			$batch = $request->get_param( 'batch' );
		}
		return $this->response( Optml_Media_Offload::rollback_images( $batch ) );
	}
	/**
	 * Get total number of images.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function number_of_library_images( WP_REST_Request $request ) {
		$action = 'offload_images';
		if ( ! empty( $request->get_param( 'action' ) ) ) {
			$action = $request->get_param( 'action' );
		}
		return $this->response( Optml_Media_Offload::number_of_library_images( $action ) );
	}
}
