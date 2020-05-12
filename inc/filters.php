<?php

/**
 * Class Optml_Filters.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Filters {

	/**
	 * Generic method to check if the page is allowed to do the action.
	 *
	 * @param array $flags Flags array.
	 *
	 * @return bool Should do action on page?
	 */
	public static function should_do_page( $flags ) {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return true;
		}
		foreach ( $flags as $rule_flag => $status ) {
			if ( strpos( $_SERVER['REQUEST_URI'], $rule_flag ) !== false ) {
				return false;
			}
			if ( $rule_flag === 'home' && ( empty( $_SERVER['REQUEST_URI'] ) || $_SERVER['REQUEST_URI'] === '/' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if image qualifies for processing.
	 *
	 * @param string $image_url Image url.
	 * @param array  $flags Flags array.
	 *
	 * @return bool Should we process the image?
	 */
	public static function should_do_image( $image_url, $flags ) {

		foreach ( $flags as $rule_flag => $status ) {
			if ( strpos( $image_url, $rule_flag ) !== false ) {
				return false;
			}
		}

		return true;

	}

	/**
	 *
	 * Check if the image should be processed based on the extension.
	 *
	 * @param array  $flags Flags array.
	 * @param string $ext Extension string.
	 *
	 * @return bool Should we do the processing?
	 */
	public static function should_do_extension( $flags, $ext ) {
		foreach ( $flags as $rule_flag => $status ) {
			if ( $rule_flag === $ext ) {
				return false;
			}
		}

		return $ext;
	}
}
