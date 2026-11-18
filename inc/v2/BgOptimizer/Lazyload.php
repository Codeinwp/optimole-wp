<?php

namespace OptimoleWP\BgOptimizer;

use OptimoleWP\PageProfiler\Profile;
use OptimoleWP\Preload\Links;
use OptimoleWP\RequestToken;
use Optml_Lazyload_Replacer;

/**
 * Class Lazyload
 *
 * @package OptimoleWP\BgOptimizer
 */
class Lazyload {
	/**
	 * Shape of a plain selector the frontend getUniqueSelector() reports: ids, tags, classes, ' > ' and :nth-of-type(N).
	 */
	const SAFE_SELECTOR_PATTERN = '/^(?:[\w\-#. >\x{00A0}-\x{10FFFF}]|:nth-of-type\(\d+\))+$/u';

	/**
	 * Most watchers the profiler reports per device; the frontend cuts the rest.
	 */
	const MAX_WATCHERS = 100;

	/**
	 * Most above-fold selectors the profiler reports per watcher; the frontend cuts the rest.
	 */
	const MAX_ABOVE_FOLD_SELECTORS = 100;

	/**
	 * Widest viewport in px the profiler counts as mobile (MOBILE_BREAKPOINT in device.js).
	 */
	private const DEVICE_BREAKPOINT = 600;

