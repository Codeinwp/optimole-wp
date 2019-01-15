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
	private $height = 'auto';

	/**
	 * Optml_Height constructor.
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
			$this->height = 'auto';
			return;
		}

		if ( ! $this->is_valid_numeric( $value ) ) {
			$this->height = 'auto';
			return;
		}

		$this->height = $this->to_positive_integer( $value );
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
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'h:%s', $this->height );
	}
}
