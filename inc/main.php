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
	 * Holds the manager class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Manager Manager instance.
	 */
	public $manager;

	/**
	 * Holds the media_offload class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var  Optml_Media_Offload instance.
	 */
	public $media_offload;

	/**
	 * Holds the rest class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Rest REST instance.
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
	 * Holds the Dam class.
	 *
	 * @access  public
	 * @since   4.0
	 * @var Optml_Dam Dam instance.
	 */
	public $dam;

	/**
	 * Holds the cli class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Cli Cli instance.
	 */
	public $cli;

	/**
	 * Optml_Main constructor.
	 */
	public function __construct() {

		register_activation_hook( OPTML_BASEFILE, [ $this, 'activate' ] );
	}

	/**
	 * Main Starter_Plugin Instance
	 *
	 * Ensures only one instance of Starter_Plugin is loaded or can be loaded.
	 *
	 * @return Optml_Main Plugin instance.
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			add_filter( 'themeisle_sdk_products', [ __CLASS__, 'register_sdk' ] );
			add_filter( 'themeisle_sdk_ran_promos', '__return_true' );
			add_filter( 'optimole-wp_uninstall_feedback_icon', [ __CLASS__, 'change_icon' ] );
			add_filter( 'optimole_wp_uninstall_feedback_after_css', [ __CLASS__, 'adds_uf_css' ] );
			add_filter( 'optimole_wp_feedback_review_message', [ __CLASS__, 'change_review_message' ] );
			add_filter( 'optimole_wp_logger_heading', [ __CLASS__, 'change_review_message' ] );
			add_filter( 'optml_register_conflicts', [ __CLASS__, 'register_conflicts' ] );
			self::$_instance          = new self();
			self::$_instance->manager = Optml_Manager::instance();
			self::$_instance->rest    = new Optml_Rest();
			self::$_instance->admin   = new Optml_Admin();
			self::$_instance->dam     = new Optml_Dam();
			self::$_instance->media_offload = Optml_Media_Offload::instance();
			if ( class_exists( 'WP_CLI' ) ) {
				self::$_instance->cli = new Optml_Cli();
			}
		}
		$vendor_file = OPTML_PATH . 'vendor/autoload.php';
		if ( is_readable( $vendor_file ) ) {
			include_once $vendor_file;
		}

		return self::$_instance;
	}

	/**
	 * Register Conflicts to watch for
	 *
	 * @param array $conflicts_to_register A list of class names of conflicts to register.
	 *
	 * @return array
	 * @since   2.0.6
	 * @access  public
	 */
	public static function register_conflicts( $conflicts_to_register = [] ) {
		$conflicts_to_register = array_merge(
			$conflicts_to_register,
			[
				'Optml_Jetpack_Photon',
				'Optml_Jetpack_Lazyload',
				'Optml_Wprocket',
				'Optml_Divi',
				'Optml_w3_total_cache_cdn',
			]
		);

		return $conflicts_to_register;
	}

	/**
	 * Change review message.
	 *
	 * @param string $message Old review message.
	 *
	 * @return string New review message.
	 */
	public static function change_review_message( $message ) {
		return str_replace( '{product}', 'Optimole', $message );
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
	 * Adds aditional CSS for uninstall feedback popup.
	 */
	public static function adds_uf_css() {
		?>
		<style type="text/css">
			body.plugins-php .optimole_wp-container #TB_title {
				background-position: 30px 10px;
				background-size: 80px;
			}

			body.plugins-php .optimole_wp-container input.button:hover,
			body.plugins-php .optimole_wp-container input.button {
				background: #5080C1;
			}
		</style>
		<?php
	}

	/**
	 * Change icon for uninstall feedback.
	 *
	 * @return string Registered product.
	 */
	public static function change_icon( $old_icon ) {

		return OPTML_URL . 'assets/img/logo.png';
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
