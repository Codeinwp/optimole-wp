<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Video_Tag extends WP_UnitTestCase {

    const IMG_TAGS_GIF = '<div id="wp-custom-header" class="wp-custom-header">
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands.gif"/>
                        </div> <div> <img src="https://www.example.org/wp-content/uploads/2018/05/image.gif"/>  </div>';
    const IMG_TAGS_NOT_GIF = '<div id="wp-custom-header" class="wp-custom-header">
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands.png"/>
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands.jpg"/>
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands.webp"/>
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands"/>
                     </div>';

    public static $sample_post;
    public function setUp() {

        parent::setUp();
        $settings = new Optml_Settings();
        $settings->update( 'service_data', [
            'cdn_key'    => 'test123',
            'cdn_secret' => '12345',
            'whitelist'  => [ 'example.com' ],

        ] );
        $settings->update( 'lazyload', 'disabled' );
        $settings->update( 'img_to_video', 'enabled' );


        Optml_Url_Replacer::instance()->init();
        Optml_Tag_Replacer::instance()->init();
        Optml_Manager::instance()->init();

        self::$sample_post        = self::factory()->post->create( [
                'post_title'   => 'Test post',
                'post_content' => self::IMG_TAGS_GIF
            ]
        );
    }
    public function test_should_replace_tag () {

        $replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_GIF );

        $this->assertContains( 'i.optimole.com', $replaced_content );
        $this->assertContains( '<video autoplay muted loop playsinline poster', $replaced_content );
        $this->assertContains( 'type="video/mp4', $replaced_content );
        $this->assertContains( '/f:mp4', $replaced_content );
        $this->assertContains( 'https://www.example.org', $replaced_content );
      }
    public function test_should_not_replace_tag () {

        $replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_NOT_GIF );
        $this->assertNotContains( '<video autoplay muted loop playsinline poster', $replaced_content );
        $this->assertNotContains( 'type="video/mp4', $replaced_content );
        $this->assertNotContains( '/f:mp4', $replaced_content );

    }
}