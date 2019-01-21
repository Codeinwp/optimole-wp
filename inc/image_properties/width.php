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
	private $width = 'auto';

	/**
	 * Optml_Width constructor.
	 *
	 * @param mixed $value Default value.
	 */
	public function __construct( $value ) {
		$this->set( $value );
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {

		if ( $value === 'auto' ) {
			$this->width = 'auto';

			return;
		}
		if ( ! $this->is_valid_numeric( $value ) ) {
			$this->width = 'auto';

			return;
		}

		$this->width = $this->to_positive_integer( $value );
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
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'w:%s', $this->width );
	}
}
