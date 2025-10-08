/**
 * Optimole Logger Module
 * Provides centralized logging functionality with debug mode support
 */

/**
 * Create utility logger with simplified structure
 */
export const optmlLogger = {
    /**
     * Check if debug mode is enabled
     * @returns {boolean} True if debug mode is active
     */
    isDebug: function() {
      return new URLSearchParams(location.search).has('optml_debug') || 
             localStorage.getItem('optml_debug') !== null;
    },

    /**
     * Generic log method
     * @param {string} level - Log level (info, warn, error)
     * @param {...any} args - Arguments to log
     */
    log: function(level, ...args) {
      if (this.isDebug()) {
        console[level]('[Optimole]', ...args);
      }
    },

    /**
     * Log info messages
     * @param {...any} args - Arguments to log
     */
    info: function(...args) {
      this.log('info', ...args);
    },

    /**
     * Log warning messages
     * @param {...any} args - Arguments to log
     */
    warn: function(...args) {
      this.log('warn', ...args);
    },

    /**
     * Log error messages
     * @param {...any} args - Arguments to log
     */
    error: function(...args) {
      this.log('error', ...args);
    },

    /**
     * Log table data
     * @param {Object|Array} data - Data to display in table format
     */
    table: function(data) {
      if (this.isDebug()) {
        console.log('[Optimole] Table:');
        console.table(data);
      }
    }
};
