<?php
trait Optml_Validator {

	/**
	 * A list of allowed extensions.
	 *
	 * @var array
	 */
	public static $extensions = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
		'svg'          => 'image/svg+xml',
	);

	public function is_valid_numeric( $value ) {
		if ( isset( $value ) && ! empty( $value ) && is_numeric( $value ) ) {
			return true;
		}
		return false;
	}

	public function is_valid_mimetype_from_url( $url ) {
		$type  = wp_check_filetype( $url, self::$extensions );

		if ( ! isset( $type['ext'] ) || empty( $type['ext'] ) ) {
			return false;
		}

		return true;
	}
}