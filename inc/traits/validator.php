<?php
trait Optml_Validator {

	public function is_valid_numeric( $value ) {
		if ( isset( $value ) && ! empty( $value ) && is_numeric( $value ) ) {
			return true;
		}
		return false;
	}

	public function is_valid_mimetype_from_url( $url ) {
		$type  = wp_check_filetype( $url, Optml_Config::$extensions );

		if ( ! isset( $type['ext'] ) || empty( $type['ext'] ) ) {
			return false;
		}

		return true;
	}
}