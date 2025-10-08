<?php

use OptimoleWP\PageProfiler\Profile;
/**
 * WordPress unit test for dimension replacement and injection functionality.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Dimension_Replacement.
 */
class Test_Dimension_Replacement extends WP_UnitTestCase {
	
    const IMAGE_URL = 'https://example.com/test-image.jpg';
	private $tag_replacer;
	private $profile_mock;
	
	public function setUp() : void {
		parent::setUp();
        $settings = new Optml_Settings();
		$settings->update('service_data', [
			'cdn_key' => 'test123',
			'cdn_secret' => '12345',
			'whitelist' => ['example.com'],
		]);
		Optml_Manager::instance()->init();
		// Get the tag replacer instance
		$this->tag_replacer = Optml_Tag_Replacer::instance();
		
		// Mock profile data for testing
		$this->setup_mock_profile_data();
	}
	
	/**
	 * Setup mock profile data with dimension information.
	 */
	private function setup_mock_profile_data() {
		// Mock dimension data that would be stored by the API
		$mock_dimensions = [
			$this->tag_replacer->get_id_by_url(self::IMAGE_URL) => [ 'w' => 1920, 'h' => 1080 ],
		];
		
		// Store this data in the profile system
		$profile = Optml_Manager::instance()->page_profiler;
		$profile->store( 'test-profile', Profile::DEVICE_TYPE_DESKTOP, [], [], [], $mock_dimensions );
		
		// Set the current profile context
		Profile::set_current_profile_id( 'test-profile' );
		$profile->set_current_profile_data();
	}
	 
	
	/**
	 * Test that width attribute is injected when missing.
	 */
	public function test_width_attribute_injection() {
		$html_input = '<img src="'.self::IMAGE_URL.'" height="1080" alt="Test image">';
		$expected_pattern = '/width="1920".*data-opt-id='.$this->tag_replacer->get_id_by_url(self::IMAGE_URL).'/';
		
		// Process the image tags
		$images = $this->extract_images_from_html( $html_input );
		
		$result = $this->tag_replacer->process_image_tags( $html_input, $images );
		
		$this->assertMatchesRegularExpression( $expected_pattern, $result );
		$this->assertStringContainsString( 'width="1920"', $result );
	}
	
	/**
	 * Test that height attribute is injected when missing.
	 */
	public function test_height_attribute_injection() {
		$html_input = '<img src="'.self::IMAGE_URL.'" width="800" alt="Test image">';
		$expected_pattern = '/height="1080".*data-opt-id='.$this->tag_replacer->get_id_by_url(self::IMAGE_URL).'/';
		
		$images = $this->extract_images_from_html( $html_input );
		
		
		$result = $this->tag_replacer->process_image_tags( $html_input, $images );
		
		$this->assertMatchesRegularExpression( $expected_pattern, $result );
		$this->assertStringContainsString( 'height="1080"', $result );
	}
	
	/**
	 * Test that both width and height are injected when both are missing.
	 */
	public function test_both_attributes_injection() {
		$html_input = '<img src="'.self::IMAGE_URL.'" alt="Test image">';
		
		$images = $this->extract_images_from_html( $html_input );
		$result = $this->tag_replacer->process_image_tags( $html_input, $images );
		
		$this->assertStringContainsString( 'width="1920"', $result );
		$this->assertStringContainsString( 'height="1080"', $result );
		$this->assertMatchesRegularExpression( '/width="1920".*height="1080".*data-opt-id='.$this->tag_replacer->get_id_by_url(self::IMAGE_URL).'/', $result );
	}
	
	/**
	 * Test that existing attributes are not overwritten.
	 */
	public function test_existing_attributes_preserved() {
		$html_input = '<img src="'.self::IMAGE_URL.'" width="500" height="300" alt="Test image">';
		
		$images = $this->extract_images_from_html( $html_input );
		$result = $this->tag_replacer->process_image_tags( $html_input, $images );
		
		// Should keep original dimensions, not inject new ones
		$this->assertStringContainsString( 'width="500"', $result );
		$this->assertStringContainsString( 'height="300"', $result );
		$this->assertStringNotContainsString( 'width="1920"', $result );
		$this->assertStringNotContainsString( 'height="1080"', $result );
	}
	
	
	/**
	 * Helper method to extract images from HTML for testing.
	 * Uses the actual manager method to ensure correct structure.
	 */
	private function extract_images_from_html( $html ) {
		return Optml_Manager::parse_images_from_html( $html );
	}
	
}
