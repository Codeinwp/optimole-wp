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
		$tag_replacer->settings = $this->retina_settings( 'disabled' );
		
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
	 * With Retina enabled, the profiler sends a 1x and a 2x entry per breakpoint. The 2x entry
	 * advertises its delivered width (824w); the CDN multiplies w by dpr, so its URL asks for the
	 * CSS width (w:412/dpr:2), not w:824/dpr:2, which would deliver 1648px.
	 */
	public function test_add_missing_srcset_attributes_retina_entry_delivers_its_advertised_width(): void {
		$tag_replacer = new Optml_Tag_Replacer();
		$tag_replacer->settings = $this->retina_settings( 'enabled' );

		$result = $tag_replacer->add_missing_srcset_attributes(
			'<img src="https://example.com/image.jpg" alt="Test" />',
			[
				[ 'w' => 412, 'h' => 300, 'd' => 1, 's' => '412w', 'b' => 768 ],
				[ 'w' => 824, 'h' => 600, 'd' => 2, 's' => '824w', 'b' => 768 ],
			],
			'https://example.i.optimole.com/w:auto/h:auto/q:mauto/https://example.com/image.jpg',
			false
		);

		$this->assertStringContainsString( 'w:412/h:300/q:mauto/https://example.com/image.jpg 412w', $result );
		$this->assertStringContainsString( 'w:412/dpr:2/h:300/q:mauto/https://example.com/image.jpg 824w', $result );
		$this->assertStringNotContainsString( 'w:824', $result );
	}

	/**
	 * The 1x and 2x entries of a breakpoint share one slot: the sizes attribute gets one
	 * condition for it, with the CSS width, not a second one with the doubled width.
	 */
	public function test_add_missing_srcset_attributes_retina_entries_share_one_sizes_condition(): void {
		$tag_replacer = new Optml_Tag_Replacer();
		$tag_replacer->settings = $this->retina_settings( 'enabled' );

		$result = $tag_replacer->add_missing_srcset_attributes(
			'<img src="https://example.com/image.jpg" alt="Test" />',
			[
				[ 'w' => 300, 'h' => 200, 'd' => 1, 's' => '300w', 'b' => 480 ],
				[ 'w' => 600, 'h' => 400, 'd' => 2, 's' => '600w', 'b' => 480 ],
				[ 'w' => 412, 'h' => 300, 'd' => 1, 's' => '412w', 'b' => 768 ],
				[ 'w' => 824, 'h' => 600, 'd' => 2, 's' => '824w', 'b' => 768 ],
			],
			'https://example.i.optimole.com/w:auto/h:auto/q:mauto/https://example.com/image.jpg',
			false
		);

		$this->assertSame( 1, substr_count( $result, '(max-width: 480px)' ) );
		$this->assertSame( 1, substr_count( $result, '(max-width: 768px)' ) );
		$this->assertStringContainsString( 'sizes="(max-width: 480px) 300px, (max-width: 768px) 412px"', $result );
	}

	/**
	 * Settings mock for the Retina images option.
	 *
	 * @param string $retina_images The option value, 'enabled' or 'disabled'.
	 *
	 * @return Optml_Settings
	 */
	private function retina_settings( string $retina_images ): Optml_Settings {
		$settings = $this->getMockBuilder( 'Optml_Settings' )->getMock();
		$settings->method( 'get' )->with( 'retina_images' )->willReturn( $retina_images );

		return $settings;
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
	 * Test that a "$0" srcset descriptor cannot inject a real onload attribute
	 */
	public function test_add_missing_srcset_attributes_prevents_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 800, 'h' => 600, 'd' => 1, 's' => '$0 onload=alert(document.domain) x', 'b' => 0 ],
		];

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		$img = $this->get_img_element( $result );

		// The payload must stay inert literal text inside the srcset value, not become a real attribute
		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertStringContainsString( '$0 onload=alert(document.domain) x', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * Test that a "$1" srcset descriptor cannot inject a real onerror attribute
	 */
	public function test_add_missing_srcset_attributes_prevents_dollar_one_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 800, 'h' => 600, 'd' => 1, 's' => '$1 onerror=alert(1) x', 'b' => 0 ],
		];

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		$img = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onerror' ) );
		$this->assertSame( 'https://example.com/image.jpg', $img->getAttribute( 'src' ) );
	}

	/**
	 * Test that enhance_existing_srcset does not expand a "$0" entry into a real attribute
	 */
	public function test_enhance_existing_srcset_prevents_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" alt="Test" />';
		$new_entries = [ 'evil.jpg $0 onload=alert(document.domain) x' ];

		$result = $tag_replacer->enhance_existing_srcset( $tag, $new_entries, false );
		$img = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * Test that enhance_existing_sizes does not expand a "$0" entry into a real attribute
	 */
	public function test_enhance_existing_sizes_prevents_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" sizes="(max-width: 480px) 300px" alt="Test" />';
		$new_entries = [ '$0 onload=alert(document.domain) x' ];

		$result = $tag_replacer->enhance_existing_sizes( $tag, $new_entries, false );
		$img = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertStringContainsString( '(max-width: 480px) 300px', $img->getAttribute( 'sizes' ) );
	}

	/**
	 * Test that a backslash-form "\0" srcset descriptor cannot inject a real onload attribute
	 */
	public function test_add_missing_srcset_attributes_prevents_backslash_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" alt="Test" />';
		$missing_srcsets = [
			[ 'w' => 800, 'h' => 600, 'd' => 1, 's' => '\0 onload=alert(document.domain) x', 'b' => 0 ],
		];

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, $missing_srcsets, 'https://example.com/image.jpg', false );
		$img = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertStringContainsString( '\0 onload=alert(document.domain) x', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * Test that enhance_existing_srcset does not expand a "\1" entry into a real attribute
	 */
	public function test_enhance_existing_srcset_prevents_backslash_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" alt="Test" />';
		$new_entries = [ 'evil.jpg \1 onerror=alert(1) x' ];

		$result = $tag_replacer->enhance_existing_srcset( $tag, $new_entries, false );
		$img = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onerror' ) );
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * JSON-escaped (slashed) markup is never enhanced: escaping quotes alone would not stop a \u0022 in a value from
	 * closing the attribute once the JSON is decoded.
	 *
	 * @dataProvider slashed_tags
	 *
	 * @param string $tag The image tag.
	 */
	public function test_add_missing_srcset_attributes_leaves_slashed_markup_unchanged( string $tag ): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 800, 'h' => 600, 'd' => 1, 's' => '\u0022 onload=alert(1) x', 'b' => 320 ] ], 'https://example.com/image.jpg', true );

		$this->assertSame( $tag, $result );
	}

	/**
	 * Image tags as they appear in JSON-escaped content.
	 *
	 * @return array<string, array{string}>
	 */
	public function slashed_tags(): array {
		return [
			'double quotes'  => [ '<img src="https:\\/\\/example.com\\/image.jpg">' ],
			'single quotes'  => [ "<img src='https:\\/\\/example.com\\/image.jpg'>" ],
		];
	}

	/**
	 * Test that enhance_existing_srcset's slashed output keeps its required escaping while a "\0" payload stays inert
	 */
	public function test_enhance_existing_srcset_slashed_prevents_backslash_backreference_injection() {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" alt="Test" />';
		$new_entries = [ 'evil.jpg \0 onload=alert(document.domain) x' ];

		$result = $tag_replacer->enhance_existing_srcset( $tag, $new_entries, true );

		$this->assertStringContainsString( 'srcset=\"', $result );
		$this->assertStringNotContainsString( 'srcset=\\\"', $result );
		$this->assertStringNotContainsString( '\" onload=', $result );
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $result );
	}

	/**
	 * An Envira Gallery image keeps a data: placeholder in srcset until its lazy loader copies
	 * data-envira-srcset in. Real candidates there would load the image before Envira does.
	 */
	public function test_add_missing_srcset_attributes_leaves_placeholder_srcset_to_the_lazy_loader(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img class="envira-gallery-image envira-lazy" src="https://example.com/image.jpg" data-envira-src="https://example.com/image.jpg" data-envira-srcset="https://example.com/image.jpg 400w,https://example.com/image.jpg 2x" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-safe-src="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 254, 'h' => 300, 'd' => 1, 's' => '254w', 'b' => 2560 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( $tag, $result );
	}

	/**
	 * A lazy loader that shows a data: placeholder in src owns the image too.
	 */
	public function test_add_missing_srcset_attributes_leaves_placeholder_src_to_the_lazy_loader(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%2F%3E" data-src="https://example.com/image.jpg" alt="Test" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( $tag, $result );
	}

	/**
	 * A placeholder written with spaces around "=", without quotes or in uppercase belongs to the lazy loader
	 * too: neither the srcset nor the sizes next to it is rewritten.
	 *
	 * @dataProvider placeholder_attribute_variants
	 *
	 * @param string $tag The image tag.
	 */
	public function test_add_missing_srcset_attributes_leaves_unquoted_and_spaced_placeholders( string $tag ): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( $tag, $result );
	}

	/**
	 * Image tags whose placeholder src or srcset has spaces around "=", no quotes or an uppercase name.
	 *
	 * @return array<string, array{string}>
	 */
	public function placeholder_attribute_variants(): array {
		$placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

		return [
			'spaced srcset'   => [ '<img src="https://example.com/image.jpg" srcset = "' . $placeholder . '" sizes="100vw" alt="Test" />' ],
			'unquoted srcset' => [ '<img src="https://example.com/image.jpg" srcset=' . $placeholder . ' sizes="100vw" alt="Test" />' ],
			'spaced src'      => [ '<img src = "' . $placeholder . '" data-src="https://example.com/image.jpg" sizes="100vw" alt="Test" />' ],
			'unquoted src'    => [ '<img src=' . $placeholder . ' data-src="https://example.com/image.jpg" sizes="100vw" alt="Test" />' ],
			'uppercase name'  => [ '<img src="https://example.com/image.jpg" SRCSET = "' . $placeholder . '" sizes="100vw" alt="Test" />' ],
		];
	}

	/**
	 * Text that only looks like an attribute inside another value (alt, title) is neither a placeholder nor an
	 * existing srcset: the image still gets its srcset and sizes.
	 */
	public function test_add_missing_srcset_attributes_ignores_attribute_text_inside_values(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" alt="Example src=data:image/gif;base64,AAAA srcset=x.jpg" title=\'srcset = "data:x"\' />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertSame( 'https://example.com/image.jpg 200w', $img->getAttribute( 'srcset' ) );
		$this->assertSame( '(max-width: 320px) 200px', $img->getAttribute( 'sizes' ) );
		$this->assertSame( 'Example src=data:image/gif;base64,AAAA srcset=x.jpg', $img->getAttribute( 'alt' ) );
	}

	/**
	 * Attribute-like text inside alt is never rewritten: a sizes inserted there would close the alt value early and turn
	 * the rest of the text (onload=…) into a real attribute.
	 */
	public function test_add_missing_srcset_attributes_never_writes_inside_another_value(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$alt = "Example srcset='data:x' onload=alert(1) ";
		$tag = '<img src="https://example.com/image.jpg" alt="' . $alt . '">';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertSame( $alt, $img->getAttribute( 'alt' ) );
		$this->assertSame( 'https://example.com/image.jpg 200w', $img->getAttribute( 'srcset' ) );
		$this->assertSame( '(max-width: 320px) 200px', $img->getAttribute( 'sizes' ) );
	}

	/**
	 * A single-quoted srcset may hold a double quote. It is written back with single quotes, so that double quote
	 * cannot close the attribute and start a new one.
	 */
	public function test_enhance_existing_srcset_keeps_the_original_quote(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" srcset=\'existing-300w.jpg 300w," onerror=alert(1) x\' alt="Test" />';

		$result = $tag_replacer->enhance_existing_srcset( $tag, [ 'new-200w.jpg 200w' ], false );
		$img    = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onerror' ) );
		$this->assertStringContainsString( 'new-200w.jpg 200w', $img->getAttribute( 'srcset' ) );
		$this->assertStringContainsString( '" onerror=alert(1) x', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * An attribute may follow a quoted value with no space between them. Text inside it (title) is never read as
	 * an attribute or written into.
	 */
	public function test_add_missing_srcset_attributes_reads_attributes_without_a_space_between(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$title = "Example srcset='x.jpg' onload=alert(1) ";
		$tag   = '<img src="https://example.com/image.jpg" alt=""title="' . $title . '">';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertFalse( $img->hasAttribute( 'onload' ) );
		$this->assertSame( $title, $img->getAttribute( 'title' ) );
		$this->assertSame( 'https://example.com/image.jpg 200w', $img->getAttribute( 'srcset' ) );
	}

	/**
	 * An image inside <noscript>, as the image parser hands it over, is still enhanced and keeps its wrapper.
	 */
	public function test_add_missing_srcset_attributes_enhances_an_image_inside_noscript(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<noscript><img src="https://example.com/image.jpg" alt="Test" /></noscript>';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( '<noscript><img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 200w" sizes="(max-width: 320px) 200px" alt="Test" /></noscript>', $result );
	}

	/**
	 * The image parser ends a tag at the first ">", even inside a quoted value. The attributes before the cut-off
	 * value are still enhanced, and the cut-off value is left as it is.
	 */
	public function test_add_missing_srcset_attributes_enhances_a_tag_cut_off_inside_a_value(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$images = Optml_Manager::parse_images_from_html( '<noscript><img src="https://example.com/image.jpg" alt="1 > 0"></noscript>' );
		$tag    = $images['img_tag'][0];
		$this->assertSame( '<noscript><img src="https://example.com/image.jpg" alt="1 >', $tag );

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( '<noscript><img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 200w" sizes="(max-width: 320px) 200px" alt="1 >', $result );
	}

	/**
	 * A vertical tab is not whitespace in HTML: the browser keeps it, and everything after it, inside the unquoted
	 * alt value. Nothing in there is read as an attribute or written into.
	 */
	public function test_add_missing_srcset_attributes_treats_a_vertical_tab_as_part_of_a_value(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = "<img src=\"https://example.com/image.jpg\" alt=x\x0Btitle=\"\"srcset='z'>";

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w onerror=alert(1)//', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( $tag, $result );
	}

	/**
	 * A tag the attribute reader can't follow to its end is left as it is.
	 */
	public function test_add_missing_srcset_attributes_leaves_unreadable_tags_unchanged(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" alt="Test""srcset="x.jpg" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( $tag, $result );
	}

	/**
	 * Only the first src and srcset count, as in the browser: a later duplicate placeholder doesn't stop the
	 * enhancement of the srcset the browser uses.
	 */
	public function test_add_missing_srcset_attributes_ignores_a_duplicate_placeholder(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" srcset="real-300w.jpg 300w" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACw=" alt="Test" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( '<img src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 200w, real-300w.jpg 300w" sizes="(max-width: 320px) 200px" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACw=" alt="Test" />', $result );
	}

	/**
	 * Only the real srcset is enhanced, and sizes is added once, after it: data-envira-srcset comes first in
	 * the tag and must neither be read as the existing srcset nor be rewritten.
	 */
	public function test_add_missing_srcset_attributes_enhances_only_the_real_srcset(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" data-envira-srcset="lazy-400w.jpg 400w" srcset="existing-300w.jpg 300w" alt="Test" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertSame( 'lazy-400w.jpg 400w', $img->getAttribute( 'data-envira-srcset' ) );
		$this->assertStringContainsString( 'existing-300w.jpg 300w', $img->getAttribute( 'srcset' ) );
		$this->assertStringContainsString( '200w', $img->getAttribute( 'srcset' ) );
		$this->assertStringNotContainsString( 'lazy-400w.jpg', $img->getAttribute( 'srcset' ) );
		$this->assertSame( 1, substr_count( $result, ' sizes="' ) );
		$this->assertSame( '(max-width: 320px) 200px', $img->getAttribute( 'sizes' ) );
	}

	/**
	 * A tag with only data-src, data-srcset and data-sizes has no srcset or sizes of its own: both are added once,
	 * after the real src, and the lazy-load attributes stay as they are.
	 */
	public function test_add_missing_srcset_attributes_adds_srcset_beside_lazy_load_attributes(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img data-src="https://example.com/image.jpg" src="https://example.com/image.jpg" data-srcset="lazy-400w.jpg 400w" data-sizes="auto" alt="Test" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertSame( 1, substr_count( $result, ' srcset="' ) );
		$this->assertSame( 1, substr_count( $result, ' sizes="' ) );
		$this->assertStringContainsString( 'src="https://example.com/image.jpg" srcset="https://example.com/image.jpg 200w" sizes="(max-width: 320px) 200px"', $result );
		$this->assertSame( 'https://example.com/image.jpg', $img->getAttribute( 'data-src' ) );
		$this->assertSame( 'lazy-400w.jpg 400w', $img->getAttribute( 'data-srcset' ) );
		$this->assertSame( 'auto', $img->getAttribute( 'data-sizes' ) );
	}

	/**
	 * An unquoted srcset or sizes, or one with spaces around "=", is still the tag's own attribute: no second
	 * one is added in front of it, where the browser would use it instead.
	 *
	 * @dataProvider own_attribute_variants
	 *
	 * @param string $tag      The image tag.
	 * @param string $original The original attribute, which must stay as it is.
	 */
	public function test_add_missing_srcset_attributes_keeps_unquoted_and_spaced_attributes( string $tag, string $original ): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );

		$this->assertSame( 1, preg_match_all( '/\ssrcset\s*=/', $result ) );
		$this->assertLessThanOrEqual( 1, preg_match_all( '/\ssizes\s*=/', $result ) );
		$this->assertStringContainsString( ' ' . $original . ' ', $result );
	}

	/**
	 * Image tags whose srcset or sizes is unquoted or has spaces around "=".
	 *
	 * @return array<string, array{string, string}>
	 */
	public function own_attribute_variants(): array {
		return [
			'unquoted srcset' => [ '<img src="https://example.com/image.jpg" srcset=https://example.com/original.jpg sizes="100vw" alt="Test" />', 'srcset=https://example.com/original.jpg' ],
			'unquoted sizes'  => [ '<img src="https://example.com/image.jpg" srcset="https://example.com/original.jpg 1000w" sizes=50vw alt="Test" />', 'sizes=50vw' ],
			'spaced srcset'   => [ '<img src="https://example.com/image.jpg" srcset = "existing-300w.jpg 300w" alt="Test" />', 'srcset = "existing-300w.jpg 300w"' ],
			'spaced sizes'    => [ '<img src="https://example.com/image.jpg" srcset="existing-300w.jpg 300w" sizes = "100vw" alt="Test" />', 'sizes = "100vw"' ],
		];
	}

	/**
	 * A data: URI in a lazy loader's data-src or data-srcset is not the image's own source: the real src still
	 * gets its srcset, and the lazy-load attributes stay as they are.
	 */
	public function test_add_missing_srcset_attributes_ignores_data_uri_in_lazy_load_attributes(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		$tag         = '<img data-src="' . $placeholder . '" data-srcset="' . $placeholder . '" src="https://example.com/image.jpg" alt="Test" />';

		$result = $tag_replacer->add_missing_srcset_attributes( $tag, [ [ 'w' => 200, 'h' => 150, 'd' => 1, 's' => '200w', 'b' => 320 ] ], 'https://example.com/image.jpg', false );
		$img    = $this->get_img_element( $result );

		$this->assertSame( 'https://example.com/image.jpg 200w', $img->getAttribute( 'srcset' ) );
		$this->assertSame( $placeholder, $img->getAttribute( 'data-srcset' ) );
	}

	/**
	 * enhance_existing_sizes() merges into the real sizes attribute, not into a data-sizes before it.
	 */
	public function test_enhance_existing_sizes_enhances_only_the_real_sizes(): void {
		$tag_replacer = new Optml_Tag_Replacer();

		$tag = '<img src="https://example.com/image.jpg" data-sizes="auto" sizes="(max-width: 480px) 300px" alt="Test" />';

		$result = $tag_replacer->enhance_existing_sizes( $tag, [ '(max-width: 320px) 200px' ], false );
		$img    = $this->get_img_element( $result );

		$this->assertSame( 'auto', $img->getAttribute( 'data-sizes' ) );
		$this->assertSame( '(max-width: 480px) 300px, (max-width: 320px) 200px', $img->getAttribute( 'sizes' ) );
	}

	/**
	 * Parse a single img tag string and return its DOM element
	 */
	private function get_img_element( $tag_html ) {
		$dom = new DOMDocument();
		$previous_setting = libxml_use_internal_errors( true );
		$dom->loadHTML( $tag_html );
		libxml_use_internal_errors( $previous_setting );

		return $dom->getElementsByTagName( 'img' )->item( 0 );
	}

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

	}
}
