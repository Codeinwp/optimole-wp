/**
 * Detects images with data-opt-id attribute that are above the fold
 * and logs them along with the current device type
 */
(function() {
  // Utility function for debouncing
  function debounce(fn, delay) {
    let timer;
    return function() {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, arguments), delay);
    };
  }

  // Create utility logger with simplified structure
  window.optmlLogger = {
    isDebug: function() {
      return new URLSearchParams(location.search).has('optml_debug') || 
             localStorage.getItem('optml_debug') !== null;
    },
    log: function(level, ...args) {
      if (this.isDebug()) console[level]('[Optimole]', ...args);
    },
    info: function(...args) { this.log('info', ...args); },
    warn: function(...args) { this.log('warn', ...args); },
    error: function(...args) { this.log('error', ...args); },
    table: function(data) {
      if (this.isDebug()) {
        console.log('[Optimole] Table:');
        console.table(data);
      }
    }
  };

  // Combined storage utilities
  const storage = {
    getKey: (url, deviceType) => `optml_pp_${url}_${deviceType}`,
    
    isProcessed: function(url, deviceType) {
      try {
        const key = this.getKey(url, deviceType);
        const storedValue = sessionStorage.getItem(key);
        
        if (!storedValue) return false;
        
        // Check if the stored timestamp is still valid (within current session)
        const timestamp = parseInt(storedValue, 10);
        const now = Date.now();
        
        // Consider it valid if it exists in the current session
        return true;
      } catch (e) {
        optmlLogger.error('Error checking sessionStorage:', e);
        return false;
      }
    },
    
    markProcessed: function(url, deviceType) {
      try {
        const key = this.getKey(url, deviceType);
        sessionStorage.setItem(key, Date.now().toString());
      } catch (e) {
        optmlLogger.error('Error setting sessionStorage:', e);
      }
    }
  };

  // Function to determine device type based on screen width
  function getDeviceType() {
    // Use 600px as the threshold between mobile and desktop
    // This is similar to what PageSpeed Insights uses
    const width = window.innerWidth;
    
    if (width <= 600) {
      optmlLogger.info('Device detected as mobile based on width:', width);
      return 1; // Mobile
    }
    
    optmlLogger.info('Device detected as desktop based on width:', width);
    return 2; // Desktop
  }

  // Function to send data to the REST API using sendBeacon
  function sendToRestApi(data) {
    // Use object destructuring for repeated property access
    const { restUrl } = optimoleDataOptimizer || {};
    
    if (!restUrl) {
      optmlLogger.error('REST API URL not available');
      return;
    }

    const endpoint = restUrl + '/optimizations';
    const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
    
    // Use sendBeacon to send the data
    const success = navigator.sendBeacon(endpoint, blob);
    
    if (success) {
      optmlLogger.info('Data sent successfully using sendBeacon');
      storage.markProcessed(data.u, data.d);
    } else {
      optmlLogger.error('Failed to send data using sendBeacon');
      
      // Fallback to fetch if sendBeacon fails
      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(responseData => {
        optmlLogger.info('Data sent successfully using fetch fallback:', responseData);
        storage.markProcessed(data.u, data.d);
      })
      .catch(error => {
        optmlLogger.error('Error sending data using fetch fallback:', error);
      });
    }
  }

  // Function to generate a unique selector for an element
  function getUniqueSelector(element) {
    if (!element || element === document.body) return 'body';
    
    // Use ID if available - fastest path
    if (element.id) {
      return `#${element.id}`;
    }
    
    const tag = element.tagName.toLowerCase();
    
    // Optimize class name processing
    let className = '';
    if (element.className && typeof element.className === 'string') {
      // Only process if needed
      if (element.className.includes('optml-bg-lazyloaded')) {
        className = '.' + element.className.trim()
          .split(/\s+/)
          .filter(cls => cls !== 'optml-bg-lazyloaded')
          .join('.');
      } else {
        // Avoid unnecessary split/filter/join when no filtering needed
        className = '.' + element.className.trim().replace(/\s+/g, '.');
      }
    }
    
    // Get parent selector - but limit recursion depth for performance
    const parentElement = element.parentElement;
    if (!parentElement || parentElement === document.body) {
      return `body > ${tag}${className}`;
    }
    
    // Optimize sibling calculation - only do this work if necessary
    let nthTypeSelector = '';
    const siblings = parentElement.children;
    let siblingCount = 0;
    let position = 0;
    
    for (let i = 0; i < siblings.length; i++) {
      if (siblings[i].tagName === element.tagName) {
        siblingCount++;
        if (siblings[i] === element) {
          position = siblingCount;
        }
      }
    }
    
    if (siblingCount > 1) {
      nthTypeSelector = `:nth-of-type(${position})`;
    }
    
    // Limit recursion depth to avoid performance issues with deeply nested DOM
    // Use a simpler parent selector if we're already several levels deep
    const parentSelector = parentElement.id ? 
      `#${parentElement.id}` : 
      getUniqueSelector(parentElement);
    
    return `${parentSelector} > ${tag}${className}${nthTypeSelector}`;
  }

  // Function to check if an element has a background image
  function hasBackgroundImage(element, returnUrl = false) {
    // Use getComputedStyle for accurate results, but only once per element
    const style = window.getComputedStyle(element);
    const bgImage = style.backgroundImage;
    
    // Check if the background image is a URL (not 'none')
    return ( bgImage && bgImage !== 'none' && bgImage.includes('url(') ) ? (returnUrl ? bgImage : true) : false;
  }

  // More efficient way to handle background image elements
  function setupBackgroundImageObservation(elements, selector, selectorMap, observer) {
    // Create a single shared observer for all elements
    const classObserver = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const element = mutation.target;
          
          // Check if element now has the required class and a background image
          if (element.classList.contains('optml-bg-lazyloaded') && hasBackgroundImage(element)) {
            // Start observing for visibility
            observer.observe(element);
            
            // Stop watching for class changes on this element
            classObserver.disconnect(element);
            
            const specificSelector = element.getAttribute('data-optml-specific-selector');
            optmlLogger.info(`Background element "${specificSelector}" is now observable`);
          }
        }
      }
    });
    
    // Process all elements at once
    const elementsToWatch = [];
    
    elements.forEach(element => {
      // Generate a specific selector for this element
      const specificSelector = getUniqueSelector(element);
      
      // Mark the element with its selectors for identification in the observer
      element.setAttribute('data-optml-bg-selector', selector);
      element.setAttribute('data-optml-specific-selector', specificSelector);
      
      // If already lazyloaded with background image, observe immediately
      if (element.classList.contains('optml-bg-lazyloaded') && hasBackgroundImage(element)) {
        observer.observe(element);
      } else {
        // Otherwise, add to the list to watch for class changes
        elementsToWatch.push(element);
      }
    });
    
    // Set up observation for class changes only on elements that need it
    if (elementsToWatch.length > 0) {
      elementsToWatch.forEach(element => {
        classObserver.observe(element, { attributes: true, attributeFilter: ['class'] });
      });
      
      // Set a timeout to disconnect the observer after a reasonable time
      setTimeout(() => {
        classObserver.disconnect();
        optmlLogger.info(`Stopped waiting for lazyload on ${selector} elements`);
      }, 5000);
    }
    
    return elementsToWatch.length;
  }

  // Function to find and log all above-the-fold images with data-opt-id
  async function findAboveTheFoldImages() {
    // Check for zero-dimension viewports and hidden pages
    if (window.innerWidth === 0 || window.innerHeight === 0) {
      optmlLogger.info('Window must have non-zero dimensions for image detection.');
      return;
    }

    if (document.visibilityState === 'hidden' && !document.prerendering) {
      optmlLogger.info('Page opened in background tab so image detection is not performed.');
      return;
    }

    // Use object destructuring for repeated property access
    const { pageProfileId, missingDevices, bgSelectors } = optimoleDataOptimizer || {};
    const deviceType = getDeviceType();
    const url = pageProfileId;
    const missingDevicesArray = missingDevices ? missingDevices.split(',') : [];
    
    optmlLogger.info('Device Type:', deviceType);
    optmlLogger.info('Missing Devices:', missingDevicesArray);
    optmlLogger.info('Profile ID:', pageProfileId);
    optmlLogger.info('Background Selectors:', bgSelectors || 'None provided');
    
    // Check if this device type is needed
    if (!missingDevicesArray.includes(deviceType.toString())) {
      optmlLogger.info('Skipping device type, data already exists:', deviceType);
      return;
    }
    
    // Check if we've already processed this device/URL combination
    if (storage.isProcessed(url, deviceType)) {
      optmlLogger.info('Skipping detection, already processed this device/URL combination');
      return;
    }

    // Wait until the resources on the page have fully loaded
    if (document.readyState !== 'complete') {
      optmlLogger.info('Waiting for page to fully load...');
      await new Promise(resolve => {
        window.addEventListener('load', resolve, { once: true });
      });
      optmlLogger.info('Page fully loaded, proceeding with detection');
    }

    // Wait for browser idle time to run detection
    await new Promise(resolve => {
      if (typeof requestIdleCallback === 'function') {
        requestIdleCallback(resolve);
      } else {
        setTimeout(resolve, 200);
      }
    });

    // Track LCP element - use a single object to store all LCP data
    let lcpData = {
      element: null,
      imageId: null,
      bgSelector: null,
      bgUrls: null
    };

    // Set up LCP detection - more performant approach
    if (PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {
      // Use a pre-existing LCP entry if available instead of waiting
      const lcpEntries = performance.getEntriesByType('largest-contentful-paint');
      
      if (lcpEntries && lcpEntries.length > 0) {
        // Use the most recent LCP entry
        const lastEntry = lcpEntries[lcpEntries.length - 1];
        if (lastEntry && lastEntry.element) {
          lcpData.element = lastEntry.element;
          optmlLogger.info('LCP element found from existing entries:', lcpData.element);
          processLcpElement(lcpData.element);
        }
      } else {
        // If no existing entries, set up observer with shorter timeout
        optmlLogger.info('Setting up LCP observer');
        
        // Create a promise that will resolve when LCP is detected or timeout
        await new Promise(resolve => {
          const lcpObserver = new PerformanceObserver(entryList => {
            const entries = entryList.getEntries();
            if (entries.length === 0) return;
            
            // Use the most recent entry
            const lastEntry = entries[entries.length - 1];
            if (lastEntry && lastEntry.element) {
              lcpData.element = lastEntry.element;
              optmlLogger.info('LCP element detected:', lcpData.element);
              processLcpElement(lcpData.element);
            }
            
            lcpObserver.disconnect();
            resolve();
          });
          
          lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
          
          // Use a shorter timeout - most LCP elements are detected within 1-2 seconds
          setTimeout(() => {
            lcpObserver.disconnect();
            resolve();
          }, 1500);
        });
      }
    } else {
      optmlLogger.info('LCP detection not supported in this browser');
    }
    
    // Helper function to process LCP element - avoids code duplication
    function processLcpElement(element) {
      if (!element) return;
      
      // Check if LCP element is an image with data-opt-id
      if (element.tagName === 'IMG') {
        const id = element.getAttribute('data-opt-id');
        if (id) {
          lcpData.imageId = parseInt(id, 10);
          optmlLogger.info('LCP element is an Optimole image with ID:', lcpData.imageId);
        }
      } 
      // Check if LCP element has a background image
      else {
        const bgImage = hasBackgroundImage(element, true);
        if (bgImage !== false ) {
          lcpData.bgSelector = getUniqueSelector(element);
          lcpData.bgUrls = extractUrlsFromBgImage(bgImage);
          optmlLogger.info('LCP element has background image:', lcpData.bgSelector, lcpData.bgUrls);
        }
      }
    }

    // Track page visibility and window resize
    let isPageVisible = document.visibilityState !== 'hidden';
    let didWindowResize = false;
    
    // Set up debounced resize handler
    const resizeHandler = debounce(() => {
      didWindowResize = true;
      optmlLogger.info('Window resized during detection, results may be affected');
    }, 100);
    
    // Set up visibility change handler
    const visibilityChangeHandler = () => {
      isPageVisible = document.visibilityState !== 'hidden';
      optmlLogger.info('Page visibility changed:', isPageVisible ? 'visible' : 'hidden');
    };
    
    // Add event listeners with passive option for better performance
    window.addEventListener('resize', resizeHandler, { passive: true });
    document.addEventListener('visibilitychange', visibilityChangeHandler);

    // Use IntersectionObserver instead of getBoundingClientRect for better performance
    const aboveTheFoldImages = [];
    const observedElements = new Map();
    
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const element = entry.target;
          
          // Handle img elements with data-opt-id
          if (element.tagName === 'IMG') {
            const id = parseInt(element.getAttribute('data-opt-id'), 10);
            if (!isNaN(id) && !aboveTheFoldImages.includes(id)) {
              aboveTheFoldImages.push(id);
            }
          } 
          // Handle background image elements
          else if (element.hasAttribute('data-optml-bg-selector')) {
            const baseSelector = element.getAttribute('data-optml-bg-selector');
            const specificSelector = element.getAttribute('data-optml-specific-selector');
            
            if (baseSelector && specificSelector && selectorMap.has(baseSelector)) {
              // Add this specific selector to the above-fold list for this base selector
              const aboveTheFoldSelectors = selectorMap.get(baseSelector);
              if (!aboveTheFoldSelectors.includes(specificSelector)) {
                aboveTheFoldSelectors.push(specificSelector);
                optmlLogger.info(`Element with selector "${specificSelector}" is above the fold`);
              }
            }
          }
        }
      });
    }, {
      threshold: 0.1 // Consider element visible when 10% is in viewport
    });
    
    // Observe all images with data-opt-id
    document.querySelectorAll('img[data-opt-id]').forEach(img => {
      const id = parseInt(img.getAttribute('data-opt-id'), 10);
      if (isNaN(id)) {
        optmlLogger.warn('Invalid data-opt-id:', img.getAttribute('data-opt-id'));
        return;
      }
      observedElements.set(img, id);
      observer.observe(img);
    });
    
    // Track which selectors are present on the page and their specific selectors
    const selectorMap = new Map(); // Maps base selector -> array of specific selectors for above-fold elements
    
    // Create a Map to store background image URLs for each selector
    const bgImageUrls = new Map(); // Maps base selector -> Map of (specific selector -> URL)
    
    // Process background image selectors if available
    if (bgSelectors && Array.isArray(bgSelectors) && bgSelectors.length > 0) {
      optmlLogger.info('Processing background selectors:', bgSelectors);
      
      let pendingElements = 0;
      
      bgSelectors.forEach(selector => {
        try {
          const elements = document.querySelectorAll(selector);
          if (elements.length === 0) {
            optmlLogger.warn('No elements found for background selector:', selector);
            return;
          }
          
          // Initialize this selector with an empty array for above-fold elements
          selectorMap.set(selector, []);
          
          // Setup observation for these elements
          pendingElements += setupBackgroundImageObservation(
            Array.from(elements), 
            selector, 
            selectorMap, 
            observer
          );
          
          optmlLogger.info(`Processed ${elements.length} elements for background selector: ${selector}`);
        } catch (e) {
          optmlLogger.error('Error processing background selector:', selector, e);
        }
      });
      
      // Extract background image URLs for elements with the selectors
      bgSelectors.forEach(selector => {
        const selectorUrlMap = new Map();
        bgImageUrls.set(selector, selectorUrlMap);
        
        document.querySelectorAll(selector).forEach(element => {
          if (element.classList.contains('optml-bg-lazyloaded')) {
            const bgImage = hasBackgroundImage(element, true);
            if(bgImage === false) return;
            
            // Get the specific selector for this element
            const specificSelector = getUniqueSelector(element);
            if (!specificSelector) return;
            
            // Extract all URLs from the background-image property
            const urls = [];
            const regex = /url\(['"]?(.*?)['"]?\)/g;
            let match;
            
            while ((match = regex.exec(bgImage)) !== null) {
              if (match[1]) urls.push(match[1]);
            }
            
            if (urls.length > 0) {
              // Store the first URL or all URLs depending on your requirements
              selectorUrlMap.set(specificSelector, urls); // or store all: urls
              optmlLogger.info(`Found background image URL(s) for "${specificSelector}":`, urls);
            }
          }
        });
      });
      
      // Adjust wait time based on whether we have pending elements
      const waitTime = pendingElements > 0 ? 600 : 300;
      optmlLogger.info(`Waiting ${waitTime}ms for ${pendingElements} pending background elements`);
      await new Promise(resolve => setTimeout(resolve, waitTime));
    } else {
      // Standard wait time if no background selectors
      await new Promise(resolve => setTimeout(resolve, 300));
    }
    
    // Disconnect observer and clean up event listeners
    observer.disconnect();
    window.removeEventListener('resize', resizeHandler);
    document.removeEventListener('visibilitychange', visibilityChangeHandler);
    
    // After observation is complete, process below-the-fold elements
    document.querySelectorAll('[data-optml-bg-selector]').forEach(element => { 
      // Remove temporary data attributes
      element.removeAttribute('data-optml-bg-selector');
      element.removeAttribute('data-optml-specific-selector');
    });
    
    // Check conditions that might affect accuracy
    if (didWindowResize) {
      optmlLogger.warn('Window was resized during detection, results may not be accurate');
    }
    
    if (!isPageVisible) {
      optmlLogger.warn('Page became hidden during detection, results may not be accurate');
    }
     
    // Log results
    optmlLogger.info('Above the fold images with data-opt-id:', aboveTheFoldImages);
    optmlLogger.info('Background selectors:', selectorMap);
    
    // Prepare and send data if we found any images or background selectors
    if (aboveTheFoldImages.length > 0 || selectorMap.size > 0 || lcpData.imageId || lcpData.bgSelector) {
      // Convert the Map to a plain object for the API
      const processedBgSelectors = {};
      
      // Process each selector that's present on the page
      selectorMap.forEach((specificSelectors, baseSelector) => {
        // Initialize the object for this base selector
        processedBgSelectors[baseSelector] = {};
        
        // For each specific selector, add its URLs if available
        specificSelectors.forEach(specificSelector => {
          // First, add the selector to indicate it's above the fold
          processedBgSelectors[baseSelector][specificSelector] = null;
          
          // Then, if we have URLs for this selector, add them
          if (bgImageUrls.has(baseSelector) && 
              bgImageUrls.get(baseSelector).has(specificSelector)) { 
            processedBgSelectors[baseSelector][specificSelector] = 
              bgImageUrls.get(baseSelector).get(specificSelector);
          }
        });
      });
      
      // Prepare the data object with LCP information using shorter key names
      const data = {
        d: deviceType,
        a: aboveTheFoldImages,
        b: processedBgSelectors,
        u: url,
        t: optimoleDataOptimizer._t,
        h: optimoleDataOptimizer.hmac,
        l: { 
          i: lcpData.imageId,
          s: lcpData.bgSelector, 
          u: lcpData.bgUrls   
        }
      };
      
      optmlLogger.info('Sending data with LCP information:', { 
        lcpImageId: lcpData.imageId, 
        lcpBgSelector: lcpData.bgSelector,
        lcpBgUrls: lcpData.bgUrls
      });
      optmlLogger.info('Sending background selectors:', processedBgSelectors);
      
      sendToRestApi(data);
      return data;
    } else {
      optmlLogger.info('No above-the-fold images, background elements, or LCP elements found');
      return null;
    }
  }

  // Helper function to extract URLs from background-image CSS property
  function extractUrlsFromBgImage(bgImage) {
    if (!bgImage) return null;
    
    const urls = [];
    const regex = /url\(['"]?(.*?)['"]?\)/g;
    let match;
    
    while ((match = regex.exec(bgImage)) !== null) {
      if (match[1]) urls.push(match[1]);
    }
    
    return urls.length > 0 ? urls : null;
  }

  // Ensure the DOM is loaded before running detection
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', findAboveTheFoldImages);
  } else {
    findAboveTheFoldImages();
  }
})();
