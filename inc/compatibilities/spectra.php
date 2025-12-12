<?php

/**
 * Class Optml_spectra
 *
 * @reason Optimizing Block images when saved to a CSS file.
 */
class Optml_spectra extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'uagb_block_attributes_for_css_and_js', [ $this, 'optimize_src' ], PHP_INT_MAX, 2 );

		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.wp-block-uagb-container';

				return $all_watchers;
			}
		);
	}

	/**
	 * Optimize the src attribute.
	 *
	 * @param array  $attrs Array of attributes.
	 * @param string $name Name of the block.
	 *
	 * @return array
	 */
	public function optimize_src( $attrs, $name ) {
		$attrs = $this->iterate_array( $attrs );

		return $attrs;
	}

	/**
	 * Iterate through the array and replace the url.
	 *
	 * @param array $attrs Array of attributes.
	 * @return array
	 */
	public function iterate_array( $attrs ) {
		foreach ( $attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				$attrs[ $key ] = $this->iterate_array( $value );
			}

			if ( is_string( $value ) && preg_match( '/(http|https):\/\/.*\.(?:png|jpg|jpeg|gif|webp)/i', $value ) ) {
				$attrs[ $key ] = Optml_Main::instance()->manager->url_replacer->build_url( $value );
			}
		}

		return $attrs;
	}
}
