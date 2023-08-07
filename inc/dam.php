<?php
/**
 * Dam class.
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      04/07/2023
 *
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Dam
 */
class Optml_Dam {
	use Optml_Normalizer;

	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	private $settings;

	/**
	 * The dam endpoint.
	 *
	 * @var string
	 */
	private $dam_endpoint = 'https://dashboard.optimole.com/dam';

	const OM_DAM_IMPORTED_FLAG = 'om-dam-imported';

	/**
	 * Optml_Dam constructor.
	 */
	public function __construct() {
		$this->settings = Optml_Main::instance()->admin->settings;

		if ( ! $this->settings->is_connected() ) {
			return;
		}

		if ( $this->settings->get( 'cloud_images' ) === 'enabled' ) {
			add_action( 'admin_menu', [ $this, 'add_menu' ] );
			add_action( 'print_media_templates', [ $this, 'print_media_template' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_media_scripts' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_page_scripts' ] );

			// Needed for this to work with elementor.
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_media_scripts' ] );
		}

		if ( defined( 'OPTML_DAM_ENDPOINT' ) && constant( 'OPTML_DAM_ENDPOINT' ) ) {
			$this->dam_endpoint = constant( 'OPTML_DAM_ENDPOINT' );
		}

		add_filter( 'wp_get_attachment_image_src', [ $this, 'alter_attachment_image_src' ], 10, 4 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'alter_attachment_metadata' ], 10, 2 );
		add_filter( 'image_downsize', [ $this, 'catch_downsize' ], 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', [$this, 'alter_attachment_for_js'], 10, 3 );
		add_filter( 'wp_image_src_get_dimensions', [$this, 'alter_img_tag_w_h'], 10, 4 );
		add_filter(
			'elementor/image_size/get_attachment_image_html',
			[
				$this,
				'alter_elementor_image_size',
			],
			10,
			4
		);
	}

	/**
	 * Catch image downsize for the DAM imported images.
	 *
	 * @param array        $image {
	 *                        Array of image data.
	 *
	 * @type string $0 Image source URL.
	 * @type int    $1 Image width in pixels.
	 * @type int    $2 Image height in pixels.
	 * @type bool   $3 Whether the image is a resized image.
	 *
	 * @param int          $id attachment id.
	 * @param string|int[] $size image size.
	 *
	 * @return array $image.
	 */
	public function catch_downsize( $image, $id, $size ) {
		return $this->alter_attachment_image_src( $image, $id, $size, false );
	}

	/**
	 * Insert attachments.
	 *
	 * @param array $images Images array.
	 *
	 * @return array
	 */
	public function insert_attachments( $images ) {
		$ids = [];

		$existing = $this->check_existing_attachments( $images );

		foreach ( $images as $image ) {
			if ( array_key_exists( $image['meta']['resourceS3'], $existing ) ) {
				$ids[] = $existing[ $image['meta']['resourceS3'] ];

				continue;
			}

			$id = $this->insert_attachment( $image );

			if ( $id === 0 ) {
				continue;
			}
			$ids[] = $id;
		}

		return $ids;
	}

	/**
	 * Insert single attachment
	 *
	 * @param array $image Image data.
	 *
	 * @return int
	 */
	private function insert_attachment( $image ) {
		$filename = basename( $image['url'] );
		$name     = pathinfo( $filename, PATHINFO_FILENAME );

		$args = [
			'post_title'     => $name,
			'post_type'      => 'attachment',
			'post_mime_type' => $image['meta']['mimeType'],
			'guid'           => $image['url'],
		];

		$id = wp_insert_attachment( $args );

		if ( $id === 0 ) {
			return $id;
		}

		update_post_meta( $id, self::OM_DAM_IMPORTED_FLAG, $image['meta']['resourceS3'] );

		$metadata = [];

		$metadata['file']      = '/id:' . $image['meta']['resourceS3'] . '/' . get_home_url() . '/' . $filename;
		$metadata['mime-type'] = $image['meta']['mimeType'];

		if ( isset( $image['meta']['filesize'] ) ) {
			$metadata['filesize'] = $image['meta']['fileSize'];
		}

		if ( isset( $image['meta']['originalWidth'] ) && isset( $image['meta']['originalHeight'] ) ) {
			$metadata['width']  = $image['meta']['originalWidth'];
			$metadata['height'] = $image['meta']['originalHeight'];
		}

		wp_update_attachment_metadata( $id, $metadata );

		return $id;
	}

