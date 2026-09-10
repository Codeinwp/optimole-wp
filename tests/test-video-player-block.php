<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */
class Test_Video_Player_Block extends WP_UnitTestCase {

	/**
	 * Whether the block had to be registered by this test case.
	 *
	 * @var bool
	 */
	private $registered_block = false;

	public function setUp(): void {
		parent::setUp();

		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],
		] );

		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'optimole/video-player' ) ) {
			$player = new Optml_Video_Player();
			$player->register_video_player_block();
			$this->registered_block = true;
		}
	}

	public function tearDown(): void {
		if ( $this->registered_block ) {
			unregister_block_type( 'optimole/video-player' );
			$this->registered_block = false;
		}

		parent::tearDown();
	}

	/**
	 * Render a serialized video player block.
	 *
	 * @param array $attributes The block attributes.
	 * @return string The rendered markup.
	 */
	private function render( $attributes ) {
		return do_blocks( '<!-- wp:optimole/video-player ' . wp_json_encode( $attributes ) . ' /-->' );
	}

	/**
	 * Undeclared attributes must never reach the wrapper element.
	 */
	public function test_event_handler_attributes_are_not_rendered() {
		$rendered = $this->render( [
			'url'          => 'https://example.com/video.mp4',
			'aspectRatio'  => '16/9',
			'onmouseover'  => 'alert(document.domain)',
			'onerror'      => 'alert(1)',
			'onfocus'      => 'alert(2)',
			'data-wp-on--click' => 'actions.evil',
		] );

		$this->assertStringNotContainsString( 'onmouseover', $rendered );
		$this->assertStringNotContainsString( 'onerror', $rendered );
		$this->assertStringNotContainsString( 'onfocus', $rendered );
		$this->assertStringNotContainsString( 'data-wp-on', $rendered );
		$this->assertStringNotContainsString( 'alert(', $rendered );
	}

	/**
	 * The wrapper keeps the classes produced by block supports.
	 */
	public function test_block_support_classes_are_preserved() {
		$rendered = $this->render( [
			'url'       => 'https://example.com/video.mp4',
			'align'     => 'wide',
			'className' => 'my-custom-class',
		] );

		$this->assertStringContainsString( 'wp-block-optimole-video-player', $rendered );
		$this->assertStringContainsString( 'alignwide', $rendered );
		$this->assertStringContainsString( 'my-custom-class', $rendered );
	}

	/**
	 * The player element keeps rendering its own attributes.
	 */
	public function test_player_element_attributes_are_rendered() {
		$rendered = $this->render( [
			'url'          => 'https://example.com/video.mp4',
			'aspectRatio'  => '4/3',
			'primaryColor' => '#ff0000',
			'loop'         => true,
			'hideControls' => true,
		] );

		$this->assertStringContainsString( 'video-src="https://example.com/video.mp4"', $rendered );
		$this->assertStringContainsString( 'loop="true"', $rendered );
		$this->assertStringContainsString( 'hide-controls="true"', $rendered );
		$this->assertStringContainsString( '--om-primary-color: #ff0000', $rendered );
		$this->assertStringContainsString( '--om-aspect-ratio: 4/3', $rendered );
	}

	/**
	 * A block saved without a url renders instead of fataling.
	 */
	public function test_block_without_url_renders() {
		$rendered = do_blocks( '<!-- wp:optimole/video-player /-->' );

		$this->assertStringContainsString( '<optimole-video-player', $rendered );
		$this->assertStringContainsString( 'video-src=""', $rendered );
		$this->assertStringNotContainsString( 'Array', $rendered );
	}

	/**
	 * Style values that are not colors or known ratios fall back to the defaults.
	 */
	public function test_style_values_are_sanitized() {
		$rendered = $this->render( [
			'url'          => 'https://example.com/video.mp4',
			'aspectRatio'  => 'auto;background:url(https://evil.test/a)',
			'primaryColor' => 'red;position:fixed;top:0',
		] );

		$this->assertStringNotContainsString( 'evil.test', $rendered );
		$this->assertStringNotContainsString( 'position:fixed', $rendered );
		$this->assertStringContainsString( '--om-primary-color: #577BF9', $rendered );
		$this->assertStringContainsString( '--om-aspect-ratio: auto', $rendered );
	}

	/**
	 * Spacing styles only render for known properties, directions and lengths.
	 */
	public function test_spacing_styles_are_sanitized() {
		$rendered = $this->render( [
			'url'   => 'https://example.com/video.mp4',
			'style' => [
				'spacing' => [
					'margin' => [
						'top'                  => 'var:preset|spacing|50',
						'bottom'               => '10px',
						'left'                 => '0;background:url(https://evil.test/b)',
						'right;color:red'      => '5px',
					],
					'behavior;color:blue' => [
						'top' => '5px',
					],
				],
			],
		] );

		$this->assertStringContainsString( 'margin-top: var(--wp--preset--spacing--50)', $rendered );
		$this->assertStringContainsString( 'margin-bottom: 10px', $rendered );
		$this->assertStringNotContainsString( 'evil.test', $rendered );
		$this->assertStringNotContainsString( 'color:red', $rendered );
		$this->assertStringNotContainsString( 'color:blue', $rendered );
	}
}
