/**
 * Optimole DOM Utilities Module
 * Provides DOM manipulation and element detection utilities
 */

/**
 * DOM utilities for element manipulation and detection
 */
export const optmlDomUtils = {
    /**
     * Utility function for debouncing
     * @param {Function} fn - Function to debounce
     * @param {number} delay - Delay in milliseconds
     * @returns {Function} Debounced function
     */
    debounce: function(fn, delay) {
      let timer;
      return function() {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, arguments), delay);
      };
    },

    /**
     * Generate a unique selector for an element
     * @param {Element} element - DOM element
     * @returns {string} Unique CSS selector
     */
    getUniqueSelector: function(element) {
      if (!element || element === document.body) return 'body';
      
      // Use ID if available - fastest path
      if (element.id) {
        return `#${element.id}`;
      }
      
      const tag = element.tagName.toLowerCase();
      
      // Optimize class name processing
      let className = '';
      if (element.className && typeof element.className === 'string') {
        // Only process if needed
        if (element.className.includes('optml-bg-lazyloaded')) {
          className = '.' + element.className.trim()
            .split(/\s+/)
            .filter(cls => cls !== 'optml-bg-lazyloaded')
            .join('.');
        } else {
          // Avoid unnecessary split/filter/join when no filtering needed
          className = '.' + element.className.trim().replace(/\s+/g, '.');
        }
      }
      
      // Get parent selector - but limit recursion depth for performance
      const parentElement = element.parentElement;
      if (!parentElement || parentElement === document.body) {
        return `body > ${tag}${className}`;
      }
      
      // Optimize sibling calculation - only do this work if necessary
      let nthTypeSelector = '';
      const siblings = parentElement.children;
      let siblingCount = 0;
      let position = 0;
      
      for (let i = 0; i < siblings.length; i++) {
        if (siblings[i].tagName === element.tagName) {
          siblingCount++;
          if (siblings[i] === element) {
            position = siblingCount;
          }
        }
      }
      
      if (siblingCount > 1) {
        nthTypeSelector = `:nth-of-type(${position})`;
      }
      
      // Limit recursion depth to avoid performance issues with deeply nested DOM
      // Use a simpler parent selector if we're already several levels deep
      const parentSelector = parentElement.id ? 
        `#${parentElement.id}` : 
        this.getUniqueSelector(parentElement);
      
      return `${parentSelector} > ${tag}${className}${nthTypeSelector}`;
    },

    /**
     * Check if an element has a background image
     * @param {Element} element - DOM element to check
     * @param {boolean} returnUrl - Whether to return the URL or just boolean
     * @returns {boolean|string} True/false or background image URL
     */
    hasBackgroundImage: function(element, returnUrl = false) {
      // Use getComputedStyle for accurate results, but only once per element
      const style = window.getComputedStyle(element);
      const bgImage = style.backgroundImage;
      
      // Check if the background image is a URL (not 'none')
      return (bgImage && bgImage !== 'none' && bgImage.includes('url(')) ? 
        (returnUrl ? bgImage : true) : false;
    },

    /**
     * Extract URLs from background-image CSS property
     * @param {string} bgImage - Background image CSS value
     * @returns {Array|null} Array of URLs or null
     */
    extractUrlsFromBgImage: function(bgImage) {
      if (!bgImage) return null;
      
      const urls = [];
      const regex = /url\(['"]?(.*?)['"]?\)/g;
      let match;
      
      while ((match = regex.exec(bgImage)) !== null) {
        if (match[1]) urls.push(match[1]);
      }
      
      return urls.length > 0 ? urls : null;
    },

    /**
     * Check viewport and page visibility conditions
     * @returns {Object} Object with validity checks
     */
    checkPageConditions: function() {
      return {
        hasValidViewport: window.innerWidth > 0 && window.innerHeight > 0,
        isVisible: document.visibilityState !== 'hidden' || document.prerendering,
        isComplete: document.readyState === 'complete'
      };
    },

    /**
     * Wait for page to be fully loaded
     * @returns {Promise} Promise that resolves when page is loaded
     */
    waitForPageLoad: function() {
      if (document.readyState === 'complete') {
        return Promise.resolve();
      }

      return new Promise(resolve => {
        window.addEventListener('load', resolve, { once: true });
      });
    },

    /**
     * Wait for browser idle time
     * @returns {Promise} Promise that resolves during idle time
     */
    waitForIdleTime: function() {
      return new Promise(resolve => {
        if (typeof requestIdleCallback === 'function') {
          requestIdleCallback(resolve);
        } else {
          setTimeout(resolve, 200);
        }
      });
    },

    /**
     * Wait for images in viewport to load
     * @param {number} delay - Additional delay in milliseconds (default: 1000)
     * @returns {Promise} Promise that resolves after images are loaded
     */
    waitForViewportImages: function(delay = 1000) {
      return new Promise(resolve => {
        // Wait for page load first
        if (document.readyState !== 'complete') {
          window.addEventListener('load', () => {
            setTimeout(resolve, delay);
          }, { once: true });
        } else {
          // Page is already loaded, add delay for images to load
          setTimeout(resolve, delay);
        }
      });
    }
};
