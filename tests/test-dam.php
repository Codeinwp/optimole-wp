<?php

/**
 * Class Test_Dam.
 */
class Test_Dam extends WP_UnitTestCase {

	const TEST_API_KEY = 'testkey';

	/**
	 * @var Optml_Settings
	 */
	private $settings;

	/**
	 * @var Optml_Dam
	 */
	private $dam;

	public function setUp() : void {
		parent::setUp();

		$plugin         = Optml_Main::instance();
		$this->dam      = $plugin->dam;
		$this->settings = $plugin->admin->settings;

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
		$images = [
			[
				'url'  => 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg',
				'meta' =>
					[
						'originalHeight' => 1800,
						'originalWidth'  => 1200,
						'updateTime'     => 1688553629048,
						'resourceS3'     => 's3flag1',
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
						'resourceS3'     => 's3flag2',
						'mimeType'       => 'image/jpeg',
						'userKey'        => 'mlckcuxuuuyb',
						'fileSize'       => 171114,
						'originURL'      => 'https://test-site.test/wp-content/uploads/2023/07/10.jpg',
						'domain_hash'    => 'dWwtcG9sZWNhdC15dWtpLmluc3Rhd3AueHl6',
					],
			],
		];

		$attachments = $this->dam->insert_attachments( $images );

		foreach ( $attachments as $index => $id ) {
			$this->assertIsInt( $id );
			$this->assertGreaterThan( 0, $id );

			$attachment = get_post( $id );

			$this->assertEquals( $images[ $index ]['url'], $attachment->guid );
			$this->assertEquals( $images[ $index ]['meta']['mimeType'], $attachment->post_mime_type );
		}

	}

	public function test_insert_duplicates() {
		$images = [
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

		$ids = $this->dam->insert_attachments( $images );

		$this->assertIsArray( $ids );
		$this->assertNotEmpty( $ids );

		$duplicates = $this->dam->insert_attachments( $images );

		$this->assertIsArray( $duplicates );
		$this->assertNotEmpty( $duplicates );
		$this->assertEquals( $ids, $duplicates );

		$images = array_merge(
			$images,
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

		$this->assertArraySubset( $ids, $third_run );
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
}
