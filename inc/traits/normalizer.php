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
		if ( $value === 'eco' ) {
			return 'eco';
		}
		if ( $value === 'auto' ) {
			return 'auto';
		}
		if ( $value === 'high_c' ) {
			return 55;
		}
		if ( $value === 'medium_c' ) {
			return 75;
		}
		if ( $value === 'low_c' ) {
			return 90;
		}

		// Legacy values.
		return 60;
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