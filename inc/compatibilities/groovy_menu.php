<?php
/**
 * Class Optml_groovy_menu.
 *
 * @reason Groovy Menu's auto-integration opens its own output buffer on `init`
 * and, on `shutdown` at priority 0, calls ob_get_clean() on whichever buffer is
 * on top to insert the menu markup after <body>. Since 4.2.12 we capture and
 * process our buffer at `shutdown` (PHP_INT_MIN) and re-arm an empty one, so
 * Groovy Menu receives an empty string, finds no <body> and drops the menu.
 *
 * We apply Groovy Menu's final-output filter to the page we capture, before
 * image replacement, and unhook its own shutdown step at that moment. The menu
 * is inserted regardless of buffer order and its images are optimized too. When
 * our capture does not run (legacy `optml_capture_at_shutdown` mode, or a
 * third-party flush of our buffer) Groovy Menu keeps its own shutdown step.
 */
class Optml_groovy_menu extends Optml_compatibility {
	/**
	 * Groovy Menu's shutdown callback that grabs the top output buffer.
	 */
	const GROOVY_SHUTDOWN_CALLBACK = 'groovy_menu_pre_shutdown';

	/**
	 * Groovy Menu's filter that inserts the menu markup into the page HTML.
	 */
	const GROOVY_OUTPUT_FILTER = 'groovy_menu_final_output';

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		return function_exists( self::GROOVY_SHUTDOWN_CALLBACK )
			&& has_action( 'shutdown', self::GROOVY_SHUTDOWN_CALLBACK ) !== false;
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'optml_captured_page_html', [ $this, 'insert_menu' ] );
	}

	/**
	 * Insert the Groovy Menu markup into the captured page and take over its shutdown step.
	 *
	 * @param string $html The captured page HTML, before image replacement.
	 *
	 * @return string
	 */
	public function insert_menu( $html ) {
		$priority = has_action( 'shutdown', self::GROOVY_SHUTDOWN_CALLBACK );
		if ( $priority === false ) {
			return $html;
		}
		// Our capture ran, so Groovy Menu must not ob_get_clean() the re-armed empty buffer afterwards.
		remove_action( 'shutdown', self::GROOVY_SHUTDOWN_CALLBACK, $priority );

		// Same guard as Groovy Menu's own shutdown callback.
		if ( ! defined( 'GROOVY_MENU_SCRIPTS_INIT' ) ) {
			return $html;
		}

		return apply_filters( self::GROOVY_OUTPUT_FILTER, $html ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Groovy Menu's own filter.
	}
}
