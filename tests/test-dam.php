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
		$this->settings = $plugin->admin->settings;

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
		$ids = $this->dam->insert_attachments( self::MOCK_ATTACHMENTS );

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
			$this->assertTrue( in_array( $id, $third_run ) );
		}
	}

	public function test_dam_size_gravity_replacer() {
		$url_map = [
			'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:testId/https://test-site.test/9.jpg' => [
				'expected' => 'https://cloudUrlTest.test/w:150/h:100/q:auto/id:testId/https://test-site.test/9.jpg',
				'args'     => [ 'width' => 150, 'height' => 100 ]
			],
			'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:testId/directUpload/9.jpg'           => [
				'expected' => 'https://cloudUrlTest.test/w:365/h:1200/g:ce/rt:fill/q:auto/id:testId/directUpload/9.jpg',
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
		$this->assertEquals( 'https://cloudUrlTest.test/w:150/h:100/g:sm/rt:fill/q:auto/id:testId/https://test-site.test/9.jpg', $processed );

		$this->settings->update( 'resize_smart', 'disabled' );
	}

	public function test_alter_attachment_image_src() {
		foreach ( $this->inserted_ids as $id ) {
			$result = $this->dam->alter_attachment_image_src( false, $id, 'test_landscape', false );
			$this->assertStringContainsString( 'w:200/h:100/g:ce/rt:fill', $result[0] );
			$this->assertEquals( 200, $result[1] );
			$this->assertEquals( 100, $result[2] );
			$this->assertTrue( $result[3] );

			$result = $this->dam->alter_attachment_image_src( false, $id, 'test_portrait', false );
			$this->assertStringContainsString( 'w:100/h:200/g:ce/rt:fill', $result[0] );
			$this->assertEquals( 100, $result[1] );
			$this->assertEquals( 200, $result[2] );
			$this->assertTrue( $result[3] );

			$result = $this->dam->alter_attachment_image_src( false, $id, 'medium', false );
			$this->assertStringContainsString( 'w:300/h:300/', $result[0] );
			$this->assertStringNotContainsString( 'g:ce/rt:fill', $result[0] );
			$this->assertEquals( 300, $result[1] );
			$this->assertEquals( 300, $result[2] );
			$this->assertFalse( $result[3] );

			$result = $this->dam->alter_attachment_image_src( false, $id, [ 50, 20 ], false );
			$this->assertStringContainsString( 'w:50/h:20/g:ce/rt:fill', $result[0] );
			$this->assertEquals( 50, $result[1] );
			$this->assertEquals( 20, $result[2] );
			$this->assertEquals( true, $result[3] );
			$this->assertTrue( $result[3] );
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

	public function test_alter_attachment_for_js(  ) {
		$mock_response = [
			'sizes' => [],
		];

		foreach ( $this->inserted_ids as $id ) {
			$post = get_post( $id );
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
