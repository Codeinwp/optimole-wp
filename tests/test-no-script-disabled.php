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
class Test_Lazyload_No_Script extends WP_UnitTestCase
{


	public function setUp(): void
	{
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update('service_data', [
			'cdn_key' => 'test123',
			'cdn_secret' => '12345',
			'whitelist' => ['example.com'],

		]);
		$settings->update('lazyload', 'enabled');
		$settings->update('native_lazyload', 'enabled');
		$settings->update('video_lazyload', 'enabled');
		$settings->update('no_script', 'disabled');
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_attachement = self::factory()->attachment->create_upload_object(OPTML_PATH . 'assets/img/logo.png');
	}

	//changing the setting to disabled in a test inside the lazyload class will not work due to test concurrency
	//moved this to a different test
	public function test_no_script_disabled()
	{
		$text = ' <a href="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png"><img class="alignnone wp-image-36442 size-full" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png"  srcset="testsrcset" data-srcset="another" data-plugin-src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" alt="How to monetize a blog" width="490" height="256"></a>';

		$replaced_content = Optml_Manager::instance()->replace_content($text);

		$this->assertStringNotContainsString('noscript', $replaced_content);
	}
}