<?php

/**
 * Class Optml_Quality
 */
class Optml_Quality extends Optml_Property_Type {

	/**
	 * Quality Property.
	 *
	 * @var mixed $quality
	 */
	private $quality;

	/**
	 * Optml_Quality constructor.
	 *
	 * @param mixed $value Default value.
	 */
	public function __construct( $value ) {
		$this->quality = $value;
	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->quality;
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		if ( $this->is_valid_numeric( $value ) ) {
			$this->quality = $this->to_bound_integer( $value, 0, 100 );
		}
		if ( $value === 'eco' ) {
			$this->quality = 'eco';
		}
	}

	/**
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'q:%s', $this->quality );
	}
}
