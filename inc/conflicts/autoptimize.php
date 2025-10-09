<?php

/**
 * Class Optml_Autoptimize
 *
 * Handles conflicts with Autoptimize plugin.
 */
class Optml_Autoptimize extends Optml_Abstract_Conflict {

	/**
	 * Optml_Autoptimize constructor.
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
			__( 'Autoptimize has <strong>Lazy loading</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with Autoptimize\'s. To continue using Optimole\'s lazy loading feature, please disable lazy loading in %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'admin.php?page=autoptimize_imgopt' ) . '">Autoptimize → Image Optimization</a>'
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
		if ( ! is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
			return false;
		}

		if ( ! Optml_Main::instance()->admin->settings->use_lazyload() ) {
			return false;
		}

		$autoptimize_imgopt_settings = get_option( 'autoptimize_imgopt_settings', '' );
		if ( ! empty( $autoptimize_imgopt_settings ) ) {
			$settings = maybe_unserialize( $autoptimize_imgopt_settings );
			if ( is_array( $settings ) && isset( $settings['autoptimize_imgopt_checkbox_field_3'] ) ) {
				return (bool) $settings['autoptimize_imgopt_checkbox_field_3'];
			}
		}

		return false;
	}
}
