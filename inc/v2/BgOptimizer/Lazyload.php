<?php

namespace OptimoleWP\BgOptimizer;

use OptimoleWP\PageProfiler\Profile;
use OptimoleWP\Preload\Links;
use Optml_Lazyload_Replacer;

/**
 * Class Lazyload
 *
 * @package OptimoleWP\BgOptimizer
 */
class Lazyload {
	const MARKER = '/* OPTML_VIEWPORT_BG_SELECTORS */';
	/**
	 * Shape of a plain selector the frontend getUniqueSelector() reports: ids, tags, classes, ' > ' and :nth-of-type(N).
	 */
	const SAFE_SELECTOR_PATTERN = '/^(?:[\w\-#. >\x{00A0}-\x{10FFFF}]|:nth-of-type\(\d+\))+$/u';

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
	 * Get the current personalized CSS for lazy loading.
	 *
	 * @return string The personalized CSS.
	 */
	public static function get_current_personalized_css() {
		return self::get_personalized_css( Profile::get_current_profile_data() );
	}
	/**
	 * Get personalized CSS based on profile data.
	 *
	 * @param array $data Profile data.
	 *
	 * @return string The personalized CSS.
	 */
	public static function get_personalized_css( $data ) {
		$lazyload_selectors = array_values( Optml_Lazyload_Replacer::get_background_lazyload_selectors() );
		$lazyload_selectors = array_fill_keys( $lazyload_selectors, true );
		$css_selectors = [];
		$preload_urls = [];
		foreach ( Profile::get_active_devices() as $device ) {
			$personalized_selectors = $data[ $device ]['bg'] ?? [];
			$lcp_data = $data[ $device ]['lcp'] ?? [];
			if ( OPTML_DEBUG ) {
				do_action( 'optml_log', 'personalized_selectors: ' . $device . ' ' . print_r( $personalized_selectors, true ) );
				do_action( 'optml_log', 'LCP data: ' . $device . ' ' . print_r( $lcp_data, true ) );
			}
			$css_selectors[ $device ] = [];
			// Discard the whole rule if a selector is unsafe.
			$device_tainted = false;
			foreach ( $personalized_selectors as $selector => $above_fold_selectors ) {
				if ( ! isset( $lazyload_selectors[ $selector ] ) ) {
					continue;
				}
				if ( empty( $above_fold_selectors ) ) {
					$css_selectors[ $device ][] = 'html ' . strip_tags( $selector ) . ':not(.optml-bg-lazyloaded)';
				} else {
					foreach ( $above_fold_selectors as $above_fold_selector => $bg_urls ) {
						if ( ! self::is_safe_selector( $above_fold_selector ) ) {
							$device_tainted = true;
							break 2;
						}
						$css_selectors[ $device ][] = 'html ' . strip_tags( $selector ) . ':not(' . $above_fold_selector . '):not(.optml-bg-lazyloaded)';
					}
				}
			}

			$preload_urls[ $device ] = [];
			$css_selectors[ $device ] = array_unique( $css_selectors[ $device ] );
			if ( isset( $lcp_data['type'] ) && $lcp_data['type'] === 'bg' ) {
				if ( ! empty( $lcp_data['bgSelector'] ) ) {
					if ( self::is_safe_selector( $lcp_data['bgSelector'] ) ) {
						$css_selectors[ $device ] = array_map(
							function ( $selector ) use ( $lcp_data ) {
								return $selector . ':not(' . $lcp_data['bgSelector'] . ')';
							},
							$css_selectors[ $device ]
						);
					} else {
						$device_tainted = true;
					}
				}
				if ( ! empty( $lcp_data['bgUrls'] ) ) {
					$preload_urls[ $device ] = array_merge( $preload_urls[ $device ], $lcp_data['bgUrls'] );
				}
			}

			if ( $device_tainted ) {
				$css_selectors[ $device ] = [];
			}
		}
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'BGCSS selectors: ' . print_r( $css_selectors, true ) );
			do_action( 'optml_log', 'BGPreload URLs: ' . print_r( $preload_urls, true ) );
		}

		foreach ( array_intersect( $preload_urls[ Profile::DEVICE_TYPE_MOBILE ], $preload_urls[ Profile::DEVICE_TYPE_DESKTOP ] ) as $url ) {
			Links::add_link( [ 'url' => $url, 'priority' => 'high' ] );
		}

		$hide_rule = ' { background-image: none !important; }';
		$mobile_selectors = implode( ',', $css_selectors[ Profile::DEVICE_TYPE_MOBILE ] );
		$desktop_selectors = implode( ',', $css_selectors[ Profile::DEVICE_TYPE_DESKTOP ] );

		if ( $mobile_selectors === $desktop_selectors ) {
				return empty( $mobile_selectors ) ? '' : $mobile_selectors . $hide_rule;
		}
		// if any of those are empty, return the other one
		if ( empty( $mobile_selectors ) ) {
				return $desktop_selectors . $hide_rule;
		}
		if ( empty( $desktop_selectors ) ) {
				return $mobile_selectors . $hide_rule;
		}

		// generate media query for desktop and mobile
		$media_query = '@media (max-width: 600px) { ' . $mobile_selectors . $hide_rule . ' } @media (min-width: 600px) { ' . $desktop_selectors . $hide_rule . ' }';
		return $media_query;
	}
}
