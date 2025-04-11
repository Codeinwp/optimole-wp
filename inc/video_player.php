<?php

/**
 * Video Player
 *
 * @since 4.0.0
 */
class Optml_Video_Player {

	/**
	 * Block attributes
	 *
	 * @var array
	 */
	private $block_attributes = [
		'url' => [
			'type' => 'string',
		],
		'primaryColor' => [
			'type' => 'string',
			'default' => '#577BF9',
		],
		'aspectRatio' => [
			'type' => 'string',
			'default' => 'auto',
		],
		'loop' => [
			'type' => 'boolean',
			'default' => false,
		],
		'hideControls' => [
			'type' => 'boolean',
			'default' => false,
		],
	];

	const LOCALIZATION_VAR = 'OMVideoPlayerBlock';

	/**
	 * Icons
	 *
	 * @var array
	 */
	private $icons = [
		'play'        => '<symbol id="optml-play-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play"><polygon points="6 3 20 12 6 21 6 3"/></symbol>',
		'volume-mute' => '<symbol id="optml-volume-mute-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-x"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><line x1="22" x2="16" y1="9" y2="15"/><line x1="16" x2="22" y1="9" y2="15"/></symbol>',
		'volume-high' => '<symbol id="optml-volume-high-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-2"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></symbol>',
		'volume-low'  => '<symbol id="optml-volume-low-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-1"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/></symbol>',
		'spinner'     => '<symbol id="optml-loader-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></symbol>',
		'maximize'    => '<symbol id="optml-maximize-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-maximize"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></symbol>',
		'minimize'    => '<symbol id="optml-minimize-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minimize"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></symbol>',
		'pause'       => '<symbol id="optml-pause-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--optml-ctrl-ico-col, #fff)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pause"><rect x="14" y="4" width="4" height="16" rx="1"/><rect x="6" y="4" width="4" height="16" rx="1"/></symbol>',
	];

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$settings = new Optml_Settings();

