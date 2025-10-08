/**
 * Optimole LCP (Largest Contentful Paint) Detection Module
 * Handles LCP element detection and processing
 */

import { optmlLogger } from './logger.js';
import { optmlDomUtils } from './dom-utils.js';

/**
 * LCP detection utilities
 */
export const optmlLcp = {
  /**
   * Detect and process LCP element
   * @returns {Promise<Object>} Promise that resolves with LCP data
   */
  detectLcpElement: async function() {
    // Track LCP element - use a single object to store all LCP data
    const lcpData = {
      element: null,
      imageId: null,
      bgSelector: null,
      bgUrls: null
    };

    // Set up LCP detection - more performant approach
    if (!PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {
      optmlLogger.info('LCP detection not supported in this browser');
      return lcpData;
    }

    // Use a pre-existing LCP entry if available instead of waiting
    const lcpEntries = performance.getEntriesByType('largest-contentful-paint');
    
    if (lcpEntries && lcpEntries.length > 0) {
      // Use the most recent LCP entry
      const lastEntry = lcpEntries[lcpEntries.length - 1];
      if (lastEntry && lastEntry.element) {
        lcpData.element = lastEntry.element;
        optmlLogger.info('LCP element found from existing entries:', lcpData.element);
        this._processLcpElement(lastEntry.element, lcpData);
      }
    } else {
      // If no existing entries, set up observer with shorter timeout
        optmlLogger.info('Setting up LCP observer');
      
      // Create a promise that will resolve when LCP is detected or timeout
      await new Promise(resolve => {
        const lcpObserver = new PerformanceObserver(entryList => {
          const entries = entryList.getEntries();
          if (entries.length === 0) return;
          
          // Use the most recent entry
          const lastEntry = entries[entries.length - 1];
          if (lastEntry && lastEntry.element) {
            lcpData.element = lastEntry.element;
            optmlLogger.info('LCP element detected:', lcpData.element);
            this._processLcpElement(lastEntry.element, lcpData);
          }
          
          lcpObserver.disconnect();
          resolve();
        });
        
        lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
        
        // Use a shorter timeout - most LCP elements are detected within 1-2 seconds
        setTimeout(() => {
          lcpObserver.disconnect();
          resolve();
        }, 1500);
      });
    }

    return lcpData;
  },

  /**
   * Process LCP element to extract relevant data
   * @private
   * @param {Element} element - LCP element
   * @param {Object} lcpData - LCP data object to populate
   */
  _processLcpElement: function(element, lcpData) {
    if (!element) return;
    
    // Check if LCP element is an image with data-opt-id
    if (element.tagName === 'IMG') {
      const id = element.getAttribute('data-opt-id');
      if (id) {
        lcpData.imageId = parseInt(id, 10);
        optmlLogger.info('LCP element is an Optimole image with ID:', lcpData.imageId);
      }
    } 
    // Check if LCP element has a background image
    else {
      const bgImage = optmlDomUtils.hasBackgroundImage(element, true);
      
      if (bgImage !== false) {
        lcpData.bgSelector = optmlDomUtils.getUniqueSelector(element);
        lcpData.bgUrls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
        
        optmlLogger.info('LCP element has background image:', lcpData.bgSelector, lcpData.bgUrls);
      }
    }
  }
};
