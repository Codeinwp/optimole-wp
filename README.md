# Image optimization service by Optimole #
**Contributors:** [optimole](https://profiles.wordpress.org/optimole)  
**Tags:** image optimization, cdn, image compression, compress image, images, optimization, perfomance, photos  
**Requires at least:** 4.7  
**Tested up to:** 5.1  
**Requires PHP:** 5.4  
**License:** GPLv3  
**License URI:** https://www.gnu.org/licenses/gpl-3.0.en.html  

End-to-end image processing
With OptiMole, your site’s images will be cropped, optimized and processed on-the-fly.


## Description ##

> **What makes Optimole so special?**
>
> * Smaller images. Same quality
> * Optimizes based on the visitor's actual device
> * Fully automated; set and forget
> * Supports all image types
> * Supports both Retina and WebP images
> * Serves images from a global CDN for free
> * Optimizes based on the visitor's actual device (no more guesswork and width estimations)
> * Full support for page builders like Elementor
> * Lazy loading without jQuery (better)
> * Fully functional free version (we have paid plans as well)
>

Optimole is the all-in-one solution to all of your image optimization needs. With full automation and a range of evolutionary features, Optimole makes it easy to clean up your heavy images and bloaty pages.

Optimole optimizes your images in real-time with a cloud-based system to speed up your website and deliver high-quality images perfectly sized for every device. With a one-click setup and minimal footprint, you can start improving your site in minutes.

You'll be free to concentrate on other aspects of your site with Optimole's set and forget image optimization.

So, if you have been looking for one plugin that provides lazy loading with a CDN and perfectly sized images, then Optimole is perfect for you.


**How does it do all these great things?**
Optimole's format based optimization is handled in the cloud on a case-by-case basis. Once you have the plugin installed, Optimole will replace all of your image URLs with cloud-based URLs. We don't replace any images on your website.
When the image is requested, Optimole will apply the specific transformations required by the device and deliver it to your visitors. This means every image is perfectly sized for every device. The final result will be cached for a month to ensure ongoing fast delivery and more site speed.

**How many images can be optimized?**
With the basic plan, you will be able to optimize 1GB of images per month with 5GB viewing bandwidth. All delivered from 7 edge locations all over the world.
Better yet. The free version is fully functional and includes all of the following great features.

**Format Based Optimization**
Our cloud-based transformation process means we can offer optimizations based on the format. If your visitor is using a WebP capable browser, then Optimole will send a WebP image to their device.

**Image Optimization**
Our algorithms crunch the numbers to provide the best lossy or lossless optimization for the best-looking image at the smallest size.

**Exact Used Size**
Optimole will use just one image and resize it to fit perfectly on your visitors' devices. No more awkward guesses at potential screen widths.

**Retina Support**
Optimole can detect Retina screens and deliver an image with the perfect Digital Pixel Ratio (DPR).

**Smart Cropping**
If you'd like to keep the most interesting part of an image; then you can enable smart cropping to help keep the image well sized without losing focus.

**No Content Shifting**
The lazy load option is perfectly sized for the container to provide a seamless viewing experience without any content shifting.

**Watermarks**
Who has time for adding watermarks? Optimole will do the hard work for you. Just set it up and pick your preferred location and Optimole will add the watermark to all of your future images.

**Downgrade Quality For Slower Connections**
Optimole provides an option to downgrade the image quality when it detects a slower network. Make the images up to 40% smaller with this neat feature to help visitors in a bottleneck.
**Compatibility**
Optimole loves page builders and has unique tweaks to solve image replacements. It also has full compatibility with the new block editor in WordPress 5.0

**CDN**
Optimole provides free access to a CDN with 7 edge locations around the world. Even more with the Pro version.

**What About Security?**
The stripped EXGIF data is not stored on our service. Optimole likes to work behind the scenes, and won't interact with your site's visitors. No data is collected but you can check the [Terms of Service](https://optimole.com/terms/)

**Smooth And Clean**
Optimole can be installed in a few clicks and then left in the back-end to do its job. Not happy with it? Optimole has a clean uninstall and your site will be just as before Optimole was installed.

**Go Pro**
Premium users will be able to optimize 10GB images per month with a 50GB viewing bandwidth. Images in the Premium plan are served from AWS Cloudfront with over 130 locations.

## Screenshots ##

1. Welcome screen
2. Connect screen
3. Plugin dashboard
4. Plugin settings

## Changelog ##

#### [Version 2.0.5](https://github.com/Codeinwp/optimole-wp/compare/v2.0.4...v2.0.5) (2019-03-25)

* **Bug Fixes**
   * adds compatibility with Jetelements Slider ([c9e518e](https://github.com/Codeinwp/optimole-wp/commit/c9e518e))
   * compatibility with Metaslider, adds full support for all slider types available ([a09d1fd](https://github.com/Codeinwp/optimole-wp/commit/a09d1fd))
   * compatibility with various themes logo markup based on the image url, removes the hook to theme_mods image replace ([34e0d85](https://github.com/Codeinwp/optimole-wp/commit/34e0d85))
   * disable image replacement when we are in the Beaver Builder editing mode ([239442f](https://github.com/Codeinwp/optimole-wp/commit/239442f))
   * image replacement on header tags which uses relative urls ([2caae7f](https://github.com/Codeinwp/optimole-wp/commit/2caae7f))
   * image replacement on json strings with html entities encoded, conflicting with  variation form Woocommerce fixes [#81](https://github.com/Codeinwp/optimole-wp/issues/81) ([2bae741](https://github.com/Codeinwp/optimole-wp/commit/2bae741))
   * lazyload animation conflict with initial image animation ([3a84250](https://github.com/Codeinwp/optimole-wp/commit/3a84250))
   * lazyload on image which uses relative urls ([4655994](https://github.com/Codeinwp/optimole-wp/commit/4655994))
   * lazyload placeholder replacement affecting non-src urls, improving compatibility with Woocommerce ([7e13a32](https://github.com/Codeinwp/optimole-wp/commit/7e13a32))
   * noscript image tag issue causing problems with too specific CSS selectors ([324effd](https://github.com/Codeinwp/optimole-wp/commit/324effd))
   * relative url image replacement conflicting with plugins like WPML ([7a47827](https://github.com/Codeinwp/optimole-wp/commit/7a47827))
   * thumbnails cropping mode not affecting the resulting optimole images ([0f6dacb](https://github.com/Codeinwp/optimole-wp/commit/0f6dacb))

* **Features**
   * adds constant to disable latest images area ([a5891b6](https://github.com/Codeinwp/optimole-wp/commit/a5891b6))
   * adds constant to switch on/off network based optimization ([1accde5](https://github.com/Codeinwp/optimole-wp/commit/1accde5))
   * adds full compatibility with Beaver Builder, processing images from the generated css ([2a17f30](https://github.com/Codeinwp/optimole-wp/commit/2a17f30))



#### [Version 2.0.4](https://github.com/Codeinwp/optimole-wp/compare/v2.0.3...v2.0.4) (2019-03-11)

* **Bug Fixes**
   * adds full compatibility with Envira gallery ([7fd618f](https://github.com/Codeinwp/optimole-wp/commit/7fd618f))
   * compatibility with Foogallery plugin when the gallery uses lazyload too ([67991dc](https://github.com/Codeinwp/optimole-wp/commit/67991dc))
   * compatibility with images which contains query arguments, causing broken image urls ([56108be](https://github.com/Codeinwp/optimole-wp/commit/56108be))
   * compatibility with relative image urls ([8089610](https://github.com/Codeinwp/optimole-wp/commit/8089610))
   * compatibility with Revolution slider, adds support for background images lazyload and exact match ([0bbd254](https://github.com/Codeinwp/optimole-wp/commit/0bbd254))
   * compatibility with shortcode ultimate plugin ([164ba35](https://github.com/Codeinwp/optimole-wp/commit/164ba35))
   * compatibility with Woocommerce, solving issue with zoom image on single product pages ([1692e2b](https://github.com/Codeinwp/optimole-wp/commit/1692e2b))
   * image replacement on WordPress REST api responses ([24d191b](https://github.com/Codeinwp/optimole-wp/commit/24d191b))
   * image url replacement on custom WordPress directory structure, fixes [#79](https://github.com/Codeinwp/optimole-wp/issues/79), thanks [@hackles](https://github.com/hackles) for reporting ([980fcef](https://github.com/Codeinwp/optimole-wp/commit/980fcef))

* **Features**
   * tested up compatibility with WordPress 5.1 ([12726b6](https://github.com/Codeinwp/optimole-wp/commit/12726b6))
   * **api:** adds filter for restricting watermark based on image source urls ([337d7fa](https://github.com/Codeinwp/optimole-wp/commit/337d7fa))
   * **api:** adds filter to disable image replacement on a specific page/ur ([3250a8d](https://github.com/Codeinwp/optimole-wp/commit/3250a8d))



#### [Version 2.0.3](https://github.com/Codeinwp/optimole-wp/compare/v2.0.2...v2.0.3) (2019-02-13)

* **Bug Fixes**
   * adds whitelisted websites among the image urls to replace ([bfb016e](https://github.com/Codeinwp/optimole-wp/commit/bfb016e))
   * allow image urls that does not contain scheme, either http or https ([2aae664](https://github.com/Codeinwp/optimole-wp/commit/2aae664))
   * anchor tags image replacement, conflicting with gallery plugins ( NextGen ) ([824acdc](https://github.com/Codeinwp/optimole-wp/commit/824acdc))
   * compatibility when jetpack photon is on, fetch directly the image source ([f339dbb](https://github.com/Codeinwp/optimole-wp/commit/f339dbb))
   * cropping behaviour when lazyload is off for certain edge causing incorrect image resize ([8aca6dc](https://github.com/Codeinwp/optimole-wp/commit/8aca6dc))
   * doubled image bug, detect if image tag has already lazyload applied and bail for Optimole lazyload ([bea5ac8](https://github.com/Codeinwp/optimole-wp/commit/bea5ac8))
   * image replacement on admin ajax requests ([924cc49](https://github.com/Codeinwp/optimole-wp/commit/924cc49))
   * image url replacement on non whitelisted urls ([8567f8b](https://github.com/Codeinwp/optimole-wp/commit/8567f8b))
   * JSON strings url replacement, improves elementor's compatibility ([2ff9e9b](https://github.com/Codeinwp/optimole-wp/commit/2ff9e9b))
   * lazyload replacement when image source is in data-src attributes, solving conflict with some gallery plugins ([c26cdab](https://github.com/Codeinwp/optimole-wp/commit/c26cdab))

* **Documentation**
   * adds faq for ajax content, remove lqip and fixed typo for plugin tags ([104a17e](https://github.com/Codeinwp/optimole-wp/commit/104a17e))



#### [2.0.2](https://github.com/Codeinwp/optimole-wp/compare/v2.0.1...v2.0.2) (2019-02-01)


**Bug Fixes**

* dashboard app loading issues for some wordpress environments ([293e277](https://github.com/Codeinwp/optimole-wp/commit/293e277))

* disable srcset attribute when the javascript resizer and lazyload is active, being redundant ([76c0307](https://github.com/Codeinwp/optimole-wp/commit/76c0307))

* domain validation when www is present in the image urls, thanks [@wpriders](https://github.com/wpriders) team for reporting ([9559ce5](https://github.com/Codeinwp/optimole-wp/commit/9559ce5))

* image resize when the size class is not available, get cropping based on the width/height of the resulting image ([e5c1aab](https://github.com/Codeinwp/optimole-wp/commit/e5c1aab))



**Documentation**

* improve readme description of the OptiMole service ([e020300](https://github.com/Codeinwp/optimole-wp/commit/e020300))



**Features**

* adds upgrade box and notice to upgrade when the user is close to the limit ([62b216a](https://github.com/Codeinwp/optimole-wp/commit/62b216a))


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





### 1.1.2 - 2018-12-24  ###

* Minor fixes to Optimole dashboard page.
* Fixes DNS prefetch call when lazyload is off.
* Enable lazyload and javascript replacement by default for new users.


### 1.1.1 - 2018-12-10  ###

* Improve the lazy loading mechanism and adds compatibility with the new javascript library. 
* Improve sample images and quality selector integration. 
* Adds a notice when the Rest API is not available. 
* Adds notice for new users on how the plugin works. 
* Tested up with WordPress 5.0, all working smooth. 
* Fix possible issues with thumbnails when the original image is available on a different url scheme.
* Ignore lazyload replacement on feed/amp pages.


### 1.1.0 - 2018-11-16  ###

* Integrates lazy load feature with support for low-quality placeholders ( LQIP ). 
* Integrates Javascript library which delivers images at the right size for each device, including Retina.
* Improve image replacement algorithm.
* Improves compatibility with Elementor and Gutenberg. 
* Adds support for Custom CNAME and Client hints.
* Improves support for custom CDN. 
* Improves AMP support. 
* Improves compatibility with WordPress Multisites.


### 1.0.5 - 2018-10-05  ###

* Adds max width/height option.
* Improves registration workflow.
* Adds image comparison slider ratio.
* Adds logo on link preload.


### 1.0.4 - 2018-10-03  ###

* Adds in-plugin service registration.
* Adds image quality control.
* Adds slider for image comparison.
* Improvements to UX and workflow.


### 1.0.3 - 2018-09-26  ###

* Adds redirect on first install.
* Improve elementor assets replacement.


### 1.0.2 - 2018-09-25  ###

* Improve compatibility with elementor external css files.
* Adds generator tag.
* Improve replacer handler hook register.


### 1.0.1 - 2018-09-23  ###

* Tag first stable version for wordpress.org.



### 1.0.0 - 2018-09-22 ###
* First version of the plugin

## Installation ##
The following are the steps to install the OptiMole plugin

1. In your WordPress Administration Panels, click on Add New option under Plugins from the menu.
Click on upload at the top.
2. Browse the location and select the OptiMole Plugin and click install now.
3. Go to Media -> OptiMole and follow in the instructions on how to enable the service.

## Frequently Asked Questions ##

### How many images I can optimize with each plan? ###

The number of images that you can optimize depends on your original image size and the number of transformations you do for it.  Using the Free plan you can optimize up to 1 GB of images, which means around 2000 images at an average of 500Kb per image.

### What happens if I exceed plan limits? ###

Once you exceed these, we will contact you and kindly ask to upgrade to the plan that fits you best.

### What Content Delivery Network (CDN) do you use? ###

Our FREE plan uses our custom made CDN built with 7 locations around the globe. For the paid plans, we have direct integration with Amazon Cloudfront, with more than 130 locations around the globe.

### I'm already using a CDN, can I use that instead of yours ? ###

Short answer, YES. We can help you integrate your default CDN but it will require some additional work from our side and is available to Enterprise plans.

### I'm already using an image optimization plugin, why should I switch to OptiMole? ###

You don’t need to change your existing optimization plugin, image optimization is just a small part of what we do, if you are happy with ShortPixel for e.g, feel free to continue to use it, OptiMole would then take care only of serving your image at the RIGHT size, advanced cropping and smart lazy-loading.

### Does Optimole handle images from Ajax content  ? ###

Yes, we do. By default, Optimole handle images delivered from your ajax content from admin-ajax.php ( not logged in users ) as well as WordPress REST API routes.

### Can i remove the blurry placeholder from the lazyload effct ? ###

Yes, you can. We have bundled this tweak into a plugin you can install while you have Optimole active. The blurry placeholder will be removed and the images will be still lazy-loaded. You can find the plugin tweak here -> http://bit.ly/optml-rm-lqip

### Will the original images be deleted? ###

Absolutely No. We use your original images as sources when deliver the optimized images.

### What is the difference between the Auto, High, Medium, Low compression levels? ###

A higher compression might result in a small loss of image quality. Selecting the auto level will let Optimole choose the minimum size with no loss in the quality of your picture.

### I used Kraken, Shortpixel, Optimus, EWWW or WP Smush, Imagify  will OptiMole further optimize my images? ###

 Yes, Optimole will also take care of serving your image at the RIGHT size for your visitors and optimize them to the best possible format for their browser.

### Which formats can be optimized ? ###

For now we support jpg, png and svg format.

### Does Optimole automatically serve WebP for Chrome users ? ###

Yes. We automatically detect user browser and serve WebP if is supported, otherwise we optimize the image in the original format.

### Can I disable lazyload for PNG images ? ###

Yes. You need to add `define("OPTML_DISABLE_PNG_LAZYLOAD",true);` to `your wp-config.php` file.

### Can I disable optimization for a certain image ? ###

Yes, you can follow this code snippet and replace the sample image with the one you need:
<code>
add_filter('optml_dont_replace_url', function( $old, $url ) {
	if ( $url === 'https://example.com/wp-content/uploads/2018/09/1.jpg' ) {
		return true;
	}
	return $old;

}, 10, 2);
</code>
