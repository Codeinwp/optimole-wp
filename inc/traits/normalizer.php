<?php

use Optimole\Sdk\Resource\ImageProperty\ResizeTypeProperty;
use Optimole\Sdk\ValueObject\Position;

/**
 * Normalization traits.
 *
 * @package    \Optml\Inc\Traits
 * @author     Optimole <friends@optimole.com>
 */
trait Optml_Normalizer {

	/**
	 * Static cache for dimension calculations
	 *
	 * @var array
	 */
	private static $dimension_cache = [];

	/**
	 * Normalize value to boolean.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return bool
	 */
	public function to_boolean( $value ) {
		if ( in_array( $value, [ 'yes', 'enabled', 'true', '1' ], true ) ) {
			return true;
		}

		if ( in_array( $value, [ 'no', 'disabled', 'false', '0' ], true ) ) {
			return false;
		}

		return boolval( $value );
	}

	/**
	 * Get the unoptimized url from an Optimole url.
	 * Works only on non-offloaded images.
	 *
	 * @param string $url The url to get the unoptimized url for.
	 *
	 * @return string The unoptimized url.
	 */
	public function get_unoptimized_url( $url ) {

		// If the url is not an optimole url, return the url
		if ( strpos( $url, Optml_Config::$service_url ) === false ) {
			return $url;
		}
		// If the url is an uploaded image, return the url
		if ( Optml_Media_Offload::is_uploaded_image( $url ) ) {
			$pattern = '#/id:([^/]+)/((?:https?://|http://|directUpload/)\S+)#';
			if ( preg_match( $pattern, $url, $matches ) ) {
				$url = $matches[0];
			}
			return $url;
		}

		$url_parts = explode( 'http', $url );
		if ( ! isset( $url_parts[2] ) ) {
			return $url;
		}
		return 'http' . $url_parts[2];
	}
	/**
	 * Return domain hash.
	 *
	 * @param string $domain Full url.
	 *
	 * @return string Domain hash.
	 */
	public function to_domain_hash( $domain ) {
		$domain_parts = parse_url( $domain );
		$domain       = isset( $domain_parts['host'] ) ? $domain_parts['host'] : '';
		$prefix       = substr( $domain, 0, 4 );
		if ( $prefix === 'www.' ) {
			$domain = substr( $domain, 4 );
		}

		return base64_encode( $domain );
	}
	/**
	 * Strip slashes on unicode encoded strings.
	 *
	 * @param string $content Input string.
	 *
	 * @return string Decoded string.
	 */
	public function strip_slashes( $content ) {
		return html_entity_decode( stripslashes( preg_replace( '/\\\u([\da-fA-F]{4})/', '&#x\1;', $content ) ) );
	}

	/**
	 * Normalize value to positive integer.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return integer
	 */
	public function to_positive_integer( $value ) {
		$integer = (int) $value;

		return ( $integer > 0 ) ? $integer : 0;
	}

	/**
	 * Normalize value to map.
	 *
	 * @param mixed $value Value to process.
	 * @param array $map Associative list from witch to return.
	 * @param mixed $initial Default.
	 *
	 * @return mixed
	 */
	public function to_map_values( $value, $map, $initial ) {
		if ( in_array( $value, $map, true ) ) {
			return $value;
		}

		return $initial;
	}

	/**
	 * Normalize value to an accepted quality.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return mixed
	 */
	public function to_accepted_quality( $value ) {
		if ( is_numeric( $value ) ) {
			return intval( $value );
		}
		$value = trim( $value );

		$accepted_qualities = [
			'eco'      => 'eco',
			'auto'     => 'auto',
			'mauto'    => 'mauto',
			'high_c'   => 55,
			'medium_c' => 75,
			'low_c'    => 90,
		];

		if ( array_key_exists( $value, $accepted_qualities ) ) {
			return $accepted_qualities[ $value ];
		}

		// Legacy values.
		return 60;
	}

