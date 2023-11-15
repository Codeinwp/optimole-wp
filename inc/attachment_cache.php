<?php

/**
 * Class Optml_Attachment_Cache.
 */
class Optml_Attachment_Cache {
	const CACHE_GROUP = 'optml_attachment_ids';

	/**
	 * Get the cached attachment ID.
	 *
	 * @param string $url the URL of the attachment.
	 *
	 * @return bool|mixed
	 */
	public static function get_cached_attachment_id( $url ) {
		$cache_key = self::get_cache_key( $url );

		return wp_cache_get( $cache_key, self::CACHE_GROUP );
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

		wp_cache_set( $cache_key, $id, self::CACHE_GROUP, DAY_IN_SECONDS );
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

		return 'attachment_id_' . crc32( $url );
	}
}
