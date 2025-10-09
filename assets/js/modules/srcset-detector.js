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
   * @param {number} config.minSize - Minimum image size to consider (default: 200)
   * @param {number} config.maxVariations - Maximum srcset variations per image (default: 8)
   * @param {number} config.sizeTolerance - Tolerance for existing sizes (default: 50)
   */
  configure: function(config) {
    if (config.minSize) this.CONFIG.MIN_SIZE = config.minSize;
    if (config.maxVariations) this.CONFIG.MAX_VARIATIONS = config.maxVariations;
    if (config.sizeTolerance) this.CONFIG.SIZE_TOLERANCE = config.sizeTolerance;
    
    optmlLogger.info('Srcset detector configured:', this.CONFIG);
  },

  /**
   * Wait for images to load and get their natural dimensions
   * @param {NodeList} images - Collection of image elements
   * @returns {Promise} Promise that resolves when all images are loaded
   */
  _waitForImagesToLoad: function(images) {
    const imagePromises = Array.from(images).map(img => {
      return new Promise((resolve) => {
        // If image is already loaded
        if (img.complete && img.naturalWidth > 0) {
          resolve(img);
          return;
        }
        
        // Wait for image to load
        const onLoad = () => {
          img.removeEventListener('load', onLoad);
          img.removeEventListener('error', onError);
          resolve(img);
        };
        
        const onError = () => {
          img.removeEventListener('load', onLoad);
          img.removeEventListener('error', onError);
          optmlLogger.warn('Image failed to load:', img.src);
          resolve(img); // Still resolve to continue processing
        };
        
        img.addEventListener('load', onLoad);
        img.addEventListener('error', onError);
        
        // Timeout after 5 seconds
        setTimeout(() => {
          img.removeEventListener('load', onLoad);
          img.removeEventListener('error', onError);
          optmlLogger.warn('Image load timeout:', img.src);
          resolve(img);
        }, 5000);
      });
    });
    
    return Promise.all(imagePromises);
  },

  /**
   * Detect all Optimole images that are NOT using lazyload (no data-opt-src)
   * and calculate missing srcset variations
   * @returns {Promise<Object>} Promise that resolves to object with srcset data and crop status
   */
  detectMissingSrcsets: async function() {
    const missingSrcsetData = {};
    const cropStatusData = {};
    
    // Find all Optimole images
    const optimoleImages = document.querySelectorAll('img[data-opt-id]');
    
    if (optimoleImages.length === 0) {
      optmlLogger.info('No Optimole images found for srcset analysis');
      return { srcset: missingSrcsetData, crop: cropStatusData };
    }
    
    optmlLogger.info(`Found ${optimoleImages.length} Optimole images, waiting for them to load...`);
    
    // Wait for all images to load to get accurate natural dimensions
    const loadedImages = await this._waitForImagesToLoad(optimoleImages);
    
    optmlLogger.info(`Loaded ${loadedImages.length} images, analyzing srcset requirements...`);
    
    loadedImages.forEach(img => {
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
          const analysisResult = this._analyzeSrcsetRequirements(img, imageId);
          
          if (analysisResult && analysisResult.srcset && analysisResult.srcset.length > 0) {
            missingSrcsetData[imageId] = analysisResult.srcset;
            cropStatusData[imageId] = analysisResult.requiresCropping;
          }
        }
      } catch (error) {
        optmlLogger.error('Error analyzing image for srcset:', img, error);
      }
    });
    
    optmlLogger.info('Images requiring srcset variations:', Object.keys(missingSrcsetData).length);
    optmlLogger.info('Images with crop status:', Object.keys(cropStatusData).length);
    return { srcset: missingSrcsetData, crop: cropStatusData };
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
    
    // Calculate aspect ratios
    const naturalAspectRatio = naturalWidth / naturalHeight;
    const currentAspectRatio = currentWidth / currentHeight;
    
    // Determine if image requires cropping based on object-fit and aspect ratio difference
    const aspectRatioDifference = Math.abs(naturalAspectRatio - currentAspectRatio);
    const requiresCropping = this._requiresCropping(img, aspectRatioDifference, naturalAspectRatio, currentAspectRatio);
    
    // Get current device type
    const currentDeviceType = optmlDevice.getDeviceType();
    
    // Calculate required sizes for different breakpoints
    // Use current aspect ratio for srcset generation to match the rendered dimensions
    const aspectRatioForSizing = requiresCropping ? currentAspectRatio : naturalAspectRatio;
    const requiredSizes = this._calculateRequiredSizes(
      currentWidth, 
      currentHeight, 
      aspectRatioForSizing,
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
      requiredSizes: requiredSizes,
      naturalAspectRatio: Math.round(naturalAspectRatio * 1000) / 1000,
      currentAspectRatio: Math.round(currentAspectRatio * 1000) / 1000,
      aspectRatioDifference: Math.round(aspectRatioDifference * 1000) / 1000,
      requiresCropping: requiresCropping,
      aspectRatioForSizing: Math.round(aspectRatioForSizing * 1000) / 1000,
      deviceType: currentDeviceType,
      missingSizes: missingSizes,
      existingSrcset: existingSrcset || null
    });

    // Additional debug logging for aspect ratio analysis
    optmlLogger.info(`[Optimole Debug] Image ${imageId} aspect ratio analysis:`, {
      natural: `${naturalWidth}x${naturalHeight} (${Math.round(naturalAspectRatio * 1000) / 1000}:1)`,
      current: `${currentWidth}x${currentHeight} (${Math.round(currentAspectRatio * 1000) / 1000}:1)`,
      difference: Math.round(aspectRatioDifference * 1000) / 1000,
      requiresCropping: requiresCropping,
      aspectRatioForSizing: Math.round(aspectRatioForSizing * 1000) / 1000,
      reason: requiresCropping ? 'Aspect ratio significantly different' : 'Aspect ratios match within tolerance'
    });
    
    // Return both srcset data and crop status separately
    return {
      srcset: missingSizes.map(size => ({
        w: size.w,
        h: size.h,
        d: size.dpr,        // dpr -> d
        s: size.descriptor, // descriptor -> s (srcset)
        b: size.breakpoint  // breakpoint -> b
      })),
      requiresCropping: requiresCropping
    };
  },

  /**
   * Determine if an image requires cropping based on object-fit and aspect ratio differences
   * Requires BOTH object-fit: cover AND significant aspect ratio difference to return true
   * @private
   * @param {HTMLImageElement} img - Image element to analyze
   * @param {number} aspectRatioDifference - Absolute difference between natural and current aspect ratios
   * @param {number} naturalAspectRatio - Natural image aspect ratio
   * @param {number} currentAspectRatio - Current displayed aspect ratio
   * @returns {boolean} True if the image requires cropping (both conditions must be true)
   */
  _requiresCropping: function(img, aspectRatioDifference, naturalAspectRatio, currentAspectRatio) {
    // First check if the image has object-fit: cover
    let hasObjectFitCover = false;
    try {
      const computedStyle = window.getComputedStyle(img);
      hasObjectFitCover = computedStyle.objectFit === 'cover';
      if (hasObjectFitCover) {
        optmlLogger.info(`Image has object-fit: cover`);
      }
    } catch (error) {
      optmlLogger.warn('Could not get computed style for object-fit check:', error);
    }
    
    // Define thresholds for determining when cropping is needed
    const ASPECT_RATIO_TOLERANCE = 0.05; // 5% tolerance for minor differences
    const SIGNIFICANT_DIFFERENCE_THRESHOLD = 0.15; // 15% for significant differences
    
    // If the difference is very small, no cropping needed
    if (aspectRatioDifference <= ASPECT_RATIO_TOLERANCE) {
      return false;
    }
    
    // Check aspect ratio conditions
    let aspectRatioRequiresCropping = false;
    
    // If the difference is significant, definitely needs cropping
    if (aspectRatioDifference >= SIGNIFICANT_DIFFERENCE_THRESHOLD) {
      aspectRatioRequiresCropping = true;
    } else {
      // For moderate differences, check if the current aspect ratio is significantly different
      // from the natural one (indicating intentional resizing that would require cropping)
      const ratioChange = Math.abs(currentAspectRatio - naturalAspectRatio) / naturalAspectRatio;
      
      // If the current aspect ratio is more than 10% different from natural, likely needs cropping
      aspectRatioRequiresCropping = ratioChange > 0.1;
    }
    
    // Both object-fit: cover AND aspect ratio difference must be true to require cropping
    const requiresCropping = hasObjectFitCover && aspectRatioRequiresCropping;
    
    if (requiresCropping) {
      optmlLogger.info(`Image requires cropping: object-fit cover=${hasObjectFitCover}, aspect ratio=${aspectRatioRequiresCropping}`);
    }
    
    return requiresCropping;
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
   * Generate responsive sizes based on the current image ratio
   * Device-aware approach: generates sizes for different viewports while maintaining the current ratio
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
    
    // Calculate the current ratio: what percentage of viewport width does this image occupy?
    const viewportWidth = window.innerWidth;
    const currentRatio = Math.min(currentWidth / viewportWidth, 1.0);
    
    optmlLogger.info(`Generating srcset for current ratio: ${Math.round(currentRatio * 100)}% of viewport`);
    
    // Use configured device breakpoints
    const breakpoints = Object.entries(this.DEVICE_BREAKPOINTS).map(([key, viewport]) => ({
      viewport,
      label: key.toLowerCase().replace('_', '-'),
      dpr: this.DPR_MULTIPLIERS
    }));
    
    // Generate sizes for each viewport using the current ratio
    breakpoints.forEach(bp => {
      const baseWidth = Math.round(bp.viewport * currentRatio);
      
      // Skip if the calculated width is too small or too large
      if (baseWidth < this.CONFIG.MIN_SIZE || baseWidth > naturalWidth * 1.2) {
        return;
      }
      
      // Generate for each DPR
      bp.dpr.forEach(dprValue => {
        const targetWidth = Math.round(baseWidth * dprValue);
        const targetHeight = Math.round(targetWidth / aspectRatio);
        
        // Don't generate 1x DPR variations larger than current size
        // But allow 2x DPR variations for retina displays
        if (dprValue === 1 && targetWidth > currentWidth) {
          return;
        }
        
        // Only include if within reasonable bounds
        if (this._isValidSize(targetWidth, targetHeight, naturalWidth, naturalHeight) &&
            targetWidth >= this.CONFIG.MIN_SIZE) {
          
          sizes.push({
            w: targetWidth,
            h: targetHeight,
            dpr: dprValue,
            breakpoint: bp.viewport,
            descriptor: `${targetWidth}w`,
            source: 'responsive',
            category: `${bp.label}-${Math.round(currentRatio * 100)}`,
            label: `${bp.label}-${targetWidth}w${dprValue > 1 ? `-${dprValue}x` : ''}`
          });
        }
      });
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
