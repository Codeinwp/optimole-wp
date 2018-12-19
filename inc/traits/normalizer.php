<?php

/**
 * Normalization traits.
 *
 * @package    \Optml\Inc\Traits
 * @author     Optimole <friends@optimole.com>
 */
trait Optml_Normalizer {

	/**
	 * Normalize value to boolean.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return bool
	 */
	public function to_boolean( $value ) {
		if ( in_array( $value, array( 'yes', 'enabled', 'true', '1' ) ) ) {
			return true;
		}

		if ( in_array( $value, array( 'no', 'disabled', 'false', '0' ) ) ) {
			return false;
		}

		return boolval( $value );
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
	 * Normalize value to positive integer.
	 *
	 * @param mixed $value Value to process.
	 *
	 * @return integer
	 */
	public function to_positive_integer( $value ) {
		$integer = intval( $value );
		return ( $integer > 0 ) ? $integer : 0;
	}

	/**
	 * Normalize value to map.
	 *
	 * @param mixed $value Value to process.
	 * @param array $map Associative list from witch to return.
	 * @param mixed $default Default.
	 *
	 * @return mixed
	 */
	public function to_map_values( $value, $map, $default ) {
		if ( in_array( $value, $map ) ) {
			return $value;
		}
		return $default;
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

		if ( $value === 'eco' || $value === 'auto' ) {
			return $value;
		}

		$accepted_qualities = array(
			'high_c' => 55,
			'medium_c' => 75,
			'low_c' => 90,
		);

		if ( array_key_exists( $value, $accepted_qualities ) ) {
			return $accepted_qualities[ $value ];
		}

		// Legacy values.
		return 60;
	}

	/**
	 * Normalize dimensions to bounds.
	 *
	 * @param mixed   $width Width.
	 * @param mixed   $height Height.
	 * @param integer $max_width Max width.
	 * @param integer $max_height Max height.
	 *
	 * @return array
	 */
	public function to_optml_dimensions_bound( $width, $height, $max_width, $max_height ) {
		global $content_width;

		if (
			doing_filter( 'the_content' )
			&& isset( $GLOBALS['content_width'] )
			&& apply_filters( 'optml_imgcdn_allow_resize_images_from_content_width', false )
		) {
			$content_width = (int) $GLOBALS['content_width'];

			if ( $max_width > $content_width ) {
				$max_width = $content_width;
			}
		}

		if ( $width > $max_width ) {
			// we need to remember how much in percentage the width was resized and apply the same treatment to the height.
			$percentWidth = ( 1 - $max_width / $width ) * 100;
			$width        = $max_width;
			$height       = round( $height * ( ( 100 - $percentWidth ) / 100 ), 0 );
		}

		// now for the height
		if ( $height > $max_height ) {
			$percentHeight = ( 1 - $max_height / $height ) * 100;
			// if we reduce the height to max_height by $x percentage than we'll also reduce the width for the same amount.
			$height = $max_height;
			$width  = round( $width * ( ( 100 - $percentHeight ) / 100 ), 0 );
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Normalize arguments for crop.
	 *
	 * @param array $crop_args Crop arguments.
	 *
	 * @return array
	 */
	public function to_optml_crop( $crop_args = array() ) {
		if ( $crop_args === false || ! is_array( $crop_args ) ) {
			return array();
		}

		if ( $crop_args === true || empty( $crop_args ) || count( $crop_args ) != 2 ) {
			return array(
				'resize'  => Optml_Image::RESIZE_FILL,
				'gravity' => Optml_Image::GRAVITY_CENTER,
			);
		}

		$allowed_gravities = array(
			'left' => Optml_Image::GRAVITY_WEST,
			'right' => Optml_Image::GRAVITY_EAST,
			'top' => Optml_Image::GRAVITY_NORTH,
			'bottom' => Optml_Image::GRAVITY_SOUTH,
			'left_top' => Optml_Image::GRAVITY_NORTH_WEST,
			'left_bottom' => Optml_Image::GRAVITY_SOUTH_WEST,
			'right_top' => Optml_Image::GRAVITY_NORTH_EAST,
			'right_bottom' => Optml_Image::GRAVITY_SOUTH_EAST,
			'center_top' => array( 0.5, 0 ),
			'center_bottom' => array( 0.5, 1 ),
			'left_center' => array( 0, 0.5 ),
			'right_center' => array( 1, 0.5 ),
		);

		$gravity = Optml_Image::GRAVITY_CENTER;
		$key_search = strval( $crop_args[0] ) . strval( $crop_args[1] );
		if ( array_key_exists( $key_search, $allowed_gravities ) ) {
			$gravity = $allowed_gravities[ $key_search ];
		}

		return array(
			'resize'  => Optml_Image::RESIZE_FILL,
			'gravity' => $gravity,
		);
	}
}
