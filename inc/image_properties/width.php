<?php

/**
 * Class Optml_Width
 */
class Optml_Width extends Optml_Property_Type {

	/**
	 * Width Property.
	 *
	 * @var mixed $width
	 */
	private $width;

	/**
	 * Optml_Width constructor.
	 *
	 * @param mixed $value Default value.
	 */
	public function __construct( $value ) {
		$this->width = $value;
	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->width;
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
