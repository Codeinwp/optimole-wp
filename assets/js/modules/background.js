/**
 * Optimole Background Image Handler Module
 * Handles background image detection and lazy loading observation
 */

import { optmlLogger } from './logger.js';
import { optmlDomUtils } from './dom-utils.js';

/**
 * Background image handling utilities
 */
export const optmlBackground = {
  /**
   * Setup background image observation for lazy loading
   * @param {Array} elements - DOM elements to observe
   * @param {string} selector - CSS selector for the elements
   * @param {Map} selectorMap - Map to track above-fold selectors
   * @param {IntersectionObserver} observer - Intersection observer instance
   * @returns {number} Number of elements waiting for lazy load
   */
  setupBackgroundImageObservation: function(elements, selector, selectorMap, observer) {
    // Create a single shared observer for all elements
    const classObserver = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const element = mutation.target;
          
          // Check if element now has the required class and a background image
          if (element.classList.contains('optml-bg-lazyloaded') && 
              optmlDomUtils.hasBackgroundImage(element)) {
            // Start observing for visibility
            observer.observe(element);
            
            // Stop watching for class changes on this element
            classObserver.disconnect(element);
            
            const specificSelector = element.getAttribute('data-optml-specific-selector');
            optmlLogger.info(`Background element "${specificSelector}" is now observable`);
          }
        }
      }
    });
    
    // Process all elements at once
    const elementsToWatch = [];
    
    elements.forEach(element => {
      // Generate a specific selector for this element
      const specificSelector = optmlDomUtils.getUniqueSelector(element);
      
      // Mark the element with its selectors for identification in the observer
      element.setAttribute('data-optml-bg-selector', selector);
      element.setAttribute('data-optml-specific-selector', specificSelector);
      
      // If already lazyloaded with background image, observe immediately
      if (element.classList.contains('optml-bg-lazyloaded') && 
          optmlDomUtils.hasBackgroundImage(element)) {
        observer.observe(element);
      } else {
        // Otherwise, add to the list to watch for class changes
        elementsToWatch.push(element);
      }
    });
    
    // Set up observation for class changes only on elements that need it
    if (elementsToWatch.length > 0) {
      elementsToWatch.forEach(element => {
        classObserver.observe(element, { attributes: true, attributeFilter: ['class'] });
      });
      
      // Set a timeout to disconnect the observer after a reasonable time
      setTimeout(() => {
        classObserver.disconnect();
        optmlLogger.info(`Stopped waiting for lazyload on ${selector} elements`);
      }, 5000);
    }
    
    return elementsToWatch.length;
  },

  /**
   * Extract background image URLs for elements with specific selectors
   * @param {Array} bgSelectors - Array of CSS selectors
   * @returns {Map} Map of selector -> Map of (specific selector -> URLs)
   */
  extractBackgroundImageUrls: function(bgSelectors) {
    const bgImageUrls = new Map();
    
    if (!bgSelectors || !Array.isArray(bgSelectors)) {
      return bgImageUrls;
    }

    bgSelectors.forEach(selector => {
      const selectorUrlMap = new Map();
      bgImageUrls.set(selector, selectorUrlMap);
      
      try {
        document.querySelectorAll(selector).forEach(element => {
          if (element.classList.contains('optml-bg-lazyloaded')) {
            const bgImage = optmlDomUtils.hasBackgroundImage(element, true);
            
            if (bgImage === false) return;
            
            // Get the specific selector for this element
            const specificSelector = optmlDomUtils.getUniqueSelector(element);
            
            if (!specificSelector) return;
            
            // Extract all URLs from the background-image property
            const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
            
            if (urls && urls.length > 0) {
              selectorUrlMap.set(specificSelector, urls);
              optmlLogger.info(`Found background image URL(s) for "${specificSelector}":`, urls);
            }
          }
        });
      } catch (e) {
        optmlLogger.error('Error extracting background URLs for selector:', selector, e);
      }
    });
    
    return bgImageUrls;
  },

  /**
   * Process background selectors and setup observation
   * @param {Array} bgSelectors - Array of CSS selectors
   * @param {IntersectionObserver} observer - Intersection observer instance
   * @returns {Object} Object containing selectorMap and pendingElements count
   */
  processBackgroundSelectors: function(bgSelectors, observer) {
    const selectorMap = new Map();
    let pendingElements = 0;

    if (!bgSelectors || !Array.isArray(bgSelectors) || bgSelectors.length === 0) {
      return { selectorMap, pendingElements };
    }

    optmlLogger.info('Processing background selectors:', bgSelectors);
    
    bgSelectors.forEach(selector => {
      try {
        const elements = document.querySelectorAll(selector);
        if (elements.length === 0) {
          optmlLogger.warn('No elements found for background selector:', selector);
          return;
        }
        
        // Initialize this selector with an empty array for above-fold elements
        selectorMap.set(selector, []);
        
        // Setup observation for these elements
        pendingElements += this.setupBackgroundImageObservation(
          Array.from(elements), 
          selector, 
          selectorMap, 
          observer
        );
        
        optmlLogger.info(`Processed ${elements.length} elements for background selector: ${selector}`);
      } catch (e) {
        optmlLogger.error('Error processing background selector:', selector, e);
      }
    });

    return { selectorMap, pendingElements };
  }
};