	/**
	 * Check whether a client-reported selector is a plain selector safe to embed in the stylesheet as-is.
	 *
	 * A selector carrying CSS metacharacters (`(`, `)`, `{`, `;`, `:` beyond nth-of-type, `/`, …) is not
	 * embedded: the browser already discards the whole rule when it sees one, so treating it as unsafe
	 * keeps the current behaviour while making CSS injection impossible.
	 *
	 * @param mixed $selector The selector to check.
	 *
	 * @return bool Whether the selector matches the plain-selector shape.
	 */
	public static function is_safe_selector( $selector ): bool {
		return is_string( $selector ) && $selector !== '' && preg_match( self::SAFE_SELECTOR_PATTERN, $selector ) === 1;
	}
	/**
	 * Get the marker printed before and after the general background CSS.
	 *
	 * The personalized CSS replaces everything between two markers. The marker carries the request
	 * token, so page content, such as a comment, cannot contain a matching pair.
	 *
	 * @return string The marker, a CSS comment.
	 */
	public static function get_marker(): string {
		return '/* OPTML_VIEWPORT_BG_SELECTORS ' . RequestToken::get() . ' */';
	}
	/**
	 * Get the current personalized CSS for lazy loading.
	 *
	 * @return string|null The personalized CSS, or null when the general CSS must stay.
	 */
	public static function get_current_personalized_css(): ?string {
		return self::get_personalized_css( Profile::get_current_profile_data() );
	}
	/**
	 * Get personalized CSS based on profile data.
	 *
	 * @param array $data Profile data.
	 *
	 * @return string|null The personalized CSS ('' hides nothing), or null when every device was voided and the general CSS must stay.
	 */
	public static function get_personalized_css( $data ): ?string {
		$lazyload_selectors = array_values( Optml_Lazyload_Replacer::get_background_lazyload_selectors() );
		$lazyload_selectors = array_fill_keys( $lazyload_selectors, true );
		$css_selectors = [];
		$preload_urls = [];
		$tainted = [];
		foreach ( Profile::get_active_devices() as $device ) {
			$personalized_selectors = $data[ $device ]['bg'] ?? [];
			$lcp_data = $data[ $device ]['lcp'] ?? [];
			// Guard against malformed profile shapes (e.g. a non-normalizing custom storage backend).
			$personalized_selectors = is_array( $personalized_selectors ) ? $personalized_selectors : [];
			$lcp_data = is_array( $lcp_data ) ? $lcp_data : [];
			if ( OPTML_DEBUG ) {
				do_action( 'optml_log', 'personalized_selectors: ' . $device . ' ' . print_r( $personalized_selectors, true ) );
				do_action( 'optml_log', 'LCP data: ' . $device . ' ' . print_r( $lcp_data, true ) );
			}

			$selectors = self::collect_device_selectors( $personalized_selectors, $lcp_data, $lazyload_selectors );
			// null means a client selector was unsafe, so the whole device rule is voided.
			$tainted[ $device ] = null === $selectors;
			$css_selectors[ $device ] = $tainted[ $device ] ? [] : $selectors;

			$preload_urls[ $device ] = [];
			if ( isset( $lcp_data['type'] ) && $lcp_data['type'] === 'bg' && ! empty( $lcp_data['bgUrls'] ) ) {
				$preload_urls[ $device ] = array_merge( $preload_urls[ $device ], $lcp_data['bgUrls'] );
			}
		}
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'BGCSS selectors: ' . print_r( $css_selectors, true ) );
			do_action( 'optml_log', 'BGPreload URLs: ' . print_r( $preload_urls, true ) );
		}

		foreach ( array_intersect( $preload_urls[ Profile::DEVICE_TYPE_MOBILE ], $preload_urls[ Profile::DEVICE_TYPE_DESKTOP ] ) as $url ) {
			Links::add_link( [ 'url' => $url, 'priority' => 'high' ] );
		}

		if ( ! in_array( false, $tainted, true ) ) {
			return null;
		}

		$hide_rule = ' { background-image: none !important; }';
		$mobile_selectors = implode( ',', $css_selectors[ Profile::DEVICE_TYPE_MOBILE ] );
		$desktop_selectors = implode( ',', $css_selectors[ Profile::DEVICE_TYPE_DESKTOP ] );

		if ( $mobile_selectors === $desktop_selectors ) {
			return $mobile_selectors === '' ? '' : $mobile_selectors . $hide_rule;
		}

		// A device without selectors hides nothing on purpose (voided or capped), so the other rule stays in its media query.
		$media_queries = [];
		if ( $mobile_selectors !== '' ) {
			$media_queries[] = '@media (max-width: ' . self::DEVICE_BREAKPOINT . 'px) { ' . $mobile_selectors . $hide_rule . ' }';
		}
		if ( $desktop_selectors !== '' ) {
			$media_queries[] = '@media (min-width: ' . ( self::DEVICE_BREAKPOINT + 1 ) . 'px) { ' . $desktop_selectors . $hide_rule . ' }';
		}
		return implode( ' ', $media_queries );
	}
	/**
	 * Build the background-hide selectors for one device.
	 *
	 * @param array<string, array<string, mixed>> $personalized_selectors Stored bg data: watcher => [ above-fold selector => urls ].
	 * @param array<string, mixed>                $lcp_data               Stored LCP data for the device.
	 * @param array<string, bool>                 $lazyload_selectors     Allowed watcher selectors as keys.
	 *
	 * @return array<int, string>|null Selector strings, or null when a client selector is unsafe (the device rule is voided).
	 */
	private static function collect_device_selectors( array $personalized_selectors, array $lcp_data, array $lazyload_selectors ) {
		$css_selectors = [];
		foreach ( $personalized_selectors as $selector => $above_fold_selectors ) {
			if ( ! isset( $lazyload_selectors[ $selector ] ) ) {
				continue;
			}
			// A full list may have been cut, so hiding the watcher could delay unlisted above-fold elements.
			if ( count( $above_fold_selectors ) >= self::MAX_ABOVE_FOLD_SELECTORS ) {
				continue;
			}
			// One rule per watcher: a separate rule per element would still hide the watcher's other above-fold elements.
			$exemptions = '';
			foreach ( $above_fold_selectors as $above_fold_selector => $bg_urls ) {
				if ( ! self::is_safe_selector( $above_fold_selector ) ) {
					return null;
				}
				$exemptions .= ':not(' . $above_fold_selector . ')';
			}
			$css_selectors[] = 'html ' . strip_tags( $selector ) . $exemptions . ':not(.optml-bg-lazyloaded)';
		}

		// This CSS replaces the generic one, so unlisted watchers keep it unless the watcher list may have been cut.
		if ( count( $personalized_selectors ) < self::MAX_WATCHERS ) {
			foreach ( array_keys( array_diff_key( $lazyload_selectors, $personalized_selectors ) ) as $selector ) {
				$css_selectors[] = 'html ' . strip_tags( $selector ) . ':not(.optml-bg-lazyloaded)';
			}
		}

		$css_selectors = array_unique( $css_selectors );

		if ( isset( $lcp_data['type'] ) && $lcp_data['type'] === 'bg' && ! empty( $lcp_data['bgSelector'] ) ) {
			if ( ! self::is_safe_selector( $lcp_data['bgSelector'] ) ) {
				return null;
			}
			$css_selectors = array_map(
				function ( $selector ) use ( $lcp_data ) {
					return $selector . ':not(' . $lcp_data['bgSelector'] . ')';
				},
				$css_selectors
			);
		}

		return array_values( $css_selectors );
	}
}
