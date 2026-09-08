<?php
/**
 * Tests for optimized-images REST polling.
 *
 * @package Optimole-WP
 */

/**
 * Class Test_Poll_Optimized_Images.
 */
class Test_Poll_Optimized_Images extends WP_UnitTestCase {
	/**
	 * HTTP mock callback for the current test.
	 *
	 * @var callable|null
	 */
	private $http_mock;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'test123',
				'cdn_secret' => '12345',
				'whitelist'  => [ 'example.com' ],
			]
		);
		add_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ], 10, 3 );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		remove_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ], 10 );
		$this->http_mock = null;
	}

	/**
	 * Route HTTP mocks for dashboard stats/images requests.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt.
	 * @param array                $args    Request args.
	 * @param string               $url     Request URL.
	 * @return false|array|WP_Error
	 */
	public function filter_pre_http_request( $preempt, $args, $url ) {
		if ( strpos( $url, 'stats/images' ) === false ) {
			return $preempt;
		}
		if ( is_callable( $this->http_mock ) ) {
			return call_user_func( $this->http_mock, $preempt, $args, $url );
		}
		return $preempt;
	}

	/**
	 * Call poll_optimized_images and return decoded REST payload.
	 *
	 * @return array{data: mixed, code: string|int}
	 */
	private function poll() {
		$rest     = new Optml_Rest();
		$request  = new WP_REST_Request( 'GET' );
		$request->set_param( 'api_key', 'test-key' );
		$response = $rest->poll_optimized_images( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		return $response->get_data();
	}

	/**
	 * Transport WP_Error must not fatal; polling returns an empty list.
	 */
	public function test_poll_returns_empty_list_on_transport_wp_error() {
		$this->http_mock = function () {
			return new WP_Error( 'http_request_failed', 'Could not connect' );
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertSame( [], $payload['data'] );
	}

	/**
	 * Non-200 API payload with an error field becomes WP_Error in Optml_Api::request().
	 */
	public function test_poll_returns_empty_list_on_api_error_payload() {
		$this->http_mock = function () {
			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					[
						'code'  => 500,
						'error' => 'upstream failed',
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertSame( [], $payload['data'] );
	}

	/**
	 * Empty API body is documented as false and must not fatal.
	 */
	public function test_poll_returns_empty_list_on_false_api_result() {
		$this->http_mock = function () {
			return [
				'headers'  => [],
				'body'     => '',
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertSame( [], $payload['data'] );
	}

	/**
	 * Successful payload without a list is treated as empty.
	 */
	public function test_poll_returns_empty_list_when_list_missing() {
		$this->http_mock = function () {
			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					[
						'code' => 200,
						'data' => [ 'count' => 0 ],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertSame( [], $payload['data'] );
	}

	/**
	 * Successful payload with an empty list is treated as empty.
	 */
	public function test_poll_returns_empty_list_when_list_empty() {
		$this->http_mock = function () {
			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					[
						'code' => 200,
						'data' => [ 'list' => [] ],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertSame( [], $payload['data'] );
	}

	/**
	 * A valid list is returned (URLs rewritten through Optimole).
	 */
	public function test_poll_returns_images_from_list() {
		$this->http_mock = function () {
			return [
				'headers'  => [],
				'body'     => wp_json_encode(
					[
						'code' => 200,
						'data' => [
							'list' => [
								[
									'url' => 'https://example.com/photo.jpg',
									'key' => 'AbC',
								],
							],
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			];
		};

		$payload = $this->poll();
		$this->assertSame( 'success', $payload['code'] );
		$this->assertCount( 1, $payload['data'] );
		$this->assertArrayHasKey( 'url', $payload['data'][0] );
		$this->assertArrayNotHasKey( 'key', $payload['data'][0] );
		$this->assertStringContainsString( 'example.com/photo.jpg', $payload['data'][0]['url'] );
	}
}
