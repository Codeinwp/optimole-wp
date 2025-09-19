<?php

/**
 * Class Optml_Jetpack_Lazyload
 *
 * An example of a conflict.
 */
class Optml_Jetpack_Lazyload extends Optml_Abstract_Conflict {

	/**
	 * Optml_Jetpack_Lazyload constructor.
	 */
	public function __construct() {
		$this->severity = self::SEVERITY_MEDIUM;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.0.6
	 * @access  public
	 */
	public function define_message() {
		$this->message = sprintf(
			/* translators: 1 is the settings path link */
			__( 'Jetpack has <strong>Lazy loading</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with Jetpack\'s. To continue using Optimole\'s lazy loading feature, please disable lazy loading in %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'admin.php?page=jetpack#/performance' ) . '">Jetpack → Performance</a>'
		);
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_conflict_valid() {

		if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return false;
		}
		if ( ! class_exists( 'Jetpack', false ) ) {
			return false;
		}
		if ( ! class_exists( 'Jetpack', false ) ) {
			return false;
		}

		if ( ! Optml_Main::instance()->admin->settings->use_lazyload() ) {
			return false;
		}

		return Jetpack::is_module_active( 'lazy-images' );
	}
}
