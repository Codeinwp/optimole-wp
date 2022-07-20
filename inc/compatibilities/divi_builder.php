<?php

/**
 * Class Optml_divi_builder
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_divi_builder extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		return (
			strcmp( wp_get_theme(), 'Divi' ) === 0 ||
			is_plugin_active( 'divi-builder/divi-builder.php' )
		);
	}

	/**
	 * Register integration details.
	 */
	public function register() {

		add_action( 'et_core_static_file_created', [$this, 'optimize_divi_static_files'] );

		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.et_pb_slides > .et_pb_slide';
				$all_watchers[] = '.et_parallax_bg';
				$all_watchers[] = '.et_pb_video_overlay';
				$all_watchers[] = '.et_pb_module:not(.et_pb_blog_grid_wrapper)';
				$all_watchers[] = '.et_pb_row';
				$all_watchers[] = '.et_pb_section.et_pb_section_1';
				$all_watchers[] = '.et_pb_with_background';
				return $all_watchers;
			}
		);
	}
	public function optimize_divi_static_files( $resource ) {
		if ( class_exists( 'ET_Core_PageResource' ) && null !== ET_Core_PageResource::$wpfs ) {
			if ( isset( $resource->path ) ) {
				$data = $resource->get_data( 'file' );

				if ( ! empty( $data ) ) {
					$data = Optml_Main::instance()->manager->replace_content( $data );
					ET_Core_PageResource::$wpfs->put_contents( $resource->path, $data, 0644 );
				}
			}
		}
	}
}
