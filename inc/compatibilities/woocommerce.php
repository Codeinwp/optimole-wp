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
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		if ( Optml_Main::instance()->admin->settings->is_offload_enabled() && Optml_Main::instance()->admin->settings->use_lazyload() ) {
			add_filter( 'optml_lazyload_early_flags', [ $this, 'add_lazyload_early_flag' ], PHP_INT_MAX, 1 );
		}
		add_filter( 'woocommerce_cart_item_thumbnail', [ Optml_Main::instance()->manager, 'replace_content' ] );
		add_filter( 'woocommerce_store_api_cart_item_images', [ $this, 'replace_store_api_cart_item_images' ] );
	}
	/**
	 * Add ignore lazyload flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_lazyload_early_flag( $flags = [] ) {
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
				++$paged;
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
		return true;
	}

	/**
	 * Filter cart item images url.
	 *
	 * @param array $product_images Product images.
	 *
	 * @return array Filtered images.
	 */
	public function replace_store_api_cart_item_images( $product_images ) {
		if ( empty( $product_images ) ) {
			return $product_images;
		}
		do_action( 'optml_replacer_setup' );
		$product_images = array_map(
			function ( $product_image ) {
				$product_image->thumbnail = Optml_Main::instance()->manager->url_replacer->build_url( $product_image->thumbnail );
				$product_image->src       = $product_image->thumbnail;
				return $product_image;
			},
			$product_images
		);
		return $product_images;
	}
}
