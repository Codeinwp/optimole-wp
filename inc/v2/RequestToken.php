<?php

namespace OptimoleWP;

/**
 * Random token the page placeholders carry, so only the copies the plugin printed are replaced.
 *
 * Page content, such as a comment or a title, can hold a placeholder's text but cannot know the token.
 *
 * @package OptimoleWP
 */
class RequestToken {
	/**
	 * Random bytes in the token, printed as twice as many hex characters.
	 */
	const BYTES = 8;

	/**
	 * The token, generated on first use and reused for the rest of the request.
	 *
	 * @var string|null
	 */
	private static $token = null;

	/**
	 * Get the token for the current request.
	 *
	 * @return string The token, 16 hex characters.
	 */
	public static function get(): string {
		if ( null === self::$token ) {
			self::$token = bin2hex( random_bytes( self::BYTES ) );
		}

		return self::$token;
	}
}
