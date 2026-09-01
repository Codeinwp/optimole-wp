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
		$manager->close_final_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, $probed );
		$this->assertStringContainsString( 'i.optimole.com', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * A buffer another plugin stacks on top of ours is flushed through its own
	 * handler first, and we process its transformed output — never swallow it.
	 */
	public function test_foreign_buffer_above_is_flushed_first() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		// Third-party handler started after ours, e.g. a minifier.
		ob_start(
			function ( $content ) {
				return $content . '<img src="http://example.org/wp-content/uploads/foreign.jpg">';
			}
		);
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		$manager->close_final_buffer();
		$out = ob_get_clean();

		// Both the page image and the one appended by the foreign handler are optimized.
		$this->assertSame( 2, substr_count( $out, 'i.optimole.com' ) );
		$this->assertStringNotContainsString( '"http://example.org/wp-content/uploads/foreign.jpg', $out );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * When third-party code force-flushes our buffer before shutdown, the
	 * fallback handler processes the content — matching the legacy behavior —
	 * and close_buffer() detects the loss without side effects.
	 */
	public function test_third_party_flush_falls_back_to_handler_processing() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush(); // Third-party force flush of our buffer.
		$this->assertSame( $this->base_level + 1, ob_get_level() );
		$manager->close_buffer();
		$manager->close_final_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
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
		$manager->close_final_buffer();
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
		$manager->close_final_buffer();

		$this->assertSame( '', ob_get_clean() );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Output echoed by shutdown callbacks running after close_buffer() is
	 * captured by the re-armed buffer and still processed.
	 */
	public function test_late_shutdown_output_is_processed() {
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		echo self::IMG_TAGS; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_final_buffer();
		$out = ob_get_clean();

		$this->assertSame( 2, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
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
		$manager->close_final_buffer();
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}
}
