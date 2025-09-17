# Optimole JavaScript Modules

This directory contains the modular JavaScript components for the Optimole image optimizer. 

## Module Structure

### Core Modules

#### `logger.js`
- **Purpose**: Centralized logging functionality with debug mode support
- **Exports**: `optmlLogger` object
- **Key Features**:
  - Debug mode detection via URL params or localStorage
  - Structured logging with prefixes
  - Table logging support
  - Conditional logging based on debug state

#### `storage.js`
- **Purpose**: Session storage management for tracking processed pages
- **Exports**: `optmlStorage` object
- **Key Features**:
  - Generate unique storage keys for URL/device combinations
  - Check if page/device combinations have been processed
  - Mark combinations as processed with timestamps
  - Error handling for storage operations

#### `device.js`
- **Purpose**: Device type detection based on screen width
- **Exports**: `optmlDevice` object
- **Key Features**:
  - Mobile/desktop detection using 600px breakpoint
  - Device type constants (MOBILE: 1, DESKTOP: 2)
  - Helper methods for device type checking
  - PageSpeed Insights compatible detection logic

#### `api.js`
- **Purpose**: REST API communication with fallback mechanisms
- **Exports**: `optmlApi` object
- **Key Features**:
  - Primary sendBeacon API for reliable data transmission
  - Fetch API fallback for when sendBeacon fails
  - Automatic storage marking on successful sends
  - Error handling and logging

#### `dom-utils.js`
- **Purpose**: DOM manipulation and utility functions
- **Exports**: `optmlDomUtils` object
- **Key Features**:
  - Debounce utility for performance optimization
  - Unique CSS selector generation for elements
  - Background image detection and URL extraction
  - Page condition checking (viewport, visibility, load state)
  - Promise-based waiting utilities

### Specialized Modules

#### `background.js`
- **Purpose**: Background image handling and lazy loading observation
- **Exports**: `optmlBackground` object
- **Key Features**:
  - Background image lazy loading detection
  - Mutation observer setup for class changes
  - URL extraction from background images
  - Selector processing and element observation

#### `lcp.js`
- **Purpose**: Largest Contentful Paint (LCP) element detection
- **Exports**: `optmlLcp` object
- **Key Features**:
  - Performance Observer integration
  - LCP element identification and processing
  - Support for both image and background image LCP elements
  - Timeout handling for LCP detection

#### `image-detector.js`
- **Purpose**: Image detection and dimension analysis
- **Exports**: `optmlImageDetector` object
- **Key Features**:
  - Missing dimension detection for images
  - Intersection Observer setup for above-fold detection
  - Optimole image observation (`data-opt-id` attributes)
  - Cleanup utilities for temporary attributes

#### `srcset-detector.js`
- **Purpose**: Srcset analysis and missing variation detection for eligible images
- **Exports**: `optmlSrcsetDetector` object
- **Key Features**:
  - Smart image detection with improved skip logic:
    - Includes images without `data-opt-src` (non-lazyload images)
    - Includes images with `data-opt-src` AND `data-opt-lazy-loaded` (completed lazyload)
    - Skips images with only `data-opt-src` (pending lazyload)
  - Calculates required srcset variations using comprehensive size ranges:
    - Mobile range: 200w-500w (50w steps) for dense mobile coverage
    - Tablet range: 500w-800w (100w steps) for tablet devices
    - Desktop range: 800w-1200w (200w steps) for desktop screens
    - High-res range: 1200w-1600w (200w steps) with strategic 2x DPR
  - Configurable generation settings:
    - `widthStepSize`: Step size for width variations (default: 100px)
    - `minSize`: Minimum image size to consider (default: 200px)
    - `maxVariations`: Maximum srcset variations per image (default: 8)
    - `sizeTolerance`: Tolerance for existing sizes (default: 50px)
  - Analyzes existing srcset attributes to identify missing sizes
  - **Ultra-compact API payload**: Sends only essential fields with short names (w, h, d, s, b)
  - **Full logging**: Complete analysis data available in console logs for debugging
  - Smart selection from dense size grid for optimal responsive coverage

#### `main.js`
- **Purpose**: Main orchestrator coordinating all functionality
- **Exports**: `optmlMain` object
- **Key Features**:
  - Complete above-the-fold detection workflow
  - Module coordination and data aggregation
  - API data preparation and submission
  - Error handling and condition checking
 
