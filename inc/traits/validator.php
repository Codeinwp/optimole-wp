<?php
trait Optml_Validator {
	public function is_valid_numeric( $value ) {
		if ( isset( $value ) && ! empty( $value ) && is_numeric( $value ) ) {
			return true;
		}
		return false;
	}
}