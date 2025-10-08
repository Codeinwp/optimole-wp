<?php

namespace OptimoleWP\PageProfiler;

use OptimoleWP\Preload\Links;
use Optml_Lazyload_Replacer;

/**
 * Class Profile
 *
 * Handles page profiling functionality for Optimole, including storage and retrieval
 * of above-fold image data for different device types.
 *
 * @package OptimoleWP\PageProfiler
 */
class Profile {

	/**
	 * Placeholder used to identify where a profile ID should be inserted.
	 */
	const PLACEHOLDER = '###pageprofileid###';

	/**
	 * Placeholder used to identify where a profile HMAC should be inserted.
	 */
	const PLACEHOLDER_HMAC = '###profilehmac###';
	/**
	 * Placeholder used to identify where a profile time should be inserted.
	 */
	const PLACEHOLDER_TIME = '###profiletime###';

	/**
	 * Placeholder used to indicate a missing profile ID.
	 */
	const PLACEHOLDER_MISSING = '###pageprofileidmissing###';

	/**
	 * Placeholder used to identify where a profile request url should be inserted.
	 */
	const PLACEHOLDER_URL = '###pageprofileurl###';

	/**
	 * Device type constant for mobile devices.
	 */
	const DEVICE_TYPE_MOBILE = 1;

	/**
	 * Device type constant for desktop devices.
	 *
	 * @var int
	 */
	const DEVICE_TYPE_DESKTOP = 2;

	/**
	 * Device type constant for global devices.
	 *
	 * @var int
	 */
	const DEVICE_TYPE_GLOBAL = -1;

	/**
	 * Stores the current profile ID being processed.
	 *
	 * @var string|null
	 */
	private static $current_profile_id = null;

	/**
	 * Stores the current profile data for all device types.
	 *
	 * @var array
	 */
	private static $current_profile_data = [];

	/**
	 * The storage handler instance.
	 *
	 * @var Storage\Base
	 */
	private $storage;

