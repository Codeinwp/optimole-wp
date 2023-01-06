<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Generic.
 */
class Test_Rest extends WP_UnitTestCase
{
	private $server;

	private $namespaced_route = '/' . OPTML_NAMESPACE . '/v1/check_redirects';

	public function setUp() : void
	{
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		wp_set_current_user(  $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		Optml_Main::instance();
		do_action( 'rest_api_init' );
		
	}
	public function test_register_route() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->namespaced_route, $routes );
	}
	public function test_endpoint_config() {
		$the_route = $this->namespaced_route;
		$routes = $this->server->get_routes();
		foreach( $routes as $route => $route_config ) {
			if( 0 === strpos( $the_route, $route ) ) {
				$this->assertTrue( is_array( $route_config ) );
				foreach( $route_config as  $endpoint ) {
					$this->assertArrayHasKey( 'callback', $endpoint );
					$this->assertArrayHasKey( 0, $endpoint[ 'callback' ] );
					$this->assertArrayHasKey( 1, $endpoint[ 'callback' ] );
					$this->assertTrue( is_callable( array( $endpoint[ 'callback' ][0], $endpoint[ 'callback' ][1] ) ) );
				}
			}
		}
	}
	public function test_response_is_log () {
		$request_body = array (
			'images' =>
				array (
					'image.shutterstock.com' =>
						array (
							'ignoredUrls' => 1,
						),
					'png.pngtree.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/Dda1BC0-8GsrmkKn/w:auto/h:auto/q:eco/https://png.pngtree.com/png-clipart/20190515/original/pngtree-concept-of-attracting-customers-and-clients-to-business-large-group-png-image_3659843.jpg',
								),
						),
					'images.pexels.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/DCrgOXo-2HhtvELK/w:auto/h:auto/q:eco/https://images.pexels.com/photos/4153187/pexels-photo-4153187.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
									1 => 'https://mlynllm40tdp.i.optimole.com/DCrgOXo-Q0w8E10z/w:auto/h:auto/q:eco/https://images.pexels.com/photos/4153629/pexels-photo-4153629.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500',
								),
						),
					'media.gettyimages.com' =>
						array (
							'ignoredUrls' => 1,
						),
					'3c05b66e.ngrok.io' =>
						array (
							'ignoredUrls' => 2,
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-KDi_XGaC/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download-1-1.jpeg',
									1 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-l1pEEP6v/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download-1.jpeg',
									2 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-2UxK9Uvh/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download.jpeg',
								),
						),
				),
		);

		$request_body = json_encode($request_body);
		$request = new WP_REST_Request( 'POST', $this->namespaced_route );
		$request->set_header( 'X-WP-Nonce' , wp_create_nonce( 'wp_rest' ) );
		$request->set_header( 'Content-Type' ,'application/json' );

		$request->set_body( $request_body );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'code', $data );
		$this->assertEquals( 'log', $data[ 'code' ] );
		$this->assertMatchesRegularExpression( '/media.gettyimages.com/' , $data ['data'] );
		$this->assertMatchesRegularExpression( '/image.shutterstock.com/', $data ['data'] );
	}
	public function test_response_is_ok () {
		$request_body = array (
			'images' =>
				array (
					'png.pngtree.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/Dda1BC0-8GsrmkKn/w:auto/h:auto/q:eco/https://png.pngtree.com/png-clipart/20190515/original/pngtree-concept-of-attracting-customers-and-clients-to-business-large-group-png-image_3659843.jpg',
								),
						),
					'images.pexels.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/DCrgOXo-2HhtvELK/w:auto/h:auto/q:eco/https://images.pexels.com/photos/4153187/pexels-photo-4153187.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260',
									1 => 'https://mlynllm40tdp.i.optimole.com/DCrgOXo-Q0w8E10z/w:auto/h:auto/q:eco/https://images.pexels.com/photos/4153629/pexels-photo-4153629.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500',
								),
						),
					'3c05b66e.ngrok.io' =>
						array (
							'ignoredUrls' => 2,
							'src' =>
								array (
									0 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-KDi_XGaC/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download-1-1.jpeg',
									1 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-l1pEEP6v/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download-1.jpeg',
									2 => 'https://mlynllm40tdp.i.optimole.com/k4SdJQQ-2UxK9Uvh/w:auto/h:auto/q:eco/http://3c05b66e.ngrok.io/wp-content/uploads/2020/03/download.jpeg',
								),
						),
				),
		);
		$request_body = json_encode($request_body);
		$request = new WP_REST_Request( 'POST', $this->namespaced_route );
		$request->set_header( 'X-WP-Nonce' , wp_create_nonce( 'wp_rest' ) );
		$request->set_header( 'Content-Type' ,'application/json' );
		$request->set_body( $request_body );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'code', $data );
		$this->assertEquals( 'ok', $data[ 'code' ] );
	}
}
