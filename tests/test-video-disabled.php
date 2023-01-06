<?php

/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Video_Tag_Disabled extends WP_UnitTestCase
{

    const IMG_TAGS_GIF = '<div id="wp-custom-header" class="wp-custom-header">
                        <img src="https://www.example.org/wp-content/uploads/2018/05/brands.gif"/>
                        </div> <div> <img src="https://www.example.org/wp-content/uploads/2018/05/image.gif"/>  </div>';

    public static $sample_post;

    public function setUp() : void
    {

        parent::setUp();
        $settings = new Optml_Settings();
        $settings->update('service_data', [
            'cdn_key' => 'test123',
            'cdn_secret' => '12345',
            'whitelist' => ['example.com'],

        ]);


        Optml_Url_Replacer::instance()->init();
        Optml_Tag_Replacer::instance()->init();
        Optml_Manager::instance()->init();

        self::$sample_post = self::factory()->post->create([
                'post_title' => 'Test post',
                'post_content' => self::IMG_TAGS_GIF
            ]
        );
    }
    public function test_should_replace_disabled()
    {
        $replaced_content = Optml_Manager::instance()->process_images_from_content(self::IMG_TAGS_GIF);
        $this->assertStringNotContainsString('<video autoplay muted loop playsinline poster', $replaced_content);
        $this->assertStringNotContainsString('type="video/mp4', $replaced_content);
        $this->assertStringNotContainsString('/f:mp4', $replaced_content);
    }
}