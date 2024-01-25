##### [Version 3.12.4](https://github.com/Codeinwp/optimole-wp/compare/v3.12.3...v3.12.4) (2024-01-25)

### Enhancements

- **Add Filter for Overriding DISABLE_WP_CRON Check**: Introduced a filter for overriding the DISABLE_WP_CRON check in the context  - optml_offload_wp_cron_disabled.

##### [Version 3.12.3](https://github.com/Codeinwp/optimole-wp/compare/v3.12.2...v3.12.3) (2024-01-16)

### Enhancements

- **Improve default settings**: Ensure default settings for image optimization and lazyloading are optimal.

##### [Version 3.12.2](https://github.com/Codeinwp/optimole-wp/compare/v3.12.1...v3.12.2) (2024-01-08)

### Bug Fixes

- **Optimization Exclusions Fix**: Resolved an issue where some pages were excluded from optimization due to AJAX requests exclusions.

### Enhancements

- **Lazyloading Menu Item**: Ensure that turning off lazyloading now disables the lazyloading settings menu item in the plugin dashboard.
- **Lazyload Support for Group Blocks**: Added lazyload support for group blocks background for improved performance.
- **Cap Offloading Log**: Capped offloading log read lines to 10,000 for better performance and manageability.

##### [Version 3.12.1](https://github.com/Codeinwp/optimole-wp/compare/v3.12.0...v3.12.1) (2023-12-21)

### Enhancements

- **Add Retries for Offloading Common Errors**: Implemented a feature to add retries to address common errors in the offloading process.


### Fixes

- **Fix Replacement in Elementor**: Resolved an issue where replacement in Elementor was not working in some cases

#### [Version 3.12.0](https://github.com/Codeinwp/optimole-wp/compare/v3.11.3...v3.12.0) (2023-12-19)

### New Features
- **Handshake Mechanism**: Implemented a handshake mechanism to ensure that the website can use Optimole when connecting.
- **New Cloud Library UI/UX**: Introduced a new and improved UI and experience for the Cloud Library.

### Enhancements

- **Improved Optimole Dashboard UX**: Enhancements to improve the user experience of the Optimole dashboard.
- **Cohesive UI on Plugin Dashboard**: Improved the overall UI on the plugin dashboard for a more cohesive look and feel.
- **Revamped Offloading User Experience**: Revamped the UI/UX for offloading operations, making it more intuitive and user-friendly.
- **Cloud Library Access by Default**: Enabled Cloud Library access by default for all users, enhancing accessibility to Optimoles features.
- **Notice for Offloaded Images Limit**: Added a notice if the count of offloaded images exceeds the limit, keeping users informed.

##### [Version 3.11.3](https://github.com/Codeinwp/optimole-wp/compare/v3.11.2...v3.11.3) (2023-12-05)

### Bug Fixes

- **Division by zero**: Addressed an edge case where a division by zero was happening when resizing images.
- **WPML Duplicated Attachments**: Resolved an issue where WPML duplicated attachments were not being accounted for as offloaded.
- **Offload Batch Size**: Lowered the batch size of images processed for offloading/rollback to address timeout issues on some servers.

### Improvements

- **Action Scheduler Integration**: The offloading/rollback process will use Action Scheduler if available.

##### [Version 3.11.2](https://github.com/Codeinwp/optimole-wp/compare/v3.11.1...v3.11.2) (2023-11-23)

### Bug Fixes

- **Rollback Issue**: Fixed an issue where rolling back images would not point the attachments to the correct uploads folder path.

##### [Version 3.11.1](https://github.com/Codeinwp/optimole-wp/compare/v3.11.0...v3.11.1) (2023-11-20)

### Bug Fixes

- **Cache for Offloaded Attachments**: Fixed an issue with caching for offloaded attachments when the object cache extension isnt enabled.
- **Error on Older WordPress Versions**: Resolved an error occurring on WordPress versions lower than 6.0.0.
- **Performance Issue on Large Instances**: Fixed a performance issue affecting instances with a large number of images.
- **Offload Rollback Issue**: Addressed a problem where offloading was trying to rollback Cloud Library images.

