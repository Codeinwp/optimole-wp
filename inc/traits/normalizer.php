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
}