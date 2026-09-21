<?php
/**
 * WordPress unit tests for the output-buffer capture lifecycle.
 *
 * Named test-zz-buffer.php on purpose: these tests drive the full page
 * replacement pipeline, and some older suites (e.g. test-dam.php) are
 * sensitive to the run order, so this file must load after them.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2026, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! function_exists( 'optml_test_flushable_handler' ) ) {
	/**
	 * Pass-through output handler with a stable name for ob_get_status().
	 *
	 * @param string $content Buffer content.
	 *
	 * @return string
	 */
	function optml_test_flushable_handler( $content ) {
		return $content;
	}
}

/**
 * Class Test_Buffer.
 */
class Test_Buffer extends WP_UnitTestCase {
	const IMG_TAGS = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" /></div></div> ';

	/**
	 * The output-buffer nesting level before each test.
	 *
	 * @var int
	 */
	private $base_level = 0;

	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		$settings->update( 'lazyload', 'disabled' );
		$settings->update( 'cdn', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		$this->reset_buffer_state();
		$this->base_level = ob_get_level();
	}

	public function tearDown(): void {
		// Make any leftover capture handler a pass-through before cleaning up.
		$this->reset_buffer_state( true );
		while ( ob_get_level() > $this->base_level ) {
			// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			if ( ! @ob_end_clean() ) {
				break;
			}
		}
		$this->reset_buffer_state();
		remove_action( 'shutdown', [ Optml_Manager::instance(), 'close_deferred_buffer' ], 0 );
		parent::tearDown();
	}

	/**
	 * Reset Optml_Manager buffer statics between tests.
	 *
	 * @param bool $processed Value for the processed flag.
	 */
	private function reset_buffer_state( $processed = false ) {
		$reflection = new ReflectionClass( Optml_Manager::class );
		foreach ( [ 'ob_started' => false, 'ob_level' => 0, 'ob_processed' => $processed ] as $property => $value ) {
			$prop = $reflection->getProperty( $property );
			$prop->setAccessible( true );
			$prop->setValue( null, $value );
		}
	}

	/**
	 * Callbacks on our filters may use output buffering without fataling.
	 *
	 * Before processing moved outside the display handler, the nested
	 * ob_start() below crashed with "Cannot use output buffering in output
	 * buffering display handlers".
	 */
	public function test_filter_callbacks_can_use_output_buffering() {
		$manager = Optml_Manager::instance();
		$probed  = 0;
		add_filter(
			'optml_url_pre_process',
			function ( $html ) use ( &$probed ) {
				ob_start();
				echo 'probe';
				ob_get_clean();
				$probed ++;
				return $html;
			}
		);
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, $probed );
		$this->assertStringContainsString( 'i.optimole.com', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * A buffer another plugin stacks on top of ours is not force-flushed at the
	 * start of shutdown. The capture waits for the priority 0 callbacks, then the
	 * foreign handler runs first and we process its transformed output.
	 */
	public function test_foreign_buffer_above_defers_capture() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		// Third-party handler started after ours, e.g. a minifier.
		ob_start(
			function ( $content ) {
				return $content . '<img src="http://example.org/wp-content/uploads/foreign.jpg">';
			}
		);
		$foreign_level = ob_get_level();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		$this->assertSame( $foreign_level, ob_get_level(), 'The foreign buffer is left alone at the start of shutdown.' );
		$this->assertSame( 0, has_action( 'shutdown', [ $manager, 'close_deferred_buffer' ] ) );

		$manager->close_deferred_buffer();
		$out = ob_get_clean();

		// Both the page image and the one appended by the foreign handler are optimized.
		$this->assertSame( 2, substr_count( $out, 'i.optimole.com' ) );
		$this->assertStringNotContainsString( '"http://example.org/wp-content/uploads/foreign.jpg', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Code that opened a buffer after ours and reads it back on shutdown
	 * priority 0 (FacetWP, Groovy Menu with an early Optimole buffer) still
	 * finds its own buffer, and what it echoes back is processed.
	 */
	public function test_foreign_buffer_above_can_be_read_back_at_shutdown() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		ob_start(); // Third-party buffer opened after ours.
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		// The third party reads its buffer back on shutdown priority 0.
		$page = ob_get_clean();
		$this->assertStringContainsString( 'wp-custom-header', $page );
		echo '<nav>menu</nav>' . $page; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$manager->close_deferred_buffer();
		$out = ob_get_clean();

		$this->assertStringContainsString( '<nav>menu</nav>', $out );
		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Code that opened a buffer before ours and reads it back on shutdown
	 * priority 0 finds its own buffer on top, holding the processed page. We
	 * leave no empty buffer of ours above it.
	 */
	public function test_foreign_buffer_below_receives_processed_page() {
		$manager = Optml_Manager::instance();
		ob_start();
		ob_start(); // Third-party buffer opened on init, before ours.
		$foreign_level = ob_get_level();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		$this->assertSame( $foreign_level, ob_get_level(), 'No buffer of ours is left above the foreign one.' );
		$this->assertFalse( has_action( 'shutdown', [ $manager, 'close_deferred_buffer' ] ) );

		// The third party reads its buffer back on shutdown priority 0.
		$page = ob_get_clean();
		$this->assertSame( 1, substr_count( $page, 'i.optimole.com' ) );
		$this->assertSame( '', ob_get_clean() );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Handlers listed as flushable, like core's template enhancement buffer,
	 * do not delay the capture.
	 */
	public function test_flushable_buffer_above_does_not_defer_capture() {
		$manager = Optml_Manager::instance();
		add_filter( 'optml_flushable_ob_handlers', [ $this, 'allow_test_handler' ] );
		ob_start();
		$manager->process_template_redirect_content();
		ob_start( 'optml_test_flushable_handler' );
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		remove_filter( 'optml_flushable_ob_handlers', [ $this, 'allow_test_handler' ] );

		$this->assertFalse( has_action( 'shutdown', [ $manager, 'close_deferred_buffer' ] ) );
		$out = ob_get_clean();
		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Mark the test handler as flushable.
	 *
	 * @param string[] $handlers Handler names.
	 *
	 * @return string[]
	 */
	public function allow_test_handler( $handlers ) {
		$handlers[] = 'optml_test_flushable_handler';

		return $handlers;
	}

	/**
	 * When third-party code force-flushes our buffer before shutdown, the
	 * content is passed through UNPROCESSED: running the filter graph inside a
	 * display handler would make any third-party ob_*() call an uncatchable
	 * fatal. close_buffer() detects the loss without side effects.
	 */
	public function test_third_party_flush_passes_content_through() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush(); // Third-party force flush of our buffer.
		$this->assertSame( $this->base_level + 1, ob_get_level() );
		$manager->close_buffer();
		$out = ob_get_clean();

		$this->assertStringNotContainsString( 'i.optimole.com', $out );
		$this->assertStringContainsString( 'themes/twentyseventeen/assets/images/header.jpg', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * A third-party flush combined with an output-buffering filter callback
	 * must not fatal. Processing inside the handler would terminate PHP with
	 * "Cannot use output buffering in output buffering display handlers",
	 * which catch ( Throwable ) cannot intercept.
	 */
	public function test_third_party_flush_with_ob_filter_does_not_fatal() {
		$manager = Optml_Manager::instance();
		add_filter(
			'optml_url_pre_process',
			function ( $html ) {
				ob_start();
				echo 'probe';
				ob_get_clean();
				return $html;
			}
		);
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush(); // Would exit(255) if the handler ran the filter graph.
		$manager->close_buffer();
		$out = ob_get_clean();

		$this->assertStringContainsString( 'themes/twentyseventeen/assets/images/header.jpg', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * A foreign buffer that ends up at our recorded nesting level is never
	 * captured or closed: ownership requires our handler identity, not just
	 * the level.
	 */
	public function test_foreign_buffer_at_same_level_is_not_consumed() {
		$manager = Optml_Manager::instance();
		$manager->process_template_redirect_content();
		ob_end_clean(); // Third party discards our buffer...
		ob_start();     // ...and opens its own at the same level.
		echo 'FOREIGN';
		$manager->close_buffer();

		$this->assertSame( $this->base_level + 1, ob_get_level() );
		$this->assertSame( 'default output handler', ob_get_status()['name'] );
		$this->assertStringContainsString( 'FOREIGN', ob_get_contents() );
		ob_end_clean();
	}

	/**
	 * Calling process_template_redirect_content() twice must not stack a
	 * second buffer, and the page is processed exactly once.
	 */
	public function test_buffer_started_once() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		$level = ob_get_level();
		$manager->process_template_redirect_content();
		$this->assertSame( $level, ob_get_level() );
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
	}

	/**
	 * An empty buffer closes without output or errors.
	 */
	public function test_empty_buffer_no_output() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		$manager->close_buffer();

		$this->assertSame( '', ob_get_clean() );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Output echoed by shutdown callbacks running after close_buffer() is not
	 * buffered by us any more: it goes straight to whatever is below.
	 */
	public function test_late_shutdown_output_is_not_buffered() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		$this->assertSame( $this->base_level + 1, ob_get_level() );
		echo 'late'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertStringEndsWith( 'late', $out );
	}

	/**
	 * Very long URLs (e.g. signed CDN URLs) must not push a replacement
	 * chunk's compiled pattern over PCRE's size limit.
	 */
	public function test_long_url_replacement_stays_within_pcre_limits() {
		$manager = Optml_Manager::instance();
		add_filter(
			'optml_content_url',
			function ( $url ) {
				return 'https://replaced.test/marker';
			}
		);
		$urls = [];
		$html = '';
		for ( $i = 0; $i < 250; $i ++ ) {
			$url    = 'https://example.org/image-' . $i . '.jpg?X-Signature=' . str_repeat( 'a1b2c3d4', 180 ) . '&i=' . $i;
			$urls[] = $url;
			$html  .= '<img src="' . $url . '">';
		}
		$out = $manager->do_url_replacement( $html, $urls );

		$this->assertSame( 250, substr_count( $out, 'https://replaced.test/marker' ) );
		$this->assertStringNotContainsString( 'X-Signature', $out );
	}

	/**
	 * The optml_capture_at_shutdown filter restores the legacy in-handler flow.
	 */
	public function test_legacy_in_handler_mode() {
		add_filter( 'optml_capture_at_shutdown', '__return_false' );
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}
}
