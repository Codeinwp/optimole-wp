/**
 * Tests for Optimole Device Detection Module
 */

import { optmlDevice } from '../device.js';
import { optmlLogger } from '../logger.js';

// Mock the logger
jest.mock('../logger.js', () => ({
  optmlLogger: {
    info: jest.fn()
  }
}));

describe('optmlDevice', () => {
  let originalInnerWidth;

  beforeEach(() => {
    originalInnerWidth = window.innerWidth;
    jest.clearAllMocks();
  });

  afterEach(() => {
    // Restore original window.innerWidth
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: originalInnerWidth
    });
  });

  describe('DEVICE_TYPES constants', () => {
    test('should have MOBILE constant with value 1', () => {
      expect(optmlDevice.DEVICE_TYPES.MOBILE).toBe(1);
    });

    test('should have DESKTOP constant with value 2', () => {
      expect(optmlDevice.DEVICE_TYPES.DESKTOP).toBe(2);
    });
  });

  describe('MOBILE_BREAKPOINT constant', () => {
    test('should have breakpoint set to 600', () => {
      expect(optmlDevice.MOBILE_BREAKPOINT).toBe(600);
    });
  });

  describe('getDeviceType', () => {
    test('should return MOBILE for width 320', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 320 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.MOBILE);
      expect(optmlLogger.info).toHaveBeenCalledWith('Device detected as mobile based on width:', 320);
    });

    test('should return MOBILE for width 600', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 600 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.MOBILE);
      expect(optmlLogger.info).toHaveBeenCalledWith('Device detected as mobile based on width:', 600);
    });

    test('should return DESKTOP for width 601', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 601 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.DESKTOP);
      expect(optmlLogger.info).toHaveBeenCalledWith('Device detected as desktop based on width:', 601);
    });

    test('should return DESKTOP for width 768', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 768 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.DESKTOP);
      expect(optmlLogger.info).toHaveBeenCalledWith('Device detected as desktop based on width:', 768);
    });

    test('should return DESKTOP for width 1024', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.DESKTOP);
    });

    test('should return DESKTOP for width 1920', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1920 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.DESKTOP);
    });

    test('should handle very small width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 100 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.MOBILE);
    });

    test('should handle very large width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 3840 });
      expect(optmlDevice.getDeviceType()).toBe(optmlDevice.DEVICE_TYPES.DESKTOP);
    });
  });

  describe('isMobile', () => {
    test('should return true for mobile width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 375 });
      expect(optmlDevice.isMobile()).toBe(true);
    });

    test('should return false for desktop width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      expect(optmlDevice.isMobile()).toBe(false);
    });

    test('should return true at breakpoint boundary', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 600 });
      expect(optmlDevice.isMobile()).toBe(true);
    });

    test('should return false just above breakpoint', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 601 });
      expect(optmlDevice.isMobile()).toBe(false);
    });
  });

  describe('isDesktop', () => {
    test('should return false for mobile width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 375 });
      expect(optmlDevice.isDesktop()).toBe(false);
    });

    test('should return true for desktop width', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      expect(optmlDevice.isDesktop()).toBe(true);
    });

    test('should return false at breakpoint boundary', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 600 });
      expect(optmlDevice.isDesktop()).toBe(false);
    });

    test('should return true just above breakpoint', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 601 });
      expect(optmlDevice.isDesktop()).toBe(true);
    });
  });

  describe('consistency between methods', () => {
    test('isMobile and isDesktop should be mutually exclusive for mobile', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 375 });
      expect(optmlDevice.isMobile()).toBe(true);
      expect(optmlDevice.isDesktop()).toBe(false);
    });

    test('isMobile and isDesktop should be mutually exclusive for desktop', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 1024 });
      expect(optmlDevice.isMobile()).toBe(false);
      expect(optmlDevice.isDesktop()).toBe(true);
    });

    test('getDeviceType should match isMobile result', () => {
      Object.defineProperty(window, 'innerWidth', { writable: true, configurable: true, value: 500 });
      const deviceType = optmlDevice.getDeviceType();
      const isMobile = optmlDevice.isMobile();
      expect(deviceType === optmlDevice.DEVICE_TYPES.MOBILE).toBe(isMobile);
    });
  });
});

