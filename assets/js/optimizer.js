/**
 * Optimole Image Optimizer - Main Entry Point
 * 
 * Detects images with data-opt-id attribute that are above the fold
 * and logs them along with the current device type
 * 
 * This file imports and orchestrates all the modular components:
 * - Logger utilities
 * - Storage management  
 * - Device detection
 * - API communication
 * - DOM utilities
 * - Background image handling
 * - LCP detection
 * - Image detection and analysis
 */

// Import all modules
import { optmlMain } from './modules/main.js';

(function() {
  'use strict';

  /**
   * Initialize the image detection process
   */
  function initializeOptimizer() {
    if (optmlMain && optmlMain.runProfiling) {
      optmlMain.runProfiling();
    } else {
      console.error('[Optimole] Main module not available');
    }
  }

  // Ensure the DOM is loaded before running detection
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeOptimizer);
  } else {
    initializeOptimizer();
  }
})();
