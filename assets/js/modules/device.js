/**
 * Optimole Device Detection Module
 * Handles device type detection based on screen width
 */

import { optmlLogger } from './logger.js';

/**
 * Device detection utilities
 */
export const optmlDevice = {
    /**
     * Device type constants
     */
    DEVICE_TYPES: {
      MOBILE: 1,
      DESKTOP: 2
    },

    /**
     * Screen width threshold for mobile/desktop detection
     * This is similar to what PageSpeed Insights uses
     */
    MOBILE_BREAKPOINT: 600,

    /**
     * Determine device type based on screen width
     * @returns {number} Device type (1=mobile, 2=desktop)
     */
    getDeviceType: function() {
      const width = window.innerWidth;
      
      if (width <= this.MOBILE_BREAKPOINT) {
      optmlLogger.info('Device detected as mobile based on width:', width);
        return this.DEVICE_TYPES.MOBILE;
      }
      
      optmlLogger.info('Device detected as desktop based on width:', width);
      return this.DEVICE_TYPES.DESKTOP;
    },

    /**
     * Check if current device is mobile
     * @returns {boolean} True if mobile device
     */
    isMobile: function() {
      return this.getDeviceType() === this.DEVICE_TYPES.MOBILE;
    },

    /**
     * Check if current device is desktop
     * @returns {boolean} True if desktop device
     */
    isDesktop: function() {
      return this.getDeviceType() === this.DEVICE_TYPES.DESKTOP;
    }
};
