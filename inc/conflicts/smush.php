<?php

/**
 * Class Optml_Smush_Conflict
 *
 * Handles conflicts with WP Smush plugin.
 */
class Optml_Smush extends Optml_Abstract_Conflict {

	/**
	 * Optml_Smush_Conflict constructor.
	 */
	public function __construct() {
		$this->severity = self::SEVERITY_MEDIUM;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   4.1.0
	 * @access  public
	 * @return  void
	 */
	public function define_message() {
		$this->message = sprintf(
			/* translators: 1 is the settings path link */
			__( 'WP Smush has <strong>Lazy loading</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with Smush\'s. To continue using Optimole\'s lazy loading feature, please disable lazy loading in %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'admin.php?page=smush-lazy-preload' ) . '">Smush → Lazy Load</a>'
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
		if ( ! is_plugin_active( 'wp-smushit/wp-smush.php' ) ) {
			return false;
		}

		if ( ! Optml_Main::instance()->admin->settings->use_lazyload() ) {
			return false;
		}

		if ( class_exists( '\Smush\Core\Settings' ) ) {
			$smush_settings = \Smush\Core\Settings::get_instance();
			if ( method_exists( $smush_settings, 'get' ) ) {
				return (bool) $smush_settings->get( 'lazy_load' );
			}
		}

		return false;
	}
}
