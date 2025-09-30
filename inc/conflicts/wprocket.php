<?php

/**
 * Class Optml_Wprocket
 *
 * An example of a conflict.
 */
class Optml_Wprocket extends Optml_Abstract_Conflict {

	/**
	 * Optml_Wprocket constructor.
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
			/* translators: 1 is plugin name, 2 is the settings path link */
			__( 'WP Rocket has <strong>Lazy loading for images</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with WP Rocket\'s. To continue using Optimole\'s lazy loading feature, please disable lazy loading in WP Rocket via %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'options-general.php?page=wprocket#media' ) . '">Settings → WP Rocket → Media</a>'
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

		if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			return false;
		}
		if ( ! function_exists( 'get_rocket_option' ) ) {
			return false;
		}

		return get_rocket_option( 'lazyload', false );
	}
}
