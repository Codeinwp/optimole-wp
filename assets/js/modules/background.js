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
    /**
     * Observe a background element, re-observing so an element already observed as an Optimole image
     * gets a fresh callback (its first one ran before it was marked as a background)
     * @param {Element} element - Background element to observe
     * @returns {void}
     */
    const observeBackground = element => {
      observer.unobserve(element);
      observer.observe(element);
    };

    // Elements still waiting for the lazyload class
    const elementsToWatch = new Set();

    // Create a single shared observer for all elements
    // Only class changes are delivered (see attributeFilter below)
    const classObserver = new MutationObserver((mutations) => {
      mutations.forEach(({ target: element }) => {
        // Check if element now has the required class and a background image
        if (!elementsToWatch.has(element) ||
            !element.classList.contains('optml-bg-lazyloaded') ||
            !optmlDomUtils.hasBackgroundImage(element)) {
          return;
        }

        // Start observing for visibility
        observeBackground(element);

        // disconnect() stops watching every element, so wait until none is pending
        elementsToWatch.delete(element);
        if (elementsToWatch.size === 0) {
          classObserver.disconnect();
        }

        const specificSelector = element.getAttribute('data-optml-specific-selector');
        optmlLogger.info(`Background element "${specificSelector}" is now observable`);
      });
    });
    
    elements.forEach(element => {
      // Generate a specific selector for this element
      const specificSelector = optmlDomUtils.getUniqueSelector(element);
      
      // Mark the element with its selectors for identification in the observer
      element.setAttribute('data-optml-bg-selector', selector);
      element.setAttribute('data-optml-specific-selector', specificSelector);
      
      // If already lazyloaded with background image, observe immediately
      if (element.classList.contains('optml-bg-lazyloaded') && 
          optmlDomUtils.hasBackgroundImage(element)) {
        observeBackground(element);
      } else {
        // Otherwise, add to the list to watch for class changes
        elementsToWatch.add(element);
      }
    });
    
    // Set up observation for class changes only on elements that need it
    if (elementsToWatch.size > 0) {
      elementsToWatch.forEach(element => {
        classObserver.observe(element, { attributes: true, attributeFilter: ['class'] });
      });
      
      // Set a timeout to disconnect the observer after a reasonable time
      setTimeout(() => {
        classObserver.disconnect();
        optmlLogger.info(`Stopped waiting for lazyload on ${selector} elements`);
      }, 5000);
    }
    
    return elementsToWatch.size;
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
   * @param {Map<string, Array<string>>} [selectorMap] - Map the observer records above-fold selectors into; must be the same instance passed to the observer
   * @returns {{selectorMap: Map<string, Array<string>>, pendingElements: number}} Object containing selectorMap and pendingElements count
   */
  processBackgroundSelectors: function(bgSelectors, observer, selectorMap = new Map()) {
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
