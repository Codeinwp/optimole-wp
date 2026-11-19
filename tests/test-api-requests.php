<?php
/**
 * Tests for the requests Optml_Api sends.
 *
 * @package Optimole-WP
 */

/**
 * Class Test_Api_Requests.
 */
class Test_Api_Requests extends WP_UnitTestCase {

	/**
	 * The arguments of the requests sent during a test, in order.
	 *
	 * @var list<array<string, mixed>>
	 */
	private $requests = [];

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->requests = [];
		add_filter( 'pre_http_request', [ $this, 'capture_request' ], 10, 2 );
	}

	/**
	 * Record the request and answer it without sending anything.
	 *
	 * @param false|array<string, mixed>|WP_Error $response The short-circuit value.
	 * @param array<string, mixed>                $args     The request arguments.
	 *
	 * @return array<string, mixed>
	 */
	public function capture_request( $response, $args ) {
		$this->requests[] = $args;

		return [
			'headers'  => [],
			'body'     => '{}',
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
			'filename' => null,
		];
	}

	/**
	 * Every request to the Optimole API checks the server certificate.
	 */
	public function test_requests_verify_the_server_certificate(): void {
		$api = new Optml_Api();

		$api->get_user_data( 'test-key' );
		$api->call_onboard_api( [ 'https://example.org/a.jpg' ] );
		$api->log_offload_error( [ 'response' => [ 'code' => 500 ] ] );

		$this->assertCount( 3, $this->requests );
		foreach ( $this->requests as $args ) {
			$this->assertTrue( $args['sslverify'] );
		}
	}
}
