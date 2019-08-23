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

    public function setUp() {

        parent::setUp();
        $settings = new Optml_Settings();
        $settings->update( 'service_data', [
            'cdn_key'    => 'test123',
            'cdn_secret' => '12345',
            'whitelist'  => [ 'encrypted-tbn0.gstatic.com' ],
            'img_to_video' => 'enabled'

        ] );
        $settings->update( 'lazyload', 'disabled' );

        Optml_Url_Replacer::instance()->init();
        Optml_Tag_Replacer::instance()->init();
        Optml_Manager::instance()->init();

        self::$sample_post        = self::factory()->post->create( [
                'post_title'   => 'Test post',
                'post_content' => self::IMG_TAGS
            ]
        );
        self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );

    }
    public function test_replace_tag () {
        $image_url = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQK4kPpuaFciC2HriDrMGbBpnbOVMoIwCAa08l5q20ZUOWU067E";
        $img_tag = '<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQK4kPpuaFciC2HriDrMGbBpnbOVMoIwCAa08l5q20ZUOWU067E" >';
        $content          = '<div class="before-footer">
			<div class="codeinwp-container">
				<p class="featuredon">Featured On</p>
				<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQK4kPpuaFciC2HriDrMGbBpnbOVMoIwCAa08l5q20ZUOWU067E"> 
			</div>
		</div>';
        $this -> assertTrue( Optml_Tag_Replacer::instance()->img_to_video($image_url, $img_tag, $content ));
        $this->assertContains( 'i.optimole.com', $content );
        $this->assertContains( 'https://encrypted-tbn0.gstatic.com', $content );
      }
}