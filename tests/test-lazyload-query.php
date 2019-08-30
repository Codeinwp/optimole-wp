<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Lazyload_Query extends WP_UnitTestCase
{

    const IMG = '<img src="https://example.org/photos/814499/pexels-photo-814499.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="">';


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
        $settings->update('lazyload', 'enabled');
        $settings->update('img_to_video', 'enabled');


        Optml_Url_Replacer::instance()->init();
        Optml_Tag_Replacer::instance()->init();
        Optml_Manager::instance()->init();

        self::$sample_post = self::factory()->post->create([
                'post_title' => 'Test post',
                'post_content' => self::IMG
            ]
        );
    }
    public function test_should_replace_url()
    {
        $replaced_content = Optml_Manager::instance()->replace_content(self::IMG);
        var_dump($replaced_content);
        $this->assertContains('i.optimole.com', $replaced_content);
        $this->assertContains('data-opt-src', $replaced_content);
    }
}