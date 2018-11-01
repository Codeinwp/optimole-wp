<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Generic.
 */
class Test_Lazyload extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],

		] );
		$settings->update( 'lazyload', 'enabled' );
		Optml_Replacer::instance()->init();

	}

	public function test_lazy_load() {

		$replaced_content = Optml_Replacer::instance()->filter_the_content( Test_Replacer::IMG_TAGS );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertNotContains( 'http://example.org', $replaced_content );

		$replaced_content = Optml_Replacer::instance()->replace_urls( Test_Replacer::IMG_TAGS . Test_Replacer::IMG_URLS );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertNotContains( 'http://example.org', $replaced_content );
	}

}