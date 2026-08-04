<?php
/**
 * WordPress unit test plugin.
 *
 * Covers the `optimole_wp_logger_data` filter used by the SDK logger cron
 * (`optimole_wp_log_activity`) when no settings have been stored yet.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Logger_Data extends WP_UnitTestCase {

	public function tearDown(): void {
		parent::tearDown();

		// The rollback restored the option, refresh the static settings cache from it.
		new Optml_Settings();
	}

	/**
	 * Raw settings must be an array even when the option was never stored.
	 */
	public function test_get_raw_settings_returns_array_when_option_missing() {
		delete_option( OPTML_NAMESPACE . '_settings' );

		$raw_settings = ( new Optml_Settings() )->get_raw_settings();

		$this->assertIsArray( $raw_settings );
		$this->assertSame( [], $raw_settings );
	}

	/**
	 * Raw settings must be an array even when the option holds a non-array value.
	 */
	public function test_get_raw_settings_returns_array_when_option_is_not_an_array() {
		update_option( OPTML_NAMESPACE . '_settings', 'corrupted' );

		$this->assertSame( [], ( new Optml_Settings() )->get_raw_settings() );
	}

	/**
	 * `add_settings` must not fatal when there is nothing stored in the database.
	 */
	public function test_add_settings_with_missing_option() {
		delete_option( OPTML_NAMESPACE . '_settings' );

		$data = Optml_Main::add_settings( [ 'foo' => 'bar' ] );

		$this->assertIsArray( $data );
		$this->assertSame( [ 'foo' => 'bar' ], $data );
	}

	/**
	 * Secrets are never sent along with the logger data.
	 */
	public function test_add_settings_strips_secrets() {
		update_option(
			OPTML_NAMESPACE . '_settings',
			[
				'api_key'      => 'secret-key',
				'service_data' => [ 'cdn_key' => 'key', 'cdn_secret' => 'secret' ],
				'quality'      => 'auto',
			]
		);

		$data = Optml_Main::add_settings( [] );

		$this->assertArrayNotHasKey( 'api_key', $data );
		$this->assertArrayNotHasKey( 'service_data', $data );
		$this->assertSame( 'auto', $data['quality'] );
	}

	/**
	 * The logger cron collects its payload through this filter, it must survive a site
	 * where Optimole settings were never saved.
	 */
	public function test_logger_data_filter_without_stored_settings() {
		delete_option( OPTML_NAMESPACE . '_settings' );

		$this->assertNotFalse( has_filter( 'optimole_wp_logger_data', [ 'Optml_Main', 'add_settings' ] ) );
		$this->assertIsArray( apply_filters( 'optimole_wp_logger_data', [] ) );
	}
}