	/**
	 * Normalize value to an accepted minify.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return mixed
	 */
	public function to_accepted_minify( $value ) {
		if ( is_numeric( $value ) ) {
			return $this->to_bound_integer( $value, 0, 1 );
		}

		return 'auto';
	}

	/**
	 * Normalize value to an integer within bounds.
	 *
	 * @param mixed   $value Value to process.
	 * @param integer $min Lower bound.
	 * @param integer $max Upper bound.
	 *
	 * @return integer
	 */
	public function to_bound_integer( $value, $min, $max ) {
		$integer = absint( $value );
		if ( $integer < $min ) {
			$integer = $min;
		}
		if ( $integer > $max ) {
			$integer = $max;
		}

		return $integer;
	}

	/**
	 * Convert image size to dimensions.
	 *
	 * This function takes an image size and its metadata, and returns the dimensions
	 * of the image based on the specified size. It handles different cases such as
	 * custom sizes, predefined sizes, and full size.
	 *
	 * @param mixed $size The size of the image. Can be an array of width and height, a predefined size, or 'full'.
	 * @param array $image_meta Metadata of the image, including width and height.
	 * @param int   $attachment_id The ID of the attachment.
	 *
	 * @return array The dimensions of the image, including width, height, and optional resize parameters.
	 */
	public function size_to_dimension( $size, $image_meta, $attachment_id = null ) {
		// default size
		$sizes = [
			'width'  => isset( $image_meta['width'] ) ? intval( $image_meta['width'] ) : false,
			'height' => isset( $image_meta['height'] ) ? intval( $image_meta['height'] ) : false,
		];
		$image_args = Optml_App_Replacer::image_sizes();
		$sizes2crop = Optml_App_Replacer::size_to_crop();
		switch ( $size ) {
			case is_array( $size ):
				$width  = isset( $size[0] ) ? (int) $size[0] : false;
				$height = isset( $size[1] ) ? (int) $size[1] : false;
				if ( ! $width || ! $height ) {
					break;
				}
				$cache_key = 'a' . $width . '_' . $height . '_' . $sizes['width'] . '_' . $sizes['height'];
				if ( isset( self::$dimension_cache[ $cache_key ] ) ) {
					return self::$dimension_cache[ $cache_key ];
				}
				if ( $attachment_id ) {
					$intermediate = image_get_intermediate_size( $attachment_id, $size );
					if ( $intermediate ) {
						$sizes['width'] = $intermediate['width'];
						$sizes['height'] = $intermediate['height'];
					}
				}

				list( $sizes['width'], $sizes['height'] ) = image_constrain_size_for_editor( $sizes['width'], $sizes['height'], $size );
				$resize = apply_filters( 'optml_default_crop', [] );
				if ( isset( $sizes2crop[ $sizes['width'] . $sizes['height'] ] ) ) {
					$resize = $this->to_optml_crop( $sizes2crop[ $sizes['width'] . $sizes['height'] ] );
				}
				$sizes['resize'] = $resize;
				self::$dimension_cache[ $cache_key ] = $sizes;
				break;
			case 'full' !== $size && isset( $image_args[ $size ] ):
				$cache_key = 'b' . $size . '_' . $sizes['width'] . '_' . $sizes['height'];
				if ( isset( self::$dimension_cache[ $cache_key ] ) ) {
					return self::$dimension_cache[ $cache_key ];
				}
				$image_resized = image_resize_dimensions( $sizes['width'], $sizes['height'], $image_args[ $size ]['width'], $image_args[ $size ]['height'], $image_args[ $size ]['crop'] );

				if ( $image_resized ) { // This could be false when the requested image size is larger than the full-size image.
					$sizes['width']  = $image_resized[6];
					$sizes['height'] = $image_resized[7];
				}
				// There are cases when the image meta is missing and image size is non existent, see SVG image handling.
				if ( ! $sizes['width'] || ! $sizes['height'] ) {
					break;
				}
				list( $sizes['width'], $sizes['height'] ) = image_constrain_size_for_editor( $sizes['width'], $sizes['height'], $size, 'display' );

				$sizes['resize'] = $this->to_optml_crop( $image_args[ $size ]['crop'] );
				self::$dimension_cache[ $cache_key ] = $sizes;
				break;
		}
		return $sizes;
	}
	/**
	 * Normalize arguments for crop.
	 *
	 * @param array|bool $crop_args Crop arguments.
	 *
	 * @return array
	 */
	public function to_optml_crop( $crop_args = [] ) {

		$enlarge = false;
		if ( isset( $crop_args['enlarge'] ) ) {
			$enlarge   = $crop_args['enlarge'];
			$crop_args = $crop_args['crop'];
		}
		if ( $crop_args === true ) {
			return [
				'type'    => ResizeTypeProperty::FILL,
				'enlarge' => $enlarge,
				'gravity' => Position::CENTER,
			];
		}
		if ( $crop_args === false || ! is_array( $crop_args ) || count( $crop_args ) !== 2 ) {
			return [];
		}

		$allowed_x         = [
			'left'   => true,
			'center' => true,
			'right'  => true,
		];
		$allowed_y         = [
			'top'    => true,
			'center' => true,
			'bottom' => true,
		];
		$allowed_gravities = [
			'left'         => Position::WEST,
			'right'        => Position::EAST,
			'top'          => Position::NORTH,
			'bottom'       => Position::SOUTH,
			'lefttop'      => Position::NORTH_WEST,
			'leftbottom'   => Position::SOUTH_WEST,
			'righttop'     => Position::NORTH_EAST,
			'rightbottom'  => Position::SOUTH_EAST,
			'centertop'    => [ 0.5, 0 ],
			'centerbottom' => [ 0.5, 1 ],
			'leftcenter'   => [ 0, 0.5 ],
			'rightcenter'  => [ 1, 0.5 ],
		];

		$gravity    = Position::CENTER;
		$key_search = ( $crop_args[0] === true ? '' :
				( isset( $allowed_x[ $crop_args[0] ] ) ? $crop_args[0] : '' ) ) .
						( $crop_args[1] === true ? '' :
							( isset( $allowed_y[ $crop_args[1] ] ) ? $crop_args[1] : '' ) );

		if ( array_key_exists( $key_search, $allowed_gravities ) ) {
			$gravity = $allowed_gravities[ $key_search ];
		}

		return [
			'type'    => ResizeTypeProperty::FILL,
			'enlarge' => $enlarge,
			'gravity' => $gravity,
		];
	}

