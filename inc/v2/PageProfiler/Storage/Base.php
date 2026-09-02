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
	 * @return array<string, mixed>|false The stored data or false if not found.
	 */
	abstract public function get( string $key );

	/**
	 * Coerce a stored profiler payload to an array.
	 *
	 * Object-cache backends that JSON-decode without associative arrays return stdClass.
	 * Nested objects (e.g. `af`, `bg`, `lcp`) are converted recursively.
	 *
	 * @param mixed $value Raw storage value.
	 * @return array<string|int, mixed>|false
	 */
	public static function normalize_value( $value ) {
		if ( false === $value || null === $value ) {
			return false;
		}

		if ( is_object( $value ) ) {
			$value = get_object_vars( $value );
		}

		if ( ! is_array( $value ) ) {
			return false;
		}

		foreach ( $value as $key => $item ) {
			if ( is_object( $item ) || is_array( $item ) ) {
				$normalized_item = self::normalize_value( $item );
				$value[ $key ]     = ( false !== $normalized_item ) ? $normalized_item : [];
			}
		}

		return $value;
	}

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
