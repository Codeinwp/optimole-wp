<?php

/**
 * Class Optml_Perfmatters
 *
 * Handles conflicts with Perfmatters plugin.
 */
class Optml_Perfmatters extends Optml_Abstract_Conflict {

	/**
	 * Optml_Perfmatters constructor.
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
			__( 'Perfmatters has <strong>Lazy loading</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with Perfmatters\'. To continue using Optimole\'s lazy loading feature, please disable lazy loading in %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'admin.php?page=perfmatters&tab=lazyload#lazyload' ) . '">Perfmatters → Lazy Load</a>'
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
		if ( ! is_plugin_active( 'perfmatters/perfmatters.php' ) ) {
			return false;
		}

		if ( ! Optml_Main::instance()->admin->settings->use_lazyload() ) {
			return false;
		}

		$perfmatters_options = get_option( 'perfmatters_options', [] );
		if ( ! empty( $perfmatters_options ) && isset( $perfmatters_options['lazyload'] ) ) {
			$lazyload_settings = $perfmatters_options['lazyload'];
			if ( is_array( $lazyload_settings ) && isset( $lazyload_settings['lazy_loading'] ) ) {
				return (bool) $lazyload_settings['lazy_loading'];
			}
		}

		return false;
	}
}