	/**
	 * Catch image downsize for the DAM imported images.
	 *
	 * @param array|false  $image {
	 *      Array of image data.
	 *
	 * @type string $0 Image source URL.
	 * @type int    $1 Image width in pixels.
	 * @type int    $2 Image height in pixels.
	 * @type bool   $3 Whether the image is a resized image.
	 * }
	 *
	 * @param int          $attachment_id attachment id.
	 * @param string|int[] $size image size.
	 * @param bool         $icon Whether the image should be treated as an icon.
	 *
	 * @return array $image.
	 */
	public function alter_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		// Skip if not DAM image.
		if ( ! $this->is_dam_imported_image( $attachment_id ) ) {
			return $image;
		}

		$image_url     = wp_get_attachment_url( $attachment_id );
		$incoming_size = Optml_Media_Offload::parse_dimension_from_optimized_url( $image_url );
		$width         = $incoming_size[0];
		$height        = $incoming_size[1];

		// Skip resize in single attachment view on backend.
		if ( $this->is_attachment_edit_page( $attachment_id ) ) {
			return [
				$image_url,
				$width,
				$height,
				false,
			];
		}

		// Use the original size if the requested size is full.
		if ( $size === 'full' ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );

			return [
				$image_url,
				$metadata['width'],
				$metadata['height'],
				false,
			];
		}

		$crop = false;

		// Size can be int [] containing width and height.
		if ( is_array( $size ) ) {
			$width  = $size[0];
			$height = $size[1];
			$crop = true;
		} else {
			$sizes = $this->get_all_image_sizes();

			if ( ! isset( $sizes[ $size ] ) ) {
				return [
					$image_url,
					$width,
					$height,
					false,
				];
			}

			$width  = $sizes[ $size ]['width'];
			$height = $sizes[ $size ]['height'];
			$crop   = (bool) $sizes[ $size ]['crop'];
		}

		$image_url = $this->replace_dam_url_args(
			[
				'width'  => $width,
				'height' => $height,
				'crop'   => $crop,
			],
			$image_url
		);

		return [
			$image_url,
			$width,
			$height,
			$crop,
		];
	}

	/**
	 * Get all registered image sizes.
	 *
	 * @return array
	 */
	private function get_all_image_sizes() {
		$additional_sizes = wp_get_additional_image_sizes();
		$intermediate     = get_intermediate_image_sizes();
		$all              = [];

		foreach ( $intermediate as $size ) {
			if ( isset( $additional_sizes[ $size ] ) ) {
				$all[ $size ] = [
					'width'  => $additional_sizes[ $size ]['width'],
					'height' => $additional_sizes[ $size ]['height'],
					'crop'   => isset( $additional_sizes[ $size ]['crop'] ) ? $additional_sizes[ $size ]['crop'] : false,
				];
			} else {
				$all[ $size ] = [
					'width'  => (int) get_option( $size . '_size_w' ),
					'height' => (int) get_option( $size . '_size_h' ),
					'crop'   => (bool) get_option( $size . '_crop' ),
				];
			}

			if ( ! empty( $additional_sizes[ $size ]['crop'] ) ) {
				$all[ $size ]['crop'] = $additional_sizes[ $size ]['crop'];
			} else {
				$all[ $size ]['crop'] = (bool) get_option( $size . '_crop' );
			}
		}

		return $all;
	}

	/**
	 * Check if we're in the attachment edit page.
	 *
	 * /wp-admin/post.php?post=<id>&action=edit
	 *
	 * Send whatever comes from the DAM.
	 *
	 * @param int $attachment_id attachment id.
	 *
	 * @return bool
	 */
	private function is_attachment_edit_page( $attachment_id ) {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! isset( $screen->base ) ) {
			return false;
		}

		if ( $screen->base !== 'post' ) {
			return false;
		}

		if ( $screen->post_type !== 'attachment' ) {
			return false;
		}

		if ( $screen->id !== 'attachment' ) {
			return false;
		}

		if ( ! isset( $_GET['post'] ) ) {
			return false;
		}

		if ( (int) sanitize_text_field( $_GET['post'] ) !== $attachment_id ) {
			return false;
		}

		return true;
	}

	/**
	 * Alter the attachment metadata.
	 *
	 * @param array $metadata attachment metadata.
	 * @param int   $id attachment ID.
	 *
	 * @return array
	 */
	public function alter_attachment_metadata( $metadata, $id ) {
		if ( ! $this->is_dam_imported_image( $id ) ) {
			return $metadata;
		}

		$sizes = $this->get_all_image_sizes();

		$post = get_post( $id );

		$sizes_meta = [];

		// SVG files don't have a width/height so we add a dummy one. These are vector images so it doesn't matter.
		$is_svg = ( $post->post_mime_type === Optml_Config::$image_extensions['svg'] );

		if ( $is_svg ) {
			$metadata['width']  = 150;
			$metadata['height'] = 150;
		}

		foreach ( $sizes as $size => $args ) {

			// check if the image is portrait or landscape using attachment metadata.
			$is_portrait = $metadata['height'] > $metadata['width'];

			// proportionally set the width/height based on this if image is uncropped.
			if ( ! (bool) $args['crop'] ) {
				if ( $is_portrait ) {
					$args['width'] = (int) ( $args['height'] * round( $metadata['width'] / $metadata['height'] ) );
				} else {
					$args['height'] = (int) ( $args['width'] * round( $metadata['height'] / $metadata['width'] ) );
				}
			}

			$sizes_meta[ $size ] = [
				'file'      => $metadata['file'],
				'width'     => $args['width'],
				'height'    => $args['height'],
				'mime-type' => $post->post_mime_type,
			];
		}

		$metadata['sizes'] = $sizes_meta;

		return $metadata;
	}

	/**
	 * Check if the images are already imported.
	 *
	 * @param array $images List of images to check.
	 *
	 * @return array List of images that are already imported.
	 */
	private function check_existing_attachments( $images ) {
		$already_imported = $this->get_dam_imported_attachments( $images );

		// All DAM imports are already in the DB.
		if ( count( $already_imported ) === count( $images ) ) {
			return $already_imported;
		}

		// Get the remaining images.
		$remaining = array_filter(
			$images,
			function ( $image ) use ( $already_imported ) {
				return ! array_key_exists( $image['meta']['resourceS3'], $already_imported );
			}
		);

		// Offloaded images.
		if ( $this->settings->get( 'offload_media' ) === 'enabled' ) {
			$offloaded = $this->get_offloaded_attachments( $remaining );

			$already_imported = array_merge( $already_imported, $offloaded );
		}

		return $already_imported;
	}

	/**
	 * Check if the images are already imported.
	 *
	 * @param array $images List of images to check.
	 *
	 * @return array
	 */
	private function get_dam_imported_attachments( $images ) {
		global $wpdb;

		$s3_ids = [];

		foreach ( $images as $image ) {
			$s3_ids[] = esc_sql( strval( $image['meta']['resourceS3'] ) );
		}

		$meta_values_str = "'" . join( "', '", $s3_ids ) . "'";

		// Select all the posts that have the flag and are in the list of images.
		$found_attachments = $wpdb->get_results(
			$wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This query cannot use interpolation.
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value IN ( {$meta_values_str} )",
				self::OM_DAM_IMPORTED_FLAG
			)
		);

		if ( empty( $found_attachments ) ) {
			return [];
		}

		$map = [];

		// Remap this in a key/value array.
		// Also ensures that if there are multiple attachments with the same S3 ID, we only get the one.
		// Shouldn't happen, but just in case.
		foreach ( $found_attachments as $attachment ) {
			$map[ $attachment->meta_value ] = (int) $attachment->post_id;
		}

		return $map;
	}

	/**
	 * Get the offloaded attachments.
	 *
	 * [ S3 ID => Attachment Post ID ]
	 *
	 * @param array $images List of images to check.
	 *
	 * @return array
	 */
	private function get_offloaded_attachments( $images ) {
		global $wpdb;

		$map = [];

		foreach ( $images as $image ) {
			$like = '%id:' . $image['meta']['resourceS3'] . '%';

			$found_attachments = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
					'_wp_attachment_metadata',
					$like
				)
			);

			if ( empty( $found_attachments ) ) {
				return [];
			}

			$map[ $image['meta']['resourceS3'] ] = (int) $found_attachments[0]->post_id;
		}

		return $map;
	}

	/**
	 * Checks that the attachment is a DAM image.
	 *
	 * @param int $post_id The attachment ID.
	 *
	 * @return bool
	 */
	private function is_dam_imported_image( $post_id ) {
		$meta = get_post_meta( $post_id, self::OM_DAM_IMPORTED_FLAG, true );

		if ( empty( $meta ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Adds menu item for DAM.
	 *
	 * @return void
	 */
	public function add_menu() {
		if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
			return;
		}

		add_submenu_page(
			'optimole',
			__( 'Cloud Library', 'optimole-wp' ),
			__( 'Cloud Library', 'optimole-wp' ),
			'manage_options',
			'optimole-dam',
			[ $this, 'render_dashboard_page' ]
		);
	}

	/**
	 * Add media template to be used in the media library.
	 *
	 * @return void
	 */
	public function print_media_template() {
		?>
		<script type="text/html" id="tmpl-optimole-dam">
			<?php $this->render_dashboard_page(); ?>
		</script>
		<?php
	}

	/**
	 * Render the dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		?>
		<iframe id="om-dam" style="display: none;" src="<?php echo( $this->build_iframe_url() ); ?>"></iframe>
		<div class="om-dam-loader">
			<img src="<?php echo esc_url( OPTML_URL . 'assets/img/logo.png' ); ?>" alt="Optimole Logo"
				 class="om-dam-logo">
			<p><?php echo esc_html__( 'Loading', 'optimole-wp' ); ?>...</p>
		</div>
		<?php
	}

	/**
	 * Build the iFrame URL.
	 *
	 * @return string
	 */
	public function build_iframe_url() {
		$api_key         = $this->settings->get( 'api_key' );
		$connected_sites = $this->settings->get( 'cloud_sites' );

		if ( empty( $api_key ) ) {
			return '';
		}

		if ( isset( $connected_sites['all'] ) && $connected_sites['all'] === 'true' ) {
			$connected_sites = [];
		} else {
			foreach ( $connected_sites as $site => $status ) {
				if ( $status !== 'true' ) {
					unset( $connected_sites[ $site ] );
				}
			}
		}

		$data = [
			'site'  => get_site_url(),
			'token' => $api_key,
			'sites' => array_keys( $connected_sites ),
		];

		$data = json_encode( $data );
		$data = rtrim( base64_encode( $data ), '=' );

		return add_query_arg(
			[
				'data' => $data,
			],
			$this->dam_endpoint
		);
	}

	/**
	 * Enqueue script for generating cloud media tab.
	 *
	 * @return void
	 */
	public function enqueue_media_scripts() {
		$asset_file = include OPTML_PATH . 'assets/build/media/media-modal.asset.php';

		wp_register_script(
			OPTML_NAMESPACE . '-media-modal',
			OPTML_URL . 'assets/build/media/media-modal.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script( OPTML_NAMESPACE . '-media-modal', 'optmlMediaModal', $this->get_localized_vars() );
		wp_enqueue_script( OPTML_NAMESPACE . '-media-modal' );
		wp_enqueue_style( OPTML_NAMESPACE . '-media-modal', OPTML_URL . 'assets/build/media/media-modal.css' );
	}

	/**
	 * Enqueue script for generating admin page.
	 *
	 * @return void
	 */
	public function enqueue_admin_page_scripts() {
		$screen = get_current_screen();

		if ( $screen->id !== 'optimole_page_optimole-dam' ) {
			return;
		}

		$asset_file = include OPTML_PATH . 'assets/build/media/admin-page.asset.php';

		wp_register_script(
			OPTML_NAMESPACE . '-admin-page',
			OPTML_URL . 'assets/build/media/admin-page.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script(
			OPTML_NAMESPACE . '-admin-page',
			'optmlAdminPage',
			[
				'siteUrl' => get_site_url(),
			]
		);

		wp_enqueue_script( OPTML_NAMESPACE . '-admin-page' );

		wp_enqueue_style( OPTML_NAMESPACE . '-admin-page', OPTML_URL . 'assets/build/media/admin-page.css' );
	}

	/**
	 * Get localized variables for the media modal.
	 *
	 * @return array
	 */
	private function get_localized_vars() {
		$routes = array_keys( Optml_Rest::$rest_routes['dam_routes'] );

		foreach ( $routes as $route ) {
			$routes[ $route ] = OPTML_NAMESPACE . '/v1/' . $route;
		}

		return [
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'routes' => $routes,
		];
	}

	/**
	 * Alter the image size for the image widget.
	 *
	 * @param string $html the attachment image HTML string.
	 * @param array  $settings       Control settings.
	 * @param string $image_size_key Optional. Settings key for image size.
	 *                               Default is `image`.
	 * @param string $image_key      Optional. Settings key for image. Default
	 *                               is null. If not defined uses image size key
	 *                               as the image key.
	 *
	 * @return string
	 */
	public function alter_elementor_image_size( $html, $settings, $image_size_key, $image_key ) {
		if ( ! isset( $settings['image'] ) ) {
			return $html;
		}

		$image = $settings['image'];

		if ( ! isset( $image['id'] ) ) {
			return $html;
		}

		if ( ! $this->is_dam_imported_image( $image['id'] ) ) {
			return $html;
		}

		if ( ! isset( $settings['image_size'] ) ) {
			return $html;
		}

		if ( $settings['image_size'] === 'custom' ) {
			if ( ! isset( $settings['image_custom_dimension'] ) ) {
				return $html;
			}

			$custom_dimensions = $settings['image_custom_dimension'];

			if ( ! isset( $custom_dimensions['width'] ) || ! isset( $custom_dimensions['height'] ) ) {
				return $html;
			}

			return $this->replace_dam_url_args( $custom_dimensions, $html );
		}

		$all_sizes = $this->get_all_image_sizes();

		if ( ! isset( $all_sizes[ $settings['image_size'] ] ) ) {
			return $html;
		}

		return $this->replace_dam_url_args( $all_sizes[ $settings['image_size'] ], $html );
	}

	/**
	 * Needed as some blocks might use the image sizes.
	 *
	 * @param array       $response Array of prepared attachment data. @see wp_prepare_attachment_for_js().
	 * @param WP_Post     $attachment Attachment object.
	 * @param array|false $meta Array of attachment meta data, or false if there is none.
	 *
	 * @return array
	 */
	public function alter_attachment_for_js( $response, $attachment, $meta ) {
		if ( ! $this->is_dam_imported_image( $attachment->ID ) ) {
			return $response;
		}

		$sizes = $this->get_all_image_sizes();

		foreach ( $sizes as $size => $args ) {
			if ( isset( $response['sizes'][ $size ] ) ) {
				continue;
			}

			$args = [
				'height'      => $args['height'],
				'width'       => $args['width'],
				'crop'        => true,
			];

			$response['sizes'][ $size ] = array_merge(
				$args,
				[
					'url'         => $this->replace_dam_url_args( $args, $response['url'] ),
					'orientation' => ( $args['height'] > $args['width'] ) ? 'portrait' : 'landscape',
				]
			);
		}

		return $response;
	}

	/**
	 * We have to short-circuit the logic that adds width and height to the img tag.
	 * It compares the URL basename, and the `file` param for each image.
	 * This happens for any image that gets its size set non-explicitly
	 * e.g. an image block with its size set from the sidebar to `thumbnail`).
	 *
	 * Optimole has a single basename for all image resizes in its URL.
	 *
	 * @param array|false $dimensions    Array with first element being the width
	 *                                   and second element being the height, or
	 *                                   false if dimensions could not be determined.
	 * @param string      $image_src     The image URL (will be Optimole URL).
	 * @param array       $image_meta    The image metadata.
	 * @param int         $attachment_id The image attachment ID. Default 0.
	 */
	public function alter_img_tag_w_h( $dimensions, $image_src, $image_meta, $attachment_id ) {
		if ( ! $this->is_dam_imported_image( $attachment_id ) ) {
			return $dimensions;
		}

		// Get the dimensions from the optimized URL.
		$incoming_size = Optml_Media_Offload::parse_dimension_from_optimized_url( $image_src );
		$width         = $incoming_size[0];
		$height        = $incoming_size[1];

		$sizes = $this->get_all_image_sizes();

		// If this is an image size. Return its dimensions.
		foreach ( $sizes as $size => $args ) {
			if ( (int) $args['width'] !== (int) $width ) {
				continue;
			}

			if ( (int) $args['height'] !== (int) $height ) {
				continue;
			}

			return [
				$args['width'],
				$args['height'],
			];
		}

		// Fall-through with the original dimensions.
		return $dimensions;
	}

	/**
	 * Replace the image size params in DAM URLs inside a string.
	 *
	 * @param array  $args   The arguments to replace.
	 *                       - width: The width of the image.
	 *                       - height: The height of the image.
	 *                       - crop: Whether to crop the image.
	 * @param string $subject The string to replace the arguments in.
	 *
	 * @return string
	 */
	public function replace_dam_url_args( $args, $subject ) {
		$args = wp_parse_args( $args, [ 'width' => 'auto', 'height' => 'auto', 'crop' => false] );

		$width = $args['width'];
		$height = $args['height'];
		$crop = (bool) $args['crop'];

		$gravity = 'ce';

		if ( $this->settings->get( 'resize_smart' ) === 'enabled' ) {
			$gravity = 'sm';
		}

		// Use the proper replacement for the image size.
		$replacement = '/w:' . $width . '/h:' . $height;

		if ( $crop ) {
			$replacement .= '/g:' . $gravity . '/rt:fill';
		}

		$replacement .= '/q:';

		return preg_replace( '/\/w:(.*)\/h:(.*)\/q:/', $replacement, $subject );
	}
}
