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
					'images.pexels.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlj0r5i75kb0.i.optimole.com/w:auto/h:auto/q:mauto/f:best/https://images.pexels.com/photos/19118634/pexels-photo-19118634/free-photo-of-arhitect-arhitectura-turism-calatorie.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
									1 => 'https://mlj0r5i75kb0.i.optimole.com/w:auto/h:auto/q:mauto/f:best/https://images.pexels.com/photos/13644908/pexels-photo-13644908.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
								),
						),
					'odd-octopus-v7m5j.instawp.xyz' =>
						array (
							'ignoredUrls' => 2,
							'src' =>
								array (
									0 => 'https://mlj0r5i75kb0.i.optimole.com/w:703/h:1024/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/szm-4-b0o_zO9fkeI-unsplash.jpg',
									1 => 'https://mlj0r5i75kb0.i.optimole.com/w:1024/h:683/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/wesley-tingey-kVmc07SPm_A-unsplash.jpg',
									3 => 'https://mlj0r5i75kb0.i.optimole.com/w:724/h:1024/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/simone-hutsch-5oYbG-sEImY-unsplash.jpg'
								),
						),
					'media.gettyimages.com' =>
						array (
							'ignoredUrls' => 1,
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
					'images.pexels.com' =>
						array (
							'src' =>
								array (
									0 => 'https://mlj0r5i75kb0.i.optimole.com/w:auto/h:auto/q:mauto/f:best/https://images.pexels.com/photos/19118634/pexels-photo-19118634/free-photo-of-arhitect-arhitectura-turism-calatorie.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
									1 => 'https://mlj0r5i75kb0.i.optimole.com/w:auto/h:auto/q:mauto/f:best/https://images.pexels.com/photos/13644908/pexels-photo-13644908.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
								),
						),
					'odd-octopus-v7m5j.instawp.xyz' =>
						array (
							'ignoredUrls' => 2,
							'src' =>
								array (
									0 => 'https://mlj0r5i75kb0.i.optimole.com/w:703/h:1024/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/szm-4-b0o_zO9fkeI-unsplash.jpg',
									1 => 'https://mlj0r5i75kb0.i.optimole.com/w:1024/h:683/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/wesley-tingey-kVmc07SPm_A-unsplash.jpg',
									3 => 'https://mlj0r5i75kb0.i.optimole.com/w:724/h:1024/q:mauto/f:best/https://odd-octopus-v7m5j.instawp.xyz/wp-content/uploads/2023/11/simone-hutsch-5oYbG-sEImY-unsplash.jpg'
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
