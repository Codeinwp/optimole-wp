/**
 * Optimole Main Module
 * Main orchestrator that coordinates all image detection functionality
 */

import { optmlLogger } from './logger.js';
import { optmlStorage } from './storage.js';
import { optmlDevice } from './device.js';
import { optmlApi } from './api.js';
import { optmlDomUtils } from './dom-utils.js';
import { optmlBackground } from './background.js';
import { optmlLcp } from './lcp.js';
import { optmlImageDetector } from './image-detector.js';
import { optmlSrcsetDetector } from './srcset-detector.js';

/**
 * Main detection functionality
 */
export const optmlMain = {
  /**
   * Run the page profiling process to detect images, background selectors, and LCP element
   * @returns {Promise<Object|null>} Detection results or null
   */
  runProfiling: async function() {
    // Check for zero-dimension viewports and hidden pages
    const pageConditions = optmlDomUtils.checkPageConditions();

    if (!pageConditions.hasValidViewport) {
      optmlLogger.info('Window must have non-zero dimensions for image detection.');
      return null;
    }

    if (!pageConditions.isVisible) {
      optmlLogger.info('Page opened in background tab so image detection is not performed.');
      return null;
    }

    // Use object destructuring for repeated property access
    const { pageProfileId, missingDevices, bgSelectors } = window.optimoleDataOptimizer || {};
    const deviceType = optmlDevice.getDeviceType();
    const url = pageProfileId;
    const missingDevicesArray = missingDevices ? missingDevices.split(',') : [];
    
    optmlLogger.info('Device Type:', deviceType);
    optmlLogger.info('Missing Devices:', missingDevicesArray);
    optmlLogger.info('Profile ID:', pageProfileId);
    optmlLogger.info('Background Selectors:', bgSelectors || 'None provided');
    
    // Check if this device type is needed
    if (!missingDevicesArray.includes(deviceType.toString())) {
      optmlLogger.info('Skipping device type, data already exists:', deviceType);
      return null;
    }
    
    // Check if we've already processed this device/URL combination
    if (optmlStorage.isProcessed(url, deviceType)) {
      optmlLogger.info('Skipping detection, already processed this device/URL combination');
      return null;
    }

    // Check if user is at the top of the page
    const isAtTopOfPage = window.pageYOffset === 0 || document.documentElement.scrollTop === 0;
    if (!isAtTopOfPage) {
      optmlLogger.info('User is not at the top of the page, skipping image detection');
      return null;
    }

    // Wait until the resources on the page have fully loaded
    if (!pageConditions.isComplete && window.optmlDomUtils) {
      optmlLogger.info('Waiting for page to fully load...');
      await optmlDomUtils.waitForPageLoad();
      optmlLogger.info('Page fully loaded, proceeding with detection');
    }

    // Wait for images in viewport to load
    optmlLogger.info('Waiting for viewport images to load...');
    await optmlDomUtils.waitForViewportImages(1500); // 1.5 second delay for images
    optmlLogger.info('Viewport images loaded, proceeding with detection');

    // Wait for browser idle time to run detection
    await optmlDomUtils.waitForIdleTime();

    // Detect LCP element
    const lcpData = await optmlLcp.detectLcpElement();

    // Track page visibility and window resize
    let isPageVisible = document.visibilityState !== 'hidden';
    let didWindowResize = false;
    
    // Set up debounced resize handler
    const resizeHandler = optmlDomUtils.debounce(() => {
      didWindowResize = true;
      optmlLogger.info('Window resized during detection, results may be affected');
    }, 100);
    
    // Set up visibility change handler
    const visibilityChangeHandler = () => {
      isPageVisible = document.visibilityState !== 'hidden';
      optmlLogger.info('Page visibility changed:', isPageVisible ? 'visible' : 'hidden');
    };
    
    // Add event listeners with passive option for better performance
    window.addEventListener('resize', resizeHandler, { passive: true });
    document.addEventListener('visibilitychange', visibilityChangeHandler);

    // Use IntersectionObserver instead of getBoundingClientRect for better performance
    const aboveTheFoldImages = [];
    
    // Create intersection observer
    let selectorMap = new Map();
    const observer = optmlImageDetector.createIntersectionObserver(aboveTheFoldImages, selectorMap);
    
    // Observe Optimole images
    const { allOptimoleImages } = optmlImageDetector.observeOptimoleImages(observer);
    
    // Detect images with missing dimensions
    const imageDimensionsData = optmlImageDetector.detectImageDimensions(allOptimoleImages);
    
    optmlLogger.info('Images with missing dimensions found:', Object.keys(imageDimensionsData).length);
    
    // Detect images requiring srcset variations (non-lazyload images)
    const srcsetResult = await optmlSrcsetDetector.detectMissingSrcsets();
    const srcsetData = srcsetResult.srcset;
    const cropStatusData = srcsetResult.crop;
    
    optmlLogger.info('Images requiring srcset variations found:', Object.keys(srcsetData).length);
    optmlLogger.info('Images with crop status found:', Object.keys(cropStatusData).length);
    
    // Process background image selectors if available
    let bgImageUrls = new Map();
    let pendingElements = 0;

    if (bgSelectors && Array.isArray(bgSelectors) && bgSelectors.length > 0) {
      const backgroundResult = optmlBackground.processBackgroundSelectors(bgSelectors, observer);
      selectorMap = backgroundResult.selectorMap;
      pendingElements = backgroundResult.pendingElements;

      // Extract background image URLs
      bgImageUrls = optmlBackground.extractBackgroundImageUrls(bgSelectors);
      
      // Adjust wait time based on whether we have pending elements
      const waitTime = pendingElements > 0 ? 600 : 300;
      optmlLogger.info(`Waiting ${waitTime}ms for ${pendingElements} pending background elements`);
      await new Promise(resolve => setTimeout(resolve, waitTime));
    } else {
      // Standard wait time if no background selectors
      await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    // Disconnect observer and clean up event listeners
    observer.disconnect();
    window.removeEventListener('resize', resizeHandler);
    document.removeEventListener('visibilitychange', visibilityChangeHandler);
    
    // Clean up temporary attributes
    optmlImageDetector.cleanupBackgroundElements();
    
    // Check conditions that might affect accuracy
    if (didWindowResize) {
      optmlLogger.warn('Window was resized during detection, results may not be accurate');
    }
    
    if (!isPageVisible) {
      optmlLogger.warn('Page became hidden during detection, results may not be accurate');
    }
     
    // Log results
    optmlLogger.info('Above the fold images with data-opt-id:', aboveTheFoldImages);
    optmlLogger.info('Background selectors:', selectorMap);
    
    // Prepare and send data if we found any images, background selectors, dimension data, or srcset data
    if (aboveTheFoldImages.length > 0 || selectorMap.size > 0 || lcpData.imageId || lcpData.bgSelector || Object.keys(imageDimensionsData).length > 0 || Object.keys(srcsetData).length > 0) {
      // Convert the Map to a plain object for the API
      const processedBgSelectors = this._processBackgroundSelectors(selectorMap, bgImageUrls);
      
      // Prepare the data object with LCP information using shorter key names
      const data = {
        d: deviceType,
        a: aboveTheFoldImages,
        b: processedBgSelectors,
        u: url,
        t: window.optimoleDataOptimizer ? window.optimoleDataOptimizer._t : null,
        h: window.optimoleDataOptimizer ? window.optimoleDataOptimizer.hmac : null,
        pu: window.optimoleDataOptimizer ? window.optimoleDataOptimizer.pageProfileUrl : null,
        l: { 
          i: lcpData.imageId,
          s: lcpData.bgSelector, 
          u: lcpData.bgUrls   
        },
        m: imageDimensionsData, // m for missing dimensions
        s: srcsetData, // s for srcset data
        c: cropStatusData // c for crop status data
      };
      
      optmlLogger.info('Sending data with LCP information:', { 
        lcpImageId: lcpData.imageId, 
        lcpBgSelector: lcpData.bgSelector,
        lcpBgUrls: lcpData.bgUrls
      });
      optmlLogger.info('Sending background selectors:', processedBgSelectors);
      optmlLogger.info('Sending dimension data for images:', imageDimensionsData);
      optmlLogger.info('Sending srcset data for images:', srcsetData);
      optmlLogger.info('Sending crop status data for images:', cropStatusData);
      
      optmlApi.sendToRestApi(data);
      return data;
    } else {
      optmlLogger.info('No above-the-fold images, background elements, LCP elements, dimension data, or srcset data found');
      return null;
    }
  },

  /**
   * Process background selectors for API submission
   * @private
   * @param {Map} selectorMap - Map of selectors to above-fold elements
   * @param {Map} bgImageUrls - Map of background image URLs
   * @returns {Object} Processed background selectors object
   */
  _processBackgroundSelectors: function(selectorMap, bgImageUrls) {
    const processedBgSelectors = {};
    
    // Process each selector that's present on the page
    selectorMap.forEach((specificSelectors, baseSelector) => {
      // Initialize the object for this base selector
      processedBgSelectors[baseSelector] = {};
      
      // For each specific selector, add its URLs if available
      specificSelectors.forEach(specificSelector => {
        // First, add the selector to indicate it's above the fold
        processedBgSelectors[baseSelector][specificSelector] = null;
        
        // Then, if we have URLs for this selector, add them
        if (bgImageUrls.has(baseSelector) && 
            bgImageUrls.get(baseSelector).has(specificSelector)) { 
          processedBgSelectors[baseSelector][specificSelector] = 
            bgImageUrls.get(baseSelector).get(specificSelector);
        }
      });
    });

    return processedBgSelectors;
  }
};
