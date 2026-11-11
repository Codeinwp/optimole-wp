<?php

namespace OptimoleWP\Media;

/**
 * Checks that attachment file paths stay inside the uploads directory.
 *
 * Paths built from attachment metadata are moved and deleted, so a `../` in that metadata
 * must not reach files elsewhere on the server.
 *
 * @package OptimoleWP\Media
 */
class UploadsPath {
	/**
	 * Check whether a file path, existing or not, is inside the uploads directory.
	 *
	 * The folder holding the file must exist; `..` and symlinks are resolved before the check.
	 *
	 * @param string $path Absolute file path.
	 *
	 * @return bool True when the file is inside the uploads directory.
	 */
	public static function contains( string $path ): bool {
		$name = basename( $path );
		if ( '' === $name || '.' === $name || '..' === $name ) {
			return false;
		}

		$uploads = realpath( wp_get_upload_dir()['basedir'] );
		$dir     = realpath( dirname( $path ) );
		if ( false === $uploads || false === $dir ) {
			return false;
		}

		return 0 === strpos( trailingslashit( $dir ), trailingslashit( $uploads ) );
	}
}
