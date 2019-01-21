=== Image optimization service by Optimole ===
Contributors: optimole
Tags: image optmization, cdn, image compression, compress image, images, optimization, perfomance, photos
Requires at least: 4.7
Tested up to: 5.0
Requires PHP: 5.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

End-to-end image processing
With OptiMole, your site’s images will be cropped, optimized and processed on-the-fly.


== Description ==
**Image optimization & resizing**
Images are processed for best results with both lossless and lossy optimization and are automatically resized for any device.

**Image acceleration through CDN**
If you dont use a CDN, we got you covered, we will serve the images using our default CDN. You already have one, just let us know and we can integrate yours.

**On-the-fly image handling**
Dynamic manipulation of images and videos (resize, compress and serve via CDN on the fly).

**Easy tracking & monitoring**
Check how OptiMole is improving your site from day 1. Transparent optimization stats are always available.

= How does it work? =

This plugin connects via API to OptiMole [image optimization service](https://optimole.com/) in order to send the images to its servers and crop, optimize and process them on-the-fly. The EXIF data will either be stripped and it is not stored on our servers. Optimole does not interact with the visitors on your website. We care about your privacy so check our [terms of use](https://optimole.com/terms/).

The plugin will rewrite your image URLs to replace them with OptiMole URLs. Your origin images will be downloaded from your storage, processed by the OptiMole infrastructure and cached in the CDN. NO development needed. Simply set up your account and enjoy faster image loading.


== Screenshots ==

1. Welcome screen
2. Connect screen
3. Plugin dashboard
4. Plugin settings

== Changelog ==

  #### [2.0.1](https://github.com/Codeinwp/optimole-wp/compare/v2.0.0...v2.0.1) (2019-01-21)


**Bug Fixes**

* fix javascript library production url ([aed9433](https://github.com/Codeinwp/optimole-wp/commit/aed9433))






### [2.0.0](https://github.com/Codeinwp/optimole-wp/compare/v1.1.2...v2.0.0) (2019-01-21)


#### Bug Fixes

* errors on low PHP environments when PHP_INT_MIN constant is not available ([44eb4af](https://github.com/Codeinwp/optimole-wp/commit/44eb4af))

* fix tag replacement on lazy load, preserve image size when found ([5c6ef70](https://github.com/Codeinwp/optimole-wp/commit/5c6ef70))

* improve image size replacement mapping for custom image sizes ([d816ccb](https://github.com/Codeinwp/optimole-wp/commit/d816ccb))

* improve lazyload fade in effect, fixes [#71](https://github.com/Codeinwp/optimole-wp/issues/71) ([eb0f76c](https://github.com/Codeinwp/optimole-wp/commit/eb0f76c))

* possible issue with image replacement not taking place when other buffer handler is registered ([3ce600a](https://github.com/Codeinwp/optimole-wp/commit/3ce600a))

* resize behaviour for WordPress defined image sizes, preserve cropping for custom sizes ([a42f830](https://github.com/Codeinwp/optimole-wp/commit/a42f830))



#### Documentation

* add basic faq to readme file ([2c09d26](https://github.com/Codeinwp/optimole-wp/commit/2c09d26))

* adds contributor related docs and github templates ([c7bbce2](https://github.com/Codeinwp/optimole-wp/commit/c7bbce2))



#### Features

* adds new service schema ([330fba0](https://github.com/Codeinwp/optimole-wp/commit/330fba0))

* adds watermark integration along with various options for watermark position ([6a4538a](https://github.com/Codeinwp/optimole-wp/commit/6a4538a))

* deactivate plugin on lower php versions, adds notice for PHP for upgrade ([58d2607](https://github.com/Codeinwp/optimole-wp/commit/58d2607))



#### Performance Improvements

* improve srcset replacement, use attachement id when present ([5587221](https://github.com/Codeinwp/optimole-wp/commit/5587221))

* improve type casting on size constrain ([589b046](https://github.com/Codeinwp/optimole-wp/commit/589b046))





= 1.1.2 - 2018-12-24  = 

* Minor fixes to Optimole dashboard page.
* Fixes DNS prefetch call when lazyload is off.
* Enable lazyload and javascript replacement by default for new users.


= 1.1.1 - 2018-12-10  = 

* Improve the lazy loading mechanism and adds compatibility with the new javascript library. 
* Improve sample images and quality selector integration. 
* Adds a notice when the Rest API is not available. 
* Adds notice for new users on how the plugin works. 
* Tested up with WordPress 5.0, all working smooth. 
* Fix possible issues with thumbnails when the original image is available on a different url scheme.
* Ignore lazyload replacement on feed/amp pages.


= 1.1.0 - 2018-11-16  = 

* Integrates lazy load feature with support for low-quality placeholders ( LQIP ). 
* Integrates Javascript library which delivers images at the right size for each device, including Retina.
* Improve image replacement algorithm.
* Improves compatibility with Elementor and Gutenberg. 
* Adds support for Custom CNAME and Client hints.
* Improves support for custom CDN. 
* Improves AMP support. 
* Improves compatibility with WordPress Multisites.


= 1.0.5 - 2018-10-05  = 

* Adds max width/height option.
* Improves registration workflow.
* Adds image comparison slider ratio.
* Adds logo on link preload.


= 1.0.4 - 2018-10-03  = 

* Adds in-plugin service registration.
* Adds image quality control.
* Adds slider for image comparison.
* Improvements to UX and workflow.


= 1.0.3 - 2018-09-26  = 

* Adds redirect on first install.
* Improve elementor assets replacement.


= 1.0.2 - 2018-09-25  = 

* Improve compatibility with elementor external css files.
* Adds generator tag.
* Improve replacer handler hook register.


= 1.0.1 - 2018-09-23  = 

* Tag first stable version for wordpress.org.



= 1.0.0 - 2018-09-22 =
* First version of the plugin

== Installation ==
The following are the steps to install the OptiMole plugin

1. In your WordPress Administration Panels, click on Add New option under Plugins from the menu.
Click on upload at the top.
2. Browse the location and select the OptiMole Plugin and click install now.
3. Go to Media -> OptiMole and follow in the instructions on how to enable the service.

== Frequently Asked Questions ==

= How many images I can optimize with each plan? =

The number of images that you can optimize depends on your original image size and the number of transformations you do for it.  Using the Free plan you can optimize up to 1 GB of images, which means around 2000 images at an average of 500Kb per image.

= What happens if I exceed plan limits? =

Once you exceed these, we will contact you and kindly ask to upgrade to the plan that fits you best.

= What Content Delivery Network (CDN) do you use? =

Our FREE plan uses our custom made CDN built with 7 locations around the globe. For the paid plans, we have direct integration with Amazon Cloudfront, with more than 130 locations around the globe.

= I'm already using a CDN, can I use that instead of yours ? =

Short answer, YES. We can help you integrate your default CDN but it will require some additional work from our side and is available to Enterprise plans.

= I'm already using an image optimization plugin, why should I switch to OptiMole? =

You don’t need to change your existing optimization plugin, image optimization is just a small part of what we do, if you are happy with ShortPixel for e.g, feel free to continue to use it, OptiMole would then take care only of serving your image at the RIGHT size, advanced cropping and smart lazy-loading.

= Will the original images be deleted? =

Absolutely No. We use your original images as sources when deliver the optimized images.

= What is the difference between the Auto, High, Medium, Low compression levels? =

A higher compression might result in a small loss of image quality. Selecting the auto level will let Optimole choose the minimum size with no loss in the quality of your picture.

= I used Kraken, Shortpixel, Optimus, EWWW or WP Smush, Imagify  will OptiMole further optimize my images? =

 Yes, Optimole will also take care of serving your image at the RIGHT size for your visitors and optimize them to the best possible format for their browser.

= Which formats can be optimized ? =

For now we support jpg, png and svg format.

= Does Optimole automatically serve WebP for Chrome users ? =

Yes. We automatically detect user browser and serve WebP if is supported, otherwise we optimize the image in the original format.

= Can i disable lazyload for PNG images ? =

Yes. You need to add `define("OPTML_DISABLE_PNG_LAZYLOAD",true);` to `your wp-config.php` file.

= Can i disable optimization for a certain image ? =

Yes, you can follow this code snippet and replace the sample image with the one you need:
<code>
add_filter('optml_dont_replace_url', function( $old, $url ) {
	if ( $url === 'https://example.com/wp-content/uploads/2018/09/1.jpg' ) {
		return true;
	}
	return $old;

}, 10, 2);
</code>