<?php
/**
 * The daily sync reconciles the offload limit warning with the account usage.
 *
 * @package Optimole-WP
 */

/**
 * Class Test_Offload_Limit_Sync.
 */
class Test_Offload_Limit_Sync extends WP_UnitTestCase {
	/**
	 * Account details the mocked API returns.
	 *
	 * @var array<string, mixed>
	 */
	private $account = [];

	/**
	 * Set up a connected site with a limit warning from an earlier upload.
	 */
	public function setUp(): void {
		parent::setUp();

		$settings = new Optml_Settings();
		$settings->update( 'api_key', 'test-key' );
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'test123',
				'cdn_secret' => '12345',
				'whitelist'  => [ 'example.com' ],
			]
		);
		$settings->update( 'offload_limit_reached', 'enabled' );
		$settings->update( 'offload_limit', 50000 );

		add_filter( 'pre_http_request', [ $this, 'mock_account_details' ], 10, 3 );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', [ $this, 'mock_account_details' ], 10 );
		parent::tearDown();
	}

	/**
	 * Answer the account details request with $this->account.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt.
	 * @param array                $args    Request args.
	 * @param string               $url     Request URL.
	 * @return false|array|WP_Error
	 */
	public function mock_account_details( $preempt, $args, $url ) {
		if ( strpos( $url, 'optml/v2/account/details' ) === false ) {
			return $preempt;
		}

		return [
			'headers'  => [],
			'body'     => wp_json_encode(
				[
					'code' => 200,
					'data' => array_merge(
						[
							'cdn_key'    => 'test123',
							'cdn_secret' => '12345',
							'whitelist'  => [ 'example.com' ],
							'status'     => 'active',
						],
						$this->account
					),
				]
			),
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
		];
	}

	/**
	 * Usage below the limit, for example after duplicate sites were removed, clears the warning.
	 */
	public function test_daily_sync_clears_the_warning_when_usage_is_below_the_limit() {
		$this->account = [
			'offloaded_images' => 1181,
			'offload_limit'    => 20000,
		];

		Optml_Main::instance()->admin->daily_sync();

		$settings = new Optml_Settings();
		$this->assertFalse( $settings->is_offload_limit_reached() );
		$this->assertSame( 20000, (int) $settings->get( 'offload_limit' ) );
	}

	/**
	 * An account still at its limit keeps the warning.
	 */
	public function test_daily_sync_keeps_the_warning_at_the_limit() {
		$this->account = [
			'offloaded_images' => 20000,
			'offload_limit'    => 20000,
		];

		Optml_Main::instance()->admin->daily_sync();

		$settings = new Optml_Settings();
		$this->assertTrue( $settings->is_offload_limit_reached() );
		$this->assertSame( 20000, (int) $settings->get( 'offload_limit' ) );
	}

	/**
	 * Without usage details in the response, nothing changes.
	 */
	public function test_daily_sync_without_usage_details_keeps_the_warning() {
		$this->account = [];

		Optml_Main::instance()->admin->daily_sync();

		$settings = new Optml_Settings();
		$this->assertTrue( $settings->is_offload_limit_reached() );
		$this->assertSame( 50000, (int) $settings->get( 'offload_limit' ) );
	}
}
