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
		$this->message = sprintf( /* translators: 1 is the start of the bold tag, 2 is ending bold tag, 3 is new line tag, 4 is anchor tag start, 5 is ending anchor tag */__( 'It seems your are using %1$sWP Rocket%2$s with Lazy loading for images option active. %3$s Optimole already provides a lazy loading mechanism by it\'s own which might conflict with this. If you would like to further use Optimole lazy loading feature, you can turn that off from %4$sSettings -> WP Rocket -> Media%5$s page.', 'optimole-wp' ), '<b>', '</b>', '<br/>', '<a target="_blank" href="' . admin_url( 'options-general.php?page=wprocket#media' ) . '">', '</a>' );
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