	/**
	 * Normalize arguments for watermark.
	 *
	 * @param array $watermark_args Watermark arguments.
	 *
	 * @return array
	 */
	public function to_optml_watermark( $watermark_args = [] ) {
		$allowed_gravities = [
			'left'         => Position::WEST,
			'right'        => Position::EAST,
			'top'          => Position::NORTH,
			'bottom'       => Position::SOUTH,
			'left_top'     => Position::NORTH_WEST,
			'left_bottom'  => Position::SOUTH_WEST,
			'right_top'    => Position::NORTH_EAST,
			'right_bottom' => Position::SOUTH_EAST,
		];
		$gravity           = Position::CENTER;
		if ( isset( $watermark_args['position'] ) && array_key_exists( $watermark_args['position'], $allowed_gravities ) ) {
			$gravity = $allowed_gravities[ $watermark_args['position'] ];
		}

		return [
			'opacity'  => 1,
			'position' => $gravity,
		];
	}
	/**
	 * If missing, add schema to urls.
	 *
	 * @param string $url Url to check.
	 *
	 * @return string
	 */
	public function add_schema( $url ) {
		$schema_url = $url;
		$should_add_schema = false;
		if ( function_exists( 'str_starts_with' ) ) {
			$should_add_schema = str_starts_with( $schema_url, '//' );
		} else {
			$should_add_schema = substr( $schema_url, 0, strlen( '//' ) ) === '//';
		}
		if ( $should_add_schema ) {
			$schema_url = is_ssl() ? 'https:' : 'http:' . $schema_url;
		}
		return $schema_url;
	}
}
