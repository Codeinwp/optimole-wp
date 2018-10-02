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
			$this->namespace, '/connect', array(
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
			$this->namespace, '/register', array(
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
			$this->namespace, '/disconnect', array(
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
			$this->namespace, '/update_option', array(
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
			$this->namespace, '/poll_optimized_images', array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'poll_optimized_images' ),
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

		return $this->response( [
			'user' => $user
		] );
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
		if ( ! isset ( $images['list'] ) || empty( $images['list'] ) ) {
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
		$option_key  = $request->get_param( 'option_key' );
		$option_type = $request->get_param( 'type' );
		if ( empty( $option_key ) ) {
			wp_send_json_error( 'No option key set.' );
		}
		if ( empty( $option_type ) ) {
			wp_send_json_error( 'No option type set.' );
		}

		$accepted_types = array( 'toggle' );

		if ( ! in_array( $option_type, $accepted_types ) ) {
			wp_send_json_error( 'Invalid option type.' );
		}

		$settings = new Optml_Settings();

		switch ( $option_type ) {
			case 'toggle':
				$status = $settings->get( $option_key );
				if ( $status === 'enabled' ) {
					$settings->update( $option_key, 'disabled' );
					wp_send_json_success( $option_key . ' disabled.' );
				} else {
					$settings->update( $option_key, 'enabled' );
					wp_send_json_success( $option_key . ' enabled.' );
				}
				break;
			default:
				break;
		}
	}

}
