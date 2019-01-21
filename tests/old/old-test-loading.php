<?php
/**
 * Loading test for lower PHP versions.
 *
 * @package ThemeIsleSDK
 */

/**
 * Test plugin is not loading.
 */
class Loading_Old_Test extends WP_UnitTestCase {
	/**
	 * Test if the SDK is not loading on lower php versions.
	 */
	public function test_plugin_not_loaded() {
		$this->assertFalse( class_exists('Optml_Main', false));
		$this->assertNull( optml() );
	}

}
