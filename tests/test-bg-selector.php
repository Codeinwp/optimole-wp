<?php

use OptimoleWP\BgOptimizer\Lazyload;
use OptimoleWP\PageProfiler\Profile;

/**
 * Regression tests: CSS injection through profiler background selectors.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

/**
 * Class Test_Bg_Selector.
 */
class Test_Bg_Selector extends WP_UnitTestCase {

	const WATCHER = '[style*="background-image:url("]';

	const PAYLOAD = 'x){} body{background:red !important} .z:not(y';

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'test123',
				'cdn_secret' => '12345',
				'whitelist'  => [ 'example.com' ],
			]
		);
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'lazyload_type', 'viewport' );
		$settings->update( 'bg_replacer', 'enabled' );
		Optml_Lazyload_Replacer::instance()->init();
		$this->reset_background_selectors();
	}

	/**
	 * Clean up the test environment after each test.
	 */
	public function tearDown(): void {
		$this->reset_background_selectors();
		Profile::reset_current_profile();
		parent::tearDown();
	}

	/**
	 * Selectors produced by the frontend getUniqueSelector() are safe to embed.
	 *
	 * @dataProvider safe_selectors
	 *
	 * @param string $selector Selector to check.
	 */
	public function test_is_safe_selector_accepts_generated_selectors( $selector ) {
		$this->assertTrue( Lazyload::is_safe_selector( $selector ) );
	}

	/**
	 * Plain selectors getUniqueSelector() emits.
	 *
	 * @return array<string, array{string}>
	 */
	public function safe_selectors() {
		return [
			'body'            => [ 'body' ],
			'id'              => [ '#hero' ],
			'body child'      => [ 'body > div.wp-block-cover' ],
			'nested nth'      => [ '#main > section.hero.is-dark:nth-of-type(2) > div.inner_wrap' ],
			'non ascii class' => [ 'body > div.café' ],
		];
	}

	/**
	 * Selectors carrying CSS metacharacters are treated as unsafe.
	 *
	 * @dataProvider unsafe_selectors
	 *
	 * @param mixed $selector Selector to check.
	 */
	public function test_is_safe_selector_rejects_metacharacters( $selector ) {
		$this->assertFalse( Lazyload::is_safe_selector( $selector ) );
	}

	/**
	 * Selectors that must not be embedded (injection or CSS-invalid, including Tailwind's `:` and `/`).
	 *
	 * @return array<string, array{mixed}>
	 */
	public function unsafe_selectors() {
		return [
			'issue payload'    => [ self::PAYLOAD ],
			'tailwind colon'   => [ 'body > div.md:flex' ],
			'tailwind slash'   => [ 'body > div.w-1/2' ],
			'closing paren'    => [ 'div)' ],
			'selector list'    => [ 'div, body' ],
			'declaration'      => [ 'div;color:red' ],
			'braces'           => [ 'div{}' ],
			'attribute'        => [ 'div[style]' ],
			'quote'            => [ 'a"b' ],
			'newline'          => [ "a\nb" ],
			'empty'            => [ '' ],
			'array'            => [ [ 'div' ] ],
		];
	}

	/**
	 * An injected above-fold selector voids the device rule instead of leaking CSS.
	 */
	public function test_personalized_css_drops_injected_selectors() {
		$device_data = [
			'bg' => [
				self::WATCHER => [
					self::PAYLOAD => [],
				],
			],
		];

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);

		$this->assertStringNotContainsString( 'background:red', $css );
		$this->assertStringNotContainsString( self::PAYLOAD, $css );
		$this->assertSame( '', $css );
	}

	/**
	 * An injected LCP selector voids the device rule.
	 */
	public function test_personalized_css_drops_injected_lcp_selector() {
		$device_data = [
			'bg'  => [ self::WATCHER => [ '#hero' => [] ] ],
			'lcp' => [
				'type'       => 'bg',
				'bgSelector' => self::PAYLOAD,
			],
		];

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);

		$this->assertStringNotContainsString( 'background:red', $css );
		$this->assertSame( '', $css );
	}

	/**
	 * A page with only plain selectors still renders exactly as before.
	 */
	public function test_personalized_css_keeps_plain_selectors() {
		$device_data = [
			'bg'  => [ self::WATCHER => [ '#hero' => [] ] ],
			'lcp' => [
				'type'       => 'bg',
				'bgSelector' => 'body > div.hero:nth-of-type(1)',
			],
		];

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);

		$this->assertStringContainsString( 'html ' . self::WATCHER . ':not(#hero):not(.optml-bg-lazyloaded)', $css );
		$this->assertStringContainsString( ':not(body > div.hero:nth-of-type(1))', $css );
		$this->assertStringContainsString( '{ background-image: none !important; }', $css );
	}

	/**
	 * A watcher with no above-fold elements still hides all its matches (unchanged behaviour).
	 */
	public function test_personalized_css_hides_all_when_no_above_fold() {
		$device_data = [
			'bg' => [ self::WATCHER => [] ],
		];

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);

		$this->assertSame( 'html ' . self::WATCHER . ':not(.optml-bg-lazyloaded) { background-image: none !important; }', $css );
	}

	/**
	 * Reset the cached background lazyload selectors.
	 */
	private function reset_background_selectors() {
		$property = new ReflectionProperty( Optml_Lazyload_Replacer::class, 'background_lazyload_selectors' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}
