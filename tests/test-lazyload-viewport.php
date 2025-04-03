<?php

use OptimoleWP\PageProfiler\Profile;
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
class Test_Lazyload_Viewport extends WP_UnitTestCase
{ 
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
		$settings->update('lazyload_type', 'viewport');
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		add_filter('optml_page_profile_id', [self::class, 'mock_page_ids']);
 
	}
	public function tearDown() : void
	{
		parent::tearDown();
		remove_filter('optml_page_profile_id', [self::class, 'mock_page_ids']);
	}
	public static function mock_page_ids(){
		return 1;
	}
	public static function get_sample_html(){
		return file_get_contents(OPTML_PATH . '/tests/assets/sample.html');
	}
	public function test_lazyload_viewport(){ 
		
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());
		//check if the html has the same number of image tags as the same numer of data-opt-id
		$this->assertEquals(substr_count($html, '<img'), substr_count($html, 'data-opt-id'));
		 
		Optml_Manager::instance()->page_profiler->store(self::mock_page_ids(), Profile::DEVICE_TYPE_DESKTOP, [1579989818]);
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html()); 
		$this->assertEquals(substr_count($html, 'data-opt-src'), substr_count($html, 'data-opt-id'));
	
		Optml_Manager::instance()->page_profiler->store(self::mock_page_ids(), Profile::DEVICE_TYPE_MOBILE, [1579989818]);
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html()); 

		$this->assertNotEquals(substr_count($html, 'data-opt-src'), substr_count($html, 'data-opt-id'));
		$image_tags = Optml_Manager::instance()->parse_images_from_html($html);
		$image_tags = array_filter($image_tags[0], function($tag){
			return strpos($tag, 'data-opt-id=1579989818') !== false;
		})[0];
		$this->assertStringNotContainsString('data-opt-src', $image_tags);
		$this->assertStringContainsString('fetchpriority="high"', $image_tags);
	}
	public function test_lazyload_viewport_with_lcp_data(){ 
		// Test with LCP (Largest Contentful Paint) data for desktop
		Optml_Manager::instance()->page_profiler->store(
			self::mock_page_ids(), 
			Profile::DEVICE_TYPE_DESKTOP, 
			[1579989818], 
			[], 
			['type' => 'img', 'imageId' => 1579989818]
		);
		
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());
		
		// Check if the LCP image has fetchpriority="high" attribute
		$image_tags = Optml_Manager::instance()->parse_images_from_html($html);
		$lcp_image = array_filter($image_tags[0], function($tag){
			return strpos($tag, 'data-opt-id=1579989818') !== false;
		})[0];
		
		$this->assertStringContainsString('fetchpriority="high"', $lcp_image);
		$this->assertStringNotContainsString('data-opt-src', $lcp_image);
	} 
	 
public function test_lazyload_viewport_with_different_device_profiles(){
	// Store different images for desktop and mobile
	Optml_Manager::instance()->page_profiler->store(
		self::mock_page_ids(), 
		Profile::DEVICE_TYPE_DESKTOP, 
		[1579989818, 1698280985]
	);
	
	Optml_Manager::instance()->page_profiler->store(
		self::mock_page_ids(), 
		Profile::DEVICE_TYPE_MOBILE, 
		[1579989818]
	);
	
	$html = Optml_Manager::instance()->replace_content(self::get_sample_html());
	
	// Image 1579989818 should be eagerly loaded on both devices
	$image_tags = Optml_Manager::instance()->parse_images_from_html($html);
	$image_1 = array_filter($image_tags[0], function($tag){
		return strpos($tag, 'data-opt-id=1579989818') !== false;
	})[0];
	$image_2 = array_values(array_filter($image_tags[0], function($tag){
		return strpos($tag, 'data-opt-id=1698280985') !== false;
	}))[0]; 
	$this->assertStringNotContainsString('data-opt-src', $image_1);
	$this->assertStringContainsString('data-opt-src', $image_2);
	 
} 

public function test_is_in_viewport_detection(){
	// Test the is_in_viewport detection methods
	Optml_Manager::instance()->page_profiler->store(
		self::mock_page_ids(), 
		Profile::DEVICE_TYPE_DESKTOP, 
		[1579989818]
	);
	
	Optml_Manager::instance()->page_profiler->store(
		self::mock_page_ids(), 
		Profile::DEVICE_TYPE_MOBILE, 
		[1579989818]
	);
	
	// Set the current profile ID to enable viewport detection
	Profile::set_current_profile_id(self::mock_page_ids());
	Optml_Manager::instance()->page_profiler->set_current_profile_data();
	
	// Test if image is detected in all viewports
	$this->assertTrue(
		Optml_Manager::instance()->page_profiler->is_in_all_viewports(1579989818)
	);
	
	// Test if image is detected in any viewport
	$this->assertNotFalse(
		Optml_Manager::instance()->page_profiler->is_in_any_viewport(1579989818)
	);
	
	// Test with an image that doesn't exist in the viewport
	$this->assertFalse(
		Optml_Manager::instance()->page_profiler->is_in_all_viewports(9999999)
	);
}
public function test_profile_data_storage_and_retrieval(){
	// Test storing and retrieving profile data
	$above_fold_images = [1579989818, 1579989819];
	$bg_selectors = [
		'.hero-banner' => [
			'.above-fold' => ['https://example.com/image1.jpg']
		]
	];
	$lcp_data = [
		'type' => 'img',
		'imageId' => 1579989818
	];
	
	// Store the data
	Optml_Manager::instance()->page_profiler->store(
		self::mock_page_ids(), 
		Profile::DEVICE_TYPE_DESKTOP, 
		$above_fold_images,
		$bg_selectors,
		$lcp_data
	);
	
	// Retrieve the data
	$profile_data = Optml_Manager::instance()->page_profiler->get_profile_data(self::mock_page_ids());
	
	// Check if data was stored correctly
	$this->assertArrayHasKey(Profile::DEVICE_TYPE_DESKTOP, $profile_data);
	$this->assertArrayHasKey('af', $profile_data[Profile::DEVICE_TYPE_DESKTOP]);
	$this->assertArrayHasKey('bg', $profile_data[Profile::DEVICE_TYPE_DESKTOP]);
	$this->assertArrayHasKey('lcp', $profile_data[Profile::DEVICE_TYPE_DESKTOP]);
	
	// Check if above-fold images are stored correctly (as keys with value true)
	$this->assertTrue($profile_data[Profile::DEVICE_TYPE_DESKTOP]['af'][1579989818]);
	$this->assertTrue($profile_data[Profile::DEVICE_TYPE_DESKTOP]['af'][1579989819]);
	
	// Check if LCP data is stored correctly
	$this->assertEquals('img', $profile_data[Profile::DEVICE_TYPE_DESKTOP]['lcp']['type']);
	$this->assertEquals(1579989818, $profile_data[Profile::DEVICE_TYPE_DESKTOP]['lcp']['imageId']);
}
}
