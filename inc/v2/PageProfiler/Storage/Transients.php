<?php

namespace OptimoleWP\PageProfiler\Storage;

/**
 * Class Transients
 *
 * Handles storage and retrieval of page profiler data using WordPress transients.
 * Transients provide a way to temporarily store cached data with an expiration time.
 *
 * @package OptimoleWP\PageProfiler\Storage
 */
class Transients extends Base {
	/**
	 * Prefix for all transient keys to avoid conflicts with other plugins.
	 */
	const PREFIX = '_oprof_';

	/**
	 * Default expiration time for transients (7 days in seconds).
	 */
	const EXPIRATION_TIME = 7 * DAY_IN_SECONDS;

	/**
	 * The actual expiration time to use, which can be filtered.
	 *
	 * @var int
	 */
	private $expiration_time;

	/**
	 * Constructor.
	 *
	 * Sets up the expiration time, allowing it to be modified via WordPress filters.
	 */
	public function __construct() {
		$this->expiration_time = apply_filters( 'optml_page_profiler_transient_expiration', self::EXPIRATION_TIME );
	}

	/**
	 * Generates the full transient key with prefix.
	 *
	 * @param string $key The base key to prefix.
	 * @return string The prefixed key.
	 */
	private function get_key( string $key ) {
		return self::PREFIX . $key;
	}

	/**
	 * Stores data in a transient.
	 *
	 * @param string $key The key to store the data under.
	 * @param array  $data The data to store.
	 * @return void
	 */
	public function store( string $key, array $data ) {
		set_transient( $this->get_key( $key ), $data, $this->expiration_time );
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'stored: ' . $this->get_key( $key ) . '|' . print_r( $data, true ) );
		}
	}

	/**
	 * Retrieves data from a transient.
	 *
	 * @param string $key The key to retrieve data for.
	 * @return mixed The stored data or false if the transient doesn't exist or has expired.
	 */
	public function get( string $key ) {
		return get_transient( $this->get_key( $key ) );
	}

	/**
	 * Deletes a specific transient.
	 *
	 * @param string $key The key of the transient to delete.
	 * @return void
	 */
	public function delete( string $key ) {
		delete_transient( $this->get_key( $key ) );
	}

	/**
	 * Deletes all transients created by this class.
	 *
	 * @return void
	 * @todo Implement this method to clear all transients with the defined prefix.
	 */
	public function delete_all() {
		// Not implemented for now.
	}
}
