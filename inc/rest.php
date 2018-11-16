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
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/**
	 * Register rest routes.
	 */
	public function register() {

		register_rest_route(
			$this->namespace,
			'/connect',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'connect' ),
					'args'                => array(
						'api_key' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/register',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'register_service' ),
					'args'                => array(
						'email' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/disconnect',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'disconnect' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/update_option',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'update_option' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/poll_optimized_images',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'poll_optimized_images' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/images-sample-rate',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'get_sample_rate' ),
				),
			)
		);
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
		$request = new Optml_Api();
		$data    = $request->get_user_data( $api_key );
		if ( $data === false ) {
			wp_send_json_error( __( 'Can not connect to optimole service', 'optimole-wp' ) );
		}
		$settings = new Optml_Settings();
		$settings->update( 'service_data', $data );
		$settings->update( 'api_key', $api_key );

		return $this->response( $data );
	}

	/**
	 * Wrapper for api response.
	 *
	 * @param array $data data from api.
	 *
	 * @return WP_REST_Response
	 */
	private function response( $data ) {
		return new WP_REST_Response( array( 'data' => $data, 'code' => 'success' ), 200 );
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
			return new WP_Error( 'error', 'Error creating account.' );
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

		$accepted_mimes = array( 'image/jpeg', 'image/png' );
		$args           = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'number'         => '5',
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'post_mime_type' => $accepted_mimes,
		);
		$image_result   = new WP_Query( $args );
		if ( empty( $image_result->posts ) ) {
			return $this->response( array() );
		}

		$image              = array(
			'id' => $image_result->posts [ array_rand( $image_result->posts, 1 ) ],
		);
		$original_image_url = wp_get_attachment_image_url( $image['id'], 'full' );

		$metadata = wp_get_attachment_metadata( $image['id'] );

		$width  = 'auto';
		$height = 'auto';
		$size   = 'full';
		if ( isset( $metadata['sizes'] ) && isset( $metadata['sizes'][ $size ] ) ) {
			$width  = $metadata['sizes'][ $size ]['width'];
			$height = $metadata['sizes'][ $size ]['height'];
		}

		$image['original'] = wp_get_attachment_image_url( $image['id'], $size );

		remove_filter( 'optml_dont_replace_url', '__return_true' );

		$image['optimized'] = apply_filters(
			'optml_replace_image',
			$original_image_url,
			array(
				'width'   => $width,
				'height'  => $height,
				'quality' => $request->get_param( 'quality' ),
			)
		);
		$optimized          = wp_remote_get(
			$image['optimized'],
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'text/html,application/xhtml+xml,image/webp,image/apng ',
				),
			)
		);

		$original = wp_remote_get( $image['original'] );

		$image['optimized_size'] = (int) wp_remote_retrieve_header( $optimized, 'content-length' );
		$image['original_size']  = (int) wp_remote_retrieve_header( $original, 'content-length' );

		return $this->response( $image );
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
			return $this->response( array() );
		}

		$final_images = array_splice( $images['list'], 0, 10 );

		return $this->response( $final_images );
	}

	/**
	 * Update options method.
	 *
	 * @param WP_REST_Request $request option update rest request.
	 */
	public function update_option( WP_REST_Request $request ) {
		$new_settings = $request->get_param( 'settings' );

		if ( empty( $new_settings ) ) {
			wp_send_json_error( 'No option key set.' );
		}

		$settings = new Optml_Settings();
		// TODO Move validation in settings model.
		$sanitized = array();
		foreach ( $new_settings as $key => $value ) {
			switch ( $key ) {
				case 'admin_bar_item':
				case 'lazyload':
				case 'image_replacer':
					$sanitized_value = ( $value === 'enabled' || $value === 'disabled' ) ? $value : 'enabled';
					break;
				case 'max_width':
				case 'max_height':
					$sanitized_value = absint( $value );
					if ( $sanitized_value < 100 ) {
						$sanitized_value = 100;
					}
					if ( $sanitized_value > 5000 ) {
						$sanitized_value = 5000;
					}

					break;
				case 'quality':
					$sanitized_value = ( $value === 'low' || $value === 'medium' || $value === 'auto' || $value === 'high' ) ? $value : 'auto';
					break;
				default:
					$sanitized_value = '';
					break;
			}
			if ( empty( $sanitized_value ) ) {
				continue;
			}
			$sanitized[ $key ] = $sanitized_value;
			$settings->update( $key, $sanitized_value );
		}

		return $this->response( $sanitized );
	}

}