#### [Version 3.11.0](https://github.com/Codeinwp/optimole-wp/compare/v3.10.0...v3.11.0) (2023-11-15)

### New Features

- **Image Optimization for ICO Files**: Added support for optimizing ICO files, ensuring that these can benefit from Optimoles optimization capabilities.
- **Toast Notifications in Optimole Dashboard**: Introduced a toast notifications system within the Optimole dashboard, informing users when settings are saved.
- **Offload Images Without Database Replacement**: Images are now offloaded without the need to perform a database replacement, improving the speed of the process and reducing complexity.
- **Logging for Offloading Process**: Implemented logging for the image offloading process, allowing users to track progress more effectively.
- **Faster Zip Generation Routine**: Optimized the zip files generation routine from the Optimole Dashboard, making it faster and more efficient. Additionally, split the Zip files per each source website when downloading images, providing a smoother experience.
- **Motion.page Compatibility**: Addressed compatibility issues to ensure smooth integration with the Motion.page.

### Improvements

- **Remove Redundant Setting**: Removed redundant settings related to resizing large images from the original source, simplifying the user interface.
- **Review of Settings Descriptions and Documentation**: Reviewed and refined settings descriptions and documentation to make it easier for users to understand what each setting does, and to configure Optimole to their specific needs.
- **Beaver Builder Compatibility with Cloud Library**: Optimoles Cloud Library is now compatible with Beaver Builder,  allowing users to import and use images from their Optimole account.
- **Progress Bar Design Consistency**: Refined the design of progress bars for a more consistent and polished user interface.

### Bug Fixes

- **Scheduled Crons Removal During Uninstall**: Fixed an issue where scheduled cron jobs were not being removed when uninstalling Optimole.
- **Cloud Library in Safari**: Resolved an issue where the Cloud Library was not functioning correctly in Safari.
- **Guidance on Third-Party Cookies for Cloud Library**: Added instructions on how to allow third-party cookies to ensure seamless functionality of the Cloud Library.

#### [Version 3.10.0](https://github.com/Codeinwp/optimole-wp/compare/v3.9.2...v3.10.0) (2023-09-18)

### New Features
- **Edit Images in Cloud Library**: Images can now be edited before inserting them into your website.
- **Lazyload for Optimole Cloud Library Images**: Images imported from the Optimole Cloud Library now support lazy-loading.
- **Apply Optimization Settings to Cloud Library**: Optimole Cloud Library images now inherit image optimization settings from the plugin.
- **More File Types in Dashboard**: Support for uploading additional file types like documents, videos, text, audio, etc. in the Optimole Dashboard.
- **Add CLI Command for Clearing Image Cache**: New command-line interface option to easily clear the image cache.
- **Best Format Toggle**: Choose to enable or disable automatic best format calculation for images.
- **Generic Lazy-Loading Placeholder Color**: Added customizable color option for the generic lazy-loading placeholder.
- **Improve WooCommerce Product Gallery Lazyload**: Enhanced lazyloading functionality for WooCommerce galleries.
- **Resizing Images in Cloud Library and Dashboard**: Manually resize images by typing dimensions in the editor modal.

### Improvements
- **Explicit Connection Error Handling**: More detailed error messages for failed plugin connection.
- **Unsaved Settings Alert**: The plugin settings page now prompts to confirm leaving the page if settings are unsaved.
- **Better Folder UI/UX in the Dashboard**: Enhanced the user interface and experience for folders on the Optimole Dashboard.
- **Multi-Image Selection UX**: Improved user experience for selecting multiple images in the Optimole Dashboard.

### Bug Fixes
- **Backwards Compatibility**: Fixed offload compatibility issues with WordPress versions below 6.0.
- **Cloud Library Modal Loader not being removed**: In some contexts, the cloud library loader was not disappearing when opening the modal a second time.
- **Visits Banner markup**: Optimole additional visits banner was interfering with modals on the front end of the website.

