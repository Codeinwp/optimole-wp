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
			'token time with trailing text'   => [ [ 't' => time() . 'h' ] ],
			'signature list'                  => [ [ 'h' => [ 'signature' ] ] ],
			'page URL list'                   => [ [ 'pu' => [ 'page' ] ] ],
		];
	}

	/**
	 * Only srcset entries with a width descriptor are stored, and a malformed descriptor never fails the request.
	 */
	public function test_only_width_srcset_descriptors_are_stored(): void {
		$entry    = [ 'h' => 300, 'd' => 1, 'b' => 768 ];
		$response = rest_get_server()->dispatch(
			$this->build_request(
				[
					's' => [
						12 => [
							$entry + [ 'w' => 400, 's' => '400w' ],
							$entry + [ 'w' => 333, 's' => '2x data-opt-id=Q onload=alert(1) z' ],
							$entry + [ 'w' => 500, 's' => '500x' ],
							$entry + [ 'w' => 600, 's' => [ '600w' ] ],
						],
					],
				]
			)
		);

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'success', $response->get_data()['code'] );
		Profile::set_current_profile_id( self::PROFILE_ID );
		$profile = new Profile();
		$profile->set_current_profile_data();
		$this->assertSame( [ 400 => [ 'h' => 300, 'w' => 400, 'd' => 1, 's' => '400w', 'b' => 768 ] ], $profile->get_missing_srcsets( 12 ) );
	}

	/**
	 * A signature is only valid for the profile ID, time and page URL it was issued for.
	 */
	public function test_signature_is_bound_to_each_signed_field(): void {
		$time = time();
		$page = home_url( '/' );

		// The same characters, with the last one of the profile ID moved to the front of the time.
		$response = rest_get_server()->dispatch(
			$this->build_request(
				[
					'u' => 'split-profile-',
					't' => (int) ( '9' . $time ),
					'h' => Profile::sign( 'split-profile-9', $time, $page ),
				]
			)
		);

		$this->assertSame( 'error', $response->get_data()['code'] );
		$this->assertFalse( ( new Profile() )->get_profile_data( 'split-profile-' )[ Profile::DEVICE_TYPE_DESKTOP ] );
	}

	/**
	 * A profile that is already stored is not replaced by a later submission.
	 */
	public function test_stored_profile_is_not_replaced(): void {
		rest_get_server()->dispatch( $this->build_request( [ 'a' => [ 11 ] ] ) );
		$response = rest_get_server()->dispatch( $this->build_request( [ 'a' => [ 22 ] ] ) );

		$this->assertSame( 'error', $response->get_data()['code'] );
		$this->assertSame( [ 11 => true ], ( new Profile() )->get_profile_data( self::PROFILE_ID )[ Profile::DEVICE_TYPE_DESKTOP ]['af'] );
	}

	/**
	 * Only background URLs on the service host are stored; a longer host that starts with it is another host.
	 */
	public function test_background_urls_are_kept_for_the_service_host_only(): void {
		$service_url = Optml_Config::$service_url;
		$this->assertNotSame( '', $service_url );

		rest_get_server()->dispatch(
			$this->build_request(
				[
					'l' => [ 'u' => [ $service_url . '.example.net/a.jpg', $service_url . '/b.jpg' ] ],
				]
			)
		);

		$stored = ( new Profile() )->get_profile_data( self::PROFILE_ID )[ Profile::DEVICE_TYPE_DESKTOP ];
		$this->assertSame( [ $service_url . '/b.jpg' ], array_values( $stored['lcp']['bgUrls'] ) );
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
						'h'  => Profile::sign( self::PROFILE_ID, $time, $page ),
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
