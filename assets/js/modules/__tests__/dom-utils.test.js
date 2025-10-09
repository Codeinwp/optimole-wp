/**
 * Tests for Optimole DOM Utilities Module
 */

import { optmlDomUtils } from '../dom-utils.js';

describe('optmlDomUtils', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
  });

  describe('debounce', () => {
    beforeEach(() => {
      jest.useFakeTimers();
    });

    afterEach(() => {
      jest.useRealTimers();
    });

    test('should delay function execution', () => {
      const mockFn = jest.fn();
      const debouncedFn = optmlDomUtils.debounce(mockFn, 100);

      debouncedFn();
      expect(mockFn).not.toHaveBeenCalled();

      jest.advanceTimersByTime(100);
      expect(mockFn).toHaveBeenCalledTimes(1);
    });

    test('should cancel previous calls', () => {
      const mockFn = jest.fn();
      const debouncedFn = optmlDomUtils.debounce(mockFn, 100);

      debouncedFn();
      debouncedFn();
      debouncedFn();

      jest.advanceTimersByTime(100);
      expect(mockFn).toHaveBeenCalledTimes(1);
    });

    test('should pass arguments to debounced function', () => {
      const mockFn = jest.fn();
      const debouncedFn = optmlDomUtils.debounce(mockFn, 100);

      debouncedFn('arg1', 'arg2');
      jest.advanceTimersByTime(100);

      expect(mockFn).toHaveBeenCalledWith('arg1', 'arg2');
    });

    test('should preserve context', () => {
      const context = { value: 42 };
      const mockFn = jest.fn(function() {
        return this.value;
      });
      const debouncedFn = optmlDomUtils.debounce(mockFn, 100);

      debouncedFn.call(context);
      jest.advanceTimersByTime(100);

      expect(mockFn).toHaveBeenCalled();
    });
  });

  describe('getUniqueSelector', () => {
    test('should return "body" for body element', () => {
      const selector = optmlDomUtils.getUniqueSelector(document.body);
      expect(selector).toBe('body');
    });

    test('should return "body" for null element', () => {
      const selector = optmlDomUtils.getUniqueSelector(null);
      expect(selector).toBe('body');
    });

    test('should use ID if available', () => {
      document.body.innerHTML = '<div id="unique-id"></div>';
      const element = document.getElementById('unique-id');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).toBe('#unique-id');
    });

    test('should include tag name and classes', () => {
      document.body.innerHTML = '<div class="test-class another-class"></div>';
      const element = document.querySelector('div');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).toContain('div');
      expect(selector).toContain('test-class');
      expect(selector).toContain('another-class');
    });

    test('should filter out optml-bg-lazyloaded class', () => {
      document.body.innerHTML = '<div class="test-class optml-bg-lazyloaded another-class"></div>';
      const element = document.querySelector('div');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).not.toContain('optml-bg-lazyloaded');
      expect(selector).toContain('test-class');
      expect(selector).toContain('another-class');
    });

    test('should handle element without classes', () => {
      document.body.innerHTML = '<div></div>';
      const element = document.querySelector('div');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).toContain('div');
    });

    test('should use nth-of-type when multiple siblings of same type exist', () => {
      // Create a container with multiple divs without IDs
      const container = document.createElement('div');
      container.innerHTML = `
        <div class="item"></div>
        <div class="item"></div>
        <div class="item"></div>
      `;
      document.body.appendChild(container);
      
      // Get the second div
      const divs = container.querySelectorAll('div.item');
      const secondDiv = divs[1];
      
      const selector = optmlDomUtils.getUniqueSelector(secondDiv);
      
      // Should contain nth-of-type since there are multiple divs
      expect(selector).toContain(':nth-of-type(2)');
    });

    test('should not use nth-of-type when element is only child of its type', () => {
      document.body.innerHTML = '<div><span></span></div>';
      const element = document.querySelector('span');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).not.toContain(':nth-of-type');
    });

    test('should handle nested elements', () => {
      document.body.innerHTML = '<div id="parent"><span class="child"></span></div>';
      const element = document.querySelector('span');
      const selector = optmlDomUtils.getUniqueSelector(element);
      expect(selector).toContain('#parent');
      expect(selector).toContain('span');
    });
  });

  describe('hasBackgroundImage', () => {
    test('should return false when no background image', () => {
      document.body.innerHTML = '<div></div>';
      const element = document.querySelector('div');
      expect(optmlDomUtils.hasBackgroundImage(element)).toBe(false);
    });

    test('should return false for background-image: none', () => {
      document.body.innerHTML = '<div style="background-image: none;"></div>';
      const element = document.querySelector('div');
      expect(optmlDomUtils.hasBackgroundImage(element)).toBe(false);
    });

    test('should return true when background image exists', () => {
      document.body.innerHTML = '<div style="background-image: url(test.jpg);"></div>';
      const element = document.querySelector('div');
      expect(optmlDomUtils.hasBackgroundImage(element)).toBe(true);
    });

    test('should return URL when returnUrl is true', () => {
      document.body.innerHTML = '<div style="background-image: url(test.jpg);"></div>';
      const element = document.querySelector('div');
      const result = optmlDomUtils.hasBackgroundImage(element, true);
      expect(result).toContain('url');
      expect(result).toContain('test.jpg');
    });

    test('should return false for empty background-image', () => {
      document.body.innerHTML = '<div style="background-image: ;"></div>';
      const element = document.querySelector('div');
      expect(optmlDomUtils.hasBackgroundImage(element)).toBe(false);
    });
  });

  describe('extractUrlsFromBgImage', () => {
    test('should return null for null input', () => {
      expect(optmlDomUtils.extractUrlsFromBgImage(null)).toBeNull();
    });

    test('should return null for empty string', () => {
      expect(optmlDomUtils.extractUrlsFromBgImage('')).toBeNull();
    });

    test('should extract single URL without quotes', () => {
      const bgImage = 'url(test.jpg)';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['test.jpg']);
    });

    test('should extract single URL with double quotes', () => {
      const bgImage = 'url("test.jpg")';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['test.jpg']);
    });

    test('should extract single URL with single quotes', () => {
      const bgImage = "url('test.jpg')";
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['test.jpg']);
    });

    test('should extract multiple URLs', () => {
      const bgImage = 'url(image1.jpg), url(image2.jpg)';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['image1.jpg', 'image2.jpg']);
    });

    test('should extract URLs from gradient with images', () => {
      const bgImage = 'linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(background.jpg)';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['background.jpg']);
    });

    test('should return null when no URLs found', () => {
      const bgImage = 'linear-gradient(red, blue)';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toBeNull();
    });

    test('should handle complex URLs', () => {
      const bgImage = 'url(https://example.com/path/to/image.jpg?param=value)';
      const urls = optmlDomUtils.extractUrlsFromBgImage(bgImage);
      expect(urls).toEqual(['https://example.com/path/to/image.jpg?param=value']);
    });
  });

  describe('checkPageConditions', () => {
    test('should return valid conditions for normal page', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      Object.defineProperty(window, 'innerHeight', { writable: true, configurable: true, value: 768 });
      Object.defineProperty(document, 'visibilityState', { writable: true, configurable: true, value: 'visible' });
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'complete' });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.hasValidViewport).toBe(true);
      expect(conditions.isVisible).toBe(true);
      expect(conditions.isComplete).toBe(true);
    });

    test('should detect invalid viewport with zero width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 0 });
      Object.defineProperty(window, 'innerHeight', { writable: true, configurable: true, value: 768 });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.hasValidViewport).toBe(false);
    });

    test('should detect invalid viewport with zero height', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      Object.defineProperty(window, 'innerHeight', { writable: true, configurable: true, value: 0 });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.hasValidViewport).toBe(false);
    });

    test('should detect hidden page', () => {
      Object.defineProperty(document, 'visibilityState', { writable: true, configurable: true, value: 'hidden' });
      Object.defineProperty(document, 'prerendering', { writable: true, configurable: true, value: false });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.isVisible).toBe(false);
    });

    test('should consider prerendering page as visible', () => {
      Object.defineProperty(document, 'visibilityState', { writable: true, configurable: true, value: 'hidden' });
      Object.defineProperty(document, 'prerendering', { writable: true, configurable: true, value: true });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.isVisible).toBe(true);
    });

    test('should detect incomplete page', () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'loading' });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.isComplete).toBe(false);
    });

    test('should detect interactive page as incomplete', () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'interactive' });

      const conditions = optmlDomUtils.checkPageConditions();
      expect(conditions.isComplete).toBe(false);
    });
  });

  describe('waitForPageLoad', () => {
    test('should resolve immediately if page is already loaded', async () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'complete' });

      const startTime = Date.now();
      await optmlDomUtils.waitForPageLoad();
      const endTime = Date.now();

      expect(endTime - startTime).toBeLessThan(10);
    });

    test('should wait for load event if page not complete', async () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'loading' });

      const promise = optmlDomUtils.waitForPageLoad();
      
      // Simulate load event
      setTimeout(() => {
        window.dispatchEvent(new Event('load'));
      }, 10);

      await promise;
      expect(true).toBe(true); // If we get here, promise resolved
    });
  });

  describe('waitForIdleTime', () => {
    test('should use requestIdleCallback if available', async () => {
      const mockRequestIdleCallback = jest.fn((cb) => setTimeout(cb, 0));
      global.requestIdleCallback = mockRequestIdleCallback;

      await optmlDomUtils.waitForIdleTime();

      expect(mockRequestIdleCallback).toHaveBeenCalled();
    });

    test('should fallback to setTimeout if requestIdleCallback not available', async () => {
      const originalRequestIdleCallback = global.requestIdleCallback;
      delete global.requestIdleCallback;

      const startTime = Date.now();
      await optmlDomUtils.waitForIdleTime();
      const endTime = Date.now();

      expect(endTime - startTime).toBeGreaterThanOrEqual(200);
      
      // Restore
      global.requestIdleCallback = originalRequestIdleCallback;
    });
  });

  describe('waitForViewportImages', () => {
    test('should resolve after delay if page already loaded', async () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'complete' });

      const startTime = Date.now();
      await optmlDomUtils.waitForViewportImages(100);
      const endTime = Date.now();

      expect(endTime - startTime).toBeGreaterThanOrEqual(100);
    });

    test('should wait for load event and then delay', async () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'loading' });

      const promise = optmlDomUtils.waitForViewportImages(50);
      
      // Simulate load event
      setTimeout(() => {
        window.dispatchEvent(new Event('load'));
      }, 10);

      const startTime = Date.now();
      await promise;
      const endTime = Date.now();

      expect(endTime - startTime).toBeGreaterThanOrEqual(50);
    });

    test('should use default delay of 1000ms', async () => {
      Object.defineProperty(document, 'readyState', { writable: true, configurable: true, value: 'complete' });

      const startTime = Date.now();
      await optmlDomUtils.waitForViewportImages();
      const endTime = Date.now();

      expect(endTime - startTime).toBeGreaterThanOrEqual(1000);
    });
  });
});

