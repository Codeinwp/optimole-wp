<?php

/**
 * Main Plugin Class
 */
final class Optml_Main {
	/**
	 * Optml_Main The single instance of Starter_Plugin.
	 *
	 * @var    object
	 * @access   private
	 * @since    1.0.0
	 */
	private static $_instance = null;

	/**
	 * Holds the replacer class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Replacer Replacer instance.
	 */
	public $replacer;
	/**
	 * Holds the replacer class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Rest Replacer instance.
	 */
	public $rest;
	/**
	 * Holds the admin class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Admin Admin instance.
	 */
	public $admin;

	/**
	 * Optml_Main constructor.
	 */
	public function __construct() {

		register_activation_hook( OPTML_BASEFILE, array( $this, 'activate' ) );
	}

	/**
	 * Main Starter_Plugin Instance
	 *
	 * Ensures only one instance of Starter_Plugin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return Optml_Main Plugin instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance           = new self();
			self::$_instance->replacer = Optml_Replacer::instance();
			self::$_instance->rest     = new Optml_Rest();
			self::$_instance->admin    = new Optml_Admin();
		}

		add_filter( 'themeisle_sdk_products', array( __CLASS__, 'register_sdk' ) );
		add_filter( 'optimole-wp_uninstall_feedback_icon', array( __CLASS__, 'change_icon' ) );

		return self::$_instance;
	}

	/**
	 * Register product into SDK.
	 *
	 * @param array $products All products.
	 *
	 * @return array Registered product.
	 */
	public static function register_sdk( $products ) {
		$products[] = OPTML_BASEFILE;

		return $products;
	}

	/**
	 * Change icon for uninstall feedback.
	 *
	 *
	 * @return string Registered product.
	 */
	public static function change_icon( $old_icon ) {

		return OPTML_URL . 'assets/logo.png';
	}

	/**
	 * Load the localisation file.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'optimole-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Install routine actions.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function activate() {

		update_option( OPTML_NAMESPACE . '-version', OPTML_VERSION );

		if ( is_multisite() ) {
			return;
		}

		set_transient( 'optml_fresh_install', true, MINUTE_IN_SECONDS );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

}
