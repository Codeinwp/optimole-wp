<?php
/**
 * Plugin Name:       Image optimization service by Optimole
 * Description:       Complete handling of your website images.
 * Version:           3.5.4
 * Author:            Optimole
 * Author URI:        https://optimole.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       optimole-wp
 * Domain Path:       /languages
 * WordPress Available:  yes
 * Requires License:    no
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Autoloader function.
 *
 * @param string $class Class to load.
 */
function optml_autoload( $class ) {
	$prefix = 'Optml';
	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}
	foreach ( [ '/inc/', '/inc/traits/', '/inc/image_properties/', '/inc/asset_properties/', '/inc/compatibilities/', '/inc/conflicts/', '/inc/cli/' ] as $folder ) {
		$file = str_replace( $prefix . '_', '', $class );
		$file = strtolower( $file );
		$file = dirname( __FILE__ ) . $folder . $file . '.php';
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
}

/**
 * Deactivates optimole plugin.
 *
 * Used when the user does not have the minimum PHP required version.
 *
 * @since    8.1.4
 */
function optml_deactivate() {
	if ( is_plugin_active( 'optimole-wp/optimole-wp.php' ) ) {
		deactivate_plugins( 'optimole-wp/optimole-wp.php' );
	}
}

/**
 * Shows a notice for sites running PHP less than 5.4.
 */
function optml_php_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<?php echo sprintf( __( '%1$s You\'re using a PHP version lower than 5.4! %2$sOptimole%3$s requires at least %4$sPHP 5.4%5$s to function properly. Plugin has been deactivated. %6$sLearn more here%5$s. %7$s', 'optimole-wp' ), '<p>', '<b>', '</b>', '<b>', '</b>', '<a href="https://themeisle.com/blog/upgrade-wordpress-to-php-7/" target="_blank">', '</a>', '</p>' ); ?>
	</div>
	<?php
}

/**
 * Initiate the Optimole plugin.
 *
 * @return Optml_Main Optimole instance.
 */
function optml() {
	if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
		add_action( 'admin_notices', 'optml_php_notice' );
		add_action( 'admin_init', 'optml_deactivate' );

		return null;
	}
	define( 'OPTML_URL', plugin_dir_url( __FILE__ ) );
	define( 'OPTML_PATH', plugin_dir_path( __FILE__ ) );
	define( 'OPTML_VERSION', '3.5.4' );
	define( 'OPTML_NAMESPACE', 'optml' );
	define( 'OPTML_BASEFILE', __FILE__ );
	// Fallback for old PHP versions when this constant is not defined.
	if ( ! defined( 'PHP_INT_MIN' ) ) {
		define( 'PHP_INT_MIN', - 999999 );
	}

	if ( ! defined( 'OPTML_DEBUG' ) ) {
		define( 'OPTML_DEBUG', ( defined( 'TEST_GROUND' ) && TEST_GROUND ) ? true : false );

	}
	if ( ! defined( 'OPTML_DEBUG_MEDIA' ) ) {
		define( 'OPTML_DEBUG_MEDIA', false );
	}

	return Optml_Main::instance();
}

spl_autoload_register( 'optml_autoload' );

optml();
