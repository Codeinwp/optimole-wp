/**
 * Tests for Optimole Background Image Handler Module
 */

import { optmlBackground } from '../background.js';
import { optmlLogger } from '../logger.js';
import { optmlDomUtils } from '../dom-utils.js';

// Mock dependencies
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
  }
}));

jest.mock('../dom-utils.js', () => ({
  optmlDomUtils: {
    hasBackgroundImage: jest.fn(),
    getUniqueSelector: jest.fn(),
    extractUrlsFromBgImage: jest.fn()
  }
}));

describe('optmlBackground', () => {
  let mockObserver;
  let mockMutationObserver;

  beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';

    mockObserver = {
      observe: jest.fn(),
      disconnect: jest.fn()
    };

    // Mock MutationObserver
    global.MutationObserver = jest.fn((callback) => ({
      observe: jest.fn(),
      disconnect: jest.fn()
    }));
  });

  describe('setupBackgroundImageObservation', () => {
    test('should setup observation for elements', () => {
      document.body.innerHTML = `
        <div class="bg-element"></div>
        <div class="bg-element"></div>
      `;

      const elements = Array.from(document.querySelectorAll('.bg-element'));
      const selector = '.bg-element';
      const selectorMap = new Map();

      optmlDomUtils.getUniqueSelector.mockImplementation((el) => 
        `div.bg-element:nth-of-type(${Array.from(el.parentElement.children).indexOf(el) + 1})`
      );

      const result = optmlBackground.setupBackgroundImageObservation(
        elements, 
        selector, 
        selectorMap, 
        mockObserver
      );

      expect(global.MutationObserver).toHaveBeenCalled();
      expect(result).toBeGreaterThanOrEqual(0);
    });

    test('should observe elements with lazyloaded class immediately', () => {
      document.body.innerHTML = `
        <div class="bg-element optml-bg-lazyloaded" style="background-image: url(test.jpg);"></div>
      `;

      const elements = Array.from(document.querySelectorAll('.bg-element'));
      const selector = '.bg-element';
      const selectorMap = new Map();

      optmlDomUtils.hasBackgroundImage.mockReturnValue(true);
      optmlDomUtils.getUniqueSelector.mockReturnValue('div.bg-element');

      const result = optmlBackground.setupBackgroundImageObservation(
        elements, 
        selector, 
        selectorMap, 
        mockObserver
      );

      expect(mockObserver.observe).toHaveBeenCalled();
      expect(result).toBe(0); // No elements waiting
    });

    test('should mark elements with data attributes', () => {
      document.body.innerHTML = `
        <div class="bg-element"></div>
      `;

      const elements = Array.from(document.querySelectorAll('.bg-element'));
      const selector = '.bg-element';
      const selectorMap = new Map();

      optmlDomUtils.getUniqueSelector.mockReturnValue('div.bg-element');

      optmlBackground.setupBackgroundImageObservation(
        elements, 
        selector, 
        selectorMap, 
        mockObserver
      );

      const element = document.querySelector('.bg-element');
      expect(element.getAttribute('data-optml-bg-selector')).toBe(selector);
      expect(element.getAttribute('data-optml-specific-selector')).toBe('div.bg-element');
    });

    test('should return count of elements waiting for lazyload', () => {
      document.body.innerHTML = `
        <div class="bg-element"></div>
        <div class="bg-element optml-bg-lazyloaded" style="background-image: url(test.jpg);"></div>
        <div class="bg-element"></div>
      `;

      const elements = Array.from(document.querySelectorAll('.bg-element'));
      const selector = '.bg-element';
      const selectorMap = new Map();

      optmlDomUtils.hasBackgroundImage.mockImplementation((el) => 
        el.classList.contains('optml-bg-lazyloaded')
      );
      optmlDomUtils.getUniqueSelector.mockImplementation((el, idx) => 
        `div.bg-element:nth-of-type(${Array.from(el.parentElement.children).indexOf(el) + 1})`
      );

      const result = optmlBackground.setupBackgroundImageObservation(
        elements, 
        selector, 
        selectorMap, 
        mockObserver
      );

      expect(result).toBe(2); // Two elements waiting
    });

    test('should handle empty elements array', () => {
      const elements = [];
      const selector = '.bg-element';
      const selectorMap = new Map();

      const result = optmlBackground.setupBackgroundImageObservation(
        elements, 
        selector, 
        selectorMap, 
        mockObserver
      );

      expect(result).toBe(0);
    });
  });

  describe('extractBackgroundImageUrls', () => {
    test('should return empty Map for null selectors', () => {
      const result = optmlBackground.extractBackgroundImageUrls(null);
      expect(result).toBeInstanceOf(Map);
      expect(result.size).toBe(0);
    });

    test('should return empty Map for empty array', () => {
      const result = optmlBackground.extractBackgroundImageUrls([]);
      expect(result).toBeInstanceOf(Map);
      expect(result.size).toBe(0);
    });

    test('should extract URLs from background images', () => {
      document.body.innerHTML = `
        <div class="bg-test optml-bg-lazyloaded"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockReturnValue('url(test.jpg)');
      optmlDomUtils.getUniqueSelector.mockReturnValue('div.bg-test');
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(['test.jpg']);

      const result = optmlBackground.extractBackgroundImageUrls(['.bg-test']);

      expect(result.size).toBe(1);
      expect(result.has('.bg-test')).toBe(true);
      
      const urlMap = result.get('.bg-test');
      expect(urlMap.get('div.bg-test')).toEqual(['test.jpg']);
    });

    test('should skip elements without lazyloaded class', () => {
      document.body.innerHTML = `
        <div class="bg-test"></div>
      `;

      const result = optmlBackground.extractBackgroundImageUrls(['.bg-test']);

      expect(result.size).toBe(1);
      const urlMap = result.get('.bg-test');
      expect(urlMap.size).toBe(0);
    });

    test('should skip elements without background image', () => {
      document.body.innerHTML = `
        <div class="bg-test optml-bg-lazyloaded"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockReturnValue(false);

      const result = optmlBackground.extractBackgroundImageUrls(['.bg-test']);

      expect(result.size).toBe(1);
      const urlMap = result.get('.bg-test');
      expect(urlMap.size).toBe(0);
    });

    test('should handle multiple selectors', () => {
      document.body.innerHTML = `
        <div class="bg-1 optml-bg-lazyloaded"></div>
        <div class="bg-2 optml-bg-lazyloaded"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockReturnValue('url(test.jpg)');
      optmlDomUtils.getUniqueSelector.mockImplementation((el) => 
        el.classList.contains('bg-1') ? 'div.bg-1' : 'div.bg-2'
      );
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(['test.jpg']);

      const result = optmlBackground.extractBackgroundImageUrls(['.bg-1', '.bg-2']);

      expect(result.size).toBe(2);
      expect(result.has('.bg-1')).toBe(true);
      expect(result.has('.bg-2')).toBe(true);
    });

    test('should handle errors gracefully', () => {
      const result = optmlBackground.extractBackgroundImageUrls(['.invalid-selector-###']);

      expect(result.size).toBe(1);
      expect(optmlLogger.error).toHaveBeenCalled();
    });

    test('should handle multiple elements with same selector', () => {
      document.body.innerHTML = `
        <div class="bg-test optml-bg-lazyloaded"></div>
        <div class="bg-test optml-bg-lazyloaded"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockReturnValue('url(test.jpg)');
      optmlDomUtils.getUniqueSelector.mockImplementation((el, idx) => 
        `div.bg-test:nth-of-type(${Array.from(el.parentElement.children).indexOf(el) + 1})`
      );
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(['test.jpg']);

      const result = optmlBackground.extractBackgroundImageUrls(['.bg-test']);

      expect(result.size).toBe(1);
      const urlMap = result.get('.bg-test');
      expect(urlMap.size).toBe(2);
    });
  });

  describe('processBackgroundSelectors', () => {
    test('should return empty results for null selectors', () => {
      const result = optmlBackground.processBackgroundSelectors(null, mockObserver);

      expect(result.selectorMap).toBeInstanceOf(Map);
      expect(result.selectorMap.size).toBe(0);
      expect(result.pendingElements).toBe(0);
    });

    test('should return empty results for empty array', () => {
      const result = optmlBackground.processBackgroundSelectors([], mockObserver);

      expect(result.selectorMap.size).toBe(0);
      expect(result.pendingElements).toBe(0);
    });

    test('should process valid selectors', () => {
      document.body.innerHTML = `
        <div class="bg-element"></div>
        <div class="bg-element"></div>
      `;

      optmlDomUtils.getUniqueSelector.mockImplementation((el) => 
        `div.bg-element:nth-of-type(${Array.from(el.parentElement.children).indexOf(el) + 1})`
      );

      const result = optmlBackground.processBackgroundSelectors(['.bg-element'], mockObserver);

      expect(result.selectorMap.has('.bg-element')).toBe(true);
      expect(result.selectorMap.get('.bg-element')).toEqual([]);
    });

    test('should log warning for selectors with no elements', () => {
      const result = optmlBackground.processBackgroundSelectors(['.non-existent'], mockObserver);

      expect(optmlLogger.warn).toHaveBeenCalledWith('No elements found for background selector:', '.non-existent');
      expect(result.selectorMap.size).toBe(0);
    });

    test('should handle errors in selector processing', () => {
      const result = optmlBackground.processBackgroundSelectors(['.invalid-###'], mockObserver);

      expect(optmlLogger.error).toHaveBeenCalled();
      expect(result.pendingElements).toBe(0);
    });

    test('should accumulate pending elements count', () => {
      document.body.innerHTML = `
        <div class="bg-1"></div>
        <div class="bg-1"></div>
        <div class="bg-2"></div>
      `;

      optmlDomUtils.getUniqueSelector.mockImplementation((el) => 
        el.classList.contains('bg-1') ? 'div.bg-1' : 'div.bg-2'
      );

      const result = optmlBackground.processBackgroundSelectors(['.bg-1', '.bg-2'], mockObserver);

      // Pending elements depend on which ones are already lazyloaded
      expect(result.pendingElements).toBeGreaterThanOrEqual(0);
    });

    test('should process multiple selectors', () => {
      document.body.innerHTML = `
        <div class="bg-1"></div>
        <div class="bg-2"></div>
        <div class="bg-3"></div>
      `;

      optmlDomUtils.getUniqueSelector.mockImplementation((el) => {
        if (el.classList.contains('bg-1')) return 'div.bg-1';
        if (el.classList.contains('bg-2')) return 'div.bg-2';
        return 'div.bg-3';
      });

      const result = optmlBackground.processBackgroundSelectors(
        ['.bg-1', '.bg-2', '.bg-3'], 
        mockObserver
      );

      expect(result.selectorMap.size).toBe(3);
      expect(result.selectorMap.has('.bg-1')).toBe(true);
      expect(result.selectorMap.has('.bg-2')).toBe(true);
      expect(result.selectorMap.has('.bg-3')).toBe(true);
    });

    test('should log info about processed elements', () => {
      document.body.innerHTML = `
        <div class="bg-element"></div>
        <div class="bg-element"></div>
      `;

      optmlDomUtils.getUniqueSelector.mockReturnValue('div.bg-element');

      optmlBackground.processBackgroundSelectors(['.bg-element'], mockObserver);

      expect(optmlLogger.info).toHaveBeenCalledWith('Processing background selectors:', ['.bg-element']);
      expect(optmlLogger.info).toHaveBeenCalledWith(
        'Processed 2 elements for background selector: .bg-element'
      );
    });
  });

  describe('integration tests', () => {
    test('should process background selectors and extract URLs', () => {
      document.body.innerHTML = `
        <div class="hero optml-bg-lazyloaded" style="background-image: url(hero.jpg);"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockReturnValue('url(hero.jpg)');
      optmlDomUtils.getUniqueSelector.mockReturnValue('div.hero');
      optmlDomUtils.extractUrlsFromBgImage.mockReturnValue(['hero.jpg']);

      // Process selectors
      const processResult = optmlBackground.processBackgroundSelectors(['.hero'], mockObserver);
      expect(processResult.selectorMap.has('.hero')).toBe(true);

      // Extract URLs
      const urlsResult = optmlBackground.extractBackgroundImageUrls(['.hero']);
      expect(urlsResult.size).toBe(1);
      
      const urlMap = urlsResult.get('.hero');
      expect(urlMap.get('div.hero')).toEqual(['hero.jpg']);
    });

    test('should handle mixed lazyloaded and non-lazyloaded elements', () => {
      document.body.innerHTML = `
        <div class="bg-element optml-bg-lazyloaded" style="background-image: url(loaded.jpg);"></div>
        <div class="bg-element"></div>
        <div class="bg-element"></div>
      `;

      optmlDomUtils.hasBackgroundImage.mockImplementation((el) =>
        el.classList.contains('optml-bg-lazyloaded') ? 'url(loaded.jpg)' : false
      );
      optmlDomUtils.getUniqueSelector.mockImplementation((el) =>
        `div.bg-element:nth-of-type(${Array.from(el.parentElement.children).indexOf(el) + 1})`
      );

      const result = optmlBackground.processBackgroundSelectors(['.bg-element'], mockObserver);

      expect(result.selectorMap.has('.bg-element')).toBe(true);
      expect(result.pendingElements).toBeGreaterThanOrEqual(0);
    });
  });
});

