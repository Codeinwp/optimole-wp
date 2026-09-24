<?php
/**
 * Regression tests: dashboard widget leaking account credentials.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

/**
 * Class Test_Dashboard_Widget.
 */
class Test_Dashboard_Widget extends WP_UnitTestCase {

	/**
	 * Widget handle.
	 */
	const HANDLE = OPTML_NAMESPACE . '-dashboard-widget';

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		require_once ABSPATH . 'wp-admin/includes/dashboard.php';

		( new Optml_Settings() )->update(
			'service_data',
			[
				'cdn_key'                => 'test-cdn-key',
				'cdn_secret'             => 'test-cdn-secret',
				'api_key'                => 'test-api-key',
				'visitors'               => 20,
				'visitors_pretty'        => '20',
				'visitors_limit'         => 5000,
				'visitors_limit_pretty'  => '5k',
				'compression_percentage' => 42.5,
				'traffic'                => 12.3,
			]
		);
		set_current_screen( 'dashboard' );
	}

	/**
	 * Clean up the test environment after each test.
	 */
	public function tearDown(): void {
		global $wp_meta_boxes;
		unset( $wp_meta_boxes['dashboard'] );
		wp_deregister_script( self::HANDLE );
		set_current_screen( 'front' );
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	/**
	 * Subscribers get neither the widget nor its localized account data.
	 */
	public function test_widget_hidden_from_subscriber() {
		global $pagenow, $wp_meta_boxes;
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

		$widget = new Optml_Dashboard_Widget();
		$widget->add_dashboard_widget();

		$previous_pagenow = $pagenow;
		$pagenow          = 'index.php';
		$widget->enqueue_widget();
		$pagenow = $previous_pagenow;

		$this->assertFalse( isset( $wp_meta_boxes['dashboard']['normal']['core'][ self::HANDLE ] ) );
		$this->assertFalse( wp_script_is( self::HANDLE, 'registered' ) );
	}

	/**
	 * Administrators still get the widget.
	 */
	public function test_widget_shown_to_administrator() {
		global $wp_meta_boxes;
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		( new Optml_Dashboard_Widget() )->add_dashboard_widget();

		$this->assertTrue( isset( $wp_meta_boxes['dashboard']['normal']['core'][ self::HANDLE ] ) );
	}

	/**
	 * The localized service data only holds the stats the widget displays.
	 */
	public function test_localized_service_data_excludes_credentials() {
		$method = new ReflectionMethod( Optml_Dashboard_Widget::class, 'get_script_localization' );
		$method->setAccessible( true );

		$service_data = $method->invoke( new Optml_Dashboard_Widget() )['serviceData'];

		$this->assertEqualsCanonicalizing(
			[ 'visitors', 'visitors_pretty', 'visitors_limit', 'visitors_limit_pretty', 'compression_percentage', 'traffic' ],
			array_keys( $service_data )
		);
		$this->assertSame( 20, $service_data['visitors'] );
	}
}
