<?php

namespace OptimoleWP\Offload;

use Optml_Config;
use Optml_Media_Offload;
use WP_Error;

/**
 * Class ImageEditor
 *
 * Custom image editor implementation for Optimole offloaded images.
 * This class extends WP_Image_Editor to handle images that have been offloaded
 * to Optimole's servers, preventing local editing operations on remote images.
 *
 * @package OptimoleWP\Offload
 */
class ImageEditor extends \WP_Image_Editor {
	/**
	 * Tests whether this editor can handle the given file.
	 *
	 * @param array $args Arguments to test the editor.
	 * @return bool True if the file is an offloaded image, false otherwise.
	 */
	public static function test( $args = [] ) {
		if ( isset( $args['path'] ) && Optml_Media_Offload::is_uploaded_image( $args['path'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if this editor supports the given MIME type.
	 *
	 * @param string $mime_type The MIME type to check.
	 * @return bool True if the MIME type is supported, false otherwise.
	 */
	public static function supports_mime_type( $mime_type ) {
		return in_array( $mime_type, Optml_Config::$image_extensions, true );
	}

	/**
	 * Saves the image to a file.
	 *
	 * For offloaded images, this operation is not supported and always returns false.
	 *
	 * @param string $destfilename Optional. The destination filename.
	 * @param string $mime_type Optional. The MIME type of the image.
	 * @return array|WP_Error {
	 *     Array on success or WP_Error if the file failed to save.
	 *
	 *     @type string $path      Path to the image file.
	 *     @type string $file      Name of the image file.
	 *     @type int    $width     Image width.
	 *     @type int    $height    Image height.
	 *     @type string $mime-type The mime type of the image.
	 *     @type int    $filesize  File size of the image.
	 * }
	 */
	public function save( $destfilename = null, $mime_type = null ) {
		return false; // @phpstan-ignore-line
	}

	/**
	 * Loads the image file.
	 *
	 * @return bool True if the file is an offloaded image, false otherwise.
	 */
	public function load() {
		if ( isset( $this->file ) && Optml_Media_Offload::is_uploaded_image( $this->file ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Resizes the image.
	 *
	 * For offloaded images, this operation is not performed locally and always returns true.
	 *
	 * @param int  $max_w Maximum width.
	 * @param int  $max_h Maximum height.
	 * @param bool $crop Optional. Whether to crop the image. Default false.
	 * @return bool Always returns true for offloaded images.
	 */
	public function resize( $max_w, $max_h, $crop = false ) {
		return true;
	}

	/**
	 * Resizes the image to multiple sizes.
	 *
	 * For offloaded images, this operation is not performed locally and always returns an empty array.
	 *
	 * @param array $sizes Array of size arrays. Each size array must include width, height, crop.
	 * @return array Empty array for offloaded images.
	 */
	public function multi_resize( $sizes ) {
		return [];
	}

	/**
	 * Crops the image.
	 *
	 * For offloaded images, this operation is not performed locally and always returns true.
	 *
	 * @param int  $src_x The start x position to crop from.
	 * @param int  $src_y The start y position to crop from.
	 * @param int  $src_w The width to crop.
	 * @param int  $src_h The height to crop.
	 * @param int  $dst_w Optional. The destination width.
	 * @param int  $dst_h Optional. The destination height.
	 * @param bool $src_abs Optional. If the source crop points are absolute.
	 * @return bool Always returns true for offloaded images.
	 */
	public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) {
		return true;
	}

	/**
	 * Rotates the image.
	 *
	 * For offloaded images, this operation is not performed locally and always returns true.
	 *
	 * @param float $angle The angle of rotation.
	 * @return bool Always returns true for offloaded images.
	 */
	public function rotate( $angle ) {
		return true;
	}

	/**
	 * Flips the image.
	 *
	 * For offloaded images, this operation is not performed locally and always returns true.
	 *
	 * @param bool $horz Whether to flip horizontally.
	 * @param bool $vert Whether to flip vertically.
	 * @return bool Always returns true for offloaded images.
	 */
	public function flip( $horz, $vert ) {
		return true;
	}

	/**
	 * Streams the image to the browser.
	 *
	 * For offloaded images, this redirects to the remote image URL.
	 *
	 * @param string $mime_type Optional. The MIME type of the image.
	 */
	public function stream( $mime_type = null ) {
		header( 'Location: ' . esc_url( $this->file ) );
		return true;
	}
}
