<?php

/**
 * Class Optml_Attachment_Cache.
 */
class Optml_Attachment_Cache {
	const CACHE_GROUP = 'om_att';
	/**
	 * Local cache map.
	 *
	 * @var array
	 */
	private static $cache_map = [];
	/**
	 * Reset the memory cache.
	 */
	public static function reset() {
		self::$cache_map = [];
	}
	/**
	 * Get the cached attachment ID.
	 *
	 * @param string $url the URL of the attachment.
	 *
	 * @return bool|mixed
	 */
	public static function get_cached_attachment_id( $url ) {

		// We cache also in memory to avoid calling DB every time when not using Object Cache.
		$cache_key = self::get_cache_key( $url );
		if ( isset( self::$cache_map[ $cache_key ] ) ) {
			return self::$cache_map[ $cache_key ];
		}

		$value                         = wp_using_ext_object_cache()
			? wp_cache_get( $cache_key, self::CACHE_GROUP )
			: get_transient( self::CACHE_GROUP . $cache_key );
		self::$cache_map[ $cache_key ] = $value;

		return $value;
	}

	/**
	 * Set the cached attachment ID.
	 *
	 * @param string $url the URL of the attachment.
	 * @param int    $id the attachment ID.
	 *
	 * @return void
	 */
	public static function set_cached_attachment_id( $url, $id ) {
		$cache_key = self::get_cache_key( $url );
		// We cache also in memory to avoid calling DB every time when not using Object Cache.
		self::$cache_map[ $cache_key ] = $id;
		// If the ID is not found we cache for 10 minutes, otherwise for a week.
		// We try to reduce the cache time when is not found to
		// avoid caching for situation when this might be temporary.
		$expiration = $id === 0 ? ( 10 * MINUTE_IN_SECONDS ) : WEEK_IN_SECONDS;
		wp_using_ext_object_cache()
			? wp_cache_set( $cache_key, $id, self::CACHE_GROUP, $expiration )
			: set_transient( self::CACHE_GROUP . $cache_key, $id, $expiration );
	}

	/**
	 * Generate cache key for URL.
	 *
	 * @param string $url the URL to generate the cache key for.
	 *
	 * @return string
	 */
	private static function get_cache_key( $url ) {
		$url = strtok( $url, '?' );

		return 'id_' . crc32( $url );
	}
}
