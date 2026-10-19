<?php
/**
 * Minimal WP-CLI stand-ins, so the plugin's WP-CLI commands can run under PHPUnit.
 *
 * `WP_CLI::error()` stops the real command; here it throws, and the output lines are recorded.
 *
 * @package Optimole-WP
 */

namespace WP_CLI\Utils {
	if ( ! function_exists( __NAMESPACE__ . '\make_progress_bar' ) ) {
		/**
		 * @param string $message Progress bar label.
		 * @param int    $count   Number of ticks.
		 * @return object
		 */
		function make_progress_bar( $message, $count ) {
			return new class() {
				public function tick() {}
				public function finish() {}
			};
		}
	}
}

namespace {
	if ( ! class_exists( 'WP_CLI' ) ) {
		/**
		 * Thrown where WP-CLI would print an error and exit.
		 */
		class Optml_Test_WP_CLI_Error extends Exception {}

		/**
		 * Records output and turns errors into exceptions.
		 */
		class WP_CLI {
			/**
			 * @var string[]
			 */
			public static $lines = [];

			public static function add_command() {}

			public static function line( $message = '' ) {
				self::$lines[] = (string) $message;
			}

			public static function success( $message ) {
				self::$lines[] = (string) $message;
			}

			public static function error( $message, $exit = true ) {
				throw new Optml_Test_WP_CLI_Error( (string) $message );
			}
		}

		/**
		 * Base class of WP-CLI commands.
		 */
		class WP_CLI_Command {}
	}
}
