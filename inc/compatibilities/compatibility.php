<?php
/**
 * Class Optml_compatibility.
 */
abstract class Optml_compatibility {
	/**
	 * Register compatibility actions/filters.
	 */
	abstract function register();

	/**
	 * Should we load the compatibility?
	 *
	 * @return bool Compatiblity
	 */
	abstract function should_load();
}
