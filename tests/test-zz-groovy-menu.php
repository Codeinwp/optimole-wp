<?php
/**
 * WordPress unit tests for the Groovy Menu compatibility.
 *
 * Named test-zz-* on purpose: these tests drive the full page replacement
 * pipeline and must load after the order-sensitive older suites.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2026, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! function_exists( 'groovy_menu_pre_shutdown' ) ) {
	/**
	 * Stub of Groovy Menu's shutdown callback: grabs the top buffer and inserts the menu.
	 */
	function groovy_menu_pre_shutdown() {
		$GLOBALS['gm_test_shutdown_calls'] = ( $GLOBALS['gm_test_shutdown_calls'] ?? 0 ) + 1;
		$final                             = ob_get_clean();
		echo apply_filters( 'groovy_menu_final_output', $final ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'groovy_menu_add_after_body' ) ) {
	/**
	 * Stub of Groovy Menu's final-output filter: inserts the menu after <body>.
	 *
	 * @param string $output Page HTML.
	 *
	 * @return string
	 */
	function groovy_menu_add_after_body( $output ) {
		return preg_replace( '#(\<body.*\>)#i', '$1' . Test_Groovy_Menu::MENU, $output, 1 );
	}
}

/**
 * Class Test_Groovy_Menu.
 */
class Test_Groovy_Menu extends WP_UnitTestCase {
	const MENU = '<div class="gm-navbar"><img src="http://example.org/wp-content/uploads/gm-logo.jpg"></div>';
	const PAGE = '<html><body><img src="http://example.org/wp-content/uploads/page.jpg"></body></html>';

	/**
	 * The output-buffer nesting level before each test.
	 *
	 * @var int
	 */
	private $base_level = 0;

	/**
	 * The compatibility under test.
	 *
	 * @var Optml_groovy_menu
	 */
	private $compatibility;

	public function setUp(): void {
		parent::setUp();
		if ( ! defined( 'GROOVY_MENU_SCRIPTS_INIT' ) ) {
			define( 'GROOVY_MENU_SCRIPTS_INIT', true );
		}
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

		$GLOBALS['gm_test_shutdown_calls'] = 0;
		add_action( 'shutdown', 'groovy_menu_pre_shutdown', 0 );
		add_filter( 'groovy_menu_final_output', 'groovy_menu_add_after_body' );
		$this->compatibility = new Optml_groovy_menu();

		$this->reset_buffer_state();
		$this->base_level = ob_get_level();
	}

	public function tearDown(): void {
		$this->reset_buffer_state( true );
		while ( ob_get_level() > $this->base_level ) {
			// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			if ( ! @ob_end_clean() ) {
				break;
			}
		}
		$this->reset_buffer_state();
		remove_action( 'shutdown', 'groovy_menu_pre_shutdown', 0 );
		remove_filter( 'groovy_menu_final_output', 'groovy_menu_add_after_body' );
		remove_filter( 'optml_captured_page_html', [ $this->compatibility, 'insert_menu' ] );
		remove_filter( 'optml_capture_at_shutdown', '__return_false' );
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
	 * The compatibility loads only when Groovy Menu's auto-integration hooked its shutdown step.
	 */
	public function test_loads_only_with_auto_integration() {
		$this->assertTrue( $this->compatibility->should_load() );
		remove_action( 'shutdown', 'groovy_menu_pre_shutdown', 0 );
		$this->assertFalse( $this->compatibility->should_load() );
	}

	/**
	 * Groovy Menu buffer opened on init sits below ours: the menu is inserted and optimized.
	 */
	public function test_menu_inserted_when_groovy_buffer_is_below_ours() {
		$this->compatibility->register();
		$manager = Optml_Manager::instance();
		ob_start();
		ob_start(); // Groovy Menu's own buffer, opened on init.
		$manager->process_template_redirect_content();
		echo self::PAGE; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		$this->assertFalse( has_action( 'shutdown', 'groovy_menu_pre_shutdown' ), 'Groovy Menu shutdown step is taken over.' );

		$manager->close_final_buffer();
		ob_end_flush(); // Core flushes Groovy Menu's buffer at shutdown.
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'gm-navbar' ) );
		$this->assertSame( 2, substr_count( $out, 'i.optimole.com' ) );
		$this->assertStringNotContainsString( '"http://example.org/wp-content/uploads/gm-logo.jpg', $out );
		$this->assertSame( 0, $GLOBALS['gm_test_shutdown_calls'] );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * Groovy Menu buffer stacked above ours is flushed through, and the menu is still inserted.
	 */
	public function test_menu_inserted_when_groovy_buffer_is_above_ours() {
		$this->compatibility->register();
		$manager = Optml_Manager::instance();
		ob_start();
		$manager->process_template_redirect_content();
		ob_start(); // Groovy Menu's buffer opened after ours.
		echo self::PAGE; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();
		$manager->close_final_buffer();
		$out = ob_get_clean();

		$this->assertFalse( has_action( 'shutdown', 'groovy_menu_pre_shutdown' ) );
		$this->assertSame( 1, substr_count( $out, 'gm-navbar' ) );
		$this->assertSame( 2, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * In legacy in-handler mode Groovy Menu keeps its own shutdown step and still works.
	 */
	public function test_legacy_mode_keeps_groovy_shutdown_step() {
		add_filter( 'optml_capture_at_shutdown', '__return_false' );
		$this->compatibility->register();
		$manager = Optml_Manager::instance();
		ob_start();
		ob_start(); // Groovy Menu's buffer.
		$manager->process_template_redirect_content();
		echo self::PAGE; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$manager->close_buffer();

		$this->assertSame( 0, has_action( 'shutdown', 'groovy_menu_pre_shutdown' ), 'Groovy Menu shutdown step is left in place.' );

		groovy_menu_pre_shutdown(); // Groovy Menu at shutdown priority 0.
		$out = ob_get_clean();

		$this->assertSame( 1, $GLOBALS['gm_test_shutdown_calls'] );
		$this->assertSame( 1, substr_count( $out, 'gm-navbar' ) );
		$this->assertSame( 1, substr_count( $out, 'i.optimole.com' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}

	/**
	 * When third-party code flushes our buffer before shutdown, Groovy Menu keeps its own shutdown step.
	 */
	public function test_early_flush_keeps_groovy_shutdown_step() {
		$this->compatibility->register();
		$manager = Optml_Manager::instance();
		ob_start();
		ob_start(); // Groovy Menu's buffer.
		$manager->process_template_redirect_content();
		echo self::PAGE; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush(); // Third-party force flush of our buffer.
		$manager->close_buffer();
		$manager->close_final_buffer();

		$this->assertSame( 0, has_action( 'shutdown', 'groovy_menu_pre_shutdown' ) );

		groovy_menu_pre_shutdown();
		$out = ob_get_clean();

		$this->assertSame( 1, substr_count( $out, 'gm-navbar' ) );
		$this->assertSame( $this->base_level, ob_get_level() );
	}
}
