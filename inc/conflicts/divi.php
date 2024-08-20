<?php

/**
 * Class Optml_Divi
 *
 * An example of a conflict.
 */
class Optml_Divi extends Optml_Abstract_Conflict {

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
		$this->message = sprintf( /* translators: 1 is the start of the bold tag, 2 is ending bold tag, 3 is new line tag, 4 is anchor tag start, 5 is ending anchor tag */ __( 'It seems your are using %1$sDivi%2$s right now. %3$s In order for Optimole to replace the images in your Divi pages, you will need to go to %4$sDivi -> Theme Options -> Builder -> Advanced -> Static CSS File Generations%5$s and click on Clear for the images to be processed. ', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'admin.php?page=et_divi_options' ) . '">', '</a>' );
	}


	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.2.6
	 * @access  public
	 */
	public function is_conflict_valid() {
		$show_message = true;
		if ( is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/divi-builder/divi-builder.php' );
			if ( version_compare( $plugin_data['Version'], '4.10.8', '<' ) ) {
				$show_message = true;
			}
		}

		$theme = wp_get_theme();
		// Divi, no child theme.
		if ( strcmp( $theme->get( 'Name' ), 'Divi' ) === 0 && $theme->parent() === false && version_compare( $theme->get( 'Version' ), '4.10.8', '<' ) ) {
			$show_message = true;
		}
		// Child theme, parent divi.
		if ( $theme->parent() !== false && strcmp( $theme->parent()->get( 'Name' ), 'Divi' ) === 0 && version_compare( $theme->parent()->get( 'Version' ), '4.10.8', '<' ) ) {
			$show_message = true;
		}
		if ( ! function_exists( 'et_get_option' ) ) {
			return false;
		}
		if ( 'off' === et_get_option( 'et_pb_static_css_file', 'off' ) ) {
			return false;
		}

		return $show_message;
	}
}
