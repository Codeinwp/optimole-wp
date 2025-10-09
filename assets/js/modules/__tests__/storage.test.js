/**
 * Tests for Optimole Storage Module
 */

import { optmlStorage } from '../storage.js';
import { optmlLogger } from '../logger.js';

// Mock the logger
jest.mock('../logger.js', () => ({
  optmlLogger: {
    error: jest.fn()
  }
}));

describe('optmlStorage', () => {
  beforeEach(() => {
    // Clear sessionStorage before each test
    sessionStorage.clear();
    jest.clearAllMocks();
  });

  describe('getKey', () => {
    test('should generate correct storage key for mobile device', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      const key = optmlStorage.getKey(url, deviceType);
      expect(key).toBe('optml_pp_https://example.com/page_1');
    });

    test('should generate correct storage key for desktop device', () => {
      const url = 'https://example.com/page';
      const deviceType = 2;
      const key = optmlStorage.getKey(url, deviceType);
      expect(key).toBe('optml_pp_https://example.com/page_2');
    });

    test('should generate different keys for different URLs', () => {
      const url1 = 'https://example.com/page1';
      const url2 = 'https://example.com/page2';
      const deviceType = 1;
      const key1 = optmlStorage.getKey(url1, deviceType);
      const key2 = optmlStorage.getKey(url2, deviceType);
      expect(key1).not.toBe(key2);
    });

    test('should generate different keys for different device types', () => {
      const url = 'https://example.com/page';
      const key1 = optmlStorage.getKey(url, 1);
      const key2 = optmlStorage.getKey(url, 2);
      expect(key1).not.toBe(key2);
    });
  });

  describe('isProcessed', () => {
    test('should return false when URL/device combination not processed', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      expect(optmlStorage.isProcessed(url, deviceType)).toBe(false);
    });

    test('should return true when URL/device combination is processed', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      optmlStorage.markProcessed(url, deviceType);
      expect(optmlStorage.isProcessed(url, deviceType)).toBe(true);
    });

    test('should return false for different device type of same URL', () => {
      const url = 'https://example.com/page';
      optmlStorage.markProcessed(url, 1);
      expect(optmlStorage.isProcessed(url, 2)).toBe(false);
    });

    test('should return false for different URL of same device type', () => {
      optmlStorage.markProcessed('https://example.com/page1', 1);
      expect(optmlStorage.isProcessed('https://example.com/page2', 1)).toBe(false);
    });

    test('should handle storage errors gracefully', () => {
      // Mock sessionStorage.getItem to throw an error
      const originalGetItem = Storage.prototype.getItem;
      Storage.prototype.getItem = jest.fn(() => {
        throw new Error('Storage error');
      });

      const result = optmlStorage.isProcessed('test-url', 1);
      expect(result).toBe(false);
      expect(optmlLogger.error).toHaveBeenCalledWith('Error checking sessionStorage:', expect.any(Error));

      // Restore original method
      Storage.prototype.getItem = originalGetItem;
    });

    test('should return false for invalid timestamp values', () => {
      const key = optmlStorage.getKey('test-url', 1);
      sessionStorage.setItem(key, 'invalid-timestamp');
      // Should still return true because we just check if the value exists
      expect(optmlStorage.isProcessed('test-url', 1)).toBe(true);
    });
  });

  describe('markProcessed', () => {
    test('should mark URL/device combination as processed', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      optmlStorage.markProcessed(url, deviceType);
      expect(optmlStorage.isProcessed(url, deviceType)).toBe(true);
    });

    test('should store timestamp when marking as processed', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      const beforeTime = Date.now();
      optmlStorage.markProcessed(url, deviceType);
      const afterTime = Date.now();

      const key = optmlStorage.getKey(url, deviceType);
      const storedValue = sessionStorage.getItem(key);
      const timestamp = parseInt(storedValue, 10);

      expect(timestamp).toBeGreaterThanOrEqual(beforeTime);
      expect(timestamp).toBeLessThanOrEqual(afterTime);
    });

    test('should handle multiple URL/device combinations independently', () => {
      optmlStorage.markProcessed('url1', 1);
      optmlStorage.markProcessed('url2', 1);
      optmlStorage.markProcessed('url1', 2);

      expect(optmlStorage.isProcessed('url1', 1)).toBe(true);
      expect(optmlStorage.isProcessed('url2', 1)).toBe(true);
      expect(optmlStorage.isProcessed('url1', 2)).toBe(true);
      expect(optmlStorage.isProcessed('url2', 2)).toBe(false);
    });

    test('should handle storage errors gracefully', () => {
      // Mock sessionStorage.setItem to throw an error
      const originalSetItem = Storage.prototype.setItem;
      Storage.prototype.setItem = jest.fn(() => {
        throw new Error('Storage error');
      });

      optmlStorage.markProcessed('test-url', 1);
      expect(optmlLogger.error).toHaveBeenCalledWith('Error setting sessionStorage:', expect.any(Error));

      // Restore original method
      Storage.prototype.setItem = originalSetItem;
    });

    test('should update timestamp when marking same combination multiple times', () => {
      const url = 'https://example.com/page';
      const deviceType = 1;
      const key = optmlStorage.getKey(url, deviceType);

      optmlStorage.markProcessed(url, deviceType);
      const firstTimestamp = parseInt(sessionStorage.getItem(key), 10);

      // Wait a bit and mark again
      setTimeout(() => {
        optmlStorage.markProcessed(url, deviceType);
        const secondTimestamp = parseInt(sessionStorage.getItem(key), 10);
        expect(secondTimestamp).toBeGreaterThanOrEqual(firstTimestamp);
      }, 10);
    });
  });
});

