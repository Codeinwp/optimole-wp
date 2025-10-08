<?php
/**
 * Unit Tests for Srcset Functionality
 * 
 * Tests the srcset detection, enhancement, and URL modification functionality
 * in the Optimole WordPress plugin.
 * 
 * @package OptimoleWP
 * @subpackage Tests
 */

class Test_Srcset_Functionality extends WP_UnitTestCase {

	/**
	 * Test the change_url_for_size method with various URL patterns
	 */
	public function test_change_url_for_size_basic_replacement() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test basic width/height replacement
		$original_url = 'https://cdn.optimole.com/w:800/h:600/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 200, 150, 1 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:200/h:150/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test DPR replacement when DPR already exists
	 */
	public function test_change_url_for_size_dpr_replacement() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:800/h:600/dpr:1/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 200, 150, 2 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:200/h:150/dpr:2/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test DPR addition when DPR is missing
	 */
	public function test_change_url_for_size_dpr_addition() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:800/h:600/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 200, 150, 2 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:200/dpr:2/h:150/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test DPR removal when DPR = 1
	 */
	public function test_change_url_for_size_dpr_removal() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:800/h:600/dpr:2/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 200, 150, 1 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:200/h:150/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test handling of "auto" values
	 */
	public function test_change_url_for_size_auto_values() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:auto/h:auto/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 300, 200, 1 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:300/h:200/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test complex URL with multiple parameters
	 */
	public function test_change_url_for_size_complex_url() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:1200/h:800/dpr:1/q:mauto/f:best/rt:fill/c:fill/http://example.com/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 500, 400, 2 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:500/h:400/dpr:2/q:mauto/f:best/rt:fill/c:fill/http://example.com/image.jpg', $result );
	}

	/**
	 * Test DPR addition with auto values
	 */
	public function test_change_url_for_size_dpr_with_auto_values() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:auto/h:auto/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 400, 300, 2 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:400/dpr:2/h:300/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test mixed auto and numeric values
	 */
	public function test_change_url_for_size_mixed_auto_numeric() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:auto/h:600/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 500, 400, 1 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:500/h:400/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test add_missing_srcset_attributes with no existing srcset
	 */
	public function test_add_missing_srcset_attributes_no_existing() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
			[ 'w' => 400, 'h' => 300, 'd' => 1, 's' => '400w', 'b' => 768 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertStringContainsString( 'srcset=', $result );
		$this->assertStringContainsString( '200w', $result );
		$this->assertStringContainsString( '400w', $result );
		$this->assertStringContainsString( 'sizes=', $result );
		$this->assertStringContainsString( '(max-width: 320px) 200px', $result );
		$this->assertStringContainsString( '(max-width: 768px) 400px', $result );
	}

	/**
	 * Test add_missing_srcset_attributes with existing srcset
	 */
	public function test_add_missing_srcset_attributes_with_existing() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
			[ 'w' => 400, 'h' => 300, 'd' => 1, 's' => '400w', 'b' => 768 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $result );
		$this->assertStringContainsString( '200w', $result );
		$this->assertStringContainsString( '400w', $result );
	}

	/**
	 * Test add_missing_srcset_attributes with existing sizes
	 */
	public function test_add_missing_srcset_attributes_with_existing_sizes() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" sizes="(max-width: 480px) 300px" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
			[ 'w' => 400, 'h' => 300, 'd' => 1, 's' => '400w', 'b' => 768 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertStringContainsString( '(max-width: 480px) 300px', $result );
		$this->assertStringContainsString( '(max-width: 320px) 200px', $result );
		$this->assertStringContainsString( '(max-width: 768px) 400px', $result );
	}

	/**
	 * Test retina images setting respect
	 */
	public function test_add_missing_srcset_attributes_retina_disabled() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Mock settings to disable retina images
		$settings = $this->getMockBuilder( 'Optml_Settings' )->getMock();
		$settings->method( 'get' )->with( 'retina_images' )->willReturn( 'disabled' );
		$tag_replacer->settings = $settings;
		
		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
			[ 'w' => 400, 'h' => 300, 'd' => 2, 's' => '400w', 'b' => 768 ], // This should be skipped
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertStringContainsString( '200w', $result );
		$this->assertStringNotContainsString( '400w', $result );
	}

	/**
	 * Test enhance_existing_srcset method
	 */
	public function test_enhance_existing_srcset() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" alt="Test" />';
		$new_entries = [ 'new-200w.jpg 200w', 'new-400w.jpg 400w' ];
		
		$result = $tag_replacer->enhance_existing_srcset( $tag, $new_entries, false );
		
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $result );
		$this->assertStringContainsString( 'new-200w.jpg 200w', $result );
		$this->assertStringContainsString( 'new-400w.jpg 400w', $result );
	}

	/**
	 * Test enhance_existing_sizes method
	 */
	public function test_enhance_existing_sizes() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" sizes="(max-width: 480px) 300px" alt="Test" />';
		$new_entries = [ '(max-width: 320px) 200px', '(max-width: 768px) 400px' ];
		
		$result = $tag_replacer->enhance_existing_sizes( $tag, $new_entries, false );
		
		$this->assertStringContainsString( '(max-width: 480px) 300px', $result );
		$this->assertStringContainsString( '(max-width: 320px) 200px', $result );
		$this->assertStringContainsString( '(max-width: 768px) 400px', $result );
	}

	/**
	 * Test empty missing_srcsets array
	 */
	public function test_add_missing_srcset_attributes_empty_array() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertEquals( $tag, $result );
	}

	/**
	 * Test invalid srcset data (missing required fields)
	 */
	public function test_add_missing_srcset_attributes_invalid_data() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150 ], // Missing 's' field
			[ 'w' => 400, 'h' => 300, 's' => '400w' ], // Valid entry
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		$this->assertStringContainsString( '400w', $result );
		$this->assertStringNotContainsString( '200w', $result );
	}

	/**
	 * Test slashed URL handling
	 */
	public function test_change_url_for_size_slashed_url() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$original_url = 'https://cdn.optimole.com/w:800/h:600/q:auto/f:best/image.jpg';
		$result = $tag_replacer->change_url_for_size( $original_url, 200, 150, 2 );
		
		$this->assertEquals( 'https://cdn.optimole.com/w:200/dpr:2/h:150/q:auto/f:best/image.jpg', $result );
	}

	/**
	 * Test performance with large srcset data
	 */
	public function test_add_missing_srcset_attributes_performance() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [];
		
		// Generate large srcset data
		for ( $i = 0; $i < 20; $i++ ) {
			$width = 200 + ( $i * 100 );
			$height = 150 + ( $i * 75 );
			$missing_srcsets[] = [
				'w' => $width,
				'h' => $height,
				'd' => 1,
				's' => $width . 'w',
				'b' => 320 + ( $i * 100 )
			];
		}
		
		$start_time = microtime( true );
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		$end_time = microtime( true );
		
		$execution_time = $end_time - $start_time;
		
		// Should complete in less than 0.1 seconds
		$this->assertLessThan( 0.1, $execution_time );
		$this->assertStringContainsString( 'srcset=', $result );
	}

	/**
	 * Test should_skip_sizes method with calc() functions
	 */
	public function test_should_skip_sizes_calc() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test calc() functions
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'calc(100vw - 20px)' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '(max-width: 768px) calc(100vw - 40px)' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'calc(50% + 10px)' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'calc(100% - 2rem)' ) );
		
		// Test simple sizes (should return false)
		$this->assertFalse( $tag_replacer->should_skip_sizes( '(max-width: 768px) 100vw' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100px' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '50%' ) );
	}

	/**
	 * Test should_skip_sizes method with mathematical operations
	 */
	public function test_should_skip_sizes_math_operations() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test mathematical operations with percentages
		$this->assertTrue( $tag_replacer->should_skip_sizes( '50% + 20px' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100% - 10px' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '25% * 2' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100% / 2' ) );
		
		// Test simple percentages (should return false)
		$this->assertFalse( $tag_replacer->should_skip_sizes( '50%' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100%' ) );
	}

	/**
	 * Test should_skip_sizes method with viewport units
	 */
	public function test_should_skip_sizes_viewport_units() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test viewport units with calculations
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100vw + 20px' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '50vh - 10px' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100vmin * 0.8' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100vmax / 2' ) );
		
		// Test simple viewport units (should return false)
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100vw' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '50vh' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100vmin' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100vmax' ) );
	}

	/**
	 * Test should_skip_sizes method with CSS functions
	 */
	public function test_should_skip_sizes_css_functions() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test CSS functions
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'min(100vw, 800px)' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'max(50%, 300px)' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( 'clamp(300px, 50%, 800px)' ) );
		
		// Test simple values (should return false)
		$this->assertFalse( $tag_replacer->should_skip_sizes( '800px' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '50%' ) );
	}

	/**
	 * Test should_skip_sizes method with percentage operations
	 */
	public function test_should_skip_sizes_percentage_operations() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test percentage operations
		$this->assertTrue( $tag_replacer->should_skip_sizes( '50% + 25%' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100% - 20%' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '25% * 2' ) );
		$this->assertTrue( $tag_replacer->should_skip_sizes( '100% / 4' ) );
		
		// Test simple percentages (should return false)
		$this->assertFalse( $tag_replacer->should_skip_sizes( '50%' ) );
		$this->assertFalse( $tag_replacer->should_skip_sizes( '100%' ) );
	}

	/**
	 * Test add_missing_srcset_attributes skips sizes when complex formulas are present
	 */
	public function test_add_missing_srcset_attributes_skips_complex_sizes() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test with calc() function
		$tag = '<img src="https://example.com/image.jpg" sizes="calc(100vw - 20px)" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		// Should preserve original sizes and not add new ones
		$this->assertStringContainsString( 'calc(100vw - 20px)', $result );
		$this->assertStringNotContainsString( '(max-width: 320px) 200px', $result );
	}

	/**
	 * Test add_missing_srcset_attributes skips sizes when mathematical operations are present
	 */
	public function test_add_missing_srcset_attributes_skips_math_operations() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test with mathematical operations
		$tag = '<img src="https://example.com/image.jpg" sizes="50% + 20px" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		// Should preserve original sizes and not add new ones
		$this->assertStringContainsString( '50% + 20px', $result );
		$this->assertStringNotContainsString( '(max-width: 320px) 200px', $result );
	}

	/**
	 * Test add_missing_srcset_attributes skips sizes when CSS functions are present
	 */
	public function test_add_missing_srcset_attributes_skips_css_functions() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test with CSS functions
		$tag = '<img src="https://example.com/image.jpg" sizes="min(100vw, 800px)" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		// Should preserve original sizes and not add new ones
		$this->assertStringContainsString( 'min(100vw, 800px)', $result );
		$this->assertStringNotContainsString( '(max-width: 320px) 200px', $result );
	}

	/**
	 * Test add_missing_srcset_attributes still adds sizes when no complex formulas are present
	 */
	public function test_add_missing_srcset_attributes_adds_simple_sizes() {
		$tag_replacer = new Optml_Tag_Replacer();
		
		// Test with simple sizes
		$tag = '<img src="https://example.com/image.jpg" sizes="(max-width: 768px) 100vw" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ],
		];
		
		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		
		// Should enhance existing sizes
		$this->assertStringContainsString( '(max-width: 768px) 100vw', $result );
		$this->assertStringContainsString( '(max-width: 320px) 200px', $result );
	}


	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		
	}
}
