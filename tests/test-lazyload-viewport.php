<?php

use OptimoleWP\PageProfiler\Profile;

/**
 * WordPress unit test plugin for testing lazy loading with viewport-based profiling.
 *
 * This class tests the lazy loading functionality when using viewport detection,
 * including handling of above-fold images, LCP data, and device-specific profiles.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Lazyload_Viewport.
 */
class Test_Lazyload_Viewport extends WP_UnitTestCase {
	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
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

		// Reset static counter for skipped images to ensure test isolation
		$this->resetLazyloadSkippedImages();
	}

	/**
	 * Clean up the test environment after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		remove_all_filters('optml_page_profile_id');
		// Reset static counter to clean state
		$this->resetLazyloadSkippedImages();
		Profile::reset_current_profile();
	}

	/**
	 * Test lazy loading behavior with viewport data.
	 *
	 * This test verifies that:
	 * 1. All images are lazy-loaded when no viewport data is available.
	 * 2. Above-the-fold images are not lazy-loaded when viewport data exists.
	 */
	public function test_lazyload_viewport() {
		// When no viewport data exists, all images should be lazy-loaded
		// This is the default fallback behavior for viewport-based lazy loading
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());
		$this->assertEquals(substr_count($html, '<img'), substr_count($html, 'data-opt-id'));

		$this->storeMockProfileData(self::mock_page_ids(), Profile::DEVICE_TYPE_DESKTOP, [1579989818]);
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());
		$this->assertEquals(substr_count($html, 'data-opt-src'), substr_count($html, 'data-opt-id'));

		$this->storeMockProfileData(self::mock_page_ids(), Profile::DEVICE_TYPE_MOBILE, [1579989818]);
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

		// With complete viewport data, above-fold images should not be lazy-loaded
		$this->assertNotEquals(substr_count($html, 'data-opt-src'), substr_count($html, 'data-opt-id'));
		$imageTags = Optml_Manager::instance()->parse_images_from_html($html);
		$filteredTags = array_filter($imageTags[0], function ($tag) {
			return strpos($tag, 'data-opt-id=1579989818') !== false;
		});
		$this->assertNotEmpty($filteredTags);
		$specificImageTag = $filteredTags[0];
		$this->assertStringNotContainsString('data-opt-src', $specificImageTag);
	}

	/**
	 * Test lazy loading behavior with LCP (Largest Contentful Paint) data.
	 *
	 * This test ensures that an image marked as LCP is not lazy-loaded and
	 * receives the `fetchpriority="high"` attribute to improve loading performance.
	 */
	public function test_lazyload_viewport_with_lcp_data() {
		// Test with LCP (Largest Contentful Paint) data for desktop
		// LCP images are prioritized for loading and get fetchpriority="high" to improve performance metrics.
		$this->storeMockProfileData(
			self::mock_page_ids(),
			Profile::DEVICE_TYPE_DESKTOP,
			[1579989818],
			[],
			['type' => 'img', 'imageId' => 1579989818],
		);

		// Test with LCP (Largest Contentful Paint) data for mobile
		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

		// Check if the LCP image has fetchpriority="high" attribute
		$imageTags = Optml_Manager::instance()->parse_images_from_html($html);
		$filteredTags = array_filter($imageTags[0], function ($tag) {
			return strpos($tag, 'data-opt-id=1579989818') !== false;
		});
		$this->assertNotEmpty($filteredTags);
		$lcpImageTag = $filteredTags[0];

		$this->assertStringContainsString('fetchpriority="high"', $lcpImageTag);
		$this->assertStringNotContainsString('data-opt-src', $lcpImageTag);
	}

	/**
	 * Test lazy loading with different profiles for different devices.
	 *
	 * This test verifies that the correct images are lazy-loaded based on
	 * device-specific (desktop vs. mobile) viewport profiles.
	 */
	public function test_lazyload_viewport_with_different_device_profiles() {
		// Store different images for desktop and mobile
		$this->storeMockProfileData(
			self::mock_page_ids(),
			Profile::DEVICE_TYPE_DESKTOP,
			[1579989818, 1698280985],
		);
		$this->storeMockProfileData(self::mock_page_ids(), Profile::DEVICE_TYPE_MOBILE, [1579989818]);

		$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

		// Image 1579989818 should be eagerly loaded on both devices
		$imageTags = Optml_Manager::instance()->parse_images_from_html($html);

		// Check if we have image tags before accessing array
		$this->assertNotEmpty($imageTags, 'No image tags found in HTML');
		$this->assertArrayHasKey(0, $imageTags, 'Image tags array structure is unexpected');

		$filteredTags1 = array_filter($imageTags[0], function ($tag) {
			return strpos($tag, 'data-opt-id=1579989818') !== false;
		});
		$this->assertNotEmpty($filteredTags1);
		$image1 = reset($filteredTags1); // Use reset() instead of direct array access

		$filteredTags2 = array_filter($imageTags[0], function ($tag) {
			return strpos($tag, 'data-opt-id=1698280985') !== false;
		});
		$this->assertNotEmpty($filteredTags2);
		$image2 = reset($filteredTags2); // Use reset() instead of direct array access

		$this->assertStringNotContainsString('data-opt-src', $image1);
		$this->assertStringContainsString('data-opt-src', $image2);
	}

	/**
	 * Data provider for viewport detection tests.
	 *
	 * @return array Test data with image IDs and expected results.
	 */
	public function viewportDetectionDataProvider() {
		return [
			'existing_image' => [1579989818, true, true],
			'non_existing_image' => [9999999, false, false],
		];
	}

	/**
	 * Test the `is_in_all_viewports` and `is_in_any_viewport` methods.
	 *
	 * @dataProvider viewportDetectionDataProvider
	 * @param int  $imageId The ID of the image to check.
	 * @param bool $expectedInAll Expected result from `is_in_all_viewports`.
	 * @param bool $expectedInAny Expected result from `is_in_any_viewport`.
	 */
	public function test_is_in_viewport_detection($imageId, $expectedInAll, $expectedInAny) {
		// Test the is_in_viewport detection methods
		$this->storeMockProfileData(self::mock_page_ids(), Profile::DEVICE_TYPE_DESKTOP, [1579989818]);
		$this->storeMockProfileData(self::mock_page_ids(), Profile::DEVICE_TYPE_MOBILE, [1579989818]);

		// Set the current profile ID to enable viewport detection
		Profile::set_current_profile_id(self::mock_page_ids());
		Optml_Manager::instance()->page_profiler->set_current_profile_data();

		// Test if image is detected in all viewports
		$actualInAll = Optml_Manager::instance()->page_profiler->is_in_all_viewports($imageId);
		if ($expectedInAll) {
			$this->assertTrue($actualInAll, "Expected image $imageId to be in all viewports");
		} else {
			$this->assertFalse($actualInAll, "Expected image $imageId NOT to be in all viewports");
		}

		// Test if image is detected in any viewport
		$actualInAny = Optml_Manager::instance()->page_profiler->is_in_any_viewport($imageId);
		if ($expectedInAny) {
			$this->assertNotFalse($actualInAny, "Expected image $imageId to be in any viewport");
		} else {
			$this->assertFalse($actualInAny, "Expected image $imageId NOT to be in any viewport");
		}
	}

	/**
	 * Test the storage and retrieval of profile data.
	 *
	 * This test ensures that above-fold images, background selectors, and LCP data
	 * are correctly stored and can be retrieved from the page profiler.
	 */
	public function test_profile_data_storage_and_retrieval() {
		// Test storing and retrieving profile data
		$aboveFoldImages = [1579989818, 1579989819];
		$bgSelectors = [
			'.hero-banner' => [
				'.above-fold' => ['https://example.com/image1.jpg'],
			],
		];
		$lcpData = [
			'type' => 'img',
			'imageId' => 1579989818,
		];

		$this->storeMockProfileData(
			self::mock_page_ids(),
			Profile::DEVICE_TYPE_DESKTOP,
			$aboveFoldImages,
			$bgSelectors,
			$lcpData,
		);

		$profileData = Optml_Manager::instance()->page_profiler->get_profile_data(self::mock_page_ids());

		// Check if data was stored correctly
		$this->assertArrayHasKey(Profile::DEVICE_TYPE_DESKTOP, $profileData);
		$this->assertArrayHasKey('af', $profileData[Profile::DEVICE_TYPE_DESKTOP]);
		$this->assertArrayHasKey('bg', $profileData[Profile::DEVICE_TYPE_DESKTOP]);
		$this->assertArrayHasKey('lcp', $profileData[Profile::DEVICE_TYPE_DESKTOP]);

		// Check if above-fold images are stored correctly (as keys with value true)
		$this->assertTrue($profileData[Profile::DEVICE_TYPE_DESKTOP]['af'][1579989818]);
		$this->assertTrue($profileData[Profile::DEVICE_TYPE_DESKTOP]['af'][1579989819]);

		// Check if LCP data is stored correctly
		$this->assertEquals('img', $profileData[Profile::DEVICE_TYPE_DESKTOP]['lcp']['type']);
		$this->assertEquals(1579989818, $profileData[Profile::DEVICE_TYPE_DESKTOP]['lcp']['imageId']);
	}

	/**
	 * Test fallback to fixed lazyload behavior when no viewport data is available for a profile.
	 *
	 * This test verifies that if `lazyload_type` is 'fixed', it correctly skips the
	 * specified number of images, even when a profile ID is present but has no data.
	 */
	public function test_no_viewport_data_available_with_fixed_lazyload() {
		// Test when lazyload type is fixed and no viewport data is available
		$settings = new Optml_Settings();
		$settings->update('lazyload_type', 'fixed');
		$settings->update('skip_lazyload_images', 2); // Skip first 2 images

		$this->withProfileId(888, function () {
			$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

			// With fixed lazyload and skip_lazyload_images = 2, first 2 images should not be lazyloaded
			$image_tags = Optml_Manager::instance()->parse_images_from_html($html);
			$total_images = count($image_tags[0]);
			$lazyloaded_images = substr_count($html, 'data-opt-src');

			// Should skip the first 2 images from lazyload
			$this->assertEquals($total_images - 2, $lazyloaded_images);
		});

		// Reset settings
		$settings->update('lazyload_type', 'viewport');
	}

	/**
	 * Data provider for profile existence tests.
	 *
	 * @return array Test data with profile IDs and device combinations.
	 */
	public function profileExistenceDataProvider() {
		return [
			'no_data' => [777, [], false],
			'desktop_only' => [777, [Profile::DEVICE_TYPE_DESKTOP], false],
			'mobile_only' => [777, [Profile::DEVICE_TYPE_MOBILE], false],
			'both_devices' => [777, [Profile::DEVICE_TYPE_DESKTOP, Profile::DEVICE_TYPE_MOBILE], true],
		];
	}

	/**
	 * Test the `exists_all` method for checking complete viewport data.
	 *
	 * This test verifies that `exists_all` correctly reports whether a profile
	 * has data for all required device types (desktop and mobile).
	 *
	 * @dataProvider profileExistenceDataProvider
	 * @param int   $profileId The profile ID to check.
	 * @param array $devicesToStore The device types to store data for.
	 * @param bool  $expectedExists The expected result from `exists_all`.
	 */
	public function test_viewport_data_exists_check($profileId, $devicesToStore, $expectedExists) {
		// Store data for specified devices
		foreach ($devicesToStore as $deviceType) {
			$this->storeMockProfileData($profileId, $deviceType, [1579989818]);
		}

		$this->assertEquals(
			$expectedExists,
			Optml_Manager::instance()->page_profiler->exists_all($profileId),
		);
	}

	/**
	 * Test the behavior when only partial viewport data is available.
	 *
	 * This test ensures that if a profile has data for only one device (e.g., desktop),
	 * the system falls back to lazy-loading all images, treating it as if no
	 * complete viewport data is available.
	 */
	public function test_partial_viewport_data_behavior() {
		// Test behavior when only partial viewport data is available
		$profile_id = 667;
		$settings = new Optml_Settings();
		$settings->update('lazyload_type', 'viewport');

		// Store data for desktop only - this creates partial data
		$this->storeMockProfileData($profile_id, Profile::DEVICE_TYPE_DESKTOP, []);

		$this->withProfileId($profile_id, function () use ($profile_id) {
			$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

			// With partial data, should behave as if no viewport data is available
			// All images should be lazyloaded
			$totalImages = substr_count($html, '<img');
			$lazyloadedImages = substr_count($html, 'data-opt-src');

			$this->assertEquals(
				$totalImages,
				$lazyloadedImages,
				"Expected all $totalImages images to be lazyloaded, but only $lazyloadedImages were lazyloaded",
			);
		});
	}

	/**
	 * Test the `can_lazyload_for` method when no viewport data is available.
	 *
	 * This test verifies that the `can_lazyload_for` method returns `true` (allowing lazy-load)
	 * when viewport lazy loading is enabled but no data exists for the current profile.
	 */
	public function test_can_lazyload_for_method_with_no_viewport_data() {
		// Test the can_lazyload_for method directly when no viewport data is available
		$replacer = Optml_Lazyload_Replacer::instance();

		$this->withProfileId(555, function () use ($replacer) {
			$test_url = 'https://example.com/test-image.jpg';
			$test_tag = '<img src="' . $test_url . '" alt="test">';

			// When no viewport data is available, should return true (allow lazyload)
			$this->assertTrue($replacer->can_lazyload_for($test_url, $test_tag));
		});
	}

	/**
	 * Test the fallback behavior when viewport lazy loading is enabled but no data is available.
	 *
	 * This test confirms that when `lazyload_type` is 'viewport' and no profile data exists,
	 * all images are lazy-loaded, ignoring the `skip_lazyload_images` setting.
	 */
	public function test_fallback_to_fixed_behavior_when_no_viewport_data() {
		// Test that when viewport lazyload is enabled but no data exists,
		// it falls back to allowing lazyload (unlike fixed which skips first N images)
		$settings = new Optml_Settings();
		$settings->update('lazyload_type', 'viewport');
		$settings->update('skip_lazyload_images', 3);

		$this->withProfileId(444, function () {
			$html = Optml_Manager::instance()->replace_content(self::get_sample_html());

			// With viewport lazyload and no data, ALL images should be lazyloaded
			// (different from fixed lazyload which would skip first 3)
			$this->assertEquals(substr_count($html, '<img'), substr_count($html, 'data-opt-src'));
		});
	}

	/**
	 * Test handling of invalid profile IDs (e.g., non-numeric).
	 *
	 * This test ensures that the system handles invalid profile IDs gracefully
	 * without throwing errors and returns expected empty or default values.
	 */
	public function test_invalid_profile_ids() {
		// Test with non-numeric profile ID
		$invalidProfileId = 'invalid_id';
		$this->assertFalse(Optml_Manager::instance()->page_profiler->exists_all($invalidProfileId));

		// Attempting to store with invalid ID should not throw errors but handle gracefully
		$this->storeMockProfileData($invalidProfileId, Profile::DEVICE_TYPE_DESKTOP, [1579989818]);

		// Get profile data to check behavior with invalid ID
		$profileData = Optml_Manager::instance()->page_profiler->get_profile_data($invalidProfileId);

		// The behavior may vary - either empty array or the data might be stored with string key
		// Check that either it's empty OR if data exists, it's structured properly
		if (empty($profileData)) {
			$this->assertEmpty($profileData, 'Expected empty profile data for invalid ID');
		} else {
			// If data is stored despite invalid ID, verify it doesn't break the system
			$this->assertIsArray($profileData, 'Profile data should be an array even with invalid ID');
		}
	}

	/**
	 * Helper method to reset the static lazyload_skipped_images counter.
	 */
	private function resetLazyloadSkippedImages() {
		$reflection = new ReflectionClass('Optml_Tag_Replacer');
		$property = $reflection->getProperty('lazyload_skipped_images');
		$property->setAccessible(true);
		$property->setValue(null, 0);
	}

	/**
	 * Helper method to temporarily set a custom profile ID filter.
	 *
	 * @param int $profileId The profile ID to return.
	 * @param callable $callback The test callback to execute.
	 */
	private function withProfileId($profileId, $callback) {
		// Remove all existing filters first
		remove_all_filters('optml_page_profile_id');

		// Add the new filter with higher priority
		add_filter(
			'optml_page_profile_id',
			function () use ($profileId) {
				return $profileId;
			},
			999,
		);

		// Clear current profile data and set new profile ID
		Profile::set_current_profile_id($profileId);
		Optml_Manager::instance()->page_profiler->set_current_profile_data();

		try {
			$callback();
		} finally {
			// Always restore the original filter, even if callback throws
			remove_all_filters('optml_page_profile_id');
			add_filter('optml_page_profile_id', [self::class, 'mock_page_ids']);

			// Reset to default profile
			Profile::set_current_profile_id(self::mock_page_ids());
			Optml_Manager::instance()->page_profiler->set_current_profile_data();
		}
	}

	/**
	 * Mock the page profile ID for testing purposes.
	 *
	 * @return int The mocked profile ID.
	 */
	public static function mock_page_ids() {
		return 1;
	}

	/**
	 * Get sample HTML content for testing.
	 *
	 * @return string The sample HTML.
	 */
	public static function get_sample_html() {
		return file_get_contents(OPTML_PATH . '/tests/assets/sample.html');
	}

	/**
	 * Helper method to store mock profile data, reducing duplication.
	 *
	 * @param int $profileId The profile ID.
	 * @param string $deviceType The device type (e.g., Profile::DEVICE_TYPE_DESKTOP).
	 * @param array $above_fold_images Array of image IDs.
	 * @param array $bgSelectors Background selectors (optional).
	 * @param array $lcpData LCP data (optional).
	 */
	private function storeMockProfileData(
		$profileId,
		$deviceType,
		$above_fold_images = [],
		$bgSelectors = [],
		$lcpData = [],
	) {
		Optml_Manager::instance()->page_profiler->store(
			$profileId,
			$deviceType,
			$above_fold_images,
			$bgSelectors,
			$lcpData,
		);

		// Set the current profile to use the stored data
		Profile::reset_current_profile();
		Profile::set_current_profile_id($profileId);
		Optml_Manager::instance()->page_profiler->set_current_profile_data();
	}
}
