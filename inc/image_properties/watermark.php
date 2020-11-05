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
	 *
	 * @param array $value Watermark details.
	 */
	public function __construct( $value = [] ) {
		$this->set( $value );
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		$this->watermark = $value;
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
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	public function toString() {
		$base = sprintf(
			'wm:%s',
			$this->watermark['id'] . ':' .
			$this->watermark['opacity'] . ':' .
			$this->watermark['position']
		);

		if ( ! empty( $this->watermark['scale'] ) ) {
			return $base . ':' .
				   $this->watermark['x_offset'] . ':' .
				   $this->watermark['y_offset'] . ':' .
				   $this->watermark['scale'];
		}
		if ( ! empty( $this->watermark['y_offset'] ) ) {
			return $base . ':' .
				   $this->watermark['x_offset'] . ':' .
				   $this->watermark['y_offset'];
		}
		if ( ! empty( $this->watermark['x_offset'] ) ) {
			return $base . ':' .
				   $this->watermark['x_offset'];
		}

		return $base;
	}
}
