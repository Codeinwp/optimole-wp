<?php
/**
 * Tests for the Support Hub metadata opt-in.
 *
 * @package optimole-wp
 */
class Test_Support_Hub_Metadata extends WP_UnitTestCase {

	/**
	 * is_pro follows the connected service plan, so paid sites are not tagged free.
	 */
	public function test_is_pro_follows_the_connected_plan() {
		$settings = Optml_Main::instance()->admin->settings;

		$settings->update( 'service_data', array( 'cdn_key' => 'k', 'cdn_secret' => 's', 'plan' => 'business' ) );
		$meta = apply_filters( 'optimole_wp_support_hub_metadata', array() );
		$this->assertTrue( $meta['is_pro'], 'a connected paid plan is pro' );
		$this->assertSame( 'groups', $meta['links'][0]['icon'] );
		$this->assertNotEmpty( $meta['links'][0]['text'] );
		$this->assertNotEmpty( $meta['links'][0]['url'] );

		$settings->update( 'service_data', array( 'cdn_key' => 'k', 'cdn_secret' => 's', 'plan' => 'free' ) );
		$this->assertFalse( apply_filters( 'optimole_wp_support_hub_metadata', array() )['is_pro'], 'a free plan is not pro' );
	}
}
