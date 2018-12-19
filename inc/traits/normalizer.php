<?php
trait Optml_Normalizer {
	public function to_boolean( $value ) {
		if ( in_array( $value, array( 'yes', 'enabled', 'true', '1' ) ) ) {
			return true;
		}

		if ( in_array( $value, array( 'no', 'disabled', 'false', '0' ) ) ) {
			return false;
		}

		return boolval( $value );
	}

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

	public function to_positive_integer( $value ) {
		$integer = intval( $value );
		return ( $integer > 0 ) ? $integer : 0;
	}

	public function  to_map_values( $value, $map, $default ) {
		if ( in_array( $value, $map ) ) {
			return $value;
		}
		return $default;
	}

	public function to_accepted_quality( $value ) {
		if ( is_numeric( $value ) ) {
			return intval( $value );
		}
		$value = trim( $value );

		if ( $value === 'eco' ||  $value === 'auto' ) {
			return $value;
		}

		$accepted_qualities = array(
			'high_c' => 55,
			'medium_c' => 75,
			'low_c' => 90,
		);

		if ( array_key_exists( $value, $accepted_qualities ) ) {
			return $accepted_qualities[$value];
		}

		// Legacy values.
		return 60;
	}

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

	public function to_optml_crop( $crop_args = array() ) {
		if ( $crop_args === false ||  ! is_array( $crop_args ) ) {
			return array();
		}

		if ( $crop_args === true ||  empty( $crop_args ) || count( $crop_args ) != 2 ) {
			return array(
				'resize'  => Optml_Image::RESIZE_FILL,
				'gravity' => Optml_Image::GRAVITY_CENTER,
			);
		}

		switch ( strval( $crop_args[0] ) . strval( $crop_args[1] ) ) {
			case 'left':
				$gravity = Optml_Image::GRAVITY_WEST;
				break;
			case 'right':
				$gravity = Optml_Image::GRAVITY_EAST;
				break;
			case 'top':
				$gravity = Optml_Image::GRAVITY_NORTH;
				break;
			case 'bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH;
				break;
			case 'center':
				$gravity = Optml_Image::GRAVITY_CENTER;
				break;

			case 'left_top':
				$gravity = Optml_Image::GRAVITY_NORTH_WEST;
				break;
			case 'left_bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH_WEST;
				break;

			case 'right_top':
				$gravity = Optml_Image::GRAVITY_NORTH_EAST;
				break;
			case 'right_bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH_EAST;
				break;
			case 'center_top':
				$gravity = array( 0.5, 0 );
				break;
			case 'center_bottom':
				$gravity = array( 0.5, 1 );
				break;
			case 'left_center':
				$gravity = array( 0, 0.5 );
				break;
			case 'right_center':
				$gravity = array( 1, 0.5 );
				break;
			case 'center_center':
			default:
				$gravity = Optml_Image::GRAVITY_CENTER;
				break;
		}

		return array(
			'resize'  => Optml_Image::RESIZE_FILL,
			'gravity' => $gravity,
		);
	}
}