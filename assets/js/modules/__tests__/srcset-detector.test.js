/**
 * Tests for Optimole Srcset Detection Module
 */

import { optmlSrcsetDetector } from '../srcset-detector.js';
import { optmlLogger } from '../logger.js';
import { optmlDevice } from '../device.js';

// Mock dependencies
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
  }
}));

jest.mock('../device.js', () => ({
  optmlDevice: {
    getDeviceType: jest.fn(),
    DEVICE_TYPES: {
      MOBILE: 1,
      DESKTOP: 2
    }
  }
}));

describe('optmlSrcsetDetector', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';
    optmlDevice.getDeviceType.mockReturnValue(2); // Default to desktop
  });

  describe('DEVICE_BREAKPOINTS constants', () => {
    test('should have all required breakpoints', () => {
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.MOBILE_SMALL).toBe(320);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.MOBILE_MEDIUM).toBe(375);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.MOBILE_LARGE).toBe(425);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.TABLET_SMALL).toBe(768);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.TABLET_LARGE).toBe(1024);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.DESKTOP_SMALL).toBe(1200);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.DESKTOP_MEDIUM).toBe(1440);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.DESKTOP_LARGE).toBe(1920);
      expect(optmlSrcsetDetector.DEVICE_BREAKPOINTS.DESKTOP_XL).toBe(2560);
    });
  });

  describe('DPR_MULTIPLIERS constant', () => {
    test('should have 1x and 2x multipliers', () => {
      expect(optmlSrcsetDetector.DPR_MULTIPLIERS).toEqual([1, 2]);
    });
  });

  describe('CONFIG', () => {
    test('should have default configuration values', () => {
      expect(optmlSrcsetDetector.CONFIG.MIN_SIZE).toBe(200);
      expect(optmlSrcsetDetector.CONFIG.MAX_VARIATIONS).toBe(8);
      expect(optmlSrcsetDetector.CONFIG.SIZE_TOLERANCE).toBe(50);
    });
  });

  describe('configure', () => {
    test('should update configuration values', () => {
      optmlSrcsetDetector.configure({
        minSize: 300,
        maxVariations: 10,
        sizeTolerance: 75
      });

      expect(optmlSrcsetDetector.CONFIG.MIN_SIZE).toBe(300);
      expect(optmlSrcsetDetector.CONFIG.MAX_VARIATIONS).toBe(10);
      expect(optmlSrcsetDetector.CONFIG.SIZE_TOLERANCE).toBe(75);

      // Reset to defaults
      optmlSrcsetDetector.configure({
        minSize: 200,
        maxVariations: 8,
        sizeTolerance: 50
      });
    });

    test('should only update provided values', () => {
      const originalMinSize = optmlSrcsetDetector.CONFIG.MIN_SIZE;
      
      optmlSrcsetDetector.configure({
        maxVariations: 12
      });

      expect(optmlSrcsetDetector.CONFIG.MAX_VARIATIONS).toBe(12);
      expect(optmlSrcsetDetector.CONFIG.MIN_SIZE).toBe(originalMinSize);
      
      // Reset
      optmlSrcsetDetector.configure({
        maxVariations: 8
      });
    });
  });

  describe('_waitForImagesToLoad', () => {
    test('should resolve immediately for already loaded images', async () => {
      document.body.innerHTML = '<img data-opt-id="123" />';
      const images = document.querySelectorAll('img');
      
      Object.defineProperty(images[0], 'complete', { value: true });
      Object.defineProperty(images[0], 'naturalWidth', { value: 800 });

      const startTime = Date.now();
      await optmlSrcsetDetector._waitForImagesToLoad(images);
      const endTime = Date.now();

      expect(endTime - startTime).toBeLessThan(100);
    });

    test('should wait for images to load', async () => {
      document.body.innerHTML = '<img data-opt-id="123" />';
      const images = document.querySelectorAll('img');
      
      Object.defineProperty(images[0], 'complete', { value: false });
      
      // Simulate image load after delay
      setTimeout(() => {
        images[0].dispatchEvent(new Event('load'));
      }, 50);

      await optmlSrcsetDetector._waitForImagesToLoad(images);
      expect(true).toBe(true); // If we get here, promise resolved
    });

    test('should handle image load errors', async () => {
      document.body.innerHTML = '<img data-opt-id="123" />';
      const images = document.querySelectorAll('img');
      
      Object.defineProperty(images[0], 'complete', { value: false });
      
      // Simulate image error
      setTimeout(() => {
        images[0].dispatchEvent(new Event('error'));
      }, 50);

      await optmlSrcsetDetector._waitForImagesToLoad(images);
      expect(optmlLogger.warn).toHaveBeenCalled();
    });
  });

  describe('_parseExistingSrcset', () => {
    test('should return empty array for null srcset', () => {
      const result = optmlSrcsetDetector._parseExistingSrcset(null);
      expect(result).toEqual([]);
    });

    test('should parse single srcset entry', () => {
      const srcset = 'image-800w.jpg 800w';
      const result = optmlSrcsetDetector._parseExistingSrcset(srcset);

      expect(result).toHaveLength(1);
      expect(result[0]).toEqual({
        w: 800,
        descriptor: '800w',
        url: 'image-800w.jpg'
      });
    });

    test('should parse multiple srcset entries', () => {
      const srcset = 'image-400w.jpg 400w, image-800w.jpg 800w, image-1200w.jpg 1200w';
      const result = optmlSrcsetDetector._parseExistingSrcset(srcset);

      expect(result).toHaveLength(3);
      expect(result[0].w).toBe(400);
      expect(result[1].w).toBe(800);
      expect(result[2].w).toBe(1200);
    });

    test('should handle srcset with URLs containing spaces', () => {
      const srcset = 'https://example.com/image.jpg 800w';
      const result = optmlSrcsetDetector._parseExistingSrcset(srcset);

      expect(result).toHaveLength(1);
      expect(result[0]).toEqual({
        w: 800,
        descriptor: '800w',
        url: 'https://example.com/image.jpg'
      });
    });

    test('should skip invalid srcset entries', () => {
      const srcset = 'invalid, image-800w.jpg 800w';
      const result = optmlSrcsetDetector._parseExistingSrcset(srcset);

      expect(result).toHaveLength(1);
      expect(result[0].w).toBe(800);
    });
  });

  describe('_isValidSize', () => {
    test('should return true for valid size within natural dimensions', () => {
      const result = optmlSrcsetDetector._isValidSize(400, 300, 800, 600);
      expect(result).toBe(true);
    });

    test('should return false if width below minimum', () => {
      const result = optmlSrcsetDetector._isValidSize(100, 75, 800, 600);
      expect(result).toBe(false);
    });

    test('should return false if height below minimum', () => {
      const result = optmlSrcsetDetector._isValidSize(300, 100, 800, 600);
      expect(result).toBe(false);
    });

    test('should return false if width exceeds natural width', () => {
      const result = optmlSrcsetDetector._isValidSize(1000, 750, 800, 600);
      expect(result).toBe(false);
    });

    test('should return false if height exceeds natural height', () => {
      const result = optmlSrcsetDetector._isValidSize(400, 700, 800, 600);
      expect(result).toBe(false);
    });

    test('should return true at boundaries', () => {
      // Ensure MIN_SIZE is at default
      optmlSrcsetDetector.configure({ minSize: 200 });
      
      expect(optmlSrcsetDetector._isValidSize(200, 200, 800, 600)).toBe(true);
      expect(optmlSrcsetDetector._isValidSize(800, 600, 800, 600)).toBe(true);
    });
  });

  describe('_findMissingSizes', () => {
    test('should return all required sizes when no existing sizes', () => {
      const requiredSizes = [
        { w: 400, h: 300, dpr: 1, descriptor: '400w' },
        { w: 800, h: 600, dpr: 1, descriptor: '800w' }
      ];
      const existingSizes = [];

      const result = optmlSrcsetDetector._findMissingSizes(requiredSizes, existingSizes);
      expect(result).toHaveLength(2);
    });

    test('should filter out existing sizes', () => {
      const requiredSizes = [
        { w: 400, h: 300, dpr: 1, descriptor: '400w' },
        { w: 800, h: 600, dpr: 1, descriptor: '800w' }
      ];
      const existingSizes = [
        { w: 400, descriptor: '400w' }
      ];

      const result = optmlSrcsetDetector._findMissingSizes(requiredSizes, existingSizes);
      expect(result).toHaveLength(1);
      expect(result[0].w).toBe(800);
    });

    test('should use tolerance when matching sizes', () => {
      const requiredSizes = [
        { w: 400, h: 300, dpr: 1, descriptor: '400w' }
      ];
      const existingSizes = [
        { w: 425, descriptor: '425w' } // Within 50px tolerance
      ];

      const result = optmlSrcsetDetector._findMissingSizes(requiredSizes, existingSizes);
      expect(result).toHaveLength(0); // Should be filtered out due to tolerance
    });

    test('should not filter sizes outside tolerance', () => {
      const requiredSizes = [
        { w: 400, h: 300, dpr: 1, descriptor: '400w' }
      ];
      const existingSizes = [
        { w: 500, descriptor: '500w' } // Outside 50px tolerance
      ];

      const result = optmlSrcsetDetector._findMissingSizes(requiredSizes, existingSizes);
      expect(result).toHaveLength(1);
      expect(result[0].w).toBe(400);
    });
  });

  describe('_removeDuplicateSizes', () => {
    test('should remove duplicate sizes', () => {
      const sizes = [
        { w: 400, h: 300 },
        { w: 400, h: 300 },
        { w: 800, h: 600 }
      ];

      const result = optmlSrcsetDetector._removeDuplicateSizes(sizes);
      expect(result).toHaveLength(2);
    });

    test('should preserve unique sizes', () => {
      const sizes = [
        { w: 400, h: 300 },
        { w: 800, h: 600 },
        { w: 1200, h: 900 }
      ];

      const result = optmlSrcsetDetector._removeDuplicateSizes(sizes);
      expect(result).toHaveLength(3);
    });

    test('should handle empty array', () => {
      const result = optmlSrcsetDetector._removeDuplicateSizes([]);
      expect(result).toEqual([]);
    });
  });

  describe('_selectBestVariations', () => {
    test('should return all sizes if below limit', () => {
      const sizes = [
        { w: 400, h: 300, dpr: 1 },
        { w: 800, h: 600, dpr: 1 }
      ];

      const result = optmlSrcsetDetector._selectBestVariations(sizes, 5);
      expect(result).toHaveLength(2);
    });

    test('should limit to maxVariations', () => {
      const sizes = Array.from({ length: 20 }, (_, i) => ({
        w: (i + 1) * 100,
        h: (i + 1) * 75,
        dpr: 1
      }));

      const result = optmlSrcsetDetector._selectBestVariations(sizes, 8);
      // The function may return fewer than maxVariations if there aren't enough valid sizes
      expect(result.length).toBeLessThanOrEqual(8);
      expect(result.length).toBeGreaterThan(0);
    });

    test('should prioritize 1x DPR variants', () => {
      const sizes = [
        { w: 400, h: 300, dpr: 1 },
        { w: 800, h: 600, dpr: 2 },
        { w: 800, h: 600, dpr: 1 },
        { w: 1200, h: 900, dpr: 2 }
      ];

      const result = optmlSrcsetDetector._selectBestVariations(sizes, 3);
      
      const dpr1Count = result.filter(s => s.dpr === 1).length;
      const dpr2Count = result.filter(s => s.dpr === 2).length;
      
      expect(dpr1Count).toBeGreaterThanOrEqual(dpr2Count);
    });

    test('should return sorted results', () => {
      // Create sizes already sorted for consistent testing
      const sizes = [
        { w: 400, h: 300, dpr: 1 },
        { w: 800, h: 600, dpr: 1 },
        { w: 1200, h: 900, dpr: 1 }
      ];

      const result = optmlSrcsetDetector._selectBestVariations(sizes, 10);
      
      // Verify we got results back
      expect(result.length).toBeGreaterThan(0);
      expect(result.length).toBeLessThanOrEqual(10);
      
      // Verify results are sorted by width
      if (result.length > 1) {
        for (let i = 1; i < result.length; i++) {
          expect(result[i].w).toBeGreaterThanOrEqual(result[i - 1].w);
        }
      }
    });
  });

  describe('_requiresCropping', () => {
    test('should return false when aspect ratios match within tolerance', () => {
      const img = document.createElement('img');
      document.body.appendChild(img);

      // Mock getComputedStyle
      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'contain'
      });

      const result = optmlSrcsetDetector._requiresCropping(img, 0.02, 1.5, 1.48);
      expect(result).toBe(false);
    });

    test('should return true when object-fit is cover and aspect ratio differs significantly', () => {
      const img = document.createElement('img');
      document.body.appendChild(img);

      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'cover'
      });

      const result = optmlSrcsetDetector._requiresCropping(img, 0.2, 1.5, 1.3);
      expect(result).toBe(true);
    });

    test('should return false when object-fit is not cover even with different aspect ratios', () => {
      const img = document.createElement('img');
      document.body.appendChild(img);

      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'contain'
      });

      const result = optmlSrcsetDetector._requiresCropping(img, 0.2, 1.5, 1.3);
      expect(result).toBe(false);
    });

    test('should return false when aspect ratio difference is very small', () => {
      const img = document.createElement('img');
      document.body.appendChild(img);

      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'cover'
      });

      const result = optmlSrcsetDetector._requiresCropping(img, 0.03, 1.5, 1.47);
      expect(result).toBe(false);
    });

    test('should handle errors gracefully', () => {
      const img = document.createElement('img');
      
      window.getComputedStyle = jest.fn().mockImplementation(() => {
        throw new Error('Style error');
      });

      const result = optmlSrcsetDetector._requiresCropping(img, 0.2, 1.5, 1.3);
      expect(optmlLogger.warn).toHaveBeenCalled();
      expect(result).toBe(false);
    });
  });

  describe('detectMissingSrcsets', () => {
    test('should return empty data when no Optimole images found', async () => {
      document.body.innerHTML = '<img />';

      const result = await optmlSrcsetDetector.detectMissingSrcsets();

      expect(result.srcset).toEqual({});
      expect(result.crop).toEqual({});
      expect(optmlLogger.info).toHaveBeenCalledWith('No Optimole images found for srcset analysis');
    });

    test('should analyze images without data-opt-src', async () => {
      document.body.innerHTML = '<img data-opt-id="123" />';
      const img = document.querySelector('img');
      
      Object.defineProperty(img, 'complete', { value: true });
      Object.defineProperty(img, 'naturalWidth', { value: 1600 });
      Object.defineProperty(img, 'naturalHeight', { value: 1200 });
      Object.defineProperty(img, 'offsetWidth', { value: 800 });
      Object.defineProperty(img, 'offsetHeight', { value: 600 });

      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'contain'
      });

      const result = await optmlSrcsetDetector.detectMissingSrcsets();

      expect(result.srcset).toBeDefined();
      expect(result.crop).toBeDefined();
    });

    test('should analyze images with data-opt-lazy-loaded', async () => {
      document.body.innerHTML = `
        <img data-opt-id="456" 
             data-opt-src="lazy.jpg" 
             data-opt-lazy-loaded="true" />
      `;
      const img = document.querySelector('img');
      
      Object.defineProperty(img, 'complete', { value: true });
      Object.defineProperty(img, 'naturalWidth', { value: 1600 });
      Object.defineProperty(img, 'naturalHeight', { value: 1200 });
      Object.defineProperty(img, 'offsetWidth', { value: 800 });
      Object.defineProperty(img, 'offsetHeight', { value: 600 });

      window.getComputedStyle = jest.fn().mockReturnValue({
        objectFit: 'contain'
      });

      const result = await optmlSrcsetDetector.detectMissingSrcsets();

      expect(result.srcset).toBeDefined();
    });

    test('should skip images with only data-opt-src (pending lazyload)', async () => {
      document.body.innerHTML = '<img data-opt-id="789" data-opt-src="pending.jpg" />';
      const img = document.querySelector('img');
      
      // Set up the image as complete but not lazy-loaded yet
      Object.defineProperty(img, 'complete', { value: true, writable: false });
      Object.defineProperty(img, 'naturalWidth', { value: 0, writable: false });
      Object.defineProperty(img, 'naturalHeight', { value: 0, writable: false });

      const result = await optmlSrcsetDetector.detectMissingSrcsets();

      expect(result.srcset[789]).toBeUndefined();
    }, 10000);
  });

  describe('getSrcsetAnalysisSummary', () => {
    test('should return summary statistics', () => {
      document.body.innerHTML = `
        <img data-opt-id="1" />
        <img data-opt-id="2" data-opt-src="lazy.jpg" />
        <img data-opt-id="3" data-opt-src="lazy2.jpg" data-opt-lazy-loaded="true" />
      `;

      Object.defineProperty(window, 'innerWidth', { value: 1024, writable: true, configurable: true });
      Object.defineProperty(window, 'innerHeight', { value: 768, writable: true, configurable: true });

      const summary = optmlSrcsetDetector.getSrcsetAnalysisSummary();

      expect(summary.totalOptimoleImages).toBe(3);
      expect(summary.includedImages).toBeGreaterThan(0);
      expect(summary.viewportSize).toEqual({ w: 1024, h: 768 });
      expect(summary.currentDeviceType).toBeDefined();
    });

    test('should categorize images correctly', () => {
      document.body.innerHTML = `
        <img data-opt-id="1" />
        <img data-opt-id="2" data-opt-src="lazy.jpg" data-opt-lazy-loaded="true" />
        <img data-opt-id="3" data-opt-src="pending.jpg" />
      `;

      const summary = optmlSrcsetDetector.getSrcsetAnalysisSummary();

      expect(summary.nonLazyloadImages).toBe(1);
      expect(summary.lazyLoadedImages).toBe(1);
      expect(summary.skippedImages).toBe(1);
    });
  });
});

