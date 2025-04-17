<?php

/**
 * Class Test_Dam.
 */
class Test_Dam extends WP_UnitTestCase {

	const TEST_API_KEY = 'testkey';

	/**
	 * @var int[] List of attachment IDs inserted during the test.
	 */
	private $inserted_ids = array();

	/**
	 * @var Optml_Settings
	 */
	private $settings;

	/**
	 * @var Optml_Dam
	 */
	private $dam;

	/**
	 * @var Optml_Manager
	 */
	private $manager;

	const MOCK_ATTACHMENTS = [
		[
			'url'  => 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg',
			'meta' =>
				[
					'originalHeight' => 1800,
					'originalWidth'  => 1200,
					'updateTime'     => 1688553629048,
					'resourceS3'     => 'randomHashForImage1',
					'mimeType'       => 'image/jpeg',
					'userKey'        => 'mlckcuxuuuyb',
					'fileSize'       => 171114,
					'originURL'      => 'https://test-site.test/wp-content/uploads/2023/07/9.jpg',
					'domain_hash'    => 'dWwtcG9sZWNhdC15dWtpLmluc3Rhd3AueHl6',
				],
		],
		[
			'url'  => 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/10.jpg',
			'meta' =>
				[
					'originalHeight' => 1800,
					'originalWidth'  => 1200,
					'updateTime'     => 1688553629048,
					'resourceS3'     => 'randomHashForImage2',
					'mimeType'       => 'image/jpeg',
					'userKey'        => 'mlckcuxuuuyb',
					'fileSize'       => 171114,
					'originURL'      => 'https://test-site.test/wp-content/uploads/2023/07/10.jpg',
					'domain_hash'    => 'dWwtcG9sZWNhdC15dWtpLmluc3Rhd3AueHl6',
				],
		],
	];

	const IMAGE_SIZES = [
		'test_landscape' => [
			'width'       => 200,
			'height'      => 100,
			'orientation' => 'landscape',
			'crop'        => true
		],
		'test_gravity' => [
			'width'       => 200,
			'height'      => 100,
			'crop'        => ['left','top']
		],
		'test_portrait'  => [
			'width'       => 100,
			'height'      => 200,
			'orientation' => 'portrait',
			'crop'        => true
		],
	];

