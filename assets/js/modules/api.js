/**
 * Optimole API Module
 * Handles communication with the REST API
 */

import { optmlLogger } from './logger.js';
import { optmlStorage } from './storage.js';

/**
 * API communication utilities
 */
export const optmlApi = {
    /**
     * Send data to the REST API using sendBeacon with fetch fallback
     * @param {Object} data - Data to send to the API
     */
    sendToRestApi: function(data) {
      // Use object destructuring for repeated property access
      const { restUrl } = window.optimoleDataOptimizer || {};
      
      if (!restUrl) {
        optmlLogger.error('REST API URL not available');
        return;
      }

      const endpoint = restUrl + '/optimizations';
      const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
      
      // Use sendBeacon to send the data
      const success = navigator.sendBeacon(endpoint, blob);
      
      if (success) {
        optmlLogger.info('Data sent successfully using sendBeacon');
        optmlStorage.markProcessed(data.u, data.d);
      } else {
        optmlLogger.error('Failed to send data using sendBeacon');
        
        // Fallback to fetch if sendBeacon fails
        this._sendWithFetch(endpoint, data);
      }
    },

    /**
     * Fallback method to send data using fetch
     * @private
     * @param {string} endpoint - API endpoint URL
     * @param {Object} data - Data to send
     */
    _sendWithFetch: function(endpoint, data) {
      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(responseData => {
        optmlLogger.info('Data sent successfully using fetch fallback:', responseData);
        optmlStorage.markProcessed(data.u, data.d);
      })
      .catch(error => {
        optmlLogger.error('Error sending data using fetch fallback:', error);
      });
    }
};
