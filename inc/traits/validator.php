<?php

/**
 * Validation traits.
 *
 * @package    \Optml\Inc\Traits
 * @author     Optimole <friends@optimole.com>
 */
trait Optml_Validator {

	/**
	 * Check if the value is a valid numeric.
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool
	 */
	public function is_valid_numeric( $value ) {
		if ( isset( $value ) && ! empty( $value ) && is_numeric( $value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the URl is a GIF url.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool Is Gif?
	 */
	public function is_valid_gif( $url ) {
		$type = wp_check_filetype( $url, [ 'gif' => 'image/gif' ] );

		if ( empty( $type['ext'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the url has an accepted mime type extension.
	 *
	 * @param mixed $url The url to check.
	 *
	 * @return bool|string
	 */
	public function is_valid_mimetype_from_url( $url, $filters = [] ) {
		$type = wp_check_filetype( $url, Optml_Config::$all_extensions );

		if ( empty( $type['ext'] ) ) {
			return false;
		}

		return Optml_Filters::should_do_extension( $filters, $type['ext'] );
	}


}
