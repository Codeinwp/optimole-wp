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
class Test_Lazyload_Class_Exclusion extends WP_UnitTestCase
{

	public static $sample_attachement;

	public function setUp() : void
	{
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update('service_data', [
			'cdn_key' => 'test123',
			'cdn_secret' => '12345',
			'whitelist' => ['example.com'],
		]);

		$settings->update('lazyload', 'enabled');
		$settings->update('no_script', 'enabled');
		$settings->update( 'lazyload_placeholder', 'disabled' );
		$settings->update('filters', array(
			Optml_Settings::FILTER_TYPE_LAZYLOAD => array (
			 Optml_Settings::FILTER_CLASS => array (
				'test' => true,
				'whatever' => true,
				'testing' => true,
			)
		)));
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_attachement = self::factory()->attachment->create_upload_object(OPTML_PATH . 'assets/img/logo.png');
	}

	public function test_class_exclusions()
	{
		$content = '<div>
						<img class="something another whatever" src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>;    
						<img class="test" src="http://example.org/wp-content/uploads/2019/09/img.jpg" alt=""/>;      
						<img class="testing class" src="http://example.org/img.png" alt=""/>;          
					</div>';
		$replaced_content = Optml_Manager::instance()->process_images_from_content($content);
		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertStringNotContainsString('data-opt-src', $replaced_content);
	}
	public function test_class_exclusions_does_not_affect_regular_replacement()
	{
		$content = '<div>
						<img class="something another code" src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>; 
						<img class="whatever_code_test" src="http://example.org/img.jpg" alt=""/>;          
					</div>';
		$replaced_content = Optml_Manager::instance()->process_images_from_content($content);

		$this->assertEquals( 4, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertEquals( 1, substr_count( $replaced_content, '<noscript>' ) );
	}
}
