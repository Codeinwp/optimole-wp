<?php

use OptimoleWP\BgOptimizer\Lazyload;
use OptimoleWP\PageProfiler\Profile;

/**
 * Background lazyload selector tests: default watchers and CSS injection through profiler selectors.
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
	public function test_is_safe_selector_accepts_generated_selectors( $selector ): void {
		$this->assertTrue( Lazyload::is_safe_selector( $selector ) );
	}

	/**
	 * Plain selectors getUniqueSelector() emits.
	 *
	 * @return array<string, array{string}>
	 */
	public function safe_selectors(): array {
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
	public function test_is_safe_selector_rejects_metacharacters( $selector ): void {
		$this->assertFalse( Lazyload::is_safe_selector( $selector ) );
	}

	/**
	 * Selectors that must not be embedded (injection or CSS-invalid, including Tailwind's `:` and `/`).
	 *
	 * @return array<string, array{mixed}>
	 */
	public function unsafe_selectors(): array {
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
	public function test_personalized_css_drops_injected_selectors(): void {
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
	 * Issue 1216: all above-fold elements of a watcher are exempted in one rule, since a separate
	 * rule per element would still hide the other elements.
	 */
	public function test_personalized_css_exempts_all_above_fold_elements_in_one_rule(): void {
		$device_data = [ 'bg' => [ self::WATCHER => [ '#hero-a' => [], '#hero-b' => [] ] ] ];

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);

		$this->assertStringContainsString( 'html ' . self::WATCHER . ':not(#hero-a):not(#hero-b):not(.optml-bg-lazyloaded)', $css );
		$this->assertStringNotContainsString( 'html ' . self::WATCHER . ':not(#hero-a):not(.optml-bg-lazyloaded)', $css );
		$this->assertStringNotContainsString( 'html ' . self::WATCHER . ':not(#hero-b):not(.optml-bg-lazyloaded)', $css );
	}

	/**
	 * A watcher the profile does not list keeps its generic hide rule while the watcher list is below the cap.
	 */
	public function test_personalized_css_keeps_generic_rule_for_unlisted_watchers(): void {
		$device_data = [ 'bg' => [ '.bg-0' => [ '#hero' => [] ] ] ];

		$css = $this->get_css_with_watchers( 1, $device_data );

		$this->assertStringContainsString( 'html ' . self::WATCHER . ':not(.optml-bg-lazyloaded)', $css );
		$this->assertStringContainsString( 'html .bg-0:not(#hero):not(.optml-bg-lazyloaded)', $css );
	}

	/**
	 * When the profile lists the maximum number of watchers, the client may have cut others, so unlisted
	 * watchers get no hide rule.
	 */
	public function test_personalized_css_skips_unlisted_watchers_when_watcher_list_is_full(): void {
		$watchers = array_map(
			static function ( int $index ): string {
				return '.bg-' . $index;
			},
			range( 0, Lazyload::MAX_WATCHERS )
		);
		$bg = array_fill_keys( array_slice( $watchers, 0, Lazyload::MAX_WATCHERS ), [] );
		$bg['.bg-0'] = [ '#hero' => [] ];

		$css = $this->get_css_with_watchers( Lazyload::MAX_WATCHERS + 1, [ 'bg' => $bg ] );

		$this->assertStringNotContainsString( 'html .bg-' . Lazyload::MAX_WATCHERS . ':', $css );
		$this->assertStringNotContainsString( 'html ' . self::WATCHER . ':', $css );
		$this->assertStringContainsString( 'html .bg-0:not(#hero):not(.optml-bg-lazyloaded)', $css );
		$this->assertStringContainsString( 'html .bg-1:not(.optml-bg-lazyloaded)', $css );
	}

	/**
	 * A watcher whose above-fold list hit the cap may be missing above-fold elements, so it gets no hide rule.
	 */
	public function test_personalized_css_skips_watcher_with_full_above_fold_list(): void {
		$above_fold = [];
		foreach ( range( 1, Lazyload::MAX_ABOVE_FOLD_SELECTORS ) as $index ) {
			$above_fold[ '#hero-' . $index ] = [];
		}
		$device_data = [
			'bg' => [
				'.bg-0' => $above_fold,
				'.bg-1' => array_slice( $above_fold, 0, Lazyload::MAX_ABOVE_FOLD_SELECTORS - 1 ),
			],
		];

		$css = $this->get_css_with_watchers( 2, $device_data );

		$this->assertStringNotContainsString( 'html .bg-0:', $css );
		$this->assertStringContainsString( 'html .bg-1:not(#hero-1)', $css );
	}

	/**
	 * Build the personalized CSS for both devices with extra watchers .bg-0 … .bg-{count-1} registered.
	 *
	 * @param int                  $count       Number of extra watchers to register.
	 * @param array<string, mixed> $device_data Profile data used for both devices.
	 *
	 * @return string Personalized CSS.
	 */
	private function get_css_with_watchers( int $count, array $device_data ): string {
		$add_watchers = static function ( array $selectors ) use ( $count ): array {
			foreach ( range( 0, $count - 1 ) as $index ) {
				$selectors[] = '.bg-' . $index;
			}
			return $selectors;
		};
		add_filter( 'optml_lazyload_bg_selectors', $add_watchers );
		$this->reset_background_selectors();

		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => $device_data,
				Profile::DEVICE_TYPE_DESKTOP => $device_data,
			]
		);
		remove_filter( 'optml_lazyload_bg_selectors', $add_watchers );

		return $css;
	}

	/**
	 * An injected LCP selector voids the device rule.
	 */
	public function test_personalized_css_drops_injected_lcp_selector(): void {
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
	 * A safe selector followed by an unsafe one still voids the whole device rule.
	 */
	public function test_personalized_css_drops_rule_when_any_selector_unsafe(): void {
		$device_data = [
			'bg' => [
				self::WATCHER => [
					'#hero'       => [],
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
		$this->assertSame( '', $css );
	}

	/**
	 * When only one device is voided, the surviving device stays scoped to its own media query
	 * instead of leaking a media-query-less rule onto the voided device's viewport.
	 */
	public function test_personalized_css_scopes_surviving_device_to_media_query(): void {
		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => [ 'bg' => [ self::WATCHER => [ self::PAYLOAD => [] ] ] ],
				Profile::DEVICE_TYPE_DESKTOP => [
					'bg'  => [ self::WATCHER => [ '#hero' => [] ] ],
					'lcp' => [
						'type'       => 'bg',
						'bgSelector' => 'body > div.hero:nth-of-type(1)',
					],
				],
			]
		);

		$this->assertStringNotContainsString( 'background:red', $css );
		$this->assertStringNotContainsString( self::PAYLOAD, $css );
		// The surviving desktop rule must stay desktop-scoped and not apply on phones.
		$this->assertStringStartsWith( '@media (min-width: 600px) {', $css );
		$this->assertStringNotContainsString( '@media (max-width: 600px)', $css );
		// The safe above-fold and LCP selectors are still rendered (the gating must not over-reject them).
		$this->assertStringContainsString( ':not(#hero):not(.optml-bg-lazyloaded)', $css );
		$this->assertStringContainsString( ':not(body > div.hero:nth-of-type(1))', $css );
	}

	/**
	 * The mirror case: when desktop is voided, the surviving mobile rule stays mobile-scoped.
	 */
	public function test_personalized_css_scopes_surviving_mobile_device_to_media_query(): void {
		$css = Lazyload::get_personalized_css(
			[
				Profile::DEVICE_TYPE_MOBILE  => [ 'bg' => [ self::WATCHER => [ '#hero' => [] ] ] ],
				Profile::DEVICE_TYPE_DESKTOP => [ 'bg' => [ self::WATCHER => [ self::PAYLOAD => [] ] ] ],
			]
		);

		$this->assertStringNotContainsString( 'background:red', $css );
		$this->assertStringNotContainsString( self::PAYLOAD, $css );
		// The surviving mobile rule must stay mobile-scoped and not apply on desktop.
		$this->assertStringStartsWith( '@media (max-width: 600px) {', $css );
		$this->assertStringNotContainsString( 'min-width', $css );
		$this->assertStringContainsString( ':not(#hero):not(.optml-bg-lazyloaded)', $css );
	}

	/**
	 * A block style with a gradient and an image in one background-image value is watched.
	 *
	 * @dataProvider gradient_with_image_styles
	 *
	 * @param string $style Inline style rendered by the core style engine.
	 */
	public function test_default_watchers_match_gradient_with_background_image( string $style ): void {
		$this->assertTrue( $this->is_watched( 'wp-block-quote has-background', $style ) );
	}

	/**
	 * Inline styles core renders for a background gradient combined with an image.
	 *
	 * @return array<string, array{0: string}>
	 */
	public function gradient_with_image_styles(): array {
		return [
			'custom gradient' => [ "background-image:linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%), url('https://example.com/hero.jpg');background-size:cover;" ],
			'preset gradient' => [ "background-image:var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple), url('https://example.com/hero.jpg');background-size:cover;" ],
		];
	}

	/**
	 * A gradient without an image is not watched.
	 */
	public function test_default_watchers_skip_gradient_without_image(): void {
		$this->assertFalse( $this->is_watched( 'wp-block-quote has-background', 'background-image:linear-gradient(135deg,red 0%,blue 100%);' ) );
	}

	/**
	 * Whether any background lazyload selector matches an element with the given class and style.
	 *
	 * Only evaluates selectors built entirely from `[class*="..."]` / `[style*="..."]` parts.
	 *
	 * @param string $class_attr Element class attribute.
	 * @param string $style      Element style attribute.
	 */
	private function is_watched( string $class_attr, string $style ): bool {
		$attributes = [
			'class' => $class_attr,
			'style' => $style,
		];
		foreach ( Optml_Lazyload_Replacer::get_background_lazyload_selectors() as $selector ) {
			preg_match_all( '/\[(class|style)\*="([^"]*)"\]/', $selector, $parts, PREG_SET_ORDER );
			if ( empty( $parts ) || implode( '', array_column( $parts, 0 ) ) !== $selector ) {
				continue;
			}
			foreach ( $parts as $part ) {
				if ( strpos( $attributes[ $part[1] ], $part[2] ) === false ) {
					continue 2;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Reset the cached background lazyload selectors.
	 */
	private function reset_background_selectors(): void {
		$property = new ReflectionProperty( Optml_Lazyload_Replacer::class, 'background_lazyload_selectors' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}
