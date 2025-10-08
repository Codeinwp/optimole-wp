/**
 * Tests for Optimole LCP Detection Module
 */

import { optmlLcp } from '../lcp.js';
import { optmlLogger } from '../logger.js';
import { optmlDomUtils } from '../dom-utils.js';

// Mock dependencies
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn()
  }
}));

jest.mock('../dom-utils.js', () => ({
  optmlDomUtils: {
    hasBackgroundImage: jest.fn(),
    getUniqueSelector: jest.fn(),
    extractUrlsFromBgImage: jest.fn()
  }
}));

describe('optmlLcp', () => {
  let mockPerformanceObserver;
  let observerCallback;

  beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';

    // Mock PerformanceObserver
    mockPerformanceObserver = {
      observe: jest.fn(),
      disconnect: jest.fn()
    };

    observerCallback = null;

    global.PerformanceObserver = jest.fn((callback) => {
      observerCallback = callback;
      return mockPerformanceObserver;
    });

    global.PerformanceObserver.supportedEntryTypes = ['largest-contentful-paint'];

    // Mock performance.getEntriesByType
    global.performance.getEntriesByType = jest.fn(() => []);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  describe('detectLcpElement', () => {
    test('should return empty lcpData when LCP not supported', async () => {
      global.PerformanceObserver.supportedEntryTypes = [];

      const result = await optmlLcp.detectLcpElement();

      expect(result).toEqual({
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      });
      expect(optmlLogger.info).toHaveBeenCalledWith('LCP detection not supported in this browser');
    });

    test('should use existing LCP entry if available', async () => {
      const mockElement = document.createElement('img');
      mockElement.setAttribute('data-opt-id', '123');
      document.body.appendChild(mockElement);

      global.performance.getEntriesByType = jest.fn(() => [{
        element: mockElement
      }]);

      const result = await optmlLcp.detectLcpElement();

      expect(result.element).toBe(mockElement);
      expect(result.imageId).toBe(123);
      expect(optmlLogger.info).toHaveBeenCalledWith('LCP element found from existing entries:', mockElement);
    });

    test('should set up observer when no existing entries', async () => {
      global.performance.getEntriesByType = jest.fn(() => []);

      // Mock setTimeout to immediately trigger timeout
      const originalSetTimeout = global.setTimeout;
      global.setTimeout = jest.fn((callback, delay) => {
        if (delay === 1500) {
          // Call the callback after a short delay to allow promise chain to set up
          Promise.resolve().then(callback);
        }
        return 1;
      });

      const result = await optmlLcp.detectLcpElement();

      expect(global.PerformanceObserver).toHaveBeenCalled();
      expect(mockPerformanceObserver.observe).toHaveBeenCalledWith({
        type: 'largest-contentful-paint',
        buffered: true
      });
      expect(mockPerformanceObserver.disconnect).toHaveBeenCalled();

      // Restore setTimeout
      global.setTimeout = originalSetTimeout;
    });

    test('should process LCP element from observer', async () => {
      global.performance.getEntriesByType = jest.fn(() => []);

      const mockElement = document.createElement('img');
      mockElement.setAttribute('data-opt-id', '456');
      document.body.appendChild(mockElement);

      const promise = optmlLcp.detectLcpElement();

      // Simulate observer callback
      if (observerCallback) {
        observerCallback({
          getEntries: () => [{
            element: mockElement
          }]
        });
      }

      const result = await promise;

      expect(result.element).toBe(mockElement);
      expect(result.imageId).toBe(456);
      expect(mockPerformanceObserver.disconnect).toHaveBeenCalled();
    });

    test('should handle multiple entries and use last one', async () => {
      const mockElement1 = document.createElement('img');
      mockElement1.setAttribute('data-opt-id', '111');
      const mockElement2 = document.createElement('img');
      mockElement2.setAttribute('data-opt-id', '222');

      global.performance.getEntriesByType = jest.fn(() => [
        { element: mockElement1 },
        { element: mockElement2 }
      ]);

      const result = await optmlLcp.detectLcpElement();

      expect(result.element).toBe(mockElement2);
      expect(result.imageId).toBe(222);
    });

    test('should timeout after 1500ms', async () => {
      global.performance.getEntriesByType = jest.fn(() => []);

      // Create a promise that we can control
      let timeoutResolve;
      const timeoutPromise = new Promise(resolve => {
        timeoutResolve = resolve;
      });

      // Mock setTimeout to capture the timeout callback
      const originalSetTimeout = global.setTimeout;
      global.setTimeout = jest.fn((callback, delay) => {
        if (delay === 1500) {
          // Immediately call the callback for testing
          callback();
        }
        return 1;
      });

      const result = await optmlLcp.detectLcpElement();

      expect(mockPerformanceObserver.disconnect).toHaveBeenCalled();
      expect(result.element).toBeNull();

      // Restore setTimeout
      global.setTimeout = originalSetTimeout;
    });
  });

  describe('_processLcpElement', () => {
    test('should do nothing for null element', () => {
      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(null, lcpData);

      expect(lcpData.imageId).toBeNull();
      expect(lcpData.bgSelector).toBeNull();
    });

    test('should extract image ID from IMG element', () => {
      const mockElement = document.createElement('img');
      mockElement.setAttribute('data-opt-id', '789');

      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(mockElement, lcpData);

      expect(lcpData.imageId).toBe(789);
      expect(optmlLogger.info).toHaveBeenCalledWith('LCP element is an Optimole image with ID:', 789);
    });

    test('should handle IMG element without data-opt-id', () => {
      const mockElement = document.createElement('img');

      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(mockElement, lcpData);

      expect(lcpData.imageId).toBeNull();
    });

    test('should process background image for non-IMG elements', () => {
      const mockElement = document.createElement('div');
      const mockBgImage = 'url(test.jpg)';
      const mockSelector = 'div.test-class';
      const mockUrls = ['test.jpg'];

      optmlDomUtils.hasBackgroundImage.mockReturnValue(mockBgImage);
      optmlDomUtils.getUniqueSelector.mockReturnValue(mockSelector);
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(mockUrls);

      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(mockElement, lcpData);

      expect(optmlDomUtils.hasBackgroundImage).toHaveBeenCalledWith(mockElement, true);
      expect(optmlDomUtils.getUniqueSelector).toHaveBeenCalledWith(mockElement);
      expect(optmlDomUtils.extractUrlsFromBgImage).toHaveBeenCalledWith(mockBgImage);
      expect(lcpData.bgSelector).toBe(mockSelector);
      expect(lcpData.bgUrls).toEqual(mockUrls);
      expect(optmlLogger.info).toHaveBeenCalledWith('LCP element has background image:', mockSelector, mockUrls);
    });

    test('should not set bgSelector if no background image', () => {
      const mockElement = document.createElement('div');

      optmlDomUtils.hasBackgroundImage.mockReturnValue(false);

      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(mockElement, lcpData);

      expect(lcpData.bgSelector).toBeNull();
      expect(lcpData.bgUrls).toBeNull();
    });

    test('should handle invalid data-opt-id values', () => {
      const mockElement = document.createElement('img');
      mockElement.setAttribute('data-opt-id', 'invalid');

      const lcpData = {
        element: null,
        imageId: null,
        bgSelector: null,
        bgUrls: null
      };

      optmlLcp._processLcpElement(mockElement, lcpData);

      expect(lcpData.imageId).toBeNaN();
    });
  });

  describe('integration tests', () => {
    test('should detect and process IMG LCP element end-to-end', async () => {
      const mockElement = document.createElement('img');
      mockElement.setAttribute('data-opt-id', '999');
      document.body.appendChild(mockElement);

      global.performance.getEntriesByType = jest.fn(() => [{
        element: mockElement
      }]);

      const result = await optmlLcp.detectLcpElement();

      expect(result.element).toBe(mockElement);
      expect(result.imageId).toBe(999);
      expect(result.bgSelector).toBeNull();
      expect(result.bgUrls).toBeNull();
    });

    test('should detect and process background LCP element end-to-end', async () => {
      const mockElement = document.createElement('div');
      document.body.appendChild(mockElement);

      global.performance.getEntriesByType = jest.fn(() => [{
        element: mockElement
      }]);

      optmlDomUtils.hasBackgroundImage.mockReturnValue('url(bg.jpg)');
      optmlDomUtils.getUniqueSelector.mockReturnValue('div.bg-element');
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(['bg.jpg']);

      const result = await optmlLcp.detectLcpElement();

      expect(result.element).toBe(mockElement);
      expect(result.imageId).toBeNull();
      expect(result.bgSelector).toBe('div.bg-element');
      expect(result.bgUrls).toEqual(['bg.jpg']);
    });
  });
});

