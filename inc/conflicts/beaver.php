<?php

/**
 * Class Optml_Beaver
 *
 * An example of a conflict.
 */
class Optml_Beaver extends Optml_abstract_conflict {

	/**
	 * Optml_Beaver constructor.
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
		$this->message = sprintf( __( 'It seems your are using %1$sBeaver Builder%2$s right now. %3$s In order for Optimole to replace the images in your Beaver Builder pages, you will need to go to %4$sSettings -> Beaver Builder -> Tools%5$s and click Clear Cache for the images to be processed. ', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'options-general.php?page=fl-builder-settings#tools' ) . '">', '</a>' );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_conflict_valid() {

		if ( ! is_plugin_active( 'bb-plugin/fl-builder.php' ) && ! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ) {
			return false;
		}

		return true;
	}
}
