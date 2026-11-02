<?php
/**
 * Attachment cache tests.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

/**
 * Class Test_Attachment_Cache.
 */
class Test_Attachment_Cache extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		Optml_Attachment_Cache::reset();
	}

	public function tearDown(): void {
		Optml_Attachment_Cache::reset();
		parent::tearDown();
	}

	/**
	 * Read the in-memory map.
	 *
	 * @return array<string, int|numeric-string|false>
	 */
	private function memory_map() {
		$property = new ReflectionProperty( Optml_Attachment_Cache::class, 'cache_map' );
		$property->setAccessible( true );

		return $property->getValue();
	}

	/**
	 * A page with many distinct uncached image URLs must not grow the in-memory map for the whole request.
	 */
	public function test_memory_map_is_bounded_on_cache_misses() {
		$limit = Optml_Attachment_Cache::MAX_MEMORY_ENTRIES;

		for ( $i = 0; $i <= $limit; $i++ ) {
			Optml_Attachment_Cache::get_cached_attachment_id( 'https://example.org/wp-content/uploads/miss-' . $i . '.jpg' );
		}

		$this->assertCount( (int) ( $limit / 2 ) + 1, $this->memory_map() );
	}

	/**
	 * The newest URLs stay in memory when the older half is dropped, and the dropped ones are
	 * still found in the persistent cache.
	 */
	public function test_dropped_entries_are_still_found_in_the_persistent_cache() {
		$limit = Optml_Attachment_Cache::MAX_MEMORY_ENTRIES;

		for ( $i = 0; $i <= $limit; $i++ ) {
			Optml_Attachment_Cache::set_cached_attachment_id( 'https://example.org/wp-content/uploads/image-' . $i . '.jpg', $i + 1 );
		}

		$map = $this->memory_map();
		$this->assertCount( (int) ( $limit / 2 ) + 1, $map );
		$this->assertContains( $limit + 1, $map );
		$this->assertNotContains( 1, $map );

		$this->assertSame( 1, (int) Optml_Attachment_Cache::get_cached_attachment_id( 'https://example.org/wp-content/uploads/image-0.jpg' ) );
	}

	/**
	 * Updating a URL that is already in memory never drops other entries.
	 */
	public function test_updating_a_known_url_keeps_the_map() {
		$limit = Optml_Attachment_Cache::MAX_MEMORY_ENTRIES;

		for ( $i = 0; $i < $limit; $i++ ) {
			Optml_Attachment_Cache::set_cached_attachment_id( 'https://example.org/wp-content/uploads/image-' . $i . '.jpg', $i + 1 );
		}
		Optml_Attachment_Cache::set_cached_attachment_id( 'https://example.org/wp-content/uploads/image-0.jpg', 99 );

		$this->assertCount( $limit, $this->memory_map() );
		$this->assertSame( 99, Optml_Attachment_Cache::get_cached_attachment_id( 'https://example.org/wp-content/uploads/image-0.jpg' ) );
	}
}
