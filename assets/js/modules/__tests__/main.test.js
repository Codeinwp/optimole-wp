/**
 * Tests for Optimole Main Module
 */

import { optmlMain } from '../main.js';
import { optmlLogger } from '../logger.js';
import { optmlStorage } from '../storage.js';
import { optmlDevice } from '../device.js';
import { optmlApi } from '../api.js';
import { optmlDomUtils } from '../dom-utils.js';
import { optmlBackground } from '../background.js';
import { optmlLcp } from '../lcp.js';
import { optmlImageDetector } from '../image-detector.js';
import { optmlSrcsetDetector } from '../srcset-detector.js';

// Mock all dependencies
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
  }
}));

jest.mock('../storage.js', () => ({
  optmlStorage: {
    isProcessed: jest.fn(),
    markProcessed: jest.fn()
  }
}));

jest.mock('../device.js', () => ({
  optmlDevice: {
    getDeviceType: jest.fn()
  }
}));

jest.mock('../api.js', () => ({
  optmlApi: {
    sendToRestApi: jest.fn()
  }
}));

jest.mock('../dom-utils.js', () => ({
  optmlDomUtils: {
    checkPageConditions: jest.fn(),
    waitForPageLoad: jest.fn(),
    waitForViewportImages: jest.fn(),
    waitForIdleTime: jest.fn(),
    debounce: jest.fn((fn) => fn),
    hasBackgroundImage: jest.fn(),
    getUniqueSelector: jest.fn(),
    extractUrlsFromBgImage: jest.fn()
  }
}));

jest.mock('../background.js', () => ({
  optmlBackground: {
    processBackgroundSelectors: jest.fn(),
    extractBackgroundImageUrls: jest.fn()
  }
}));

jest.mock('../lcp.js', () => ({
  optmlLcp: {
    detectLcpElement: jest.fn()
  }
}));

jest.mock('../image-detector.js', () => ({
  optmlImageDetector: {
    createIntersectionObserver: jest.fn(),
    observeOptimoleImages: jest.fn(),
    detectImageDimensions: jest.fn(),
    cleanupBackgroundElements: jest.fn()
  }
}));

jest.mock('../srcset-detector.js', () => ({
  optmlSrcsetDetector: {
    detectMissingSrcsets: jest.fn()
  }
}));

