<?php
/**
 * Class Optml_groovy_menu.
 *
 * @reason Groovy Menu's auto-integration opens its own output buffer on `init`
 * and, on `shutdown` at priority 0, calls ob_get_clean() on whichever buffer is
 * on top to insert the menu markup after <body>.
 *
 * When its buffer sits below ours, we capture and process the page at the start
 * of shutdown. We apply Groovy Menu's final-output filter to that page first,
 * before image replacement, and unhook its own shutdown step at that moment, so
 * the menu images are optimized too. When its buffer sits above ours the capture
 * is deferred, Groovy Menu's own shutdown step runs first and we only make sure
 * the menu is not inserted twice. When our capture does not run at all (legacy
 * `optml_capture_at_shutdown` mode, or a third-party flush of our buffer) Groovy
 * Menu keeps its own shutdown step.
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
	 * Whether Groovy Menu's final-output filter already ran for this request.
	 *
	 * @var bool
	 */
	private $menu_inserted = false;

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
		add_filter( self::GROOVY_OUTPUT_FILTER, [ $this, 'mark_menu_inserted' ], PHP_INT_MAX ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Groovy Menu's own filter.
	}

	/**
	 * Remember that Groovy Menu's final-output filter already ran for this request.
	 *
	 * When third-party buffers sit above ours the capture is deferred, and Groovy
	 * Menu's own shutdown step runs first. The menu must not be inserted twice.
	 *
	 * @param string $html The page HTML.
	 *
	 * @return string
	 */
	public function mark_menu_inserted( $html ) {
		$this->menu_inserted = true;

		return $html;
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
		if ( $priority === false || $this->menu_inserted ) {
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
