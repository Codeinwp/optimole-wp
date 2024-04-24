<?php

/**
 * Class Optml_Jetpack_Photon
 *
 * An example of a conflict.
 */
class Optml_Jetpack_Photon extends Optml_Abstract_Conflict {

	/**
	 * Optml_Jetpack_Photon constructor.
	 */
	public function __construct() {
		$this->priority = 2;
		$this->severity = self::SEVERITY_HIGH;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.0.6
	 * @access  public
	 */
	public function define_message() {
		$this->message = sprintf( /* translators: 1 is the start of the bold tag, 2 is ending bold tag, 3 is new line tag, 4 is anchor tag start, 5 is ending anchor tag, 6 is start of bold tag, 7 is ending bold tag */ __( 'It seems your are using %1$sJetpack%2$s with site accelerator option enabled for images. %3$s To avoid any possible conflicts with Optimole replacement mechanism, you can go to %4$sJetpack -> Perfomance%5$s and turn off the site accelerator option for %6$simages%7$s', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'admin.php?page=jetpack#/performance' ) . '">', '</a>', '<b>', '</b>' );
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

		return Jetpack::is_module_active( 'photon' );
	}
}
