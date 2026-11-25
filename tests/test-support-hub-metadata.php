<?php
/**
 * Tests for the Support Hub metadata opt-in.
 *
 * @package optimole-wp
 */
class Test_Support_Hub_Metadata extends WP_UnitTestCase {

	/**
	 * @var mixed Original service_data, restored in teardown (settings are statically cached).
	 */
	private $original_service_data;

	public function set_up() {
		parent::set_up();
		$this->original_service_data = Optml_Main::instance()->admin->settings->get( 'service_data' );
	}

	public function tear_down() {
		Optml_Main::instance()->admin->settings->update( 'service_data', $this->original_service_data );
		parent::tear_down();
	}

	/**
	 * is_pro follows the connected service plan, so paid sites are not tagged free.
	 */
	public function test_is_pro_follows_the_connected_plan() {
		$settings = Optml_Main::instance()->admin->settings;

		$settings->update( 'service_data', array( 'cdn_key' => 'k', 'cdn_secret' => 's', 'plan' => 'business' ) );
		$meta = apply_filters( 'optimole_wp_support_hub_metadata', array() );
		$this->assertTrue( $meta['is_pro'], 'a connected paid plan is pro' );
		$this->assertSame( 'friends@optimole.com', $meta['support_email'] );
		$this->assertSame( array( 'book-alt', 'groups' ), array_column( $meta['links'], 'icon' ) );
		$this->assertStringStartsWith( 'https://docs.optimole.com/?', $meta['links'][0]['url'] );
		$this->assertStringContainsString( 'utm_campaign=supporthub', $meta['links'][0]['url'] );
		$this->assertSame( 'https://wordpress.org/support/plugin/optimole-wp/', $meta['links'][1]['url'] );

		$settings->update( 'service_data', array( 'cdn_key' => 'k', 'cdn_secret' => 's', 'plan' => 'free' ) );
		$this->assertFalse( apply_filters( 'optimole_wp_support_hub_metadata', array() )['is_pro'], 'a free plan is not pro' );
	}
}
