<?php

/**
 * Class Optml_Divi
 *
 * An example of a conflict.
 */
class Optml_Divi extends Optml_abstract_conflict {

	/**
	 * Optml_Divi constructor.
	 */
	public function __construct() {
		$this->priority = 2;
		$this->severity = self::SEVERITY_HIGH;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.2.6
	 * @access  public
	 */
	public function define_message() {
		$this->message = sprintf( __( 'It seems your are using %1$sDivi%2$s right now. %3$s In order for Optimole to replace the images in your Divi pages, you will need to go to %4$sDivi -> Theme Options -> Builder -> Advanced -> Static CSS File Generations%5$s and click on Clear for the images to be processed. ', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'admin.php?page=et_divi_options' ) . '">', '</a>' );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.2.6
	 * @access  public
	 */
	public function is_conflict_valid() {

		if ( ! is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
			return false;
		}

		return true;
	}
}