<?php

/**
 * Class Optml_Property_Type
 * Base Property definition/
 */
abstract class Optml_Property_Type {
	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	abstract public function get();

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	abstract public function set( $value );

	/**
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return mixed
	 */
	abstract public function toString();
}