		if ( ! $settings->is_connected() ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_admin_video_player_assets' ] );
		add_action( 'admin_head', [ $this, 'add_editor_player_icons' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_video_player_script' ] );
		add_action( 'init', [ $this, 'register_video_player_block' ] );
	}

	/**
	 * Enqueue the admin video player assets.
	 *
	 * @since 4.0.0
	 */
	public function enqueue_admin_video_player_assets() {
		$asset_file = include OPTML_PATH . 'assets/build/video-player/editor/editor.asset.php';

		if ( empty( $asset_file ) ) {
			return;
		}

		$handle = 'optimole-video-player-editor';

		wp_register_script(
			$handle,
			OPTML_URL . 'assets/build/video-player/editor/editor.js',
			array_merge( $asset_file['dependencies'] ),
			$asset_file['version'],
			true
		);

		wp_localize_script( $handle, 'OMVideoPlayerBlock', $this->get_localization( true ) );
		wp_enqueue_script( $handle );

		wp_enqueue_style( $handle, OPTML_URL . 'assets/build/video-player/frontend/style-frontend.css', [], $asset_file['version'] );
	}

	/**
	 * Add the video player icons to the editor.
	 *
	 * @since 4.0.0
	 */
	public function add_editor_player_icons() {
		$screen = get_current_screen();
		if ( ! $screen || ! $screen->is_block_editor() ) {
			return;
		}

		$this->render_player_icons();
	}

	/**
	 * Maybe enqueue the video player component script.
	 *
	 * @since 4.0.0
	 */
	public function maybe_enqueue_video_player_script() {
		if ( ! has_block( 'optimole/video-player' ) ) {
			return;
		}

		$this->enqueue_video_player_component_script();
		$this->render_player_icons();
	}

	/**
	 * Enqueue the video player component script.
	 *
	 * @since 4.0.0
	 */
	private function enqueue_video_player_component_script() {
		$asset_file = include OPTML_PATH . 'assets/build/video-player/frontend/frontend.asset.php';

		if ( empty( $asset_file ) ) {
			return;
		}

		wp_register_script(
			'optimole-video-player-component',
			OPTML_URL . 'assets/build/video-player/frontend/frontend.js',
			$asset_file['dependencies'],
		);

		wp_localize_script( 'optimole-video-player-component', self::LOCALIZATION_VAR, $this->get_localization() );
		wp_enqueue_script( 'optimole-video-player-component' );

		wp_enqueue_style(
			'optimole-video-player-component',
			OPTML_URL . 'assets/build/video-player/frontend/style-frontend.css',
			[],
			$asset_file['version']
		);
	}

	/**
	 * Render the video player icons
	 *
	 * @since 4.0.0
	 */
	private function render_player_icons() {
		?>
	<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
		<?php foreach ( $this->icons as $icon_id => $icon_svg ) : ?>
			<?php echo $icon_svg; ?>
		<?php endforeach; ?>
	</svg>
		<?php
	}

	/**
	 * Register the video player block.
	 *
	 * @since 4.0.0
	 */
	public function register_video_player_block() {
		register_block_type(
			'optimole/video-player',
			[
				'render_callback' => [ $this, 'render_video_player_block' ],
				'attributes'      => $this->block_attributes,
				'supports'        => [
					'align' => true,
				],
			]
		);
	}

	/**
	 * Render the video player block.
	 *
	 * @param array $attributes The block attributes.
	 * @return string The video player block markup.
	 *
	 * @since 4.0.0
	 */
	public function render_video_player_block( $attributes, $content, $block ) {
		$attributes = wp_parse_args( $attributes, $this->block_attributes );

		$style = [
			'--om-primary-color' => $attributes['primaryColor'],
			'--om-aspect-ratio' => $attributes['aspectRatio'],
		];

		if ( isset( $attributes['style'] ) ) {
			$style = array_merge( $style, $this->block_style_attributes_to_css_array( $attributes['style'] ) );
		}

		$css = array_map(
			function ( $key, $value ) {
				return $key . ': ' . $value;
			},
			array_keys( $style ),
			$style
		);

		$tag_attributes = [
			'video-src' => esc_url( $attributes['url'] ),
			'loop' => $attributes['loop'] ? 'true' : 'false',
			'hide-controls' => $attributes['hideControls'] ? 'true' : 'false',
			'style' => esc_attr( implode( ';', $css ) ),
		];

		$tag_attributes = array_map(
			function ( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $tag_attributes ),
			$tag_attributes
		);

		$wrapper_attributes = array_filter(
			$attributes,
			function ( $key ) {
				return ! in_array( $key, array_keys( $this->block_attributes ), true ) && $key !== 'style';
			},
			ARRAY_FILTER_USE_KEY
		);

		return sprintf(
			'<div %s><optimole-video-player %s></optimole-video-player></div>',
			get_block_wrapper_attributes( $wrapper_attributes ),
			implode( ' ', $tag_attributes ),
		);
	}

	/**
	 * Get the localization strings.
	 *
	 * @param boolean $editor Whether to get the localization for the editor.
	 * @return array The localization strings.
	 *
	 * @since 4.0.0
	 */
	private function get_localization( $editor = false ) {
		$strings = [
			'play'            => __( 'Play', 'optimole-wp' ),
			'pause'           => __( 'Pause', 'optimole-wp' ),
			'mute'            => __( 'Mute', 'optimole-wp' ),
			'unmute'          => __( 'Unmute', 'optimole-wp' ),
			'fullscreen'      => __( 'Fullscreen', 'optimole-wp' ),
			'exitFullscreen'  => __( 'Exit Fullscreen', 'optimole-wp' ),
		];

		if ( ! $editor ) {
			return $strings;
		}

		$options = new Optml_Settings();
		$cdn_url = $options->get_cdn_url();

		return array_merge(
			$strings,
			[
				'domain'            => $cdn_url,
				'blockTitle'        => __( 'Optimole Video', 'optimole-wp' ),
				'blockDescription'  => __( 'Optimole Video', 'optimole-wp' ),
				'aspectRatioLabel'  => __( 'Aspect Ratio', 'optimole-wp' ),
				'urlLabel'          => __( 'Video URL', 'optimole-wp' ),
				'urlHelp'           => __( 'Enter the URL of the video you want to display.', 'optimole-wp' ),
				'editLabel'         => __( 'Change URL', 'optimole-wp' ),
				'loopLabel'         => __( 'Loop Video', 'optimole-wp' ),
				'hideControlsLabel' => __( 'Hide Video Controls Bar', 'optimole-wp' ),
				// translators: %s is 'Optimole Dashboard'.
				'invalidUrlError'   => sprintf( __( 'Invalid URL. Please enter a valid video URL from %s', 'optimole-wp' ), '<a target="_blank" href="https://dashboard.optimole.com">' . __( 'Optimole Dashboard', 'optimole-wp' ) . '</a>' ),
				'auto'                          => __( 'Auto', 'optimole-wp' ),
				'save'                          => __( 'Save', 'optimole-wp' ),
				'primaryColorLabel' => __( 'Controls color', 'optimole-wp' ),
			]
		);
	}

	/**
	 * Convert the block style attributes to a css array.
	 *
	 * @param array $attributes The block attributes.
	 * @return array The css array.
	 *
	 * @since 4.0.0
	 */
	private function block_style_attributes_to_css_array( $attributes ) {
		$css = [];

		if ( isset( $attributes['spacing'] ) ) {
			$spacing = $attributes['spacing'];

			foreach ( $spacing as $css_prop_prefix => $values ) {
				foreach ( $values as $direction => $value ) {
					$css[ $css_prop_prefix . '-' . $direction ] = $this->core_var_to_css_var( $value );
				}
			}
		}

		return $css;
	}

	/**
	 * Convert a core var to a css var.
	 * e.g.: var:preset|spacing|50 -> var(--wp--preset--spacing--50)
	 *
	 * @param string $css_value The css value.
	 * @return string The css var.
	 *
	 * @since 4.0.0
	 */
	private function core_var_to_css_var( $css_value ) {
		if ( strpos( $css_value, 'var:' ) !== 0 ) {
			return $css_value;
		}

		$css_value = str_replace( 'var:', '', $css_value );
		$css_value = str_replace( '|', '--', $css_value );

		return 'var(--wp--' . $css_value . ')';
	}
}
