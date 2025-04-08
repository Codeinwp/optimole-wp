<?php

namespace OptimoleWP\PageProfiler\Storage;

/**
 * WordPress object cache implementation for page profiler data storage.
 *
 * This class provides methods to store, retrieve, and manage page profiler data
 * using WordPress's object cache system.
 */
class ObjectCache extends Base {
	/**
	 * Cache group name for Optimole page profiler data.
	 *
	 * @var string
	 */
	const GROUP = 'optimole_page_profiler';

	/**
	 * Default cache expiration time in seconds (7 days).
	 *
	 * @var int
	 */
	const EXPIRATION = 7 * DAY_IN_SECONDS;

	/**
	 * The cache expiration time in seconds.
	 *
	 * @var int
	 */
	private $expiration;

	/**
	 * Initialize the object cache storage.
	 *
	 * Sets up the cache expiration time, which can be modified using the
	 * 'optml_page_profiler_object_cache_expiration' filter.
	 */
	public function __construct() {
		$this->expiration = apply_filters( 'optml_page_profiler_object_cache_expiration', self::EXPIRATION );
	}

	/**
	 * Store data in the object cache.
	 *
	 * @param string $key  The unique identifier for the data.
	 * @param array  $data The data to store.
	 * @return bool True on success, false on failure.
	 */
	public function store( string $key, array $data ) {
		return wp_cache_set( $key, $data, self::GROUP, $this->expiration );
	}

	/**
	 * Retrieve data from the object cache.
	 *
	 * @param string $key The unique identifier for the data to retrieve.
	 * @return array|false The stored data or false if not found.
	 */
	public function get( string $key ) {
		return wp_cache_get( $key, self::GROUP );
	}

	/**
	 * Delete data from the object cache.
	 *
	 * @param string $key The unique identifier for the data to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( string $key ) {
		return wp_cache_delete( $key, self::GROUP );
	}

	/**
	 * Delete all data from the object cache group.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete_all() {
		return wp_cache_flush_group( self::GROUP );
	}
}
