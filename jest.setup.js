// Setup file for Jest tests
// Add any global test setup here

// Mock URL and URLSearchParams for older environments
if (typeof URL === 'undefined') {
  global.URL = class URL {
    constructor(url) {
      this.href = url;
    }
  };
}

if (typeof URLSearchParams === 'undefined') {
  global.URLSearchParams = class URLSearchParams {
    constructor(search) {
      this.params = new Map();
      if (search) {
        search.replace(/^\?/, '').split('&').forEach(param => {
          const [key, value] = param.split('=');
          if (key) this.params.set(key, value || '');
        });
      }
    }
    has(key) {
      return this.params.has(key);
    }
    get(key) {
      return this.params.get(key);
    }
  };
}

// Mock requestIdleCallback if not available
if (typeof requestIdleCallback === 'undefined') {
  global.requestIdleCallback = (callback) => setTimeout(callback, 0);
}

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
  constructor(callback, options) {
    this.callback = callback;
    this.options = options;
    this.root = options?.root || null;
    this.rootMargin = options?.rootMargin || '0px';
    this.thresholds = Array.isArray(options?.threshold) ? options.threshold : [options?.threshold || 0];
  }
  
  observe() {
    // Mock implementation
  }
  
  unobserve() {
    // Mock implementation
  }
  
  disconnect() {
    // Mock implementation
  }
  
  takeRecords() {
    return [];
  }
};

// Add custom matchers if needed
expect.extend({
  toBeWithinRange(received, floor, ceiling) {
    const pass = received >= floor && received <= ceiling;
    if (pass) {
      return {
        message: () => `expected ${received} not to be within range ${floor} - ${ceiling}`,
        pass: true
      };
    } else {
      return {
        message: () => `expected ${received} to be within range ${floor} - ${ceiling}`,
        pass: false
      };
    }
  }
});

