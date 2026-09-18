<?php

use Optimole\Sdk\ValueObject\Position;

/**
 * Class Optml_Abilities.
 *
 * Registers Optimole abilities with the WordPress Abilities API (WP 6.9+).
 */
class Optml_Abilities {

	const CATEGORY = 'optimole';

	/**
	 * Maximum number of attachments handled by a single call, same cap as the media library bulk handler.
	 */
	const MAX_MEDIA_IDS = 20;

	/**
	 * Maximum number of attachments that can be inspected in a single status call.
	 */
	const MAX_STATUS_IDS = 100;

	/**
	 * Settings that accept the enabled/disabled values.
	 *
	 * @var string[]
	 */
	private static $toggle_settings = [
		'image_replacer',
		'cdn',
		'autoquality',
		'best_format',
		'strip_metadata',
		'network_optimization',
		'retina_images',
		'scale',
		'resize_smart',
		'limit_dimensions',
		'lazyload',
		'lazyload_placeholder',
		'native_lazyload',
		'video_lazyload',
		'bg_replacer',
		'no_script',
		'css_minify',
		'js_minify',
	];

	/**
	 * Settings that accept a value from a fixed list.
	 *
	 * @var array<string, string[]>
	 */
	private static $enum_settings = [
		'compression_mode' => [ 'speed_optimized', 'quality_optimized', 'custom' ],
		'lazyload_type'    => [ 'fixed', 'viewport', 'all', 'fixed|viewport' ],
		'wm_position'      => [
			Position::NORTH,
			Position::NORTH_EAST,
			Position::NORTH_WEST,
			Position::CENTER,
			Position::EAST,
			Position::WEST,
			Position::SOUTH_EAST,
			Position::SOUTH,
			Position::SOUTH_WEST,
		],
	];

	/**
	 * Settings that accept a bounded integer.
	 *
	 * @var array<string, int[]>
	 */
	private static $integer_settings = [
		'quality'              => [ 50, 100 ],
		'limit_width'          => [ 100, 5000 ],
		'limit_height'         => [ 100, 5000 ],
		'skip_lazyload_images' => [ 0, 100 ],
	];

	/**
	 * Settings that accept a number.
	 *
	 * @var string[]
	 */
	private static $number_settings = [ 'wm_opacity', 'wm_scale', 'wm_x', 'wm_y' ];

