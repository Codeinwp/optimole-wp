<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

/**
 * Files next to the uploads directory, reached from attachment metadata with `../`.
 */
trait Optml_Test_Outside_Uploads {

	/**
	 * Create a file next to the uploads directory, outside it.
	 *
	 * @param string $name   File name.
	 * @param string $source File to copy. The file holds "keep" when it is empty.
	 */
	private static function outside_uploads_file( string $name, string $source = '' ): string {
		$path = dirname( wp_get_upload_dir()['basedir'] ) . '/' . $name;
		'' === $source ? file_put_contents( $path, 'keep' ) : copy( $source, $path );

		return $path;
	}

	/**
	 * Build the `../` path from a folder inside uploads to a file next to the uploads directory.
	 */
	private static function relative_from( string $dir, string $file ): string {
		$parent = dirname( wp_get_upload_dir()['basedir'] );
		$depth  = substr_count( trim( substr( $dir, strlen( $parent ) ), '/' ), '/' ) + 1;

		return str_repeat( '../', $depth ) . basename( $file );
	}
}
