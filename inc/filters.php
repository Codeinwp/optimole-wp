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
	 * @param array $contains_flags Contains flags array.
	 * @param array $match_flags Exact path match flags array.
	 * @return bool Should do action on page?
	 */
	public static function should_do_page( $contains_flags, $match_flags ) {

		if ( empty( $contains_flags ) && empty( $match_flags ) ) {
			return true;
		}

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return true;
		}

		$check_against = [ $_SERVER['REQUEST_URI'] ];
		// This code is designed to handle ajax requests on pages that are excluded.
		// For ajax requests, the referer is set to the page URL and they use a POST method.
		// If an ajax request uses a GET method, it can be managed using the available exclusion rules.
		if ( isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$check_against[] = $_SERVER['HTTP_REFERER'];
		}
		foreach ( $check_against as $check ) {
			foreach ( $match_flags as $rule_flag => $status ) {
				if ( $rule_flag === $check ) {
					return false;
				}
			}
			foreach ( $contains_flags as $rule_flag => $status ) {
				if ( strpos( $check, $rule_flag ) !== false ) {
					return false;
				}
				if ( $rule_flag === 'home' && ( empty( $check ) || $check === '/' ) ) {
					return false;
				}
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
	 * @return bool|string Should we do the processing?
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
