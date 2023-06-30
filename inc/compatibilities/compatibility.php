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

	/**
	 * Will the compatibility be loaded?
	 *
	 * @return bool
	 */
	public final function will_load() {
		if ( ! Optml_Main::instance()->admin->settings->is_connected() ) {
			return false;
		}

		return $this->should_load();
	}

	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return false;
	}
}