	public function setUp(): void {
		parent::setUp();
		foreach ( self::IMAGE_SIZES as $slug => $args ) {
			add_image_size( $slug, $args['width'], $args['height'], $args['crop'] );
		}

		$plugin         = Optml_Main::instance();
		$this->dam      = $plugin->dam;
		$this->manager      = $plugin->manager;
		$this->settings = $plugin->admin->settings;
		$this->settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.org' ], 
		] );
		$this->insert_mock_attachments();
		$this->settings->update( 'api_key', self::TEST_API_KEY );
		$this->settings->update( 'cloud_images', 'enabled' );

	}

	public function test_iframe_url_selected_sites_enabled() {
		$this->settings->update( 'cloud_sites', [
			'all'           => 'false',
			'example.com'   => 'true',
			'othersite.com' => 'false'
		] );

		$iframe_url = $this->dam->build_iframe_url();

		$data = $this->get_iframe_args_from_url( $iframe_url );

		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
		$this->assertEquals( self::TEST_API_KEY, $data['token'] );
		$this->assertNotEmpty( $data['sites'] );
		$this->assertContains( 'example.com', $data['sites'] );
		$this->assertNotEmpty( $data['site'] );
		$this->assertEquals( $data['site'], get_site_url() );
	}

	public function test_iframe_url_all_cloud_sites_enabled() {
		$this->settings->update( 'cloud_sites', [
			'all'           => 'true',
			'example.com'   => 'false',
			'othersite.com' => 'false',
		] );

		$iframe_url = $this->dam->build_iframe_url();

		$data = $this->get_iframe_args_from_url( $iframe_url );

		$this->assertIsArray( $data );
		$this->assertEmpty( $data['sites'] );
	}

	public function test_insert_attachments() {
		foreach ( $this->inserted_ids as $index => $id ) {
			$this->assertIsInt( $id );
			$this->assertGreaterThan( 0, $id );

			$attachment = get_post( $id );

			$this->assertEquals( self::MOCK_ATTACHMENTS[ $index ]['url'], $attachment->guid );
			$this->assertEquals( self::MOCK_ATTACHMENTS[ $index ]['meta']['mimeType'], $attachment->post_mime_type );
		}
	}

	public function test_insert_duplicates() {
		$ids = $this->inserted_ids;

		$this->assertIsArray( $ids );
		$this->assertNotEmpty( $ids );

		$duplicates = $this->dam->insert_attachments( self::MOCK_ATTACHMENTS );

		$this->assertIsArray( $duplicates );
		$this->assertNotEmpty( $duplicates );
		$this->assertEquals( $ids, $duplicates );

		$images = array_merge(
			self::MOCK_ATTACHMENTS,
			[
				[
					'url'  => 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/11.jpg',
					'meta' =>
						[
							'originalHeight' => 1800,
							'originalWidth'  => 1200,
							'updateTime'     => 1688553629048,
							'resourceS3'     => 'randomHashForImage3',
							'mimeType'       => 'image/jpeg',
							'userKey'        => 'mlckcuxuuuyb',
							'fileSize'       => 171114,
							'originURL'      => 'https://test-site.test/wp-content/uploads/2023/07/11.jpg',
							'domain_hash'    => 'dWwtcG9sZWNhdC15dWtpLmluc3Rhd3AueHl6',
						],
				],
			],
		);

		$third_run = $this->dam->insert_attachments( $images );

		$this->assertIsArray( $third_run );
		$this->assertNotEmpty( $third_run );
		$this->assertNotEquals( $ids, $third_run );

		foreach ( $ids as $id ) {
			$this->assertContains( $id, $third_run );
		}
	}

	public function test_insert_edits() {
		$ids = $this->inserted_ids;

		$this->assertIsArray( $ids );
		$this->assertNotEmpty( $ids );

		$edit = self::MOCK_ATTACHMENTS[0];
		$edit['isEdit'] = true;

		// An edit is inserted regardless if the image is already in the media library.
		$inserted_edit = $this->dam->insert_attachments( [$edit] );
		$this->assertIsArray( $inserted_edit );
		$this->assertNotEmpty( $inserted_edit );
		$this->assertNotContains( $ids, $inserted_edit );

		// Another edit should be inserted regardless if it's the same image.
		$another_inserted_edit = $this->dam->insert_attachments( [$edit] );
		$this->assertNotEquals( $inserted_edit, $another_inserted_edit );
		$this->assertNotEquals( $inserted_edit[0], $another_inserted_edit[0] );
	}

	public function test_dam_size_gravity_replacer() {
		$url_map = [
			'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:testId/https://test-site.test/9.jpg' => [
				'expected' => 'https://cloudUrlTest.test/dam:1/w:150/h:100/q:auto/id:testId/https://test-site.test/9.jpg',
				'args'     => [ 'width' => 150, 'height' => 100 ]
			],
			'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:testId/directUpload/9.jpg'           => [
				'expected' => 'https://cloudUrlTest.test/dam:1/w:365/h:1200/g:ce/rt:fill/q:auto/id:testId/directUpload/9.jpg',
				'args'     => [ 'width' => 365, 'height' => 1200, 'crop' => true ]
			],
		];

		foreach ( $url_map as $url => $data ) {
			$processed = $this->dam->replace_dam_url_args( $data['args'], $url );

			$this->assertEquals( $processed, $data['expected'] );
		}

		// Check with smart resize.
		$this->settings->update( 'resize_smart', 'enabled' );

		$url       = 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:testId/https://test-site.test/9.jpg';
		$processed = $this->dam->replace_dam_url_args( [ 'width' => 150, 'height' => 100, 'crop' => true ], $url );
		$this->assertEquals( 'https://cloudUrlTest.test/dam:1/w:150/h:100/g:sm/rt:fill/q:auto/id:testId/https://test-site.test/9.jpg', $processed );

		$this->settings->update( 'resize_smart', 'disabled' );
	}

	public function test_alter_attachment_image_src() {
		foreach ( $this->inserted_ids as $id ) {
			$result = $this->dam->alter_attachment_image_src( false, $id, 'test_landscape', false );
			$this->assertStringContainsString( 'w:200/h:100/g:ce/rt:fill', $result[0] );
			$this->assertEquals( 200, $result[1] );
			$this->assertEquals( 100, $result[2] );
			$this->assertFalse( $result[3] );
			$result = $this->dam->alter_attachment_image_src( false, $id, 'test_gravity', false );
			$this->assertStringContainsString( 'w:200/h:100/g:noea/rt:fill', $result[0] );

			$result = $this->dam->alter_attachment_image_src( false, $id, 'test_portrait', false );
			$this->assertStringContainsString( 'w:100/h:200/g:ce/rt:fill', $result[0] );
			$this->assertEquals( 100, $result[1] );
			$this->assertEquals( 200, $result[2] );
			$this->assertFalse( $result[3] );

			$result = $this->dam->alter_attachment_image_src( false, $id, 'medium', false );
			$this->assertStringContainsString( 'w:200/h:300/', $result[0] );
			$this->assertStringNotContainsString( 'g:ce/rt:fill', $result[0] );
			$this->assertEquals( 200, $result[1] );
			$this->assertEquals( 300, $result[2] );
			$this->assertFalse( $result[3] );

			$result = $this->dam->alter_attachment_image_src( false, $id, [ 50, 20 ], false );
			$this->assertStringContainsString( 'w:13/h:20/', $result[0] );
			$this->assertEquals( 13, $result[1] );
			$this->assertEquals( 20, $result[2] );
			$this->assertFalse( $result[3] );
		}
	}
	public function test_retina_images() {
		$this->settings->update( 'retina_images', 'enabled' );
		//We need to do this to apply the filters from the constructor.
		$this->dam = new Optml_Dam();
		foreach ( $this->inserted_ids as $id ) {
			$content = wp_get_attachment_image( $id, 'full' );
			$this->assertStringContainsString( 'dpr:2', $content );
		}
	}
	public function test_alter_attachment_metadata() {
		foreach ( $this->inserted_ids as $id ) {
			$metadata = wp_get_attachment_metadata( $id );

			$this->assertArrayNotHasKey( 'sizes', $metadata );


			$altered = $this->dam->alter_attachment_metadata( $metadata, $id );

			$this->assertArrayHasKey( 'sizes', $altered );

			$this->arrayHasKey( 'test_landscape', $altered['sizes'] );
			$this->assertEquals( self::IMAGE_SIZES['test_landscape']['width'], $altered['sizes']['test_landscape']['width'] );
			$this->assertEquals( self::IMAGE_SIZES['test_landscape']['height'], $altered['sizes']['test_landscape']['height'] );
			$this->assertEquals( $metadata['file'], $altered['sizes']['test_landscape']['file'] );

			$this->arrayHasKey( 'test_portrait', $altered['sizes'] );
			$this->assertEquals( self::IMAGE_SIZES['test_portrait']['width'], $altered['sizes']['test_portrait']['width'] );
			$this->assertEquals( self::IMAGE_SIZES['test_portrait']['height'], $altered['sizes']['test_portrait']['height'] );
			$this->assertEquals( $metadata['file'], $altered['sizes']['test_portrait']['file'] );
		}
	}

	public function test_alter_attachment_for_js() {
		$mock_response = [
			'sizes' => [],
			'width' => 1920,
			'height' => 1080
		];

		foreach ( $this->inserted_ids as $id ) {
			$post     = get_post( $id );
			$response = array_merge( $mock_response, [
				'url' => wp_get_attachment_url( $id ),
			] );

			$this->assertEmpty( $response['sizes'] );
			$altered = $this->dam->alter_attachment_for_js( $response, $post, [] );

			$this->assertNotEmpty( $altered['sizes'] );
			$this->assertArrayHasKey( 'test_landscape', $altered['sizes'] );
			$this->assertEquals( self::IMAGE_SIZES['test_landscape']['width'], $altered['sizes']['test_landscape']['width'] );
			$this->assertEquals( self::IMAGE_SIZES['test_landscape']['height'], $altered['sizes']['test_landscape']['height'] );
			$this->assertEquals( self::IMAGE_SIZES['test_landscape']['orientation'], $altered['sizes']['test_landscape']['orientation'] );
			$this->assertStringContainsString( 'w:200/h:100/g:ce/rt:fill', $altered['sizes']['test_landscape']['url'] );

			$this->assertArrayHasKey( 'test_portrait', $altered['sizes'] );
			$this->assertEquals( self::IMAGE_SIZES['test_portrait']['width'], $altered['sizes']['test_portrait']['width'] );
			$this->assertEquals( self::IMAGE_SIZES['test_portrait']['height'], $altered['sizes']['test_portrait']['height'] );
			$this->assertEquals( self::IMAGE_SIZES['test_portrait']['orientation'], $altered['sizes']['test_portrait']['orientation'] );
			$this->assertStringContainsString( 'w:100/h:200/g:ce/rt:fill', $altered['sizes']['test_portrait']['url'] );
		}
	}

	
	public function test_alter_image_tag_w_h() {
		$test_data = self::MOCK_ATTACHMENTS;
		$other_dimensions = [1920, 1080];

		foreach ( $this->inserted_ids as $idx => $id ) {
			$current_attachment                  = $test_data[ $idx ];

			// Set these images as thumbnails.
			$args     = [ 'width' => 150, 'height' => 150, 'crop' => true ];
			$test_url = $this->dam->replace_dam_url_args( $args, $current_attachment['url'] );
			$altered_dimensions = $this->manager->tag_replacer->filter_image_src_get_dimensions( $other_dimensions, $test_url, [], $id );

			$this->assertEquals( 150, $altered_dimensions[0] );
			$this->assertEquals( 150, $altered_dimensions[1] );

			// Test custom image sizes.
			foreach ( self::IMAGE_SIZES as $size => $image_size_args ) {

				$args = $this->dam->size_to_dimension( $size, wp_get_attachment_metadata($id) );

				$test_url = $this->dam->replace_dam_url_args( $args, $current_attachment['url'] );
				$altered_dimensions = $this->manager->tag_replacer->filter_image_src_get_dimensions( $other_dimensions, $test_url, [], $id );

				$this->assertEquals( $image_size_args['width'], $altered_dimensions[0] );
				$this->assertEquals( $image_size_args['height'], $altered_dimensions[1] );
			}

			// Test a size that doesn't correspond to an image size - should return the original dimensions.
			$args = [
				'width'  => 231,
				'height' => 512,
				'crop'   => true
			];

			$test_url = $this->dam->replace_dam_url_args( $args, $current_attachment['url'] );
			$altered_dimensions = $this->manager->tag_replacer->filter_image_src_get_dimensions( $other_dimensions, $test_url, [], $id );

			$this->assertEquals( $other_dimensions[0], $altered_dimensions[0] );
			$this->assertEquals( $other_dimensions[1], $altered_dimensions[1] );
		}
	}

	/**
	 * Get the data array for the DAM authorization from the iframe url.
	 *
	 * @param string $url The url to parse.
	 *
	 * @return array
	 */
	private function get_iframe_args_from_url( $url ) {
		$url = parse_url( $url );

		parse_str( $url['query'], $params );

		$data = $params['data'];
		$data = base64_decode( $data );

		return json_decode( $data, true );
	}

	private function insert_mock_attachments() {
		$this->inserted_ids = $this->dam->insert_attachments( self::MOCK_ATTACHMENTS );
	}
}
