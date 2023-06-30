<?php
/**
 * The Conflicting Plugins class, documents and displays dashboard notice for conflicting plugins.
 *
 * @package \Optimole\Inc\Conflicts
 * @author  Hardeep Asrani <hardeep@optimole.com>
 */

/**
 * Class Optml_Conflicting_Plugins
 *
 * @since 3.8.0
 */
class Optml_Conflicting_Plugins {

	/**
	 * Option key.
	 *
	 * @since  3.8.0
	 * @access private
	 * @var    string
	 */
	private $option_main = 'optml_dismissed_plugin_conflicts';

	/**
	 * Optml_Conflicting_Plugins constructor.
	 *
	 * @since  3.8.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_optml_dismiss_conflict_notice', [ $this, 'dismiss_notice' ] );
		add_filter( 'all_plugins', [ $this, 'filter_conflicting_plugins' ] );
	}

	/**
	 * Define conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access private
	 * @return array
	 */
	private function defined_plugins() {
		$plugins = [
			'wp-smush'     => 'wp-smushit/wp-smush.php',
			'wp-smush-pro' => 'wp-smush-pro/wp-smush.php',
			'kraken'       => 'kraken-image-optimizer/kraken.php',
			'tinypng'      => 'tiny-compress-images/tiny-compress-images.php',
			'shortpixel'   => 'shortpixel-image-optimiser/wp-shortpixel.php',
			'ewww'         => 'ewww-image-optimizer/ewww-image-optimizer.php',
			'ewww-cloud'   => 'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php',
			'imagerecycle' => 'imagerecycle-pdf-image-compression/wp-image-recycle.php',
			'imagify'      => 'imagify/imagify.php',
			// 'plugin-slug' => 'plugin-folder/plugin-file.php'
		];

		return apply_filters( 'optml_conflicting_defined_plugins', $plugins );
	}

	/**
	 * Get a list of active conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access private
	 * @return array
	 */
	private function get_active_plugins() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$conflicting_plugins = $this->defined_plugins();
		$conflicting_plugins = array_filter( $conflicting_plugins, 'is_plugin_active' );
		return apply_filters( 'optml_conflicting_active_plugins', $conflicting_plugins );
	}

	/**
	 * Get a list of dismissed conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access private
	 * @return array
	 */
	private function get_dismissed_notices() {
		$dismissed_conflicts = get_option( $this->option_main, '{}' );
		$dismissed_conflicts = json_decode( $dismissed_conflicts, true );

		if ( empty( $dismissed_conflicts ) ) {
			return [];
		}

		return $dismissed_conflicts;
	}

	/**
	 * Get a list of undismissed conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access private
	 * @param  boolean $show_dismissed Also show the dismissed plugins.
	 * @return array
	 */
	public function get_conflicting_plugins( $show_dismissed = false ) {
		$conflicting_plugins = $this->get_active_plugins();

		if ( true === $show_dismissed ) {
			return $conflicting_plugins;
		}

		$dismissed_conflicts = $this->get_dismissed_notices();

		$conflicting_plugins = array_diff_key( $conflicting_plugins, $dismissed_conflicts );

		return $conflicting_plugins;
	}

	/**
	 * Checks if there are any conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access public
	 * @return boolean
	 */
	public function has_conflicting_plugins() {
		$conflicting_plugins = $this->get_conflicting_plugins();

		return ! empty( $conflicting_plugins );
	}

	/**
	 * Dismiss conflicting plugins.
	 *
	 * @since  3.8.0
	 * @access public
	 */
	public function dismiss_conflicting_plugins() {
		$options = get_option( $this->option_main, '{}' );
		$options = json_decode( $options, true );

		if ( empty( $options ) ) {
			$options = [];
		}

		$conflicting_plugins = $this->get_conflicting_plugins();

		foreach ( $conflicting_plugins as $slug => $file ) {
			$conflicting_plugins[ $slug ] = true;
		}

		$options = array_merge( $options, $conflicting_plugins );

		update_option( $this->option_main, wp_json_encode( $options ) );
	}

	/**
	 * Check if we should show the notice.
	 *
	 * @since  3.8.0
	 * @access public
	 * @return bool Should show?
	 */
	public function should_show_notice() {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return false;
		}

		if ( is_network_admin() ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$current_screen = get_current_screen();

		if ( empty( $current_screen ) ) {
			return false;
		}

		if ( ! $this->has_conflicting_plugins() ) {
			return false;
		}

		return true;
	}

	/**
	 * Filter conflicting plugins.
	 * It will appear at wp-admin/plugins.php?optimole_conflicts
	 *
	 * @since  3.8.0
	 * @access public
	 * @param  array $plugins List of plugins.
	 * @return array
	 */
	public function filter_conflicting_plugins( $plugins ) {
		if ( ! isset( $_GET['optimole_conflicts'] ) ) {
			return $plugins;
		}

		$allowed_plugins = $this->get_conflicting_plugins( true );

		$filtered_plugins = [];

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( in_array( $plugin_file, $allowed_plugins, true ) ) {
				$filtered_plugins[ $plugin_file ] = $plugin_data;
			}
		}

		return $filtered_plugins;
	}

	/**
	 * Update the option value using AJAX
	 *
	 * @since  3.8.0
	 * @access public
	 */
	public function dismiss_notice() {
		if ( ! isset( $_POST['nonce'] ) ) {
			$response = [
				'success' => false,
				'message' => 'Missing nonce or value.',
			];
			wp_send_json( $response );
		}

		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'optml_dismiss_conflict_notice' ) ) {
			$response = [
				'success' => false,
				'message' => 'Invalid nonce.',
			];
			wp_send_json( $response );
		}

		$this->dismiss_conflicting_plugins();

		$response = [
			'success' => true,
		];

		wp_send_json( $response );
	}
}
