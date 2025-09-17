/**
 * Optimole Srcset Detection Module
 * Detects images without data-opt-src (lazyload) and calculates missing srcset variations
 * for different device types and viewport widths
 */

import { optmlLogger } from './logger.js';
import { optmlDevice } from './device.js';

/**
 * Srcset detection and calculation utilities
 */
export const optmlSrcsetDetector = {
  /**
   * Common device breakpoints for responsive images
   * Based on popular CSS frameworks and real-world usage patterns
   */
  DEVICE_BREAKPOINTS: {
    // Mobile breakpoints
    MOBILE_SMALL: 320,   // Small mobile devices
    MOBILE_MEDIUM: 375,  // Medium mobile devices
    MOBILE_LARGE: 425,   // Large mobile devices
    
    // Tablet breakpoints  
    TABLET_SMALL: 768,   // Small tablets
    TABLET_LARGE: 1024,  // Large tablets
    
    // Desktop breakpoints
    DESKTOP_SMALL: 1200, // Small desktop
    DESKTOP_MEDIUM: 1440, // Medium desktop
    DESKTOP_LARGE: 1920,  // Large desktop
    DESKTOP_XL: 2560      // Extra large desktop
  },

  /**
   * Device pixel ratio multipliers for high-DPI displays
   */
  DPR_MULTIPLIERS: [1, 2],

  /**
   * Configuration for srcset generation
   */
  CONFIG: {
    // Step size for width variations (in pixels) - not used in responsive mode
    WIDTH_STEP_SIZE: 100,
    
    // Minimum image size to consider (in pixels)
    MIN_SIZE: 200,
    
    // Maximum number of srcset variations per image
    MAX_VARIATIONS: 8,
    
    // Tolerance for existing srcset sizes (in pixels)
    SIZE_TOLERANCE: 50
  },

  /**
   * Configure srcset generation settings
   * @param {Object} config - Configuration options
   * @param {number} config.widthStepSize - Step size for width variations (default: 100)
   * @param {number} config.minSize - Minimum image size to consider (default: 100)
   * @param {number} config.maxVariations - Maximum srcset variations per image (default: 8)
   * @param {number} config.sizeTolerance - Tolerance for existing sizes (default: 50)
   */
  configure: function(config) {
    if (config.widthStepSize) this.CONFIG.WIDTH_STEP_SIZE = config.widthStepSize;
    if (config.minSize) this.CONFIG.MIN_SIZE = config.minSize;
    if (config.maxVariations) this.CONFIG.MAX_VARIATIONS = config.maxVariations;
    if (config.sizeTolerance) this.CONFIG.SIZE_TOLERANCE = config.sizeTolerance;
    
    optmlLogger.info('Srcset detector configured:', this.CONFIG);
  },

  /**
   * Detect all Optimole images that are NOT using lazyload (no data-opt-src)
   * and calculate missing srcset variations
   * @returns {Object} Object mapping image IDs to their required srcset data
   */
  detectMissingSrcsets: function() {
    const missingSrcsetData = {};
    
    // Find all Optimole images
    const optimoleImages = document.querySelectorAll('img[data-opt-id]');
    
    optimoleImages.forEach(img => {
      try {
        const imageId = parseInt(img.getAttribute('data-opt-id'), 10);
        if (isNaN(imageId)) return;
        
        // Improved skip logic:
        // - Include if image doesn't have data-opt-src
        // - Include if image has data-opt-src but also has data-opt-lazy-loaded
        // - Skip otherwise
        const hasOptSrc = img.hasAttribute('data-opt-src');
        const hasLazyLoaded = img.hasAttribute('data-opt-lazy-loaded');
        
        const shouldInclude = !hasOptSrc || (hasOptSrc && hasLazyLoaded);
        
        if (shouldInclude) {
          const reason = !hasOptSrc ? 
            'not using lazyload' : 
            'lazyload completed (has data-opt-lazy-loaded)';
          optmlLogger.info(`Image ${imageId} ${reason}, analyzing srcset requirements`);
          
          // Analyze the image and calculate required srcset variations
          const missingSizes = this._analyzeSrcsetRequirements(img, imageId);
          
          if (missingSizes && missingSizes.length > 0) {
            missingSrcsetData[imageId] = missingSizes;
          }
        }
      } catch (error) {
        optmlLogger.error('Error analyzing image for srcset:', img, error);
      }
    });
    
    optmlLogger.info('Images requiring srcset variations:', Object.keys(missingSrcsetData).length);
    return missingSrcsetData;
  },

  /**
   * Analyze an image and determine what srcset variations are needed
   * @private
   * @param {HTMLImageElement} img - Image element to analyze
   * @param {number} imageId - Optimole image ID
   * @returns {Object|null} Srcset requirements data or null
   */
  _analyzeSrcsetRequirements: function(img, imageId) {
    // Get current image dimensions
    const currentWidth = img.offsetWidth || img.clientWidth;
    const currentHeight = img.offsetHeight || img.clientHeight;
    const naturalWidth = img.naturalWidth || 0;
    const naturalHeight = img.naturalHeight || 0;
    
    // Skip if we can't determine dimensions
    if (!currentWidth || !currentHeight || !naturalWidth || !naturalHeight) {
      optmlLogger.warn(`Skipping image ${imageId}: insufficient dimension data`);
      return null;
    }
    
    optmlLogger.info(`Analyzing image ${imageId}:`, {
      current: `${currentWidth}x${currentHeight}`,
      natural: `${naturalWidth}x${naturalHeight}`
    });
    
    // Calculate aspect ratio
    const aspectRatio = naturalWidth / naturalHeight;
    
    // Get current device type
    const currentDeviceType = optmlDevice.getDeviceType();
    
    // Calculate required sizes for different breakpoints
    const requiredSizes = this._calculateRequiredSizes(
      currentWidth, 
      currentHeight, 
      aspectRatio,
      currentDeviceType,
      naturalWidth,
      naturalHeight
    );
    
    // Check if current image has existing srcset
    const existingSrcset = img.getAttribute('srcset');
    const existingSizes = this._parseExistingSrcset(existingSrcset);
    
    // Determine missing sizes
    const missingSizes = this._findMissingSizes(requiredSizes, existingSizes);
    
    if (missingSizes.length === 0) {
      optmlLogger.info(`Image ${imageId} already has adequate srcset coverage`);
      return null;
    }
    
    // Log full analysis for debugging
    optmlLogger.info(`Image ${imageId} srcset analysis:`, {
      currentSize: { w: currentWidth, h: currentHeight },
      naturalSize: { w: naturalWidth, h: naturalHeight },
      aspectRatio: Math.round(aspectRatio * 1000) / 1000,
      deviceType: currentDeviceType,
      missingSizes: missingSizes,
      existingSrcset: existingSrcset || null
    });
    
    // Return only essential fields for API (ultra-minimal payload with short names)
    return missingSizes.map(size => ({
      w: size.w,
      h: size.h,
      d: size.dpr,        // dpr -> d
      s: size.descriptor, // descriptor -> s (srcset)
      b: size.breakpoint  // breakpoint -> b
    }));
  },

  /**
   * Calculate required image sizes for different breakpoints and DPR
   * @private
   * @param {number} currentWidth - Current displayed width
   * @param {number} currentHeight - Current displayed height
   * @param {number} aspectRatio - Image aspect ratio
   * @param {number} currentDeviceType - Current device type
   * @param {number} naturalWidth - Natural image width
   * @param {number} naturalHeight - Natural image height
   * @returns {Array} Array of required size objects
   */
  _calculateRequiredSizes: function(currentWidth, currentHeight, aspectRatio, currentDeviceType, naturalWidth, naturalHeight) {
    const requiredSizes = [];
    
    // Generate responsive sizes based on common viewport widths and typical image usage
    const responsiveSizes = this._generateResponsiveSizes(currentWidth, currentHeight, aspectRatio, naturalWidth, naturalHeight);
    
    // Add all responsive sizes
    responsiveSizes.forEach(size => {
      requiredSizes.push(size);
    });
    
    // Remove duplicates and sort by width
    const uniqueSizes = this._removeDuplicateSizes(requiredSizes);
    const sortedSizes = uniqueSizes.sort((a, b) => a.w - b.w);
    
    // Apply MAX_VARIATIONS limit to the final result with smart selection
    if (sortedSizes.length > this.CONFIG.MAX_VARIATIONS) {
      optmlLogger.info(`Limiting srcset variations from ${sortedSizes.length} to ${this.CONFIG.MAX_VARIATIONS}`);
      return this._selectBestVariations(sortedSizes, this.CONFIG.MAX_VARIATIONS);
    }
    
    return sortedSizes;
  },

  /**
   * Parse existing srcset attribute to understand what sizes are already available
   * @private
   * @param {string|null} srcset - Existing srcset attribute value
   * @returns {Array} Array of existing size descriptors
   */
  _parseExistingSrcset: function(srcset) {
    if (!srcset) return [];
    
    const existingSizes = [];
    const srcsetEntries = srcset.split(',').map(entry => entry.trim());
    
    srcsetEntries.forEach(entry => {
      const parts = entry.split(/\s+/);
      if (parts.length >= 2) {
        const descriptor = parts[parts.length - 1];
        
        // Parse width descriptor (e.g., "800w")
        if (descriptor.endsWith('w')) {
          const width = parseInt(descriptor.slice(0, -1), 10);
          if (!isNaN(width)) {
            existingSizes.push({
              w: width,
              descriptor: descriptor,
              url: parts.slice(0, -1).join(' ')
            });
          }
        }
      }
    });
    
    return existingSizes;
  },

  /**
   * Generate responsive sizes based on real-world viewport usage patterns
   * @private
   * @param {number} currentWidth - Current displayed width
   * @param {number} currentHeight - Current displayed height
   * @param {number} aspectRatio - Image aspect ratio
   * @param {number} naturalWidth - Natural image width
   * @param {number} naturalHeight - Natural image height
   * @returns {Array} Array of responsive size objects
   */
  _generateResponsiveSizes: function(currentWidth, currentHeight, aspectRatio, naturalWidth, naturalHeight) {
    const sizes = [];
    
    // Generate comprehensive responsive sizes with better coverage
    // Strategy: Create a dense grid of sizes for better responsive coverage
    
    // Define size ranges for different device categories
    const sizeRanges = [
      // Mobile range: 200w - 500w (step 50w) - dense coverage for mobile
      { min: 200, max: 500, step: 50, dpr: [1], category: 'mobile' },
      
      // Tablet range: 500w - 800w (step 100w) - good tablet coverage
      { min: 500, max: 800, step: 100, dpr: [1], category: 'tablet' },
      
      // Desktop range: 800w - 1200w (step 200w) - desktop coverage
      { min: 800, max: 1200, step: 200, dpr: [1], category: 'desktop' },
      
      // High-res range: 1200w - 1600w (step 200w) with selective 2x - practical high-res
      { min: 1200, max: 1600, step: 200, dpr: [1, 2], category: 'high-res' }
    ];
    
    // Generate sizes for each range
    sizeRanges.forEach(range => {
      for (let width = range.min; width <= range.max; width += range.step) {
        range.dpr.forEach(dprValue => {
          const targetWidth = Math.round(width * dprValue);
          const targetHeight = Math.round(targetWidth / aspectRatio);
          
          // Only include if within reasonable bounds
          if (this._isValidSize(targetWidth, targetHeight, naturalWidth, naturalHeight) &&
              targetWidth >= this.CONFIG.MIN_SIZE) {
            
            // Determine breakpoint based on width
            let breakpoint = null;
            if (targetWidth <= 400) breakpoint = 320;
            else if (targetWidth <= 600) breakpoint = 768;
            else if (targetWidth <= 900) breakpoint = 1024;
            else if (targetWidth <= 1200) breakpoint = 1440;
            else breakpoint = 1920;
            
            sizes.push({
              w: targetWidth,
              h: targetHeight,
              dpr: dprValue,
              breakpoint: breakpoint,
              descriptor: `${targetWidth}w`,
              source: 'responsive',
              category: range.category,
              label: `${range.category}-${targetWidth}w${dprValue > 1 ? `-${dprValue}x` : ''}`
            });
          }
        });
      }
    });
    
    // Add current size if not already covered
    const currentExists = sizes.some(size => 
      Math.abs(size.w - currentWidth) <= this.CONFIG.SIZE_TOLERANCE
    );
    
    if (!currentExists && this._isValidSize(currentWidth, currentHeight, naturalWidth, naturalHeight)) {
      sizes.push({
        w: currentWidth,
        h: currentHeight,
        dpr: 1,
        breakpoint: window.innerWidth,
        descriptor: `${currentWidth}w`,
        source: 'current',
        category: 'current',
        label: 'current-size'
      });
    }
    
    return sizes;
  },

  /**
   * Check if a size is valid for srcset generation
   * @private
   * @param {number} width - Target width
   * @param {number} height - Target height
   * @param {number} naturalWidth - Natural image width
   * @param {number} naturalHeight - Natural image height
   * @returns {boolean} True if size is valid
   */
  _isValidSize: function(width, height, naturalWidth, naturalHeight) {
    return width >= this.CONFIG.MIN_SIZE && 
           height >= this.CONFIG.MIN_SIZE && 
           width <= naturalWidth && 
           height <= naturalHeight;
  },

  /**
   * Find missing sizes by comparing required vs existing
   * @private
   * @param {Array} requiredSizes - Required size variations
   * @param {Array} existingSizes - Existing size variations
   * @returns {Array} Array of missing size variations
   */
  _findMissingSizes: function(requiredSizes, existingSizes) {
    const existingWidths = new Set(existingSizes.map(size => size.w));
    const tolerance = this.CONFIG.SIZE_TOLERANCE;
    
    return requiredSizes.filter(required => {
      // Check if we already have a size within tolerance
      const hasCloseMatch = Array.from(existingWidths).some(existingWidth => 
        Math.abs(existingWidth - required.w) <= tolerance
      );
      
      return !hasCloseMatch;
    });
  },

  /**
   * Select the best variations when limiting the number of srcset sizes
   * Prioritizes 1x variants and ensures good coverage across the size range
   * @private
   * @param {Array} sortedSizes - Array of size objects sorted by width
   * @param {number} maxVariations - Maximum number of variations to select
   * @returns {Array} Array of selected size variations
   */
  _selectBestVariations: function(sortedSizes, maxVariations) {
    if (sortedSizes.length <= maxVariations) {
      return sortedSizes;
    }
    
    // Separate 1x and 2x variants
    const dpr1Sizes = sortedSizes.filter(size => size.dpr === 1);
    const dpr2Sizes = sortedSizes.filter(size => size.dpr === 2);
    
    const selected = [];
    
    // Strategy: Prioritize 1x variants, then add strategic 2x variants
    const target1xCount = Math.min(dpr1Sizes.length, Math.ceil(maxVariations * 0.75)); // 75% should be 1x
    const target2xCount = maxVariations - target1xCount;
    
    // Select 1x variants with even distribution
    if (dpr1Sizes.length > 0) {
      if (dpr1Sizes.length <= target1xCount) {
        // Include all 1x variants
        selected.push(...dpr1Sizes);
      } else {
        // Distribute 1x variants evenly
        const step = (dpr1Sizes.length - 1) / (target1xCount - 1);
        for (let i = 0; i < target1xCount; i++) {
          const index = Math.round(i * step);
          selected.push(dpr1Sizes[index]);
        }
      }
    }
    
    // Select 2x variants strategically (prefer middle sizes for 2x)
    if (target2xCount > 0 && dpr2Sizes.length > 0) {
      if (dpr2Sizes.length <= target2xCount) {
        // Include all 2x variants
        selected.push(...dpr2Sizes);
      } else {
        // Select middle-range 2x variants (most useful for retina displays)
        const sortedDpr2 = dpr2Sizes.sort((a, b) => a.w - b.w);
        const middleStart = Math.floor(sortedDpr2.length / 4);
        const middleEnd = Math.ceil(sortedDpr2.length * 3 / 4);
        const middleRange = sortedDpr2.slice(middleStart, middleEnd);
        
        const step = Math.max(1, Math.floor(middleRange.length / target2xCount));
        for (let i = 0; i < target2xCount && i * step < middleRange.length; i++) {
          selected.push(middleRange[i * step]);
        }
      }
    }
    
    // Remove duplicates and sort by width
    const uniqueSelected = selected.filter((size, index, arr) => 
      arr.findIndex(s => s.w === size.w) === index
    );
    
    return uniqueSelected.sort((a, b) => a.w - b.w);
  },

  /**
   * Remove duplicate sizes from the required sizes array
   * @private
   * @param {Array} sizes - Array of size objects
   * @returns {Array} Array with duplicates removed
   */
  _removeDuplicateSizes: function(sizes) {
    const seen = new Set();
    return sizes.filter(size => {
      const key = `${size.w}x${size.h}`;
      if (seen.has(key)) {
        return false;
      }
      seen.add(key);
      return true;
    });
  },

  /**
   * Get a summary of srcset analysis for all images
   * @returns {Object} Summary statistics
   */
  getSrcsetAnalysisSummary: function() {
    const allImages = document.querySelectorAll('img[data-opt-id]');
    
    // Count images based on new logic
    let includedImages = 0;
    let skippedImages = 0;
    let lazyLoadedImages = 0;
    let nonLazyloadImages = 0;
    
    allImages.forEach(img => {
      const hasOptSrc = img.hasAttribute('data-opt-src');
      const hasLazyLoaded = img.hasAttribute('data-opt-lazy-loaded');
      const shouldInclude = !hasOptSrc || (hasOptSrc && hasLazyLoaded);
      
      if (shouldInclude) {
        includedImages++;
        if (!hasOptSrc) {
          nonLazyloadImages++;
        } else if (hasLazyLoaded) {
          lazyLoadedImages++;
        }
      } else {
        skippedImages++;
      }
    });
    
    return {
      totalOptimoleImages: allImages.length,
      includedImages: includedImages,
      skippedImages: skippedImages,
      nonLazyloadImages: nonLazyloadImages,
      lazyLoadedImages: lazyLoadedImages,
      currentDeviceType: optmlDevice.getDeviceType(),
      viewportSize: {
        w: window.innerWidth,
        h: window.innerHeight
      }
    };
  }
};
