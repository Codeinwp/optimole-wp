<?php
/**
 * Tests for object-shaped page-profiler storage values.
 *
 * @package Optimole-WP
 */

use OptimoleWP\BgOptimizer\Lazyload;
use OptimoleWP\PageProfiler\Profile;
use OptimoleWP\PageProfiler\Storage\Base as ProfilerStorage;
use OptimoleWP\PageProfiler\Storage\ObjectCache;
use OptimoleWP\PageProfiler\Storage\Transients;

/**
 * Class Test_Page_Profiler_Shape.
 */
class Test_Page_Profiler_Shape extends WP_UnitTestCase {
	const ABOVE_FOLD_IMAGE_ID = 1579989818;
	const OTHER_IMAGE_ID = 9999999;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'test123',
				'cdn_secret' => '12345',
				'whitelist'  => [ 'example.com' ],
			]
		);
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'lazyload_type', 'viewport' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		Profile::reset_current_profile();
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		Profile::reset_current_profile();
		wp_cache_flush();
	}

	/**
	 * Build a valid device profile payload as an array.
	 *
	 * @param array $overrides Optional overrides.
	 * @return array
	 */
	private function arrayProfile( $overrides = [] ) {
		return array_merge(
			[
				'af'  => [ self::ABOVE_FOLD_IMAGE_ID => true ],
				'bg'  => [
					'[style*="background-image:url("]' => [
						'.hero' => [ 'https://example.com/bg.jpg' ],
					],
				],
				'lcp' => [
					'type'    => 'img',
					'imageId' => self::ABOVE_FOLD_IMAGE_ID,
				],
			],
			$overrides
		);
	}

	/**
	 * JSON-decode a payload without associative arrays (Redis/JSON object-cache shape).
	 *
	 * @param array $payload Array payload.
	 * @return object
	 */
	private function objectProfile( $payload = null ) {
		if ( null === $payload ) {
			$payload = $this->arrayProfile();
		}
		return json_decode( wp_json_encode( $payload ) );
	}

	/**
	 * Seed transients with object-shaped payloads and load them through storage.
	 *
	 * @param mixed $mobile  Mobile payload, or false to skip.
	 * @param mixed $desktop Desktop payload, or false to skip.
	 * @param mixed $global  Global payload, or false to skip.
	 * @return string Profile ID.
	 */
	private function loadProfileFromStorage( $mobile, $desktop, $global = false ) {
		$profile_id = 'shape_' . wp_generate_uuid4();
		if ( false !== $mobile ) {
			set_transient( Transients::PREFIX . $profile_id . '_' . Profile::DEVICE_TYPE_MOBILE, $mobile, HOUR_IN_SECONDS );
		}
		if ( false !== $desktop ) {
			set_transient( Transients::PREFIX . $profile_id . '_' . Profile::DEVICE_TYPE_DESKTOP, $desktop, HOUR_IN_SECONDS );
		}
		if ( false !== $global ) {
			set_transient( Transients::PREFIX . $profile_id, $global, HOUR_IN_SECONDS );
		}
		Profile::reset_current_profile();
		Profile::set_current_profile_id( $profile_id );
		Optml_Manager::instance()->page_profiler->set_current_profile_data();
		return $profile_id;
	}

	/**
	 * @dataProvider normalizeValueProvider
	 * @param mixed $input    Raw value.
	 * @param mixed $expected Expected normalize result.
	 */
	public function test_normalize_value( $input, $expected ) {
		$this->assertSame( $expected, ProfilerStorage::normalize_value( $input ) );
	}

	/**
	 * Data provider for normalize_value.
	 *
	 * @return array
	 */
	public function normalizeValueProvider() {
		return [
			'false'        => [ false, false ],
			'null'         => [ null, false ],
			'string'       => [ 'corrupt', false ],
			'integer'      => [ 0, false ],
			'empty_array'  => [ [], [] ],
			'flat_array'   => [
				[ 'af' => [ 1 => true ] ],
				[ 'af' => [ 1 => true ] ],
			],
		];
	}

	/**
	 * Nested stdClass trees become associative arrays, including numeric keys.
	 */
	public function test_normalize_value_converts_nested_stdclass() {
		$object     = $this->objectProfile();
		$normalized = ProfilerStorage::normalize_value( $object );

		$this->assertIsArray( $normalized );
		$this->assertIsArray( $normalized['af'] );
		$this->assertTrue( $normalized['af'][ self::ABOVE_FOLD_IMAGE_ID ] );
		$this->assertIsArray( $normalized['bg'] );
		$this->assertIsArray( $normalized['lcp'] );
		$this->assertSame( 'img', $normalized['lcp']['type'] );
		$this->assertSame( self::ABOVE_FOLD_IMAGE_ID, $normalized['lcp']['imageId'] );
	}

	/**
	 * Top-level array with object-shaped members is still coerced.
	 */
	public function test_normalize_value_converts_object_members_inside_array() {
		$payload    = [
			'af'  => (object) [ (string) self::ABOVE_FOLD_IMAGE_ID => true ],
			'bg'  => (object) [],
			'lcp' => (object) [ 'type' => 'img', 'imageId' => self::ABOVE_FOLD_IMAGE_ID ],
		];
		$normalized = ProfilerStorage::normalize_value( $payload );

		$this->assertIsArray( $normalized['af'] );
		$this->assertTrue( ! empty( $normalized['af'][ self::ABOVE_FOLD_IMAGE_ID ] ) );
		$this->assertIsArray( $normalized['lcp'] );
		$this->assertSame( 'img', $normalized['lcp']['type'] );
	}

	/**
	 * Object cache get() returns arrays when the backend stored stdClass.
	 */
	public function test_object_cache_get_normalizes_stdclass() {
		$storage = new ObjectCache();
		$key     = 'shape_oc_' . wp_generate_uuid4();
		wp_cache_set( $key, $this->objectProfile(), ObjectCache::GROUP );

		$retrieved = $storage->get( $key );
		$this->assertIsArray( $retrieved );
		$this->assertTrue( $retrieved['af'][ self::ABOVE_FOLD_IMAGE_ID ] );
	}

	/**
	 * Object cache miss stays false.
	 */
	public function test_object_cache_get_miss_returns_false() {
		$storage = new ObjectCache();
		$this->assertFalse( $storage->get( 'missing_profiler_key_' . wp_generate_uuid4() ) );
	}

	/**
	 * Object cache still returns stored arrays unchanged.
	 */
	public function test_object_cache_get_preserves_arrays() {
		$storage = new ObjectCache();
		$key     = 'shape_oc_array_' . wp_generate_uuid4();
		$payload = $this->arrayProfile();
		$storage->store( $key, $payload );

		$this->assertSame( $payload, $storage->get( $key ) );
	}

	/**
	 * Corrupt object-cache values are treated as a miss.
	 */
	public function test_object_cache_get_rejects_scalars() {
		$storage = new ObjectCache();
		$key     = 'shape_oc_bad_' . wp_generate_uuid4();
		wp_cache_set( $key, 'not-a-profile', ObjectCache::GROUP );

		$this->assertFalse( $storage->get( $key ) );
	}

	/**
	 * Transient get() returns arrays when the stored value is stdClass.
	 */
	public function test_transients_get_normalizes_stdclass() {
		$storage = new Transients();
		$key     = 'shape_tr_' . wp_generate_uuid4();
		set_transient( Transients::PREFIX . $key, $this->objectProfile(), HOUR_IN_SECONDS );

		$retrieved = $storage->get( $key );
		$this->assertIsArray( $retrieved );
		$this->assertTrue( $retrieved['af'][ self::ABOVE_FOLD_IMAGE_ID ] );
	}

	/**
	 * Transient miss stays false.
	 */
	public function test_transients_get_miss_returns_false() {
		$storage = new Transients();
		$this->assertFalse( $storage->get( 'missing_transient_' . wp_generate_uuid4() ) );
	}

	/**
	 * The reported crash: indexing object-shaped device data as an array.
	 */
	public function test_is_in_all_viewports_does_not_fatal_on_stdclass() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertTrue( $profiler->is_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertFalse( $profiler->is_in_all_viewports( self::OTHER_IMAGE_ID ) );
	}

	/**
	 * Object-shaped above-fold map only (array wrapper, object `af`).
	 */
	public function test_is_in_all_viewports_with_object_shaped_af_member() {
		$mobile        = $this->arrayProfile();
		$desktop       = $this->arrayProfile();
		$mobile['af']  = (object) [ (string) self::ABOVE_FOLD_IMAGE_ID => true ];
		$desktop['af'] = (object) [ (string) self::ABOVE_FOLD_IMAGE_ID => true ];
		$this->loadProfileFromStorage( $mobile, $desktop );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertTrue( $profiler->is_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
	}

	/**
	 * Missing or empty object-shaped device data is treated as unavailable.
	 */
	public function test_is_in_all_viewports_empty_object_is_unavailable() {
		$this->loadProfileFromStorage( new stdClass(), $this->objectProfile() );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertFalse( $profiler->is_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertFalse( $profiler->is_data_available() );
	}

	/**
	 * is_in_any_viewport must not fatal on object-shaped data.
	 */
	public function test_is_in_any_viewport_does_not_fatal_on_stdclass() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertSame( Profile::DEVICE_TYPE_MOBILE, $profiler->is_in_any_viewport( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertFalse( $profiler->is_in_any_viewport( self::OTHER_IMAGE_ID ) );
	}

	/**
	 * LCP lookup must not fatal on object-shaped `lcp`.
	 */
	public function test_is_lcp_image_in_all_viewports_does_not_fatal_on_stdclass() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertTrue( $profiler->is_lcp_image_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertFalse( $profiler->is_lcp_image_in_all_viewports( self::OTHER_IMAGE_ID ) );
	}

	/**
	 * Missing LCP imageId must not warn or fatal.
	 */
	public function test_is_lcp_image_handles_missing_image_id() {
		$payload        = $this->arrayProfile();
		$payload['lcp'] = [ 'type' => 'img' ];
		$this->loadProfileFromStorage( $payload, $payload );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertFalse( $profiler->is_lcp_image_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
	}

	/**
	 * Global missing-dimension lookups must not fatal on object-shaped global data.
	 */
	public function test_global_lookups_do_not_fatal_on_stdclass() {
		$global = (object) [
			'm' => (object) [
				(string) self::ABOVE_FOLD_IMAGE_ID => (object) [ 'w' => 100, 'h' => 80 ],
			],
			's' => (object) [
				(string) self::ABOVE_FOLD_IMAGE_ID => (object) [
					'200' => (object) [
						'w' => 200,
						'h' => 160,
						'd' => 1,
						's' => 'https://example.com/i.jpg',
						'b' => 1,
					],
				],
			],
			'c' => (object) [ (string) self::ABOVE_FOLD_IMAGE_ID => true ],
		];
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile(), $global );
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertSame( [ 'w' => 100, 'h' => 80 ], $profiler->get_missing_dimensions( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertSame( [], $profiler->get_missing_dimensions( self::OTHER_IMAGE_ID ) );
		$this->assertNotEmpty( $profiler->get_missing_srcsets( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertTrue( $profiler->get_crop_status( self::ABOVE_FOLD_IMAGE_ID ) );
		$this->assertFalse( $profiler->get_crop_status( self::OTHER_IMAGE_ID ) );
	}

	/**
	 * Loading current profile data from object-shaped transients yields arrays.
	 */
	public function test_set_current_profile_data_normalizes_transients() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );
		$data     = Profile::get_current_profile_data();
		$profiler = Optml_Manager::instance()->page_profiler;

		$this->assertIsArray( $data[ Profile::DEVICE_TYPE_MOBILE ] );
		$this->assertIsArray( $data[ Profile::DEVICE_TYPE_DESKTOP ] );
		$this->assertTrue( $data[ Profile::DEVICE_TYPE_MOBILE ]['af'][ self::ABOVE_FOLD_IMAGE_ID ] );
		$this->assertTrue( $profiler->is_in_all_viewports( self::ABOVE_FOLD_IMAGE_ID ) );
	}

	/**
	 * get_profile_data() also normalizes object-shaped storage.
	 */
	public function test_get_profile_data_normalizes_stdclass() {
		$profile_id = 'shape_get_' . wp_generate_uuid4();
		set_transient(
			Transients::PREFIX . $profile_id . '_' . Profile::DEVICE_TYPE_DESKTOP,
			$this->objectProfile(),
			HOUR_IN_SECONDS
		);

		$data = Optml_Manager::instance()->page_profiler->get_profile_data( $profile_id );
		$this->assertIsArray( $data[ Profile::DEVICE_TYPE_DESKTOP ] );
		$this->assertTrue( $data[ Profile::DEVICE_TYPE_DESKTOP ]['af'][ self::ABOVE_FOLD_IMAGE_ID ] );
	}

	/**
	 * exists() is true after object-shaped data is normalized, false for scalars.
	 */
	public function test_exists_with_object_shaped_and_corrupt_storage() {
		$profiler = Optml_Manager::instance()->page_profiler;
		$good_id  = 'shape_exists_good_' . wp_generate_uuid4();
		$bad_id   = 'shape_exists_bad_' . wp_generate_uuid4();

		set_transient(
			Transients::PREFIX . $good_id . '_' . Profile::DEVICE_TYPE_DESKTOP,
			$this->objectProfile(),
			HOUR_IN_SECONDS
		);
		set_transient(
			Transients::PREFIX . $bad_id . '_' . Profile::DEVICE_TYPE_DESKTOP,
			'nope',
			HOUR_IN_SECONDS
		);

		$this->assertTrue( $profiler->exists( $good_id, Profile::DEVICE_TYPE_DESKTOP ) );
		$this->assertFalse( $profiler->exists( $bad_id, Profile::DEVICE_TYPE_DESKTOP ) );
		$this->assertFalse( $profiler->exists_all( $good_id ) );
	}

	/**
	 * Personalized background CSS must not fatal when current profile came from stdClass storage.
	 */
	public function test_personalized_css_does_not_fatal_on_stdclass() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );

		$css = Lazyload::get_current_personalized_css();
		$this->assertIsString( $css );
	}

	/**
	 * Frontend HTML replacement must not fatal when transients hold stdClass profiles.
	 */
	public function test_replace_content_does_not_fatal_on_object_shaped_profile() {
		$profile_id = $this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );
		add_filter(
			'optml_page_profile_id',
			function () use ( $profile_id ) {
				return $profile_id;
			}
		);

		$html = Optml_Manager::instance()->replace_content( Test_Lazyload_Viewport::get_sample_html() );
		$this->assertNotEmpty( $html );
		$this->assertStringContainsString( '<img', $html );

		remove_all_filters( 'optml_page_profile_id' );
	}

	/**
	 * can_lazyload_for() uses the viewport lookup and must not fatal.
	 */
	public function test_can_lazyload_for_does_not_fatal_on_stdclass() {
		$this->loadProfileFromStorage( $this->objectProfile(), $this->objectProfile() );

		$replacer = Optml_Lazyload_Replacer::instance();
		$url      = 'https://example.com/test-image.jpg';
		$tag      = '<img src="' . $url . '" alt="test">';

		$this->assertIsBool( $replacer->can_lazyload_for( $url, $tag ) );
	}
}
