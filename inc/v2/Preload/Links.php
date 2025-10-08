<?php
namespace OptimoleWP\Preload;

/**
 * Class Links
 *
 * @package OptimoleWP\Preload
 */
class Links {
	/**
	 * The maximum number of links to preload.
	 *
	 * @var int $MAX_LINKS The maximum number of links to preload.
	 */
	const MAX_LINKS = 10;
	/**
	 * Links map that contains the url as the key and the url data as the value.
	 *
	 * @var array $links The array of links to preload.
	 */
	private static $links = [];
	/**
	 * Ids map of the images that are preloaded.
	 *
	 * @var array $ids The array of ids to preload.
	 */
	private static $ids = [];
	/**
	 * Add a link to the preload array.
	 *
	 * @param array $url_data The array of url data to add.
	 */
	public static function add_link( array $url_data ) {
		if ( ! isset( $url_data['url'] ) ) {
			return;
		}
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'add preload link : ' . print_r( $url_data, true ) );
		}
		self::$links[ crc32( $url_data['url'] ) ] = $url_data;
	}

	/**
	 * Add an id to the preload array.
	 *
	 * @param int    $id The id to add.
	 * @param string $priority The priority of the id.
	 */
	public static function add_id( int $id, string $priority = 'auto' ) {
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'add preload id : ' . $id . ' ' . $priority );
		}
		self::$ids[ $id ] = $priority;
	}

	/**
	 * Check if an id is preloaded.
	 *
	 * @param int $id The id to check.
	 * @return string|false The priority of the id or false if it is not preloaded.
	 */
	public static function is_preloaded( int $id ) {
		return self::$ids[ $id ] ?? false;
	}

	/**
	 * Preload a tag.
	 *
	 * @param string $tag The tag to preload.
	 * @param string $priority The priority of the tag.
	 */
	public static function preload_tag( string $tag, string $priority = '' ) {
		// Extract src, srcset, and sizes from the tag using regexes for each  one
		$src = '';
		$srcset = '';
		$sizes = '';

		$src_pattern = '/<img[^>]+src=["|\']([^"|\']+)["|\']/i';
		$srcset_pattern = '/<img[^>]+srcset=["|\']([^"|\']+)["|\']/i';
		$sizes_pattern = '/<img[^>]+sizes=["|\']([^"|\']+)["|\']/i';

		if ( preg_match( $src_pattern, $tag, $matches ) ) {
			$src = $matches[1];
		}
		if ( preg_match( $srcset_pattern, $tag, $matches ) ) {
			$srcset = $matches[1];
		}
		if ( preg_match( $sizes_pattern, $tag, $matches ) ) {
			$sizes = $matches[1];
		}
		if ( OPTML_DEBUG ) {
			do_action(
				'optml_log',
				'preload_tag: ' . print_r(
					[
						'url' => $src,
						'srcset' => $srcset,
						'sizes' => $sizes,
						'priority' => $priority,
					],
					true
				) . ' ' . $priority
			);
		}
		// Add the preload link to the links array
		self::add_link(
			[
				'url' => $src,
				'srcset' => $srcset,
				'sizes' => $sizes,
				'priority' => $priority,
			]
		);
	}

	/**
	 * Get the links.
	 *
	 * @return array The links.
	 */
	public static function get_links(): array {
		return self::$links;
	}

	/**
	 * Get the links count.
	 *
	 * @return int The links count.
	 */
	public static function get_links_count(): int {
		return count( self::$links );
	}

	/**
	 * Get the links html.
	 *
	 * @return string The links html.
	 */
	public static function get_links_html(): string {
		// generate image preload links for all links
		$links = [];
		foreach ( self::$links as $link ) {
			$url = esc_url( $link['url'] );
			if ( empty( $url ) ) {
				continue;
			}
			$preload = '<link rel="preload" media="screen" href="' . $url . '" ';
			if ( isset( $link['priority'] ) && $link['priority'] !== 'auto' ) {
				$preload .= 'fetchpriority="' . esc_attr( $link['priority'] ) . '" ';
			}
			// Add imagesrcset if available
			if ( isset( $link['srcset'] ) && ! empty( $link['srcset'] ) ) {
				$preload .= 'imagesrcset="' . esc_attr( $link['srcset'] ) . '" ';
			}
			// Add imagesizes if available
			if ( isset( $link['sizes'] ) && ! empty( $link['sizes'] ) ) {
				$preload .= 'imagesizes="' . esc_attr( $link['sizes'] ) . '" ';
			}
			// Complete the preload tag
			$preload .= 'as="image">';
			if ( count( $links ) >= self::MAX_LINKS ) {
				break;
			}
			$links[] = $preload;
		}
		return implode( "\n", $links ) . "\n";
	}
}