##### [Version 3.9.2](https://github.com/Codeinwp/optimole-wp/compare/v3.9.1...v3.9.2) (2023-08-10)

### Bug Fixes
- **Fatal error**: In some edge-cases, the Hero Preloader feature was throwing a fatal error, trying to access inexistent posts.
- **Lazyload default status**: Lazyload was off for new users by default when it should have been on.

##### [Version 3.9.1](https://github.com/Codeinwp/optimole-wp/compare/v3.9.0...v3.9.1) (2023-08-07)

### Bug Fixes
- **Fatal error on PHP<7.3**: Fixed fatal error that the plugin was throwing on PHP versions lower than 7.3.
- **PHP Warning**: Fixed warning when inserting an image with a defined height but no width.

#### [Version 3.9.0](https://github.com/Codeinwp/optimole-wp/compare/v3.8.2...v3.9.0) (2023-08-03)

### New Features
- **Hero Image Optimization**: Adds fetch priority for hero images to preload them for enhanced performance.
- **Digital Assets Management (DAM)**: Replaced old media modal integration with a new interface to browse and use the images on your Optimole account.
- **DAM Elementor Compatibility**: Use images from your Optimole account in Elementor Builder.
- **DAM Core Editor Integration**: Integrated DAM with the WordPress core editor.
- **Cloud Library Dashboard**: Added a new Cloud Library page to manage your cloud assets straight from the website dashboard.

### Improvements
- **Image Offloading & Rollback**: Images offloading & rollback process is now ~50% faster and operates in the background.
- **Moving Images**: Instant reflection of image movement to folders inside the Dashboard and DAM.

### Bug Fixes
- **AVIF Disabling Feature**: Fixed issue where serving AVIF format could not be disabled.
- **Optimole Banner Toggling**: Fixed an issue where toggling the Optimole banner would sometimes not save the setting.
- **Dashboard Image Uploading**: Fixes issue where certain images could not be uploaded to the Optimole Dashboard;

##### [Version 3.8.2](https://github.com/Codeinwp/optimole-wp/compare/v3.8.1...v3.8.2) (2023-07-06)

### Bug Fixes
- **Compatibilities loading**: Ensure service is connected before loading compatibilities, throwing errors in some edge cases.
- **Widgets screen layout**: Resolved issue with Optimole banner appearing in the admin inside widget iframes.
- **Cloud library whitelist**: Fixed problem with sites not being removed from the cloud library whitelist.

### Improvements
- **Add Support Link**: Added a support link for Premium users.
- **Removed Native Lazyload Fallback**: Native lazyload fallback has been removed.
- **Add Compatibility for Spectra Blocks**: Introduced compatibility for Spectra Blocks.
- **Add Error Notice**: Added an error notice when the filter length is not three or more characters.

##### [Version 3.8.1](https://github.com/Codeinwp/optimole-wp/compare/v3.8.0...v3.8.1) (2023-06-27)

### Bug Fixes
- **Warning on lower versions of PHP when using Elementor**: Resolved the warning when the plugin was activated, but the service wasnt connected on lower versions of PHP when using Elementor.

#### [Version 3.8.0](https://github.com/Codeinwp/optimole-wp/compare/v3.7.0...v3.8.0) (2023-06-19)

### New features
- **Option for max-width and max-height:** Users can select a maximum width/height for images delivered.
- **Media Library bulk operations:** Users can now efficiently move multiple images at once within the media library folders.
- **Additional visits:** Adds an option to enable a banner on all websites connected to the account promoting Optimole, gaining an additional 20,000 visits.

### Improvements
- **Plugin dashboard UI:** Reworked plugin dashboard to use React and have a more consistent design.
- **Conflict notice:** Adds a notice to inform users about potential conflicts with other plugins.
- **Eliminate Redundant Lazyload Resizing:** Skip unnecessary resizing when lazyloading images.
- **Better search on the service dashboard**: Improved the search functionality to enhance performance and provide faster search results.

