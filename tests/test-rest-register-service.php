<?php
/**
 * Tests for the register_service REST handler.
 *
 * @package Optimole-WP
 */

/**
 * Class Test_Rest_Register_Service.
 *
 * @phpstan-type HttpReply array{headers: array{}, body: string, response: array{code: int, message: string}, cookies: array{}, filename: null}
 */
class Test_Rest_Register_Service extends WP_UnitTestCase {
	/**
	 * Faked account API reply for the current test.
	 *
	 * @var HttpReply|null
	 */
	private $reply = null;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ], 10, 3 );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ], 10 );
		$this->reply = null;
		parent::tearDown();
	}

	/**
	 * Answer account registration requests with the faked reply.
	 *
	 * @param false|array<string, mixed>|WP_Error $preempt Whether to preempt.
	 * @param array<string, mixed>                $args    Request args.
	 * @param string                              $url     Request URL.
	 * @return false|array<string, mixed>|WP_Error
	 */
	public function filter_pre_http_request( $preempt, array $args, string $url ) {
		if ( $this->reply === null || strpos( $url, 'complete_register_remote' ) === false ) {
			return $preempt;
		}
		return $this->reply;
	}

	/**
	 * Build a faked HTTP reply.
	 *
	 * @param int    $status HTTP status code.
	 * @param string $body   Response body.
	 * @return HttpReply
	 */
	private static function reply( int $status, string $body ): array {
		return [
			'headers'  => [],
			'body'     => $body,
			'response' => [
				'code'    => $status,
				'message' => get_status_header_desc( $status ),
			],
			'cookies'  => [],
			'filename' => null,
		];
	}

	/**
	 * Call register_service and return the REST payload.
	 *
	 * @param array<string, string> $params Request params.
	 * @return array{data: null, message: string, code: string}
	 */
	private function register( array $params = [] ): array {
		$request = new WP_REST_Request( 'POST', '/optml/v1/register_service' );
		$request->set_param( 'email', 'qa@example.test' );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = ( new Optml_Rest() )->register_service( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		return $response->get_data();
	}

	/**
	 * API replies that the client cannot turn into a result or a WP_Error.
	 *
	 * @return array<string, array{0: HttpReply}>
	 */
	public function unusable_replies(): array {
		return [
			'empty body'             => [ self::reply( 502, '' ) ],
			'HTML proxy page'        => [ self::reply( 502, '<html><body><h1>502 Bad Gateway</h1></body></html>' ) ],
			'JSON without code'      => [ self::reply( 200, '{"message":"ok"}' ) ],
			'non-200 code, no error' => [ self::reply( 500, '{"code":500}' ) ],
		];
	}

	/**
	 * An unusable API reply returns the generic account error instead of a fatal.
	 *
	 * @dataProvider unusable_replies
	 *
	 * @param HttpReply $reply Faked API reply.
	 */
	public function test_unusable_api_reply_returns_account_error( array $reply ): void {
		$this->reply = $reply;

		$data = $this->register();

		$this->assertSame( 'error', $data['code'] );
		$this->assertSame( 'Error creating account.', $data['message'] );
	}

	/**
	 * An API error message is appended to the generic account error.
	 */
	public function test_api_error_message_is_appended(): void {
		$this->reply = self::reply( 400, '{"code":400,"error":"Some API error"}' );

		$data = $this->register();

		$this->assertSame( 'error', $data['code'] );
		$this->assertSame( 'Error creating account. Some API error', $data['message'] );
	}

	/**
	 * An unreachable site keeps its dedicated response code.
	 */
	public function test_domain_not_accessible_keeps_its_code(): void {
		$this->reply = self::reply( 400, '{"code":400,"error":"domain_not_accessible"}' );

		$data = $this->register();

		$this->assertSame( 'domain_not_accessible', $data['code'] );
		$this->assertStringContainsString( 'Optimole is having trouble reaching your website', $data['message'] );
	}

	/**
	 * An already registered email keeps its dedicated response code.
	 */
	public function test_registered_email_keeps_its_code(): void {
		$this->reply = self::reply( 400, '{"code":400,"error":"This email address is already registered."}' );

		$data = $this->register();

		$this->assertSame( 'email_registered', $data['code'] );
	}

	/**
	 * Auto connect clears the stored email even when registration fails.
	 */
	public function test_auto_connect_clears_stored_email_on_failure(): void {
		update_option( Optml_Settings::OPTML_USER_EMAIL, 'qa@example.test' );
		$this->reply = self::reply( 502, '' );

		$this->register( [ 'auto_connect' => 'true' ] );

		$this->assertFalse( get_option( Optml_Settings::OPTML_USER_EMAIL ) );
	}
}