describe('optmlMain', () => {
  let mockObserver;

  beforeEach(() => {
    jest.clearAllMocks();
    document.body.innerHTML = '';
    
    // Setup default mocks
    optmlDevice.getDeviceType.mockReturnValue(2);
    optmlStorage.isProcessed.mockReturnValue(false);
    
    optmlDomUtils.checkPageConditions.mockReturnValue({
      hasValidViewport: true,
      isVisible: true,
      isComplete: true
    });
    
    optmlDomUtils.waitForPageLoad.mockResolvedValue();
    optmlDomUtils.waitForViewportImages.mockResolvedValue();
    optmlDomUtils.waitForIdleTime.mockResolvedValue();
    
    optmlLcp.detectLcpElement.mockResolvedValue({
      element: null,
      imageId: null,
      bgSelector: null,
      bgUrls: null
    });

    mockObserver = {
      observe: jest.fn(),
      disconnect: jest.fn()
    };

    optmlImageDetector.createIntersectionObserver.mockReturnValue(mockObserver);
    optmlImageDetector.observeOptimoleImages.mockReturnValue({
      allOptimoleImages: [],
      observedElements: new Map()
    });
    optmlImageDetector.detectImageDimensions.mockReturnValue({});

    optmlSrcsetDetector.detectMissingSrcsets.mockResolvedValue({
      srcset: {},
      crop: {}
    });

    optmlBackground.processBackgroundSelectors.mockReturnValue({
      selectorMap: new Map(),
      pendingElements: 0
    });
    optmlBackground.extractBackgroundImageUrls.mockReturnValue(new Map());

    // Setup window.optimoleDataOptimizer
    window.optimoleDataOptimizer = {
      pageProfileId: 'test-page-id',
      missingDevices: '2',
      bgSelectors: [],
      _t: '12345',
      hmac: 'test-hmac',
      pageProfileUrl: 'https://example.com/page'
    };

    // Mock page scroll position - reset to top
    delete window.pageYOffset;
    delete document.documentElement.scrollTop;
    Object.defineProperty(window, 'pageYOffset', { writable: true, configurable: true, value: 0 });
    Object.defineProperty(document.documentElement, 'scrollTop', { writable: true, configurable: true, value: 0 });
  });

  afterEach(() => {
    delete window.optimoleDataOptimizer;
  });

  describe('runProfiling', () => {
    test('should return null when viewport is invalid', async () => {
      optmlDomUtils.checkPageConditions.mockReturnValue({
        hasValidViewport: false,
        isVisible: true,
        isComplete: true
      });

      const result = await optmlMain.runProfiling();

      expect(result).toBeNull();
      expect(optmlLogger.info).toHaveBeenCalledWith('Window must have non-zero dimensions for image detection.');
    });

    test('should return null when page is not visible', async () => {
      optmlDomUtils.checkPageConditions.mockReturnValue({
        hasValidViewport: true,
        isVisible: false,
        isComplete: true
      });

      const result = await optmlMain.runProfiling();

      expect(result).toBeNull();
      expect(optmlLogger.info).toHaveBeenCalledWith('Page opened in background tab so image detection is not performed.');
    });

    test('should return null when device type not in missing devices', async () => {
      optmlDevice.getDeviceType.mockReturnValue(1); // Mobile
      window.optimoleDataOptimizer.missingDevices = '2'; // Only desktop missing

      const result = await optmlMain.runProfiling();

      expect(result).toBeNull();
      expect(optmlLogger.info).toHaveBeenCalledWith('Skipping device type, data already exists:', 1);
    });

    test('should return null when already processed', async () => {
      optmlStorage.isProcessed.mockReturnValue(true);

      const result = await optmlMain.runProfiling();

      expect(result).toBeNull();
      expect(optmlLogger.info).toHaveBeenCalledWith('Skipping detection, already processed this device/URL combination');
    });

    test('should return null when user is not at top of page', async () => {
      // Mock both scroll position properties
      Object.defineProperty(window, 'pageYOffset', { value: 100, writable: true, configurable: true });
      Object.defineProperty(document.documentElement, 'scrollTop', { value: 100, writable: true, configurable: true });

      const result = await optmlMain.runProfiling();

      expect(result).toBeNull();
      // The logger will have been called multiple times, check that this message is in one of the calls
      const logCalls = optmlLogger.info.mock.calls.map(call => call[0]);
      expect(logCalls).toContain('User is not at the top of the page, skipping image detection');
    });

    test('should wait for page load when page is not complete', async () => {
      optmlDomUtils.checkPageConditions.mockReturnValue({
        hasValidViewport: true,
        isVisible: true,
        isComplete: false
      });

      // Add global reference for the check
      window.optmlDomUtils = optmlDomUtils;

      await optmlMain.runProfiling();

      expect(optmlDomUtils.waitForPageLoad).toHaveBeenCalled();
      expect(optmlLogger.info).toHaveBeenCalledWith('Waiting for page to fully load...');
      
      delete window.optmlDomUtils;
    });

    test('should detect LCP element', async () => {
      const mockLcpData = {
        element: document.createElement('img'),
        imageId: 123,
        bgSelector: null,
        bgUrls: null
      };
      optmlLcp.detectLcpElement.mockResolvedValue(mockLcpData);

      await optmlMain.runProfiling();

      expect(optmlLcp.detectLcpElement).toHaveBeenCalled();
    });

    test('should create and use intersection observer', async () => {
      await optmlMain.runProfiling();

      expect(optmlImageDetector.createIntersectionObserver).toHaveBeenCalled();
      expect(optmlImageDetector.observeOptimoleImages).toHaveBeenCalledWith(mockObserver);
      expect(mockObserver.disconnect).toHaveBeenCalled();
    });

    test('should detect image dimensions', async () => {
      const mockImages = [document.createElement('img')];
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map()
      });

      await optmlMain.runProfiling();

      expect(optmlImageDetector.detectImageDimensions).toHaveBeenCalledWith(mockImages);
    });

    test('should detect missing srcsets', async () => {
      await optmlMain.runProfiling();

      expect(optmlSrcsetDetector.detectMissingSrcsets).toHaveBeenCalled();
    });

    test('should process background selectors when provided', async () => {
      window.optimoleDataOptimizer.bgSelectors = ['.hero', '.banner'];

      await optmlMain.runProfiling();

      expect(optmlBackground.processBackgroundSelectors).toHaveBeenCalledWith(
        ['.hero', '.banner'],
        mockObserver
      );
      expect(optmlBackground.extractBackgroundImageUrls).toHaveBeenCalledWith(['.hero', '.banner']);
    });

    test('should send data to API when images found', async () => {
      // Create a real image element and add to DOM
      document.body.innerHTML = '<img data-opt-id="123" />';
      const mockImages = document.querySelectorAll('img');
      
      // Mock the observer to simulate finding above-fold images
      const aboveTheFoldImages = [123];
      optmlImageDetector.createIntersectionObserver.mockImplementation((atfImages, selectorMap) => {
        // Simulate adding image to above-fold list
        atfImages.push(123);
        return mockObserver;
      });
      
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map([[mockImages[0], 123]])
      });

      await optmlMain.runProfiling();

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 400));

      expect(optmlApi.sendToRestApi).toHaveBeenCalled();
    });

    test('should return null when no data to send', async () => {
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: [],
        observedElements: new Map()
      });
      
      optmlImageDetector.detectImageDimensions.mockReturnValue({});
      optmlSrcsetDetector.detectMissingSrcsets.mockResolvedValue({
        srcset: {},
        crop: {}
      });

      const result = await optmlMain.runProfiling();

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 400));

      expect(result).toBeNull();
      expect(optmlLogger.info).toHaveBeenCalledWith(
        'No above-the-fold images, background elements, LCP elements, dimension data, or srcset data found'
      );
    });

    test('should clean up event listeners after profiling', async () => {
      await optmlMain.runProfiling();

      // Event listeners should be removed (tested through completion without errors)
      expect(mockObserver.disconnect).toHaveBeenCalled();
      expect(optmlImageDetector.cleanupBackgroundElements).toHaveBeenCalled();
    });

    test('should include LCP data in API payload', async () => {
      const mockLcpData = {
        element: document.createElement('img'),
        imageId: 456,
        bgSelector: 'div.hero',
        bgUrls: ['hero.jpg']
      };
      optmlLcp.detectLcpElement.mockResolvedValue(mockLcpData);

      const mockImages = [document.createElement('img')];
      mockImages[0].setAttribute('data-opt-id', '123');
      
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map([[mockImages[0], 123]])
      });

      await optmlMain.runProfiling();
      await new Promise(resolve => setTimeout(resolve, 400));

      const callArgs = optmlApi.sendToRestApi.mock.calls[0];
      expect(callArgs[0].l).toEqual({
        i: 456,
        s: 'div.hero',
        u: ['hero.jpg']
      });
    });

    test('should include dimension data in API payload', async () => {
      optmlImageDetector.detectImageDimensions.mockReturnValue({
        123: { w: 800, h: 600 },
        456: { w: 1200, h: 900 }
      });

      const mockImages = [document.createElement('img')];
      mockImages[0].setAttribute('data-opt-id', '123');
      
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map([[mockImages[0], 123]])
      });

      await optmlMain.runProfiling();
      await new Promise(resolve => setTimeout(resolve, 400));

      const callArgs = optmlApi.sendToRestApi.mock.calls[0];
      expect(callArgs[0].m).toEqual({
        123: { w: 800, h: 600 },
        456: { w: 1200, h: 900 }
      });
    });

    test('should include srcset data in API payload', async () => {
      optmlSrcsetDetector.detectMissingSrcsets.mockResolvedValue({
        srcset: {
          123: [{ w: 400, h: 300, d: 1, s: '400w', b: 768 }]
        },
        crop: {
          123: true
        }
      });

      const mockImages = [document.createElement('img')];
      mockImages[0].setAttribute('data-opt-id', '123');
      
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map([[mockImages[0], 123]])
      });

      await optmlMain.runProfiling();
      await new Promise(resolve => setTimeout(resolve, 400));

      const callArgs = optmlApi.sendToRestApi.mock.calls[0];
      expect(callArgs[0].s).toEqual({
        123: [{ w: 400, h: 300, d: 1, s: '400w', b: 768 }]
      });
      expect(callArgs[0].c).toEqual({ 123: true });
    });

    test('should handle mixed data sources in API payload', async () => {
      // Setup LCP
      optmlLcp.detectLcpElement.mockResolvedValue({
        element: document.createElement('img'),
        imageId: 999,
        bgSelector: null,
        bgUrls: null
      });

      // Setup dimensions
      optmlImageDetector.detectImageDimensions.mockReturnValue({
        123: { w: 800, h: 600 }
      });

      // Setup srcset
      optmlSrcsetDetector.detectMissingSrcsets.mockResolvedValue({
        srcset: {
          456: [{ w: 400, h: 300, d: 1, s: '400w', b: 768 }]
        },
        crop: {
          456: false
        }
      });

      // Setup background
      const selectorMap = new Map();
      selectorMap.set('.hero', ['div.hero']);
      optmlBackground.processBackgroundSelectors.mockReturnValue({
        selectorMap,
        pendingElements: 0
      });

      const bgUrls = new Map();
      const heroUrls = new Map();
      heroUrls.set('div.hero', ['hero.jpg']);
      bgUrls.set('.hero', heroUrls);
      optmlBackground.extractBackgroundImageUrls.mockReturnValue(bgUrls);

      window.optimoleDataOptimizer.bgSelectors = ['.hero'];

      const mockImages = [document.createElement('img')];
      mockImages[0].setAttribute('data-opt-id', '123');
      
      optmlImageDetector.observeOptimoleImages.mockReturnValue({
        allOptimoleImages: mockImages,
        observedElements: new Map([[mockImages[0], 123]])
      });

      await optmlMain.runProfiling();
      await new Promise(resolve => setTimeout(resolve, 700));

      expect(optmlApi.sendToRestApi).toHaveBeenCalled();
      const callArgs = optmlApi.sendToRestApi.mock.calls[0];
      
      expect(callArgs[0].l.i).toBe(999); // LCP image ID
      expect(callArgs[0].m[123]).toEqual({ w: 800, h: 600 }); // Dimensions
      expect(callArgs[0].s[456]).toBeDefined(); // Srcset
      expect(callArgs[0].c[456]).toBe(false); // Crop status
      expect(callArgs[0].b['.hero']).toBeDefined(); // Background
    });
  });

  describe('_processBackgroundSelectors', () => {
    test('should process background selectors with URLs', () => {
      const selectorMap = new Map();
      selectorMap.set('.hero', ['div.hero', 'section.hero']);

      const bgUrls = new Map();
      const heroUrls = new Map();
      heroUrls.set('div.hero', ['hero1.jpg']);
      heroUrls.set('section.hero', ['hero2.jpg']);
      bgUrls.set('.hero', heroUrls);

      const result = optmlMain._processBackgroundSelectors(selectorMap, bgUrls);

      expect(result['.hero']).toBeDefined();
      expect(result['.hero']['div.hero']).toEqual(['hero1.jpg']);
      expect(result['.hero']['section.hero']).toEqual(['hero2.jpg']);
    });

    test('should handle selectors without URLs', () => {
      const selectorMap = new Map();
      selectorMap.set('.banner', ['div.banner']);

      const bgUrls = new Map();

      const result = optmlMain._processBackgroundSelectors(selectorMap, bgUrls);

      expect(result['.banner']).toBeDefined();
      expect(result['.banner']['div.banner']).toBeNull();
    });

    test('should handle multiple selectors', () => {
      const selectorMap = new Map();
      selectorMap.set('.hero', ['div.hero']);
      selectorMap.set('.banner', ['div.banner']);

      const bgUrls = new Map();
      const heroUrls = new Map();
      heroUrls.set('div.hero', ['hero.jpg']);
      bgUrls.set('.hero', heroUrls);

      const result = optmlMain._processBackgroundSelectors(selectorMap, bgUrls);

      expect(result['.hero']).toBeDefined();
      expect(result['.banner']).toBeDefined();
    });

    test('should handle empty selector map', () => {
      const selectorMap = new Map();
      const bgUrls = new Map();

      const result = optmlMain._processBackgroundSelectors(selectorMap, bgUrls);

      expect(Object.keys(result)).toHaveLength(0);
    });
  });
});

