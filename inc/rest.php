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
			$this->namespace, 'connect', array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function ( \WP_REST_Request $request ) {

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
					'methods'             => \WP_REST_Server::CREATABLE,
					'permission_callback' => function ( \WP_REST_Request $request ) {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'disconnect' ),
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
			return new WP_Error( 'error_api', __( 'Can not connect to optimole service', 'optimole-wp' ) );
		}
		$settings = new Optml_Settings();
		$settings->update( 'service_data', $data );

		return $this->response( $data );
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