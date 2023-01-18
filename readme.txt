=== Image optimization & Lazy Load by Optimole ===
Contributors: optimole
Tags: image optimization, convert webp, responsive images, lazy load, images, optimization, performance, photos, free cdn
Requires at least: 4.7
Tested up to: 6.1
Requires PHP: 5.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Automatically compress, optimize and serve scaled images as well as convert WebP & AVIF all from CloudFront CDN. Lazy load included ⚡

== Description ==

> **What makes Optimole so special?**
>
> * Smaller images. Same quality
> * Fully automated; [set and forget](https://docs.optimole.com/article/1173-how-to-get-started-with-optimole-in-just-3-steps)
> * Supports all image types
> * Supports both Retina and WebP images
> * Machine Learning(ML) powered automatic compression
> * AVIF format support for lower image size
> * Cloud image library support
> * Serves images from a global CDN for free
> * Optimizes based on the visitor's actual device (no more guesswork and width estimations)
> * Full support for page builders like Elementor
> * Lazy load without jQuery (better)
> * Fully functional free version (we have paid plans as well)
>

Optimole is the all-in-one solution to all of your image optimization needs. With full automation and a range of evolutionary features, Optimole makes it easy to clean up your heavy images and bloaty pages.

Optimole [optimizes](https://www.codeinwp.com/blog/how-to-optimize-images/) your images in real-time with a cloud-based system to [speed up your website](https://optimole.com/website-speed-optimization-and-how-to-speed-up-wordpress/) and deliver high-quality images perfectly sized for every device. With a one-click setup and minimal footprint, you can start improving your site in minutes.

You'll be free to concentrate on other aspects of your site with Optimole's set and forget image optimization.

So, if you have been looking for one plugin that provides lazy loading with a CDN and perfectly sized images, then Optimole is perfect for you.


**How does it do all these great things?**
Optimole's format based optimization is handled in the cloud on a case-by-case basis. Once you have the plugin installed, Optimole will replace all of your image URLs with cloud-based URLs. We don't replace any images on your website, we compress images on the fly.
When the image is requested, Optimole will apply the specific transformations required by the device and deliver it to your visitors. This means every image is perfectly sized for every device. The final result will be cached for a month to ensure ongoing fast delivery and more site speed.

**How many images can be optimized?**
With the basic plan, you will be able to optimize unmetered number of images for up to 5k monthly [visits](https://docs.optimole.com/article/1134-how-optimole-counts-the-number-of-visitors). All delivered from more than 200+ locations around the globe.
Better yet. The free version is fully functional and includes all of the following great features:

**Format Based Optimization**
Our cloud-based transformation process means we can optimize images based on the format as well as serve images in next-gen formats. If your visitor is using a WebP capable browser, then Optimole will convert to WebP the image and send it to their device. AVIF support included.

**Cloud Library support**
Offload your website images directly to Optimole Cloud and save storage space on your server. Cross share images between your all your websites connected to Optimole.

**Image Optimization**
Our algorithms crunch the numbers to provide the best lossy or lossless optimization for the best-looking image at the smallest size using Machine Learning(ML) powered compression.

**Exact Used Size**
Optimole will use just one image and resize it delivering a responsive image to fit perfectly on your visitors' devices. No more awkward guesses at potential screen widths. Serve scaled images instantly.

**Retina Support**
Optimole can detect Retina screens and deliver an image with the perfect Digital Pixel Ratio (DPR).

**Smart Cropping**
If you'd like to keep the most interesting part of an image; then you can enable smart cropping to help keep the image well sized without losing focus.

**No Content Shifting**
The lazy load option defer offscreen images and is perfectly sized for the container to provide a seamless viewing experience without any content shifting.

**Watermarks**
Who has time for adding watermarks? Optimole will do the hard work for you. Just set it up and pick your preferred location and Optimole will add the watermark to all of your future images.

**Downgrade Quality For Slower Connections**
Optimole provides an option to downgrade the image quality when it detects a slower network. Efficiently encode images by making up to 40% smaller with this neat feature to help visitors in a bottleneck.

**Compatibility**
Optimole loves page builders and has unique tweaks to solve image replacements. It also has full compatibility with the new block editor since WordPress 5.0

**CDN**
Optimole provides free access to a AWS CloudFront CDN with edge locations in more than 200 cities around the globe.

**What About Security?**
The stripped EXGIF data is not stored on our service. Optimole likes to work behind the scenes, and won't interact with your site's visitors. No data is collected but you can check the [Terms of Service](https://optimole.com/terms/)

**Smooth And Clean**
Optimole can be installed in a few clicks and then left in the back-end to do its job. Not happy with it? Optimole has a clean uninstall and your site will be just as before Optimole was installed.

**Go Pro**
Premium users will be able to optimize images starting with more than 25k monthly active users. Images in the Premium plan are served from AWS Cloudfront with over 200 locations all over the world.

== Screenshots ==

1. Welcome screen
2. Connect screen
3. Plugin dashboard
4. Plugin settings

== Changelog ==

##### [Version 3.5.5](https://github.com/Codeinwp/optimole-wp/compare/v3.5.4...v3.5.5) (2023-01-18)

* Updated iframe lazyload exclusion flags to include the default flags
* Fixed offload media compatibility with all php versions above 5.4




##### [Version 3.5.4](https://github.com/Codeinwp/optimole-wp/compare/v3.5.3...v3.5.4) (2023-01-06)

* Fixed image deduplication on media offload




##### [Version 3.5.3](https://github.com/Codeinwp/optimole-wp/compare/v3.5.2...v3.5.3) (2022-12-12)

* Improve compatibility with WPML plugin




##### [Version 3.5.2](https://github.com/Codeinwp/optimole-wp/compare/v3.5.1...v3.5.2) (2022-11-11)

* Fixed media offload nonce update




##### [Version 3.5.1](https://github.com/Codeinwp/optimole-wp/compare/v3.5.0...v3.5.1) (2022-11-04)

* Improve media offload estimation time.
* Improve gif to video conversion.
* Accessibility improvements to docs and external links.
* Improve media offload process for sites with thousands of images. 
* Improve welcome notice




#### [Version 3.5.0](https://github.com/Codeinwp/optimole-wp/compare/v3.4.6...v3.5.0) (2022-10-17)

* Improved images offload speed by marking processed pages
* Adds conflict validation before the rollback process
* Updated the logging for images that fail when offloading
* Enhance Elementor compatibility




##### [Version 3.4.6](https://github.com/Codeinwp/optimole-wp/compare/v3.4.5...v3.4.6) (2022-09-08)

* Updated cache buster format
* Adds validation for width/height values according to HTML standards




##### [Version 3.4.5](https://github.com/Codeinwp/optimole-wp/compare/v3.4.4...v3.4.5) (2022-08-22)

#### Features
* Allows users to add cropped image sizes from within the plugin settings
* Adds an option to exclude a page path from optimization using exact matching
* Adds the option to create and connect an account with one click
* Adds filter, <code>optml_keep_copyright</code> , to control if image optimization should keep copyright metadata 
#### Fixes
* Enhances compatibility with Beaver builder to optimize images in javascript files 
* Enhances compatibility with Cache enabler to use the latest plugin's filters and clears page cache when Optimole's settings are updated
* Updates compatibility with Divi theme/builder to optimize images in the static css/js files and regenerate those files when Optimole's settings are updated
* Enhances compatibility with Elementor to update the optimized images in the generated css files upon changing Optimole's settings
* Adds the latest lazyload exclusion flags for Slider Revolution
* Enhances W3 Total Cache compatibility to clear the cache when Optimole's settings are updated
* Updates compatibility with YITH WooCommerce Quick View to optimize quick view images




##### [Version 3.4.4](https://github.com/Codeinwp/optimole-wp/compare/v3.4.3...v3.4.4) (2022-07-14)

* Enhance WooCommerce and WPBakery compatibilities when users are offloading the images to Optimole cloud.
* Improve compatibility with all plugins that are editing the media modal tabs.




##### [Version 3.4.3](https://github.com/Codeinwp/optimole-wp/compare/v3.4.2...v3.4.3) (2022-05-30)

* Enhance Thrive compatibility when users are offloading the images to Optimole cloud.




##### [Version 3.4.2](https://github.com/Codeinwp/optimole-wp/compare/v3.4.1...v3.4.2) (2022-05-25)

* Fix edge cases for auto allowing domain on site migration.




##### [Version 3.4.1](https://github.com/Codeinwp/optimole-wp/compare/v3.4.0...v3.4.1) (2022-05-10)

* Auto allow domain when the website URL is being changed, such as moving from production -> staging or viceversa




#### [Version 3.4.0](https://github.com/Codeinwp/optimole-wp/compare/v3.3.5...v3.4.0) (2022-04-18)

* Adds Machine Learning(ML) quality compression which will predict the right quality for your image in order to get the smallest possible size with minimum perceived quality losses, delivering images with ~40% smaller size than the current solution.
* Adds AVIF format conversion enabled by default for everyone. 
* Fix edge case when content URL is relative and prevents Optimole from replacing the URLs.




##### [Version 3.3.5](https://github.com/Codeinwp/optimole-wp/compare/v3.3.4...v3.3.5) (2022-03-31)

#### Fixes
- updates compatibility with FacetWP 
- fixes warning regarding image size calculation




##### [Version 3.3.4](https://github.com/Codeinwp/optimole-wp/compare/v3.3.3...v3.3.4) (2022-03-25)

* Add support for HEIC/AVIF formats as source input




##### [Version 3.3.3](https://github.com/Codeinwp/optimole-wp/compare/v3.3.2...v3.3.3) (2022-03-18)

#### Fixes
- adds filter <code>optml_gif_to_video_flags</code> to exclude GIF placeholders from video conversion
- adds compatibility with Avada live to remove replacement in edit mode




##### [Version 3.3.2](https://github.com/Codeinwp/optimole-wp/compare/v3.3.1...v3.3.2) (2022-03-17)

#### Fixes
* Hardening security for users with administrator roles. 
* Update dependencies to the latest version.




##### [Version 3.3.1](https://github.com/Codeinwp/optimole-wp/compare/v3.3.0...v3.3.1) (2022-03-10)

#### Features
- Adds filter, <code>optml_should_avif_ext</code> , for more control over which images are converted to AVIF, by default SVG images are not converted
#### Fixes
- Plugin interface header display size on safari




#### [Version 3.3.0](https://github.com/Codeinwp/optimole-wp/compare/v3.2.1...v3.3.0) (2022-02-25)

#### Features
* Adds opt-in AVIF compatibility, improving the image optimization savings on average with at 30%
* Major dashboard UI/UX improvements make it cleaner and easier to use.
* Adds separate functionality for clearing only CSS/JS when Optimole is serving those.
* Improve Optimole Cloud optimizations speed and performances




##### [Version 3.2.1](https://github.com/Codeinwp/optimole-wp/compare/v3.2.0...v3.2.1) (2021-10-01)

* Fix issue when the quota exceeded message shows up on new connections. 
* Fix local JS loading of lazyload library.




#### [Version 3.2.0](https://github.com/Codeinwp/optimole-wp/compare/v3.1.3...v3.2.0) (2021-09-28)

#### Features
- Improve media cloud offloading by making faster image handling for large sites

#### Fixes
- Adds compatibility with FaceWP
- Improve compatibility with WP Rest Cache plugin
- Improve compatibility with Woocommerce variations plugin
- Improve usage of WP Rest API endpoints
- Improve handling of files with non-media files which are stored as attachments
- Fix print of pages which uses Optimole lazyload




##### [Version 3.1.3](https://github.com/Codeinwp/optimole-wp/compare/v3.1.2...v3.1.3) (2021-08-06)

* Preserve selected custom domain on stats refresh when multiple custom domains are used.




##### [Version 3.1.2](https://github.com/Codeinwp/optimole-wp/compare/v3.1.1...v3.1.2) (2021-08-04)

* Adds a filter to force replacements as optml_force_replacement
* Fix content path being root directory




##### [Version 3.1.1](https://github.com/Codeinwp/optimole-wp/compare/v3.1.0...v3.1.1) (2021-05-31)

* Adds option to setup API key via wp-config variables fix [#314](https://github.com/Codeinwp/optimole-wp/issues/314) as OPTIML_API_KEY
* Fix lazyload on video tag working improperly [#368](https://github.com/Codeinwp/optimole-wp/issues/368)
* Adds filter for Optimole processed URLs  as optml_processed_url 
* Fix error message when a user is already registered with the same email




#### [Version 3.1.0](https://github.com/Codeinwp/optimole-wp/compare/v3.0.1...v3.1.0) (2021-05-13)

* Adds support for multiple custom domains
* Adds option to skip first X images from lazyloading
* Adds support for async decoding for image tags
* Adds support for video lazyload
* Improve media offloading and rollback mechanism 
* Adds support for the new watermark feature




##### [Version 3.0.1](https://github.com/Codeinwp/optimole-wp/compare/v3.0.0...v3.0.1) (2021-03-16)

* improve behavior on browsers that don't support javascript, causing issues with some 3rd party plugins.
* server lazyload script from the same domain avoiding extra DNS checks and improving loading




#### [Version 3.0.0](https://github.com/Codeinwp/optimole-wp/compare/v2.5.7...v3.0.0) (2021-02-23)

### Features
- Adds option to offload images to Optimole Cloud, saving space on your server storage. 
- Adds Optimole Cloud integration directly in the Media library, allowing you to cross-share images from all the connected sites. 
### Fixes 
- Improve compatibility with Background images lazyload for Elementor




##### [Version 2.5.7](https://github.com/Codeinwp/optimole-wp/compare/v2.5.6...v2.5.7) (2020-12-17)

#### Fixes
* Adds iframe style for lazyloading only when the iframe is used on the current page
* Improve lazyload CLS web vital
* Adds compatibility with Smart Search for Woocommerce

#### Features
* Adds option to set the plugin settings via [wp-config](https://docs.optimole.com/article/1316-setting-plugin-options-via-wp-config)constants  
* Adds more option to set plugin settings via [wp cli](https://docs.optimole.com/article/1202-plugin-cli-commands)


[See all versions.](https://github.com/Codeinwp/optimole-wp/releases)
== Installation ==
The following are the steps to install the OptiMole plugin

1. In your WordPress Administration Panels, click on Add New option under Plugins from the menu.
Click on upload at the top.
2. Browse the location and select the OptiMole Plugin and click install now.
3. Go to Media -> OptiMole and follow in the instructions on how to enable the service.

== Frequently Asked Questions ==

= How many images I can optimize with each plan? =

The number of images that you can optimize or store is unlimited, we care only about the number of visits you have per month.

= What happens if I exceed plan limits? =

Once you exceed these, we will contact you and kindly ask to upgrade to the plan that fits you best.

= What Content Delivery Network (CDN) do you use? =

For both FREE and Paid plans we use AWS CloudFront CDN with more than 200 locations around the globe.

= I'm already using a CDN, can I use that instead of yours ? =

Short answer, YES. You will only need to whitelist the domain of your existing CDN for Optimole to pick images stored there.

= I'm already using an image optimization plugin, why should I switch to Optimole? =

You don’t need to change your existing optimization plugin, image optimization is just a small part of what we do, if you are happy with ShortPixel for e.g, feel free to continue to use it, Optimole would then take care only of serving your image at the RIGHT size, advanced cropping and smart lazy-loading.

= Does Optimole handle images from Ajax content  ? =

Yes, we do. By default, Optimole handle images delivered from your ajax content from admin-ajax.php ( not logged in users ) as well as WordPress REST API routes.

= Can I remove the blurry placeholder from the lazyload effect ? =

Yes, you can. You only need to toggle this feature in the plugin as explained per this doc - https://docs.optimole.com/article/1020-can-i-remove-the-blurry-placeholder-from-the-lazyload-effct

= Will the original images be deleted? =

We use your original images as sources when deliver the optimized images. Unless you want to remove images from your server to save some space with Cloud Library by offloading images to the cloud as explained here - https://docs.optimole.com/article/1323-cloud-library-browsing

= What is the difference between the Auto, High, Medium, Low compression levels? =

A higher compression might result in a small loss of image quality. Selecting the auto level will let Optimole choose the minimum size with no loss in the quality of your picture.

= I used Kraken, Shortpixel, Optimus, EWWW or WP Smush, Imagify  will Optimole further optimize my images? =

Yes, Optimole will also take care of serving your image at the RIGHT size for your visitors and optimize them to the best possible format for their browser.

= Which formats can be optimized ? =

For now we support jpg, png and svg format.

= Does Optimole automatically serve WebP for Chrome users ? =

Yes. We automatically detect user browser and serve WebP if is supported, otherwise we optimize the image in the original format.

= Can I disable lazyload for PNG images ? =

Yes. You need to add `define("OPTML_DISABLE_PNG_LAZYLOAD",true);` to `your wp-config.php` file.

You can also use plugin's UI and exclude images by their type as explained here - https://docs.optimole.com/article/1191-exclude-from-optimizing-or-lazy-loading

= Can I disable optimization for a certain image ? =

Yes, you can exclude certain image by its name as explained here - https://docs.optimole.com/article/1191-exclude-from-optimizing-or-lazy-loading

In case you're tech-savvy and want programatically exclude images by some conditions you can follow this code snippet and replace the sample image with the one you need or have other conditions:

<code>
add_filter('optml_dont_replace_url', function( $old, $url ) {
	if ( $url === 'https://example.com/wp-content/uploads/2018/09/1.jpg' ) {
		return true;
	}
	return $old;

}, 10, 2);
</code>
