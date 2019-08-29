<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Background_Image extends WP_UnitTestCase
{

    const BACKGROUND_IMG = '<div data-hash="5c2602bbe76e6f966a846fb661b9d76d" data-desktop="//366b4e08.ngrok.io/wp-content/uploads/2019/08/image3-10-5.jpg" class="n2-ss-slide-background-image" data-blur="0" style="background-image:url("//example.org/wp-content/themes/test/assets/images/header-300x300.png");">  </div>';
    const BACKGROUND_IMG_SECOND = '<div data-hash="5c2602bbe76e6f966a846fb661b9d76d" data-desktop="://www.example.org/wp-content/uploads/2018/05/brands2.png?test=123"" class="n2-ss-slide-background-image" data-blur="0" style="background-image:url("//example.org/wp-content/themes/test/assets/images/header-300x300.png");">  </div>';


    public static $sample_post;

    public function setUp()
    {

        parent::setUp();
        $settings = new Optml_Settings();
        $settings->update('service_data', [
            'cdn_key' => 'test123',
            'cdn_secret' => '12345',
            'whitelist' => ['example.com'],

        ]);
        $settings->update('lazyload', 'disabled');
        $settings->update('img_to_video', 'enabled');


        Optml_Url_Replacer::instance()->init();
        Optml_Tag_Replacer::instance()->init();
        Optml_Manager::instance()->init();

        self::$sample_post = self::factory()->post->create([
                'post_title' => 'Test post',
                'post_content' => self::BACKGROUND_IMG
            ]
        );
    }

    public function test_should_replace_tag()
    {

        $replaced_content = Optml_Manager::instance()->replace_content(self::BACKGROUND_IMG);
        $this->assertContains('i.optimole.com', $replaced_content);
        $replaced_content = Optml_Manager::instance()->replace_content(self::BACKGROUND_IMG_SECOND);
        $this->assertContains('i.optimole.com', $replaced_content);
    }
}