### Bug Fixes
- **WEBP extension exclusion:** Fixed an issue where the WEBP image format couldnt be excluded from lazy-load.
- **Elementor compatibility:** Fixes an issue where Elementor backgrounds werent lazy-loaded on some newer versions.
- **Invalid CDN parameters:** Fixes an issue where the image URL might have had invalid values.
- **Upload file names:** Fixes an issue with the offload functionality, where some files were renamed when uploaded.

#### [Version 3.7.0](https://github.com/Codeinwp/optimole-wp/compare/v3.6.1...v3.7.0) (2023-05-15)

### New Features
- **Best Format Optimization:** Implemented a system that automatically chooses the optimal image format, based on smallest resulting file size, to enhance site loading speed.
- **Media Library Folders:** Improved browsing experience in the Media Library by adding the option to organize images into folders.
- **Metadata Strip Option:** Introduced an option to enable/disable the stripping of metadata (EXIF, IPTC, etc.) from the resulting image, providing users with more control over their content.
- **Noscript Tag:** Added a new option to disable the noscript tag in settings, providing more flexibility in configuring your site.
- **Visit Stats by Domain:** Introduced a feature to display visit statistics by domain, aiding in traffic analysis and site optimization.
- **Background Lazyload:** Added compatibility for background lazyload with both Otter Blocks and the core cover block, improving page load times.
- **Hide API Key:** For enhanced security, API keys are now hidden by default.

### Improvements
- **Media Library Stats Formatting:** Improved number formatting for more readable Media Library statistics.
- **Invoices Redesign:** Enhanced the design of invoices for better readability and user experience.
- **User Experience on Fresh Installs:** Improved the user experience of the last images section on fresh installs, providing a more intuitive and engaging user interface.
- **CDN Locations:** Updated the number of CDN locations to 450, offering improved content delivery speeds globally.
- **Generic Placeholder:** Generic placeholders are now enabled by default.
- **Video and Iframe Lazyload:** Lazyload is now enabled by default for videos and iframes to improve page load times.


### Bug Fixes
- **AVIF Option Bug:** Fixed an issue where the AVIF option was causing Microsoft Edge to fallback to JPEG/PNG instead of the intended WEBP format.
- **Divi Blog Archive Pagination:** Resolved a bug that was preventing pagination from working on Divi blog archive templates when Optimole lazyloading was activated.
- **PHP 8.2 Compatibility:** Fixed compatibility issues with PHP 8.2, ensuring smooth functionality across different PHP environments.
- **View Sample Image Button:** Fixed the behaviour of the 'View Sample Image' button to work as intended.
- **3rd Party Plugin Exclusions:** Resolved an issue where images injected by 3rd party plugins were ignoring exclusions in some cases.
- **Search Bar Refresh:** Fixed an issue where the search bar required a page refresh when displaying no results.
- **Upgrade Button Behaviour:** Fixed an issue with the behaviour of the 'Upgrade' button in dashboard cards.
- **Display of Large Image Names:** Fixed a display issue for images with larger names, ensuring all image names are displayed correctly.
- **Login and Signup Form Validation:** Fixed validation errors on the Login and Signup forms to ensure accurate data entry.

##### [Version 3.6.1](https://github.com/Codeinwp/optimole-wp/compare/v3.6.0...v3.6.1) (2023-04-09)

#### Bug Fixes
- **Posts Screen Error**: Resolved an issue with the last releases that show an error on the Posts screen.

#### [Version 3.6.0](https://github.com/Codeinwp/optimole-wp/compare/v3.5.7...v3.6.0) (2023-04-06)

#### Improvements
- **Initial Setup Speed**: Warmed up the cache on connect to speed up the initial setup process, providing a more efficient and seamless user experience.
- **Iframe Lazyload**: Enhanced the iframe lazyload feature by improving conflict detection mechanisms to avoid issues with other plugins or themes.

