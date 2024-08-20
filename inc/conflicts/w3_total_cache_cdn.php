<?php

/**
 * Class Optml_w3_total_cache_cdn
 *
 * External css/js needs to be added to a list in order to be processed by w3 total cache.
 */
class Optml_w3_total_cache_cdn extends Optml_Abstract_Conflict {

	/**
	 * Optml_Jetpack_Lazyload constructor.
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

		/* translators: 1 is new line tag, 2 is the starting anchor tag, 3 is the ending anchor tag and 4 is the domain name of Optimole */
		$this->message = sprintf( __( 'It seems your are using W3 Total Cache. %1$s If you are using the CSS or JavaScript minify/combine option from  %2$sW3 Total Cache -> Performance -> Minify page %3$s %1$s add this line: %4$s to Include external files/libraries and check Use regular expressions for file matching checkbox.', 'optimole-wp' ), '<br/>', '<a target="_blank" href="' . admin_url( 'admin.php?page=w3tc_minify' ) . '">', '</a>', 'https://' . Optml_Main::instance()->admin->settings->get_cdn_url() . '/*' );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_conflict_valid() {
		return Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'w3-total-cache/w3-total-cache.php' );
	}
}
