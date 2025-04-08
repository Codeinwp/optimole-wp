<?php

namespace OptimoleWP\PageProfiler\Storage;

/**
 * Abstract base class for storage implementations.
 *
 * This class defines the interface for storage operations that concrete
 * implementations must provide.
 */
abstract class Base {

	/**
	 * Store data with the given key.
	 *
	 * @param string $key  The unique identifier for the data.
	 * @param array  $data The data to store.
	 */
	abstract public function store( string $key, array $data );

	/**
	 * Retrieve data by key.
	 *
	 * @param string $key The unique identifier for the data to retrieve.
	 * @return array|false The stored data or null if not found.
	 */
	abstract public function get( string $key );

	/**
	 * Delete data by key.
	 *
	 * @param string $key The unique identifier for the data to delete.
	 */
	abstract public function delete( string $key );

	/**
	 * Delete all stored data.
	 */
	abstract public function delete_all();
}
