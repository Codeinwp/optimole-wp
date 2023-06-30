<?php

/**
 * Class Optml_Minify
 */
class Optml_Minify extends Optml_Property_Type {

	/**
	 * Default minify value.
	 *
	 * @var int Minify value.
	 */
	public static $default_minify = 0;
	/**
	 * Minify Property.
	 *
	 * @var mixed $minify
	 */
	private $minify;

	/**
	 * Optml_Minify constructor.
	 *
	 * @param mixed $value Default value.
	 */
	public function __construct( $value = '' ) {
		if ( empty( $value ) ) {
			$value = self::$default_minify;
		}
		$this->set( $value );
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		$this->minify = $this->to_bound_integer( $value, 0, 1 );

	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		return $this->minify;
	}

	/**
	 * Return minify asset URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'm:%s', $this->minify );
	}
}
