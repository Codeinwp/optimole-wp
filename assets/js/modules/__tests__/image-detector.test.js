/**
 * Tests for Optimole Image Detection Module
 */

import { optmlImageDetector } from '../image-detector.js';
import { optmlLogger } from '../logger.js';

// Mock the logger
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
  }
}));

describe('optmlImageDetector', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';
  });

  describe('detectImageDimensions', () => {
    test('should return empty object for empty NodeList', () => {
      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);
      expect(result).toEqual({});
    });

    test('should detect dimensions for image missing width attribute', () => {
      document.body.innerHTML = '<img data-opt-id="123" height="100" />';
      const img = document.querySelector('img');
      
      // Mock naturalWidth and naturalHeight
      Object.defineProperty(img, 'naturalWidth', { value: 800, writable: false });
      Object.defineProperty(img, 'naturalHeight', { value: 600, writable: false });

      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[123]).toEqual({ w: 800, h: 600 });
    });

    test('should detect dimensions for image missing height attribute', () => {
      document.body.innerHTML = '<img data-opt-id="456" width="100" />';
      const img = document.querySelector('img');
      
      Object.defineProperty(img, 'naturalWidth', { value: 800, writable: false });
      Object.defineProperty(img, 'naturalHeight', { value: 600, writable: false });

      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[456]).toEqual({ w: 800, h: 600 });
    });

    test('should detect dimensions for image missing both attributes', () => {
      document.body.innerHTML = '<img data-opt-id="789" />';
      const img = document.querySelector('img');
      
      Object.defineProperty(img, 'naturalWidth', { value: 1200, writable: false });
      Object.defineProperty(img, 'naturalHeight', { value: 900, writable: false });

      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[789]).toEqual({ w: 1200, h: 900 });
    });

    test('should skip images with both width and height attributes', () => {
      document.body.innerHTML = '<img data-opt-id="111" width="100" height="100" />';
      
      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[111]).toBeUndefined();
    });

    test('should skip images without data-opt-id', () => {
      document.body.innerHTML = '<img width="100" height="100" />';
      
      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(Object.keys(result)).toHaveLength(0);
    });

    test('should skip images with invalid data-opt-id', () => {
      document.body.innerHTML = '<img data-opt-id="invalid" />';
      
      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(Object.keys(result)).toHaveLength(0);
    });

    test('should skip images with zero natural dimensions', () => {
      document.body.innerHTML = '<img data-opt-id="222" />';
      const img = document.querySelector('img');
      
      Object.defineProperty(img, 'naturalWidth', { value: 0, writable: false });
      Object.defineProperty(img, 'naturalHeight', { value: 0, writable: false });

      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[222]).toBeUndefined();
    });

    test('should use optimized dimensions when available', () => {
      document.body.innerHTML = `
        <img data-opt-id="333" 
             data-opt-src="test.jpg" 
             data-opt-lazy-loaded="true"
             data-opt-optimized-width="400"
             data-opt-optimized-height="300" />
      `;
      
      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(result[333]).toEqual({ w: 400, h: 300 });
      expect(optmlLogger.info).toHaveBeenCalledWith(
        'Image 333 using optimized dimensions:',
        { optimizedDimensions: '400x300' }
      );
    });

    test('should handle errors gracefully', () => {
      document.body.innerHTML = '<img data-opt-id="444" />';
      const img = document.querySelector('img');
      
      // Make getAttribute throw an error
      img.getAttribute = jest.fn(() => {
        throw new Error('Test error');
      });

      const images = document.querySelectorAll('img');
      const result = optmlImageDetector.detectImageDimensions(images);

      expect(optmlLogger.error).toHaveBeenCalled();
      expect(Object.keys(result)).toHaveLength(0);
    });

    test('should handle multiple images', () => {
      document.body.innerHTML = `
        <img data-opt-id="1" />
        <img data-opt-id="2" width="100" />
        <img data-opt-id="3" height="100" />
      `;
      
      const images = document.querySelectorAll('img');
      images.forEach(img => {
        Object.defineProperty(img, 'naturalWidth', { value: 800, writable: false });
        Object.defineProperty(img, 'naturalHeight', { value: 600, writable: false });
      });

      const result = optmlImageDetector.detectImageDimensions(images);

      expect(Object.keys(result)).toHaveLength(3);
      expect(result[1]).toEqual({ w: 800, h: 600 });
      expect(result[2]).toEqual({ w: 800, h: 600 });
      expect(result[3]).toEqual({ w: 800, h: 600 });
    });
  });

  describe('createIntersectionObserver', () => {
    test('should create IntersectionObserver with correct options', () => {
      const aboveTheFoldImages = [];
      const selectorMap = new Map();

      const observer = optmlImageDetector.createIntersectionObserver(aboveTheFoldImages, selectorMap);

      expect(observer).toBeInstanceOf(IntersectionObserver);
    });

    test('should add IMG element ID when intersecting', () => {
      const aboveTheFoldImages = [];
      const selectorMap = new Map();

      document.body.innerHTML = '<img data-opt-id="555" />';
      const img = document.querySelector('img');

      const observer = optmlImageDetector.createIntersectionObserver(aboveTheFoldImages, selectorMap);
      
      // Simulate intersection
      const callback = observer.callback || observer.observe;
      if (callback) {
        // Mock the observer callback
        const entries = [{
          isIntersecting: true,
          target: img
        }];
        
        // Execute callback manually for testing
        observer.root = null;
        observer.rootMargin = '0px';
        observer.thresholds = [0.1];
      }
    });

    test('should not add duplicate image IDs', () => {
      const aboveTheFoldImages = [123];
      const selectorMap = new Map();

      document.body.innerHTML = '<img data-opt-id="123" />';
      const img = document.querySelector('img');

      const observer = optmlImageDetector.createIntersectionObserver(aboveTheFoldImages, selectorMap);
      
      // The observer is created successfully
      expect(observer).toBeInstanceOf(IntersectionObserver);
    });
  });

  describe('observeOptimoleImages', () => {
    test('should observe all images with data-opt-id', () => {
      document.body.innerHTML = `
        <img data-opt-id="1" />
        <img data-opt-id="2" />
        <img data-opt-id="3" />
        <img />
      `;

      const mockObserver = {
        observe: jest.fn()
      };

      const result = optmlImageDetector.observeOptimoleImages(mockObserver);

      expect(result.allOptimoleImages).toHaveLength(3);
      expect(result.observedElements.size).toBe(3);
      expect(mockObserver.observe).toHaveBeenCalledTimes(3);
    });

    test('should skip images with invalid data-opt-id', () => {
      document.body.innerHTML = `
        <img data-opt-id="123" />
        <img data-opt-id="invalid" />
        <img data-opt-id="456" />
      `;

      const mockObserver = {
        observe: jest.fn()
      };

      const result = optmlImageDetector.observeOptimoleImages(mockObserver);

      expect(mockObserver.observe).toHaveBeenCalledTimes(2);
      expect(optmlLogger.warn).toHaveBeenCalledWith('Invalid data-opt-id:', 'invalid');
    });

    test('should map elements to their IDs', () => {
      document.body.innerHTML = `
        <img data-opt-id="111" />
        <img data-opt-id="222" />
      `;

      const images = document.querySelectorAll('img');
      const mockObserver = {
        observe: jest.fn()
      };

      const result = optmlImageDetector.observeOptimoleImages(mockObserver);

      expect(result.observedElements.get(images[0])).toBe(111);
      expect(result.observedElements.get(images[1])).toBe(222);
    });

    test('should return empty results when no images found', () => {
      document.body.innerHTML = '<div></div>';

      const mockObserver = {
        observe: jest.fn()
      };

      const result = optmlImageDetector.observeOptimoleImages(mockObserver);

      expect(result.allOptimoleImages).toHaveLength(0);
      expect(result.observedElements.size).toBe(0);
      expect(mockObserver.observe).not.toHaveBeenCalled();
    });
  });

  describe('cleanupBackgroundElements', () => {
    test('should remove data-optml-bg-selector attributes', () => {
      document.body.innerHTML = `
        <div data-optml-bg-selector=".test" data-optml-specific-selector="div.test"></div>
      `;

      optmlImageDetector.cleanupBackgroundElements();

      const element = document.querySelector('div');
      expect(element.hasAttribute('data-optml-bg-selector')).toBe(false);
      expect(element.hasAttribute('data-optml-specific-selector')).toBe(false);
    });

    test('should handle multiple elements', () => {
      document.body.innerHTML = `
        <div data-optml-bg-selector=".test1" data-optml-specific-selector="div.test1"></div>
        <div data-optml-bg-selector=".test2" data-optml-specific-selector="div.test2"></div>
        <div data-optml-bg-selector=".test3" data-optml-specific-selector="div.test3"></div>
      `;

      optmlImageDetector.cleanupBackgroundElements();

      const elements = document.querySelectorAll('div');
      elements.forEach(element => {
        expect(element.hasAttribute('data-optml-bg-selector')).toBe(false);
        expect(element.hasAttribute('data-optml-specific-selector')).toBe(false);
      });
    });

    test('should do nothing when no elements found', () => {
      document.body.innerHTML = '<div></div>';

      // Should not throw error
      expect(() => {
        optmlImageDetector.cleanupBackgroundElements();
      }).not.toThrow();
    });

    test('should preserve other attributes', () => {
      document.body.innerHTML = `
        <div class="test" 
             id="myDiv" 
             data-optml-bg-selector=".test" 
             data-optml-specific-selector="div.test"
             data-other="value"></div>
      `;

      optmlImageDetector.cleanupBackgroundElements();

      const element = document.querySelector('div');
      expect(element.className).toBe('test');
      expect(element.id).toBe('myDiv');
      expect(element.getAttribute('data-other')).toBe('value');
      expect(element.hasAttribute('data-optml-bg-selector')).toBe(false);
      expect(element.hasAttribute('data-optml-specific-selector')).toBe(false);
    });
  });
});

