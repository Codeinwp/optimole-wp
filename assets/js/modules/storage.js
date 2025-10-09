/**
 * Optimole Storage Module
 * Handles session storage operations for tracking processed pages
 */

import { optmlLogger } from './logger.js';

/**
 * Storage utilities for managing processed page data
 */
export const optmlStorage = {
    /**
     * Generate storage key for URL and device type combination
     * @param {string} url - Page URL or profile ID
     * @param {number} deviceType - Device type (1=mobile, 2=desktop)
     * @returns {string} Storage key
     */
    getKey: function(url, deviceType) {
      return `optml_pp_${url}_${deviceType}`;
    },
    
    /**
     * Check if a URL/device combination has been processed
     * @param {string} url - Page URL or profile ID
     * @param {number} deviceType - Device type (1=mobile, 2=desktop)
     * @returns {boolean} True if already processed
     */
    isProcessed: function(url, deviceType) {
      try {
        const key = this.getKey(url, deviceType);
        const storedValue = sessionStorage.getItem(key);
        
        if (!storedValue) return false;
        
        // Check if the stored timestamp is still valid (within current session)
        const timestamp = parseInt(storedValue, 10);
        const now = Date.now();
        
        // Consider it valid if it exists in the current session
        return true;
      } catch (e) {
        optmlLogger.error('Error checking sessionStorage:', e);
        return false;
      }
    },
    
    /**
     * Mark a URL/device combination as processed
     * @param {string} url - Page URL or profile ID
     * @param {number} deviceType - Device type (1=mobile, 2=desktop)
     */
    markProcessed: function(url, deviceType) {
      try {
        const key = this.getKey(url, deviceType);
        sessionStorage.setItem(key, Date.now().toString());
      } catch (e) {
        optmlLogger.error('Error setting sessionStorage:', e);
      }
    }
};
