<?php

/**
 * Class Optml_swift_performance.
 *
 * @reason Cache_swift_performance stores the content of the page before Optimole starts replacing url's
 */
class Optml_swift_performance extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$settings = new Optml_Settings();
		return $settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'swift-performance-lite/performance.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'swift_performance_buffer', [$this, 'optml_process_content'], 10, 1 );
	}
	/**
	 * Process content buffer.
	 */
	public function optml_process_content( $buffer ) {
		$buffer = Optml_Manager::instance()->replace_content( $buffer );
		return $buffer;
	}
}

