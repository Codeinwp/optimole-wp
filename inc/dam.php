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
	use Optml_Dam_Offload_Utils;
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
	const URL_DAM_FLAG = '/dam:1';
	const IS_EDIT_FLAG = 'om-dam-edit';

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
			add_action( 'wp_enqueue_media', [ $this, 'enqueue_media_scripts' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_page_scripts' ] );
		}

		if ( defined( 'OPTML_DAM_ENDPOINT' ) ) {
			$this->dam_endpoint = constant( 'OPTML_DAM_ENDPOINT' );
		}

		add_filter( 'wp_get_attachment_image_src', [ $this, 'alter_attachment_image_src' ], 10, 4 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'alter_attachment_metadata' ], 10, 2 );
		add_filter( 'image_downsize', [ $this, 'catch_downsize' ], 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', [$this, 'alter_attachment_for_js'], 10, 3 );
		add_filter( 'wp_image_src_get_dimensions', [$this, 'alter_img_tag_w_h'], 10, 4 );
		add_filter( 'get_attached_file', [$this, 'alter_attached_file_response'], 10, 2 );
		add_filter( 'wp_calculate_image_srcset', [$this, 'disable_dam_images_srcset'], 1, 5 );

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
			if ( ! isset( $image['isEdit'] ) && array_key_exists( $image['meta']['resourceS3'], $existing ) ) {
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

		if ( isset( $image['isEdit'] ) ) {
			update_post_meta( $id, self::IS_EDIT_FLAG, true );
		}

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
	 * Alter attachment image src for DAM imported images.
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
		$incoming_size = $this->parse_dimension_from_optimized_url( $image_url );
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

			$image_url = $this->replace_dam_url_args(
				[
					'width'  => $metadata['width'],
					'height' => $metadata['height'],
					'crop'   => false,
				],
				$image_url
			);

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

		return $this->get_altered_metadata_for_remote_images( $metadata, $id );
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
			// Skip edits.
			if ( ! empty( get_post_meta( $attachment->post_id, self::IS_EDIT_FLAG, true ) ) ) {
				continue;
			}

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
			<style>
				.notice:not(.optml-notice-optin){
					display: none !important;
				}
			</style>
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

		$url_args = [
			'height' => $response['height'],
			'width'  => $response['width'],
			'crop'   => false,
		];

		$response['url'] = $this->replace_dam_url_args( $url_args, $response['url'] );

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
		$incoming_size = $this->parse_dimension_from_optimized_url( $image_src );
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
		$args = wp_parse_args( $args, [ 'width' => 'auto', 'height' => 'auto', 'crop' => false, 'dam' => true] );

		$width = $args['width'];
		$height = $args['height'];
		$crop = (bool) $args['crop'];

		$gravity = Optml_Resize::GRAVITY_CENTER;

		if ( $this->settings->get( 'resize_smart' ) === 'enabled' ) {
			$gravity = Optml_Resize::GRAVITY_SMART;
		}

		if ( $width === 0 ) {
			$width = 'auto';
		}

		if ( $height === 0 ) {
			$height = 'auto';
		}

		// Use the proper replacement for the image size.
		$replacement = '/w:' . $width . '/h:' . $height;

		if ( $crop ) {
			$replacement .= '/g:' . $gravity . '/rt:fill';
		}

		$replacement .= '/q:';

		if ( $args['dam'] ) {
			$replacement = self::URL_DAM_FLAG . $replacement;
		}

		return preg_replace( '/\/w:(.*)\/h:(.*)\/q:/', $replacement, $subject );
	}

	/**
	 * Elementor checks if the file exists before requesting a specific image size.
	 *
	 * Needed because otherwise there won't be any width/height on the `img` tags, breaking lazyload.
	 *
	 * Also needed because some
	 *
	 * @param string $file The file path.
	 * @param int    $id The attachment ID.
	 *
	 * @return bool|string
	 */
	public function alter_attached_file_response( $file, $id ) {
		if ( ! $this->is_dam_imported_image( $id ) ) {
			return $file;
		}

		$metadata = wp_get_attachment_metadata( $id );

		if ( isset( $metadata['file'] ) ) {
			$uploads = wp_get_upload_dir();

			return $uploads['basedir'] . '/' . $metadata['file'];
		}

		return true;
	}

	/**
	 * Alter the srcSet for DAM images.
	 *
	 * @param array  $sources Initial source array.
	 * @param array  $size_array Requested size.
	 * @param string $image_src Image source URL.
	 * @param array  $image_meta Image meta data.
	 * @param int    $attachment_id Image attachment ID.
	 *
	 * @return array
	 */
	public function disable_dam_images_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! $this->is_dam_imported_image( $attachment_id ) ) {
			return $sources;
		}

		return [];
	}
}
