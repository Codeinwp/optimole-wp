<?php

/**
 * Class Optml_Height
 */
class Optml_Height extends Optml_Property_Type {

	/**
	 * Height Property.
	 *
	 * @var mixed $height
	 */
	private $height;

	/**
	 * Optml_Height constructor.
	 *
	 * @param mixed $value Default value.
	 */
	public function __construct( $value ) {
		$this->height = $value;
	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->height;
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		if ( $this->is_valid_numeric( $value ) ) {
			$this->height = $this->to_positive_integer( $value );
		}
	}

	/**
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'h:%s', $this->height );
	}
}
