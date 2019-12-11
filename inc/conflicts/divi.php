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
		$show_message = false;
		$theme = wp_get_theme( 'Divi' );
		if ( $theme->exists() ) {
			if ( strcmp( $theme, 'Divi' ) === 0 ) {
				$show_message = true;
			}
		}
		if ( is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
			$show_message = true;
		}
		if ( get_template_directory() !== get_stylesheet_directory() ) {
			if ( strcmp( wp_get_theme( get_template() )->get( 'Author' ), 'Divi' ) === 0 ) {
				$show_message = true;
			}
		}
		if ( ! function_exists( 'et_get_option' ) ) {
			return false;
		}
		if ( 'off' === et_get_option( 'et_pb_static_css_file', 'on' ) ) {
			return false;
		}

		return $show_message;
	}
}
