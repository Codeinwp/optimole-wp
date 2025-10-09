<?php
/**
 * Optimole Rest related actions.
 *
 * @package     Optimole/Inc
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

use Optimole\Sdk\Resource\ImageProperty\ResizeTypeProperty;
use Optimole\Sdk\ValueObject\Position;
use OptimoleWP\PageProfiler\Profile;

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
	 * @var array upload_conflicts_api.
	 */
	public static $rest_routes = [
		'service_routes' => [
			'update_option' => 'POST', 'request_update' => 'GET', 'check_redirects' => 'POST_PUT_PATCH',
				'connect' => [
		'POST', 'args'  => [
							'api_key' => [
								'type'     => 'string',
								'required' => true,
							],
						],
					],
				'select_application' => [
			'POST', 'args'  => [
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
				'register_service' => [
			'POST', 'args' => [
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
			'upload_onboard_images' => [
				'POST', 'args'  => [
						'offset' => [
							'type'     => 'number',
							'required' => false,
							'default'  => 0,
						],
					],
			],
		],
		'media_cloud_routes' => [
			'number_of_images_and_pages' => 'POST',
			'clear_offload_errors' => 'GET',
			'get_offload_conflicts' => 'GET',
			'move_image' => [
				'POST',
				'args' => [
					'id' => [
						'type'     => 'number',
						'required' => true,
					],
					'action' => [
						'type'     => 'string',
						'required' => true,
					],
				],
				'permission_callback' => 'upload_files',
			],
		],
		'conflict_routes' => [
			'poll_conflicts' => 'GET',
			'dismiss_conflict' => 'POST',
		],
		'cache_routes' => [
			'clear_cache_request' => 'POST',
		],
		'dam_routes' => [
			'insert_images' => [
				'POST',
				'args' => [
					'images' => [
						'type'     => 'array',
						'required' => true,
					],
				],
				'permission_callback' => 'upload_files',
			],
		],
		'notification_dismiss_routes' => [
			'dismiss_notice' => [
				'POST',
				'args' => [
					'key' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		],
		'optimization_routes' => [
			'optimizations' => [
				'POST',
				'args' => [
					'd' => [
						'type'     => 'integer',
						'required' => true,
					],
					'a' => [
						'type'     => 'array',
						'required' => true,
					],
					'u' => [
						'type'     => 'string',
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],
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
	 * @param string $permission_callback Optional permission callback.
	 */
	private function reqister_route( $route, $method = 'GET', $args = [], $permission_callback = 'manage_options' ) {
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
				'permission_callback' => function_exists( $permission_callback ) ? $permission_callback : function () use ( $permission_callback ) {
					return current_user_can( $permission_callback );
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
		$this->register_conflict_routes();
		$this->register_cache_routes();
		$this->register_media_offload_routes();
		$this->register_dam_routes();
		$this->register_notification_routes();
		$this->register_optimization_routes();
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

			$permission = isset( $details['permission_callback'] ) ? $details['permission_callback'] : 'manage_options';
			$args       = isset( $details['args'] ) ? $details['args'] : [];
			$this->reqister_route( $route, is_array( $details ) ? $details[0] : $details, $args, $permission );
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
	 * Register DAM routes.
	 *
	 * @return void
	 */
	public function register_dam_routes() {
		foreach ( self::$rest_routes['dam_routes'] as $route => $details ) {
			$permission = isset( $details['permission_callback'] ) ? $details['permission_callback'] : 'manage_options';
			$args       = isset( $details['args'] ) ? $details['args'] : [];
			$this->reqister_route( $route, $details[0], $args, $permission );
		}
	}

	/**
	 * Register notification dismiss routes.
	 *
	 * @return void
	 */
	public function register_notification_routes() {
		foreach ( self::$rest_routes['notification_dismiss_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details[0], isset( $details['args'] ) ? $details['args'] : [] );
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
		$response = $settings->clear_cache( $type );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		return $this->response( $response, '200' );
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
				if ( $data->get_error_code() === 'domain_not_accessible' ) {
					return $this->response( $data->get_error_message(), 400 );
				}
				/**
				 * Error from api.
				 *
				 * @var WP_Error $data Error object.
				 */
				$extra = sprintf( /* translators: Error details */ __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
			}
			return $this->response( __( 'Can not connect to Optimole service', 'optimole-wp' ) . $extra, 400 );
		}
		$settings = new Optml_Settings();
		$settings->update( 'api_key', $api_key );

		if ( isset( $data['extra_visits'] ) ) {
			$settings->update_frontend_banner_from_remote( $data['extra_visits'] );
		}

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
				$extra = sprintf( /* translators: Error details */ __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
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
			if ( $user->get_error_code() === 'domain_not_accessible' ) {
				return new WP_REST_Response(
					[
						'data'    => null,
						'message' => $user->get_error_message(),
						'code'    => 'domain_not_accessible',
					],
					200
				);
			}

			return new WP_REST_Response(
				[
					'data'    => null,
					'message' => __( 'Error creating account.', 'optimole-wp' ) . ' ' . $user->get_error_message(),
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

		if ( $user === 'site_exists' ) {
			return new WP_REST_Response(
				[
					'data'    => null,
					'message' => sprintf( /* translators: 1 start anchor tag to register page, 2 is ending anchor tag. */ __( 'Error: This site has been previously registered. You can login to your account from %1$shere%2$s', 'optimole-wp' ), '<a href="' . esc_url( tsdk_translate_link( 'https://dashboard.optimole.com/login', 'query' ) ) . '" target="_blank"> ', '</a>' ),
					'code'    => 'site_exists',
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

		if ( isset( $user_data['extra_visits'] ) ) {
			$settings->update_frontend_banner_from_remote( $user_data['extra_visits'] );
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
			'posts_per_page'         => 100,
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

		// Check if image_url array has at least 100 items, if not, we have reached the end of the images
		return $this->response( count( $image_urls ) < 100 ? true : false );
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
					'type'    => ResizeTypeProperty::FILL,
					'enlarge' => false,
					'gravity' => Position::CENTER,
				]
			);
			unset( $final_images[ $index ]['key'] );
		}

		return $this->response( $final_images );
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
				$result .= '<li>❌ ' . sprintf( /* translators: 1 is the domain name, 2 is starting anchor tag, 3 is the ending anchor tag. */__( 'The images from: %1$s are not optimized by Optimole. If you would like to do so, you can follow this: %2$sWhy Optimole does not optimize all the images from my site?%3$s.', 'optimole-wp' ), $domain, '<a target="_blank" href="https://docs.optimole.com/article/1290-how-to-optimize-images-using-optimole-from-my-domain">', '</a>' ) . '</li>';
				$status = 'log';
				continue;
			}

			if ( $processed_images > 0 ) {
				$response = wp_remote_get( $value['src'][ rand( 0, $processed_images - 1 ) ], $args );
				if ( ! is_wp_error( $response ) ) {
					$headers     = $response['headers']; // array of http header lines
					$status_code = $response['response']['code'];
					if ( $status_code === 301 ) {
						$status = 'deactivated';
						$result = '<li>❌ ' . sprintf( /* translators: 1 is starting anchor tag, 2 is the ending anchor tag. */ __( 'Your account is currently disabled due to exceeding quota and Optimole is no longer able to optimize the images. In order to fix this you will need to %1$supgrade%2$s.', 'optimole-wp' ), '<a target="_blank" href="' . esc_url( tsdk_translate_link( Optml_Admin::get_upgrade_base_link() ) ) . '">', '</a>' ) . '</li>';
						break;
					}
					if ( $status_code === 302 ) {
						if ( isset( $headers['x-redirect-o'] ) ) {
							$optimole_code = (int) $headers['x-redirect-o'];
							if ( $optimole_code === 1 ) {
								$status = 'log';
								$result .= '<li>❌ ' . sprintf( /* translators: 1 is the domain, 2 is starting anchor tag, 3 is the ending anchor tag. */ __( 'The domain: %1$s is not allowed to optimize images using your Optimole account. You can add this to the allowed list %2$shere%3$s.', 'optimole-wp' ), '<b>' . $domain . '</b>', '<a target="_blank" href="' . esc_url( tsdk_translate_link( 'https://dashboard.optimole.com/whitelist', 'query' ) ) . '">', '</a>' ) . '</li>';
							}
							if ( $optimole_code === 4 ) {
								$status = 'log';
								$result .= '<li>❌ ' . sprintf( /* translators: 1 is the domain, 2 is starting anchor tag, 3 is the ending anchor tag. */ __( 'We are not able to download the images from %1$s. Please check %2$sthis%3$s document for a more advanced guide on how to solve this.', 'optimole-wp' ), '<b>' . $domain . '</b>', '<a target="_blank" href="https://docs.optimole.com/article/1291-why-optimole-is-not-able-to-download-the-images-from-my-site">', '</a>' ) . '<br />' . '</li>';
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
	 * Get total number of images.
	 *
	 * @param WP_REST_Request $request rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function number_of_images_and_pages( WP_REST_Request $request ) {
		$action = 'offload_images';
		$refresh = false;

		if ( ! empty( $request->get_param( 'action' ) ) ) {
			$action = $request->get_param( 'action' );
		}

		if ( ! empty( $request->get_param( 'refresh' ) ) ) {
			$refresh = $request->get_param( 'refresh' );
		}

		return $this->response( Optml_Media_Offload::move_images( $action, $refresh ) );
	}

	/**
	 * Clear the offload errors from previous offload attempts.
	 *
	 * @param WP_REST_Request $request Rest request object.
	 *
	 * @return WP_REST_Response
	 */
	public function clear_offload_errors( WP_REST_Request $request ) {
		$delete_count = Optml_Media_Offload::clear_offload_errors_meta();

		return $this->response( [ 'success' => $delete_count ] );
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

	/**
	 * Insert images request.
	 *
	 * @param WP_REST_Request $request insert images rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function insert_images( WP_REST_Request $request ) {
		$images = $request->get_param( 'images' );

		if ( ! is_array( $images ) || empty( $images ) ) {
			return $this->response( [ 'error' => 'No images found' ] );
		}

		$insert = Optml_Main::instance()->dam->insert_attachments( $images );

		return $this->response( $insert );
	}

	/**
	 * Dismiss a notification (set the notification key to 'yes').
	 *
	 * @param WP_REST_Request $request the incoming request.
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_notice( WP_REST_Request $request ) {
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			return $this->response( [ 'error' => 'Invalid key' ], 'error' );
		}

		$result = update_option( $key, 'yes' );

		if ( ! $result ) {
			return $this->response( [ 'error' => 'Could not dismiss notice' ], 'error' );
		}

		return $this->response( [ 'success' => 'Notice dismissed' ] );
	}

	/**
	 * Store optimization data.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function optimizations( WP_REST_Request $request ) {
		$settings = new Optml_Settings();
		if ( ! Optml_Manager::should_load_profiler() ) {
			return $this->response( 'Optimization is not enabled', 'error' );
		}
		$time = $request->get_param( 't' );
		$hmac = $request->get_param( 'h' );
		$current_url = $request->get_param( 'pu' );
		if ( empty( $time ) || empty( $hmac ) ) {
			return $this->response( 'Missing required parameters', 'error' );
		}

		$device_type = (int) $request->get_param( 'd' );
		$above_fold_images = $request->get_param( 'a' );
		$url = $request->get_param( 'u' );
		if ( $time < time() - 300 ) {
			return $this->response( 'Invalid Signature.', 'error' );
		}
		if ( wp_hash( $url . $time . $current_url, 'nonce' ) !== $hmac ) {
			return $this->response( 'Invalid Signature.', 'error' );
		}
		$bg_selectors = $request->get_param( 'b' );
		$lcp_data = $request->get_param( 'l' );
		$crop_status = $request->get_param( 'c' );
		$origin = $request->get_header( 'origin' );
		if ( empty( $origin ) || ! is_allowed_http_origin( $origin ) ) {
			return $this->response( 'Invalid origin', 'error' );
		}
		if ( empty( $device_type ) || empty( $url ) || ! is_array( $above_fold_images ) ) {
			return $this->response( 'Missing required parameters', 'error' );
		}
		// save just the first 6 images, see https://github.com/Codeinwp/optimole-service/issues/1588#issuecomment-3357110865
		$above_fold_images = array_slice( $above_fold_images, 0, 6 );
		if ( count( $bg_selectors ) > 100 ) {
			return $this->response( 'Background selectors limit exceeded', 'error' );
		}
		if ( $url === Profile::PLACEHOLDER ) {
			return $this->response( 'Missing profile parameters', 'error' );
		}

		$current_selectors = array_values( Optml_Lazyload_Replacer::get_background_lazyload_selectors() );
		$sanitized_selectors = [];
		foreach ( $bg_selectors as $selector => $above_fold_bg_selectors ) {
			if ( ! in_array( $selector, $current_selectors, true ) ) {
				return $this->response( 'Invalid background selector', 'error' );
			}
			if ( count( $above_fold_bg_selectors ) > 100 ) {
				return $this->response( 'Above fold background selectors limit exceeded', 'error' );
			}
			$selector = strip_tags( $selector );
			$sanitized_selectors[ $selector ] = [];
			foreach ( $above_fold_bg_selectors as $above_fold_bg_selector => $bg_urls ) {
				if ( count( $bg_urls ) > 3 ) {
					return $this->response( 'Background URLs limit exceeded', 'error' );
				}
				$sanitized_selectors[ $selector ][ strip_tags( $above_fold_bg_selector ) ] = array_filter(
					array_map( 'sanitize_url', array_values( $bg_urls ) ),
					function ( $url ) {
						// we ignore urls that are not from our service
						return strpos( $url, Optml_Config::$service_url ) === 0;
					}
				);
			}
		}
		$missing_dimensions = $request->get_param( 'm' );
		$sanitized_missing_dimensions = [];
		if ( ! empty( $missing_dimensions ) ) {
			foreach ( $missing_dimensions as $id => $dimension ) {
				$sanitized_missing_dimensions[ intval( $id ) ] = [ 'w' => intval( $dimension['w'] ), 'h' => intval( $dimension['h'] ) ];
			}
		}
		$missing_srcsets = $request->get_param( 's' );
		$sanitized_missing_srcsets = [];
		if ( ! empty( $missing_srcsets ) ) {
			foreach ( $missing_srcsets as $id => $srcset ) {
				foreach ( $srcset as $size ) {
					if ( ! isset( $size['w'], $size['h'], $size['d'], $size['s'], $size['b'] ) ) {
						continue;
					}
					$sanitized_missing_srcsets[ intval( $id ) ][ intval( $size['w'] ) ] = [ 'h' => intval( $size['h'] ), 'w' => intval( $size['w'] ), 'd' => intval( $size['d'] ), 's' => sanitize_text_field( $size['s'] ), 'b' => intval( $size['b'] ) ];
				}
			}
		}
		$sanitized_crop_status = [];
		if ( ! empty( $crop_status ) ) {
			foreach ( $crop_status as $id => $crop ) {
				$sanitized_crop_status[ intval( $id ) ] = (bool) $crop;
			}
		}
		$sanitized_lcp_data = [];
		if ( ! empty( $lcp_data ) ) {
			$sanitized_lcp_data['imageId'] = sanitize_text_field( $lcp_data['i'] ?? '' );
			$sanitized_lcp_data['bgSelector'] = sanitize_text_field( $lcp_data['s'] ?? '' );
			if ( count( $lcp_data['u'] ?? [] ) > 3 ) {
				return $this->response( 'LCP Background URLs limit exceeded', 'error' );
			}
			$sanitized_lcp_data['bgUrls'] = array_filter(
				array_map( 'sanitize_url', array_values( $lcp_data['u'] ?? [] ) ),
				function ( $url ) {
					// we ignore urls that are not from our service
					return strpos( $url, Optml_Config::$service_url ) === 0;
				}
			);
			$sanitized_lcp_data['type'] = empty( $sanitized_lcp_data['imageId'] ) ? 'bg' : 'img';
		}

		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'Storing profile data: ' . $url . ' - ' . $device_type . ' - ' . print_r( $above_fold_images, true ) . print_r( $sanitized_selectors, true ) . print_r( $sanitized_lcp_data, true ) . print_r( $sanitized_missing_dimensions, true ) . print_r( $sanitized_missing_srcsets, true ) . print_r( $sanitized_crop_status, true ) );
		}
		$profile = new Profile();
		$profile->store( $url, $device_type, $above_fold_images, $sanitized_selectors, $sanitized_lcp_data, $sanitized_missing_dimensions, $sanitized_missing_srcsets, $sanitized_crop_status );

		if ( $profile->exists_all( $url ) ) {
			/**
			 * Clear cache when storing profile data.
			 *
			 * @var string $url The url to clear the cache for.
			 */
			do_action( 'optml_clear_cache', $current_url );
		}
		return $this->response( 'Optimization data stored successfully' );
	}

	/**
	 * Method to register above fold data routes.
	 */
	public function register_optimization_routes() {
		foreach ( self::$rest_routes['optimization_routes'] as $route => $details ) {
			$this->reqister_route( $route, $details[0], $details['args'], $details['permission_callback'] );
		}
	}

	/**
	 * Move image.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function move_image( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$action = $request->get_param( 'action' );

		if ( $request->get_param( 'status' ) === 'start' ) {
			try {
				Optml_Media_Offload::instance()->move_single_image( $action === 'offload_image' ? 'offload_images' : 'rollback_images', $id );
			} catch ( Exception $e ) {
				return $this->response( strip_tags( $e->getMessage() ), 'error' );
			}
		}

		$meta = wp_get_attachment_metadata( $id );
		$file = $meta['file'];
		$result = 'not_moved';
		if ( $action === 'offload_image' ) {
			$result = Optml_Media_Offload::is_uploaded_image( $file ) ? 'moved' : 'not_moved';
		}

		if ( $action === 'rollback_image' ) {
			$result = ! Optml_Media_Offload::is_uploaded_image( $file ) ? 'moved' : 'not_moved';
		}

		return $this->response( [ $id,$action ], $result );
	}
}
