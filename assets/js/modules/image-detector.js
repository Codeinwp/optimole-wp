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
   * @param {Map} selectorMap - Map for background selectors
   * @returns {IntersectionObserver} Configured intersection observer
   */
  createIntersectionObserver: function(aboveTheFoldImages, selectorMap) {
    return new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const element = entry.target;
          
          // Handle img elements with data-opt-id
          if (element.tagName === 'IMG') {
            const id = parseInt(element.getAttribute('data-opt-id'), 10);
            if (!isNaN(id) && !aboveTheFoldImages.includes(id)) {
              aboveTheFoldImages.push(id);
            }
          } 
          // Handle background image elements
          else if (element.hasAttribute('data-optml-bg-selector')) {
            const baseSelector = element.getAttribute('data-optml-bg-selector');
            const specificSelector = element.getAttribute('data-optml-specific-selector');
            
            if (baseSelector && specificSelector && selectorMap.has(baseSelector)) {
              // Add this specific selector to the above-fold list for this base selector
              const aboveTheFoldSelectors = selectorMap.get(baseSelector);
              if (!aboveTheFoldSelectors.includes(specificSelector)) {
                aboveTheFoldSelectors.push(specificSelector);
                optmlLogger.info(`Element with selector "${specificSelector}" is above the fold`);
              }
            }
          }
        }
      });
    }, {
      threshold: 0.1 // Consider element visible when 10% is in viewport
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
