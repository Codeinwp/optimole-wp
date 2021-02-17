<?php

/**
 * Class Optml_Quality
 */
class Optml_Quality extends Optml_Property_Type {

	/**
	 * Default quality value.
	 *
	 * @var string Quality value.
	 */
	public static $default_quality = 'auto';
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
	public function __construct( $value = '' ) {
		if ( empty( $value ) ) {
			$value = self::$default_quality;
		}
		$this->set( $value );
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value, $is_avif = false ) {

		if ( $value === 'auto' ) {
			$this->quality = 'auto';
			if ( $is_avif === true ) {
				$this->quality = 50;
			}

			return;
		}
		if ( $value === 'eco' ) {
			$this->quality = 'eco';

			return;
		}

		if ( ! $this->is_valid_numeric( $value ) ) {
			$this->quality = 'auto';
			if ( $is_avif === true ) {
				$this->quality = 50;
			}
			return;
		}

		$this->quality = $this->to_bound_integer( $value, 0, 100 );
		if ( $is_avif === true ) {
			$this->quality = max( 0, $this->quality - 30 );
		}

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
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		return sprintf( 'q:%s', $this->quality );
	}
}
