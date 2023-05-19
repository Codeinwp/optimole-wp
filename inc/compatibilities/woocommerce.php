<?php

/**
 * Class Optml_woocommerce
 *
 * @reason Adding flags for ignoring the lazyloaded tags.
 */
class Optml_woocommerce extends Optml_compatibility {



	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		if ( Optml_Main::instance()->admin->settings->use_lazyload() ) {
			add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_ignore_lazyload' ], PHP_INT_MAX, 1 );
		}
		add_filter('optml_media_attachment_template_id', [$this, 'add_remote_image'], 10, 2);
		add_filter('admin_post_thumbnail_html', [$this, 'generate_remote_images_thumbnail'], 10, 4);

	}

	public function add_remote_image( $id, $optimized_url ) {
		$remote_ids = get_transient('optml_remote_ids');
		if (empty($remote_ids)) {
			$remote_ids = array();
		}

		$remote_ids[$id] = $optimized_url;
		set_transient('optml_remote_ids', $remote_ids, 5 * MINUTE_IN_SECONDS);

	}
	public function generate_remote_images_thumbnail ( $content, $post_id, $thumbnail_id ) {
		$remote_ids = get_transient('optml_remote_ids');
		if ( ! isset( $remote_ids[$thumbnail_id]  ) ) {
			return $content;
		}

		// we get one existing image attachment to use for generating the thumbnail html
		// we will replace that html with the remote image
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => 1,
			'fields' => 'ids'
		);

		$attachment = get_posts($args);

		if ( empty( $attachment ) || ! isset( $attachment[0] ) ) {
			return $content;
		}
		$attachment_id = $attachment[0];

		$thumbnail_remplate = _wp_post_thumbnail_html( $attachment_id, $post_id );


		//remove the srcset and sizes attributes
		$thumbnail_remplate = preg_replace('/\s+srcset="[^"]*"/i', '', $thumbnail_remplate);
		$thumbnail_remplate = preg_replace('/\s+sizes="[^"]*"/i', '', $thumbnail_remplate);

		//replace the src attribute with the remote image url
		$thumbnail_remplate= preg_replace('/(<img[^>]+) src="[^"]*"/i', '$1 src="' . $remote_ids[$thumbnail_id] . '"', $thumbnail_remplate);
		$thumbnail_remplate= preg_replace('/(<input[^>]+) value="[^"]*"/i', '$1 value="' . $thumbnail_id . '"', $thumbnail_remplate);

		return $thumbnail_remplate;

	}
	/**
	 * Add ignore lazyload flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_ignore_lazyload( $flags = [] ) {
		$flags[] = 'data-large_image';

		return $flags;
	}
	/**
	 * Ads the product pages to the list of posts parents when querying images for offload.
	 *
	 * @param array $parents Default post parents.
	 *
	 * @return array New post parents that include product pages.
	 */
	public function add_product_pages_to_image_query( $parents = [ 0 ] ) {
		$paged = 1;
		$query_args = [
			'post_type' => 'product',
			'fields'   => 'ids',
			'posts_per_page' => 150,
			'post_status' => 'publish',
			'paged' => $paged,
		];
		$products = new \WP_Query( $query_args );
		$ids = $products->get_posts();
		while ( ! empty( $ids ) ) {
				$paged++;
				$parents = array_merge( $parents, $ids );
				$query_args['paged'] = $paged;
				$products = new \WP_Query( $query_args );
				$ids = $products->get_posts();
		}

		return $parents;
	}
	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		if ( Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled' ||
			Optml_Main::instance()->admin->settings->get( 'cloud_images' ) === 'enabled') {
			return true;
		}
		return false;
	}
}
