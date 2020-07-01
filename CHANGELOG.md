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

### Bug Fixes
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

#### [Version 2.1.2](https://github.com/Codeinwp/optimole-wp/compare/v2.1.1...v2.1.2) (2019-09-25)

* **Bug Fixes**
   * adds preconnect hint for image domain and js library domain ([11b697d](https://github.com/Codeinwp/optimole-wp/commit/11b697d))
   * compatibility with cache_enabler [#136](https://github.com/Codeinwp/optimole-wp/issues/136) ([483262f](https://github.com/Codeinwp/optimole-wp/commit/483262f))
   * improve checking for editing context when the replacement should be off ([e7510f6](https://github.com/Codeinwp/optimole-wp/commit/e7510f6))
   * lazyload query urls part of [#145](https://github.com/Codeinwp/optimole-wp/issues/145) ([a048f68](https://github.com/Codeinwp/optimole-wp/commit/a048f68))
   * preload lazyload js file when lazyload setting is active ([828e1de](https://github.com/Codeinwp/optimole-wp/commit/828e1de))
   * remove replacement on Divi theme builder ([86ab6d2](https://github.com/Codeinwp/optimole-wp/commit/86ab6d2))
   * replacement was not working for urls with special chars ([48a4966](https://github.com/Codeinwp/optimole-wp/commit/48a4966))
   * replacing url's with query strings without the query in the modified url [#141](https://github.com/Codeinwp/optimole-wp/issues/141) ([0025559](https://github.com/Codeinwp/optimole-wp/commit/0025559))
   * replacing url's with regex in <a> tags [#141](https://github.com/Codeinwp/optimole-wp/issues/141) ([4b2264f](https://github.com/Codeinwp/optimole-wp/commit/4b2264f))
   * resource hints condition check [skip release] ([a0f30e7](https://github.com/Codeinwp/optimole-wp/commit/a0f30e7))

* **Features**
   * adds retina settings control which enable/disable serving of HiDPI images ([73c8712](https://github.com/Codeinwp/optimole-wp/commit/73c8712))
   * adds visitors based plan integration ([ea07a94](https://github.com/Codeinwp/optimole-wp/commit/ea07a94))

#### [Version 2.1.1](https://github.com/Codeinwp/optimole-wp/compare/v2.1.0...v2.1.1) (2019-07-22)

* **Bug Fixes**
   * clicking on advanced menu move the focus to the focus to the compression tab, fix [#118](https://github.com/Codeinwp/optimole-wp/issues/118) ([178d1fc](https://github.com/Codeinwp/optimole-wp/commit/178d1fc))
   * improve generic lazyload setting description and behaviour, fix [#119](https://github.com/Codeinwp/optimole-wp/issues/119) ([13ce758](https://github.com/Codeinwp/optimole-wp/commit/13ce758))
   * improve responsivness behaviour on small screens, fix [#117](https://github.com/Codeinwp/optimole-wp/issues/117) ([10484bb](https://github.com/Codeinwp/optimole-wp/commit/10484bb))
   * when service update cron returns an error, we should use the old data ([4f80cc0](https://github.com/Codeinwp/optimole-wp/commit/4f80cc0))

### [Version 2.1.0](https://github.com/Codeinwp/optimole-wp/compare/v2.0.7...v2.1.0) (2019-07-16)

* #### Bug Fixes
   * api request should return error message, if fails ([51e180e](https://github.com/Codeinwp/optimole-wp/commit/51e180e))
   * compatibility with shortcode ultimate, adds more usecases to test ([c6673eb](https://github.com/Codeinwp/optimole-wp/commit/c6673eb))
   * disable REST API image replacement when the user is logged in ([f577723](https://github.com/Codeinwp/optimole-wp/commit/f577723))
   * disable rest api replacement when we are in the edit context ([58d83b0](https://github.com/Codeinwp/optimole-wp/commit/58d83b0))
   * duplicate requests when generating the lazyload placeholder, using an unified url for every source url, fix [#103](https://github.com/Codeinwp/optimole-wp/issues/103) ([1c00c0c](https://github.com/Codeinwp/optimole-wp/commit/1c00c0c))
   * ignore lazyload tag on noscript list of images, improve compatibility with Envira ([74219b1](https://github.com/Codeinwp/optimole-wp/commit/74219b1))
   * improve compatibility with Envira, ignora optimole lazyload on them ([00b68fd](https://github.com/Codeinwp/optimole-wp/commit/00b68fd))
   * possible issues when list of allowed domains is not an array ([2ee9997](https://github.com/Codeinwp/optimole-wp/commit/2ee9997))
   * prefetch custom domain instead of the assigned one, when custom domain is used ([cedbb79](https://github.com/Codeinwp/optimole-wp/commit/cedbb79))
   * remove replacement on POST requests ([f4637ba](https://github.com/Codeinwp/optimole-wp/commit/f4637ba))
   * removes admin bar traffic node ([0e21fe8](https://github.com/Codeinwp/optimole-wp/commit/0e21fe8))

* #### Features
   * adds basic filter, smart cropping and network optimization dashboard settings, fix [#105](https://github.com/Codeinwp/optimole-wp/issues/105), [#104](https://github.com/Codeinwp/optimole-wp/issues/104), [#96](https://github.com/Codeinwp/optimole-wp/issues/96) ([aba242d](https://github.com/Codeinwp/optimole-wp/commit/aba242d))
   * adds compatibility with new optimization service, improving resizing and compression with up to 15% smaller images than before ([1f2528f](https://github.com/Codeinwp/optimole-wp/commit/1f2528f))
   * adds generic lazyload placeholder option as a setting ([43b33fb](https://github.com/Codeinwp/optimole-wp/commit/43b33fb))

#### [Version 2.0.7](https://github.com/Codeinwp/optimole-wp/compare/v2.0.6...v2.0.7) (2019-05-29)

* **Bug Fixes**
   * **rest:** WordPress core routes were called with double slashes ([2e47fe0](https://github.com/Codeinwp/optimole-wp/commit/2e47fe0))
   * better replacement for relative paths ([c18be2f](https://github.com/Codeinwp/optimole-wp/commit/c18be2f))
   * image replacement was breaking non-image urls with query strings ([2ef2212](https://github.com/Codeinwp/optimole-wp/commit/2ef2212))
   * improve admin bar quota looking and copyright [#99](https://github.com/Codeinwp/optimole-wp/issues/99) ([200e746](https://github.com/Codeinwp/optimole-wp/commit/200e746))
   * improve CDN domain label naming [#97](https://github.com/Codeinwp/optimole-wp/issues/97) ([8a2da71](https://github.com/Codeinwp/optimole-wp/commit/8a2da71))
   * improve error reporting on connection to the service ([11ee514](https://github.com/Codeinwp/optimole-wp/commit/11ee514))
   * remove image replacement on Thrive Architect editor ([3a8c46b](https://github.com/Codeinwp/optimole-wp/commit/3a8c46b))
   * removes redundant api request parameters ([4385f68](https://github.com/Codeinwp/optimole-wp/commit/4385f68))

* **Documentation**
   * improve readme description ([4134b05](https://github.com/Codeinwp/optimole-wp/commit/4134b05))

* **Features**
   * **api:** adds filter before url replacement, for individual image args ([d936944](https://github.com/Codeinwp/optimole-wp/commit/d936944))
   * adds possible conflicts tab which reports the most common problems that Optimole might have with various plugins ([3b1ec3f](https://github.com/Codeinwp/optimole-wp/commit/3b1ec3f))

#### [Version 2.0.6](https://github.com/Codeinwp/optimole-wp/compare/v2.0.5...v2.0.6) (2019-03-29)

* **Bug Fixes**
   * adds compatibility with background images lazyload on Essential Grid plugin ([a73d1cd](https://github.com/Codeinwp/optimole-wp/commit/a73d1cd))
   * compatibility with Oxygen builder, removes image replacement in the editor ([015698d](https://github.com/Codeinwp/optimole-wp/commit/015698d))
   * compatibility with Woocommerce zoom image feature on product single pages ([4445d1e](https://github.com/Codeinwp/optimole-wp/commit/4445d1e))
   * compatibility with YITH WooCommerce Quick View plugin, replacing images returned by the ajax request, fix [#87](https://github.com/Codeinwp/optimole-wp/issues/87) ([60169b2](https://github.com/Codeinwp/optimole-wp/commit/60169b2))
   * cropping behaviour being inconsistent with due to variable reset in image parsing loop ([2b14e7c](https://github.com/Codeinwp/optimole-wp/commit/2b14e7c))
   * remove unnecessary variables in the rest request causing conflicts with redirection plugins ([68c04ee](https://github.com/Codeinwp/optimole-wp/commit/68c04ee))
   * Woocommerce image archive ratio, preserving cropping with Optimole images, fixes [#85](https://github.com/Codeinwp/optimole-wp/issues/85) ([a3a8504](https://github.com/Codeinwp/optimole-wp/commit/a3a8504))

* **Features**
   * adds refresh stats button, syncing user details with Optimole dashboard, fix [#83](https://github.com/Codeinwp/optimole-wp/issues/83) ([1461483](https://github.com/Codeinwp/optimole-wp/commit/1461483))

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

### v1.1.2 - 2018-12-24 
 **Changes:** 
 * Minor fixes to Optimole dashboard page.
* Fixes DNS prefetch call when lazyload is off.
* Enable lazyload and javascript replacement by default for new users.
 
 ### v1.1.1 - 2018-12-10 
 **Changes:** 
 * Improve the lazy loading mechanism and adds compatibility with the new javascript library. 
* Improve sample images and quality selector integration. 
* Adds a notice when the Rest API is not available. 
* Adds notice for new users on how the plugin works. 
* Tested up with WordPress 5.0, all working smooth. 
* Fix possible issues with thumbnails when the original image is available on a different url scheme.
* Ignore lazyload replacement on feed/amp pages.
 
 ### v1.1.0 - 2018-11-16 
 **Changes:** 
 * Integrates lazy load feature with support for low-quality placeholders ( LQIP ). 
* Integrates Javascript library which delivers images at the right size for each device, including Retina.
* Improve image replacement algorithm.
* Improves compatibility with Elementor and Gutenberg. 
* Adds support for Custom CNAME and Client hints.
* Improves support for custom CDN. 
* Improves AMP support. 
* Improves compatibility with WordPress Multisites.
 
 ### v1.0.5 - 2018-10-05 
 **Changes:** 
 * Adds max width/height option.
* Improves registration workflow.
* Adds image comparison slider ratio.
* Adds logo on link preload.
 
 ### v1.0.4 - 2018-10-03 
 **Changes:** 
 * Adds in-plugin service registration.
* Adds image quality control.
* Adds slider for image comparison.
* Improvements to UX and workflow.
 
 ### v1.0.3 - 2018-09-26 
 **Changes:** 
 * Adds redirect on first install.
 * Improve elementor assets replacement.
  
 ### v1.0.2 - 2018-09-25 
 **Changes:** 
 * Improve compatibility with elementor external css files.
* Adds generator tag.
* Improve replacer handler hook register.
 
 ### v1.0.1 - 2018-09-23 
 **Changes:** 
 * Tag first stable version for wordpress.org.
 
 ### v1.0.0 - 2018-09-11 
 **Changes:** 
   * First version of the plugin
