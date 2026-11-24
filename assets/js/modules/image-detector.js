/**
 * Optimole Image Detection Module
 * Handles above-the-fold image detection and dimension analysis
 */

import { optmlLogger } from './logger.js';

/**
 * Image detection utilities
 */
export const optmlImageDetector = {
  /**
   * Detect images with missing width/height attributes and calculate their dimensions
   * @param {NodeList} images - Collection of image elements
   * @returns {Object} Object mapping image IDs to their dimensions
   */
  detectImageDimensions: function(images) {
    const imageDimensions = {};
    
    images.forEach(img => {
      try {
        const id = parseInt(img.getAttribute('data-opt-id'), 10);
        if (isNaN(id)) return;
        
        const hasOptSrc = img.hasAttribute('data-opt-src');
        const hasOptLazyLoaded = img.hasAttribute('data-opt-lazy-loaded');
        
        // If image has data-opt-src and data-opt-lazy-loaded, use optimized dimensions to fix https://github.com/Codeinwp/optimole-service/issues/1588#issuecomment-3373656185
        if (hasOptSrc && hasOptLazyLoaded) {
          const optimizedWidth = parseInt(img.getAttribute('data-opt-optimized-width'), 10);
          const optimizedHeight = parseInt(img.getAttribute('data-opt-optimized-height'), 10);
          
          if (!isNaN(optimizedWidth) && !isNaN(optimizedHeight) && optimizedWidth > 0 && optimizedHeight > 0) {
            imageDimensions[id] = {
              w: optimizedWidth,
              h: optimizedHeight
            };
            
            optmlLogger.info(`Image ${id} using optimized dimensions:`, {
              optimizedDimensions: `${optimizedWidth}x${optimizedHeight}`
            });
            return; // Skip further processing for this image
          }
        }
        
        const hasWidth = img.hasAttribute('width') && img.getAttribute('width') !== '';
        const hasHeight = img.hasAttribute('height') && img.getAttribute('height') !== '';
        
        // Only process images that are missing width or height attributes
        if (!hasWidth || !hasHeight) {
          const naturalWidth = img.naturalWidth || 0;
          const naturalHeight = img.naturalHeight || 0;
          
          // Only add if we have valid natural dimensions
          if (naturalWidth > 0 && naturalHeight > 0) {
            imageDimensions[id] = {
              w: naturalWidth,
              h: naturalHeight
            };
            
            optmlLogger.info(`Image ${id} missing dimensions:`, {
              missingWidth: !hasWidth,
              missingHeight: !hasHeight,
              naturalDimensions: `${naturalWidth}x${naturalHeight}`
            });
          }
        }
      } catch (error) {
        optmlLogger.error('Error detecting dimensions for image:', img, error);
      }
    });
    
    return imageDimensions;
  },

  /**
   * Setup intersection observer for image detection
   * @param {Array} aboveTheFoldImages - Array to store above-fold image IDs
   * @param {Map<string, Array<string>>} selectorMap - Map of base selector to above-fold specific selectors
   * @returns {IntersectionObserver} Configured intersection observer
   */
  createIntersectionObserver: function(aboveTheFoldImages, selectorMap) {
    return new IntersectionObserver(entries => {
      this.recordIntersections(entries, aboveTheFoldImages, selectorMap);
    }, {
      threshold: 0.1 // Consider element visible when 10% is in viewport
    });
  },

  /**
   * Record the above-fold images and background elements of intersection entries
   * @param {Array<IntersectionObserverEntry>} entries - Entries reported by the observer
   * @param {Array<number>} aboveTheFoldImages - Array to store above-fold image IDs
   * @param {Map<string, Array<string>>} selectorMap - Map of base selector to above-fold specific selectors
   * @returns {void}
   */
  recordIntersections: function(entries, aboveTheFoldImages, selectorMap) {
    entries.forEach(entry => {
      if (!entry.isIntersecting) {
        return;
      }
      const element = entry.target;

      // Handle img elements with data-opt-id
      if (element.tagName === 'IMG') {
        const id = parseInt(element.getAttribute('data-opt-id'), 10);
        if (!isNaN(id) && !aboveTheFoldImages.includes(id)) {
          aboveTheFoldImages.push(id);
        }
      }

      // An img can also carry a background image, so this is checked independently.
      if (element.hasAttribute('data-optml-bg-selector')) {
        this.recordAboveFoldBackground(element, selectorMap);
      }
    });
  },

  /**
   * Record elements observed shortly before the observer is disconnected
   * @param {IntersectionObserver} observer - Observer created by createIntersectionObserver
   * @param {Array<number>} aboveTheFoldImages - Array to store above-fold image IDs
   * @param {Map<string, Array<string>>} selectorMap - Map of base selector to above-fold specific selectors
   * @returns {Promise<void>} Resolves once the queued entries are recorded
   */
  flushIntersectionObserver: async function(observer, aboveTheFoldImages, selectorMap) {
    // A new observation is computed only at the next rendering; hidden tabs render no frames, hence the timeout.
    await Promise.race([
      new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))),
      new Promise(resolve => setTimeout(resolve, 100))
    ]);
    this.recordIntersections(observer.takeRecords(), aboveTheFoldImages, selectorMap);
  },

  /**
   * Record a background element under every base selector it matches
   * @param {Element} element - Above-the-fold background element
   * @param {Map<string, Array<string>>} selectorMap - Map of base selector to above-fold specific selectors
   * @returns {void}
   */
  recordAboveFoldBackground: function(element, selectorMap) {
    const specificSelector = element.getAttribute('data-optml-specific-selector');
    if (!specificSelector) {
      return;
    }

    // An element can match several watchers; each one needs it listed or the server hides it.
    selectorMap.forEach((aboveTheFoldSelectors, baseSelector) => {
      if (element.matches(baseSelector) && !aboveTheFoldSelectors.includes(specificSelector)) {
        aboveTheFoldSelectors.push(specificSelector);
        optmlLogger.info(`Element with selector "${specificSelector}" is above the fold`);
      }
    });
  },

  /**
   * Observe all images with data-opt-id attributes
   * @param {IntersectionObserver} observer - Intersection observer instance
   * @returns {Object} Object containing images and observedElements map
   */
  observeOptimoleImages: function(observer) {
    // Get all images with data-opt-id for processing
    const allOptimoleImages = document.querySelectorAll('img[data-opt-id]');
    const observedElements = new Map();
    
    // Observe all images with data-opt-id
    allOptimoleImages.forEach(img => {
      const id = parseInt(img.getAttribute('data-opt-id'), 10);
      if (isNaN(id)) {
        optmlLogger.warn('Invalid data-opt-id:', img.getAttribute('data-opt-id'));
        return;
      }
      observedElements.set(img, id);
      observer.observe(img);
    });

    return { allOptimoleImages, observedElements };
  },

  /**
   * Clean up temporary data attributes from background elements
   */
  cleanupBackgroundElements: function() {
    // After observation is complete, process below-the-fold elements
    document.querySelectorAll('[data-optml-bg-selector]').forEach(element => { 
      // Remove temporary data attributes
      element.removeAttribute('data-optml-bg-selector');
      element.removeAttribute('data-optml-specific-selector');
    });
  }
};
