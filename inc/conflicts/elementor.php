<?php

/**
 * Class Optml_Elementor
 *
 * An example of a conflict.
 */
class Optml_Elementor extends Optml_abstract_conflict {

	/**
	 * Optml_Elementor constructor.
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
		$this->message = sprintf( __( 'It seems your are using %1$sElementor%2$s right now. %3$s In order for Optimole to replace the images in your Elementor pages, you will need to go to %4$sElementor -> Tools -> General%5$s and click Regenerate files for the images to be processed. ', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'admin.php?page=elementor-tools' ) . '">', '</a>' );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_conflict_valid() {

		if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
			return false;
		}

		$print_method = get_option( 'elementor_css_print_method', 'external' );

		return $print_method === 'external';
	}
}
