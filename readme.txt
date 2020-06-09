=== Image optimization & Lazy Load by Optimole ===
Contributors: optimole
Tags: image optimization, convert webp, responsive images, lazy load, images, optimization, performance, photos
Requires at least: 4.7
Tested up to: 5.4
Requires PHP: 5.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Automatically compress, optimize and serve scaled images along with next-gen WebP all from CloudFront CDN. Lazy load included ⚡

== Description ==

> **What makes Optimole so special?**
>
> * Smaller images. Same quality
> * Fully automated; [set and forget](https://docs.optimole.com/article/1173-how-to-get-started-with-optimole-in-just-3-steps)
> * Supports all image types
> * Supports both Retina and WebP images
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
Our cloud-based transformation process means we can optimize images based on the format as well as serve images in next-gen formats. If your visitor is using a WebP capable browser, then Optimole will convert to WebP the image and send it to their device.

**Image Optimization**
Our algorithms crunch the numbers to provide the best lossy or lossless optimization for the best-looking image at the smallest size.

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
Optimole loves page builders and has unique tweaks to solve image replacements. It also has full compatibility with the new block editor in WordPress 5.0

**CDN**
Optimole provides free access to a AWS CloudFront CDN with edge locations in more than 200 cities around the globe.

**What About Security?**
The stripped EXGIF data is not stored on our service. Optimole likes to work behind the scenes, and won't interact with your site's visitors. No data is collected but you can check the [Terms of Service](https://optimole.com/terms/)

**Smooth And Clean**
Optimole can be installed in a few clicks and then left in the back-end to do its job. Not happy with it? Optimole has a clean uninstall and your site will be just as before Optimole was installed.

**Go Pro**
Premium users will be able to optimize images for more than 25k monthly active users. Images in the Premium plan are served from AWS Cloudfront with over 200 locations all over the world.

== Screenshots ==

1. Welcome screen
2. Connect screen
3. Plugin dashboard
4. Plugin settings

== Changelog ==

##### [Version 2.4.1](https://github.com/Codeinwp/optimole-wp/compare/v2.4.0...v2.4.1) (2020-06-09)

* Improves settings UI for CSS/JS toggle
* Improves settings description for autoscaling toggle.




#### [Version 2.4.0](https://github.com/Codeinwp/optimole-wp/compare/v2.3.1...v2.4.0) (2020-06-08)

#### Features: 
- Adds the option to serve CSS/JS via Optimole.
- Adds the option to serve minified CSS/JS files. 
- Adds the option to disable scaling but keep lazyload. 

#### Fixes: 
- Optimize images that are hardcoded in the CSS/JS files.
- Improve notice feedback when the quota is exceeded.




##### [Version 2.3.1](https://github.com/Codeinwp/optimole-wp/compare/v2.3.0...v2.3.1) (2020-04-21)

#### Bug Fixes
- improve AMP compatibility, solve the issue when we were loading non-AMP resources on AMP context, props [@westonruter](https://github.com/westonruter) 
- improve lazy-loading on non-Latin image filenames




#### [Version 2.3.0](https://github.com/Codeinwp/optimole-wp/compare/v2.2.9...v2.3.0) (2020-04-06)

#### Features
- Adds clear image cache feature
#### Fixes
- Improve compatibility with Pinterest sharing plugins
- Improve compatibility with Unicode image names.




#### [Version 2.2.9](https://github.com/Codeinwp/optimole-wp/compare/v2.2.8...v2.2.9) (2020-02-25)

* **Bug Fixes**
   * compatibility with Fusion builder, remove replacement when in edit mode ([b16683b](https://github.com/Codeinwp/optimole-wp/commit/b16683b))
   * improve compatibility with Divi builder ([efdabfe](https://github.com/Codeinwp/optimole-wp/commit/efdabfe))



#### [Version 2.2.8](https://github.com/Codeinwp/optimole-wp/compare/v2.2.7...v2.2.8) (2020-01-14)

* **Bug Fixes**
   * catch image URLs that contains some symbols ([ce1c162](https://github.com/Codeinwp/optimole-wp/commit/ce1c162))
   * compatibility with Sassy Social share plugin ([d4224cb](https://github.com/Codeinwp/optimole-wp/commit/d4224cb))
   * consider image URLs that contains chars like ~ ([670597e](https://github.com/Codeinwp/optimole-wp/commit/670597e))
   * improve LQIP transition effect, fix [#212](https://github.com/Codeinwp/optimole-wp/issues/212) ([946e16c](https://github.com/Codeinwp/optimole-wp/commit/946e16c))
   * prevent losing filters settings after disconnecting the api key, fix [#210](https://github.com/Codeinwp/optimole-wp/issues/210) ([db431b9](https://github.com/Codeinwp/optimole-wp/commit/db431b9))
   * removes custom logo preload causing duplicate content issues ([04cad07](https://github.com/Codeinwp/optimole-wp/commit/04cad07))
   * strip script tag on amp modes fix [#203](https://github.com/Codeinwp/optimole-wp/issues/203) ([35f59f3](https://github.com/Codeinwp/optimole-wp/commit/35f59f3))

* **Features**
   * adds data-skip-lazy attr for skipping lazyload ([8726127](https://github.com/Codeinwp/optimole-wp/commit/8726127))
   * adds wp-cli commands for most common operations ([577406b](https://github.com/Codeinwp/optimole-wp/commit/577406b))



#### [Version 2.2.7](https://github.com/Codeinwp/optimole-wp/compare/v2.2.6...v2.2.7) (2019-12-17)

* **Bug Fixes**
   * edge case lazyload replacement when noscript images are present ([c788c3f](https://github.com/Codeinwp/optimole-wp/commit/c788c3f))
   * improve compatibility with Divi builder ([7d1c469](https://github.com/Codeinwp/optimole-wp/commit/7d1c469))
   * improve compatibility with W3TC minification system ([bf9f058](https://github.com/Codeinwp/optimole-wp/commit/bf9f058))
   * improve compatibility with WP Migrate DB ([02df077](https://github.com/Codeinwp/optimole-wp/commit/02df077))
   * improve lazyload and replacement on json strings ([b7f67fd](https://github.com/Codeinwp/optimole-wp/commit/b7f67fd))
   * removed blur from lazyload placeholders ([2c7d66d](https://github.com/Codeinwp/optimole-wp/commit/2c7d66d))

* **Features**
   * adds compatibility for schemaless and relative image URLs ([e3886eb](https://github.com/Codeinwp/optimole-wp/commit/e3886eb))
   * hide Optimole menu via constant [#196](https://github.com/Codeinwp/optimole-wp/issues/196) ([e0268fa](https://github.com/Codeinwp/optimole-wp/commit/e0268fa))
   * skip lazyload on images with skip-lazy flag ([4d9219f](https://github.com/Codeinwp/optimole-wp/commit/4d9219f))



#### [Version 2.2.6](https://github.com/Codeinwp/optimole-wp/compare/v2.2.5...v2.2.6) (2019-12-02)

* **Bug Fixes**
   * compatibility with Edge 15 of js library, fix [#187](https://github.com/Codeinwp/optimole-wp/issues/187) ([8e73668](https://github.com/Codeinwp/optimole-wp/commit/8e73668))
   * compatibility with slider revolution ([05c21ce](https://github.com/Codeinwp/optimole-wp/commit/05c21ce))
   * edge case for optimole failed to lazyload certain images ([0f8de69](https://github.com/Codeinwp/optimole-wp/commit/0f8de69))
   * edge cases cropping behaviour when two images sizes are using different cropping ([beb8c27](https://github.com/Codeinwp/optimole-wp/commit/beb8c27))
   * improve compatibility with master slider ([cf57717](https://github.com/Codeinwp/optimole-wp/commit/cf57717))
   * searching through multiple classes the right way, added tests ([4751edb](https://github.com/Codeinwp/optimole-wp/commit/4751edb))

* **Features**
   * adds exclude by class filter for lazyload mechanism ([3ce87e2](https://github.com/Codeinwp/optimole-wp/commit/3ce87e2))



#### [Version 2.2.5](https://github.com/Codeinwp/optimole-wp/compare/v2.2.4...v2.2.5) (2019-11-18)

* **Bug Fixes**
   * inherit custom sizes desired cropping, adds enlarge compatibility ([84f7056](https://github.com/Codeinwp/optimole-wp/commit/84f7056))

* **Features**
   * adds compatibility with multiple slider plugins ([d78fd1b](https://github.com/Codeinwp/optimole-wp/commit/d78fd1b))
   * improve compatibility with various page builders: Divi, Thrive, Elementor, Beaver ([a4391b4](https://github.com/Codeinwp/optimole-wp/commit/a4391b4))



#### [Version 2.2.4](https://github.com/Codeinwp/optimole-wp/compare/v2.2.3...v2.2.4) (2019-11-05)

* **Bug Fixes**
   * conditions for close to limit notice ([e3530b8](https://github.com/Codeinwp/optimole-wp/commit/e3530b8))



#### [Version 2.2.3](https://github.com/Codeinwp/optimole-wp/compare/v2.2.2...v2.2.3) (2019-11-05)

* **Bug Fixes**
   * change minimum slider range for compression to 50 instead of 0 ([592c3e2](https://github.com/Codeinwp/optimole-wp/commit/592c3e2))
   * image quality setting description, fix [#162](https://github.com/Codeinwp/optimole-wp/issues/162) ([60cbe5e](https://github.com/Codeinwp/optimole-wp/commit/60cbe5e))
   * improve description for background image lazyload fix [#163](https://github.com/Codeinwp/optimole-wp/issues/163) ([f0f190b](https://github.com/Codeinwp/optimole-wp/commit/f0f190b))
   * upgrade notice when user is close to visits limit ([ff716f7](https://github.com/Codeinwp/optimole-wp/commit/ff716f7))



#### [Version 2.2.2](https://github.com/Codeinwp/optimole-wp/compare/v2.2.1...v2.2.2) (2019-11-04)

* **Bug Fixes**
   * ignore rescale on GIF and SVG when lazyload is active ([d4e63b6](https://github.com/Codeinwp/optimole-wp/commit/d4e63b6))
   * improve compatibility with Thrive Architect ([55a5952](https://github.com/Codeinwp/optimole-wp/commit/55a5952))
   * improve generic placeholder computed width/height when the source size is unknown ([0c3cc3f](https://github.com/Codeinwp/optimole-wp/commit/0c3cc3f))
   * sign only URLs that don't use an whitelisted domain.  ([7813bf9](https://github.com/Codeinwp/optimole-wp/commit/7813bf9))
   * strip retina based prefix to use original url ([948c667](https://github.com/Codeinwp/optimole-wp/commit/948c667))



#### [Version 2.2.1](https://github.com/Codeinwp/optimole-wp/compare/v2.2.0...v2.2.1) (2019-10-21)

* **Bug Fixes**
   * gif to video setting description ([063d37b](https://github.com/Codeinwp/optimole-wp/commit/063d37b))



### [Version 2.2.0](https://github.com/Codeinwp/optimole-wp/compare/v2.1.2...v2.2.0) (2019-10-21)

* #### Bug Fixes
   * modified tests, checking if an url contains gif image ([f76f2c9](https://github.com/Codeinwp/optimole-wp/commit/f76f2c9))
   * small typo on max sizes settings description ([4d83dbf](https://github.com/Codeinwp/optimole-wp/commit/4d83dbf))

* #### Features
   * adds GIF compression and conversion to video files ( mp4 and WebM ) ([04d9f7d](https://github.com/Codeinwp/optimole-wp/commit/04d9f7d))
   * adds gif to the exclusions list ([914cd11](https://github.com/Codeinwp/optimole-wp/commit/914cd11))
   * adds option to lazyload images used as backgrounds ([c346244](https://github.com/Codeinwp/optimole-wp/commit/c346244))
   * adds toggle to disable bg image replacement ([3c3997a](https://github.com/Codeinwp/optimole-wp/commit/3c3997a))


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

Short answer, YES. We can help you integrate your default CDN but it will require some additional work from our side and is available to Enterprise plans.

= I'm already using an image optimization plugin, why should I switch to OptiMole? =

You don’t need to change your existing optimization plugin, image optimization is just a small part of what we do, if you are happy with ShortPixel for e.g, feel free to continue to use it, OptiMole would then take care only of serving your image at the RIGHT size, advanced cropping and smart lazy-loading.

= Does Optimole handle images from Ajax content  ? =

Yes, we do. By default, Optimole handle images delivered from your ajax content from admin-ajax.php ( not logged in users ) as well as WordPress REST API routes.

= Can I remove the blurry placeholder from the lazyload effct ? =

Yes, you can. We have bundled this tweak into a plugin you can install while you have Optimole active. The blurry placeholder will be removed and the images will be still lazy-loaded. You can find the plugin tweak here -> http://bit.ly/optml-rm-lqip

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

= Can I disable lazyload for PNG images ? =

Yes. You need to add `define("OPTML_DISABLE_PNG_LAZYLOAD",true);` to `your wp-config.php` file.

= Can I disable optimization for a certain image ? =

Yes, you can follow this code snippet and replace the sample image with the one you need:
<code>
add_filter('optml_dont_replace_url', function( $old, $url ) {
	if ( $url === 'https://example.com/wp-content/uploads/2018/09/1.jpg' ) {
		return true;
	}
	return $old;

}, 10, 2);
</code>
