/**
 * Tests for Optimole Logger Module
 */

import { optmlLogger } from '../logger.js';

describe('optmlLogger', () => {
  let consoleInfoSpy;
  let consoleWarnSpy;
  let consoleErrorSpy;
  let consoleLogSpy;
  let consoleTableSpy;

  beforeEach(() => {
    // Mock console methods
    consoleInfoSpy = jest.spyOn(console, 'info').mockImplementation();
    consoleWarnSpy = jest.spyOn(console, 'warn').mockImplementation();
    consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
    consoleLogSpy = jest.spyOn(console, 'log').mockImplementation();
    consoleTableSpy = jest.spyOn(console, 'table').mockImplementation();

    // Clear localStorage and URL params
    localStorage.clear();
    delete window.location;
    window.location = { search: '' };
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  describe('isDebug', () => {
    test('should return false when debug is not enabled', () => {
      expect(optmlLogger.isDebug()).toBe(false);
    });

    test('should return true when optml_debug is in URL params', () => {
      window.location = { search: '?optml_debug' };
      expect(optmlLogger.isDebug()).toBe(true);
    });

    test('should return true when optml_debug is in localStorage', () => {
      localStorage.setItem('optml_debug', '1');
      expect(optmlLogger.isDebug()).toBe(true);
    });

    test('should return true when both URL and localStorage have debug flag', () => {
      window.location = { search: '?optml_debug' };
      localStorage.setItem('optml_debug', '1');
      expect(optmlLogger.isDebug()).toBe(true);
    });
  });

  describe('log', () => {
    test('should not log when debug is disabled', () => {
      optmlLogger.log('info', 'test message');
      expect(consoleInfoSpy).not.toHaveBeenCalled();
    });

    test('should log info when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.log('info', 'test message');
      expect(consoleInfoSpy).toHaveBeenCalledWith('[Optimole]', 'test message');
    });

    test('should log warn when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.log('warn', 'warning message');
      expect(consoleWarnSpy).toHaveBeenCalledWith('[Optimole]', 'warning message');
    });

    test('should log error when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.log('error', 'error message');
      expect(consoleErrorSpy).toHaveBeenCalledWith('[Optimole]', 'error message');
    });

    test('should handle multiple arguments', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.log('info', 'message', 'arg1', 'arg2', { key: 'value' });
      expect(consoleInfoSpy).toHaveBeenCalledWith('[Optimole]', 'message', 'arg1', 'arg2', { key: 'value' });
    });
  });

  describe('info', () => {
    test('should not log when debug is disabled', () => {
      optmlLogger.info('test message');
      expect(consoleInfoSpy).not.toHaveBeenCalled();
    });

    test('should log info message when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.info('test message');
      expect(consoleInfoSpy).toHaveBeenCalledWith('[Optimole]', 'test message');
    });

    test('should handle multiple arguments', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.info('message', { data: 'value' }, [1, 2, 3]);
      expect(consoleInfoSpy).toHaveBeenCalledWith('[Optimole]', 'message', { data: 'value' }, [1, 2, 3]);
    });
  });

  describe('warn', () => {
    test('should not log when debug is disabled', () => {
      optmlLogger.warn('warning message');
      expect(consoleWarnSpy).not.toHaveBeenCalled();
    });

    test('should log warning message when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.warn('warning message');
      expect(consoleWarnSpy).toHaveBeenCalledWith('[Optimole]', 'warning message');
    });
  });

  describe('error', () => {
    test('should not log when debug is disabled', () => {
      optmlLogger.error('error message');
      expect(consoleErrorSpy).not.toHaveBeenCalled();
    });

    test('should log error message when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      optmlLogger.error('error message');
      expect(consoleErrorSpy).toHaveBeenCalledWith('[Optimole]', 'error message');
    });

    test('should handle Error objects', () => {
      localStorage.setItem('optml_debug', '1');
      const error = new Error('Test error');
      optmlLogger.error('An error occurred:', error);
      expect(consoleErrorSpy).toHaveBeenCalledWith('[Optimole]', 'An error occurred:', error);
    });
  });

  describe('table', () => {
    test('should not log table when debug is disabled', () => {
      const data = [{ id: 1, name: 'Test' }];
      optmlLogger.table(data);
      expect(consoleLogSpy).not.toHaveBeenCalled();
      expect(consoleTableSpy).not.toHaveBeenCalled();
    });

    test('should log table when debug is enabled', () => {
      localStorage.setItem('optml_debug', '1');
      const data = [{ id: 1, name: 'Test' }];
      optmlLogger.table(data);
      expect(consoleLogSpy).toHaveBeenCalledWith('[Optimole] Table:');
      expect(consoleTableSpy).toHaveBeenCalledWith(data);
    });

    test('should handle object data', () => {
      localStorage.setItem('optml_debug', '1');
      const data = { key1: 'value1', key2: 'value2' };
      optmlLogger.table(data);
      expect(consoleTableSpy).toHaveBeenCalledWith(data);
    });

    test('should handle array data', () => {
      localStorage.setItem('optml_debug', '1');
      const data = ['item1', 'item2', 'item3'];
      optmlLogger.table(data);
      expect(consoleTableSpy).toHaveBeenCalledWith(data);
    });
  });
});

