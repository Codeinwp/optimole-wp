<?php
/**
 * Page processing after a fatal error.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

/**
 * Class Test_Fatal_Error_Passthrough.
 */
class Test_Fatal_Error_Passthrough extends WP_UnitTestCase {

	/**
	 * Engine fatals that can cut a query short count as fatal errors.
	 *
	 * @dataProvider fatal_error_types
	 *
	 * @param int $type The error type.
	 */
	public function test_engine_fatal_errors_are_detected( int $type ): void {
		$this->assertTrue( Optml_Manager::is_fatal_error( [ 'type' => $type, 'message' => 'Allowed memory size exhausted' ] ) );
	}

	/**
	 * @return array<string, array{int}>
	 */
	public function fatal_error_types(): array {
		return [
			'E_ERROR'         => [ E_ERROR ],
			'E_PARSE'         => [ E_PARSE ],
			'E_CORE_ERROR'    => [ E_CORE_ERROR ],
			'E_COMPILE_ERROR' => [ E_COMPILE_ERROR ],
		];
	}

	/**
	 * Warnings, notices and deprecations don't stop the page from being processed.
	 *
	 * @dataProvider non_fatal_errors
	 *
	 * @param array<string, mixed>|null $error The last error.
	 */
	public function test_other_errors_are_not_fatal( $error ): void {
		$this->assertFalse( Optml_Manager::is_fatal_error( $error ) );
	}

	/**
	 * @return array<string, array{array<string, mixed>|null}>
	 */
	public function non_fatal_errors(): array {
		return [
			'no error'     => [ null ],
			'E_WARNING'    => [ [ 'type' => E_WARNING ] ],
			'E_NOTICE'     => [ [ 'type' => E_NOTICE ] ],
			'E_DEPRECATED' => [ [ 'type' => E_DEPRECATED ] ],
			'no type'      => [ [ 'message' => 'x' ] ],
		];
	}

	/**
	 * A warning earlier in the request still lets the output handler process the page.
	 */
	public function test_output_handler_processes_page_after_a_warning(): void {
		$settings = new Optml_Settings();
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'test123',
				'cdn_secret' => '12345',
				'whitelist'  => [ 'example.org' ],
			]
		);
		$settings->update( 'lazyload', 'disabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- sets error_get_last() to a warning.
		@trigger_error( 'earlier warning', E_USER_WARNING );
		$html = '<html><body><img src="http://example.org/wp-content/uploads/image.jpg"></body></html>';

		$result = Optml_Manager::instance()->handle_buffer_fallback( $html );

		$this->assertStringContainsString( 'i.optimole.com', $result );
	}
}
