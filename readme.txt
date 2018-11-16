=== Image optimization service by Optimole ===
Contributors: optimole
Tags: image optmization, cdn, image compression
Requires at least: 4.7
Tested up to: 4.9
Requires PHP: 5.4
Stable tag: trunk
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

== Changelog ==
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