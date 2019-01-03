<?php

/**
 * Class Optml_Watermark
 */
class Optml_Watermark extends Optml_Property_Type {

	/**
	 * Watermark Property.
	 *
	 * @var mixed $watermark
	 */
	private $watermark;

	/**
	 * Optml_Watermark constructor.
	 */
	public function __construct() {
		$settings = new Optml_Settings();
		$this->watermark = $settings->get_site_settings()['watermark'];
	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->watermark;
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		if ( $this->is_valid_numeric( $value ) ) {
			$this->width = $this->to_positive_integer( $value );
		}
	}

	/**
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'w:%s', $this->width );
	}
}
