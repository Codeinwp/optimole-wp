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
}