#### Bug Fixes
- **RSS Feed Lazyload**: Resolved an issue where lazyload replacements were not functioning correctly on RSS feeds.
- **Elementor CSS Image Replacement**: Fixed compatibility issues with newer versions of the Elementor page builder, ensuring proper CSS image replacement.
- **Bullet Symbol Filenames**: Fixed a bug where images with a bullet symbol in the filename were not being optimized correctly.

#### New Features
- **SVG Upload Compatibility**: Added support for SVG file uploads when Optimole is installed, allowing users to work with this popular image format.
- **Central Dashboard Image Upload**: Added the ability to upload images directly to the central dashboard at dashboard.optimole.com for more convenient management.
- **Export Offloaded Source Images**: Implemented the ability to export source images offloaded to Optimole, giving users more control over their image assets.

#### Updates
- **Dependencies and WordPress Compatibility**: Updated dependencies and ensured compatibility with the latest tested WordPress version, guaranteeing smooth integration with the platform.

##### [Version 3.5.7](https://github.com/Codeinwp/optimole-wp/compare/v3.5.6...v3.5.7) (2023-02-23)

* Improved media rollback stability
* Updated GIF lazyload and video conversion default exclusions
* Added notification for existing accounts on auto connect

##### [Version 3.5.6](https://github.com/Codeinwp/optimole-wp/compare/v3.5.5...v3.5.6) (2023-01-31)

* Improved video lazyload

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

Fix edge cases for auto allowing domain on site migration.

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
- Adds filter, `optml_should_avif_ext` , for more control over which images are converted to AVIF, by default SVG images are not converted
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

##### [Version 2.5.6](https://github.com/Codeinwp/optimole-wp/compare/v2.5.5...v2.5.6) (2020-10-19)

* Fixed regression on image lazyload after version v2.5.5

##### [Version 2.5.5](https://github.com/Codeinwp/optimole-wp/compare/v2.5.4...v2.5.5) (2020-10-19)

* Fix compatibility with image urls which had uppercase image extensions
* Improve CLS web vital for the lazyloading mechanism.

##### [Version 2.5.4](https://github.com/Codeinwp/optimole-wp/compare/v2.5.3...v2.5.4) (2020-09-21)

* Fix compatibility with GiveWP
* Fix compatibility with IE browser

##### [Version 2.5.3](https://github.com/Codeinwp/optimole-wp/compare/v2.5.2...v2.5.3) (2020-09-07)

* Improve compatibility with native lazyload from WP 5.5

##### [Version 2.5.2](https://github.com/Codeinwp/optimole-wp/compare/v2.5.1...v2.5.2) (2020-09-02)

* Improve compatibility with various plugins for the video lazyload functionality

##### [Version 2.5.1](https://github.com/Codeinwp/optimole-wp/compare/v2.5.0...v2.5.1) (2020-08-26)

* Fix small bug between native lazyload compatibility and caching plugins

#### [Version 2.5.0](https://github.com/Codeinwp/optimole-wp/compare/v2.4.2...v2.5.0) (2020-08-25)

#### Features
- Adds option to lazyload videos and iframes
- Adds support for browser native lazyload
- Adds support for CSS classes in the optimizations exclusion filters
- Allow local JS serving for the lazyload library using configurable constant

### Fixes
- improve settings description texts
- improve texts for the diagnosis popup report
- adds compatibility with WordPress 5.5

##### [Version 2.4.2](https://github.com/Codeinwp/optimole-wp/compare/v2.4.1...v2.4.2) (2020-07-20)

#### Features
- adds debugger mechanism for troubleshooting various issues with the plugin integration

#### Fixes
- Improve compatibility with SiteGround Optimizer
- Improve compatibility with Swift Performance
- Improve compatibility with TranslatePress 
- Improve compatibility with W3 Total Cache
- Improve compatibility with WP Fastest Cache

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
