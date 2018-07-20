<?php
/**
 * Optimole Rest related actions.
 * @package     Optimole/Inc
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
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
						)
					)
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
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'enable_admin_bar_item' ),
				),
			)
		);
	}

	/**
	 * Connect to optimole service.
	 *
	 * @param WP_REST_Request $request
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
	 * Disconnect from optimole service.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function disconnect( WP_REST_Request $request ) {
		$settings = new Optml_Settings();
		$settings->reset();
		wp_send_json_success( 'Disconnected' );
	}

	/**
	 * Enable the admin bar item.
	 *
	 * @param WP_REST_Request $request
	 */
	public function enable_admin_bar_item( WP_REST_Request $request ) {
		$settings = new Optml_Settings();
		$status   = $settings->get( 'admin_bar_item' );
		if ( $status === 'enabled' ) {
			$status = 'disabled';
		} else {
			$status = 'enabled';
		}

		$settings->update( 'admin_bar_item', $status );
		wp_send_json_success( 'Admin bar item ' . $status . '.' );
	}

	/**
	 * Wrapper for api response.
	 *
	 * @param $data
	 *
	 * @return WP_REST_Response
	 */
	private function response( $data ) {
		return new WP_REST_Response( array( 'data' => $data, 'code' => 'success' ), 200 );
	}

}