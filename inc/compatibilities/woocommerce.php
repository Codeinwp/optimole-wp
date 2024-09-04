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
		if ( Optml_Main::instance()->admin->settings->use_lazyload() ) {
			add_filter( 'optml_lazyload_early_flags', [ $this, 'add_lazyload_early_flag' ], PHP_INT_MAX, 1 );
			if ( class_exists( '\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' ) && \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_cart_block_default() && function_exists( 'wc_cart_totals_subtotal_html' ) ) {
				add_filter( 'image_downsize', [ $this, 'filter_cart_item_image' ], PHP_INT_MAX, 3 );
			}
		}
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
		if ( Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled' ) {
			return true;
		}
		return false;
	}

	/**
	 * This filter will replace all the images retrieved via "wp_get_image" type of functions.
	 *
	 * @param array        $image The filtered value.
	 * @param int          $attachment_id The related attachment id.
	 * @param array|string $size This could be the name of the thumbnail size or an array of custom dimensions.
	 *
	 * @return array
	 */
	public function filter_cart_item_image( $image, $attachment_id, $size ) {
		if ( ! function_exists( 'is_cart' ) ) {
			return $image;
		}
		if ( ! is_cart() ) {
			return $image;
		}
		return Optml_Tag_Replacer::instance()->filter_image_downsize( $image, $attachment_id, $size );
	}
}
