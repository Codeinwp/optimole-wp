<?php
/**
 * Tests for the page profiler REST endpoint payload validation.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

use OptimoleWP\PageProfiler\Profile;

/**
 * Class Test_Rest_Optimizations.
 */
class Test_Rest_Optimizations extends WP_UnitTestCase {

	const PROFILE_ID = 'rest-optimizations-profile';

	const WATCHER = '[style*="background-image:url("]';

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
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'lazyload_type', 'viewport' );
		$settings->update( 'bg_replacer', 'enabled' );
		Optml_Lazyload_Replacer::instance()->init();
		$this->reset_background_selectors();
		// The page capture buffer is not under test; hooks are restored by WP_UnitTestCase.
		remove_action( 'rest_api_init', [ Optml_Manager::instance(), 'process_template_redirect_content' ], PHP_INT_MIN );
	}

	/**
	 * Clean up the test environment after each test.
	 */
	public function tearDown(): void {
		$this->reset_background_selectors();
		Profile::reset_current_profile();
		parent::tearDown();
	}

	/**
	 * Payloads without usable background URLs are stored instead of failing.
	 *
	 * @dataProvider payloads_without_background_urls
	 *
	 * @param array<string, mixed>                       $fields      Fields added to the signed request.
	 * @param array<string, array<string, list<string>>> $expected_bg Background selectors expected in the profile.
	 */
	public function test_payload_without_background_urls_is_stored( array $fields, array $expected_bg ): void {
		$response = rest_get_server()->dispatch( $this->build_request( $fields ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'success', $response->get_data()['code'] );
		$stored = ( new Profile() )->get_profile_data( self::PROFILE_ID );
		$this->assertSame( $expected_bg, $stored[ Profile::DEVICE_TYPE_DESKTOP ]['bg'] );
	}

	/**
	 * Payloads with no usable background URLs, including scalars REST coerces into URL lists.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: array<string, array<string, list<string>>>}>
	 */
	public function payloads_without_background_urls(): array {
		return [
			'no background selectors'           => [ [], [] ],
			'above-fold element with null URLs' => [ [ 'b' => [ self::WATCHER => [ '#hero' => null ] ] ], [ self::WATCHER => [ '#hero' => [] ] ] ],
			'background URLs string'            => [ [ 'b' => [ self::WATCHER => [ '#hero' => 'https://example.com/a.jpg' ] ] ], [ self::WATCHER => [ '#hero' => [] ] ] ],
			'LCP background URLs string'        => [ [ 'b' => [], 'l' => [ 'u' => 'https://example.com/a.jpg' ] ], [] ],
		];
	}

	/**
	 * Malformed payloads are rejected before the handler and store nothing.
	 *
	 * @dataProvider malformed_payloads
	 *
	 * @param array<string, mixed> $fields Fields added to the signed request.
	 */
	public function test_malformed_payload_is_rejected( array $fields ): void {
		$response = rest_get_server()->dispatch( $this->build_request( $fields ) );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'rest_invalid_param', $response->get_data()['code'] );
		$this->assertFalse( ( new Profile() )->get_profile_data( self::PROFILE_ID )[ Profile::DEVICE_TYPE_DESKTOP ] );
	}

	/**
	 * Payload fields with a type the handler cannot process.
	 *
	 * @return array<string, array{0: array<string, mixed>}>
	 */
	public function malformed_payloads(): array {
		return [
			'background selectors null'       => [ [ 'b' => null ] ],
			'background selectors string'     => [ [ 'b' => 'selectors' ] ],
			'selector entry null'             => [ [ 'b' => [ self::WATCHER => null ] ] ],
			'selector entry string'           => [ [ 'b' => [ self::WATCHER => 'hero' ] ] ],
			'background URL nested array'     => [ [ 'b' => [ self::WATCHER => [ '#hero' => [ [ 'https://example.com/a.jpg' ] ] ] ] ] ],
			'LCP background URL nested array' => [ [ 'b' => [], 'l' => [ 'u' => [ [ 'https://example.com/a.jpg' ] ] ] ] ],
			'missing dimensions entry string' => [ [ 'b' => [], 'm' => [ 12 => 'size' ] ] ],
		];
	}

	/**
	 * A well-formed payload with a selector outside the watcher list is still refused.
	 */
	public function test_unknown_background_selector_is_rejected(): void {
		$response = rest_get_server()->dispatch( $this->build_request( [ 'b' => [ '.not-a-watcher' => [ '#hero' => null ] ] ] ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( [ 'data' => 'Invalid background selector', 'code' => 'error' ], $response->get_data() );
		$this->assertFalse( ( new Profile() )->get_profile_data( self::PROFILE_ID )[ Profile::DEVICE_TYPE_DESKTOP ] );
	}

	/**
	 * Build a signed optimizations request as the frontend profiler sends it.
	 *
	 * @param array<string, mixed> $fields Fields added to the required ones.
	 */
	private function build_request( array $fields ): WP_REST_Request {
		$time    = time();
		$page    = home_url( '/' );
		$request = new WP_REST_Request( 'POST', '/' . OPTML_NAMESPACE . '/v1/optimizations' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_header( 'Origin', home_url() );
		$request->set_body(
			wp_json_encode(
				array_merge(
					[
						'd'  => Profile::DEVICE_TYPE_DESKTOP,
						'a'  => [],
						'u'  => self::PROFILE_ID,
						't'  => $time,
						'h'  => wp_hash( self::PROFILE_ID . $time . $page, 'nonce' ),
						'pu' => $page,
					],
					$fields
				)
			)
		);

		return $request;
	}

	/**
	 * Reset the cached background lazyload selectors.
	 */
	private function reset_background_selectors(): void {
		$property = new ReflectionProperty( Optml_Lazyload_Replacer::class, 'background_lazyload_selectors' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}
