<?php

/**
 * Class Optml_Quality
 */
class Optml_Quality extends Optml_Property_Type {
	const ECO = 'eco';
	const MAUTO = 'mauto';
	const AUTO = 'auto';
	/**
	 * Default quality value.
	 *
	 * @var string Quality value.
	 */
	public static $default_quality = 'mauto';
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
	public function set( $value ) {
		static $quality_enums = [
			self::ECO   => true,
			self::MAUTO => true,
			self::AUTO  => true,
		];
		if ( isset( $quality_enums[ $value ] ) ) {
			$this->quality = $value;

			return;
		}

		if ( ! $this->is_valid_numeric( $value ) ) {
			$this->quality = 'mauto';

			return;
		}

		$this->quality = $this->to_bound_integer( $value, 0, 100 );

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