	/**
	 * Constructor.
	 *
	 * Initializes the storage handler based on the provided filter.
	 *
	 * @throws \Exception If an invalid storage class is provided.
	 */
	public function __construct() {
		/**
		 * Filter the storage class.
		 * Allows to change the storage class to a different one, i.e a database storage class/file storage class etc.
		 *
		 * @param string $storage_class The storage class.
		 *
		 * @return string The storage class.
		 */
		$storage_class = apply_filters( 'optml_page_profiler_storage', wp_using_ext_object_cache() ? Storage\ObjectCache::class : Storage\Transients::class );
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'storage_class: ' . $storage_class );
		}
		if ( ! is_subclass_of( $storage_class, Storage\Base::class ) ) {
			throw new \Exception( 'Invalid storage class' );
		}
		$this->storage = new $storage_class();
	}

	/**
	 * Generate a unique ID for the page profile
	 *
	 * @param string $content The content of the page.
	 *
	 * @return string New id
	 */
	public static function generate_id( string $content = '' ): string {
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'Generating page profile ID: ' . $content . ' ' . ( $_SERVER['REQUEST_URI'] ?? '' ) );
		}

		/**
		 * Filter the page profile ID. This can be altered to change to logic differently, i.e generate the id based on the url or query args or other parameters.
		 *
		 * @param string $id The page profile ID.
		 * @param string $content The content of the page.
		 *
		 * @return string New id
		 */
		return apply_filters( 'optml_page_profile_id', self::get_default_id( $content ), $content );
	}

	/**
	 * Generate a default ID for the page profile
	 *
	 * @param string $content The content of the page.
	 *
	 * @return string New id
	 */
	public static function get_default_id( string $content ): string {
		global $post;
		global $wp_query;
		$page_id = serialize( get_theme_mods() ) .
					get_queried_object_id() .
					( get_queried_object() ? get_class( get_queried_object() ) : '' ) .
					( $post->post_modified ?? '' ) .
					( serialize( $wp_query->posts ?? '' ) );
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'Default page profile ID: ' . $page_id . '|' . sha1( $page_id ) );
		}
		return sha1(
			$page_id
		);
	}

	/**
	 * Stores above-fold image data for a specific profile ID and device type.
	 *
	 * @param string                                                                              $id The profile ID.
	 * @param int                                                                                 $device_type The device type constant.
	 * @param array<string>                                                                       $above_fold_images Array of above-fold images.
	 * @param array<string, array<string, array<int, string>>>                                    $af_bg_selectors Array of above-fold background selectors.
	 *                                        Array structure:
	 *                                        [
	 *                                            'css_selector' => [
	 *                                                'above_the_fold_selector' => [
	 *                                                    0 => 'background_image_url',
	 *                                                    1 => 'background_image_url',
	 *                                                    ...
	 *                                                ],
	 *                                                ...
	 *                                            ],
	 *                                            ...
	 *                                        ] Array of above-fold background selectors.
	 * @param array{imageId?: string, bgSelector?: string, bgUrls?: array<string>, type?: string} $lcp_data LCP (Largest Contentful Paint) data.
	 *                                                                   where 'imageId' is the element identifier,
	 *                                                                   'bgSelector' is the selector,
	 *                                                                   'bgUrls' is an array of URLs
	 *                                                                   'type' is the type of the LCP element.
	 * @param array<int, array{w: int, h: int}>                                                   $missing_dimensions Array of missing dimensions.
	 * @param array<int, array<int, array{w: int, h: int, d: int, s: string, b: int}>>            $missing_srcsets Array of missing srcsets.
	 * @param array<int, bool>                                                                    $crop_status Array of crop status for images.
	 * @return void
	 */
	public function store( string $id, int $device_type, array $above_fold_images, $af_bg_selectors = [], $lcp_data = [], $missing_dimensions = [], $missing_srcsets = [], $crop_status = [] ) {
		if ( ! in_array( (int) $device_type, self::get_active_devices(), true ) ) {
			return;
		}

		// store $above_fold_images as image_id => true to faster access.
		$above_fold_images = array_fill_keys( $above_fold_images, true );
		// Store missing dimensions only from desktop device to avoid using mobile dimensions on desktop.
		// Mobile will rely on srcset for proper dimensions.
		if ( $device_type === self::DEVICE_TYPE_DESKTOP ) {
			$global_data = [];
			if ( ! empty( $missing_srcsets ) ) {
				$global_data['s'] = $missing_srcsets;
			}
			if ( ! empty( $missing_dimensions ) ) {
				$global_data['m'] = $missing_dimensions;
			}
			if ( ! empty( $crop_status ) ) {
				$global_data['c'] = $crop_status;
			}
			if ( ! empty( $global_data ) ) {
				// those measurements are not device specific, so we store them in on a global profile scope.
				$this->storage->store(
					$id,
					$global_data
				);
			}
		}
		$this->storage->store(
			$id . '_' . $device_type,
			[
				'af'  => $above_fold_images,
				'bg'  => $af_bg_selectors,
				'lcp' => $lcp_data,
			]
		);
	}

	/**
	 * Gets the missing dimensions for a specific profile ID.
	 *
	 * @param int $image_id The image ID to get the missing dimensions for.
	 *
	 * @return array{w: int, h: int}|array{} The missing dimensions.
	 */
	public function get_missing_dimensions( int $image_id ): array {
		return self::$current_profile_data[ self::DEVICE_TYPE_GLOBAL ]['m'][ $image_id ] ?? [];
	}

	/**
	 * Gets the missing srcsets for a specific profile ID.
	 *
	 * @param int $image_id The image ID to get the missing srcsets for.
	 *
	 * @return array<int, array{w: int, h: int, d: int, s: string, b: int}>|array{} The missing srcsets.
	 */
	public function get_missing_srcsets( int $image_id ): array {
		return self::$current_profile_data[ self::DEVICE_TYPE_GLOBAL ]['s'][ $image_id ] ?? [];
	}

	/**
	 * Gets the crop status for a specific image ID.
	 *
	 * @param int $image_id The image ID to get the crop status for.
	 *
	 * @return bool The crop status.
	 */
	public function get_crop_status( int $image_id ): bool {
		return self::$current_profile_data[ self::DEVICE_TYPE_GLOBAL ]['c'][ $image_id ] ?? false;
	}
	/**
	 * Checks if profile data exists for all active device types.
	 *
	 * @param string $id The profile ID to check.
	 *
	 * @return bool True if data exists for all device types, false otherwise.
	 */
	public function exists_all( $id ): bool {
		foreach ( self::get_active_devices() as $device ) {
			if ( ! $this->exists( $id, $device ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Gets a list of device types that are missing profile data.
	 *
	 * @param string $id The profile ID to check.
	 *
	 * @return array List of device types missing profile data.
	 */
	public function missing_devices( $id ): array {
		$missing = [];
		foreach ( self::get_active_devices() as $device ) {
			if ( ! $this->exists( $id, $device ) ) {
				$missing[] = $device;
			}
		}

		return $missing;
	}

	/**
	 * Checks if profile data exists for a specific device type.
	 *
	 * @param string $id The profile ID to check.
	 * @param int    $device The device type constant.
	 *
	 * @return bool True if data exists, false otherwise.
	 */
	public function exists( $id, $device ): bool {
		return $this->storage->get( $id . '_' . $device ) !== false;
	}

	/**
	 * Gets the current profile ID being processed.
	 *
	 * @return string The current profile ID or null if not set.
	 */
	public static function get_current_profile_id(): string {
		return self::$current_profile_id;
	}

	/**
	 * Sets the current profile ID.
	 *
	 * @param string $id The profile ID to set as current.
	 *
	 * @return void
	 */
	public static function set_current_profile_id( $id ): void {
		self::$current_profile_id = $id;
	}

	/**
	 * Gets the current profile data for all device types.
	 *
	 * @return array The current profile data.
	 */
	public static function get_current_profile_data(): array {
		return self::$current_profile_data;
	}

	/**
	 * Sets the current profile data by loading it from storage.
	 *
	 * @return array The loaded profile data.
	 * @throws \Exception If current profile ID is not set.
	 */
	public function set_current_profile_data(): array {
		if ( empty( self::get_current_profile_id() ) ) {
			throw new \Exception( 'Current profile ID is not set' );
		}
		if ( ! empty( self::$current_profile_data ) ) {
			return self::$current_profile_data;
		}
		self::$current_profile_data = [
			self::DEVICE_TYPE_MOBILE  => $this->storage->get( self::get_current_profile_id() . '_' . self::DEVICE_TYPE_MOBILE ),
			self::DEVICE_TYPE_DESKTOP => $this->storage->get( self::get_current_profile_id() . '_' . self::DEVICE_TYPE_DESKTOP ),
			self::DEVICE_TYPE_GLOBAL  => $this->storage->get( self::get_current_profile_id() ),
		];
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'Profile data: ' . print_r( self::$current_profile_data, true ) . ' for id: ' . self::get_current_profile_id() );
		}

		return self::$current_profile_data;
	}

	/**
	 * Checks if an image is in the viewport of all device types.
	 *
	 * @param int $image_id The image ID to check.
	 *
	 * @return bool True if the image is in the viewport of all device types, false otherwise.
	 */
	public function is_in_all_viewports( int $image_id ): bool {
		foreach ( self::get_active_devices() as $device ) {
			// If the data is not available for the device, return false.
			if ( empty( self::$current_profile_data[ $device ] ?? null ) ) {
				return false;
			}
			// If the image is not in the viewport of the device, return false.
			if ( ! ( self::$current_profile_data[ $device ]['af'][ $image_id ] ?? false ) ) {
				return false;
			}
		}

		// If the image is in the viewport of all device types, return true.
		return true;
	}

	/**
	 * Checks if the LCP image is in the viewport of all device types.
	 *
	 * @param int $image_id The image ID to check.
	 *
	 * @return bool True if the LCP image is in the viewport of all device types, false otherwise.
	 */
	public function is_lcp_image_in_all_viewports( int $image_id ): bool {
		foreach ( self::get_active_devices() as $device ) {
			if ( ( ( self::$current_profile_data[ $device ]['lcp']['type'] ?? '' ) === 'img' ) && ( self::$current_profile_data[ $device ]['lcp']['imageId'] === $image_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if an image is in the viewport of any device type.
	 *
	 * @param mixed $image_id The image ID to check.
	 *
	 * @return int|false The device type if the image is in the viewport, false otherwise.
	 */
	public function is_in_any_viewport( $image_id ) {
		foreach ( self::get_active_devices() as $device ) {
			if ( self::$current_profile_data[ $device ]['af'][ $image_id ] ?? false ) {
				return $device;
			}
		}

		return false;
	}

	/**
	 * Gets the profile data for a specific ID.
	 *
	 * @param string $id The profile ID to get data for.
	 *
	 * @return array The profile data.
	 */
	public function get_profile_data( $id ) {
		$profile_data = [];
		foreach ( self::get_active_devices() as $device ) {
			$profile_data[ $device ] = $this->storage->get( $id . '_' . $device );
		}

		return $profile_data;
	}

	/**
	 * Resets the current profile ID and data.
	 *
	 * @return void
	 */
	public static function reset_current_profile() {
		self::$current_profile_id   = null;
		self::$current_profile_data = [];
	}

	/**
	 * Gets the list of active device types supported by the profiler.
	 *
	 * @return array Array of device type constants.
	 */
	public static function get_active_devices(): array {
		return [
			self::DEVICE_TYPE_MOBILE,
			self::DEVICE_TYPE_DESKTOP,
		];
	}

	/**
	 * Check if there is any profile data available for the current profile.
	 *
	 * @return bool
	 */
	public function is_data_available(): bool {
		foreach ( self::get_active_devices() as $device ) {
			if ( empty( self::$current_profile_data[ $device ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Generate HTML comment with profile data and performance metrics.
	 *
	 * @return string HTML comment with profile data and metrics.
	 */
	public function get_current_profile_html_comment(): string {
		$profile_data = self::get_current_profile_data();

		if ( empty( $profile_data ) ) {
			return '<!-- plugin=optimole-wp: No profile ID available -->';
		}

		// Format the HTML comment
		$comment_parts = [
			'plugin=optimole-wp',
		];

		// Add device-specific metrics
		foreach ( $profile_data as $device_type => $device_data ) {
			$device_name = $this->get_device_name( $device_type );
			$comment_parts[] = 'measurement#device-' . $device_name . '#' . json_encode( $device_data );
		}

		return '<!-- ' . implode( ' ', $comment_parts ) . ' -->';
	}


	/**
	 * Get device name from device type constant.
	 *
	 * @param int $device_type The device type constant.
	 * @return string Device name.
	 */
	private function get_device_name( int $device_type ): string {
		switch ( $device_type ) {
			case self::DEVICE_TYPE_MOBILE:
				return 'mobile';
			case self::DEVICE_TYPE_DESKTOP:
				return 'desktop';
			case self::DEVICE_TYPE_GLOBAL:
				return 'global';
			default:
				return 'unknown';
		}
	}
}
