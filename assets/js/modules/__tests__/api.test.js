/**
 * Tests for Optimole API Module
 */

import { optmlApi } from '../api.js';
import { optmlLogger } from '../logger.js';
import { optmlStorage } from '../storage.js';

// Mock dependencies
jest.mock('../logger.js', () => ({
  optmlLogger: {
    error: jest.fn(),
    info: jest.fn()
  }
}));

jest.mock('../storage.js', () => ({
  optmlStorage: {
    markProcessed: jest.fn()
  }
}));

describe('optmlApi', () => {
  let sendBeaconSpy;
  let fetchSpy;

  beforeEach(() => {
    jest.clearAllMocks();
    
    // Mock sendBeacon
    sendBeaconSpy = jest.fn();
    navigator.sendBeacon = sendBeaconSpy;

    // Mock fetch
    fetchSpy = jest.fn();
    global.fetch = fetchSpy;

    // Mock window.optimoleDataOptimizer
    window.optimoleDataOptimizer = {
      restUrl: 'https://example.com/wp-json/optimole/v1'
    };
  });

  afterEach(() => {
    delete window.optimoleDataOptimizer;
  });

  describe('sendToRestApi', () => {
    test('should log error when REST URL not available', () => {
      delete window.optimoleDataOptimizer;
      
      const data = { u: 'test-url', d: 1 };
      optmlApi.sendToRestApi(data);

      expect(optmlLogger.error).toHaveBeenCalledWith('REST API URL not available');
      expect(sendBeaconSpy).not.toHaveBeenCalled();
    });

    test('should send data using sendBeacon when available', () => {
      sendBeaconSpy.mockReturnValue(true);
      
      const data = { u: 'test-url', d: 1 };
      optmlApi.sendToRestApi(data);

      expect(sendBeaconSpy).toHaveBeenCalledTimes(1);
      
      const callArgs = sendBeaconSpy.mock.calls[0];
      expect(callArgs[0]).toBe('https://example.com/wp-json/optimole/v1/optimizations');
      expect(callArgs[1]).toBeInstanceOf(Blob);
      expect(callArgs[1].type).toBe('application/json');
      
      expect(optmlLogger.info).toHaveBeenCalledWith('Data sent successfully using sendBeacon');
      expect(optmlStorage.markProcessed).toHaveBeenCalledWith('test-url', 1);
    });

    test('should create correct JSON blob for sendBeacon', () => {
      sendBeaconSpy.mockReturnValue(true);
      
      const data = { u: 'test-url', d: 1, a: [1, 2, 3] };
      optmlApi.sendToRestApi(data);

      const callArgs = sendBeaconSpy.mock.calls[0];
      const blob = callArgs[1];
      
      // Verify blob properties
      expect(blob).toBeInstanceOf(Blob);
      expect(blob.type).toBe('application/json');
      
      // Verify the data was serialized correctly by checking the call was made
      expect(sendBeaconSpy).toHaveBeenCalledWith(
        'https://example.com/wp-json/optimole/v1/optimizations',
        expect.any(Blob)
      );
    });

    test('should fallback to fetch when sendBeacon fails', () => {
      sendBeaconSpy.mockReturnValue(false);
      fetchSpy.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ success: true })
      });
      
      const data = { u: 'test-url', d: 1 };
      optmlApi.sendToRestApi(data);

      expect(sendBeaconSpy).toHaveBeenCalled();
      expect(optmlLogger.error).toHaveBeenCalledWith('Failed to send data using sendBeacon');
      expect(fetchSpy).toHaveBeenCalled();
    });

    test('should mark as processed after successful sendBeacon', () => {
      sendBeaconSpy.mockReturnValue(true);
      
      const data = { u: 'page-url', d: 2 };
      optmlApi.sendToRestApi(data);

      expect(optmlStorage.markProcessed).toHaveBeenCalledWith('page-url', 2);
    });
  });

  describe('_sendWithFetch', () => {
    test('should send data using fetch with correct parameters', () => {
      fetchSpy.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ success: true })
      });

      const data = { u: 'test-url', d: 1 };
      const endpoint = 'https://example.com/wp-json/optimole/v1/optimizations';
      
      optmlApi._sendWithFetch(endpoint, data);

      expect(fetchSpy).toHaveBeenCalledWith(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      });
    });

    test('should log success and mark as processed on successful fetch', async () => {
      const responseData = { success: true, message: 'Data received' };
      fetchSpy.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve(responseData)
      });

      const data = { u: 'test-url', d: 1 };
      const endpoint = 'https://example.com/wp-json/optimole/v1/optimizations';
      
      await optmlApi._sendWithFetch(endpoint, data);

      // Wait for promises to resolve
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(optmlLogger.info).toHaveBeenCalledWith('Data sent successfully using fetch fallback:', responseData);
      expect(optmlStorage.markProcessed).toHaveBeenCalledWith('test-url', 1);
    });

    test('should log error when fetch response is not ok', async () => {
      fetchSpy.mockResolvedValue({
        ok: false,
        status: 404
      });

      const data = { u: 'test-url', d: 1 };
      const endpoint = 'https://example.com/wp-json/optimole/v1/optimizations';
      
      await optmlApi._sendWithFetch(endpoint, data);

      // Wait for promises to resolve
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(optmlLogger.error).toHaveBeenCalledWith('Error sending data using fetch fallback:', expect.any(Error));
    });

    test('should log error when fetch throws', async () => {
      const fetchError = new Error('Network error');
      fetchSpy.mockRejectedValue(fetchError);

      const data = { u: 'test-url', d: 1 };
      const endpoint = 'https://example.com/wp-json/optimole/v1/optimizations';
      
      await optmlApi._sendWithFetch(endpoint, data);

      // Wait for promises to resolve
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(optmlLogger.error).toHaveBeenCalledWith('Error sending data using fetch fallback:', fetchError);
    });

    test('should not mark as processed when fetch fails', async () => {
      fetchSpy.mockRejectedValue(new Error('Network error'));

      const data = { u: 'test-url', d: 1 };
      const endpoint = 'https://example.com/wp-json/optimole/v1/optimizations';
      
      await optmlApi._sendWithFetch(endpoint, data);

      // Wait for promises to resolve
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(optmlStorage.markProcessed).not.toHaveBeenCalled();
    });
  });

  describe('integration between sendToRestApi and _sendWithFetch', () => {
    test('should use fetch fallback with same data when sendBeacon fails', async () => {
      sendBeaconSpy.mockReturnValue(false);
      fetchSpy.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ success: true })
      });

      const data = { u: 'test-url', d: 1, a: [1, 2, 3] };
      optmlApi.sendToRestApi(data);

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(fetchSpy).toHaveBeenCalledWith(
        'https://example.com/wp-json/optimole/v1/optimizations',
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(data)
        })
      );
    });

    test('should eventually mark as processed when fetch succeeds after sendBeacon fails', async () => {
      sendBeaconSpy.mockReturnValue(false);
      fetchSpy.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ success: true })
      });

      const data = { u: 'test-url', d: 1 };
      optmlApi.sendToRestApi(data);

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(optmlStorage.markProcessed).toHaveBeenCalledWith('test-url', 1);
    });
  });
});