	/**
	 * Optml_Abilities constructor.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			[
				'label'       => __( 'Optimole', 'optimole-wp' ),
				'description' => __( 'Image delivery, cloud offloading and cache operations provided by Optimole.', 'optimole-wp' ),
			]
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'optimole/get-delivery-settings',
			[
				'label'               => __( 'Get Optimole delivery settings', 'optimole-wp' ),
				'description'         => __( 'Returns the effective Optimole image delivery settings: quality and compression, resize, lazy loading, exclusion rules and watermark. Credentials are never returned.', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'connected' => [ 'type' => 'boolean' ],
						'settings'  => [ 'type' => 'object' ],
					],
				],
				'execute_callback'    => [ $this, 'get_delivery_settings' ],
				'permission_callback' => [ $this, 'can_manage' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);

		wp_register_ability(
			'optimole/update-delivery-settings',
			[
				'label'               => __( 'Update Optimole delivery settings', 'optimole-wp' ),
				'description'         => __( 'Updates Optimole image delivery settings. Only the provided settings are changed. Exclusion rules are added or removed individually.', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_update_settings_schema(),
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'dry_run'  => [ 'type' => 'boolean' ],
						'changes'  => [ 'type' => 'object' ],
						'settings' => [ 'type' => 'object' ],
					],
				],
				'execute_callback'    => [ $this, 'update_delivery_settings' ],
				'permission_callback' => [ $this, 'can_manage' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);

		wp_register_ability(
			'optimole/offload-media',
			[
				'label'               => __( 'Offload media to Optimole', 'optimole-wp' ),
				'description'         => __( 'Moves the selected media library images to Optimole Cloud and removes the local files, using the same process as the "Offload to Optimole" media library action. Requires the offload media option to be enabled.', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_media_ids_schema( self::MAX_MEDIA_IDS, true ),
				'output_schema'       => $this->get_move_output_schema(),
				'execute_callback'    => [ $this, 'offload_media' ],
				'permission_callback' => [ $this, 'can_move_media' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);

		wp_register_ability(
			'optimole/restore-media',
			[
				'label'               => __( 'Restore media from Optimole', 'optimole-wp' ),
				'description'         => __( 'Restores the selected offloaded images from Optimole Cloud back to the media library, using the same process as the "Restore image to media library" action.', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_media_ids_schema( self::MAX_MEDIA_IDS, true ),
				'output_schema'       => $this->get_move_output_schema(),
				'execute_callback'    => [ $this, 'restore_media' ],
				'permission_callback' => [ $this, 'can_move_media' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);

		wp_register_ability(
			'optimole/get-offload-job',
			[
				'label'               => __( 'Get Optimole offload status', 'optimole-wp' ),
				'description'         => __( 'Returns the progress of the current offload or restore transfer and, when media IDs are given, the offload state of each image.', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_media_ids_schema( self::MAX_STATUS_IDS, false ),
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'offload_enabled' => [ 'type' => 'boolean' ],
						'transfer'        => [
							'type'       => 'object',
							'properties' => [
								'action'                => [ 'type' => 'string' ],
								'offload_in_progress'   => [ 'type' => 'boolean' ],
								'restore_in_progress'   => [ 'type' => 'boolean' ],
								'count'                 => [ 'type' => 'integer' ],
								'remaining'             => [ 'type' => 'integer' ],
								'minutes_elapsed'       => [ 'type' => 'number' ],
								'offload_limit'         => [ 'type' => 'integer' ],
								'offload_limit_reached' => [ 'type' => 'boolean' ],
							],
						],
						'items'           => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'media_id' => [ 'type' => 'integer' ],
									'state'    => [ 'type' => 'string' ],
									'error'    => [ 'type' => 'string' ],
								],
							],
						],
					],
				],
				'execute_callback'    => [ $this, 'get_offload_job' ],
				'permission_callback' => [ $this, 'can_manage' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);

		wp_register_ability(
			'optimole/purge-image-cache',
			[
				'label'               => __( 'Purge Optimole image cache', 'optimole-wp' ),
				'description'         => __( 'Invalidates the optimized variants of the selected images. Pass all=true instead of media IDs to clear the cache for every optimized image (allowed once every 5 minutes).', 'optimole-wp' ),
				'category'            => self::CATEGORY,
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'media_ids' => [
							'type'        => 'array',
							'description' => __( 'Attachment IDs of the images to purge.', 'optimole-wp' ),
							'items'       => [
								'type'    => 'integer',
								'minimum' => 1,
							],
							'maxItems'    => self::MAX_MEDIA_IDS,
						],
						'all'       => [
							'type'        => 'boolean',
							'description' => __( 'Clear the cache for all optimized images. Ignored when media_ids is provided.', 'optimole-wp' ),
							'default'     => false,
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'scope'   => [ 'type' => 'string' ],
						'purged'  => [
							'type'  => 'array',
							'items' => [ 'type' => 'integer' ],
						],
						'skipped' => $this->get_skipped_schema(),
					],
				],
				'execute_callback'    => [ $this, 'purge_image_cache' ],
				'permission_callback' => [ $this, 'can_manage' ],
				'meta'                => [
					'annotations'  => [
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
				],
			]
		);
	}

	/**
	 * Permission check used by the settings, cache and transfer status REST routes.
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Permission check used by the move image REST route. Per image checks are done when executing.
	 *
	 * @return bool
	 */
	public function can_move_media() {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Get the delivery settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_delivery_settings() {
		$settings = new Optml_Settings();

		return [
			'connected' => $settings->is_connected(),
			'settings'  => $this->read_delivery_settings( $settings ),
		];
	}

	/**
	 * Update the delivery settings.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function update_delivery_settings( $input ) {
		$input   = is_array( $input ) ? $input : [];
		$dry_run = ! empty( $input['dry_run'] );
		$changes = [];

		$requested = isset( $input['settings'] ) && is_array( $input['settings'] ) ? $input['settings'] : [];

		foreach ( $requested as $key => $value ) {
			if ( in_array( $key, self::$toggle_settings, true ) ) {
				if ( ! in_array( $value, [ 'enabled', 'disabled' ], true ) ) {
					return $this->invalid_setting( $key, __( 'Expected "enabled" or "disabled".', 'optimole-wp' ) );
				}
			} elseif ( isset( self::$enum_settings[ $key ] ) ) {
				if ( ! in_array( $value, self::$enum_settings[ $key ], true ) ) {
					/* translators: %s: list of accepted values. */
					return $this->invalid_setting( $key, sprintf( __( 'Expected one of: %s.', 'optimole-wp' ), implode( ', ', self::$enum_settings[ $key ] ) ) );
				}
			} elseif ( isset( self::$integer_settings[ $key ] ) ) {
				list( $min, $max ) = self::$integer_settings[ $key ];
				if ( ! is_numeric( $value ) || (int) $value < $min || (int) $value > $max ) {
					/* translators: 1: minimum value, 2: maximum value. */
					return $this->invalid_setting( $key, sprintf( __( 'Expected an integer between %1$d and %2$d.', 'optimole-wp' ), $min, $max ) );
				}
				$value = (int) $value;
			} elseif ( in_array( $key, self::$number_settings, true ) ) {
				if ( ! is_numeric( $value ) ) {
					return $this->invalid_setting( $key, __( 'Expected a number.', 'optimole-wp' ) );
				}
				$value = (float) $value;
			} elseif ( $key === 'wm_id' ) {
				if ( ! is_numeric( $value ) ) {
					return $this->invalid_setting( $key, __( 'Expected an integer.', 'optimole-wp' ) );
				}
				$value = (int) $value;
			} elseif ( $key === 'placeholder_color' ) {
				if ( ! is_string( $value ) ) {
					return $this->invalid_setting( $key, __( 'Expected a string.', 'optimole-wp' ) );
				}
				$value = sanitize_text_field( $value );
			} else {
				return $this->invalid_setting( (string) $key, __( 'Unknown setting.', 'optimole-wp' ) );
			}

			$changes[ $key ] = $value;
		}

		$filters = [];
		foreach (
			[
				'add_exclusions'    => true,
				'remove_exclusions' => false,
			] as $field => $state
		) {
			if ( ! isset( $input[ $field ] ) ) {
				continue;
			}
			if ( ! is_array( $input[ $field ] ) ) {
				return new WP_Error( 'optimole_invalid_exclusion', __( 'Exclusions must be a list of rules.', 'optimole-wp' ) );
			}
			foreach ( $input[ $field ] as $rule ) {
				$rule = $this->sanitize_exclusion( $rule );
				if ( is_wp_error( $rule ) ) {
					return $rule;
				}
				$filters[ $rule['scope'] ][ $rule['rule'] ][ $rule['value'] ] = $state;
			}
		}
		if ( ! empty( $filters ) ) {
			$changes['filters'] = $filters;
		}

		if ( empty( $changes ) ) {
			return new WP_Error( 'optimole_no_changes', __( 'No settings to update were provided.', 'optimole-wp' ) );
		}

		$settings = new Optml_Settings();

		if ( ! $dry_run ) {
			$settings->parse_settings( $changes );
			$settings = new Optml_Settings();
		}

		return [
			'dry_run'  => $dry_run,
			'changes'  => $changes,
			'settings' => $this->read_delivery_settings( $settings ),
		];
	}

	/**
	 * Offload the selected images.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function offload_media( $input ) {
		return $this->move_media( 'offload_images', $input );
	}

	/**
	 * Restore the selected images.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function restore_media( $input ) {
		return $this->move_media( 'rollback_images', $input );
	}

	/**
	 * Get the transfer progress and the state of the selected images.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function get_offload_job( $input ) {
		$ids = $this->parse_media_ids( $input, self::MAX_STATUS_IDS, false );
		if ( is_wp_error( $ids ) ) {
			return $ids;
		}

		$settings = new Optml_Settings();
		$meta     = Optml_Media_Offload::get_process_meta();
		$items    = [];

		foreach ( $ids as $id ) {
			$items[] = $this->get_media_state( $id );
		}

		return [
			'offload_enabled' => $settings->is_offload_enabled(),
			'transfer'        => [
				'action'                => (string) $settings->get( 'transfer_status' ),
				'offload_in_progress'   => $settings->get( 'offloading_status' ) === 'enabled',
				'restore_in_progress'   => $settings->get( 'rollback_status' ) === 'enabled',
				'count'                 => (int) $meta['count'],
				'remaining'             => (int) $meta['remaining'],
				'minutes_elapsed'       => round( (float) $meta['time_passed'], 2 ),
				'offload_limit'         => (int) $settings->get( 'offload_limit' ),
				'offload_limit_reached' => $settings->is_offload_limit_reached(),
			],
			'items'           => $items,
		];
	}

	/**
	 * Purge the image cache.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function purge_image_cache( $input ) {
		$ids = $this->parse_media_ids( $input, self::MAX_MEDIA_IDS, false );
		if ( is_wp_error( $ids ) ) {
			return $ids;
		}

		$settings = new Optml_Settings();
		if ( ! $settings->is_connected() ) {
			return $this->not_connected();
		}

		if ( empty( $ids ) ) {
			if ( ! is_array( $input ) || empty( $input['all'] ) ) {
				return new WP_Error( 'optimole_missing_media_ids', __( 'Provide media_ids, or all=true to clear the cache for every image.', 'optimole-wp' ) );
			}

			$response = $settings->clear_cache( 'images' );
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			return [
				'scope'   => 'all',
				'purged'  => [],
				'skipped' => [],
			];
		}

		$purged  = [];
		$skipped = [];

		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) !== 'attachment' ) {
				$skipped[] = $this->skipped( $id, 'not_found' );
				continue;
			}

			$meta = wp_get_attachment_metadata( $id );
			if ( ! is_array( $meta ) || empty( $meta['file'] ) || ! is_string( $meta['file'] ) ) {
				$skipped[] = $this->skipped( $id, 'not_an_image' );
				continue;
			}

			// Same call as Optml_Admin::purge_image_cache().
			$response = $settings->clear_cache( wp_basename( $meta['file'] ) );
			if ( is_wp_error( $response ) ) {
				$skipped[] = $this->skipped( $id, $response->get_error_code(), $response->get_error_message() );
				continue;
			}

			$purged[] = $id;
		}

		return [
			'scope'   => 'media',
			'purged'  => $purged,
			'skipped' => $skipped,
		];
	}

	/**
	 * Move the images to or from the cloud, one at a time, as the move image REST route does.
	 *
	 * @param string $action Either offload_images or rollback_images.
	 * @param mixed  $input Ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	private function move_media( $action, $input ) {
		$ids = $this->parse_media_ids( $input, self::MAX_MEDIA_IDS, true );
		if ( is_wp_error( $ids ) ) {
			return $ids;
		}

		$settings = new Optml_Settings();
		if ( ! $settings->is_connected() ) {
			return $this->not_connected();
		}
		if ( ! $settings->is_offload_enabled() ) {
			return new WP_Error( 'optimole_offload_disabled', __( 'The offload media option is not enabled in Optimole.', 'optimole-wp' ) );
		}

		$is_offload = $action === 'offload_images';
		$error_key  = Optml_Media_Offload::META_KEYS[ $is_offload ? 'offload_error' : 'rollback_error' ];
		$offload    = Optml_Media_Offload::instance();
		$accepted   = [];
		$skipped    = [];

		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) !== 'attachment' ) {
				$skipped[] = $this->skipped( $id, 'not_found' );
				continue;
			}
			if ( ! current_user_can( 'edit_post', $id ) || ! current_user_can( 'delete_post', $id ) ) {
				$skipped[] = $this->skipped( $id, 'forbidden' );
				continue;
			}

			$meta = wp_get_attachment_metadata( $id );
			if (
				! is_array( $meta ) || empty( $meta['file'] ) || ! is_string( $meta['file'] ) ||
				wp_check_filetype( $meta['file'], Optml_Config::$all_extensions )['ext'] === false
			) {
				$skipped[] = $this->skipped( $id, 'unsupported_file' );
				continue;
			}
			if ( Optml_Media_Offload::is_uploaded_image( $meta['file'] ) === $is_offload ) {
				$skipped[] = $this->skipped( $id, $is_offload ? 'already_offloaded' : 'not_offloaded' );
				continue;
			}

			try {
				$offload->move_single_image( $action, $id );
			} catch ( Exception $e ) {
				$skipped[] = $this->skipped( $id, 'move_failed', wp_strip_all_tags( $e->getMessage() ) );
				continue;
			}

			$meta  = wp_get_attachment_metadata( $id );
			$moved = is_array( $meta ) && ! empty( $meta['file'] ) && Optml_Media_Offload::is_uploaded_image( $meta['file'] ) === $is_offload;
			if ( ! $moved ) {
				$reason = ! empty( get_post_meta( $id, $error_key, true ) ) ? 'move_error' : 'not_moved';
				if ( $is_offload && $settings->is_offload_limit_reached() ) {
					$reason = 'offload_limit_reached';
				}
				$skipped[] = $this->skipped( $id, $reason );
				continue;
			}

			$accepted[] = $id;
		}

		return [
			'action'   => $is_offload ? 'offload' : 'restore',
			'accepted' => $accepted,
			'skipped'  => $skipped,
		];
	}

	/**
	 * Get the offload state of an attachment.
	 *
	 * @param int $id Attachment ID.
	 *
	 * @return array<string, mixed>
	 */
	private function get_media_state( $id ) {
		$item = [
			'media_id' => $id,
			'state'    => 'not_found',
			'error'    => '',
		];

		if ( get_post_type( $id ) !== 'attachment' ) {
			return $item;
		}

		$meta = wp_get_attachment_metadata( $id );
		if ( ! is_array( $meta ) || empty( $meta['file'] ) || ! is_string( $meta['file'] ) ) {
			$item['state'] = 'unsupported_file';

			return $item;
		}

		$item['state'] = Optml_Media_Offload::is_uploaded_image( $meta['file'] ) ? 'offloaded' : 'local';

		if ( ! empty( get_post_meta( $id, Optml_Media_Offload::META_KEYS['offload_error'], true ) ) ) {
			$item['error'] = 'offload_error';
		} elseif ( ! empty( get_post_meta( $id, Optml_Media_Offload::META_KEYS['rollback_error'], true ) ) ) {
			$item['error'] = 'rollback_error';
		}

		return $item;
	}

	/**
	 * Read the delivery settings exposed by the abilities.
	 *
	 * @param Optml_Settings $settings Settings instance.
	 *
	 * @return array<string, mixed>
	 */
	private function read_delivery_settings( $settings ) {
		$result = [];
		$keys   = array_merge(
			self::$toggle_settings,
			array_keys( self::$integer_settings ),
			[ 'compression_mode', 'lazyload_type', 'placeholder_color' ]
		);

		foreach ( $keys as $key ) {
			$result[ $key ] = $settings->get( $key );
		}

		$result['effective_quality'] = $settings->get_quality();
		$result['exclusions']        = [];

		foreach ( $settings->get_filters() as $scope => $rules ) {
			if ( ! is_array( $rules ) ) {
				continue;
			}
			foreach ( $rules as $rule => $values ) {
				if ( ! is_array( $values ) ) {
					continue;
				}
				foreach ( $values as $value => $state ) {
					if ( $state === false || $state === 'false' ) {
						continue;
					}
					$result['exclusions'][] = [
						'scope' => (string) $scope,
						'rule'  => (string) $rule,
						'value' => (string) $value,
					];
				}
			}
		}

		$result['watermark'] = $settings->get_watermark();

		return $result;
	}

	/**
	 * Validate an exclusion rule.
	 *
	 * @param mixed $rule Exclusion rule.
	 *
	 * @return array{scope: string, rule: string, value: string}|WP_Error
	 */
	private function sanitize_exclusion( $rule ) {
		$scopes = [ Optml_Settings::FILTER_TYPE_LAZYLOAD, Optml_Settings::FILTER_TYPE_OPTIMIZE ];
		$types  = [
			Optml_Settings::FILTER_EXT,
			Optml_Settings::FILTER_FILENAME,
			Optml_Settings::FILTER_URL,
			Optml_Settings::FILTER_URL_MATCH,
			Optml_Settings::FILTER_CLASS,
		];

		if (
			! is_array( $rule ) ||
			! isset( $rule['scope'], $rule['rule'], $rule['value'] ) ||
			! in_array( $rule['scope'], $scopes, true ) ||
			! in_array( $rule['rule'], $types, true ) ||
			! is_string( $rule['value'] )
		) {
			return new WP_Error( 'optimole_invalid_exclusion', __( 'Each exclusion needs a valid scope, rule and value.', 'optimole-wp' ) );
		}

		$value = sanitize_text_field( $rule['value'] );
		if ( $value === '' ) {
			return new WP_Error( 'optimole_invalid_exclusion', __( 'The exclusion value can not be empty.', 'optimole-wp' ) );
		}

		return [
			'scope' => $rule['scope'],
			'rule'  => $rule['rule'],
			'value' => $value,
		];
	}

	/**
	 * Parse the media IDs from the input.
	 *
	 * @param mixed $input Ability input.
	 * @param int   $max Maximum number of IDs.
	 * @param bool  $required Whether at least one ID is required.
	 *
	 * @return int[]|WP_Error
	 */
	private function parse_media_ids( $input, $max, $required ) {
		$ids = is_array( $input ) && isset( $input['media_ids'] ) && is_array( $input['media_ids'] ) ? $input['media_ids'] : [];
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

		if ( $required && empty( $ids ) ) {
			return new WP_Error( 'optimole_missing_media_ids', __( 'At least one media ID is required.', 'optimole-wp' ) );
		}
		if ( count( $ids ) > $max ) {
			/* translators: %d: maximum number of media IDs. */
			return new WP_Error( 'optimole_too_many_media_ids', sprintf( __( 'A maximum of %d media IDs can be processed at once.', 'optimole-wp' ), $max ) );
		}

		return $ids;
	}

	/**
	 * Build a skipped item.
	 *
	 * @param int    $id Attachment ID.
	 * @param string $reason Reason code.
	 * @param string $message Optional details.
	 *
	 * @return array<string, mixed>
	 */
	private function skipped( $id, $reason, $message = '' ) {
		return [
			'media_id' => $id,
			'reason'   => $reason,
			'message'  => $message,
		];
	}

	/**
	 * Error for an invalid setting value.
	 *
	 * @param string $key Setting key.
	 * @param string $message Error details.
	 *
	 * @return WP_Error
	 */
	private function invalid_setting( $key, $message ) {
		return new WP_Error(
			'optimole_invalid_setting',
			/* translators: 1: setting name, 2: error details. */
			sprintf( __( 'Invalid value for "%1$s". %2$s', 'optimole-wp' ), $key, $message ),
			[ 'setting' => $key ]
		);
	}

	/**
	 * Error for a site that is not connected to Optimole.
	 *
	 * @return WP_Error
	 */
	private function not_connected() {
		return new WP_Error( 'optimole_not_connected', __( 'This site is not connected to Optimole.', 'optimole-wp' ) );
	}

	/**
	 * Input schema for the update settings ability.
	 *
	 * @return array<string, mixed>
	 */
	private function get_update_settings_schema() {
		$properties = [];

		foreach ( self::$toggle_settings as $key ) {
			$properties[ $key ] = [
				'type' => 'string',
				'enum' => [ 'enabled', 'disabled' ],
			];
		}
		$properties['scale']['description'] = __( 'Legacy inverted flag: "disabled" means images are scaled to the visitor screen, "enabled" turns scaling off.', 'optimole-wp' );
		$properties['quality']              = [
			'type'        => 'integer',
			'minimum'     => 50,
			'maximum'     => 100,
			'description' => __( 'Used only when autoquality is disabled.', 'optimole-wp' ),
		];

		foreach ( self::$enum_settings as $key => $values ) {
			$properties[ $key ] = [
				'type' => 'string',
				'enum' => $values,
			];
		}
		foreach ( self::$integer_settings as $key => $bounds ) {
			if ( isset( $properties[ $key ] ) ) {
				continue;
			}
			$properties[ $key ] = [
				'type'    => 'integer',
				'minimum' => $bounds[0],
				'maximum' => $bounds[1],
			];
		}
		foreach ( self::$number_settings as $key ) {
			$properties[ $key ] = [ 'type' => 'number' ];
		}
		$properties['wm_id']             = [
			'type'        => 'integer',
			'description' => __( 'Watermark ID from the Optimole dashboard, -1 to disable the watermark.', 'optimole-wp' ),
		];
		$properties['placeholder_color'] = [ 'type' => 'string' ];

		$exclusions = [
			'type'  => 'array',
			'items' => [
				'type'                 => 'object',
				'properties'           => [
					'scope' => [
						'type' => 'string',
						'enum' => [ Optml_Settings::FILTER_TYPE_LAZYLOAD, Optml_Settings::FILTER_TYPE_OPTIMIZE ],
					],
					'rule'  => [
						'type' => 'string',
						'enum' => [
							Optml_Settings::FILTER_EXT,
							Optml_Settings::FILTER_FILENAME,
							Optml_Settings::FILTER_URL,
							Optml_Settings::FILTER_URL_MATCH,
							Optml_Settings::FILTER_CLASS,
						],
					],
					'value' => [ 'type' => 'string' ],
				],
				'required'             => [ 'scope', 'rule', 'value' ],
				'additionalProperties' => false,
			],
		];

		return [
			'type'                 => 'object',
			'properties'           => [
				'settings'          => [
					'type'                 => 'object',
					'properties'           => $properties,
					'additionalProperties' => false,
				],
				'add_exclusions'    => $exclusions,
				'remove_exclusions' => $exclusions,
				'dry_run'           => [
					'type'        => 'boolean',
					'description' => __( 'Validate and return the changes without saving them.', 'optimole-wp' ),
					'default'     => false,
				],
			],
			'additionalProperties' => false,
		];
	}

	/**
	 * Input schema for abilities that take a list of media IDs.
	 *
	 * @param int  $max Maximum number of IDs.
	 * @param bool $required Whether the list is required.
	 *
	 * @return array<string, mixed>
	 */
	private function get_media_ids_schema( $max, $required ) {
		$schema = [
			'type'                 => 'object',
			'properties'           => [
				'media_ids' => [
					'type'        => 'array',
					'description' => __( 'Attachment IDs of the images.', 'optimole-wp' ),
					'items'       => [
						'type'    => 'integer',
						'minimum' => 1,
					],
					'maxItems'    => $max,
				],
			],
			'additionalProperties' => false,
		];

		if ( $required ) {
			$schema['properties']['media_ids']['minItems'] = 1;
			$schema['required']                            = [ 'media_ids' ];
		} else {
			$schema['default'] = [];
		}

		return $schema;
	}

	/**
	 * Output schema for the offload and restore abilities.
	 *
	 * @return array<string, mixed>
	 */
	private function get_move_output_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'action'   => [ 'type' => 'string' ],
				'accepted' => [
					'type'  => 'array',
					'items' => [ 'type' => 'integer' ],
				],
				'skipped'  => $this->get_skipped_schema(),
			],
		];
	}

	/**
	 * Schema for the skipped items list.
	 *
	 * @return array<string, mixed>
	 */
	private function get_skipped_schema() {
		return [
			'type'  => 'array',
			'items' => [
				'type'       => 'object',
				'properties' => [
					'media_id' => [ 'type' => 'integer' ],
					'reason'   => [ 'type' => 'string' ],
					'message'  => [ 'type' => 'string' ],
				],
			],
		];
	}
}
