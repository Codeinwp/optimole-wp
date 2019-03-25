/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 7);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function() {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		var result = [];
		for(var i = 0; i < this.length; i++) {
			var item = this[i];
			if(item[2]) {
				result.push("@media " + item[2] + "{" + item[1] + "}");
			} else {
				result.push(item[1]);
			}
		}
		return result.join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};


/***/ }),
/* 1 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
var stylesInDom = {},
	memoize = function(fn) {
		var memo;
		return function () {
			if (typeof memo === "undefined") memo = fn.apply(this, arguments);
			return memo;
		};
	},
	isOldIE = memoize(function() {
		return /msie [6-9]\b/.test(self.navigator.userAgent.toLowerCase());
	}),
	getHeadElement = memoize(function () {
		return document.head || document.getElementsByTagName("head")[0];
	}),
	singletonElement = null,
	singletonCounter = 0,
	styleElementsInsertedAtTop = [];

module.exports = function(list, options) {
	if(typeof DEBUG !== "undefined" && DEBUG) {
		if(typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};
	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (typeof options.singleton === "undefined") options.singleton = isOldIE();

	// By default, add <style> tags to the bottom of <head>.
	if (typeof options.insertAt === "undefined") options.insertAt = "bottom";

	var styles = listToStyles(list);
	addStylesToDom(styles, options);

	return function update(newList) {
		var mayRemove = [];
		for(var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];
			domStyle.refs--;
			mayRemove.push(domStyle);
		}
		if(newList) {
			var newStyles = listToStyles(newList);
			addStylesToDom(newStyles, options);
		}
		for(var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];
			if(domStyle.refs === 0) {
				for(var j = 0; j < domStyle.parts.length; j++)
					domStyle.parts[j]();
				delete stylesInDom[domStyle.id];
			}
		}
	};
}

function addStylesToDom(styles, options) {
	for(var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];
		if(domStyle) {
			domStyle.refs++;
			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}
			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];
			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}
			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles(list) {
	var styles = [];
	var newStyles = {};
	for(var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};
		if(!newStyles[id])
			styles.push(newStyles[id] = {id: id, parts: [part]});
		else
			newStyles[id].parts.push(part);
	}
	return styles;
}

function insertStyleElement(options, styleElement) {
	var head = getHeadElement();
	var lastStyleElementInsertedAtTop = styleElementsInsertedAtTop[styleElementsInsertedAtTop.length - 1];
	if (options.insertAt === "top") {
		if(!lastStyleElementInsertedAtTop) {
			head.insertBefore(styleElement, head.firstChild);
		} else if(lastStyleElementInsertedAtTop.nextSibling) {
			head.insertBefore(styleElement, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			head.appendChild(styleElement);
		}
		styleElementsInsertedAtTop.push(styleElement);
	} else if (options.insertAt === "bottom") {
		head.appendChild(styleElement);
	} else {
		throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
	}
}

function removeStyleElement(styleElement) {
	styleElement.parentNode.removeChild(styleElement);
	var idx = styleElementsInsertedAtTop.indexOf(styleElement);
	if(idx >= 0) {
		styleElementsInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement(options) {
	var styleElement = document.createElement("style");
	styleElement.type = "text/css";
	insertStyleElement(options, styleElement);
	return styleElement;
}

function createLinkElement(options) {
	var linkElement = document.createElement("link");
	linkElement.rel = "stylesheet";
	insertStyleElement(options, linkElement);
	return linkElement;
}

function addStyle(obj, options) {
	var styleElement, update, remove;

	if (options.singleton) {
		var styleIndex = singletonCounter++;
		styleElement = singletonElement || (singletonElement = createStyleElement(options));
		update = applyToSingletonTag.bind(null, styleElement, styleIndex, false);
		remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true);
	} else if(obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function") {
		styleElement = createLinkElement(options);
		update = updateLink.bind(null, styleElement);
		remove = function() {
			removeStyleElement(styleElement);
			if(styleElement.href)
				URL.revokeObjectURL(styleElement.href);
		};
	} else {
		styleElement = createStyleElement(options);
		update = applyToTag.bind(null, styleElement);
		remove = function() {
			removeStyleElement(styleElement);
		};
	}

	update(obj);

	return function updateStyle(newObj) {
		if(newObj) {
			if(newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap)
				return;
			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;
		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag(styleElement, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (styleElement.styleSheet) {
		styleElement.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = styleElement.childNodes;
		if (childNodes[index]) styleElement.removeChild(childNodes[index]);
		if (childNodes.length) {
			styleElement.insertBefore(cssNode, childNodes[index]);
		} else {
			styleElement.appendChild(cssNode);
		}
	}
}

function applyToTag(styleElement, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		styleElement.setAttribute("media", media)
	}

	if(styleElement.styleSheet) {
		styleElement.styleSheet.cssText = css;
	} else {
		while(styleElement.firstChild) {
			styleElement.removeChild(styleElement.firstChild);
		}
		styleElement.appendChild(document.createTextNode(css));
	}
}

function updateLink(linkElement, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	if(sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = linkElement.href;

	linkElement.href = URL.createObjectURL(blob);

	if(oldSrc)
		URL.revokeObjectURL(oldSrc);
}


/***/ }),
/* 2 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1,eval)("this");
} catch(e) {
	// This works if the window reference is available
	if(typeof window === "object")
		g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 3 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(process, global, setImmediate) {/*!
 * Vue.js v2.6.6
 * (c) 2014-2019 Evan You
 * Released under the MIT License.
 */
/*  */

var emptyObject = Object.freeze({});

// These helpers produce better VM code in JS engines due to their
// explicitness and function inlining.
function isUndef (v) {
  return v === undefined || v === null
}

function isDef (v) {
  return v !== undefined && v !== null
}

function isTrue (v) {
  return v === true
}

function isFalse (v) {
  return v === false
}

/**
 * Check if value is primitive.
 */
function isPrimitive (value) {
  return (
    typeof value === 'string' ||
    typeof value === 'number' ||
    // $flow-disable-line
    typeof value === 'symbol' ||
    typeof value === 'boolean'
  )
}

/**
 * Quick object check - this is primarily used to tell
 * Objects from primitive values when we know the value
 * is a JSON-compliant type.
 */
function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

/**
 * Get the raw type string of a value, e.g., [object Object].
 */
var _toString = Object.prototype.toString;

function toRawType (value) {
  return _toString.call(value).slice(8, -1)
}

/**
 * Strict object type check. Only returns true
 * for plain JavaScript objects.
 */
function isPlainObject (obj) {
  return _toString.call(obj) === '[object Object]'
}

function isRegExp (v) {
  return _toString.call(v) === '[object RegExp]'
}

/**
 * Check if val is a valid array index.
 */
function isValidArrayIndex (val) {
  var n = parseFloat(String(val));
  return n >= 0 && Math.floor(n) === n && isFinite(val)
}

function isPromise (val) {
  return (
    isDef(val) &&
    typeof val.then === 'function' &&
    typeof val.catch === 'function'
  )
}

/**
 * Convert a value to a string that is actually rendered.
 */
function toString (val) {
  return val == null
    ? ''
    : Array.isArray(val) || (isPlainObject(val) && val.toString === _toString)
      ? JSON.stringify(val, null, 2)
      : String(val)
}

/**
 * Convert an input value to a number for persistence.
 * If the conversion fails, return original string.
 */
function toNumber (val) {
  var n = parseFloat(val);
  return isNaN(n) ? val : n
}

/**
 * Make a map and return a function for checking if a key
 * is in that map.
 */
function makeMap (
  str,
  expectsLowerCase
) {
  var map = Object.create(null);
  var list = str.split(',');
  for (var i = 0; i < list.length; i++) {
    map[list[i]] = true;
  }
  return expectsLowerCase
    ? function (val) { return map[val.toLowerCase()]; }
    : function (val) { return map[val]; }
}

/**
 * Check if a tag is a built-in tag.
 */
var isBuiltInTag = makeMap('slot,component', true);

/**
 * Check if an attribute is a reserved attribute.
 */
var isReservedAttribute = makeMap('key,ref,slot,slot-scope,is');

/**
 * Remove an item from an array.
 */
function remove (arr, item) {
  if (arr.length) {
    var index = arr.indexOf(item);
    if (index > -1) {
      return arr.splice(index, 1)
    }
  }
}

/**
 * Check whether an object has the property.
 */
var hasOwnProperty = Object.prototype.hasOwnProperty;
function hasOwn (obj, key) {
  return hasOwnProperty.call(obj, key)
}

/**
 * Create a cached version of a pure function.
 */
function cached (fn) {
  var cache = Object.create(null);
  return (function cachedFn (str) {
    var hit = cache[str];
    return hit || (cache[str] = fn(str))
  })
}

/**
 * Camelize a hyphen-delimited string.
 */
var camelizeRE = /-(\w)/g;
var camelize = cached(function (str) {
  return str.replace(camelizeRE, function (_, c) { return c ? c.toUpperCase() : ''; })
});

/**
 * Capitalize a string.
 */
var capitalize = cached(function (str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
});

/**
 * Hyphenate a camelCase string.
 */
var hyphenateRE = /\B([A-Z])/g;
var hyphenate = cached(function (str) {
  return str.replace(hyphenateRE, '-$1').toLowerCase()
});

/**
 * Simple bind polyfill for environments that do not support it,
 * e.g., PhantomJS 1.x. Technically, we don't need this anymore
 * since native bind is now performant enough in most browsers.
 * But removing it would mean breaking code that was able to run in
 * PhantomJS 1.x, so this must be kept for backward compatibility.
 */

/* istanbul ignore next */
function polyfillBind (fn, ctx) {
  function boundFn (a) {
    var l = arguments.length;
    return l
      ? l > 1
        ? fn.apply(ctx, arguments)
        : fn.call(ctx, a)
      : fn.call(ctx)
  }

  boundFn._length = fn.length;
  return boundFn
}

function nativeBind (fn, ctx) {
  return fn.bind(ctx)
}

var bind = Function.prototype.bind
  ? nativeBind
  : polyfillBind;

/**
 * Convert an Array-like object to a real Array.
 */
function toArray (list, start) {
  start = start || 0;
  var i = list.length - start;
  var ret = new Array(i);
  while (i--) {
    ret[i] = list[i + start];
  }
  return ret
}

/**
 * Mix properties into target object.
 */
function extend (to, _from) {
  for (var key in _from) {
    to[key] = _from[key];
  }
  return to
}

/**
 * Merge an Array of Objects into a single Object.
 */
function toObject (arr) {
  var res = {};
  for (var i = 0; i < arr.length; i++) {
    if (arr[i]) {
      extend(res, arr[i]);
    }
  }
  return res
}

/* eslint-disable no-unused-vars */

/**
 * Perform no operation.
 * Stubbing args to make Flow happy without leaving useless transpiled code
 * with ...rest (https://flow.org/blog/2017/05/07/Strict-Function-Call-Arity/).
 */
function noop (a, b, c) {}

/**
 * Always return false.
 */
var no = function (a, b, c) { return false; };

/* eslint-enable no-unused-vars */

/**
 * Return the same value.
 */
var identity = function (_) { return _; };

/**
 * Generate a string containing static keys from compiler modules.
 */
function genStaticKeys (modules) {
  return modules.reduce(function (keys, m) {
    return keys.concat(m.staticKeys || [])
  }, []).join(',')
}

/**
 * Check if two values are loosely equal - that is,
 * if they are plain objects, do they have the same shape?
 */
function looseEqual (a, b) {
  if (a === b) { return true }
  var isObjectA = isObject(a);
  var isObjectB = isObject(b);
  if (isObjectA && isObjectB) {
    try {
      var isArrayA = Array.isArray(a);
      var isArrayB = Array.isArray(b);
      if (isArrayA && isArrayB) {
        return a.length === b.length && a.every(function (e, i) {
          return looseEqual(e, b[i])
        })
      } else if (a instanceof Date && b instanceof Date) {
        return a.getTime() === b.getTime()
      } else if (!isArrayA && !isArrayB) {
        var keysA = Object.keys(a);
        var keysB = Object.keys(b);
        return keysA.length === keysB.length && keysA.every(function (key) {
          return looseEqual(a[key], b[key])
        })
      } else {
        /* istanbul ignore next */
        return false
      }
    } catch (e) {
      /* istanbul ignore next */
      return false
    }
  } else if (!isObjectA && !isObjectB) {
    return String(a) === String(b)
  } else {
    return false
  }
}

/**
 * Return the first index at which a loosely equal value can be
 * found in the array (if value is a plain object, the array must
 * contain an object of the same shape), or -1 if it is not present.
 */
function looseIndexOf (arr, val) {
  for (var i = 0; i < arr.length; i++) {
    if (looseEqual(arr[i], val)) { return i }
  }
  return -1
}

/**
 * Ensure a function is called only once.
 */
function once (fn) {
  var called = false;
  return function () {
    if (!called) {
      called = true;
      fn.apply(this, arguments);
    }
  }
}

var SSR_ATTR = 'data-server-rendered';

var ASSET_TYPES = [
  'component',
  'directive',
  'filter'
];

var LIFECYCLE_HOOKS = [
  'beforeCreate',
  'created',
  'beforeMount',
  'mounted',
  'beforeUpdate',
  'updated',
  'beforeDestroy',
  'destroyed',
  'activated',
  'deactivated',
  'errorCaptured',
  'serverPrefetch'
];

/*  */



var config = ({
  /**
   * Option merge strategies (used in core/util/options)
   */
  // $flow-disable-line
  optionMergeStrategies: Object.create(null),

  /**
   * Whether to suppress warnings.
   */
  silent: false,

  /**
   * Show production mode tip message on boot?
   */
  productionTip: process.env.NODE_ENV !== 'production',

  /**
   * Whether to enable devtools
   */
  devtools: process.env.NODE_ENV !== 'production',

  /**
   * Whether to record perf
   */
  performance: false,

  /**
   * Error handler for watcher errors
   */
  errorHandler: null,

  /**
   * Warn handler for watcher warns
   */
  warnHandler: null,

  /**
   * Ignore certain custom elements
   */
  ignoredElements: [],

  /**
   * Custom user key aliases for v-on
   */
  // $flow-disable-line
  keyCodes: Object.create(null),

  /**
   * Check if a tag is reserved so that it cannot be registered as a
   * component. This is platform-dependent and may be overwritten.
   */
  isReservedTag: no,

  /**
   * Check if an attribute is reserved so that it cannot be used as a component
   * prop. This is platform-dependent and may be overwritten.
   */
  isReservedAttr: no,

  /**
   * Check if a tag is an unknown element.
   * Platform-dependent.
   */
  isUnknownElement: no,

  /**
   * Get the namespace of an element
   */
  getTagNamespace: noop,

  /**
   * Parse the real tag name for the specific platform.
   */
  parsePlatformTagName: identity,

  /**
   * Check if an attribute must be bound using property, e.g. value
   * Platform-dependent.
   */
  mustUseProp: no,

  /**
   * Perform updates asynchronously. Intended to be used by Vue Test Utils
   * This will significantly reduce performance if set to false.
   */
  async: true,

  /**
   * Exposed for legacy reasons
   */
  _lifecycleHooks: LIFECYCLE_HOOKS
});

/*  */

/**
 * unicode letters used for parsing html tags, component names and property paths.
 * using https://www.w3.org/TR/html53/semantics-scripting.html#potentialcustomelementname
 * skipping \u10000-\uEFFFF due to it freezing up PhantomJS
 */
var unicodeLetters = 'a-zA-Z\u00B7\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u037D\u037F-\u1FFF\u200C-\u200D\u203F-\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD';

/**
 * Check if a string starts with $ or _
 */
function isReserved (str) {
  var c = (str + '').charCodeAt(0);
  return c === 0x24 || c === 0x5F
}

/**
 * Define a property.
 */
function def (obj, key, val, enumerable) {
  Object.defineProperty(obj, key, {
    value: val,
    enumerable: !!enumerable,
    writable: true,
    configurable: true
  });
}

/**
 * Parse simple path.
 */
var bailRE = new RegExp(("[^" + unicodeLetters + ".$_\\d]"));
function parsePath (path) {
  if (bailRE.test(path)) {
    return
  }
  var segments = path.split('.');
  return function (obj) {
    for (var i = 0; i < segments.length; i++) {
      if (!obj) { return }
      obj = obj[segments[i]];
    }
    return obj
  }
}

/*  */

// can we use __proto__?
var hasProto = '__proto__' in {};

// Browser environment sniffing
var inBrowser = typeof window !== 'undefined';
var inWeex = typeof WXEnvironment !== 'undefined' && !!WXEnvironment.platform;
var weexPlatform = inWeex && WXEnvironment.platform.toLowerCase();
var UA = inBrowser && window.navigator.userAgent.toLowerCase();
var isIE = UA && /msie|trident/.test(UA);
var isIE9 = UA && UA.indexOf('msie 9.0') > 0;
var isEdge = UA && UA.indexOf('edge/') > 0;
var isAndroid = (UA && UA.indexOf('android') > 0) || (weexPlatform === 'android');
var isIOS = (UA && /iphone|ipad|ipod|ios/.test(UA)) || (weexPlatform === 'ios');
var isChrome = UA && /chrome\/\d+/.test(UA) && !isEdge;
var isPhantomJS = UA && /phantomjs/.test(UA);
var isFF = UA && UA.match(/firefox\/(\d+)/);

// Firefox has a "watch" function on Object.prototype...
var nativeWatch = ({}).watch;

var supportsPassive = false;
if (inBrowser) {
  try {
    var opts = {};
    Object.defineProperty(opts, 'passive', ({
      get: function get () {
        /* istanbul ignore next */
        supportsPassive = true;
      }
    })); // https://github.com/facebook/flow/issues/285
    window.addEventListener('test-passive', null, opts);
  } catch (e) {}
}

// this needs to be lazy-evaled because vue may be required before
// vue-server-renderer can set VUE_ENV
var _isServer;
var isServerRendering = function () {
  if (_isServer === undefined) {
    /* istanbul ignore if */
    if (!inBrowser && !inWeex && typeof global !== 'undefined') {
      // detect presence of vue-server-renderer and avoid
      // Webpack shimming the process
      _isServer = global['process'] && global['process'].env.VUE_ENV === 'server';
    } else {
      _isServer = false;
    }
  }
  return _isServer
};

// detect devtools
var devtools = inBrowser && window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

/* istanbul ignore next */
function isNative (Ctor) {
  return typeof Ctor === 'function' && /native code/.test(Ctor.toString())
}

var hasSymbol =
  typeof Symbol !== 'undefined' && isNative(Symbol) &&
  typeof Reflect !== 'undefined' && isNative(Reflect.ownKeys);

var _Set;
/* istanbul ignore if */ // $flow-disable-line
if (typeof Set !== 'undefined' && isNative(Set)) {
  // use native Set when available.
  _Set = Set;
} else {
  // a non-standard Set polyfill that only works with primitive keys.
  _Set = /*@__PURE__*/(function () {
    function Set () {
      this.set = Object.create(null);
    }
    Set.prototype.has = function has (key) {
      return this.set[key] === true
    };
    Set.prototype.add = function add (key) {
      this.set[key] = true;
    };
    Set.prototype.clear = function clear () {
      this.set = Object.create(null);
    };

    return Set;
  }());
}

/*  */

var warn = noop;
var tip = noop;
var generateComponentTrace = (noop); // work around flow check
var formatComponentName = (noop);

if (process.env.NODE_ENV !== 'production') {
  var hasConsole = typeof console !== 'undefined';
  var classifyRE = /(?:^|[-_])(\w)/g;
  var classify = function (str) { return str
    .replace(classifyRE, function (c) { return c.toUpperCase(); })
    .replace(/[-_]/g, ''); };

  warn = function (msg, vm) {
    var trace = vm ? generateComponentTrace(vm) : '';

    if (config.warnHandler) {
      config.warnHandler.call(null, msg, vm, trace);
    } else if (hasConsole && (!config.silent)) {
      console.error(("[Vue warn]: " + msg + trace));
    }
  };

  tip = function (msg, vm) {
    if (hasConsole && (!config.silent)) {
      console.warn("[Vue tip]: " + msg + (
        vm ? generateComponentTrace(vm) : ''
      ));
    }
  };

  formatComponentName = function (vm, includeFile) {
    if (vm.$root === vm) {
      return '<Root>'
    }
    var options = typeof vm === 'function' && vm.cid != null
      ? vm.options
      : vm._isVue
        ? vm.$options || vm.constructor.options
        : vm;
    var name = options.name || options._componentTag;
    var file = options.__file;
    if (!name && file) {
      var match = file.match(/([^/\\]+)\.vue$/);
      name = match && match[1];
    }

    return (
      (name ? ("<" + (classify(name)) + ">") : "<Anonymous>") +
      (file && includeFile !== false ? (" at " + file) : '')
    )
  };

  var repeat = function (str, n) {
    var res = '';
    while (n) {
      if (n % 2 === 1) { res += str; }
      if (n > 1) { str += str; }
      n >>= 1;
    }
    return res
  };

  generateComponentTrace = function (vm) {
    if (vm._isVue && vm.$parent) {
      var tree = [];
      var currentRecursiveSequence = 0;
      while (vm) {
        if (tree.length > 0) {
          var last = tree[tree.length - 1];
          if (last.constructor === vm.constructor) {
            currentRecursiveSequence++;
            vm = vm.$parent;
            continue
          } else if (currentRecursiveSequence > 0) {
            tree[tree.length - 1] = [last, currentRecursiveSequence];
            currentRecursiveSequence = 0;
          }
        }
        tree.push(vm);
        vm = vm.$parent;
      }
      return '\n\nfound in\n\n' + tree
        .map(function (vm, i) { return ("" + (i === 0 ? '---> ' : repeat(' ', 5 + i * 2)) + (Array.isArray(vm)
            ? ((formatComponentName(vm[0])) + "... (" + (vm[1]) + " recursive calls)")
            : formatComponentName(vm))); })
        .join('\n')
    } else {
      return ("\n\n(found in " + (formatComponentName(vm)) + ")")
    }
  };
}

/*  */

var uid = 0;

/**
 * A dep is an observable that can have multiple
 * directives subscribing to it.
 */
var Dep = function Dep () {
  this.id = uid++;
  this.subs = [];
};

Dep.prototype.addSub = function addSub (sub) {
  this.subs.push(sub);
};

Dep.prototype.removeSub = function removeSub (sub) {
  remove(this.subs, sub);
};

Dep.prototype.depend = function depend () {
  if (Dep.target) {
    Dep.target.addDep(this);
  }
};

Dep.prototype.notify = function notify () {
  // stabilize the subscriber list first
  var subs = this.subs.slice();
  if (process.env.NODE_ENV !== 'production' && !config.async) {
    // subs aren't sorted in scheduler if not running async
    // we need to sort them now to make sure they fire in correct
    // order
    subs.sort(function (a, b) { return a.id - b.id; });
  }
  for (var i = 0, l = subs.length; i < l; i++) {
    subs[i].update();
  }
};

// The current target watcher being evaluated.
// This is globally unique because only one watcher
// can be evaluated at a time.
Dep.target = null;
var targetStack = [];

function pushTarget (target) {
  targetStack.push(target);
  Dep.target = target;
}

function popTarget () {
  targetStack.pop();
  Dep.target = targetStack[targetStack.length - 1];
}

/*  */

var VNode = function VNode (
  tag,
  data,
  children,
  text,
  elm,
  context,
  componentOptions,
  asyncFactory
) {
  this.tag = tag;
  this.data = data;
  this.children = children;
  this.text = text;
  this.elm = elm;
  this.ns = undefined;
  this.context = context;
  this.fnContext = undefined;
  this.fnOptions = undefined;
  this.fnScopeId = undefined;
  this.key = data && data.key;
  this.componentOptions = componentOptions;
  this.componentInstance = undefined;
  this.parent = undefined;
  this.raw = false;
  this.isStatic = false;
  this.isRootInsert = true;
  this.isComment = false;
  this.isCloned = false;
  this.isOnce = false;
  this.asyncFactory = asyncFactory;
  this.asyncMeta = undefined;
  this.isAsyncPlaceholder = false;
};

var prototypeAccessors = { child: { configurable: true } };

// DEPRECATED: alias for componentInstance for backwards compat.
/* istanbul ignore next */
prototypeAccessors.child.get = function () {
  return this.componentInstance
};

Object.defineProperties( VNode.prototype, prototypeAccessors );

var createEmptyVNode = function (text) {
  if ( text === void 0 ) text = '';

  var node = new VNode();
  node.text = text;
  node.isComment = true;
  return node
};

function createTextVNode (val) {
  return new VNode(undefined, undefined, undefined, String(val))
}

// optimized shallow clone
// used for static nodes and slot nodes because they may be reused across
// multiple renders, cloning them avoids errors when DOM manipulations rely
// on their elm reference.
function cloneVNode (vnode) {
  var cloned = new VNode(
    vnode.tag,
    vnode.data,
    // #7975
    // clone children array to avoid mutating original in case of cloning
    // a child.
    vnode.children && vnode.children.slice(),
    vnode.text,
    vnode.elm,
    vnode.context,
    vnode.componentOptions,
    vnode.asyncFactory
  );
  cloned.ns = vnode.ns;
  cloned.isStatic = vnode.isStatic;
  cloned.key = vnode.key;
  cloned.isComment = vnode.isComment;
  cloned.fnContext = vnode.fnContext;
  cloned.fnOptions = vnode.fnOptions;
  cloned.fnScopeId = vnode.fnScopeId;
  cloned.asyncMeta = vnode.asyncMeta;
  cloned.isCloned = true;
  return cloned
}

/*
 * not type checking this file because flow doesn't play well with
 * dynamically accessing methods on Array prototype
 */

var arrayProto = Array.prototype;
var arrayMethods = Object.create(arrayProto);

var methodsToPatch = [
  'push',
  'pop',
  'shift',
  'unshift',
  'splice',
  'sort',
  'reverse'
];

/**
 * Intercept mutating methods and emit events
 */
methodsToPatch.forEach(function (method) {
  // cache original method
  var original = arrayProto[method];
  def(arrayMethods, method, function mutator () {
    var args = [], len = arguments.length;
    while ( len-- ) args[ len ] = arguments[ len ];

    var result = original.apply(this, args);
    var ob = this.__ob__;
    var inserted;
    switch (method) {
      case 'push':
      case 'unshift':
        inserted = args;
        break
      case 'splice':
        inserted = args.slice(2);
        break
    }
    if (inserted) { ob.observeArray(inserted); }
    // notify change
    ob.dep.notify();
    return result
  });
});

/*  */

var arrayKeys = Object.getOwnPropertyNames(arrayMethods);

/**
 * In some cases we may want to disable observation inside a component's
 * update computation.
 */
var shouldObserve = true;

function toggleObserving (value) {
  shouldObserve = value;
}

/**
 * Observer class that is attached to each observed
 * object. Once attached, the observer converts the target
 * object's property keys into getter/setters that
 * collect dependencies and dispatch updates.
 */
var Observer = function Observer (value) {
  this.value = value;
  this.dep = new Dep();
  this.vmCount = 0;
  def(value, '__ob__', this);
  if (Array.isArray(value)) {
    if (hasProto) {
      protoAugment(value, arrayMethods);
    } else {
      copyAugment(value, arrayMethods, arrayKeys);
    }
    this.observeArray(value);
  } else {
    this.walk(value);
  }
};

/**
 * Walk through all properties and convert them into
 * getter/setters. This method should only be called when
 * value type is Object.
 */
Observer.prototype.walk = function walk (obj) {
  var keys = Object.keys(obj);
  for (var i = 0; i < keys.length; i++) {
    defineReactive$$1(obj, keys[i]);
  }
};

/**
 * Observe a list of Array items.
 */
Observer.prototype.observeArray = function observeArray (items) {
  for (var i = 0, l = items.length; i < l; i++) {
    observe(items[i]);
  }
};

// helpers

/**
 * Augment a target Object or Array by intercepting
 * the prototype chain using __proto__
 */
function protoAugment (target, src) {
  /* eslint-disable no-proto */
  target.__proto__ = src;
  /* eslint-enable no-proto */
}

/**
 * Augment a target Object or Array by defining
 * hidden properties.
 */
/* istanbul ignore next */
function copyAugment (target, src, keys) {
  for (var i = 0, l = keys.length; i < l; i++) {
    var key = keys[i];
    def(target, key, src[key]);
  }
}

/**
 * Attempt to create an observer instance for a value,
 * returns the new observer if successfully observed,
 * or the existing observer if the value already has one.
 */
function observe (value, asRootData) {
  if (!isObject(value) || value instanceof VNode) {
    return
  }
  var ob;
  if (hasOwn(value, '__ob__') && value.__ob__ instanceof Observer) {
    ob = value.__ob__;
  } else if (
    shouldObserve &&
    !isServerRendering() &&
    (Array.isArray(value) || isPlainObject(value)) &&
    Object.isExtensible(value) &&
    !value._isVue
  ) {
    ob = new Observer(value);
  }
  if (asRootData && ob) {
    ob.vmCount++;
  }
  return ob
}

/**
 * Define a reactive property on an Object.
 */
function defineReactive$$1 (
  obj,
  key,
  val,
  customSetter,
  shallow
) {
  var dep = new Dep();

  var property = Object.getOwnPropertyDescriptor(obj, key);
  if (property && property.configurable === false) {
    return
  }

  // cater for pre-defined getter/setters
  var getter = property && property.get;
  var setter = property && property.set;
  if ((!getter || setter) && arguments.length === 2) {
    val = obj[key];
  }

  var childOb = !shallow && observe(val);
  Object.defineProperty(obj, key, {
    enumerable: true,
    configurable: true,
    get: function reactiveGetter () {
      var value = getter ? getter.call(obj) : val;
      if (Dep.target) {
        dep.depend();
        if (childOb) {
          childOb.dep.depend();
          if (Array.isArray(value)) {
            dependArray(value);
          }
        }
      }
      return value
    },
    set: function reactiveSetter (newVal) {
      var value = getter ? getter.call(obj) : val;
      /* eslint-disable no-self-compare */
      if (newVal === value || (newVal !== newVal && value !== value)) {
        return
      }
      /* eslint-enable no-self-compare */
      if (process.env.NODE_ENV !== 'production' && customSetter) {
        customSetter();
      }
      // #7981: for accessor properties without setter
      if (getter && !setter) { return }
      if (setter) {
        setter.call(obj, newVal);
      } else {
        val = newVal;
      }
      childOb = !shallow && observe(newVal);
      dep.notify();
    }
  });
}

/**
 * Set a property on an object. Adds the new property and
 * triggers change notification if the property doesn't
 * already exist.
 */
function set (target, key, val) {
  if (process.env.NODE_ENV !== 'production' &&
    (isUndef(target) || isPrimitive(target))
  ) {
    warn(("Cannot set reactive property on undefined, null, or primitive value: " + ((target))));
  }
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.length = Math.max(target.length, key);
    target.splice(key, 1, val);
    return val
  }
  if (key in target && !(key in Object.prototype)) {
    target[key] = val;
    return val
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    process.env.NODE_ENV !== 'production' && warn(
      'Avoid adding reactive properties to a Vue instance or its root $data ' +
      'at runtime - declare it upfront in the data option.'
    );
    return val
  }
  if (!ob) {
    target[key] = val;
    return val
  }
  defineReactive$$1(ob.value, key, val);
  ob.dep.notify();
  return val
}

/**
 * Delete a property and trigger change if necessary.
 */
function del (target, key) {
  if (process.env.NODE_ENV !== 'production' &&
    (isUndef(target) || isPrimitive(target))
  ) {
    warn(("Cannot delete reactive property on undefined, null, or primitive value: " + ((target))));
  }
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.splice(key, 1);
    return
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    process.env.NODE_ENV !== 'production' && warn(
      'Avoid deleting properties on a Vue instance or its root $data ' +
      '- just set it to null.'
    );
    return
  }
  if (!hasOwn(target, key)) {
    return
  }
  delete target[key];
  if (!ob) {
    return
  }
  ob.dep.notify();
}

/**
 * Collect dependencies on array elements when the array is touched, since
 * we cannot intercept array element access like property getters.
 */
function dependArray (value) {
  for (var e = (void 0), i = 0, l = value.length; i < l; i++) {
    e = value[i];
    e && e.__ob__ && e.__ob__.dep.depend();
    if (Array.isArray(e)) {
      dependArray(e);
    }
  }
}

/*  */

/**
 * Option overwriting strategies are functions that handle
 * how to merge a parent option value and a child option
 * value into the final value.
 */
var strats = config.optionMergeStrategies;

/**
 * Options with restrictions
 */
if (process.env.NODE_ENV !== 'production') {
  strats.el = strats.propsData = function (parent, child, vm, key) {
    if (!vm) {
      warn(
        "option \"" + key + "\" can only be used during instance " +
        'creation with the `new` keyword.'
      );
    }
    return defaultStrat(parent, child)
  };
}

/**
 * Helper that recursively merges two data objects together.
 */
function mergeData (to, from) {
  if (!from) { return to }
  var key, toVal, fromVal;

  var keys = hasSymbol
    ? Reflect.ownKeys(from)
    : Object.keys(from);

  for (var i = 0; i < keys.length; i++) {
    key = keys[i];
    // in case the object is already observed...
    if (key === '__ob__') { continue }
    toVal = to[key];
    fromVal = from[key];
    if (!hasOwn(to, key)) {
      set(to, key, fromVal);
    } else if (
      toVal !== fromVal &&
      isPlainObject(toVal) &&
      isPlainObject(fromVal)
    ) {
      mergeData(toVal, fromVal);
    }
  }
  return to
}

/**
 * Data
 */
function mergeDataOrFn (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    // in a Vue.extend merge, both should be functions
    if (!childVal) {
      return parentVal
    }
    if (!parentVal) {
      return childVal
    }
    // when parentVal & childVal are both present,
    // we need to return a function that returns the
    // merged result of both functions... no need to
    // check if parentVal is a function here because
    // it has to be a function to pass previous merges.
    return function mergedDataFn () {
      return mergeData(
        typeof childVal === 'function' ? childVal.call(this, this) : childVal,
        typeof parentVal === 'function' ? parentVal.call(this, this) : parentVal
      )
    }
  } else {
    return function mergedInstanceDataFn () {
      // instance merge
      var instanceData = typeof childVal === 'function'
        ? childVal.call(vm, vm)
        : childVal;
      var defaultData = typeof parentVal === 'function'
        ? parentVal.call(vm, vm)
        : parentVal;
      if (instanceData) {
        return mergeData(instanceData, defaultData)
      } else {
        return defaultData
      }
    }
  }
}

strats.data = function (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    if (childVal && typeof childVal !== 'function') {
      process.env.NODE_ENV !== 'production' && warn(
        'The "data" option should be a function ' +
        'that returns a per-instance value in component ' +
        'definitions.',
        vm
      );

      return parentVal
    }
    return mergeDataOrFn(parentVal, childVal)
  }

  return mergeDataOrFn(parentVal, childVal, vm)
};

/**
 * Hooks and props are merged as arrays.
 */
function mergeHook (
  parentVal,
  childVal
) {
  var res = childVal
    ? parentVal
      ? parentVal.concat(childVal)
      : Array.isArray(childVal)
        ? childVal
        : [childVal]
    : parentVal;
  return res
    ? dedupeHooks(res)
    : res
}

function dedupeHooks (hooks) {
  var res = [];
  for (var i = 0; i < hooks.length; i++) {
    if (res.indexOf(hooks[i]) === -1) {
      res.push(hooks[i]);
    }
  }
  return res
}

LIFECYCLE_HOOKS.forEach(function (hook) {
  strats[hook] = mergeHook;
});

/**
 * Assets
 *
 * When a vm is present (instance creation), we need to do
 * a three-way merge between constructor options, instance
 * options and parent options.
 */
function mergeAssets (
  parentVal,
  childVal,
  vm,
  key
) {
  var res = Object.create(parentVal || null);
  if (childVal) {
    process.env.NODE_ENV !== 'production' && assertObjectType(key, childVal, vm);
    return extend(res, childVal)
  } else {
    return res
  }
}

ASSET_TYPES.forEach(function (type) {
  strats[type + 's'] = mergeAssets;
});

/**
 * Watchers.
 *
 * Watchers hashes should not overwrite one
 * another, so we merge them as arrays.
 */
strats.watch = function (
  parentVal,
  childVal,
  vm,
  key
) {
  // work around Firefox's Object.prototype.watch...
  if (parentVal === nativeWatch) { parentVal = undefined; }
  if (childVal === nativeWatch) { childVal = undefined; }
  /* istanbul ignore if */
  if (!childVal) { return Object.create(parentVal || null) }
  if (process.env.NODE_ENV !== 'production') {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) { return childVal }
  var ret = {};
  extend(ret, parentVal);
  for (var key$1 in childVal) {
    var parent = ret[key$1];
    var child = childVal[key$1];
    if (parent && !Array.isArray(parent)) {
      parent = [parent];
    }
    ret[key$1] = parent
      ? parent.concat(child)
      : Array.isArray(child) ? child : [child];
  }
  return ret
};

/**
 * Other object hashes.
 */
strats.props =
strats.methods =
strats.inject =
strats.computed = function (
  parentVal,
  childVal,
  vm,
  key
) {
  if (childVal && process.env.NODE_ENV !== 'production') {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) { return childVal }
  var ret = Object.create(null);
  extend(ret, parentVal);
  if (childVal) { extend(ret, childVal); }
  return ret
};
strats.provide = mergeDataOrFn;

/**
 * Default strategy.
 */
var defaultStrat = function (parentVal, childVal) {
  return childVal === undefined
    ? parentVal
    : childVal
};

/**
 * Validate component names
 */
function checkComponents (options) {
  for (var key in options.components) {
    validateComponentName(key);
  }
}

function validateComponentName (name) {
  if (!new RegExp(("^[a-zA-Z][\\-\\.0-9_" + unicodeLetters + "]*$")).test(name)) {
    warn(
      'Invalid component name: "' + name + '". Component names ' +
      'should conform to valid custom element name in html5 specification.'
    );
  }
  if (isBuiltInTag(name) || config.isReservedTag(name)) {
    warn(
      'Do not use built-in or reserved HTML elements as component ' +
      'id: ' + name
    );
  }
}

/**
 * Ensure all props option syntax are normalized into the
 * Object-based format.
 */
function normalizeProps (options, vm) {
  var props = options.props;
  if (!props) { return }
  var res = {};
  var i, val, name;
  if (Array.isArray(props)) {
    i = props.length;
    while (i--) {
      val = props[i];
      if (typeof val === 'string') {
        name = camelize(val);
        res[name] = { type: null };
      } else if (process.env.NODE_ENV !== 'production') {
        warn('props must be strings when using array syntax.');
      }
    }
  } else if (isPlainObject(props)) {
    for (var key in props) {
      val = props[key];
      name = camelize(key);
      res[name] = isPlainObject(val)
        ? val
        : { type: val };
    }
  } else if (process.env.NODE_ENV !== 'production') {
    warn(
      "Invalid value for option \"props\": expected an Array or an Object, " +
      "but got " + (toRawType(props)) + ".",
      vm
    );
  }
  options.props = res;
}

/**
 * Normalize all injections into Object-based format
 */
function normalizeInject (options, vm) {
  var inject = options.inject;
  if (!inject) { return }
  var normalized = options.inject = {};
  if (Array.isArray(inject)) {
    for (var i = 0; i < inject.length; i++) {
      normalized[inject[i]] = { from: inject[i] };
    }
  } else if (isPlainObject(inject)) {
    for (var key in inject) {
      var val = inject[key];
      normalized[key] = isPlainObject(val)
        ? extend({ from: key }, val)
        : { from: val };
    }
  } else if (process.env.NODE_ENV !== 'production') {
    warn(
      "Invalid value for option \"inject\": expected an Array or an Object, " +
      "but got " + (toRawType(inject)) + ".",
      vm
    );
  }
}

/**
 * Normalize raw function directives into object format.
 */
function normalizeDirectives (options) {
  var dirs = options.directives;
  if (dirs) {
    for (var key in dirs) {
      var def$$1 = dirs[key];
      if (typeof def$$1 === 'function') {
        dirs[key] = { bind: def$$1, update: def$$1 };
      }
    }
  }
}

function assertObjectType (name, value, vm) {
  if (!isPlainObject(value)) {
    warn(
      "Invalid value for option \"" + name + "\": expected an Object, " +
      "but got " + (toRawType(value)) + ".",
      vm
    );
  }
}

/**
 * Merge two option objects into a new one.
 * Core utility used in both instantiation and inheritance.
 */
function mergeOptions (
  parent,
  child,
  vm
) {
  if (process.env.NODE_ENV !== 'production') {
    checkComponents(child);
  }

  if (typeof child === 'function') {
    child = child.options;
  }

  normalizeProps(child, vm);
  normalizeInject(child, vm);
  normalizeDirectives(child);

  // Apply extends and mixins on the child options,
  // but only if it is a raw options object that isn't
  // the result of another mergeOptions call.
  // Only merged options has the _base property.
  if (!child._base) {
    if (child.extends) {
      parent = mergeOptions(parent, child.extends, vm);
    }
    if (child.mixins) {
      for (var i = 0, l = child.mixins.length; i < l; i++) {
        parent = mergeOptions(parent, child.mixins[i], vm);
      }
    }
  }

  var options = {};
  var key;
  for (key in parent) {
    mergeField(key);
  }
  for (key in child) {
    if (!hasOwn(parent, key)) {
      mergeField(key);
    }
  }
  function mergeField (key) {
    var strat = strats[key] || defaultStrat;
    options[key] = strat(parent[key], child[key], vm, key);
  }
  return options
}

/**
 * Resolve an asset.
 * This function is used because child instances need access
 * to assets defined in its ancestor chain.
 */
function resolveAsset (
  options,
  type,
  id,
  warnMissing
) {
  /* istanbul ignore if */
  if (typeof id !== 'string') {
    return
  }
  var assets = options[type];
  // check local registration variations first
  if (hasOwn(assets, id)) { return assets[id] }
  var camelizedId = camelize(id);
  if (hasOwn(assets, camelizedId)) { return assets[camelizedId] }
  var PascalCaseId = capitalize(camelizedId);
  if (hasOwn(assets, PascalCaseId)) { return assets[PascalCaseId] }
  // fallback to prototype chain
  var res = assets[id] || assets[camelizedId] || assets[PascalCaseId];
  if (process.env.NODE_ENV !== 'production' && warnMissing && !res) {
    warn(
      'Failed to resolve ' + type.slice(0, -1) + ': ' + id,
      options
    );
  }
  return res
}

/*  */



function validateProp (
  key,
  propOptions,
  propsData,
  vm
) {
  var prop = propOptions[key];
  var absent = !hasOwn(propsData, key);
  var value = propsData[key];
  // boolean casting
  var booleanIndex = getTypeIndex(Boolean, prop.type);
  if (booleanIndex > -1) {
    if (absent && !hasOwn(prop, 'default')) {
      value = false;
    } else if (value === '' || value === hyphenate(key)) {
      // only cast empty string / same name to boolean if
      // boolean has higher priority
      var stringIndex = getTypeIndex(String, prop.type);
      if (stringIndex < 0 || booleanIndex < stringIndex) {
        value = true;
      }
    }
  }
  // check default value
  if (value === undefined) {
    value = getPropDefaultValue(vm, prop, key);
    // since the default value is a fresh copy,
    // make sure to observe it.
    var prevShouldObserve = shouldObserve;
    toggleObserving(true);
    observe(value);
    toggleObserving(prevShouldObserve);
  }
  if (
    process.env.NODE_ENV !== 'production' &&
    // skip validation for weex recycle-list child component props
    !(false)
  ) {
    assertProp(prop, key, value, vm, absent);
  }
  return value
}

/**
 * Get the default value of a prop.
 */
function getPropDefaultValue (vm, prop, key) {
  // no default, return undefined
  if (!hasOwn(prop, 'default')) {
    return undefined
  }
  var def = prop.default;
  // warn against non-factory defaults for Object & Array
  if (process.env.NODE_ENV !== 'production' && isObject(def)) {
    warn(
      'Invalid default value for prop "' + key + '": ' +
      'Props with type Object/Array must use a factory function ' +
      'to return the default value.',
      vm
    );
  }
  // the raw prop value was also undefined from previous render,
  // return previous default value to avoid unnecessary watcher trigger
  if (vm && vm.$options.propsData &&
    vm.$options.propsData[key] === undefined &&
    vm._props[key] !== undefined
  ) {
    return vm._props[key]
  }
  // call factory function for non-Function types
  // a value is Function if its prototype is function even across different execution context
  return typeof def === 'function' && getType(prop.type) !== 'Function'
    ? def.call(vm)
    : def
}

/**
 * Assert whether a prop is valid.
 */
function assertProp (
  prop,
  name,
  value,
  vm,
  absent
) {
  if (prop.required && absent) {
    warn(
      'Missing required prop: "' + name + '"',
      vm
    );
    return
  }
  if (value == null && !prop.required) {
    return
  }
  var type = prop.type;
  var valid = !type || type === true;
  var expectedTypes = [];
  if (type) {
    if (!Array.isArray(type)) {
      type = [type];
    }
    for (var i = 0; i < type.length && !valid; i++) {
      var assertedType = assertType(value, type[i]);
      expectedTypes.push(assertedType.expectedType || '');
      valid = assertedType.valid;
    }
  }

  if (!valid) {
    warn(
      getInvalidTypeMessage(name, value, expectedTypes),
      vm
    );
    return
  }
  var validator = prop.validator;
  if (validator) {
    if (!validator(value)) {
      warn(
        'Invalid prop: custom validator check failed for prop "' + name + '".',
        vm
      );
    }
  }
}

var simpleCheckRE = /^(String|Number|Boolean|Function|Symbol)$/;

function assertType (value, type) {
  var valid;
  var expectedType = getType(type);
  if (simpleCheckRE.test(expectedType)) {
    var t = typeof value;
    valid = t === expectedType.toLowerCase();
    // for primitive wrapper objects
    if (!valid && t === 'object') {
      valid = value instanceof type;
    }
  } else if (expectedType === 'Object') {
    valid = isPlainObject(value);
  } else if (expectedType === 'Array') {
    valid = Array.isArray(value);
  } else {
    valid = value instanceof type;
  }
  return {
    valid: valid,
    expectedType: expectedType
  }
}

/**
 * Use function string name to check built-in types,
 * because a simple equality check will fail when running
 * across different vms / iframes.
 */
function getType (fn) {
  var match = fn && fn.toString().match(/^\s*function (\w+)/);
  return match ? match[1] : ''
}

function isSameType (a, b) {
  return getType(a) === getType(b)
}

function getTypeIndex (type, expectedTypes) {
  if (!Array.isArray(expectedTypes)) {
    return isSameType(expectedTypes, type) ? 0 : -1
  }
  for (var i = 0, len = expectedTypes.length; i < len; i++) {
    if (isSameType(expectedTypes[i], type)) {
      return i
    }
  }
  return -1
}

function getInvalidTypeMessage (name, value, expectedTypes) {
  var message = "Invalid prop: type check failed for prop \"" + name + "\"." +
    " Expected " + (expectedTypes.map(capitalize).join(', '));
  var expectedType = expectedTypes[0];
  var receivedType = toRawType(value);
  var expectedValue = styleValue(value, expectedType);
  var receivedValue = styleValue(value, receivedType);
  // check if we need to specify expected value
  if (expectedTypes.length === 1 &&
      isExplicable(expectedType) &&
      !isBoolean(expectedType, receivedType)) {
    message += " with value " + expectedValue;
  }
  message += ", got " + receivedType + " ";
  // check if we need to specify received value
  if (isExplicable(receivedType)) {
    message += "with value " + receivedValue + ".";
  }
  return message
}

function styleValue (value, type) {
  if (type === 'String') {
    return ("\"" + value + "\"")
  } else if (type === 'Number') {
    return ("" + (Number(value)))
  } else {
    return ("" + value)
  }
}

function isExplicable (value) {
  var explicitTypes = ['string', 'number', 'boolean'];
  return explicitTypes.some(function (elem) { return value.toLowerCase() === elem; })
}

function isBoolean () {
  var args = [], len = arguments.length;
  while ( len-- ) args[ len ] = arguments[ len ];

  return args.some(function (elem) { return elem.toLowerCase() === 'boolean'; })
}

/*  */

function handleError (err, vm, info) {
  if (vm) {
    var cur = vm;
    while ((cur = cur.$parent)) {
      var hooks = cur.$options.errorCaptured;
      if (hooks) {
        for (var i = 0; i < hooks.length; i++) {
          try {
            var capture = hooks[i].call(cur, err, vm, info) === false;
            if (capture) { return }
          } catch (e) {
            globalHandleError(e, cur, 'errorCaptured hook');
          }
        }
      }
    }
  }
  globalHandleError(err, vm, info);
}

function invokeWithErrorHandling (
  handler,
  context,
  args,
  vm,
  info
) {
  var res;
  try {
    res = args ? handler.apply(context, args) : handler.call(context);
    if (res && !res._isVue && isPromise(res)) {
      res.catch(function (e) { return handleError(e, vm, info + " (Promise/async)"); });
    }
  } catch (e) {
    handleError(e, vm, info);
  }
  return res
}

function globalHandleError (err, vm, info) {
  if (config.errorHandler) {
    try {
      return config.errorHandler.call(null, err, vm, info)
    } catch (e) {
      // if the user intentionally throws the original error in the handler,
      // do not log it twice
      if (e !== err) {
        logError(e, null, 'config.errorHandler');
      }
    }
  }
  logError(err, vm, info);
}

function logError (err, vm, info) {
  if (process.env.NODE_ENV !== 'production') {
    warn(("Error in " + info + ": \"" + (err.toString()) + "\""), vm);
  }
  /* istanbul ignore else */
  if ((inBrowser || inWeex) && typeof console !== 'undefined') {
    console.error(err);
  } else {
    throw err
  }
}

/*  */

var isUsingMicroTask = false;

var callbacks = [];
var pending = false;

function flushCallbacks () {
  pending = false;
  var copies = callbacks.slice(0);
  callbacks.length = 0;
  for (var i = 0; i < copies.length; i++) {
    copies[i]();
  }
}

// Here we have async deferring wrappers using microtasks.
// In 2.5 we used (macro) tasks (in combination with microtasks).
// However, it has subtle problems when state is changed right before repaint
// (e.g. #6813, out-in transitions).
// Also, using (macro) tasks in event handler would cause some weird behaviors
// that cannot be circumvented (e.g. #7109, #7153, #7546, #7834, #8109).
// So we now use microtasks everywhere, again.
// A major drawback of this tradeoff is that there are some scenarios
// where microtasks have too high a priority and fire in between supposedly
// sequential events (e.g. #4521, #6690, which have workarounds)
// or even between bubbling of the same event (#6566).
var timerFunc;

// The nextTick behavior leverages the microtask queue, which can be accessed
// via either native Promise.then or MutationObserver.
// MutationObserver has wider support, however it is seriously bugged in
// UIWebView in iOS >= 9.3.3 when triggered in touch event handlers. It
// completely stops working after triggering a few times... so, if native
// Promise is available, we will use it:
/* istanbul ignore next, $flow-disable-line */
if (typeof Promise !== 'undefined' && isNative(Promise)) {
  var p = Promise.resolve();
  timerFunc = function () {
    p.then(flushCallbacks);
    // In problematic UIWebViews, Promise.then doesn't completely break, but
    // it can get stuck in a weird state where callbacks are pushed into the
    // microtask queue but the queue isn't being flushed, until the browser
    // needs to do some other work, e.g. handle a timer. Therefore we can
    // "force" the microtask queue to be flushed by adding an empty timer.
    if (isIOS) { setTimeout(noop); }
  };
  isUsingMicroTask = true;
} else if (!isIE && typeof MutationObserver !== 'undefined' && (
  isNative(MutationObserver) ||
  // PhantomJS and iOS 7.x
  MutationObserver.toString() === '[object MutationObserverConstructor]'
)) {
  // Use MutationObserver where native Promise is not available,
  // e.g. PhantomJS, iOS7, Android 4.4
  // (#6466 MutationObserver is unreliable in IE11)
  var counter = 1;
  var observer = new MutationObserver(flushCallbacks);
  var textNode = document.createTextNode(String(counter));
  observer.observe(textNode, {
    characterData: true
  });
  timerFunc = function () {
    counter = (counter + 1) % 2;
    textNode.data = String(counter);
  };
  isUsingMicroTask = true;
} else if (typeof setImmediate !== 'undefined' && isNative(setImmediate)) {
  // Fallback to setImmediate.
  // Techinically it leverages the (macro) task queue,
  // but it is still a better choice than setTimeout.
  timerFunc = function () {
    setImmediate(flushCallbacks);
  };
} else {
  // Fallback to setTimeout.
  timerFunc = function () {
    setTimeout(flushCallbacks, 0);
  };
}

function nextTick (cb, ctx) {
  var _resolve;
  callbacks.push(function () {
    if (cb) {
      try {
        cb.call(ctx);
      } catch (e) {
        handleError(e, ctx, 'nextTick');
      }
    } else if (_resolve) {
      _resolve(ctx);
    }
  });
  if (!pending) {
    pending = true;
    timerFunc();
  }
  // $flow-disable-line
  if (!cb && typeof Promise !== 'undefined') {
    return new Promise(function (resolve) {
      _resolve = resolve;
    })
  }
}

/*  */

var mark;
var measure;

if (process.env.NODE_ENV !== 'production') {
  var perf = inBrowser && window.performance;
  /* istanbul ignore if */
  if (
    perf &&
    perf.mark &&
    perf.measure &&
    perf.clearMarks &&
    perf.clearMeasures
  ) {
    mark = function (tag) { return perf.mark(tag); };
    measure = function (name, startTag, endTag) {
      perf.measure(name, startTag, endTag);
      perf.clearMarks(startTag);
      perf.clearMarks(endTag);
      // perf.clearMeasures(name)
    };
  }
}

/* not type checking this file because flow doesn't play well with Proxy */

var initProxy;

if (process.env.NODE_ENV !== 'production') {
  var allowedGlobals = makeMap(
    'Infinity,undefined,NaN,isFinite,isNaN,' +
    'parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,' +
    'Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,' +
    'require' // for Webpack/Browserify
  );

  var warnNonPresent = function (target, key) {
    warn(
      "Property or method \"" + key + "\" is not defined on the instance but " +
      'referenced during render. Make sure that this property is reactive, ' +
      'either in the data option, or for class-based components, by ' +
      'initializing the property. ' +
      'See: https://vuejs.org/v2/guide/reactivity.html#Declaring-Reactive-Properties.',
      target
    );
  };

  var warnReservedPrefix = function (target, key) {
    warn(
      "Property \"" + key + "\" must be accessed with \"$data." + key + "\" because " +
      'properties starting with "$" or "_" are not proxied in the Vue instance to ' +
      'prevent conflicts with Vue internals' +
      'See: https://vuejs.org/v2/api/#data',
      target
    );
  };

  var hasProxy =
    typeof Proxy !== 'undefined' && isNative(Proxy);

  if (hasProxy) {
    var isBuiltInModifier = makeMap('stop,prevent,self,ctrl,shift,alt,meta,exact');
    config.keyCodes = new Proxy(config.keyCodes, {
      set: function set (target, key, value) {
        if (isBuiltInModifier(key)) {
          warn(("Avoid overwriting built-in modifier in config.keyCodes: ." + key));
          return false
        } else {
          target[key] = value;
          return true
        }
      }
    });
  }

  var hasHandler = {
    has: function has (target, key) {
      var has = key in target;
      var isAllowed = allowedGlobals(key) ||
        (typeof key === 'string' && key.charAt(0) === '_' && !(key in target.$data));
      if (!has && !isAllowed) {
        if (key in target.$data) { warnReservedPrefix(target, key); }
        else { warnNonPresent(target, key); }
      }
      return has || !isAllowed
    }
  };

  var getHandler = {
    get: function get (target, key) {
      if (typeof key === 'string' && !(key in target)) {
        if (key in target.$data) { warnReservedPrefix(target, key); }
        else { warnNonPresent(target, key); }
      }
      return target[key]
    }
  };

  initProxy = function initProxy (vm) {
    if (hasProxy) {
      // determine which proxy handler to use
      var options = vm.$options;
      var handlers = options.render && options.render._withStripped
        ? getHandler
        : hasHandler;
      vm._renderProxy = new Proxy(vm, handlers);
    } else {
      vm._renderProxy = vm;
    }
  };
}

/*  */

var seenObjects = new _Set();

/**
 * Recursively traverse an object to evoke all converted
 * getters, so that every nested property inside the object
 * is collected as a "deep" dependency.
 */
function traverse (val) {
  _traverse(val, seenObjects);
  seenObjects.clear();
}

function _traverse (val, seen) {
  var i, keys;
  var isA = Array.isArray(val);
  if ((!isA && !isObject(val)) || Object.isFrozen(val) || val instanceof VNode) {
    return
  }
  if (val.__ob__) {
    var depId = val.__ob__.dep.id;
    if (seen.has(depId)) {
      return
    }
    seen.add(depId);
  }
  if (isA) {
    i = val.length;
    while (i--) { _traverse(val[i], seen); }
  } else {
    keys = Object.keys(val);
    i = keys.length;
    while (i--) { _traverse(val[keys[i]], seen); }
  }
}

/*  */

var normalizeEvent = cached(function (name) {
  var passive = name.charAt(0) === '&';
  name = passive ? name.slice(1) : name;
  var once$$1 = name.charAt(0) === '~'; // Prefixed last, checked first
  name = once$$1 ? name.slice(1) : name;
  var capture = name.charAt(0) === '!';
  name = capture ? name.slice(1) : name;
  return {
    name: name,
    once: once$$1,
    capture: capture,
    passive: passive
  }
});

function createFnInvoker (fns, vm) {
  function invoker () {
    var arguments$1 = arguments;

    var fns = invoker.fns;
    if (Array.isArray(fns)) {
      var cloned = fns.slice();
      for (var i = 0; i < cloned.length; i++) {
        invokeWithErrorHandling(cloned[i], null, arguments$1, vm, "v-on handler");
      }
    } else {
      // return handler return value for single handlers
      return invokeWithErrorHandling(fns, null, arguments, vm, "v-on handler")
    }
  }
  invoker.fns = fns;
  return invoker
}

function updateListeners (
  on,
  oldOn,
  add,
  remove$$1,
  createOnceHandler,
  vm
) {
  var name, def$$1, cur, old, event;
  for (name in on) {
    def$$1 = cur = on[name];
    old = oldOn[name];
    event = normalizeEvent(name);
    if (isUndef(cur)) {
      process.env.NODE_ENV !== 'production' && warn(
        "Invalid handler for event \"" + (event.name) + "\": got " + String(cur),
        vm
      );
    } else if (isUndef(old)) {
      if (isUndef(cur.fns)) {
        cur = on[name] = createFnInvoker(cur, vm);
      }
      if (isTrue(event.once)) {
        cur = on[name] = createOnceHandler(event.name, cur, event.capture);
      }
      add(event.name, cur, event.capture, event.passive, event.params);
    } else if (cur !== old) {
      old.fns = cur;
      on[name] = old;
    }
  }
  for (name in oldOn) {
    if (isUndef(on[name])) {
      event = normalizeEvent(name);
      remove$$1(event.name, oldOn[name], event.capture);
    }
  }
}

/*  */

function mergeVNodeHook (def, hookKey, hook) {
  if (def instanceof VNode) {
    def = def.data.hook || (def.data.hook = {});
  }
  var invoker;
  var oldHook = def[hookKey];

  function wrappedHook () {
    hook.apply(this, arguments);
    // important: remove merged hook to ensure it's called only once
    // and prevent memory leak
    remove(invoker.fns, wrappedHook);
  }

  if (isUndef(oldHook)) {
    // no existing hook
    invoker = createFnInvoker([wrappedHook]);
  } else {
    /* istanbul ignore if */
    if (isDef(oldHook.fns) && isTrue(oldHook.merged)) {
      // already a merged invoker
      invoker = oldHook;
      invoker.fns.push(wrappedHook);
    } else {
      // existing plain hook
      invoker = createFnInvoker([oldHook, wrappedHook]);
    }
  }

  invoker.merged = true;
  def[hookKey] = invoker;
}

/*  */

function extractPropsFromVNodeData (
  data,
  Ctor,
  tag
) {
  // we are only extracting raw values here.
  // validation and default values are handled in the child
  // component itself.
  var propOptions = Ctor.options.props;
  if (isUndef(propOptions)) {
    return
  }
  var res = {};
  var attrs = data.attrs;
  var props = data.props;
  if (isDef(attrs) || isDef(props)) {
    for (var key in propOptions) {
      var altKey = hyphenate(key);
      if (process.env.NODE_ENV !== 'production') {
        var keyInLowerCase = key.toLowerCase();
        if (
          key !== keyInLowerCase &&
          attrs && hasOwn(attrs, keyInLowerCase)
        ) {
          tip(
            "Prop \"" + keyInLowerCase + "\" is passed to component " +
            (formatComponentName(tag || Ctor)) + ", but the declared prop name is" +
            " \"" + key + "\". " +
            "Note that HTML attributes are case-insensitive and camelCased " +
            "props need to use their kebab-case equivalents when using in-DOM " +
            "templates. You should probably use \"" + altKey + "\" instead of \"" + key + "\"."
          );
        }
      }
      checkProp(res, props, key, altKey, true) ||
      checkProp(res, attrs, key, altKey, false);
    }
  }
  return res
}

function checkProp (
  res,
  hash,
  key,
  altKey,
  preserve
) {
  if (isDef(hash)) {
    if (hasOwn(hash, key)) {
      res[key] = hash[key];
      if (!preserve) {
        delete hash[key];
      }
      return true
    } else if (hasOwn(hash, altKey)) {
      res[key] = hash[altKey];
      if (!preserve) {
        delete hash[altKey];
      }
      return true
    }
  }
  return false
}

/*  */

// The template compiler attempts to minimize the need for normalization by
// statically analyzing the template at compile time.
//
// For plain HTML markup, normalization can be completely skipped because the
// generated render function is guaranteed to return Array<VNode>. There are
// two cases where extra normalization is needed:

// 1. When the children contains components - because a functional component
// may return an Array instead of a single root. In this case, just a simple
// normalization is needed - if any child is an Array, we flatten the whole
// thing with Array.prototype.concat. It is guaranteed to be only 1-level deep
// because functional components already normalize their own children.
function simpleNormalizeChildren (children) {
  for (var i = 0; i < children.length; i++) {
    if (Array.isArray(children[i])) {
      return Array.prototype.concat.apply([], children)
    }
  }
  return children
}

// 2. When the children contains constructs that always generated nested Arrays,
// e.g. <template>, <slot>, v-for, or when the children is provided by user
// with hand-written render functions / JSX. In such cases a full normalization
// is needed to cater to all possible types of children values.
function normalizeChildren (children) {
  return isPrimitive(children)
    ? [createTextVNode(children)]
    : Array.isArray(children)
      ? normalizeArrayChildren(children)
      : undefined
}

function isTextNode (node) {
  return isDef(node) && isDef(node.text) && isFalse(node.isComment)
}

function normalizeArrayChildren (children, nestedIndex) {
  var res = [];
  var i, c, lastIndex, last;
  for (i = 0; i < children.length; i++) {
    c = children[i];
    if (isUndef(c) || typeof c === 'boolean') { continue }
    lastIndex = res.length - 1;
    last = res[lastIndex];
    //  nested
    if (Array.isArray(c)) {
      if (c.length > 0) {
        c = normalizeArrayChildren(c, ((nestedIndex || '') + "_" + i));
        // merge adjacent text nodes
        if (isTextNode(c[0]) && isTextNode(last)) {
          res[lastIndex] = createTextVNode(last.text + (c[0]).text);
          c.shift();
        }
        res.push.apply(res, c);
      }
    } else if (isPrimitive(c)) {
      if (isTextNode(last)) {
        // merge adjacent text nodes
        // this is necessary for SSR hydration because text nodes are
        // essentially merged when rendered to HTML strings
        res[lastIndex] = createTextVNode(last.text + c);
      } else if (c !== '') {
        // convert primitive to vnode
        res.push(createTextVNode(c));
      }
    } else {
      if (isTextNode(c) && isTextNode(last)) {
        // merge adjacent text nodes
        res[lastIndex] = createTextVNode(last.text + c.text);
      } else {
        // default key for nested array children (likely generated by v-for)
        if (isTrue(children._isVList) &&
          isDef(c.tag) &&
          isUndef(c.key) &&
          isDef(nestedIndex)) {
          c.key = "__vlist" + nestedIndex + "_" + i + "__";
        }
        res.push(c);
      }
    }
  }
  return res
}

/*  */

function initProvide (vm) {
  var provide = vm.$options.provide;
  if (provide) {
    vm._provided = typeof provide === 'function'
      ? provide.call(vm)
      : provide;
  }
}

function initInjections (vm) {
  var result = resolveInject(vm.$options.inject, vm);
  if (result) {
    toggleObserving(false);
    Object.keys(result).forEach(function (key) {
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production') {
        defineReactive$$1(vm, key, result[key], function () {
          warn(
            "Avoid mutating an injected value directly since the changes will be " +
            "overwritten whenever the provided component re-renders. " +
            "injection being mutated: \"" + key + "\"",
            vm
          );
        });
      } else {
        defineReactive$$1(vm, key, result[key]);
      }
    });
    toggleObserving(true);
  }
}

function resolveInject (inject, vm) {
  if (inject) {
    // inject is :any because flow is not smart enough to figure out cached
    var result = Object.create(null);
    var keys = hasSymbol
      ? Reflect.ownKeys(inject)
      : Object.keys(inject);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      // #6574 in case the inject object is observed...
      if (key === '__ob__') { continue }
      var provideKey = inject[key].from;
      var source = vm;
      while (source) {
        if (source._provided && hasOwn(source._provided, provideKey)) {
          result[key] = source._provided[provideKey];
          break
        }
        source = source.$parent;
      }
      if (!source) {
        if ('default' in inject[key]) {
          var provideDefault = inject[key].default;
          result[key] = typeof provideDefault === 'function'
            ? provideDefault.call(vm)
            : provideDefault;
        } else if (process.env.NODE_ENV !== 'production') {
          warn(("Injection \"" + key + "\" not found"), vm);
        }
      }
    }
    return result
  }
}

/*  */



/**
 * Runtime helper for resolving raw children VNodes into a slot object.
 */
function resolveSlots (
  children,
  context
) {
  if (!children || !children.length) {
    return {}
  }
  var slots = {};
  for (var i = 0, l = children.length; i < l; i++) {
    var child = children[i];
    var data = child.data;
    // remove slot attribute if the node is resolved as a Vue slot node
    if (data && data.attrs && data.attrs.slot) {
      delete data.attrs.slot;
    }
    // named slots should only be respected if the vnode was rendered in the
    // same context.
    if ((child.context === context || child.fnContext === context) &&
      data && data.slot != null
    ) {
      var name = data.slot;
      var slot = (slots[name] || (slots[name] = []));
      if (child.tag === 'template') {
        slot.push.apply(slot, child.children || []);
      } else {
        slot.push(child);
      }
    } else {
      (slots.default || (slots.default = [])).push(child);
    }
  }
  // ignore slots that contains only whitespace
  for (var name$1 in slots) {
    if (slots[name$1].every(isWhitespace)) {
      delete slots[name$1];
    }
  }
  return slots
}

function isWhitespace (node) {
  return (node.isComment && !node.asyncFactory) || node.text === ' '
}

/*  */

function normalizeScopedSlots (
  slots,
  normalSlots,
  prevSlots
) {
  var res;
  if (!slots) {
    res = {};
  } else if (slots._normalized) {
    // fast path 1: child component re-render only, parent did not change
    return slots._normalized
  } else if (
    slots.$stable &&
    prevSlots &&
    prevSlots !== emptyObject &&
    Object.keys(normalSlots).length === 0
  ) {
    // fast path 2: stable scoped slots w/ no normal slots to proxy,
    // only need to normalize once
    return prevSlots
  } else {
    res = {};
    for (var key in slots) {
      if (slots[key] && key[0] !== '$') {
        res[key] = normalizeScopedSlot(normalSlots, key, slots[key]);
      }
    }
  }
  // expose normal slots on scopedSlots
  for (var key$1 in normalSlots) {
    if (!(key$1 in res)) {
      res[key$1] = proxyNormalSlot(normalSlots, key$1);
    }
  }
  // avoriaz seems to mock a non-extensible $scopedSlots object
  // and when that is passed down this would cause an error
  if (slots && Object.isExtensible(slots)) {
    (slots)._normalized = res;
  }
  def(res, '$stable', slots ? !!slots.$stable : true);
  return res
}

function normalizeScopedSlot(normalSlots, key, fn) {
  var normalized = function () {
    var res = arguments.length ? fn.apply(null, arguments) : fn({});
    res = res && typeof res === 'object' && !Array.isArray(res)
      ? [res] // single vnode
      : normalizeChildren(res);
    return res && res.length === 0
      ? undefined
      : res
  };
  // this is a slot using the new v-slot syntax without scope. although it is
  // compiled as a scoped slot, render fn users would expect it to be present
  // on this.$slots because the usage is semantically a normal slot.
  if (fn.proxy) {
    Object.defineProperty(normalSlots, key, {
      get: normalized,
      enumerable: true,
      configurable: true
    });
  }
  return normalized
}

function proxyNormalSlot(slots, key) {
  return function () { return slots[key]; }
}

/*  */

/**
 * Runtime helper for rendering v-for lists.
 */
function renderList (
  val,
  render
) {
  var ret, i, l, keys, key;
  if (Array.isArray(val) || typeof val === 'string') {
    ret = new Array(val.length);
    for (i = 0, l = val.length; i < l; i++) {
      ret[i] = render(val[i], i);
    }
  } else if (typeof val === 'number') {
    ret = new Array(val);
    for (i = 0; i < val; i++) {
      ret[i] = render(i + 1, i);
    }
  } else if (isObject(val)) {
    if (hasSymbol && val[Symbol.iterator]) {
      ret = [];
      var iterator = val[Symbol.iterator]();
      var result = iterator.next();
      while (!result.done) {
        ret.push(render(result.value, ret.length));
        result = iterator.next();
      }
    } else {
      keys = Object.keys(val);
      ret = new Array(keys.length);
      for (i = 0, l = keys.length; i < l; i++) {
        key = keys[i];
        ret[i] = render(val[key], key, i);
      }
    }
  }
  if (!isDef(ret)) {
    ret = [];
  }
  (ret)._isVList = true;
  return ret
}

/*  */

/**
 * Runtime helper for rendering <slot>
 */
function renderSlot (
  name,
  fallback,
  props,
  bindObject
) {
  var scopedSlotFn = this.$scopedSlots[name];
  var nodes;
  if (scopedSlotFn) { // scoped slot
    props = props || {};
    if (bindObject) {
      if (process.env.NODE_ENV !== 'production' && !isObject(bindObject)) {
        warn(
          'slot v-bind without argument expects an Object',
          this
        );
      }
      props = extend(extend({}, bindObject), props);
    }
    nodes = scopedSlotFn(props) || fallback;
  } else {
    nodes = this.$slots[name] || fallback;
  }

  var target = props && props.slot;
  if (target) {
    return this.$createElement('template', { slot: target }, nodes)
  } else {
    return nodes
  }
}

/*  */

/**
 * Runtime helper for resolving filters
 */
function resolveFilter (id) {
  return resolveAsset(this.$options, 'filters', id, true) || identity
}

/*  */

function isKeyNotMatch (expect, actual) {
  if (Array.isArray(expect)) {
    return expect.indexOf(actual) === -1
  } else {
    return expect !== actual
  }
}

/**
 * Runtime helper for checking keyCodes from config.
 * exposed as Vue.prototype._k
 * passing in eventKeyName as last argument separately for backwards compat
 */
function checkKeyCodes (
  eventKeyCode,
  key,
  builtInKeyCode,
  eventKeyName,
  builtInKeyName
) {
  var mappedKeyCode = config.keyCodes[key] || builtInKeyCode;
  if (builtInKeyName && eventKeyName && !config.keyCodes[key]) {
    return isKeyNotMatch(builtInKeyName, eventKeyName)
  } else if (mappedKeyCode) {
    return isKeyNotMatch(mappedKeyCode, eventKeyCode)
  } else if (eventKeyName) {
    return hyphenate(eventKeyName) !== key
  }
}

/*  */

/**
 * Runtime helper for merging v-bind="object" into a VNode's data.
 */
function bindObjectProps (
  data,
  tag,
  value,
  asProp,
  isSync
) {
  if (value) {
    if (!isObject(value)) {
      process.env.NODE_ENV !== 'production' && warn(
        'v-bind without argument expects an Object or Array value',
        this
      );
    } else {
      if (Array.isArray(value)) {
        value = toObject(value);
      }
      var hash;
      var loop = function ( key ) {
        if (
          key === 'class' ||
          key === 'style' ||
          isReservedAttribute(key)
        ) {
          hash = data;
        } else {
          var type = data.attrs && data.attrs.type;
          hash = asProp || config.mustUseProp(tag, type, key)
            ? data.domProps || (data.domProps = {})
            : data.attrs || (data.attrs = {});
        }
        var camelizedKey = camelize(key);
        if (!(key in hash) && !(camelizedKey in hash)) {
          hash[key] = value[key];

          if (isSync) {
            var on = data.on || (data.on = {});
            on[("update:" + camelizedKey)] = function ($event) {
              value[key] = $event;
            };
          }
        }
      };

      for (var key in value) loop( key );
    }
  }
  return data
}

/*  */

/**
 * Runtime helper for rendering static trees.
 */
function renderStatic (
  index,
  isInFor
) {
  var cached = this._staticTrees || (this._staticTrees = []);
  var tree = cached[index];
  // if has already-rendered static tree and not inside v-for,
  // we can reuse the same tree.
  if (tree && !isInFor) {
    return tree
  }
  // otherwise, render a fresh tree.
  tree = cached[index] = this.$options.staticRenderFns[index].call(
    this._renderProxy,
    null,
    this // for render fns generated for functional component templates
  );
  markStatic(tree, ("__static__" + index), false);
  return tree
}

/**
 * Runtime helper for v-once.
 * Effectively it means marking the node as static with a unique key.
 */
function markOnce (
  tree,
  index,
  key
) {
  markStatic(tree, ("__once__" + index + (key ? ("_" + key) : "")), true);
  return tree
}

function markStatic (
  tree,
  key,
  isOnce
) {
  if (Array.isArray(tree)) {
    for (var i = 0; i < tree.length; i++) {
      if (tree[i] && typeof tree[i] !== 'string') {
        markStaticNode(tree[i], (key + "_" + i), isOnce);
      }
    }
  } else {
    markStaticNode(tree, key, isOnce);
  }
}

function markStaticNode (node, key, isOnce) {
  node.isStatic = true;
  node.key = key;
  node.isOnce = isOnce;
}

/*  */

function bindObjectListeners (data, value) {
  if (value) {
    if (!isPlainObject(value)) {
      process.env.NODE_ENV !== 'production' && warn(
        'v-on without argument expects an Object value',
        this
      );
    } else {
      var on = data.on = data.on ? extend({}, data.on) : {};
      for (var key in value) {
        var existing = on[key];
        var ours = value[key];
        on[key] = existing ? [].concat(existing, ours) : ours;
      }
    }
  }
  return data
}

/*  */

function resolveScopedSlots (
  fns, // see flow/vnode
  hasDynamicKeys,
  res
) {
  res = res || { $stable: !hasDynamicKeys };
  for (var i = 0; i < fns.length; i++) {
    var slot = fns[i];
    if (Array.isArray(slot)) {
      resolveScopedSlots(slot, hasDynamicKeys, res);
    } else if (slot) {
      // marker for reverse proxying v-slot without scope on this.$slots
      if (slot.proxy) {
        slot.fn.proxy = true;
      }
      res[slot.key] = slot.fn;
    }
  }
  return res
}

/*  */

function bindDynamicKeys (baseObj, values) {
  for (var i = 0; i < values.length; i += 2) {
    var key = values[i];
    if (typeof key === 'string' && key) {
      baseObj[values[i]] = values[i + 1];
    } else if (process.env.NODE_ENV !== 'production' && key !== '' && key !== null) {
      // null is a speical value for explicitly removing a binding
      warn(
        ("Invalid value for dynamic directive argument (expected string or null): " + key),
        this
      );
    }
  }
  return baseObj
}

// helper to dynamically append modifier runtime markers to event names.
// ensure only append when value is already string, otherwise it will be cast
// to string and cause the type check to miss.
function prependModifier (value, symbol) {
  return typeof value === 'string' ? symbol + value : value
}

/*  */

function installRenderHelpers (target) {
  target._o = markOnce;
  target._n = toNumber;
  target._s = toString;
  target._l = renderList;
  target._t = renderSlot;
  target._q = looseEqual;
  target._i = looseIndexOf;
  target._m = renderStatic;
  target._f = resolveFilter;
  target._k = checkKeyCodes;
  target._b = bindObjectProps;
  target._v = createTextVNode;
  target._e = createEmptyVNode;
  target._u = resolveScopedSlots;
  target._g = bindObjectListeners;
  target._d = bindDynamicKeys;
  target._p = prependModifier;
}

/*  */

function FunctionalRenderContext (
  data,
  props,
  children,
  parent,
  Ctor
) {
  var this$1 = this;

  var options = Ctor.options;
  // ensure the createElement function in functional components
  // gets a unique context - this is necessary for correct named slot check
  var contextVm;
  if (hasOwn(parent, '_uid')) {
    contextVm = Object.create(parent);
    // $flow-disable-line
    contextVm._original = parent;
  } else {
    // the context vm passed in is a functional context as well.
    // in this case we want to make sure we are able to get a hold to the
    // real context instance.
    contextVm = parent;
    // $flow-disable-line
    parent = parent._original;
  }
  var isCompiled = isTrue(options._compiled);
  var needNormalization = !isCompiled;

  this.data = data;
  this.props = props;
  this.children = children;
  this.parent = parent;
  this.listeners = data.on || emptyObject;
  this.injections = resolveInject(options.inject, parent);
  this.slots = function () {
    if (!this$1.$slots) {
      normalizeScopedSlots(
        data.scopedSlots,
        this$1.$slots = resolveSlots(children, parent)
      );
    }
    return this$1.$slots
  };

  Object.defineProperty(this, 'scopedSlots', ({
    enumerable: true,
    get: function get () {
      return normalizeScopedSlots(data.scopedSlots, this.slots())
    }
  }));

  // support for compiled functional template
  if (isCompiled) {
    // exposing $options for renderStatic()
    this.$options = options;
    // pre-resolve slots for renderSlot()
    this.$slots = this.slots();
    this.$scopedSlots = normalizeScopedSlots(data.scopedSlots, this.$slots);
  }

  if (options._scopeId) {
    this._c = function (a, b, c, d) {
      var vnode = createElement(contextVm, a, b, c, d, needNormalization);
      if (vnode && !Array.isArray(vnode)) {
        vnode.fnScopeId = options._scopeId;
        vnode.fnContext = parent;
      }
      return vnode
    };
  } else {
    this._c = function (a, b, c, d) { return createElement(contextVm, a, b, c, d, needNormalization); };
  }
}

installRenderHelpers(FunctionalRenderContext.prototype);

function createFunctionalComponent (
  Ctor,
  propsData,
  data,
  contextVm,
  children
) {
  var options = Ctor.options;
  var props = {};
  var propOptions = options.props;
  if (isDef(propOptions)) {
    for (var key in propOptions) {
      props[key] = validateProp(key, propOptions, propsData || emptyObject);
    }
  } else {
    if (isDef(data.attrs)) { mergeProps(props, data.attrs); }
    if (isDef(data.props)) { mergeProps(props, data.props); }
  }

  var renderContext = new FunctionalRenderContext(
    data,
    props,
    children,
    contextVm,
    Ctor
  );

  var vnode = options.render.call(null, renderContext._c, renderContext);

  if (vnode instanceof VNode) {
    return cloneAndMarkFunctionalResult(vnode, data, renderContext.parent, options, renderContext)
  } else if (Array.isArray(vnode)) {
    var vnodes = normalizeChildren(vnode) || [];
    var res = new Array(vnodes.length);
    for (var i = 0; i < vnodes.length; i++) {
      res[i] = cloneAndMarkFunctionalResult(vnodes[i], data, renderContext.parent, options, renderContext);
    }
    return res
  }
}

function cloneAndMarkFunctionalResult (vnode, data, contextVm, options, renderContext) {
  // #7817 clone node before setting fnContext, otherwise if the node is reused
  // (e.g. it was from a cached normal slot) the fnContext causes named slots
  // that should not be matched to match.
  var clone = cloneVNode(vnode);
  clone.fnContext = contextVm;
  clone.fnOptions = options;
  if (process.env.NODE_ENV !== 'production') {
    (clone.devtoolsMeta = clone.devtoolsMeta || {}).renderContext = renderContext;
  }
  if (data.slot) {
    (clone.data || (clone.data = {})).slot = data.slot;
  }
  return clone
}

function mergeProps (to, from) {
  for (var key in from) {
    to[camelize(key)] = from[key];
  }
}

/*  */

/*  */

/*  */

/*  */

// inline hooks to be invoked on component VNodes during patch
var componentVNodeHooks = {
  init: function init (vnode, hydrating) {
    if (
      vnode.componentInstance &&
      !vnode.componentInstance._isDestroyed &&
      vnode.data.keepAlive
    ) {
      // kept-alive components, treat as a patch
      var mountedNode = vnode; // work around flow
      componentVNodeHooks.prepatch(mountedNode, mountedNode);
    } else {
      var child = vnode.componentInstance = createComponentInstanceForVnode(
        vnode,
        activeInstance
      );
      child.$mount(hydrating ? vnode.elm : undefined, hydrating);
    }
  },

  prepatch: function prepatch (oldVnode, vnode) {
    var options = vnode.componentOptions;
    var child = vnode.componentInstance = oldVnode.componentInstance;
    updateChildComponent(
      child,
      options.propsData, // updated props
      options.listeners, // updated listeners
      vnode, // new parent vnode
      options.children // new children
    );
  },

  insert: function insert (vnode) {
    var context = vnode.context;
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isMounted) {
      componentInstance._isMounted = true;
      callHook(componentInstance, 'mounted');
    }
    if (vnode.data.keepAlive) {
      if (context._isMounted) {
        // vue-router#1212
        // During updates, a kept-alive component's child components may
        // change, so directly walking the tree here may call activated hooks
        // on incorrect children. Instead we push them into a queue which will
        // be processed after the whole patch process ended.
        queueActivatedComponent(componentInstance);
      } else {
        activateChildComponent(componentInstance, true /* direct */);
      }
    }
  },

  destroy: function destroy (vnode) {
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isDestroyed) {
      if (!vnode.data.keepAlive) {
        componentInstance.$destroy();
      } else {
        deactivateChildComponent(componentInstance, true /* direct */);
      }
    }
  }
};

var hooksToMerge = Object.keys(componentVNodeHooks);

function createComponent (
  Ctor,
  data,
  context,
  children,
  tag
) {
  if (isUndef(Ctor)) {
    return
  }

  var baseCtor = context.$options._base;

  // plain options object: turn it into a constructor
  if (isObject(Ctor)) {
    Ctor = baseCtor.extend(Ctor);
  }

  // if at this stage it's not a constructor or an async component factory,
  // reject.
  if (typeof Ctor !== 'function') {
    if (process.env.NODE_ENV !== 'production') {
      warn(("Invalid Component definition: " + (String(Ctor))), context);
    }
    return
  }

  // async component
  var asyncFactory;
  if (isUndef(Ctor.cid)) {
    asyncFactory = Ctor;
    Ctor = resolveAsyncComponent(asyncFactory, baseCtor);
    if (Ctor === undefined) {
      // return a placeholder node for async component, which is rendered
      // as a comment node but preserves all the raw information for the node.
      // the information will be used for async server-rendering and hydration.
      return createAsyncPlaceholder(
        asyncFactory,
        data,
        context,
        children,
        tag
      )
    }
  }

  data = data || {};

  // resolve constructor options in case global mixins are applied after
  // component constructor creation
  resolveConstructorOptions(Ctor);

  // transform component v-model data into props & events
  if (isDef(data.model)) {
    transformModel(Ctor.options, data);
  }

  // extract props
  var propsData = extractPropsFromVNodeData(data, Ctor, tag);

  // functional component
  if (isTrue(Ctor.options.functional)) {
    return createFunctionalComponent(Ctor, propsData, data, context, children)
  }

  // extract listeners, since these needs to be treated as
  // child component listeners instead of DOM listeners
  var listeners = data.on;
  // replace with listeners with .native modifier
  // so it gets processed during parent component patch.
  data.on = data.nativeOn;

  if (isTrue(Ctor.options.abstract)) {
    // abstract components do not keep anything
    // other than props & listeners & slot

    // work around flow
    var slot = data.slot;
    data = {};
    if (slot) {
      data.slot = slot;
    }
  }

  // install component management hooks onto the placeholder node
  installComponentHooks(data);

  // return a placeholder vnode
  var name = Ctor.options.name || tag;
  var vnode = new VNode(
    ("vue-component-" + (Ctor.cid) + (name ? ("-" + name) : '')),
    data, undefined, undefined, undefined, context,
    { Ctor: Ctor, propsData: propsData, listeners: listeners, tag: tag, children: children },
    asyncFactory
  );

  return vnode
}

function createComponentInstanceForVnode (
  vnode, // we know it's MountedComponentVNode but flow doesn't
  parent // activeInstance in lifecycle state
) {
  var options = {
    _isComponent: true,
    _parentVnode: vnode,
    parent: parent
  };
  // check inline-template render functions
  var inlineTemplate = vnode.data.inlineTemplate;
  if (isDef(inlineTemplate)) {
    options.render = inlineTemplate.render;
    options.staticRenderFns = inlineTemplate.staticRenderFns;
  }
  return new vnode.componentOptions.Ctor(options)
}

function installComponentHooks (data) {
  var hooks = data.hook || (data.hook = {});
  for (var i = 0; i < hooksToMerge.length; i++) {
    var key = hooksToMerge[i];
    var existing = hooks[key];
    var toMerge = componentVNodeHooks[key];
    if (existing !== toMerge && !(existing && existing._merged)) {
      hooks[key] = existing ? mergeHook$1(toMerge, existing) : toMerge;
    }
  }
}

function mergeHook$1 (f1, f2) {
  var merged = function (a, b) {
    // flow complains about extra args which is why we use any
    f1(a, b);
    f2(a, b);
  };
  merged._merged = true;
  return merged
}

// transform component v-model info (value and callback) into
// prop and event handler respectively.
function transformModel (options, data) {
  var prop = (options.model && options.model.prop) || 'value';
  var event = (options.model && options.model.event) || 'input'
  ;(data.attrs || (data.attrs = {}))[prop] = data.model.value;
  var on = data.on || (data.on = {});
  var existing = on[event];
  var callback = data.model.callback;
  if (isDef(existing)) {
    if (
      Array.isArray(existing)
        ? existing.indexOf(callback) === -1
        : existing !== callback
    ) {
      on[event] = [callback].concat(existing);
    }
  } else {
    on[event] = callback;
  }
}

/*  */

var SIMPLE_NORMALIZE = 1;
var ALWAYS_NORMALIZE = 2;

// wrapper function for providing a more flexible interface
// without getting yelled at by flow
function createElement (
  context,
  tag,
  data,
  children,
  normalizationType,
  alwaysNormalize
) {
  if (Array.isArray(data) || isPrimitive(data)) {
    normalizationType = children;
    children = data;
    data = undefined;
  }
  if (isTrue(alwaysNormalize)) {
    normalizationType = ALWAYS_NORMALIZE;
  }
  return _createElement(context, tag, data, children, normalizationType)
}

function _createElement (
  context,
  tag,
  data,
  children,
  normalizationType
) {
  if (isDef(data) && isDef((data).__ob__)) {
    process.env.NODE_ENV !== 'production' && warn(
      "Avoid using observed data object as vnode data: " + (JSON.stringify(data)) + "\n" +
      'Always create fresh vnode data objects in each render!',
      context
    );
    return createEmptyVNode()
  }
  // object syntax in v-bind
  if (isDef(data) && isDef(data.is)) {
    tag = data.is;
  }
  if (!tag) {
    // in case of component :is set to falsy value
    return createEmptyVNode()
  }
  // warn against non-primitive key
  if (process.env.NODE_ENV !== 'production' &&
    isDef(data) && isDef(data.key) && !isPrimitive(data.key)
  ) {
    {
      warn(
        'Avoid using non-primitive value as key, ' +
        'use string/number value instead.',
        context
      );
    }
  }
  // support single function children as default scoped slot
  if (Array.isArray(children) &&
    typeof children[0] === 'function'
  ) {
    data = data || {};
    data.scopedSlots = { default: children[0] };
    children.length = 0;
  }
  if (normalizationType === ALWAYS_NORMALIZE) {
    children = normalizeChildren(children);
  } else if (normalizationType === SIMPLE_NORMALIZE) {
    children = simpleNormalizeChildren(children);
  }
  var vnode, ns;
  if (typeof tag === 'string') {
    var Ctor;
    ns = (context.$vnode && context.$vnode.ns) || config.getTagNamespace(tag);
    if (config.isReservedTag(tag)) {
      // platform built-in elements
      vnode = new VNode(
        config.parsePlatformTagName(tag), data, children,
        undefined, undefined, context
      );
    } else if ((!data || !data.pre) && isDef(Ctor = resolveAsset(context.$options, 'components', tag))) {
      // component
      vnode = createComponent(Ctor, data, context, children, tag);
    } else {
      // unknown or unlisted namespaced elements
      // check at runtime because it may get assigned a namespace when its
      // parent normalizes children
      vnode = new VNode(
        tag, data, children,
        undefined, undefined, context
      );
    }
  } else {
    // direct component options / constructor
    vnode = createComponent(tag, data, context, children);
  }
  if (Array.isArray(vnode)) {
    return vnode
  } else if (isDef(vnode)) {
    if (isDef(ns)) { applyNS(vnode, ns); }
    if (isDef(data)) { registerDeepBindings(data); }
    return vnode
  } else {
    return createEmptyVNode()
  }
}

function applyNS (vnode, ns, force) {
  vnode.ns = ns;
  if (vnode.tag === 'foreignObject') {
    // use default namespace inside foreignObject
    ns = undefined;
    force = true;
  }
  if (isDef(vnode.children)) {
    for (var i = 0, l = vnode.children.length; i < l; i++) {
      var child = vnode.children[i];
      if (isDef(child.tag) && (
        isUndef(child.ns) || (isTrue(force) && child.tag !== 'svg'))) {
        applyNS(child, ns, force);
      }
    }
  }
}

// ref #5318
// necessary to ensure parent re-render when deep bindings like :style and
// :class are used on slot nodes
function registerDeepBindings (data) {
  if (isObject(data.style)) {
    traverse(data.style);
  }
  if (isObject(data.class)) {
    traverse(data.class);
  }
}

/*  */

function initRender (vm) {
  vm._vnode = null; // the root of the child tree
  vm._staticTrees = null; // v-once cached trees
  var options = vm.$options;
  var parentVnode = vm.$vnode = options._parentVnode; // the placeholder node in parent tree
  var renderContext = parentVnode && parentVnode.context;
  vm.$slots = resolveSlots(options._renderChildren, renderContext);
  vm.$scopedSlots = emptyObject;
  // bind the createElement fn to this instance
  // so that we get proper render context inside it.
  // args order: tag, data, children, normalizationType, alwaysNormalize
  // internal version is used by render functions compiled from templates
  vm._c = function (a, b, c, d) { return createElement(vm, a, b, c, d, false); };
  // normalization is always applied for the public version, used in
  // user-written render functions.
  vm.$createElement = function (a, b, c, d) { return createElement(vm, a, b, c, d, true); };

  // $attrs & $listeners are exposed for easier HOC creation.
  // they need to be reactive so that HOCs using them are always updated
  var parentData = parentVnode && parentVnode.data;

  /* istanbul ignore else */
  if (process.env.NODE_ENV !== 'production') {
    defineReactive$$1(vm, '$attrs', parentData && parentData.attrs || emptyObject, function () {
      !isUpdatingChildComponent && warn("$attrs is readonly.", vm);
    }, true);
    defineReactive$$1(vm, '$listeners', options._parentListeners || emptyObject, function () {
      !isUpdatingChildComponent && warn("$listeners is readonly.", vm);
    }, true);
  } else {
    defineReactive$$1(vm, '$attrs', parentData && parentData.attrs || emptyObject, null, true);
    defineReactive$$1(vm, '$listeners', options._parentListeners || emptyObject, null, true);
  }
}

var currentRenderingInstance = null;

function renderMixin (Vue) {
  // install runtime convenience helpers
  installRenderHelpers(Vue.prototype);

  Vue.prototype.$nextTick = function (fn) {
    return nextTick(fn, this)
  };

  Vue.prototype._render = function () {
    var vm = this;
    var ref = vm.$options;
    var render = ref.render;
    var _parentVnode = ref._parentVnode;

    if (_parentVnode) {
      vm.$scopedSlots = normalizeScopedSlots(
        _parentVnode.data.scopedSlots,
        vm.$slots,
        vm.$scopedSlots
      );
    }

    // set parent vnode. this allows render functions to have access
    // to the data on the placeholder node.
    vm.$vnode = _parentVnode;
    // render self
    var vnode;
    try {
      // There's no need to maintain a stack becaues all render fns are called
      // separately from one another. Nested component's render fns are called
      // when parent component is patched.
      currentRenderingInstance = vm;
      vnode = render.call(vm._renderProxy, vm.$createElement);
    } catch (e) {
      handleError(e, vm, "render");
      // return error render result,
      // or previous vnode to prevent render error causing blank component
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production' && vm.$options.renderError) {
        try {
          vnode = vm.$options.renderError.call(vm._renderProxy, vm.$createElement, e);
        } catch (e) {
          handleError(e, vm, "renderError");
          vnode = vm._vnode;
        }
      } else {
        vnode = vm._vnode;
      }
    } finally {
      currentRenderingInstance = null;
    }
    // if the returned array contains only a single node, allow it
    if (Array.isArray(vnode) && vnode.length === 1) {
      vnode = vnode[0];
    }
    // return empty vnode in case the render function errored out
    if (!(vnode instanceof VNode)) {
      if (process.env.NODE_ENV !== 'production' && Array.isArray(vnode)) {
        warn(
          'Multiple root nodes returned from render function. Render function ' +
          'should return a single root node.',
          vm
        );
      }
      vnode = createEmptyVNode();
    }
    // set parent
    vnode.parent = _parentVnode;
    return vnode
  };
}

/*  */

function ensureCtor (comp, base) {
  if (
    comp.__esModule ||
    (hasSymbol && comp[Symbol.toStringTag] === 'Module')
  ) {
    comp = comp.default;
  }
  return isObject(comp)
    ? base.extend(comp)
    : comp
}

function createAsyncPlaceholder (
  factory,
  data,
  context,
  children,
  tag
) {
  var node = createEmptyVNode();
  node.asyncFactory = factory;
  node.asyncMeta = { data: data, context: context, children: children, tag: tag };
  return node
}

function resolveAsyncComponent (
  factory,
  baseCtor
) {
  if (isTrue(factory.error) && isDef(factory.errorComp)) {
    return factory.errorComp
  }

  if (isDef(factory.resolved)) {
    return factory.resolved
  }

  if (isTrue(factory.loading) && isDef(factory.loadingComp)) {
    return factory.loadingComp
  }

  var owner = currentRenderingInstance;
  if (isDef(factory.owners)) {
    // already pending
    factory.owners.push(owner);
  } else {
    var owners = factory.owners = [owner];
    var sync = true;

    var forceRender = function (renderCompleted) {
      for (var i = 0, l = owners.length; i < l; i++) {
        (owners[i]).$forceUpdate();
      }

      if (renderCompleted) {
        owners.length = 0;
      }
    };

    var resolve = once(function (res) {
      // cache resolved
      factory.resolved = ensureCtor(res, baseCtor);
      // invoke callbacks only if this is not a synchronous resolve
      // (async resolves are shimmed as synchronous during SSR)
      if (!sync) {
        forceRender(true);
      } else {
        owners.length = 0;
      }
    });

    var reject = once(function (reason) {
      process.env.NODE_ENV !== 'production' && warn(
        "Failed to resolve async component: " + (String(factory)) +
        (reason ? ("\nReason: " + reason) : '')
      );
      if (isDef(factory.errorComp)) {
        factory.error = true;
        forceRender(true);
      }
    });

    var res = factory(resolve, reject);

    if (isObject(res)) {
      if (isPromise(res)) {
        // () => Promise
        if (isUndef(factory.resolved)) {
          res.then(resolve, reject);
        }
      } else if (isPromise(res.component)) {
        res.component.then(resolve, reject);

        if (isDef(res.error)) {
          factory.errorComp = ensureCtor(res.error, baseCtor);
        }

        if (isDef(res.loading)) {
          factory.loadingComp = ensureCtor(res.loading, baseCtor);
          if (res.delay === 0) {
            factory.loading = true;
          } else {
            setTimeout(function () {
              if (isUndef(factory.resolved) && isUndef(factory.error)) {
                factory.loading = true;
                forceRender(false);
              }
            }, res.delay || 200);
          }
        }

        if (isDef(res.timeout)) {
          setTimeout(function () {
            if (isUndef(factory.resolved)) {
              reject(
                process.env.NODE_ENV !== 'production'
                  ? ("timeout (" + (res.timeout) + "ms)")
                  : null
              );
            }
          }, res.timeout);
        }
      }
    }

    sync = false;
    // return in case resolved synchronously
    return factory.loading
      ? factory.loadingComp
      : factory.resolved
  }
}

/*  */

function isAsyncPlaceholder (node) {
  return node.isComment && node.asyncFactory
}

/*  */

function getFirstComponentChild (children) {
  if (Array.isArray(children)) {
    for (var i = 0; i < children.length; i++) {
      var c = children[i];
      if (isDef(c) && (isDef(c.componentOptions) || isAsyncPlaceholder(c))) {
        return c
      }
    }
  }
}

/*  */

/*  */

function initEvents (vm) {
  vm._events = Object.create(null);
  vm._hasHookEvent = false;
  // init parent attached events
  var listeners = vm.$options._parentListeners;
  if (listeners) {
    updateComponentListeners(vm, listeners);
  }
}

var target;

function add (event, fn) {
  target.$on(event, fn);
}

function remove$1 (event, fn) {
  target.$off(event, fn);
}

function createOnceHandler (event, fn) {
  var _target = target;
  return function onceHandler () {
    var res = fn.apply(null, arguments);
    if (res !== null) {
      _target.$off(event, onceHandler);
    }
  }
}

function updateComponentListeners (
  vm,
  listeners,
  oldListeners
) {
  target = vm;
  updateListeners(listeners, oldListeners || {}, add, remove$1, createOnceHandler, vm);
  target = undefined;
}

function eventsMixin (Vue) {
  var hookRE = /^hook:/;
  Vue.prototype.$on = function (event, fn) {
    var vm = this;
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        vm.$on(event[i], fn);
      }
    } else {
      (vm._events[event] || (vm._events[event] = [])).push(fn);
      // optimize hook:event cost by using a boolean flag marked at registration
      // instead of a hash lookup
      if (hookRE.test(event)) {
        vm._hasHookEvent = true;
      }
    }
    return vm
  };

  Vue.prototype.$once = function (event, fn) {
    var vm = this;
    function on () {
      vm.$off(event, on);
      fn.apply(vm, arguments);
    }
    on.fn = fn;
    vm.$on(event, on);
    return vm
  };

  Vue.prototype.$off = function (event, fn) {
    var vm = this;
    // all
    if (!arguments.length) {
      vm._events = Object.create(null);
      return vm
    }
    // array of events
    if (Array.isArray(event)) {
      for (var i$1 = 0, l = event.length; i$1 < l; i$1++) {
        vm.$off(event[i$1], fn);
      }
      return vm
    }
    // specific event
    var cbs = vm._events[event];
    if (!cbs) {
      return vm
    }
    if (!fn) {
      vm._events[event] = null;
      return vm
    }
    // specific handler
    var cb;
    var i = cbs.length;
    while (i--) {
      cb = cbs[i];
      if (cb === fn || cb.fn === fn) {
        cbs.splice(i, 1);
        break
      }
    }
    return vm
  };

  Vue.prototype.$emit = function (event) {
    var vm = this;
    if (process.env.NODE_ENV !== 'production') {
      var lowerCaseEvent = event.toLowerCase();
      if (lowerCaseEvent !== event && vm._events[lowerCaseEvent]) {
        tip(
          "Event \"" + lowerCaseEvent + "\" is emitted in component " +
          (formatComponentName(vm)) + " but the handler is registered for \"" + event + "\". " +
          "Note that HTML attributes are case-insensitive and you cannot use " +
          "v-on to listen to camelCase events when using in-DOM templates. " +
          "You should probably use \"" + (hyphenate(event)) + "\" instead of \"" + event + "\"."
        );
      }
    }
    var cbs = vm._events[event];
    if (cbs) {
      cbs = cbs.length > 1 ? toArray(cbs) : cbs;
      var args = toArray(arguments, 1);
      var info = "event handler for \"" + event + "\"";
      for (var i = 0, l = cbs.length; i < l; i++) {
        invokeWithErrorHandling(cbs[i], vm, args, vm, info);
      }
    }
    return vm
  };
}

/*  */

var activeInstance = null;
var isUpdatingChildComponent = false;

function setActiveInstance(vm) {
  var prevActiveInstance = activeInstance;
  activeInstance = vm;
  return function () {
    activeInstance = prevActiveInstance;
  }
}

function initLifecycle (vm) {
  var options = vm.$options;

  // locate first non-abstract parent
  var parent = options.parent;
  if (parent && !options.abstract) {
    while (parent.$options.abstract && parent.$parent) {
      parent = parent.$parent;
    }
    parent.$children.push(vm);
  }

  vm.$parent = parent;
  vm.$root = parent ? parent.$root : vm;

  vm.$children = [];
  vm.$refs = {};

  vm._watcher = null;
  vm._inactive = null;
  vm._directInactive = false;
  vm._isMounted = false;
  vm._isDestroyed = false;
  vm._isBeingDestroyed = false;
}

function lifecycleMixin (Vue) {
  Vue.prototype._update = function (vnode, hydrating) {
    var vm = this;
    var prevEl = vm.$el;
    var prevVnode = vm._vnode;
    var restoreActiveInstance = setActiveInstance(vm);
    vm._vnode = vnode;
    // Vue.prototype.__patch__ is injected in entry points
    // based on the rendering backend used.
    if (!prevVnode) {
      // initial render
      vm.$el = vm.__patch__(vm.$el, vnode, hydrating, false /* removeOnly */);
    } else {
      // updates
      vm.$el = vm.__patch__(prevVnode, vnode);
    }
    restoreActiveInstance();
    // update __vue__ reference
    if (prevEl) {
      prevEl.__vue__ = null;
    }
    if (vm.$el) {
      vm.$el.__vue__ = vm;
    }
    // if parent is an HOC, update its $el as well
    if (vm.$vnode && vm.$parent && vm.$vnode === vm.$parent._vnode) {
      vm.$parent.$el = vm.$el;
    }
    // updated hook is called by the scheduler to ensure that children are
    // updated in a parent's updated hook.
  };

  Vue.prototype.$forceUpdate = function () {
    var vm = this;
    if (vm._watcher) {
      vm._watcher.update();
    }
  };

  Vue.prototype.$destroy = function () {
    var vm = this;
    if (vm._isBeingDestroyed) {
      return
    }
    callHook(vm, 'beforeDestroy');
    vm._isBeingDestroyed = true;
    // remove self from parent
    var parent = vm.$parent;
    if (parent && !parent._isBeingDestroyed && !vm.$options.abstract) {
      remove(parent.$children, vm);
    }
    // teardown watchers
    if (vm._watcher) {
      vm._watcher.teardown();
    }
    var i = vm._watchers.length;
    while (i--) {
      vm._watchers[i].teardown();
    }
    // remove reference from data ob
    // frozen object may not have observer.
    if (vm._data.__ob__) {
      vm._data.__ob__.vmCount--;
    }
    // call the last hook...
    vm._isDestroyed = true;
    // invoke destroy hooks on current rendered tree
    vm.__patch__(vm._vnode, null);
    // fire destroyed hook
    callHook(vm, 'destroyed');
    // turn off all instance listeners.
    vm.$off();
    // remove __vue__ reference
    if (vm.$el) {
      vm.$el.__vue__ = null;
    }
    // release circular reference (#6759)
    if (vm.$vnode) {
      vm.$vnode.parent = null;
    }
  };
}

function mountComponent (
  vm,
  el,
  hydrating
) {
  vm.$el = el;
  if (!vm.$options.render) {
    vm.$options.render = createEmptyVNode;
    if (process.env.NODE_ENV !== 'production') {
      /* istanbul ignore if */
      if ((vm.$options.template && vm.$options.template.charAt(0) !== '#') ||
        vm.$options.el || el) {
        warn(
          'You are using the runtime-only build of Vue where the template ' +
          'compiler is not available. Either pre-compile the templates into ' +
          'render functions, or use the compiler-included build.',
          vm
        );
      } else {
        warn(
          'Failed to mount component: template or render function not defined.',
          vm
        );
      }
    }
  }
  callHook(vm, 'beforeMount');

  var updateComponent;
  /* istanbul ignore if */
  if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
    updateComponent = function () {
      var name = vm._name;
      var id = vm._uid;
      var startTag = "vue-perf-start:" + id;
      var endTag = "vue-perf-end:" + id;

      mark(startTag);
      var vnode = vm._render();
      mark(endTag);
      measure(("vue " + name + " render"), startTag, endTag);

      mark(startTag);
      vm._update(vnode, hydrating);
      mark(endTag);
      measure(("vue " + name + " patch"), startTag, endTag);
    };
  } else {
    updateComponent = function () {
      vm._update(vm._render(), hydrating);
    };
  }

  // we set this to vm._watcher inside the watcher's constructor
  // since the watcher's initial patch may call $forceUpdate (e.g. inside child
  // component's mounted hook), which relies on vm._watcher being already defined
  new Watcher(vm, updateComponent, noop, {
    before: function before () {
      if (vm._isMounted && !vm._isDestroyed) {
        callHook(vm, 'beforeUpdate');
      }
    }
  }, true /* isRenderWatcher */);
  hydrating = false;

  // manually mounted instance, call mounted on self
  // mounted is called for render-created child components in its inserted hook
  if (vm.$vnode == null) {
    vm._isMounted = true;
    callHook(vm, 'mounted');
  }
  return vm
}

function updateChildComponent (
  vm,
  propsData,
  listeners,
  parentVnode,
  renderChildren
) {
  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = true;
  }

  // determine whether component has slot children
  // we need to do this before overwriting $options._renderChildren.

  // check if there are dynamic scopedSlots (hand-written or compiled but with
  // dynamic slot names). Static scoped slots compiled from template has the
  // "$stable" marker.
  var hasDynamicScopedSlot = !!(
    (parentVnode.data.scopedSlots && !parentVnode.data.scopedSlots.$stable) ||
    (vm.$scopedSlots !== emptyObject && !vm.$scopedSlots.$stable)
  );

  // Any static slot children from the parent may have changed during parent's
  // update. Dynamic scoped slots may also have changed. In such cases, a forced
  // update is necessary to ensure correctness.
  var needsForceUpdate = !!(
    renderChildren ||               // has new static slots
    vm.$options._renderChildren ||  // has old static slots
    hasDynamicScopedSlot
  );

  vm.$options._parentVnode = parentVnode;
  vm.$vnode = parentVnode; // update vm's placeholder node without re-render

  if (vm._vnode) { // update child tree's parent
    vm._vnode.parent = parentVnode;
  }
  vm.$options._renderChildren = renderChildren;

  // update $attrs and $listeners hash
  // these are also reactive so they may trigger child update if the child
  // used them during render
  vm.$attrs = parentVnode.data.attrs || emptyObject;
  vm.$listeners = listeners || emptyObject;

  // update props
  if (propsData && vm.$options.props) {
    toggleObserving(false);
    var props = vm._props;
    var propKeys = vm.$options._propKeys || [];
    for (var i = 0; i < propKeys.length; i++) {
      var key = propKeys[i];
      var propOptions = vm.$options.props; // wtf flow?
      props[key] = validateProp(key, propOptions, propsData, vm);
    }
    toggleObserving(true);
    // keep a copy of raw propsData
    vm.$options.propsData = propsData;
  }

  // update listeners
  listeners = listeners || emptyObject;
  var oldListeners = vm.$options._parentListeners;
  vm.$options._parentListeners = listeners;
  updateComponentListeners(vm, listeners, oldListeners);

  // resolve slots + force update if has children
  if (needsForceUpdate) {
    vm.$slots = resolveSlots(renderChildren, parentVnode.context);
    vm.$forceUpdate();
  }

  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = false;
  }
}

function isInInactiveTree (vm) {
  while (vm && (vm = vm.$parent)) {
    if (vm._inactive) { return true }
  }
  return false
}

function activateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = false;
    if (isInInactiveTree(vm)) {
      return
    }
  } else if (vm._directInactive) {
    return
  }
  if (vm._inactive || vm._inactive === null) {
    vm._inactive = false;
    for (var i = 0; i < vm.$children.length; i++) {
      activateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'activated');
  }
}

function deactivateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = true;
    if (isInInactiveTree(vm)) {
      return
    }
  }
  if (!vm._inactive) {
    vm._inactive = true;
    for (var i = 0; i < vm.$children.length; i++) {
      deactivateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'deactivated');
  }
}

function callHook (vm, hook) {
  // #7573 disable dep collection when invoking lifecycle hooks
  pushTarget();
  var handlers = vm.$options[hook];
  var info = hook + " hook";
  if (handlers) {
    for (var i = 0, j = handlers.length; i < j; i++) {
      invokeWithErrorHandling(handlers[i], vm, null, vm, info);
    }
  }
  if (vm._hasHookEvent) {
    vm.$emit('hook:' + hook);
  }
  popTarget();
}

/*  */

var MAX_UPDATE_COUNT = 100;

var queue = [];
var activatedChildren = [];
var has = {};
var circular = {};
var waiting = false;
var flushing = false;
var index = 0;

/**
 * Reset the scheduler's state.
 */
function resetSchedulerState () {
  index = queue.length = activatedChildren.length = 0;
  has = {};
  if (process.env.NODE_ENV !== 'production') {
    circular = {};
  }
  waiting = flushing = false;
}

// Async edge case #6566 requires saving the timestamp when event listeners are
// attached. However, calling performance.now() has a perf overhead especially
// if the page has thousands of event listeners. Instead, we take a timestamp
// every time the scheduler flushes and use that for all event listeners
// attached during that flush.
var currentFlushTimestamp = 0;

// Async edge case fix requires storing an event listener's attach timestamp.
var getNow = Date.now;

// Determine what event timestamp the browser is using. Annoyingly, the
// timestamp can either be hi-res (relative to page load) or low-res
// (relative to UNIX epoch), so in order to compare time we have to use the
// same timestamp type when saving the flush timestamp.
if (inBrowser && getNow() > document.createEvent('Event').timeStamp) {
  // if the low-res timestamp which is bigger than the event timestamp
  // (which is evaluated AFTER) it means the event is using a hi-res timestamp,
  // and we need to use the hi-res version for event listeners as well.
  getNow = function () { return performance.now(); };
}

/**
 * Flush both queues and run the watchers.
 */
function flushSchedulerQueue () {
  currentFlushTimestamp = getNow();
  flushing = true;
  var watcher, id;

  // Sort queue before flush.
  // This ensures that:
  // 1. Components are updated from parent to child. (because parent is always
  //    created before the child)
  // 2. A component's user watchers are run before its render watcher (because
  //    user watchers are created before the render watcher)
  // 3. If a component is destroyed during a parent component's watcher run,
  //    its watchers can be skipped.
  queue.sort(function (a, b) { return a.id - b.id; });

  // do not cache length because more watchers might be pushed
  // as we run existing watchers
  for (index = 0; index < queue.length; index++) {
    watcher = queue[index];
    if (watcher.before) {
      watcher.before();
    }
    id = watcher.id;
    has[id] = null;
    watcher.run();
    // in dev build, check and stop circular updates.
    if (process.env.NODE_ENV !== 'production' && has[id] != null) {
      circular[id] = (circular[id] || 0) + 1;
      if (circular[id] > MAX_UPDATE_COUNT) {
        warn(
          'You may have an infinite update loop ' + (
            watcher.user
              ? ("in watcher with expression \"" + (watcher.expression) + "\"")
              : "in a component render function."
          ),
          watcher.vm
        );
        break
      }
    }
  }

  // keep copies of post queues before resetting state
  var activatedQueue = activatedChildren.slice();
  var updatedQueue = queue.slice();

  resetSchedulerState();

  // call component updated and activated hooks
  callActivatedHooks(activatedQueue);
  callUpdatedHooks(updatedQueue);

  // devtool hook
  /* istanbul ignore if */
  if (devtools && config.devtools) {
    devtools.emit('flush');
  }
}

function callUpdatedHooks (queue) {
  var i = queue.length;
  while (i--) {
    var watcher = queue[i];
    var vm = watcher.vm;
    if (vm._watcher === watcher && vm._isMounted && !vm._isDestroyed) {
      callHook(vm, 'updated');
    }
  }
}

/**
 * Queue a kept-alive component that was activated during patch.
 * The queue will be processed after the entire tree has been patched.
 */
function queueActivatedComponent (vm) {
  // setting _inactive to false here so that a render function can
  // rely on checking whether it's in an inactive tree (e.g. router-view)
  vm._inactive = false;
  activatedChildren.push(vm);
}

function callActivatedHooks (queue) {
  for (var i = 0; i < queue.length; i++) {
    queue[i]._inactive = true;
    activateChildComponent(queue[i], true /* true */);
  }
}

/**
 * Push a watcher into the watcher queue.
 * Jobs with duplicate IDs will be skipped unless it's
 * pushed when the queue is being flushed.
 */
function queueWatcher (watcher) {
  var id = watcher.id;
  if (has[id] == null) {
    has[id] = true;
    if (!flushing) {
      queue.push(watcher);
    } else {
      // if already flushing, splice the watcher based on its id
      // if already past its id, it will be run next immediately.
      var i = queue.length - 1;
      while (i > index && queue[i].id > watcher.id) {
        i--;
      }
      queue.splice(i + 1, 0, watcher);
    }
    // queue the flush
    if (!waiting) {
      waiting = true;

      if (process.env.NODE_ENV !== 'production' && !config.async) {
        flushSchedulerQueue();
        return
      }
      nextTick(flushSchedulerQueue);
    }
  }
}

/*  */



var uid$2 = 0;

/**
 * A watcher parses an expression, collects dependencies,
 * and fires callback when the expression value changes.
 * This is used for both the $watch() api and directives.
 */
var Watcher = function Watcher (
  vm,
  expOrFn,
  cb,
  options,
  isRenderWatcher
) {
  this.vm = vm;
  if (isRenderWatcher) {
    vm._watcher = this;
  }
  vm._watchers.push(this);
  // options
  if (options) {
    this.deep = !!options.deep;
    this.user = !!options.user;
    this.lazy = !!options.lazy;
    this.sync = !!options.sync;
    this.before = options.before;
  } else {
    this.deep = this.user = this.lazy = this.sync = false;
  }
  this.cb = cb;
  this.id = ++uid$2; // uid for batching
  this.active = true;
  this.dirty = this.lazy; // for lazy watchers
  this.deps = [];
  this.newDeps = [];
  this.depIds = new _Set();
  this.newDepIds = new _Set();
  this.expression = process.env.NODE_ENV !== 'production'
    ? expOrFn.toString()
    : '';
  // parse expression for getter
  if (typeof expOrFn === 'function') {
    this.getter = expOrFn;
  } else {
    this.getter = parsePath(expOrFn);
    if (!this.getter) {
      this.getter = noop;
      process.env.NODE_ENV !== 'production' && warn(
        "Failed watching path: \"" + expOrFn + "\" " +
        'Watcher only accepts simple dot-delimited paths. ' +
        'For full control, use a function instead.',
        vm
      );
    }
  }
  this.value = this.lazy
    ? undefined
    : this.get();
};

/**
 * Evaluate the getter, and re-collect dependencies.
 */
Watcher.prototype.get = function get () {
  pushTarget(this);
  var value;
  var vm = this.vm;
  try {
    value = this.getter.call(vm, vm);
  } catch (e) {
    if (this.user) {
      handleError(e, vm, ("getter for watcher \"" + (this.expression) + "\""));
    } else {
      throw e
    }
  } finally {
    // "touch" every property so they are all tracked as
    // dependencies for deep watching
    if (this.deep) {
      traverse(value);
    }
    popTarget();
    this.cleanupDeps();
  }
  return value
};

/**
 * Add a dependency to this directive.
 */
Watcher.prototype.addDep = function addDep (dep) {
  var id = dep.id;
  if (!this.newDepIds.has(id)) {
    this.newDepIds.add(id);
    this.newDeps.push(dep);
    if (!this.depIds.has(id)) {
      dep.addSub(this);
    }
  }
};

/**
 * Clean up for dependency collection.
 */
Watcher.prototype.cleanupDeps = function cleanupDeps () {
  var i = this.deps.length;
  while (i--) {
    var dep = this.deps[i];
    if (!this.newDepIds.has(dep.id)) {
      dep.removeSub(this);
    }
  }
  var tmp = this.depIds;
  this.depIds = this.newDepIds;
  this.newDepIds = tmp;
  this.newDepIds.clear();
  tmp = this.deps;
  this.deps = this.newDeps;
  this.newDeps = tmp;
  this.newDeps.length = 0;
};

/**
 * Subscriber interface.
 * Will be called when a dependency changes.
 */
Watcher.prototype.update = function update () {
  /* istanbul ignore else */
  if (this.lazy) {
    this.dirty = true;
  } else if (this.sync) {
    this.run();
  } else {
    queueWatcher(this);
  }
};

/**
 * Scheduler job interface.
 * Will be called by the scheduler.
 */
Watcher.prototype.run = function run () {
  if (this.active) {
    var value = this.get();
    if (
      value !== this.value ||
      // Deep watchers and watchers on Object/Arrays should fire even
      // when the value is the same, because the value may
      // have mutated.
      isObject(value) ||
      this.deep
    ) {
      // set new value
      var oldValue = this.value;
      this.value = value;
      if (this.user) {
        try {
          this.cb.call(this.vm, value, oldValue);
        } catch (e) {
          handleError(e, this.vm, ("callback for watcher \"" + (this.expression) + "\""));
        }
      } else {
        this.cb.call(this.vm, value, oldValue);
      }
    }
  }
};

/**
 * Evaluate the value of the watcher.
 * This only gets called for lazy watchers.
 */
Watcher.prototype.evaluate = function evaluate () {
  this.value = this.get();
  this.dirty = false;
};

/**
 * Depend on all deps collected by this watcher.
 */
Watcher.prototype.depend = function depend () {
  var i = this.deps.length;
  while (i--) {
    this.deps[i].depend();
  }
};

/**
 * Remove self from all dependencies' subscriber list.
 */
Watcher.prototype.teardown = function teardown () {
  if (this.active) {
    // remove self from vm's watcher list
    // this is a somewhat expensive operation so we skip it
    // if the vm is being destroyed.
    if (!this.vm._isBeingDestroyed) {
      remove(this.vm._watchers, this);
    }
    var i = this.deps.length;
    while (i--) {
      this.deps[i].removeSub(this);
    }
    this.active = false;
  }
};

/*  */

var sharedPropertyDefinition = {
  enumerable: true,
  configurable: true,
  get: noop,
  set: noop
};

function proxy (target, sourceKey, key) {
  sharedPropertyDefinition.get = function proxyGetter () {
    return this[sourceKey][key]
  };
  sharedPropertyDefinition.set = function proxySetter (val) {
    this[sourceKey][key] = val;
  };
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function initState (vm) {
  vm._watchers = [];
  var opts = vm.$options;
  if (opts.props) { initProps(vm, opts.props); }
  if (opts.methods) { initMethods(vm, opts.methods); }
  if (opts.data) {
    initData(vm);
  } else {
    observe(vm._data = {}, true /* asRootData */);
  }
  if (opts.computed) { initComputed(vm, opts.computed); }
  if (opts.watch && opts.watch !== nativeWatch) {
    initWatch(vm, opts.watch);
  }
}

function initProps (vm, propsOptions) {
  var propsData = vm.$options.propsData || {};
  var props = vm._props = {};
  // cache prop keys so that future props updates can iterate using Array
  // instead of dynamic object key enumeration.
  var keys = vm.$options._propKeys = [];
  var isRoot = !vm.$parent;
  // root instance props should be converted
  if (!isRoot) {
    toggleObserving(false);
  }
  var loop = function ( key ) {
    keys.push(key);
    var value = validateProp(key, propsOptions, propsData, vm);
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      var hyphenatedKey = hyphenate(key);
      if (isReservedAttribute(hyphenatedKey) ||
          config.isReservedAttr(hyphenatedKey)) {
        warn(
          ("\"" + hyphenatedKey + "\" is a reserved attribute and cannot be used as component prop."),
          vm
        );
      }
      defineReactive$$1(props, key, value, function () {
        if (!isRoot && !isUpdatingChildComponent) {
          warn(
            "Avoid mutating a prop directly since the value will be " +
            "overwritten whenever the parent component re-renders. " +
            "Instead, use a data or computed property based on the prop's " +
            "value. Prop being mutated: \"" + key + "\"",
            vm
          );
        }
      });
    } else {
      defineReactive$$1(props, key, value);
    }
    // static props are already proxied on the component's prototype
    // during Vue.extend(). We only need to proxy props defined at
    // instantiation here.
    if (!(key in vm)) {
      proxy(vm, "_props", key);
    }
  };

  for (var key in propsOptions) loop( key );
  toggleObserving(true);
}

function initData (vm) {
  var data = vm.$options.data;
  data = vm._data = typeof data === 'function'
    ? getData(data, vm)
    : data || {};
  if (!isPlainObject(data)) {
    data = {};
    process.env.NODE_ENV !== 'production' && warn(
      'data functions should return an object:\n' +
      'https://vuejs.org/v2/guide/components.html#data-Must-Be-a-Function',
      vm
    );
  }
  // proxy data on instance
  var keys = Object.keys(data);
  var props = vm.$options.props;
  var methods = vm.$options.methods;
  var i = keys.length;
  while (i--) {
    var key = keys[i];
    if (process.env.NODE_ENV !== 'production') {
      if (methods && hasOwn(methods, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a data property."),
          vm
        );
      }
    }
    if (props && hasOwn(props, key)) {
      process.env.NODE_ENV !== 'production' && warn(
        "The data property \"" + key + "\" is already declared as a prop. " +
        "Use prop default value instead.",
        vm
      );
    } else if (!isReserved(key)) {
      proxy(vm, "_data", key);
    }
  }
  // observe data
  observe(data, true /* asRootData */);
}

function getData (data, vm) {
  // #7573 disable dep collection when invoking data getters
  pushTarget();
  try {
    return data.call(vm, vm)
  } catch (e) {
    handleError(e, vm, "data()");
    return {}
  } finally {
    popTarget();
  }
}

var computedWatcherOptions = { lazy: true };

function initComputed (vm, computed) {
  // $flow-disable-line
  var watchers = vm._computedWatchers = Object.create(null);
  // computed properties are just getters during SSR
  var isSSR = isServerRendering();

  for (var key in computed) {
    var userDef = computed[key];
    var getter = typeof userDef === 'function' ? userDef : userDef.get;
    if (process.env.NODE_ENV !== 'production' && getter == null) {
      warn(
        ("Getter is missing for computed property \"" + key + "\"."),
        vm
      );
    }

    if (!isSSR) {
      // create internal watcher for the computed property.
      watchers[key] = new Watcher(
        vm,
        getter || noop,
        noop,
        computedWatcherOptions
      );
    }

    // component-defined computed properties are already defined on the
    // component prototype. We only need to define computed properties defined
    // at instantiation here.
    if (!(key in vm)) {
      defineComputed(vm, key, userDef);
    } else if (process.env.NODE_ENV !== 'production') {
      if (key in vm.$data) {
        warn(("The computed property \"" + key + "\" is already defined in data."), vm);
      } else if (vm.$options.props && key in vm.$options.props) {
        warn(("The computed property \"" + key + "\" is already defined as a prop."), vm);
      }
    }
  }
}

function defineComputed (
  target,
  key,
  userDef
) {
  var shouldCache = !isServerRendering();
  if (typeof userDef === 'function') {
    sharedPropertyDefinition.get = shouldCache
      ? createComputedGetter(key)
      : createGetterInvoker(userDef);
    sharedPropertyDefinition.set = noop;
  } else {
    sharedPropertyDefinition.get = userDef.get
      ? shouldCache && userDef.cache !== false
        ? createComputedGetter(key)
        : createGetterInvoker(userDef.get)
      : noop;
    sharedPropertyDefinition.set = userDef.set || noop;
  }
  if (process.env.NODE_ENV !== 'production' &&
      sharedPropertyDefinition.set === noop) {
    sharedPropertyDefinition.set = function () {
      warn(
        ("Computed property \"" + key + "\" was assigned to but it has no setter."),
        this
      );
    };
  }
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function createComputedGetter (key) {
  return function computedGetter () {
    var watcher = this._computedWatchers && this._computedWatchers[key];
    if (watcher) {
      if (watcher.dirty) {
        watcher.evaluate();
      }
      if (Dep.target) {
        watcher.depend();
      }
      return watcher.value
    }
  }
}

function createGetterInvoker(fn) {
  return function computedGetter () {
    return fn.call(this, this)
  }
}

function initMethods (vm, methods) {
  var props = vm.$options.props;
  for (var key in methods) {
    if (process.env.NODE_ENV !== 'production') {
      if (typeof methods[key] !== 'function') {
        warn(
          "Method \"" + key + "\" has type \"" + (typeof methods[key]) + "\" in the component definition. " +
          "Did you reference the function correctly?",
          vm
        );
      }
      if (props && hasOwn(props, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a prop."),
          vm
        );
      }
      if ((key in vm) && isReserved(key)) {
        warn(
          "Method \"" + key + "\" conflicts with an existing Vue instance method. " +
          "Avoid defining component methods that start with _ or $."
        );
      }
    }
    vm[key] = typeof methods[key] !== 'function' ? noop : bind(methods[key], vm);
  }
}

function initWatch (vm, watch) {
  for (var key in watch) {
    var handler = watch[key];
    if (Array.isArray(handler)) {
      for (var i = 0; i < handler.length; i++) {
        createWatcher(vm, key, handler[i]);
      }
    } else {
      createWatcher(vm, key, handler);
    }
  }
}

function createWatcher (
  vm,
  expOrFn,
  handler,
  options
) {
  if (isPlainObject(handler)) {
    options = handler;
    handler = handler.handler;
  }
  if (typeof handler === 'string') {
    handler = vm[handler];
  }
  return vm.$watch(expOrFn, handler, options)
}

function stateMixin (Vue) {
  // flow somehow has problems with directly declared definition object
  // when using Object.defineProperty, so we have to procedurally build up
  // the object here.
  var dataDef = {};
  dataDef.get = function () { return this._data };
  var propsDef = {};
  propsDef.get = function () { return this._props };
  if (process.env.NODE_ENV !== 'production') {
    dataDef.set = function () {
      warn(
        'Avoid replacing instance root $data. ' +
        'Use nested data properties instead.',
        this
      );
    };
    propsDef.set = function () {
      warn("$props is readonly.", this);
    };
  }
  Object.defineProperty(Vue.prototype, '$data', dataDef);
  Object.defineProperty(Vue.prototype, '$props', propsDef);

  Vue.prototype.$set = set;
  Vue.prototype.$delete = del;

  Vue.prototype.$watch = function (
    expOrFn,
    cb,
    options
  ) {
    var vm = this;
    if (isPlainObject(cb)) {
      return createWatcher(vm, expOrFn, cb, options)
    }
    options = options || {};
    options.user = true;
    var watcher = new Watcher(vm, expOrFn, cb, options);
    if (options.immediate) {
      try {
        cb.call(vm, watcher.value);
      } catch (error) {
        handleError(error, vm, ("callback for immediate watcher \"" + (watcher.expression) + "\""));
      }
    }
    return function unwatchFn () {
      watcher.teardown();
    }
  };
}

/*  */

var uid$3 = 0;

function initMixin (Vue) {
  Vue.prototype._init = function (options) {
    var vm = this;
    // a uid
    vm._uid = uid$3++;

    var startTag, endTag;
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      startTag = "vue-perf-start:" + (vm._uid);
      endTag = "vue-perf-end:" + (vm._uid);
      mark(startTag);
    }

    // a flag to avoid this being observed
    vm._isVue = true;
    // merge options
    if (options && options._isComponent) {
      // optimize internal component instantiation
      // since dynamic options merging is pretty slow, and none of the
      // internal component options needs special treatment.
      initInternalComponent(vm, options);
    } else {
      vm.$options = mergeOptions(
        resolveConstructorOptions(vm.constructor),
        options || {},
        vm
      );
    }
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      initProxy(vm);
    } else {
      vm._renderProxy = vm;
    }
    // expose real self
    vm._self = vm;
    initLifecycle(vm);
    initEvents(vm);
    initRender(vm);
    callHook(vm, 'beforeCreate');
    initInjections(vm); // resolve injections before data/props
    initState(vm);
    initProvide(vm); // resolve provide after data/props
    callHook(vm, 'created');

    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      vm._name = formatComponentName(vm, false);
      mark(endTag);
      measure(("vue " + (vm._name) + " init"), startTag, endTag);
    }

    if (vm.$options.el) {
      vm.$mount(vm.$options.el);
    }
  };
}

function initInternalComponent (vm, options) {
  var opts = vm.$options = Object.create(vm.constructor.options);
  // doing this because it's faster than dynamic enumeration.
  var parentVnode = options._parentVnode;
  opts.parent = options.parent;
  opts._parentVnode = parentVnode;

  var vnodeComponentOptions = parentVnode.componentOptions;
  opts.propsData = vnodeComponentOptions.propsData;
  opts._parentListeners = vnodeComponentOptions.listeners;
  opts._renderChildren = vnodeComponentOptions.children;
  opts._componentTag = vnodeComponentOptions.tag;

  if (options.render) {
    opts.render = options.render;
    opts.staticRenderFns = options.staticRenderFns;
  }
}

function resolveConstructorOptions (Ctor) {
  var options = Ctor.options;
  if (Ctor.super) {
    var superOptions = resolveConstructorOptions(Ctor.super);
    var cachedSuperOptions = Ctor.superOptions;
    if (superOptions !== cachedSuperOptions) {
      // super option changed,
      // need to resolve new options.
      Ctor.superOptions = superOptions;
      // check if there are any late-modified/attached options (#4976)
      var modifiedOptions = resolveModifiedOptions(Ctor);
      // update base extend options
      if (modifiedOptions) {
        extend(Ctor.extendOptions, modifiedOptions);
      }
      options = Ctor.options = mergeOptions(superOptions, Ctor.extendOptions);
      if (options.name) {
        options.components[options.name] = Ctor;
      }
    }
  }
  return options
}

function resolveModifiedOptions (Ctor) {
  var modified;
  var latest = Ctor.options;
  var sealed = Ctor.sealedOptions;
  for (var key in latest) {
    if (latest[key] !== sealed[key]) {
      if (!modified) { modified = {}; }
      modified[key] = latest[key];
    }
  }
  return modified
}

function Vue (options) {
  if (process.env.NODE_ENV !== 'production' &&
    !(this instanceof Vue)
  ) {
    warn('Vue is a constructor and should be called with the `new` keyword');
  }
  this._init(options);
}

initMixin(Vue);
stateMixin(Vue);
eventsMixin(Vue);
lifecycleMixin(Vue);
renderMixin(Vue);

/*  */

function initUse (Vue) {
  Vue.use = function (plugin) {
    var installedPlugins = (this._installedPlugins || (this._installedPlugins = []));
    if (installedPlugins.indexOf(plugin) > -1) {
      return this
    }

    // additional parameters
    var args = toArray(arguments, 1);
    args.unshift(this);
    if (typeof plugin.install === 'function') {
      plugin.install.apply(plugin, args);
    } else if (typeof plugin === 'function') {
      plugin.apply(null, args);
    }
    installedPlugins.push(plugin);
    return this
  };
}

/*  */

function initMixin$1 (Vue) {
  Vue.mixin = function (mixin) {
    this.options = mergeOptions(this.options, mixin);
    return this
  };
}

/*  */

function initExtend (Vue) {
  /**
   * Each instance constructor, including Vue, has a unique
   * cid. This enables us to create wrapped "child
   * constructors" for prototypal inheritance and cache them.
   */
  Vue.cid = 0;
  var cid = 1;

  /**
   * Class inheritance
   */
  Vue.extend = function (extendOptions) {
    extendOptions = extendOptions || {};
    var Super = this;
    var SuperId = Super.cid;
    var cachedCtors = extendOptions._Ctor || (extendOptions._Ctor = {});
    if (cachedCtors[SuperId]) {
      return cachedCtors[SuperId]
    }

    var name = extendOptions.name || Super.options.name;
    if (process.env.NODE_ENV !== 'production' && name) {
      validateComponentName(name);
    }

    var Sub = function VueComponent (options) {
      this._init(options);
    };
    Sub.prototype = Object.create(Super.prototype);
    Sub.prototype.constructor = Sub;
    Sub.cid = cid++;
    Sub.options = mergeOptions(
      Super.options,
      extendOptions
    );
    Sub['super'] = Super;

    // For props and computed properties, we define the proxy getters on
    // the Vue instances at extension time, on the extended prototype. This
    // avoids Object.defineProperty calls for each instance created.
    if (Sub.options.props) {
      initProps$1(Sub);
    }
    if (Sub.options.computed) {
      initComputed$1(Sub);
    }

    // allow further extension/mixin/plugin usage
    Sub.extend = Super.extend;
    Sub.mixin = Super.mixin;
    Sub.use = Super.use;

    // create asset registers, so extended classes
    // can have their private assets too.
    ASSET_TYPES.forEach(function (type) {
      Sub[type] = Super[type];
    });
    // enable recursive self-lookup
    if (name) {
      Sub.options.components[name] = Sub;
    }

    // keep a reference to the super options at extension time.
    // later at instantiation we can check if Super's options have
    // been updated.
    Sub.superOptions = Super.options;
    Sub.extendOptions = extendOptions;
    Sub.sealedOptions = extend({}, Sub.options);

    // cache constructor
    cachedCtors[SuperId] = Sub;
    return Sub
  };
}

function initProps$1 (Comp) {
  var props = Comp.options.props;
  for (var key in props) {
    proxy(Comp.prototype, "_props", key);
  }
}

function initComputed$1 (Comp) {
  var computed = Comp.options.computed;
  for (var key in computed) {
    defineComputed(Comp.prototype, key, computed[key]);
  }
}

/*  */

function initAssetRegisters (Vue) {
  /**
   * Create asset registration methods.
   */
  ASSET_TYPES.forEach(function (type) {
    Vue[type] = function (
      id,
      definition
    ) {
      if (!definition) {
        return this.options[type + 's'][id]
      } else {
        /* istanbul ignore if */
        if (process.env.NODE_ENV !== 'production' && type === 'component') {
          validateComponentName(id);
        }
        if (type === 'component' && isPlainObject(definition)) {
          definition.name = definition.name || id;
          definition = this.options._base.extend(definition);
        }
        if (type === 'directive' && typeof definition === 'function') {
          definition = { bind: definition, update: definition };
        }
        this.options[type + 's'][id] = definition;
        return definition
      }
    };
  });
}

/*  */



function getComponentName (opts) {
  return opts && (opts.Ctor.options.name || opts.tag)
}

function matches (pattern, name) {
  if (Array.isArray(pattern)) {
    return pattern.indexOf(name) > -1
  } else if (typeof pattern === 'string') {
    return pattern.split(',').indexOf(name) > -1
  } else if (isRegExp(pattern)) {
    return pattern.test(name)
  }
  /* istanbul ignore next */
  return false
}

function pruneCache (keepAliveInstance, filter) {
  var cache = keepAliveInstance.cache;
  var keys = keepAliveInstance.keys;
  var _vnode = keepAliveInstance._vnode;
  for (var key in cache) {
    var cachedNode = cache[key];
    if (cachedNode) {
      var name = getComponentName(cachedNode.componentOptions);
      if (name && !filter(name)) {
        pruneCacheEntry(cache, key, keys, _vnode);
      }
    }
  }
}

function pruneCacheEntry (
  cache,
  key,
  keys,
  current
) {
  var cached$$1 = cache[key];
  if (cached$$1 && (!current || cached$$1.tag !== current.tag)) {
    cached$$1.componentInstance.$destroy();
  }
  cache[key] = null;
  remove(keys, key);
}

var patternTypes = [String, RegExp, Array];

var KeepAlive = {
  name: 'keep-alive',
  abstract: true,

  props: {
    include: patternTypes,
    exclude: patternTypes,
    max: [String, Number]
  },

  created: function created () {
    this.cache = Object.create(null);
    this.keys = [];
  },

  destroyed: function destroyed () {
    for (var key in this.cache) {
      pruneCacheEntry(this.cache, key, this.keys);
    }
  },

  mounted: function mounted () {
    var this$1 = this;

    this.$watch('include', function (val) {
      pruneCache(this$1, function (name) { return matches(val, name); });
    });
    this.$watch('exclude', function (val) {
      pruneCache(this$1, function (name) { return !matches(val, name); });
    });
  },

  render: function render () {
    var slot = this.$slots.default;
    var vnode = getFirstComponentChild(slot);
    var componentOptions = vnode && vnode.componentOptions;
    if (componentOptions) {
      // check pattern
      var name = getComponentName(componentOptions);
      var ref = this;
      var include = ref.include;
      var exclude = ref.exclude;
      if (
        // not included
        (include && (!name || !matches(include, name))) ||
        // excluded
        (exclude && name && matches(exclude, name))
      ) {
        return vnode
      }

      var ref$1 = this;
      var cache = ref$1.cache;
      var keys = ref$1.keys;
      var key = vnode.key == null
        // same constructor may get registered as different local components
        // so cid alone is not enough (#3269)
        ? componentOptions.Ctor.cid + (componentOptions.tag ? ("::" + (componentOptions.tag)) : '')
        : vnode.key;
      if (cache[key]) {
        vnode.componentInstance = cache[key].componentInstance;
        // make current key freshest
        remove(keys, key);
        keys.push(key);
      } else {
        cache[key] = vnode;
        keys.push(key);
        // prune oldest entry
        if (this.max && keys.length > parseInt(this.max)) {
          pruneCacheEntry(cache, keys[0], keys, this._vnode);
        }
      }

      vnode.data.keepAlive = true;
    }
    return vnode || (slot && slot[0])
  }
};

var builtInComponents = {
  KeepAlive: KeepAlive
};

/*  */

function initGlobalAPI (Vue) {
  // config
  var configDef = {};
  configDef.get = function () { return config; };
  if (process.env.NODE_ENV !== 'production') {
    configDef.set = function () {
      warn(
        'Do not replace the Vue.config object, set individual fields instead.'
      );
    };
  }
  Object.defineProperty(Vue, 'config', configDef);

  // exposed util methods.
  // NOTE: these are not considered part of the public API - avoid relying on
  // them unless you are aware of the risk.
  Vue.util = {
    warn: warn,
    extend: extend,
    mergeOptions: mergeOptions,
    defineReactive: defineReactive$$1
  };

  Vue.set = set;
  Vue.delete = del;
  Vue.nextTick = nextTick;

  // 2.6 explicit observable API
  Vue.observable = function (obj) {
    observe(obj);
    return obj
  };

  Vue.options = Object.create(null);
  ASSET_TYPES.forEach(function (type) {
    Vue.options[type + 's'] = Object.create(null);
  });

  // this is used to identify the "base" constructor to extend all plain-object
  // components with in Weex's multi-instance scenarios.
  Vue.options._base = Vue;

  extend(Vue.options.components, builtInComponents);

  initUse(Vue);
  initMixin$1(Vue);
  initExtend(Vue);
  initAssetRegisters(Vue);
}

initGlobalAPI(Vue);

Object.defineProperty(Vue.prototype, '$isServer', {
  get: isServerRendering
});

Object.defineProperty(Vue.prototype, '$ssrContext', {
  get: function get () {
    /* istanbul ignore next */
    return this.$vnode && this.$vnode.ssrContext
  }
});

// expose FunctionalRenderContext for ssr runtime helper installation
Object.defineProperty(Vue, 'FunctionalRenderContext', {
  value: FunctionalRenderContext
});

Vue.version = '2.6.6';

/*  */

// these are reserved for web because they are directly compiled away
// during template compilation
var isReservedAttr = makeMap('style,class');

// attributes that should be using props for binding
var acceptValue = makeMap('input,textarea,option,select,progress');
var mustUseProp = function (tag, type, attr) {
  return (
    (attr === 'value' && acceptValue(tag)) && type !== 'button' ||
    (attr === 'selected' && tag === 'option') ||
    (attr === 'checked' && tag === 'input') ||
    (attr === 'muted' && tag === 'video')
  )
};

var isEnumeratedAttr = makeMap('contenteditable,draggable,spellcheck');

var isValidContentEditableValue = makeMap('events,caret,typing,plaintext-only');

var convertEnumeratedValue = function (key, value) {
  return isFalsyAttrValue(value) || value === 'false'
    ? 'false'
    // allow arbitrary string value for contenteditable
    : key === 'contenteditable' && isValidContentEditableValue(value)
      ? value
      : 'true'
};

var isBooleanAttr = makeMap(
  'allowfullscreen,async,autofocus,autoplay,checked,compact,controls,declare,' +
  'default,defaultchecked,defaultmuted,defaultselected,defer,disabled,' +
  'enabled,formnovalidate,hidden,indeterminate,inert,ismap,itemscope,loop,multiple,' +
  'muted,nohref,noresize,noshade,novalidate,nowrap,open,pauseonexit,readonly,' +
  'required,reversed,scoped,seamless,selected,sortable,translate,' +
  'truespeed,typemustmatch,visible'
);

var xlinkNS = 'http://www.w3.org/1999/xlink';

var isXlink = function (name) {
  return name.charAt(5) === ':' && name.slice(0, 5) === 'xlink'
};

var getXlinkProp = function (name) {
  return isXlink(name) ? name.slice(6, name.length) : ''
};

var isFalsyAttrValue = function (val) {
  return val == null || val === false
};

/*  */

function genClassForVnode (vnode) {
  var data = vnode.data;
  var parentNode = vnode;
  var childNode = vnode;
  while (isDef(childNode.componentInstance)) {
    childNode = childNode.componentInstance._vnode;
    if (childNode && childNode.data) {
      data = mergeClassData(childNode.data, data);
    }
  }
  while (isDef(parentNode = parentNode.parent)) {
    if (parentNode && parentNode.data) {
      data = mergeClassData(data, parentNode.data);
    }
  }
  return renderClass(data.staticClass, data.class)
}

function mergeClassData (child, parent) {
  return {
    staticClass: concat(child.staticClass, parent.staticClass),
    class: isDef(child.class)
      ? [child.class, parent.class]
      : parent.class
  }
}

function renderClass (
  staticClass,
  dynamicClass
) {
  if (isDef(staticClass) || isDef(dynamicClass)) {
    return concat(staticClass, stringifyClass(dynamicClass))
  }
  /* istanbul ignore next */
  return ''
}

function concat (a, b) {
  return a ? b ? (a + ' ' + b) : a : (b || '')
}

function stringifyClass (value) {
  if (Array.isArray(value)) {
    return stringifyArray(value)
  }
  if (isObject(value)) {
    return stringifyObject(value)
  }
  if (typeof value === 'string') {
    return value
  }
  /* istanbul ignore next */
  return ''
}

function stringifyArray (value) {
  var res = '';
  var stringified;
  for (var i = 0, l = value.length; i < l; i++) {
    if (isDef(stringified = stringifyClass(value[i])) && stringified !== '') {
      if (res) { res += ' '; }
      res += stringified;
    }
  }
  return res
}

function stringifyObject (value) {
  var res = '';
  for (var key in value) {
    if (value[key]) {
      if (res) { res += ' '; }
      res += key;
    }
  }
  return res
}

/*  */

var namespaceMap = {
  svg: 'http://www.w3.org/2000/svg',
  math: 'http://www.w3.org/1998/Math/MathML'
};

var isHTMLTag = makeMap(
  'html,body,base,head,link,meta,style,title,' +
  'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' +
  'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' +
  'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' +
  's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' +
  'embed,object,param,source,canvas,script,noscript,del,ins,' +
  'caption,col,colgroup,table,thead,tbody,td,th,tr,' +
  'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' +
  'output,progress,select,textarea,' +
  'details,dialog,menu,menuitem,summary,' +
  'content,element,shadow,template,blockquote,iframe,tfoot'
);

// this map is intentionally selective, only covering SVG elements that may
// contain child elements.
var isSVG = makeMap(
  'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' +
  'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' +
  'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view',
  true
);

var isPreTag = function (tag) { return tag === 'pre'; };

var isReservedTag = function (tag) {
  return isHTMLTag(tag) || isSVG(tag)
};

function getTagNamespace (tag) {
  if (isSVG(tag)) {
    return 'svg'
  }
  // basic support for MathML
  // note it doesn't support other MathML elements being component roots
  if (tag === 'math') {
    return 'math'
  }
}

var unknownElementCache = Object.create(null);
function isUnknownElement (tag) {
  /* istanbul ignore if */
  if (!inBrowser) {
    return true
  }
  if (isReservedTag(tag)) {
    return false
  }
  tag = tag.toLowerCase();
  /* istanbul ignore if */
  if (unknownElementCache[tag] != null) {
    return unknownElementCache[tag]
  }
  var el = document.createElement(tag);
  if (tag.indexOf('-') > -1) {
    // http://stackoverflow.com/a/28210364/1070244
    return (unknownElementCache[tag] = (
      el.constructor === window.HTMLUnknownElement ||
      el.constructor === window.HTMLElement
    ))
  } else {
    return (unknownElementCache[tag] = /HTMLUnknownElement/.test(el.toString()))
  }
}

var isTextInputType = makeMap('text,number,password,search,email,tel,url');

/*  */

/**
 * Query an element selector if it's not an element already.
 */
function query (el) {
  if (typeof el === 'string') {
    var selected = document.querySelector(el);
    if (!selected) {
      process.env.NODE_ENV !== 'production' && warn(
        'Cannot find element: ' + el
      );
      return document.createElement('div')
    }
    return selected
  } else {
    return el
  }
}

/*  */

function createElement$1 (tagName, vnode) {
  var elm = document.createElement(tagName);
  if (tagName !== 'select') {
    return elm
  }
  // false or null will remove the attribute but undefined will not
  if (vnode.data && vnode.data.attrs && vnode.data.attrs.multiple !== undefined) {
    elm.setAttribute('multiple', 'multiple');
  }
  return elm
}

function createElementNS (namespace, tagName) {
  return document.createElementNS(namespaceMap[namespace], tagName)
}

function createTextNode (text) {
  return document.createTextNode(text)
}

function createComment (text) {
  return document.createComment(text)
}

function insertBefore (parentNode, newNode, referenceNode) {
  parentNode.insertBefore(newNode, referenceNode);
}

function removeChild (node, child) {
  node.removeChild(child);
}

function appendChild (node, child) {
  node.appendChild(child);
}

function parentNode (node) {
  return node.parentNode
}

function nextSibling (node) {
  return node.nextSibling
}

function tagName (node) {
  return node.tagName
}

function setTextContent (node, text) {
  node.textContent = text;
}

function setStyleScope (node, scopeId) {
  node.setAttribute(scopeId, '');
}

var nodeOps = /*#__PURE__*/Object.freeze({
  createElement: createElement$1,
  createElementNS: createElementNS,
  createTextNode: createTextNode,
  createComment: createComment,
  insertBefore: insertBefore,
  removeChild: removeChild,
  appendChild: appendChild,
  parentNode: parentNode,
  nextSibling: nextSibling,
  tagName: tagName,
  setTextContent: setTextContent,
  setStyleScope: setStyleScope
});

/*  */

var ref = {
  create: function create (_, vnode) {
    registerRef(vnode);
  },
  update: function update (oldVnode, vnode) {
    if (oldVnode.data.ref !== vnode.data.ref) {
      registerRef(oldVnode, true);
      registerRef(vnode);
    }
  },
  destroy: function destroy (vnode) {
    registerRef(vnode, true);
  }
};

function registerRef (vnode, isRemoval) {
  var key = vnode.data.ref;
  if (!isDef(key)) { return }

  var vm = vnode.context;
  var ref = vnode.componentInstance || vnode.elm;
  var refs = vm.$refs;
  if (isRemoval) {
    if (Array.isArray(refs[key])) {
      remove(refs[key], ref);
    } else if (refs[key] === ref) {
      refs[key] = undefined;
    }
  } else {
    if (vnode.data.refInFor) {
      if (!Array.isArray(refs[key])) {
        refs[key] = [ref];
      } else if (refs[key].indexOf(ref) < 0) {
        // $flow-disable-line
        refs[key].push(ref);
      }
    } else {
      refs[key] = ref;
    }
  }
}

/**
 * Virtual DOM patching algorithm based on Snabbdom by
 * Simon Friis Vindum (@paldepind)
 * Licensed under the MIT License
 * https://github.com/paldepind/snabbdom/blob/master/LICENSE
 *
 * modified by Evan You (@yyx990803)
 *
 * Not type-checking this because this file is perf-critical and the cost
 * of making flow understand it is not worth it.
 */

var emptyNode = new VNode('', {}, []);

var hooks = ['create', 'activate', 'update', 'remove', 'destroy'];

function sameVnode (a, b) {
  return (
    a.key === b.key && (
      (
        a.tag === b.tag &&
        a.isComment === b.isComment &&
        isDef(a.data) === isDef(b.data) &&
        sameInputType(a, b)
      ) || (
        isTrue(a.isAsyncPlaceholder) &&
        a.asyncFactory === b.asyncFactory &&
        isUndef(b.asyncFactory.error)
      )
    )
  )
}

function sameInputType (a, b) {
  if (a.tag !== 'input') { return true }
  var i;
  var typeA = isDef(i = a.data) && isDef(i = i.attrs) && i.type;
  var typeB = isDef(i = b.data) && isDef(i = i.attrs) && i.type;
  return typeA === typeB || isTextInputType(typeA) && isTextInputType(typeB)
}

function createKeyToOldIdx (children, beginIdx, endIdx) {
  var i, key;
  var map = {};
  for (i = beginIdx; i <= endIdx; ++i) {
    key = children[i].key;
    if (isDef(key)) { map[key] = i; }
  }
  return map
}

function createPatchFunction (backend) {
  var i, j;
  var cbs = {};

  var modules = backend.modules;
  var nodeOps = backend.nodeOps;

  for (i = 0; i < hooks.length; ++i) {
    cbs[hooks[i]] = [];
    for (j = 0; j < modules.length; ++j) {
      if (isDef(modules[j][hooks[i]])) {
        cbs[hooks[i]].push(modules[j][hooks[i]]);
      }
    }
  }

  function emptyNodeAt (elm) {
    return new VNode(nodeOps.tagName(elm).toLowerCase(), {}, [], undefined, elm)
  }

  function createRmCb (childElm, listeners) {
    function remove$$1 () {
      if (--remove$$1.listeners === 0) {
        removeNode(childElm);
      }
    }
    remove$$1.listeners = listeners;
    return remove$$1
  }

  function removeNode (el) {
    var parent = nodeOps.parentNode(el);
    // element may have already been removed due to v-html / v-text
    if (isDef(parent)) {
      nodeOps.removeChild(parent, el);
    }
  }

  function isUnknownElement$$1 (vnode, inVPre) {
    return (
      !inVPre &&
      !vnode.ns &&
      !(
        config.ignoredElements.length &&
        config.ignoredElements.some(function (ignore) {
          return isRegExp(ignore)
            ? ignore.test(vnode.tag)
            : ignore === vnode.tag
        })
      ) &&
      config.isUnknownElement(vnode.tag)
    )
  }

  var creatingElmInVPre = 0;

  function createElm (
    vnode,
    insertedVnodeQueue,
    parentElm,
    refElm,
    nested,
    ownerArray,
    index
  ) {
    if (isDef(vnode.elm) && isDef(ownerArray)) {
      // This vnode was used in a previous render!
      // now it's used as a new node, overwriting its elm would cause
      // potential patch errors down the road when it's used as an insertion
      // reference node. Instead, we clone the node on-demand before creating
      // associated DOM element for it.
      vnode = ownerArray[index] = cloneVNode(vnode);
    }

    vnode.isRootInsert = !nested; // for transition enter check
    if (createComponent(vnode, insertedVnodeQueue, parentElm, refElm)) {
      return
    }

    var data = vnode.data;
    var children = vnode.children;
    var tag = vnode.tag;
    if (isDef(tag)) {
      if (process.env.NODE_ENV !== 'production') {
        if (data && data.pre) {
          creatingElmInVPre++;
        }
        if (isUnknownElement$$1(vnode, creatingElmInVPre)) {
          warn(
            'Unknown custom element: <' + tag + '> - did you ' +
            'register the component correctly? For recursive components, ' +
            'make sure to provide the "name" option.',
            vnode.context
          );
        }
      }

      vnode.elm = vnode.ns
        ? nodeOps.createElementNS(vnode.ns, tag)
        : nodeOps.createElement(tag, vnode);
      setScope(vnode);

      /* istanbul ignore if */
      {
        createChildren(vnode, children, insertedVnodeQueue);
        if (isDef(data)) {
          invokeCreateHooks(vnode, insertedVnodeQueue);
        }
        insert(parentElm, vnode.elm, refElm);
      }

      if (process.env.NODE_ENV !== 'production' && data && data.pre) {
        creatingElmInVPre--;
      }
    } else if (isTrue(vnode.isComment)) {
      vnode.elm = nodeOps.createComment(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    } else {
      vnode.elm = nodeOps.createTextNode(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    }
  }

  function createComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i = vnode.data;
    if (isDef(i)) {
      var isReactivated = isDef(vnode.componentInstance) && i.keepAlive;
      if (isDef(i = i.hook) && isDef(i = i.init)) {
        i(vnode, false /* hydrating */);
      }
      // after calling the init hook, if the vnode is a child component
      // it should've created a child instance and mounted it. the child
      // component also has set the placeholder vnode's elm.
      // in that case we can just return the element and be done.
      if (isDef(vnode.componentInstance)) {
        initComponent(vnode, insertedVnodeQueue);
        insert(parentElm, vnode.elm, refElm);
        if (isTrue(isReactivated)) {
          reactivateComponent(vnode, insertedVnodeQueue, parentElm, refElm);
        }
        return true
      }
    }
  }

  function initComponent (vnode, insertedVnodeQueue) {
    if (isDef(vnode.data.pendingInsert)) {
      insertedVnodeQueue.push.apply(insertedVnodeQueue, vnode.data.pendingInsert);
      vnode.data.pendingInsert = null;
    }
    vnode.elm = vnode.componentInstance.$el;
    if (isPatchable(vnode)) {
      invokeCreateHooks(vnode, insertedVnodeQueue);
      setScope(vnode);
    } else {
      // empty component root.
      // skip all element-related modules except for ref (#3455)
      registerRef(vnode);
      // make sure to invoke the insert hook
      insertedVnodeQueue.push(vnode);
    }
  }

  function reactivateComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i;
    // hack for #4339: a reactivated component with inner transition
    // does not trigger because the inner node's created hooks are not called
    // again. It's not ideal to involve module-specific logic in here but
    // there doesn't seem to be a better way to do it.
    var innerNode = vnode;
    while (innerNode.componentInstance) {
      innerNode = innerNode.componentInstance._vnode;
      if (isDef(i = innerNode.data) && isDef(i = i.transition)) {
        for (i = 0; i < cbs.activate.length; ++i) {
          cbs.activate[i](emptyNode, innerNode);
        }
        insertedVnodeQueue.push(innerNode);
        break
      }
    }
    // unlike a newly created component,
    // a reactivated keep-alive component doesn't insert itself
    insert(parentElm, vnode.elm, refElm);
  }

  function insert (parent, elm, ref$$1) {
    if (isDef(parent)) {
      if (isDef(ref$$1)) {
        if (nodeOps.parentNode(ref$$1) === parent) {
          nodeOps.insertBefore(parent, elm, ref$$1);
        }
      } else {
        nodeOps.appendChild(parent, elm);
      }
    }
  }

  function createChildren (vnode, children, insertedVnodeQueue) {
    if (Array.isArray(children)) {
      if (process.env.NODE_ENV !== 'production') {
        checkDuplicateKeys(children);
      }
      for (var i = 0; i < children.length; ++i) {
        createElm(children[i], insertedVnodeQueue, vnode.elm, null, true, children, i);
      }
    } else if (isPrimitive(vnode.text)) {
      nodeOps.appendChild(vnode.elm, nodeOps.createTextNode(String(vnode.text)));
    }
  }

  function isPatchable (vnode) {
    while (vnode.componentInstance) {
      vnode = vnode.componentInstance._vnode;
    }
    return isDef(vnode.tag)
  }

  function invokeCreateHooks (vnode, insertedVnodeQueue) {
    for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
      cbs.create[i$1](emptyNode, vnode);
    }
    i = vnode.data.hook; // Reuse variable
    if (isDef(i)) {
      if (isDef(i.create)) { i.create(emptyNode, vnode); }
      if (isDef(i.insert)) { insertedVnodeQueue.push(vnode); }
    }
  }

  // set scope id attribute for scoped CSS.
  // this is implemented as a special case to avoid the overhead
  // of going through the normal attribute patching process.
  function setScope (vnode) {
    var i;
    if (isDef(i = vnode.fnScopeId)) {
      nodeOps.setStyleScope(vnode.elm, i);
    } else {
      var ancestor = vnode;
      while (ancestor) {
        if (isDef(i = ancestor.context) && isDef(i = i.$options._scopeId)) {
          nodeOps.setStyleScope(vnode.elm, i);
        }
        ancestor = ancestor.parent;
      }
    }
    // for slot content they should also get the scopeId from the host instance.
    if (isDef(i = activeInstance) &&
      i !== vnode.context &&
      i !== vnode.fnContext &&
      isDef(i = i.$options._scopeId)
    ) {
      nodeOps.setStyleScope(vnode.elm, i);
    }
  }

  function addVnodes (parentElm, refElm, vnodes, startIdx, endIdx, insertedVnodeQueue) {
    for (; startIdx <= endIdx; ++startIdx) {
      createElm(vnodes[startIdx], insertedVnodeQueue, parentElm, refElm, false, vnodes, startIdx);
    }
  }

  function invokeDestroyHook (vnode) {
    var i, j;
    var data = vnode.data;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.destroy)) { i(vnode); }
      for (i = 0; i < cbs.destroy.length; ++i) { cbs.destroy[i](vnode); }
    }
    if (isDef(i = vnode.children)) {
      for (j = 0; j < vnode.children.length; ++j) {
        invokeDestroyHook(vnode.children[j]);
      }
    }
  }

  function removeVnodes (parentElm, vnodes, startIdx, endIdx) {
    for (; startIdx <= endIdx; ++startIdx) {
      var ch = vnodes[startIdx];
      if (isDef(ch)) {
        if (isDef(ch.tag)) {
          removeAndInvokeRemoveHook(ch);
          invokeDestroyHook(ch);
        } else { // Text node
          removeNode(ch.elm);
        }
      }
    }
  }

  function removeAndInvokeRemoveHook (vnode, rm) {
    if (isDef(rm) || isDef(vnode.data)) {
      var i;
      var listeners = cbs.remove.length + 1;
      if (isDef(rm)) {
        // we have a recursively passed down rm callback
        // increase the listeners count
        rm.listeners += listeners;
      } else {
        // directly removing
        rm = createRmCb(vnode.elm, listeners);
      }
      // recursively invoke hooks on child component root node
      if (isDef(i = vnode.componentInstance) && isDef(i = i._vnode) && isDef(i.data)) {
        removeAndInvokeRemoveHook(i, rm);
      }
      for (i = 0; i < cbs.remove.length; ++i) {
        cbs.remove[i](vnode, rm);
      }
      if (isDef(i = vnode.data.hook) && isDef(i = i.remove)) {
        i(vnode, rm);
      } else {
        rm();
      }
    } else {
      removeNode(vnode.elm);
    }
  }

  function updateChildren (parentElm, oldCh, newCh, insertedVnodeQueue, removeOnly) {
    var oldStartIdx = 0;
    var newStartIdx = 0;
    var oldEndIdx = oldCh.length - 1;
    var oldStartVnode = oldCh[0];
    var oldEndVnode = oldCh[oldEndIdx];
    var newEndIdx = newCh.length - 1;
    var newStartVnode = newCh[0];
    var newEndVnode = newCh[newEndIdx];
    var oldKeyToIdx, idxInOld, vnodeToMove, refElm;

    // removeOnly is a special flag used only by <transition-group>
    // to ensure removed elements stay in correct relative positions
    // during leaving transitions
    var canMove = !removeOnly;

    if (process.env.NODE_ENV !== 'production') {
      checkDuplicateKeys(newCh);
    }

    while (oldStartIdx <= oldEndIdx && newStartIdx <= newEndIdx) {
      if (isUndef(oldStartVnode)) {
        oldStartVnode = oldCh[++oldStartIdx]; // Vnode has been moved left
      } else if (isUndef(oldEndVnode)) {
        oldEndVnode = oldCh[--oldEndIdx];
      } else if (sameVnode(oldStartVnode, newStartVnode)) {
        patchVnode(oldStartVnode, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
        oldStartVnode = oldCh[++oldStartIdx];
        newStartVnode = newCh[++newStartIdx];
      } else if (sameVnode(oldEndVnode, newEndVnode)) {
        patchVnode(oldEndVnode, newEndVnode, insertedVnodeQueue, newCh, newEndIdx);
        oldEndVnode = oldCh[--oldEndIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldStartVnode, newEndVnode)) { // Vnode moved right
        patchVnode(oldStartVnode, newEndVnode, insertedVnodeQueue, newCh, newEndIdx);
        canMove && nodeOps.insertBefore(parentElm, oldStartVnode.elm, nodeOps.nextSibling(oldEndVnode.elm));
        oldStartVnode = oldCh[++oldStartIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldEndVnode, newStartVnode)) { // Vnode moved left
        patchVnode(oldEndVnode, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
        canMove && nodeOps.insertBefore(parentElm, oldEndVnode.elm, oldStartVnode.elm);
        oldEndVnode = oldCh[--oldEndIdx];
        newStartVnode = newCh[++newStartIdx];
      } else {
        if (isUndef(oldKeyToIdx)) { oldKeyToIdx = createKeyToOldIdx(oldCh, oldStartIdx, oldEndIdx); }
        idxInOld = isDef(newStartVnode.key)
          ? oldKeyToIdx[newStartVnode.key]
          : findIdxInOld(newStartVnode, oldCh, oldStartIdx, oldEndIdx);
        if (isUndef(idxInOld)) { // New element
          createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm, false, newCh, newStartIdx);
        } else {
          vnodeToMove = oldCh[idxInOld];
          if (sameVnode(vnodeToMove, newStartVnode)) {
            patchVnode(vnodeToMove, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
            oldCh[idxInOld] = undefined;
            canMove && nodeOps.insertBefore(parentElm, vnodeToMove.elm, oldStartVnode.elm);
          } else {
            // same key but different element. treat as new element
            createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm, false, newCh, newStartIdx);
          }
        }
        newStartVnode = newCh[++newStartIdx];
      }
    }
    if (oldStartIdx > oldEndIdx) {
      refElm = isUndef(newCh[newEndIdx + 1]) ? null : newCh[newEndIdx + 1].elm;
      addVnodes(parentElm, refElm, newCh, newStartIdx, newEndIdx, insertedVnodeQueue);
    } else if (newStartIdx > newEndIdx) {
      removeVnodes(parentElm, oldCh, oldStartIdx, oldEndIdx);
    }
  }

  function checkDuplicateKeys (children) {
    var seenKeys = {};
    for (var i = 0; i < children.length; i++) {
      var vnode = children[i];
      var key = vnode.key;
      if (isDef(key)) {
        if (seenKeys[key]) {
          warn(
            ("Duplicate keys detected: '" + key + "'. This may cause an update error."),
            vnode.context
          );
        } else {
          seenKeys[key] = true;
        }
      }
    }
  }

  function findIdxInOld (node, oldCh, start, end) {
    for (var i = start; i < end; i++) {
      var c = oldCh[i];
      if (isDef(c) && sameVnode(node, c)) { return i }
    }
  }

  function patchVnode (
    oldVnode,
    vnode,
    insertedVnodeQueue,
    ownerArray,
    index,
    removeOnly
  ) {
    if (oldVnode === vnode) {
      return
    }

    if (isDef(vnode.elm) && isDef(ownerArray)) {
      // clone reused vnode
      vnode = ownerArray[index] = cloneVNode(vnode);
    }

    var elm = vnode.elm = oldVnode.elm;

    if (isTrue(oldVnode.isAsyncPlaceholder)) {
      if (isDef(vnode.asyncFactory.resolved)) {
        hydrate(oldVnode.elm, vnode, insertedVnodeQueue);
      } else {
        vnode.isAsyncPlaceholder = true;
      }
      return
    }

    // reuse element for static trees.
    // note we only do this if the vnode is cloned -
    // if the new node is not cloned it means the render functions have been
    // reset by the hot-reload-api and we need to do a proper re-render.
    if (isTrue(vnode.isStatic) &&
      isTrue(oldVnode.isStatic) &&
      vnode.key === oldVnode.key &&
      (isTrue(vnode.isCloned) || isTrue(vnode.isOnce))
    ) {
      vnode.componentInstance = oldVnode.componentInstance;
      return
    }

    var i;
    var data = vnode.data;
    if (isDef(data) && isDef(i = data.hook) && isDef(i = i.prepatch)) {
      i(oldVnode, vnode);
    }

    var oldCh = oldVnode.children;
    var ch = vnode.children;
    if (isDef(data) && isPatchable(vnode)) {
      for (i = 0; i < cbs.update.length; ++i) { cbs.update[i](oldVnode, vnode); }
      if (isDef(i = data.hook) && isDef(i = i.update)) { i(oldVnode, vnode); }
    }
    if (isUndef(vnode.text)) {
      if (isDef(oldCh) && isDef(ch)) {
        if (oldCh !== ch) { updateChildren(elm, oldCh, ch, insertedVnodeQueue, removeOnly); }
      } else if (isDef(ch)) {
        if (process.env.NODE_ENV !== 'production') {
          checkDuplicateKeys(ch);
        }
        if (isDef(oldVnode.text)) { nodeOps.setTextContent(elm, ''); }
        addVnodes(elm, null, ch, 0, ch.length - 1, insertedVnodeQueue);
      } else if (isDef(oldCh)) {
        removeVnodes(elm, oldCh, 0, oldCh.length - 1);
      } else if (isDef(oldVnode.text)) {
        nodeOps.setTextContent(elm, '');
      }
    } else if (oldVnode.text !== vnode.text) {
      nodeOps.setTextContent(elm, vnode.text);
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.postpatch)) { i(oldVnode, vnode); }
    }
  }

  function invokeInsertHook (vnode, queue, initial) {
    // delay insert hooks for component root nodes, invoke them after the
    // element is really inserted
    if (isTrue(initial) && isDef(vnode.parent)) {
      vnode.parent.data.pendingInsert = queue;
    } else {
      for (var i = 0; i < queue.length; ++i) {
        queue[i].data.hook.insert(queue[i]);
      }
    }
  }

  var hydrationBailed = false;
  // list of modules that can skip create hook during hydration because they
  // are already rendered on the client or has no need for initialization
  // Note: style is excluded because it relies on initial clone for future
  // deep updates (#7063).
  var isRenderedModule = makeMap('attrs,class,staticClass,staticStyle,key');

  // Note: this is a browser-only function so we can assume elms are DOM nodes.
  function hydrate (elm, vnode, insertedVnodeQueue, inVPre) {
    var i;
    var tag = vnode.tag;
    var data = vnode.data;
    var children = vnode.children;
    inVPre = inVPre || (data && data.pre);
    vnode.elm = elm;

    if (isTrue(vnode.isComment) && isDef(vnode.asyncFactory)) {
      vnode.isAsyncPlaceholder = true;
      return true
    }
    // assert node match
    if (process.env.NODE_ENV !== 'production') {
      if (!assertNodeMatch(elm, vnode, inVPre)) {
        return false
      }
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.init)) { i(vnode, true /* hydrating */); }
      if (isDef(i = vnode.componentInstance)) {
        // child component. it should have hydrated its own tree.
        initComponent(vnode, insertedVnodeQueue);
        return true
      }
    }
    if (isDef(tag)) {
      if (isDef(children)) {
        // empty element, allow client to pick up and populate children
        if (!elm.hasChildNodes()) {
          createChildren(vnode, children, insertedVnodeQueue);
        } else {
          // v-html and domProps: innerHTML
          if (isDef(i = data) && isDef(i = i.domProps) && isDef(i = i.innerHTML)) {
            if (i !== elm.innerHTML) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' &&
                typeof console !== 'undefined' &&
                !hydrationBailed
              ) {
                hydrationBailed = true;
                console.warn('Parent: ', elm);
                console.warn('server innerHTML: ', i);
                console.warn('client innerHTML: ', elm.innerHTML);
              }
              return false
            }
          } else {
            // iterate and compare children lists
            var childrenMatch = true;
            var childNode = elm.firstChild;
            for (var i$1 = 0; i$1 < children.length; i$1++) {
              if (!childNode || !hydrate(childNode, children[i$1], insertedVnodeQueue, inVPre)) {
                childrenMatch = false;
                break
              }
              childNode = childNode.nextSibling;
            }
            // if childNode is not null, it means the actual childNodes list is
            // longer than the virtual children list.
            if (!childrenMatch || childNode) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' &&
                typeof console !== 'undefined' &&
                !hydrationBailed
              ) {
                hydrationBailed = true;
                console.warn('Parent: ', elm);
                console.warn('Mismatching childNodes vs. VNodes: ', elm.childNodes, children);
              }
              return false
            }
          }
        }
      }
      if (isDef(data)) {
        var fullInvoke = false;
        for (var key in data) {
          if (!isRenderedModule(key)) {
            fullInvoke = true;
            invokeCreateHooks(vnode, insertedVnodeQueue);
            break
          }
        }
        if (!fullInvoke && data['class']) {
          // ensure collecting deps for deep class bindings for future updates
          traverse(data['class']);
        }
      }
    } else if (elm.data !== vnode.text) {
      elm.data = vnode.text;
    }
    return true
  }

  function assertNodeMatch (node, vnode, inVPre) {
    if (isDef(vnode.tag)) {
      return vnode.tag.indexOf('vue-component') === 0 || (
        !isUnknownElement$$1(vnode, inVPre) &&
        vnode.tag.toLowerCase() === (node.tagName && node.tagName.toLowerCase())
      )
    } else {
      return node.nodeType === (vnode.isComment ? 8 : 3)
    }
  }

  return function patch (oldVnode, vnode, hydrating, removeOnly) {
    if (isUndef(vnode)) {
      if (isDef(oldVnode)) { invokeDestroyHook(oldVnode); }
      return
    }

    var isInitialPatch = false;
    var insertedVnodeQueue = [];

    if (isUndef(oldVnode)) {
      // empty mount (likely as component), create new root element
      isInitialPatch = true;
      createElm(vnode, insertedVnodeQueue);
    } else {
      var isRealElement = isDef(oldVnode.nodeType);
      if (!isRealElement && sameVnode(oldVnode, vnode)) {
        // patch existing root node
        patchVnode(oldVnode, vnode, insertedVnodeQueue, null, null, removeOnly);
      } else {
        if (isRealElement) {
          // mounting to a real element
          // check if this is server-rendered content and if we can perform
          // a successful hydration.
          if (oldVnode.nodeType === 1 && oldVnode.hasAttribute(SSR_ATTR)) {
            oldVnode.removeAttribute(SSR_ATTR);
            hydrating = true;
          }
          if (isTrue(hydrating)) {
            if (hydrate(oldVnode, vnode, insertedVnodeQueue)) {
              invokeInsertHook(vnode, insertedVnodeQueue, true);
              return oldVnode
            } else if (process.env.NODE_ENV !== 'production') {
              warn(
                'The client-side rendered virtual DOM tree is not matching ' +
                'server-rendered content. This is likely caused by incorrect ' +
                'HTML markup, for example nesting block-level elements inside ' +
                '<p>, or missing <tbody>. Bailing hydration and performing ' +
                'full client-side render.'
              );
            }
          }
          // either not server-rendered, or hydration failed.
          // create an empty node and replace it
          oldVnode = emptyNodeAt(oldVnode);
        }

        // replacing existing element
        var oldElm = oldVnode.elm;
        var parentElm = nodeOps.parentNode(oldElm);

        // create new node
        createElm(
          vnode,
          insertedVnodeQueue,
          // extremely rare edge case: do not insert if old element is in a
          // leaving transition. Only happens when combining transition +
          // keep-alive + HOCs. (#4590)
          oldElm._leaveCb ? null : parentElm,
          nodeOps.nextSibling(oldElm)
        );

        // update parent placeholder node element, recursively
        if (isDef(vnode.parent)) {
          var ancestor = vnode.parent;
          var patchable = isPatchable(vnode);
          while (ancestor) {
            for (var i = 0; i < cbs.destroy.length; ++i) {
              cbs.destroy[i](ancestor);
            }
            ancestor.elm = vnode.elm;
            if (patchable) {
              for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
                cbs.create[i$1](emptyNode, ancestor);
              }
              // #6513
              // invoke insert hooks that may have been merged by create hooks.
              // e.g. for directives that uses the "inserted" hook.
              var insert = ancestor.data.hook.insert;
              if (insert.merged) {
                // start at index 1 to avoid re-invoking component mounted hook
                for (var i$2 = 1; i$2 < insert.fns.length; i$2++) {
                  insert.fns[i$2]();
                }
              }
            } else {
              registerRef(ancestor);
            }
            ancestor = ancestor.parent;
          }
        }

        // destroy old node
        if (isDef(parentElm)) {
          removeVnodes(parentElm, [oldVnode], 0, 0);
        } else if (isDef(oldVnode.tag)) {
          invokeDestroyHook(oldVnode);
        }
      }
    }

    invokeInsertHook(vnode, insertedVnodeQueue, isInitialPatch);
    return vnode.elm
  }
}

/*  */

var directives = {
  create: updateDirectives,
  update: updateDirectives,
  destroy: function unbindDirectives (vnode) {
    updateDirectives(vnode, emptyNode);
  }
};

function updateDirectives (oldVnode, vnode) {
  if (oldVnode.data.directives || vnode.data.directives) {
    _update(oldVnode, vnode);
  }
}

function _update (oldVnode, vnode) {
  var isCreate = oldVnode === emptyNode;
  var isDestroy = vnode === emptyNode;
  var oldDirs = normalizeDirectives$1(oldVnode.data.directives, oldVnode.context);
  var newDirs = normalizeDirectives$1(vnode.data.directives, vnode.context);

  var dirsWithInsert = [];
  var dirsWithPostpatch = [];

  var key, oldDir, dir;
  for (key in newDirs) {
    oldDir = oldDirs[key];
    dir = newDirs[key];
    if (!oldDir) {
      // new directive, bind
      callHook$1(dir, 'bind', vnode, oldVnode);
      if (dir.def && dir.def.inserted) {
        dirsWithInsert.push(dir);
      }
    } else {
      // existing directive, update
      dir.oldValue = oldDir.value;
      dir.oldArg = oldDir.arg;
      callHook$1(dir, 'update', vnode, oldVnode);
      if (dir.def && dir.def.componentUpdated) {
        dirsWithPostpatch.push(dir);
      }
    }
  }

  if (dirsWithInsert.length) {
    var callInsert = function () {
      for (var i = 0; i < dirsWithInsert.length; i++) {
        callHook$1(dirsWithInsert[i], 'inserted', vnode, oldVnode);
      }
    };
    if (isCreate) {
      mergeVNodeHook(vnode, 'insert', callInsert);
    } else {
      callInsert();
    }
  }

  if (dirsWithPostpatch.length) {
    mergeVNodeHook(vnode, 'postpatch', function () {
      for (var i = 0; i < dirsWithPostpatch.length; i++) {
        callHook$1(dirsWithPostpatch[i], 'componentUpdated', vnode, oldVnode);
      }
    });
  }

  if (!isCreate) {
    for (key in oldDirs) {
      if (!newDirs[key]) {
        // no longer present, unbind
        callHook$1(oldDirs[key], 'unbind', oldVnode, oldVnode, isDestroy);
      }
    }
  }
}

var emptyModifiers = Object.create(null);

function normalizeDirectives$1 (
  dirs,
  vm
) {
  var res = Object.create(null);
  if (!dirs) {
    // $flow-disable-line
    return res
  }
  var i, dir;
  for (i = 0; i < dirs.length; i++) {
    dir = dirs[i];
    if (!dir.modifiers) {
      // $flow-disable-line
      dir.modifiers = emptyModifiers;
    }
    res[getRawDirName(dir)] = dir;
    dir.def = resolveAsset(vm.$options, 'directives', dir.name, true);
  }
  // $flow-disable-line
  return res
}

function getRawDirName (dir) {
  return dir.rawName || ((dir.name) + "." + (Object.keys(dir.modifiers || {}).join('.')))
}

function callHook$1 (dir, hook, vnode, oldVnode, isDestroy) {
  var fn = dir.def && dir.def[hook];
  if (fn) {
    try {
      fn(vnode.elm, dir, vnode, oldVnode, isDestroy);
    } catch (e) {
      handleError(e, vnode.context, ("directive " + (dir.name) + " " + hook + " hook"));
    }
  }
}

var baseModules = [
  ref,
  directives
];

/*  */

function updateAttrs (oldVnode, vnode) {
  var opts = vnode.componentOptions;
  if (isDef(opts) && opts.Ctor.options.inheritAttrs === false) {
    return
  }
  if (isUndef(oldVnode.data.attrs) && isUndef(vnode.data.attrs)) {
    return
  }
  var key, cur, old;
  var elm = vnode.elm;
  var oldAttrs = oldVnode.data.attrs || {};
  var attrs = vnode.data.attrs || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(attrs.__ob__)) {
    attrs = vnode.data.attrs = extend({}, attrs);
  }

  for (key in attrs) {
    cur = attrs[key];
    old = oldAttrs[key];
    if (old !== cur) {
      setAttr(elm, key, cur);
    }
  }
  // #4391: in IE9, setting type can reset value for input[type=radio]
  // #6666: IE/Edge forces progress value down to 1 before setting a max
  /* istanbul ignore if */
  if ((isIE || isEdge) && attrs.value !== oldAttrs.value) {
    setAttr(elm, 'value', attrs.value);
  }
  for (key in oldAttrs) {
    if (isUndef(attrs[key])) {
      if (isXlink(key)) {
        elm.removeAttributeNS(xlinkNS, getXlinkProp(key));
      } else if (!isEnumeratedAttr(key)) {
        elm.removeAttribute(key);
      }
    }
  }
}

function setAttr (el, key, value) {
  if (el.tagName.indexOf('-') > -1) {
    baseSetAttr(el, key, value);
  } else if (isBooleanAttr(key)) {
    // set attribute for blank value
    // e.g. <option disabled>Select one</option>
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      // technically allowfullscreen is a boolean attribute for <iframe>,
      // but Flash expects a value of "true" when used on <embed> tag
      value = key === 'allowfullscreen' && el.tagName === 'EMBED'
        ? 'true'
        : key;
      el.setAttribute(key, value);
    }
  } else if (isEnumeratedAttr(key)) {
    el.setAttribute(key, convertEnumeratedValue(key, value));
  } else if (isXlink(key)) {
    if (isFalsyAttrValue(value)) {
      el.removeAttributeNS(xlinkNS, getXlinkProp(key));
    } else {
      el.setAttributeNS(xlinkNS, key, value);
    }
  } else {
    baseSetAttr(el, key, value);
  }
}

function baseSetAttr (el, key, value) {
  if (isFalsyAttrValue(value)) {
    el.removeAttribute(key);
  } else {
    // #7138: IE10 & 11 fires input event when setting placeholder on
    // <textarea>... block the first input event and remove the blocker
    // immediately.
    /* istanbul ignore if */
    if (
      isIE && !isIE9 &&
      el.tagName === 'TEXTAREA' &&
      key === 'placeholder' && value !== '' && !el.__ieph
    ) {
      var blocker = function (e) {
        e.stopImmediatePropagation();
        el.removeEventListener('input', blocker);
      };
      el.addEventListener('input', blocker);
      // $flow-disable-line
      el.__ieph = true; /* IE placeholder patched */
    }
    el.setAttribute(key, value);
  }
}

var attrs = {
  create: updateAttrs,
  update: updateAttrs
};

/*  */

function updateClass (oldVnode, vnode) {
  var el = vnode.elm;
  var data = vnode.data;
  var oldData = oldVnode.data;
  if (
    isUndef(data.staticClass) &&
    isUndef(data.class) && (
      isUndef(oldData) || (
        isUndef(oldData.staticClass) &&
        isUndef(oldData.class)
      )
    )
  ) {
    return
  }

  var cls = genClassForVnode(vnode);

  // handle transition classes
  var transitionClass = el._transitionClasses;
  if (isDef(transitionClass)) {
    cls = concat(cls, stringifyClass(transitionClass));
  }

  // set the class
  if (cls !== el._prevClass) {
    el.setAttribute('class', cls);
    el._prevClass = cls;
  }
}

var klass = {
  create: updateClass,
  update: updateClass
};

/*  */

var validDivisionCharRE = /[\w).+\-_$\]]/;

function parseFilters (exp) {
  var inSingle = false;
  var inDouble = false;
  var inTemplateString = false;
  var inRegex = false;
  var curly = 0;
  var square = 0;
  var paren = 0;
  var lastFilterIndex = 0;
  var c, prev, i, expression, filters;

  for (i = 0; i < exp.length; i++) {
    prev = c;
    c = exp.charCodeAt(i);
    if (inSingle) {
      if (c === 0x27 && prev !== 0x5C) { inSingle = false; }
    } else if (inDouble) {
      if (c === 0x22 && prev !== 0x5C) { inDouble = false; }
    } else if (inTemplateString) {
      if (c === 0x60 && prev !== 0x5C) { inTemplateString = false; }
    } else if (inRegex) {
      if (c === 0x2f && prev !== 0x5C) { inRegex = false; }
    } else if (
      c === 0x7C && // pipe
      exp.charCodeAt(i + 1) !== 0x7C &&
      exp.charCodeAt(i - 1) !== 0x7C &&
      !curly && !square && !paren
    ) {
      if (expression === undefined) {
        // first filter, end of expression
        lastFilterIndex = i + 1;
        expression = exp.slice(0, i).trim();
      } else {
        pushFilter();
      }
    } else {
      switch (c) {
        case 0x22: inDouble = true; break         // "
        case 0x27: inSingle = true; break         // '
        case 0x60: inTemplateString = true; break // `
        case 0x28: paren++; break                 // (
        case 0x29: paren--; break                 // )
        case 0x5B: square++; break                // [
        case 0x5D: square--; break                // ]
        case 0x7B: curly++; break                 // {
        case 0x7D: curly--; break                 // }
      }
      if (c === 0x2f) { // /
        var j = i - 1;
        var p = (void 0);
        // find first non-whitespace prev char
        for (; j >= 0; j--) {
          p = exp.charAt(j);
          if (p !== ' ') { break }
        }
        if (!p || !validDivisionCharRE.test(p)) {
          inRegex = true;
        }
      }
    }
  }

  if (expression === undefined) {
    expression = exp.slice(0, i).trim();
  } else if (lastFilterIndex !== 0) {
    pushFilter();
  }

  function pushFilter () {
    (filters || (filters = [])).push(exp.slice(lastFilterIndex, i).trim());
    lastFilterIndex = i + 1;
  }

  if (filters) {
    for (i = 0; i < filters.length; i++) {
      expression = wrapFilter(expression, filters[i]);
    }
  }

  return expression
}

function wrapFilter (exp, filter) {
  var i = filter.indexOf('(');
  if (i < 0) {
    // _f: resolveFilter
    return ("_f(\"" + filter + "\")(" + exp + ")")
  } else {
    var name = filter.slice(0, i);
    var args = filter.slice(i + 1);
    return ("_f(\"" + name + "\")(" + exp + (args !== ')' ? ',' + args : args))
  }
}

/*  */



/* eslint-disable no-unused-vars */
function baseWarn (msg, range) {
  console.error(("[Vue compiler]: " + msg));
}
/* eslint-enable no-unused-vars */

function pluckModuleFunction (
  modules,
  key
) {
  return modules
    ? modules.map(function (m) { return m[key]; }).filter(function (_) { return _; })
    : []
}

function addProp (el, name, value, range, dynamic) {
  (el.props || (el.props = [])).push(rangeSetItem({ name: name, value: value, dynamic: dynamic }, range));
  el.plain = false;
}

function addAttr (el, name, value, range, dynamic) {
  var attrs = dynamic
    ? (el.dynamicAttrs || (el.dynamicAttrs = []))
    : (el.attrs || (el.attrs = []));
  attrs.push(rangeSetItem({ name: name, value: value, dynamic: dynamic }, range));
  el.plain = false;
}

// add a raw attr (use this in preTransforms)
function addRawAttr (el, name, value, range) {
  el.attrsMap[name] = value;
  el.attrsList.push(rangeSetItem({ name: name, value: value }, range));
}

function addDirective (
  el,
  name,
  rawName,
  value,
  arg,
  isDynamicArg,
  modifiers,
  range
) {
  (el.directives || (el.directives = [])).push(rangeSetItem({
    name: name,
    rawName: rawName,
    value: value,
    arg: arg,
    isDynamicArg: isDynamicArg,
    modifiers: modifiers
  }, range));
  el.plain = false;
}

function prependModifierMarker (symbol, name, dynamic) {
  return dynamic
    ? ("_p(" + name + ",\"" + symbol + "\")")
    : symbol + name // mark the event as captured
}

function addHandler (
  el,
  name,
  value,
  modifiers,
  important,
  warn,
  range,
  dynamic
) {
  modifiers = modifiers || emptyObject;
  // warn prevent and passive modifier
  /* istanbul ignore if */
  if (
    process.env.NODE_ENV !== 'production' && warn &&
    modifiers.prevent && modifiers.passive
  ) {
    warn(
      'passive and prevent can\'t be used together. ' +
      'Passive handler can\'t prevent default event.',
      range
    );
  }

  // normalize click.right and click.middle since they don't actually fire
  // this is technically browser-specific, but at least for now browsers are
  // the only target envs that have right/middle clicks.
  if (modifiers.right) {
    if (dynamic) {
      name = "(" + name + ")==='click'?'contextmenu':(" + name + ")";
    } else if (name === 'click') {
      name = 'contextmenu';
      delete modifiers.right;
    }
  } else if (modifiers.middle) {
    if (dynamic) {
      name = "(" + name + ")==='click'?'mouseup':(" + name + ")";
    } else if (name === 'click') {
      name = 'mouseup';
    }
  }

  // check capture modifier
  if (modifiers.capture) {
    delete modifiers.capture;
    name = prependModifierMarker('!', name, dynamic);
  }
  if (modifiers.once) {
    delete modifiers.once;
    name = prependModifierMarker('~', name, dynamic);
  }
  /* istanbul ignore if */
  if (modifiers.passive) {
    delete modifiers.passive;
    name = prependModifierMarker('&', name, dynamic);
  }

  var events;
  if (modifiers.native) {
    delete modifiers.native;
    events = el.nativeEvents || (el.nativeEvents = {});
  } else {
    events = el.events || (el.events = {});
  }

  var newHandler = rangeSetItem({ value: value.trim(), dynamic: dynamic }, range);
  if (modifiers !== emptyObject) {
    newHandler.modifiers = modifiers;
  }

  var handlers = events[name];
  /* istanbul ignore if */
  if (Array.isArray(handlers)) {
    important ? handlers.unshift(newHandler) : handlers.push(newHandler);
  } else if (handlers) {
    events[name] = important ? [newHandler, handlers] : [handlers, newHandler];
  } else {
    events[name] = newHandler;
  }

  el.plain = false;
}

function getRawBindingAttr (
  el,
  name
) {
  return el.rawAttrsMap[':' + name] ||
    el.rawAttrsMap['v-bind:' + name] ||
    el.rawAttrsMap[name]
}

function getBindingAttr (
  el,
  name,
  getStatic
) {
  var dynamicValue =
    getAndRemoveAttr(el, ':' + name) ||
    getAndRemoveAttr(el, 'v-bind:' + name);
  if (dynamicValue != null) {
    return parseFilters(dynamicValue)
  } else if (getStatic !== false) {
    var staticValue = getAndRemoveAttr(el, name);
    if (staticValue != null) {
      return JSON.stringify(staticValue)
    }
  }
}

// note: this only removes the attr from the Array (attrsList) so that it
// doesn't get processed by processAttrs.
// By default it does NOT remove it from the map (attrsMap) because the map is
// needed during codegen.
function getAndRemoveAttr (
  el,
  name,
  removeFromMap
) {
  var val;
  if ((val = el.attrsMap[name]) != null) {
    var list = el.attrsList;
    for (var i = 0, l = list.length; i < l; i++) {
      if (list[i].name === name) {
        list.splice(i, 1);
        break
      }
    }
  }
  if (removeFromMap) {
    delete el.attrsMap[name];
  }
  return val
}

function getAndRemoveAttrByRegex (
  el,
  name
) {
  var list = el.attrsList;
  for (var i = 0, l = list.length; i < l; i++) {
    var attr = list[i];
    if (name.test(attr.name)) {
      list.splice(i, 1);
      return attr
    }
  }
}

function rangeSetItem (
  item,
  range
) {
  if (range) {
    if (range.start != null) {
      item.start = range.start;
    }
    if (range.end != null) {
      item.end = range.end;
    }
  }
  return item
}

/*  */

/**
 * Cross-platform code generation for component v-model
 */
function genComponentModel (
  el,
  value,
  modifiers
) {
  var ref = modifiers || {};
  var number = ref.number;
  var trim = ref.trim;

  var baseValueExpression = '$$v';
  var valueExpression = baseValueExpression;
  if (trim) {
    valueExpression =
      "(typeof " + baseValueExpression + " === 'string'" +
      "? " + baseValueExpression + ".trim()" +
      ": " + baseValueExpression + ")";
  }
  if (number) {
    valueExpression = "_n(" + valueExpression + ")";
  }
  var assignment = genAssignmentCode(value, valueExpression);

  el.model = {
    value: ("(" + value + ")"),
    expression: JSON.stringify(value),
    callback: ("function (" + baseValueExpression + ") {" + assignment + "}")
  };
}

/**
 * Cross-platform codegen helper for generating v-model value assignment code.
 */
function genAssignmentCode (
  value,
  assignment
) {
  var res = parseModel(value);
  if (res.key === null) {
    return (value + "=" + assignment)
  } else {
    return ("$set(" + (res.exp) + ", " + (res.key) + ", " + assignment + ")")
  }
}

/**
 * Parse a v-model expression into a base path and a final key segment.
 * Handles both dot-path and possible square brackets.
 *
 * Possible cases:
 *
 * - test
 * - test[key]
 * - test[test1[key]]
 * - test["a"][key]
 * - xxx.test[a[a].test1[key]]
 * - test.xxx.a["asa"][test1[key]]
 *
 */

var len, str, chr, index$1, expressionPos, expressionEndPos;



function parseModel (val) {
  // Fix https://github.com/vuejs/vue/pull/7730
  // allow v-model="obj.val " (trailing whitespace)
  val = val.trim();
  len = val.length;

  if (val.indexOf('[') < 0 || val.lastIndexOf(']') < len - 1) {
    index$1 = val.lastIndexOf('.');
    if (index$1 > -1) {
      return {
        exp: val.slice(0, index$1),
        key: '"' + val.slice(index$1 + 1) + '"'
      }
    } else {
      return {
        exp: val,
        key: null
      }
    }
  }

  str = val;
  index$1 = expressionPos = expressionEndPos = 0;

  while (!eof()) {
    chr = next();
    /* istanbul ignore if */
    if (isStringStart(chr)) {
      parseString(chr);
    } else if (chr === 0x5B) {
      parseBracket(chr);
    }
  }

  return {
    exp: val.slice(0, expressionPos),
    key: val.slice(expressionPos + 1, expressionEndPos)
  }
}

function next () {
  return str.charCodeAt(++index$1)
}

function eof () {
  return index$1 >= len
}

function isStringStart (chr) {
  return chr === 0x22 || chr === 0x27
}

function parseBracket (chr) {
  var inBracket = 1;
  expressionPos = index$1;
  while (!eof()) {
    chr = next();
    if (isStringStart(chr)) {
      parseString(chr);
      continue
    }
    if (chr === 0x5B) { inBracket++; }
    if (chr === 0x5D) { inBracket--; }
    if (inBracket === 0) {
      expressionEndPos = index$1;
      break
    }
  }
}

function parseString (chr) {
  var stringQuote = chr;
  while (!eof()) {
    chr = next();
    if (chr === stringQuote) {
      break
    }
  }
}

/*  */

var warn$1;

// in some cases, the event used has to be determined at runtime
// so we used some reserved tokens during compile.
var RANGE_TOKEN = '__r';
var CHECKBOX_RADIO_TOKEN = '__c';

function model (
  el,
  dir,
  _warn
) {
  warn$1 = _warn;
  var value = dir.value;
  var modifiers = dir.modifiers;
  var tag = el.tag;
  var type = el.attrsMap.type;

  if (process.env.NODE_ENV !== 'production') {
    // inputs with type="file" are read only and setting the input's
    // value will throw an error.
    if (tag === 'input' && type === 'file') {
      warn$1(
        "<" + (el.tag) + " v-model=\"" + value + "\" type=\"file\">:\n" +
        "File inputs are read only. Use a v-on:change listener instead.",
        el.rawAttrsMap['v-model']
      );
    }
  }

  if (el.component) {
    genComponentModel(el, value, modifiers);
    // component v-model doesn't need extra runtime
    return false
  } else if (tag === 'select') {
    genSelect(el, value, modifiers);
  } else if (tag === 'input' && type === 'checkbox') {
    genCheckboxModel(el, value, modifiers);
  } else if (tag === 'input' && type === 'radio') {
    genRadioModel(el, value, modifiers);
  } else if (tag === 'input' || tag === 'textarea') {
    genDefaultModel(el, value, modifiers);
  } else if (!config.isReservedTag(tag)) {
    genComponentModel(el, value, modifiers);
    // component v-model doesn't need extra runtime
    return false
  } else if (process.env.NODE_ENV !== 'production') {
    warn$1(
      "<" + (el.tag) + " v-model=\"" + value + "\">: " +
      "v-model is not supported on this element type. " +
      'If you are working with contenteditable, it\'s recommended to ' +
      'wrap a library dedicated for that purpose inside a custom component.',
      el.rawAttrsMap['v-model']
    );
  }

  // ensure runtime directive metadata
  return true
}

function genCheckboxModel (
  el,
  value,
  modifiers
) {
  var number = modifiers && modifiers.number;
  var valueBinding = getBindingAttr(el, 'value') || 'null';
  var trueValueBinding = getBindingAttr(el, 'true-value') || 'true';
  var falseValueBinding = getBindingAttr(el, 'false-value') || 'false';
  addProp(el, 'checked',
    "Array.isArray(" + value + ")" +
    "?_i(" + value + "," + valueBinding + ")>-1" + (
      trueValueBinding === 'true'
        ? (":(" + value + ")")
        : (":_q(" + value + "," + trueValueBinding + ")")
    )
  );
  addHandler(el, 'change',
    "var $$a=" + value + "," +
        '$$el=$event.target,' +
        "$$c=$$el.checked?(" + trueValueBinding + "):(" + falseValueBinding + ");" +
    'if(Array.isArray($$a)){' +
      "var $$v=" + (number ? '_n(' + valueBinding + ')' : valueBinding) + "," +
          '$$i=_i($$a,$$v);' +
      "if($$el.checked){$$i<0&&(" + (genAssignmentCode(value, '$$a.concat([$$v])')) + ")}" +
      "else{$$i>-1&&(" + (genAssignmentCode(value, '$$a.slice(0,$$i).concat($$a.slice($$i+1))')) + ")}" +
    "}else{" + (genAssignmentCode(value, '$$c')) + "}",
    null, true
  );
}

function genRadioModel (
  el,
  value,
  modifiers
) {
  var number = modifiers && modifiers.number;
  var valueBinding = getBindingAttr(el, 'value') || 'null';
  valueBinding = number ? ("_n(" + valueBinding + ")") : valueBinding;
  addProp(el, 'checked', ("_q(" + value + "," + valueBinding + ")"));
  addHandler(el, 'change', genAssignmentCode(value, valueBinding), null, true);
}

function genSelect (
  el,
  value,
  modifiers
) {
  var number = modifiers && modifiers.number;
  var selectedVal = "Array.prototype.filter" +
    ".call($event.target.options,function(o){return o.selected})" +
    ".map(function(o){var val = \"_value\" in o ? o._value : o.value;" +
    "return " + (number ? '_n(val)' : 'val') + "})";

  var assignment = '$event.target.multiple ? $$selectedVal : $$selectedVal[0]';
  var code = "var $$selectedVal = " + selectedVal + ";";
  code = code + " " + (genAssignmentCode(value, assignment));
  addHandler(el, 'change', code, null, true);
}

function genDefaultModel (
  el,
  value,
  modifiers
) {
  var type = el.attrsMap.type;

  // warn if v-bind:value conflicts with v-model
  // except for inputs with v-bind:type
  if (process.env.NODE_ENV !== 'production') {
    var value$1 = el.attrsMap['v-bind:value'] || el.attrsMap[':value'];
    var typeBinding = el.attrsMap['v-bind:type'] || el.attrsMap[':type'];
    if (value$1 && !typeBinding) {
      var binding = el.attrsMap['v-bind:value'] ? 'v-bind:value' : ':value';
      warn$1(
        binding + "=\"" + value$1 + "\" conflicts with v-model on the same element " +
        'because the latter already expands to a value binding internally',
        el.rawAttrsMap[binding]
      );
    }
  }

  var ref = modifiers || {};
  var lazy = ref.lazy;
  var number = ref.number;
  var trim = ref.trim;
  var needCompositionGuard = !lazy && type !== 'range';
  var event = lazy
    ? 'change'
    : type === 'range'
      ? RANGE_TOKEN
      : 'input';

  var valueExpression = '$event.target.value';
  if (trim) {
    valueExpression = "$event.target.value.trim()";
  }
  if (number) {
    valueExpression = "_n(" + valueExpression + ")";
  }

  var code = genAssignmentCode(value, valueExpression);
  if (needCompositionGuard) {
    code = "if($event.target.composing)return;" + code;
  }

  addProp(el, 'value', ("(" + value + ")"));
  addHandler(el, event, code, null, true);
  if (trim || number) {
    addHandler(el, 'blur', '$forceUpdate()');
  }
}

/*  */

// normalize v-model event tokens that can only be determined at runtime.
// it's important to place the event as the first in the array because
// the whole point is ensuring the v-model callback gets called before
// user-attached handlers.
function normalizeEvents (on) {
  /* istanbul ignore if */
  if (isDef(on[RANGE_TOKEN])) {
    // IE input[type=range] only supports `change` event
    var event = isIE ? 'change' : 'input';
    on[event] = [].concat(on[RANGE_TOKEN], on[event] || []);
    delete on[RANGE_TOKEN];
  }
  // This was originally intended to fix #4521 but no longer necessary
  // after 2.5. Keeping it for backwards compat with generated code from < 2.4
  /* istanbul ignore if */
  if (isDef(on[CHECKBOX_RADIO_TOKEN])) {
    on.change = [].concat(on[CHECKBOX_RADIO_TOKEN], on.change || []);
    delete on[CHECKBOX_RADIO_TOKEN];
  }
}

var target$1;

function createOnceHandler$1 (event, handler, capture) {
  var _target = target$1; // save current target element in closure
  return function onceHandler () {
    var res = handler.apply(null, arguments);
    if (res !== null) {
      remove$2(event, onceHandler, capture, _target);
    }
  }
}

// #9446: Firefox <= 53 (in particular, ESR 52) has incorrect Event.timeStamp
// implementation and does not fire microtasks in between event propagation, so
// safe to exclude.
var useMicrotaskFix = isUsingMicroTask && !(isFF && Number(isFF[1]) <= 53);

function add$1 (
  name,
  handler,
  capture,
  passive
) {
  // async edge case #6566: inner click event triggers patch, event handler
  // attached to outer element during patch, and triggered again. This
  // happens because browsers fire microtask ticks between event propagation.
  // the solution is simple: we save the timestamp when a handler is attached,
  // and the handler would only fire if the event passed to it was fired
  // AFTER it was attached.
  if (useMicrotaskFix) {
    var attachedTimestamp = currentFlushTimestamp;
    var original = handler;
    handler = original._wrapper = function (e) {
      if (
        // no bubbling, should always fire.
        // this is just a safety net in case event.timeStamp is unreliable in
        // certain weird environments...
        e.target === e.currentTarget ||
        // event is fired after handler attachment
        e.timeStamp >= attachedTimestamp ||
        // #9462 bail for iOS 9 bug: event.timeStamp is 0 after history.pushState
        e.timeStamp === 0 ||
        // #9448 bail if event is fired in another document in a multi-page
        // electron/nw.js app, since event.timeStamp will be using a different
        // starting reference
        e.target.ownerDocument !== document
      ) {
        return original.apply(this, arguments)
      }
    };
  }
  target$1.addEventListener(
    name,
    handler,
    supportsPassive
      ? { capture: capture, passive: passive }
      : capture
  );
}

function remove$2 (
  name,
  handler,
  capture,
  _target
) {
  (_target || target$1).removeEventListener(
    name,
    handler._wrapper || handler,
    capture
  );
}

function updateDOMListeners (oldVnode, vnode) {
  if (isUndef(oldVnode.data.on) && isUndef(vnode.data.on)) {
    return
  }
  var on = vnode.data.on || {};
  var oldOn = oldVnode.data.on || {};
  target$1 = vnode.elm;
  normalizeEvents(on);
  updateListeners(on, oldOn, add$1, remove$2, createOnceHandler$1, vnode.context);
  target$1 = undefined;
}

var events = {
  create: updateDOMListeners,
  update: updateDOMListeners
};

/*  */

var svgContainer;

function updateDOMProps (oldVnode, vnode) {
  if (isUndef(oldVnode.data.domProps) && isUndef(vnode.data.domProps)) {
    return
  }
  var key, cur;
  var elm = vnode.elm;
  var oldProps = oldVnode.data.domProps || {};
  var props = vnode.data.domProps || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(props.__ob__)) {
    props = vnode.data.domProps = extend({}, props);
  }

  for (key in oldProps) {
    if (isUndef(props[key])) {
      elm[key] = '';
    }
  }
  for (key in props) {
    cur = props[key];
    // ignore children if the node has textContent or innerHTML,
    // as these will throw away existing DOM nodes and cause removal errors
    // on subsequent patches (#3360)
    if (key === 'textContent' || key === 'innerHTML') {
      if (vnode.children) { vnode.children.length = 0; }
      if (cur === oldProps[key]) { continue }
      // #6601 work around Chrome version <= 55 bug where single textNode
      // replaced by innerHTML/textContent retains its parentNode property
      if (elm.childNodes.length === 1) {
        elm.removeChild(elm.childNodes[0]);
      }
    }

    // skip the update if old and new VDOM state is the same.
    // the only exception is `value` where the DOM value may be temporarily
    // out of sync with VDOM state due to focus, composition and modifiers.
    // This also covers #4521 by skipping the unnecesarry `checked` update.
    if (key !== 'value' && cur === oldProps[key]) {
      continue
    }

    if (key === 'value') {
      // store value as _value as well since
      // non-string values will be stringified
      elm._value = cur;
      // avoid resetting cursor position when value is the same
      var strCur = isUndef(cur) ? '' : String(cur);
      if (shouldUpdateValue(elm, strCur)) {
        elm.value = strCur;
      }
    } else if (key === 'innerHTML' && isSVG(elm.tagName) && isUndef(elm.innerHTML)) {
      // IE doesn't support innerHTML for SVG elements
      svgContainer = svgContainer || document.createElement('div');
      svgContainer.innerHTML = "<svg>" + cur + "</svg>";
      var svg = svgContainer.firstChild;
      while (elm.firstChild) {
        elm.removeChild(elm.firstChild);
      }
      while (svg.firstChild) {
        elm.appendChild(svg.firstChild);
      }
    } else {
      elm[key] = cur;
    }
  }
}

// check platforms/web/util/attrs.js acceptValue


function shouldUpdateValue (elm, checkVal) {
  return (!elm.composing && (
    elm.tagName === 'OPTION' ||
    isNotInFocusAndDirty(elm, checkVal) ||
    isDirtyWithModifiers(elm, checkVal)
  ))
}

function isNotInFocusAndDirty (elm, checkVal) {
  // return true when textbox (.number and .trim) loses focus and its value is
  // not equal to the updated value
  var notInFocus = true;
  // #6157
  // work around IE bug when accessing document.activeElement in an iframe
  try { notInFocus = document.activeElement !== elm; } catch (e) {}
  return notInFocus && elm.value !== checkVal
}

function isDirtyWithModifiers (elm, newVal) {
  var value = elm.value;
  var modifiers = elm._vModifiers; // injected by v-model runtime
  if (isDef(modifiers)) {
    if (modifiers.number) {
      return toNumber(value) !== toNumber(newVal)
    }
    if (modifiers.trim) {
      return value.trim() !== newVal.trim()
    }
  }
  return value !== newVal
}

var domProps = {
  create: updateDOMProps,
  update: updateDOMProps
};

/*  */

var parseStyleText = cached(function (cssText) {
  var res = {};
  var listDelimiter = /;(?![^(]*\))/g;
  var propertyDelimiter = /:(.+)/;
  cssText.split(listDelimiter).forEach(function (item) {
    if (item) {
      var tmp = item.split(propertyDelimiter);
      tmp.length > 1 && (res[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return res
});

// merge static and dynamic style data on the same vnode
function normalizeStyleData (data) {
  var style = normalizeStyleBinding(data.style);
  // static style is pre-processed into an object during compilation
  // and is always a fresh object, so it's safe to merge into it
  return data.staticStyle
    ? extend(data.staticStyle, style)
    : style
}

// normalize possible array / string values into Object
function normalizeStyleBinding (bindingStyle) {
  if (Array.isArray(bindingStyle)) {
    return toObject(bindingStyle)
  }
  if (typeof bindingStyle === 'string') {
    return parseStyleText(bindingStyle)
  }
  return bindingStyle
}

/**
 * parent component style should be after child's
 * so that parent component's style could override it
 */
function getStyle (vnode, checkChild) {
  var res = {};
  var styleData;

  if (checkChild) {
    var childNode = vnode;
    while (childNode.componentInstance) {
      childNode = childNode.componentInstance._vnode;
      if (
        childNode && childNode.data &&
        (styleData = normalizeStyleData(childNode.data))
      ) {
        extend(res, styleData);
      }
    }
  }

  if ((styleData = normalizeStyleData(vnode.data))) {
    extend(res, styleData);
  }

  var parentNode = vnode;
  while ((parentNode = parentNode.parent)) {
    if (parentNode.data && (styleData = normalizeStyleData(parentNode.data))) {
      extend(res, styleData);
    }
  }
  return res
}

/*  */

var cssVarRE = /^--/;
var importantRE = /\s*!important$/;
var setProp = function (el, name, val) {
  /* istanbul ignore if */
  if (cssVarRE.test(name)) {
    el.style.setProperty(name, val);
  } else if (importantRE.test(val)) {
    el.style.setProperty(hyphenate(name), val.replace(importantRE, ''), 'important');
  } else {
    var normalizedName = normalize(name);
    if (Array.isArray(val)) {
      // Support values array created by autoprefixer, e.g.
      // {display: ["-webkit-box", "-ms-flexbox", "flex"]}
      // Set them one by one, and the browser will only set those it can recognize
      for (var i = 0, len = val.length; i < len; i++) {
        el.style[normalizedName] = val[i];
      }
    } else {
      el.style[normalizedName] = val;
    }
  }
};

var vendorNames = ['Webkit', 'Moz', 'ms'];

var emptyStyle;
var normalize = cached(function (prop) {
  emptyStyle = emptyStyle || document.createElement('div').style;
  prop = camelize(prop);
  if (prop !== 'filter' && (prop in emptyStyle)) {
    return prop
  }
  var capName = prop.charAt(0).toUpperCase() + prop.slice(1);
  for (var i = 0; i < vendorNames.length; i++) {
    var name = vendorNames[i] + capName;
    if (name in emptyStyle) {
      return name
    }
  }
});

function updateStyle (oldVnode, vnode) {
  var data = vnode.data;
  var oldData = oldVnode.data;

  if (isUndef(data.staticStyle) && isUndef(data.style) &&
    isUndef(oldData.staticStyle) && isUndef(oldData.style)
  ) {
    return
  }

  var cur, name;
  var el = vnode.elm;
  var oldStaticStyle = oldData.staticStyle;
  var oldStyleBinding = oldData.normalizedStyle || oldData.style || {};

  // if static style exists, stylebinding already merged into it when doing normalizeStyleData
  var oldStyle = oldStaticStyle || oldStyleBinding;

  var style = normalizeStyleBinding(vnode.data.style) || {};

  // store normalized style under a different key for next diff
  // make sure to clone it if it's reactive, since the user likely wants
  // to mutate it.
  vnode.data.normalizedStyle = isDef(style.__ob__)
    ? extend({}, style)
    : style;

  var newStyle = getStyle(vnode, true);

  for (name in oldStyle) {
    if (isUndef(newStyle[name])) {
      setProp(el, name, '');
    }
  }
  for (name in newStyle) {
    cur = newStyle[name];
    if (cur !== oldStyle[name]) {
      // ie9 setting to null has no effect, must use empty string
      setProp(el, name, cur == null ? '' : cur);
    }
  }
}

var style = {
  create: updateStyle,
  update: updateStyle
};

/*  */

var whitespaceRE = /\s+/;

/**
 * Add class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function addClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(whitespaceRE).forEach(function (c) { return el.classList.add(c); });
    } else {
      el.classList.add(cls);
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    if (cur.indexOf(' ' + cls + ' ') < 0) {
      el.setAttribute('class', (cur + cls).trim());
    }
  }
}

/**
 * Remove class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function removeClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(whitespaceRE).forEach(function (c) { return el.classList.remove(c); });
    } else {
      el.classList.remove(cls);
    }
    if (!el.classList.length) {
      el.removeAttribute('class');
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    var tar = ' ' + cls + ' ';
    while (cur.indexOf(tar) >= 0) {
      cur = cur.replace(tar, ' ');
    }
    cur = cur.trim();
    if (cur) {
      el.setAttribute('class', cur);
    } else {
      el.removeAttribute('class');
    }
  }
}

/*  */

function resolveTransition (def$$1) {
  if (!def$$1) {
    return
  }
  /* istanbul ignore else */
  if (typeof def$$1 === 'object') {
    var res = {};
    if (def$$1.css !== false) {
      extend(res, autoCssTransition(def$$1.name || 'v'));
    }
    extend(res, def$$1);
    return res
  } else if (typeof def$$1 === 'string') {
    return autoCssTransition(def$$1)
  }
}

var autoCssTransition = cached(function (name) {
  return {
    enterClass: (name + "-enter"),
    enterToClass: (name + "-enter-to"),
    enterActiveClass: (name + "-enter-active"),
    leaveClass: (name + "-leave"),
    leaveToClass: (name + "-leave-to"),
    leaveActiveClass: (name + "-leave-active")
  }
});

var hasTransition = inBrowser && !isIE9;
var TRANSITION = 'transition';
var ANIMATION = 'animation';

// Transition property/event sniffing
var transitionProp = 'transition';
var transitionEndEvent = 'transitionend';
var animationProp = 'animation';
var animationEndEvent = 'animationend';
if (hasTransition) {
  /* istanbul ignore if */
  if (window.ontransitionend === undefined &&
    window.onwebkittransitionend !== undefined
  ) {
    transitionProp = 'WebkitTransition';
    transitionEndEvent = 'webkitTransitionEnd';
  }
  if (window.onanimationend === undefined &&
    window.onwebkitanimationend !== undefined
  ) {
    animationProp = 'WebkitAnimation';
    animationEndEvent = 'webkitAnimationEnd';
  }
}

// binding to window is necessary to make hot reload work in IE in strict mode
var raf = inBrowser
  ? window.requestAnimationFrame
    ? window.requestAnimationFrame.bind(window)
    : setTimeout
  : /* istanbul ignore next */ function (fn) { return fn(); };

function nextFrame (fn) {
  raf(function () {
    raf(fn);
  });
}

function addTransitionClass (el, cls) {
  var transitionClasses = el._transitionClasses || (el._transitionClasses = []);
  if (transitionClasses.indexOf(cls) < 0) {
    transitionClasses.push(cls);
    addClass(el, cls);
  }
}

function removeTransitionClass (el, cls) {
  if (el._transitionClasses) {
    remove(el._transitionClasses, cls);
  }
  removeClass(el, cls);
}

function whenTransitionEnds (
  el,
  expectedType,
  cb
) {
  var ref = getTransitionInfo(el, expectedType);
  var type = ref.type;
  var timeout = ref.timeout;
  var propCount = ref.propCount;
  if (!type) { return cb() }
  var event = type === TRANSITION ? transitionEndEvent : animationEndEvent;
  var ended = 0;
  var end = function () {
    el.removeEventListener(event, onEnd);
    cb();
  };
  var onEnd = function (e) {
    if (e.target === el) {
      if (++ended >= propCount) {
        end();
      }
    }
  };
  setTimeout(function () {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(event, onEnd);
}

var transformRE = /\b(transform|all)(,|$)/;

function getTransitionInfo (el, expectedType) {
  var styles = window.getComputedStyle(el);
  // JSDOM may return undefined for transition properties
  var transitionDelays = (styles[transitionProp + 'Delay'] || '').split(', ');
  var transitionDurations = (styles[transitionProp + 'Duration'] || '').split(', ');
  var transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  var animationDelays = (styles[animationProp + 'Delay'] || '').split(', ');
  var animationDurations = (styles[animationProp + 'Duration'] || '').split(', ');
  var animationTimeout = getTimeout(animationDelays, animationDurations);

  var type;
  var timeout = 0;
  var propCount = 0;
  /* istanbul ignore if */
  if (expectedType === TRANSITION) {
    if (transitionTimeout > 0) {
      type = TRANSITION;
      timeout = transitionTimeout;
      propCount = transitionDurations.length;
    }
  } else if (expectedType === ANIMATION) {
    if (animationTimeout > 0) {
      type = ANIMATION;
      timeout = animationTimeout;
      propCount = animationDurations.length;
    }
  } else {
    timeout = Math.max(transitionTimeout, animationTimeout);
    type = timeout > 0
      ? transitionTimeout > animationTimeout
        ? TRANSITION
        : ANIMATION
      : null;
    propCount = type
      ? type === TRANSITION
        ? transitionDurations.length
        : animationDurations.length
      : 0;
  }
  var hasTransform =
    type === TRANSITION &&
    transformRE.test(styles[transitionProp + 'Property']);
  return {
    type: type,
    timeout: timeout,
    propCount: propCount,
    hasTransform: hasTransform
  }
}

function getTimeout (delays, durations) {
  /* istanbul ignore next */
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }

  return Math.max.apply(null, durations.map(function (d, i) {
    return toMs(d) + toMs(delays[i])
  }))
}

// Old versions of Chromium (below 61.0.3163.100) formats floating pointer numbers
// in a locale-dependent way, using a comma instead of a dot.
// If comma is not replaced with a dot, the input will be rounded down (i.e. acting
// as a floor function) causing unexpected behaviors
function toMs (s) {
  return Number(s.slice(0, -1).replace(',', '.')) * 1000
}

/*  */

function enter (vnode, toggleDisplay) {
  var el = vnode.elm;

  // call leave callback now
  if (isDef(el._leaveCb)) {
    el._leaveCb.cancelled = true;
    el._leaveCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return
  }

  /* istanbul ignore if */
  if (isDef(el._enterCb) || el.nodeType !== 1) {
    return
  }

  var css = data.css;
  var type = data.type;
  var enterClass = data.enterClass;
  var enterToClass = data.enterToClass;
  var enterActiveClass = data.enterActiveClass;
  var appearClass = data.appearClass;
  var appearToClass = data.appearToClass;
  var appearActiveClass = data.appearActiveClass;
  var beforeEnter = data.beforeEnter;
  var enter = data.enter;
  var afterEnter = data.afterEnter;
  var enterCancelled = data.enterCancelled;
  var beforeAppear = data.beforeAppear;
  var appear = data.appear;
  var afterAppear = data.afterAppear;
  var appearCancelled = data.appearCancelled;
  var duration = data.duration;

  // activeInstance will always be the <transition> component managing this
  // transition. One edge case to check is when the <transition> is placed
  // as the root node of a child component. In that case we need to check
  // <transition>'s parent for appear check.
  var context = activeInstance;
  var transitionNode = activeInstance.$vnode;
  while (transitionNode && transitionNode.parent) {
    transitionNode = transitionNode.parent;
    context = transitionNode.context;
  }

  var isAppear = !context._isMounted || !vnode.isRootInsert;

  if (isAppear && !appear && appear !== '') {
    return
  }

  var startClass = isAppear && appearClass
    ? appearClass
    : enterClass;
  var activeClass = isAppear && appearActiveClass
    ? appearActiveClass
    : enterActiveClass;
  var toClass = isAppear && appearToClass
    ? appearToClass
    : enterToClass;

  var beforeEnterHook = isAppear
    ? (beforeAppear || beforeEnter)
    : beforeEnter;
  var enterHook = isAppear
    ? (typeof appear === 'function' ? appear : enter)
    : enter;
  var afterEnterHook = isAppear
    ? (afterAppear || afterEnter)
    : afterEnter;
  var enterCancelledHook = isAppear
    ? (appearCancelled || enterCancelled)
    : enterCancelled;

  var explicitEnterDuration = toNumber(
    isObject(duration)
      ? duration.enter
      : duration
  );

  if (process.env.NODE_ENV !== 'production' && explicitEnterDuration != null) {
    checkDuration(explicitEnterDuration, 'enter', vnode);
  }

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(enterHook);

  var cb = el._enterCb = once(function () {
    if (expectsCSS) {
      removeTransitionClass(el, toClass);
      removeTransitionClass(el, activeClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, startClass);
      }
      enterCancelledHook && enterCancelledHook(el);
    } else {
      afterEnterHook && afterEnterHook(el);
    }
    el._enterCb = null;
  });

  if (!vnode.data.show) {
    // remove pending leave element on enter by injecting an insert hook
    mergeVNodeHook(vnode, 'insert', function () {
      var parent = el.parentNode;
      var pendingNode = parent && parent._pending && parent._pending[vnode.key];
      if (pendingNode &&
        pendingNode.tag === vnode.tag &&
        pendingNode.elm._leaveCb
      ) {
        pendingNode.elm._leaveCb();
      }
      enterHook && enterHook(el, cb);
    });
  }

  // start enter transition
  beforeEnterHook && beforeEnterHook(el);
  if (expectsCSS) {
    addTransitionClass(el, startClass);
    addTransitionClass(el, activeClass);
    nextFrame(function () {
      removeTransitionClass(el, startClass);
      if (!cb.cancelled) {
        addTransitionClass(el, toClass);
        if (!userWantsControl) {
          if (isValidDuration(explicitEnterDuration)) {
            setTimeout(cb, explicitEnterDuration);
          } else {
            whenTransitionEnds(el, type, cb);
          }
        }
      }
    });
  }

  if (vnode.data.show) {
    toggleDisplay && toggleDisplay();
    enterHook && enterHook(el, cb);
  }

  if (!expectsCSS && !userWantsControl) {
    cb();
  }
}

function leave (vnode, rm) {
  var el = vnode.elm;

  // call enter callback now
  if (isDef(el._enterCb)) {
    el._enterCb.cancelled = true;
    el._enterCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data) || el.nodeType !== 1) {
    return rm()
  }

  /* istanbul ignore if */
  if (isDef(el._leaveCb)) {
    return
  }

  var css = data.css;
  var type = data.type;
  var leaveClass = data.leaveClass;
  var leaveToClass = data.leaveToClass;
  var leaveActiveClass = data.leaveActiveClass;
  var beforeLeave = data.beforeLeave;
  var leave = data.leave;
  var afterLeave = data.afterLeave;
  var leaveCancelled = data.leaveCancelled;
  var delayLeave = data.delayLeave;
  var duration = data.duration;

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(leave);

  var explicitLeaveDuration = toNumber(
    isObject(duration)
      ? duration.leave
      : duration
  );

  if (process.env.NODE_ENV !== 'production' && isDef(explicitLeaveDuration)) {
    checkDuration(explicitLeaveDuration, 'leave', vnode);
  }

  var cb = el._leaveCb = once(function () {
    if (el.parentNode && el.parentNode._pending) {
      el.parentNode._pending[vnode.key] = null;
    }
    if (expectsCSS) {
      removeTransitionClass(el, leaveToClass);
      removeTransitionClass(el, leaveActiveClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, leaveClass);
      }
      leaveCancelled && leaveCancelled(el);
    } else {
      rm();
      afterLeave && afterLeave(el);
    }
    el._leaveCb = null;
  });

  if (delayLeave) {
    delayLeave(performLeave);
  } else {
    performLeave();
  }

  function performLeave () {
    // the delayed leave may have already been cancelled
    if (cb.cancelled) {
      return
    }
    // record leaving element
    if (!vnode.data.show && el.parentNode) {
      (el.parentNode._pending || (el.parentNode._pending = {}))[(vnode.key)] = vnode;
    }
    beforeLeave && beforeLeave(el);
    if (expectsCSS) {
      addTransitionClass(el, leaveClass);
      addTransitionClass(el, leaveActiveClass);
      nextFrame(function () {
        removeTransitionClass(el, leaveClass);
        if (!cb.cancelled) {
          addTransitionClass(el, leaveToClass);
          if (!userWantsControl) {
            if (isValidDuration(explicitLeaveDuration)) {
              setTimeout(cb, explicitLeaveDuration);
            } else {
              whenTransitionEnds(el, type, cb);
            }
          }
        }
      });
    }
    leave && leave(el, cb);
    if (!expectsCSS && !userWantsControl) {
      cb();
    }
  }
}

// only used in dev mode
function checkDuration (val, name, vnode) {
  if (typeof val !== 'number') {
    warn(
      "<transition> explicit " + name + " duration is not a valid number - " +
      "got " + (JSON.stringify(val)) + ".",
      vnode.context
    );
  } else if (isNaN(val)) {
    warn(
      "<transition> explicit " + name + " duration is NaN - " +
      'the duration expression might be incorrect.',
      vnode.context
    );
  }
}

function isValidDuration (val) {
  return typeof val === 'number' && !isNaN(val)
}

/**
 * Normalize a transition hook's argument length. The hook may be:
 * - a merged hook (invoker) with the original in .fns
 * - a wrapped component method (check ._length)
 * - a plain function (.length)
 */
function getHookArgumentsLength (fn) {
  if (isUndef(fn)) {
    return false
  }
  var invokerFns = fn.fns;
  if (isDef(invokerFns)) {
    // invoker
    return getHookArgumentsLength(
      Array.isArray(invokerFns)
        ? invokerFns[0]
        : invokerFns
    )
  } else {
    return (fn._length || fn.length) > 1
  }
}

function _enter (_, vnode) {
  if (vnode.data.show !== true) {
    enter(vnode);
  }
}

var transition = inBrowser ? {
  create: _enter,
  activate: _enter,
  remove: function remove$$1 (vnode, rm) {
    /* istanbul ignore else */
    if (vnode.data.show !== true) {
      leave(vnode, rm);
    } else {
      rm();
    }
  }
} : {};

var platformModules = [
  attrs,
  klass,
  events,
  domProps,
  style,
  transition
];

/*  */

// the directive module should be applied last, after all
// built-in modules have been applied.
var modules = platformModules.concat(baseModules);

var patch = createPatchFunction({ nodeOps: nodeOps, modules: modules });

/**
 * Not type checking this file because flow doesn't like attaching
 * properties to Elements.
 */

/* istanbul ignore if */
if (isIE9) {
  // http://www.matts411.com/post/internet-explorer-9-oninput/
  document.addEventListener('selectionchange', function () {
    var el = document.activeElement;
    if (el && el.vmodel) {
      trigger(el, 'input');
    }
  });
}

var directive = {
  inserted: function inserted (el, binding, vnode, oldVnode) {
    if (vnode.tag === 'select') {
      // #6903
      if (oldVnode.elm && !oldVnode.elm._vOptions) {
        mergeVNodeHook(vnode, 'postpatch', function () {
          directive.componentUpdated(el, binding, vnode);
        });
      } else {
        setSelected(el, binding, vnode.context);
      }
      el._vOptions = [].map.call(el.options, getValue);
    } else if (vnode.tag === 'textarea' || isTextInputType(el.type)) {
      el._vModifiers = binding.modifiers;
      if (!binding.modifiers.lazy) {
        el.addEventListener('compositionstart', onCompositionStart);
        el.addEventListener('compositionend', onCompositionEnd);
        // Safari < 10.2 & UIWebView doesn't fire compositionend when
        // switching focus before confirming composition choice
        // this also fixes the issue where some browsers e.g. iOS Chrome
        // fires "change" instead of "input" on autocomplete.
        el.addEventListener('change', onCompositionEnd);
        /* istanbul ignore if */
        if (isIE9) {
          el.vmodel = true;
        }
      }
    }
  },

  componentUpdated: function componentUpdated (el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      // in case the options rendered by v-for have changed,
      // it's possible that the value is out-of-sync with the rendered options.
      // detect such cases and filter out values that no longer has a matching
      // option in the DOM.
      var prevOptions = el._vOptions;
      var curOptions = el._vOptions = [].map.call(el.options, getValue);
      if (curOptions.some(function (o, i) { return !looseEqual(o, prevOptions[i]); })) {
        // trigger change event if
        // no matching option found for at least one value
        var needReset = el.multiple
          ? binding.value.some(function (v) { return hasNoMatchingOption(v, curOptions); })
          : binding.value !== binding.oldValue && hasNoMatchingOption(binding.value, curOptions);
        if (needReset) {
          trigger(el, 'change');
        }
      }
    }
  }
};

function setSelected (el, binding, vm) {
  actuallySetSelected(el, binding, vm);
  /* istanbul ignore if */
  if (isIE || isEdge) {
    setTimeout(function () {
      actuallySetSelected(el, binding, vm);
    }, 0);
  }
}

function actuallySetSelected (el, binding, vm) {
  var value = binding.value;
  var isMultiple = el.multiple;
  if (isMultiple && !Array.isArray(value)) {
    process.env.NODE_ENV !== 'production' && warn(
      "<select multiple v-model=\"" + (binding.expression) + "\"> " +
      "expects an Array value for its binding, but got " + (Object.prototype.toString.call(value).slice(8, -1)),
      vm
    );
    return
  }
  var selected, option;
  for (var i = 0, l = el.options.length; i < l; i++) {
    option = el.options[i];
    if (isMultiple) {
      selected = looseIndexOf(value, getValue(option)) > -1;
      if (option.selected !== selected) {
        option.selected = selected;
      }
    } else {
      if (looseEqual(getValue(option), value)) {
        if (el.selectedIndex !== i) {
          el.selectedIndex = i;
        }
        return
      }
    }
  }
  if (!isMultiple) {
    el.selectedIndex = -1;
  }
}

function hasNoMatchingOption (value, options) {
  return options.every(function (o) { return !looseEqual(o, value); })
}

function getValue (option) {
  return '_value' in option
    ? option._value
    : option.value
}

function onCompositionStart (e) {
  e.target.composing = true;
}

function onCompositionEnd (e) {
  // prevent triggering an input event for no reason
  if (!e.target.composing) { return }
  e.target.composing = false;
  trigger(e.target, 'input');
}

function trigger (el, type) {
  var e = document.createEvent('HTMLEvents');
  e.initEvent(type, true, true);
  el.dispatchEvent(e);
}

/*  */

// recursively search for possible transition defined inside the component root
function locateNode (vnode) {
  return vnode.componentInstance && (!vnode.data || !vnode.data.transition)
    ? locateNode(vnode.componentInstance._vnode)
    : vnode
}

var show = {
  bind: function bind (el, ref, vnode) {
    var value = ref.value;

    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    var originalDisplay = el.__vOriginalDisplay =
      el.style.display === 'none' ? '' : el.style.display;
    if (value && transition$$1) {
      vnode.data.show = true;
      enter(vnode, function () {
        el.style.display = originalDisplay;
      });
    } else {
      el.style.display = value ? originalDisplay : 'none';
    }
  },

  update: function update (el, ref, vnode) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    /* istanbul ignore if */
    if (!value === !oldValue) { return }
    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    if (transition$$1) {
      vnode.data.show = true;
      if (value) {
        enter(vnode, function () {
          el.style.display = el.__vOriginalDisplay;
        });
      } else {
        leave(vnode, function () {
          el.style.display = 'none';
        });
      }
    } else {
      el.style.display = value ? el.__vOriginalDisplay : 'none';
    }
  },

  unbind: function unbind (
    el,
    binding,
    vnode,
    oldVnode,
    isDestroy
  ) {
    if (!isDestroy) {
      el.style.display = el.__vOriginalDisplay;
    }
  }
};

var platformDirectives = {
  model: directive,
  show: show
};

/*  */

var transitionProps = {
  name: String,
  appear: Boolean,
  css: Boolean,
  mode: String,
  type: String,
  enterClass: String,
  leaveClass: String,
  enterToClass: String,
  leaveToClass: String,
  enterActiveClass: String,
  leaveActiveClass: String,
  appearClass: String,
  appearActiveClass: String,
  appearToClass: String,
  duration: [Number, String, Object]
};

// in case the child is also an abstract component, e.g. <keep-alive>
// we want to recursively retrieve the real component to be rendered
function getRealChild (vnode) {
  var compOptions = vnode && vnode.componentOptions;
  if (compOptions && compOptions.Ctor.options.abstract) {
    return getRealChild(getFirstComponentChild(compOptions.children))
  } else {
    return vnode
  }
}

function extractTransitionData (comp) {
  var data = {};
  var options = comp.$options;
  // props
  for (var key in options.propsData) {
    data[key] = comp[key];
  }
  // events.
  // extract listeners and pass them directly to the transition methods
  var listeners = options._parentListeners;
  for (var key$1 in listeners) {
    data[camelize(key$1)] = listeners[key$1];
  }
  return data
}

function placeholder (h, rawChild) {
  if (/\d-keep-alive$/.test(rawChild.tag)) {
    return h('keep-alive', {
      props: rawChild.componentOptions.propsData
    })
  }
}

function hasParentTransition (vnode) {
  while ((vnode = vnode.parent)) {
    if (vnode.data.transition) {
      return true
    }
  }
}

function isSameChild (child, oldChild) {
  return oldChild.key === child.key && oldChild.tag === child.tag
}

var isNotTextNode = function (c) { return c.tag || isAsyncPlaceholder(c); };

var isVShowDirective = function (d) { return d.name === 'show'; };

var Transition = {
  name: 'transition',
  props: transitionProps,
  abstract: true,

  render: function render (h) {
    var this$1 = this;

    var children = this.$slots.default;
    if (!children) {
      return
    }

    // filter out text nodes (possible whitespaces)
    children = children.filter(isNotTextNode);
    /* istanbul ignore if */
    if (!children.length) {
      return
    }

    // warn multiple elements
    if (process.env.NODE_ENV !== 'production' && children.length > 1) {
      warn(
        '<transition> can only be used on a single element. Use ' +
        '<transition-group> for lists.',
        this.$parent
      );
    }

    var mode = this.mode;

    // warn invalid mode
    if (process.env.NODE_ENV !== 'production' &&
      mode && mode !== 'in-out' && mode !== 'out-in'
    ) {
      warn(
        'invalid <transition> mode: ' + mode,
        this.$parent
      );
    }

    var rawChild = children[0];

    // if this is a component root node and the component's
    // parent container node also has transition, skip.
    if (hasParentTransition(this.$vnode)) {
      return rawChild
    }

    // apply transition data to child
    // use getRealChild() to ignore abstract components e.g. keep-alive
    var child = getRealChild(rawChild);
    /* istanbul ignore if */
    if (!child) {
      return rawChild
    }

    if (this._leaving) {
      return placeholder(h, rawChild)
    }

    // ensure a key that is unique to the vnode type and to this transition
    // component instance. This key will be used to remove pending leaving nodes
    // during entering.
    var id = "__transition-" + (this._uid) + "-";
    child.key = child.key == null
      ? child.isComment
        ? id + 'comment'
        : id + child.tag
      : isPrimitive(child.key)
        ? (String(child.key).indexOf(id) === 0 ? child.key : id + child.key)
        : child.key;

    var data = (child.data || (child.data = {})).transition = extractTransitionData(this);
    var oldRawChild = this._vnode;
    var oldChild = getRealChild(oldRawChild);

    // mark v-show
    // so that the transition module can hand over the control to the directive
    if (child.data.directives && child.data.directives.some(isVShowDirective)) {
      child.data.show = true;
    }

    if (
      oldChild &&
      oldChild.data &&
      !isSameChild(child, oldChild) &&
      !isAsyncPlaceholder(oldChild) &&
      // #6687 component root is a comment node
      !(oldChild.componentInstance && oldChild.componentInstance._vnode.isComment)
    ) {
      // replace old child transition data with fresh one
      // important for dynamic transitions!
      var oldData = oldChild.data.transition = extend({}, data);
      // handle transition mode
      if (mode === 'out-in') {
        // return placeholder node and queue update when leave finishes
        this._leaving = true;
        mergeVNodeHook(oldData, 'afterLeave', function () {
          this$1._leaving = false;
          this$1.$forceUpdate();
        });
        return placeholder(h, rawChild)
      } else if (mode === 'in-out') {
        if (isAsyncPlaceholder(child)) {
          return oldRawChild
        }
        var delayedLeave;
        var performLeave = function () { delayedLeave(); };
        mergeVNodeHook(data, 'afterEnter', performLeave);
        mergeVNodeHook(data, 'enterCancelled', performLeave);
        mergeVNodeHook(oldData, 'delayLeave', function (leave) { delayedLeave = leave; });
      }
    }

    return rawChild
  }
};

/*  */

var props = extend({
  tag: String,
  moveClass: String
}, transitionProps);

delete props.mode;

var TransitionGroup = {
  props: props,

  beforeMount: function beforeMount () {
    var this$1 = this;

    var update = this._update;
    this._update = function (vnode, hydrating) {
      var restoreActiveInstance = setActiveInstance(this$1);
      // force removing pass
      this$1.__patch__(
        this$1._vnode,
        this$1.kept,
        false, // hydrating
        true // removeOnly (!important, avoids unnecessary moves)
      );
      this$1._vnode = this$1.kept;
      restoreActiveInstance();
      update.call(this$1, vnode, hydrating);
    };
  },

  render: function render (h) {
    var tag = this.tag || this.$vnode.data.tag || 'span';
    var map = Object.create(null);
    var prevChildren = this.prevChildren = this.children;
    var rawChildren = this.$slots.default || [];
    var children = this.children = [];
    var transitionData = extractTransitionData(this);

    for (var i = 0; i < rawChildren.length; i++) {
      var c = rawChildren[i];
      if (c.tag) {
        if (c.key != null && String(c.key).indexOf('__vlist') !== 0) {
          children.push(c);
          map[c.key] = c
          ;(c.data || (c.data = {})).transition = transitionData;
        } else if (process.env.NODE_ENV !== 'production') {
          var opts = c.componentOptions;
          var name = opts ? (opts.Ctor.options.name || opts.tag || '') : c.tag;
          warn(("<transition-group> children must be keyed: <" + name + ">"));
        }
      }
    }

    if (prevChildren) {
      var kept = [];
      var removed = [];
      for (var i$1 = 0; i$1 < prevChildren.length; i$1++) {
        var c$1 = prevChildren[i$1];
        c$1.data.transition = transitionData;
        c$1.data.pos = c$1.elm.getBoundingClientRect();
        if (map[c$1.key]) {
          kept.push(c$1);
        } else {
          removed.push(c$1);
        }
      }
      this.kept = h(tag, null, kept);
      this.removed = removed;
    }

    return h(tag, null, children)
  },

  updated: function updated () {
    var children = this.prevChildren;
    var moveClass = this.moveClass || ((this.name || 'v') + '-move');
    if (!children.length || !this.hasMove(children[0].elm, moveClass)) {
      return
    }

    // we divide the work into three loops to avoid mixing DOM reads and writes
    // in each iteration - which helps prevent layout thrashing.
    children.forEach(callPendingCbs);
    children.forEach(recordPosition);
    children.forEach(applyTranslation);

    // force reflow to put everything in position
    // assign to this to avoid being removed in tree-shaking
    // $flow-disable-line
    this._reflow = document.body.offsetHeight;

    children.forEach(function (c) {
      if (c.data.moved) {
        var el = c.elm;
        var s = el.style;
        addTransitionClass(el, moveClass);
        s.transform = s.WebkitTransform = s.transitionDuration = '';
        el.addEventListener(transitionEndEvent, el._moveCb = function cb (e) {
          if (e && e.target !== el) {
            return
          }
          if (!e || /transform$/.test(e.propertyName)) {
            el.removeEventListener(transitionEndEvent, cb);
            el._moveCb = null;
            removeTransitionClass(el, moveClass);
          }
        });
      }
    });
  },

  methods: {
    hasMove: function hasMove (el, moveClass) {
      /* istanbul ignore if */
      if (!hasTransition) {
        return false
      }
      /* istanbul ignore if */
      if (this._hasMove) {
        return this._hasMove
      }
      // Detect whether an element with the move class applied has
      // CSS transitions. Since the element may be inside an entering
      // transition at this very moment, we make a clone of it and remove
      // all other transition classes applied to ensure only the move class
      // is applied.
      var clone = el.cloneNode();
      if (el._transitionClasses) {
        el._transitionClasses.forEach(function (cls) { removeClass(clone, cls); });
      }
      addClass(clone, moveClass);
      clone.style.display = 'none';
      this.$el.appendChild(clone);
      var info = getTransitionInfo(clone);
      this.$el.removeChild(clone);
      return (this._hasMove = info.hasTransform)
    }
  }
};

function callPendingCbs (c) {
  /* istanbul ignore if */
  if (c.elm._moveCb) {
    c.elm._moveCb();
  }
  /* istanbul ignore if */
  if (c.elm._enterCb) {
    c.elm._enterCb();
  }
}

function recordPosition (c) {
  c.data.newPos = c.elm.getBoundingClientRect();
}

function applyTranslation (c) {
  var oldPos = c.data.pos;
  var newPos = c.data.newPos;
  var dx = oldPos.left - newPos.left;
  var dy = oldPos.top - newPos.top;
  if (dx || dy) {
    c.data.moved = true;
    var s = c.elm.style;
    s.transform = s.WebkitTransform = "translate(" + dx + "px," + dy + "px)";
    s.transitionDuration = '0s';
  }
}

var platformComponents = {
  Transition: Transition,
  TransitionGroup: TransitionGroup
};

/*  */

// install platform specific utils
Vue.config.mustUseProp = mustUseProp;
Vue.config.isReservedTag = isReservedTag;
Vue.config.isReservedAttr = isReservedAttr;
Vue.config.getTagNamespace = getTagNamespace;
Vue.config.isUnknownElement = isUnknownElement;

// install platform runtime directives & components
extend(Vue.options.directives, platformDirectives);
extend(Vue.options.components, platformComponents);

// install platform patch function
Vue.prototype.__patch__ = inBrowser ? patch : noop;

// public mount method
Vue.prototype.$mount = function (
  el,
  hydrating
) {
  el = el && inBrowser ? query(el) : undefined;
  return mountComponent(this, el, hydrating)
};

// devtools global hook
/* istanbul ignore next */
if (inBrowser) {
  setTimeout(function () {
    if (config.devtools) {
      if (devtools) {
        devtools.emit('init', Vue);
      } else if (
        process.env.NODE_ENV !== 'production' &&
        process.env.NODE_ENV !== 'test'
      ) {
        console[console.info ? 'info' : 'log'](
          'Download the Vue Devtools extension for a better development experience:\n' +
          'https://github.com/vuejs/vue-devtools'
        );
      }
    }
    if (process.env.NODE_ENV !== 'production' &&
      process.env.NODE_ENV !== 'test' &&
      config.productionTip !== false &&
      typeof console !== 'undefined'
    ) {
      console[console.info ? 'info' : 'log'](
        "You are running Vue in development mode.\n" +
        "Make sure to turn on production mode when deploying for production.\n" +
        "See more tips at https://vuejs.org/guide/deployment.html"
      );
    }
  }, 0);
}

/*  */

var defaultTagRE = /\{\{((?:.|\r?\n)+?)\}\}/g;
var regexEscapeRE = /[-.*+?^${}()|[\]\/\\]/g;

var buildRegex = cached(function (delimiters) {
  var open = delimiters[0].replace(regexEscapeRE, '\\$&');
  var close = delimiters[1].replace(regexEscapeRE, '\\$&');
  return new RegExp(open + '((?:.|\\n)+?)' + close, 'g')
});



function parseText (
  text,
  delimiters
) {
  var tagRE = delimiters ? buildRegex(delimiters) : defaultTagRE;
  if (!tagRE.test(text)) {
    return
  }
  var tokens = [];
  var rawTokens = [];
  var lastIndex = tagRE.lastIndex = 0;
  var match, index, tokenValue;
  while ((match = tagRE.exec(text))) {
    index = match.index;
    // push text token
    if (index > lastIndex) {
      rawTokens.push(tokenValue = text.slice(lastIndex, index));
      tokens.push(JSON.stringify(tokenValue));
    }
    // tag token
    var exp = parseFilters(match[1].trim());
    tokens.push(("_s(" + exp + ")"));
    rawTokens.push({ '@binding': exp });
    lastIndex = index + match[0].length;
  }
  if (lastIndex < text.length) {
    rawTokens.push(tokenValue = text.slice(lastIndex));
    tokens.push(JSON.stringify(tokenValue));
  }
  return {
    expression: tokens.join('+'),
    tokens: rawTokens
  }
}

/*  */

function transformNode (el, options) {
  var warn = options.warn || baseWarn;
  var staticClass = getAndRemoveAttr(el, 'class');
  if (process.env.NODE_ENV !== 'production' && staticClass) {
    var res = parseText(staticClass, options.delimiters);
    if (res) {
      warn(
        "class=\"" + staticClass + "\": " +
        'Interpolation inside attributes has been removed. ' +
        'Use v-bind or the colon shorthand instead. For example, ' +
        'instead of <div class="{{ val }}">, use <div :class="val">.',
        el.rawAttrsMap['class']
      );
    }
  }
  if (staticClass) {
    el.staticClass = JSON.stringify(staticClass);
  }
  var classBinding = getBindingAttr(el, 'class', false /* getStatic */);
  if (classBinding) {
    el.classBinding = classBinding;
  }
}

function genData (el) {
  var data = '';
  if (el.staticClass) {
    data += "staticClass:" + (el.staticClass) + ",";
  }
  if (el.classBinding) {
    data += "class:" + (el.classBinding) + ",";
  }
  return data
}

var klass$1 = {
  staticKeys: ['staticClass'],
  transformNode: transformNode,
  genData: genData
};

/*  */

function transformNode$1 (el, options) {
  var warn = options.warn || baseWarn;
  var staticStyle = getAndRemoveAttr(el, 'style');
  if (staticStyle) {
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      var res = parseText(staticStyle, options.delimiters);
      if (res) {
        warn(
          "style=\"" + staticStyle + "\": " +
          'Interpolation inside attributes has been removed. ' +
          'Use v-bind or the colon shorthand instead. For example, ' +
          'instead of <div style="{{ val }}">, use <div :style="val">.',
          el.rawAttrsMap['style']
        );
      }
    }
    el.staticStyle = JSON.stringify(parseStyleText(staticStyle));
  }

  var styleBinding = getBindingAttr(el, 'style', false /* getStatic */);
  if (styleBinding) {
    el.styleBinding = styleBinding;
  }
}

function genData$1 (el) {
  var data = '';
  if (el.staticStyle) {
    data += "staticStyle:" + (el.staticStyle) + ",";
  }
  if (el.styleBinding) {
    data += "style:(" + (el.styleBinding) + "),";
  }
  return data
}

var style$1 = {
  staticKeys: ['staticStyle'],
  transformNode: transformNode$1,
  genData: genData$1
};

/*  */

var decoder;

var he = {
  decode: function decode (html) {
    decoder = decoder || document.createElement('div');
    decoder.innerHTML = html;
    return decoder.textContent
  }
};

/*  */

var isUnaryTag = makeMap(
  'area,base,br,col,embed,frame,hr,img,input,isindex,keygen,' +
  'link,meta,param,source,track,wbr'
);

// Elements that you can, intentionally, leave open
// (and which close themselves)
var canBeLeftOpenTag = makeMap(
  'colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr,source'
);

// HTML5 tags https://html.spec.whatwg.org/multipage/indices.html#elements-3
// Phrasing Content https://html.spec.whatwg.org/multipage/dom.html#phrasing-content
var isNonPhrasingTag = makeMap(
  'address,article,aside,base,blockquote,body,caption,col,colgroup,dd,' +
  'details,dialog,div,dl,dt,fieldset,figcaption,figure,footer,form,' +
  'h1,h2,h3,h4,h5,h6,head,header,hgroup,hr,html,legend,li,menuitem,meta,' +
  'optgroup,option,param,rp,rt,source,style,summary,tbody,td,tfoot,th,thead,' +
  'title,tr,track'
);

/**
 * Not type-checking this file because it's mostly vendor code.
 */

// Regular Expressions for parsing tags and attributes
var attribute = /^\s*([^\s"'<>\/=]+)(?:\s*(=)\s*(?:"([^"]*)"+|'([^']*)'+|([^\s"'=<>`]+)))?/;
var dynamicArgAttribute = /^\s*((?:v-[\w-]+:|@|:|#)\[[^=]+\][^\s"'<>\/=]*)(?:\s*(=)\s*(?:"([^"]*)"+|'([^']*)'+|([^\s"'=<>`]+)))?/;
var ncname = "[a-zA-Z_][\\-\\.0-9_a-zA-Z" + unicodeLetters + "]*";
var qnameCapture = "((?:" + ncname + "\\:)?" + ncname + ")";
var startTagOpen = new RegExp(("^<" + qnameCapture));
var startTagClose = /^\s*(\/?)>/;
var endTag = new RegExp(("^<\\/" + qnameCapture + "[^>]*>"));
var doctype = /^<!DOCTYPE [^>]+>/i;
// #7298: escape - to avoid being pased as HTML comment when inlined in page
var comment = /^<!\--/;
var conditionalComment = /^<!\[/;

// Special Elements (can contain anything)
var isPlainTextElement = makeMap('script,style,textarea', true);
var reCache = {};

var decodingMap = {
  '&lt;': '<',
  '&gt;': '>',
  '&quot;': '"',
  '&amp;': '&',
  '&#10;': '\n',
  '&#9;': '\t',
  '&#39;': "'"
};
var encodedAttr = /&(?:lt|gt|quot|amp|#39);/g;
var encodedAttrWithNewLines = /&(?:lt|gt|quot|amp|#39|#10|#9);/g;

// #5992
var isIgnoreNewlineTag = makeMap('pre,textarea', true);
var shouldIgnoreFirstNewline = function (tag, html) { return tag && isIgnoreNewlineTag(tag) && html[0] === '\n'; };

function decodeAttr (value, shouldDecodeNewlines) {
  var re = shouldDecodeNewlines ? encodedAttrWithNewLines : encodedAttr;
  return value.replace(re, function (match) { return decodingMap[match]; })
}

function parseHTML (html, options) {
  var stack = [];
  var expectHTML = options.expectHTML;
  var isUnaryTag$$1 = options.isUnaryTag || no;
  var canBeLeftOpenTag$$1 = options.canBeLeftOpenTag || no;
  var index = 0;
  var last, lastTag;
  while (html) {
    last = html;
    // Make sure we're not in a plaintext content element like script/style
    if (!lastTag || !isPlainTextElement(lastTag)) {
      var textEnd = html.indexOf('<');
      if (textEnd === 0) {
        // Comment:
        if (comment.test(html)) {
          var commentEnd = html.indexOf('-->');

          if (commentEnd >= 0) {
            if (options.shouldKeepComment) {
              options.comment(html.substring(4, commentEnd), index, index + commentEnd + 3);
            }
            advance(commentEnd + 3);
            continue
          }
        }

        // http://en.wikipedia.org/wiki/Conditional_comment#Downlevel-revealed_conditional_comment
        if (conditionalComment.test(html)) {
          var conditionalEnd = html.indexOf(']>');

          if (conditionalEnd >= 0) {
            advance(conditionalEnd + 2);
            continue
          }
        }

        // Doctype:
        var doctypeMatch = html.match(doctype);
        if (doctypeMatch) {
          advance(doctypeMatch[0].length);
          continue
        }

        // End tag:
        var endTagMatch = html.match(endTag);
        if (endTagMatch) {
          var curIndex = index;
          advance(endTagMatch[0].length);
          parseEndTag(endTagMatch[1], curIndex, index);
          continue
        }

        // Start tag:
        var startTagMatch = parseStartTag();
        if (startTagMatch) {
          handleStartTag(startTagMatch);
          if (shouldIgnoreFirstNewline(startTagMatch.tagName, html)) {
            advance(1);
          }
          continue
        }
      }

      var text = (void 0), rest = (void 0), next = (void 0);
      if (textEnd >= 0) {
        rest = html.slice(textEnd);
        while (
          !endTag.test(rest) &&
          !startTagOpen.test(rest) &&
          !comment.test(rest) &&
          !conditionalComment.test(rest)
        ) {
          // < in plain text, be forgiving and treat it as text
          next = rest.indexOf('<', 1);
          if (next < 0) { break }
          textEnd += next;
          rest = html.slice(textEnd);
        }
        text = html.substring(0, textEnd);
      }

      if (textEnd < 0) {
        text = html;
      }

      if (text) {
        advance(text.length);
      }

      if (options.chars && text) {
        options.chars(text, index - text.length, index);
      }
    } else {
      var endTagLength = 0;
      var stackedTag = lastTag.toLowerCase();
      var reStackedTag = reCache[stackedTag] || (reCache[stackedTag] = new RegExp('([\\s\\S]*?)(</' + stackedTag + '[^>]*>)', 'i'));
      var rest$1 = html.replace(reStackedTag, function (all, text, endTag) {
        endTagLength = endTag.length;
        if (!isPlainTextElement(stackedTag) && stackedTag !== 'noscript') {
          text = text
            .replace(/<!\--([\s\S]*?)-->/g, '$1') // #7298
            .replace(/<!\[CDATA\[([\s\S]*?)]]>/g, '$1');
        }
        if (shouldIgnoreFirstNewline(stackedTag, text)) {
          text = text.slice(1);
        }
        if (options.chars) {
          options.chars(text);
        }
        return ''
      });
      index += html.length - rest$1.length;
      html = rest$1;
      parseEndTag(stackedTag, index - endTagLength, index);
    }

    if (html === last) {
      options.chars && options.chars(html);
      if (process.env.NODE_ENV !== 'production' && !stack.length && options.warn) {
        options.warn(("Mal-formatted tag at end of template: \"" + html + "\""), { start: index + html.length });
      }
      break
    }
  }

  // Clean up any remaining tags
  parseEndTag();

  function advance (n) {
    index += n;
    html = html.substring(n);
  }

  function parseStartTag () {
    var start = html.match(startTagOpen);
    if (start) {
      var match = {
        tagName: start[1],
        attrs: [],
        start: index
      };
      advance(start[0].length);
      var end, attr;
      while (!(end = html.match(startTagClose)) && (attr = html.match(dynamicArgAttribute) || html.match(attribute))) {
        attr.start = index;
        advance(attr[0].length);
        attr.end = index;
        match.attrs.push(attr);
      }
      if (end) {
        match.unarySlash = end[1];
        advance(end[0].length);
        match.end = index;
        return match
      }
    }
  }

  function handleStartTag (match) {
    var tagName = match.tagName;
    var unarySlash = match.unarySlash;

    if (expectHTML) {
      if (lastTag === 'p' && isNonPhrasingTag(tagName)) {
        parseEndTag(lastTag);
      }
      if (canBeLeftOpenTag$$1(tagName) && lastTag === tagName) {
        parseEndTag(tagName);
      }
    }

    var unary = isUnaryTag$$1(tagName) || !!unarySlash;

    var l = match.attrs.length;
    var attrs = new Array(l);
    for (var i = 0; i < l; i++) {
      var args = match.attrs[i];
      var value = args[3] || args[4] || args[5] || '';
      var shouldDecodeNewlines = tagName === 'a' && args[1] === 'href'
        ? options.shouldDecodeNewlinesForHref
        : options.shouldDecodeNewlines;
      attrs[i] = {
        name: args[1],
        value: decodeAttr(value, shouldDecodeNewlines)
      };
      if (process.env.NODE_ENV !== 'production' && options.outputSourceRange) {
        attrs[i].start = args.start + args[0].match(/^\s*/).length;
        attrs[i].end = args.end;
      }
    }

    if (!unary) {
      stack.push({ tag: tagName, lowerCasedTag: tagName.toLowerCase(), attrs: attrs, start: match.start, end: match.end });
      lastTag = tagName;
    }

    if (options.start) {
      options.start(tagName, attrs, unary, match.start, match.end);
    }
  }

  function parseEndTag (tagName, start, end) {
    var pos, lowerCasedTagName;
    if (start == null) { start = index; }
    if (end == null) { end = index; }

    // Find the closest opened tag of the same type
    if (tagName) {
      lowerCasedTagName = tagName.toLowerCase();
      for (pos = stack.length - 1; pos >= 0; pos--) {
        if (stack[pos].lowerCasedTag === lowerCasedTagName) {
          break
        }
      }
    } else {
      // If no tag name is provided, clean shop
      pos = 0;
    }

    if (pos >= 0) {
      // Close all the open elements, up the stack
      for (var i = stack.length - 1; i >= pos; i--) {
        if (process.env.NODE_ENV !== 'production' &&
          (i > pos || !tagName) &&
          options.warn
        ) {
          options.warn(
            ("tag <" + (stack[i].tag) + "> has no matching end tag."),
            { start: stack[i].start }
          );
        }
        if (options.end) {
          options.end(stack[i].tag, start, end);
        }
      }

      // Remove the open elements from the stack
      stack.length = pos;
      lastTag = pos && stack[pos - 1].tag;
    } else if (lowerCasedTagName === 'br') {
      if (options.start) {
        options.start(tagName, [], true, start, end);
      }
    } else if (lowerCasedTagName === 'p') {
      if (options.start) {
        options.start(tagName, [], false, start, end);
      }
      if (options.end) {
        options.end(tagName, start, end);
      }
    }
  }
}

/*  */

var onRE = /^@|^v-on:/;
var dirRE = /^v-|^@|^:/;
var forAliasRE = /([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/;
var forIteratorRE = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/;
var stripParensRE = /^\(|\)$/g;
var dynamicArgRE = /^\[.*\]$/;

var argRE = /:(.*)$/;
var bindRE = /^:|^\.|^v-bind:/;
var modifierRE = /\.[^.]+/g;

var slotRE = /^v-slot(:|$)|^#/;

var lineBreakRE = /[\r\n]/;
var whitespaceRE$1 = /\s+/g;

var invalidAttributeRE = /[\s"'<>\/=]/;

var decodeHTMLCached = cached(he.decode);

var emptySlotScopeToken = "_empty_";

// configurable state
var warn$2;
var delimiters;
var transforms;
var preTransforms;
var postTransforms;
var platformIsPreTag;
var platformMustUseProp;
var platformGetTagNamespace;
var maybeComponent;

function createASTElement (
  tag,
  attrs,
  parent
) {
  return {
    type: 1,
    tag: tag,
    attrsList: attrs,
    attrsMap: makeAttrsMap(attrs),
    rawAttrsMap: {},
    parent: parent,
    children: []
  }
}

/**
 * Convert HTML string to AST.
 */
function parse (
  template,
  options
) {
  warn$2 = options.warn || baseWarn;

  platformIsPreTag = options.isPreTag || no;
  platformMustUseProp = options.mustUseProp || no;
  platformGetTagNamespace = options.getTagNamespace || no;
  var isReservedTag = options.isReservedTag || no;
  maybeComponent = function (el) { return !!el.component || !isReservedTag(el.tag); };

  transforms = pluckModuleFunction(options.modules, 'transformNode');
  preTransforms = pluckModuleFunction(options.modules, 'preTransformNode');
  postTransforms = pluckModuleFunction(options.modules, 'postTransformNode');

  delimiters = options.delimiters;

  var stack = [];
  var preserveWhitespace = options.preserveWhitespace !== false;
  var whitespaceOption = options.whitespace;
  var root;
  var currentParent;
  var inVPre = false;
  var inPre = false;
  var warned = false;

  function warnOnce (msg, range) {
    if (!warned) {
      warned = true;
      warn$2(msg, range);
    }
  }

  function closeElement (element) {
    trimEndingWhitespace(element);
    if (!inVPre && !element.processed) {
      element = processElement(element, options);
    }
    // tree management
    if (!stack.length && element !== root) {
      // allow root elements with v-if, v-else-if and v-else
      if (root.if && (element.elseif || element.else)) {
        if (process.env.NODE_ENV !== 'production') {
          checkRootConstraints(element);
        }
        addIfCondition(root, {
          exp: element.elseif,
          block: element
        });
      } else if (process.env.NODE_ENV !== 'production') {
        warnOnce(
          "Component template should contain exactly one root element. " +
          "If you are using v-if on multiple elements, " +
          "use v-else-if to chain them instead.",
          { start: element.start }
        );
      }
    }
    if (currentParent && !element.forbidden) {
      if (element.elseif || element.else) {
        processIfConditions(element, currentParent);
      } else {
        if (element.slotScope) {
          // scoped slot
          // keep it in the children list so that v-else(-if) conditions can
          // find it as the prev node.
          var name = element.slotTarget || '"default"'
          ;(currentParent.scopedSlots || (currentParent.scopedSlots = {}))[name] = element;
        }
        currentParent.children.push(element);
        element.parent = currentParent;
      }
    }

    // final children cleanup
    // filter out scoped slots
    element.children = element.children.filter(function (c) { return !(c).slotScope; });
    // remove trailing whitespace node again
    trimEndingWhitespace(element);

    // check pre state
    if (element.pre) {
      inVPre = false;
    }
    if (platformIsPreTag(element.tag)) {
      inPre = false;
    }
    // apply post-transforms
    for (var i = 0; i < postTransforms.length; i++) {
      postTransforms[i](element, options);
    }
  }

  function trimEndingWhitespace (el) {
    // remove trailing whitespace node
    if (!inPre) {
      var lastNode;
      while (
        (lastNode = el.children[el.children.length - 1]) &&
        lastNode.type === 3 &&
        lastNode.text === ' '
      ) {
        el.children.pop();
      }
    }
  }

  function checkRootConstraints (el) {
    if (el.tag === 'slot' || el.tag === 'template') {
      warnOnce(
        "Cannot use <" + (el.tag) + "> as component root element because it may " +
        'contain multiple nodes.',
        { start: el.start }
      );
    }
    if (el.attrsMap.hasOwnProperty('v-for')) {
      warnOnce(
        'Cannot use v-for on stateful component root element because ' +
        'it renders multiple elements.',
        el.rawAttrsMap['v-for']
      );
    }
  }

  parseHTML(template, {
    warn: warn$2,
    expectHTML: options.expectHTML,
    isUnaryTag: options.isUnaryTag,
    canBeLeftOpenTag: options.canBeLeftOpenTag,
    shouldDecodeNewlines: options.shouldDecodeNewlines,
    shouldDecodeNewlinesForHref: options.shouldDecodeNewlinesForHref,
    shouldKeepComment: options.comments,
    outputSourceRange: options.outputSourceRange,
    start: function start (tag, attrs, unary, start$1) {
      // check namespace.
      // inherit parent ns if there is one
      var ns = (currentParent && currentParent.ns) || platformGetTagNamespace(tag);

      // handle IE svg bug
      /* istanbul ignore if */
      if (isIE && ns === 'svg') {
        attrs = guardIESVGBug(attrs);
      }

      var element = createASTElement(tag, attrs, currentParent);
      if (ns) {
        element.ns = ns;
      }

      if (process.env.NODE_ENV !== 'production') {
        if (options.outputSourceRange) {
          element.start = start$1;
          element.rawAttrsMap = element.attrsList.reduce(function (cumulated, attr) {
            cumulated[attr.name] = attr;
            return cumulated
          }, {});
        }
        attrs.forEach(function (attr) {
          if (invalidAttributeRE.test(attr.name)) {
            warn$2(
              "Invalid dynamic argument expression: attribute names cannot contain " +
              "spaces, quotes, <, >, / or =.",
              {
                start: attr.start + attr.name.indexOf("["),
                end: attr.start + attr.name.length
              }
            );
          }
        });
      }

      if (isForbiddenTag(element) && !isServerRendering()) {
        element.forbidden = true;
        process.env.NODE_ENV !== 'production' && warn$2(
          'Templates should only be responsible for mapping the state to the ' +
          'UI. Avoid placing tags with side-effects in your templates, such as ' +
          "<" + tag + ">" + ', as they will not be parsed.',
          { start: element.start }
        );
      }

      // apply pre-transforms
      for (var i = 0; i < preTransforms.length; i++) {
        element = preTransforms[i](element, options) || element;
      }

      if (!inVPre) {
        processPre(element);
        if (element.pre) {
          inVPre = true;
        }
      }
      if (platformIsPreTag(element.tag)) {
        inPre = true;
      }
      if (inVPre) {
        processRawAttrs(element);
      } else if (!element.processed) {
        // structural directives
        processFor(element);
        processIf(element);
        processOnce(element);
      }

      if (!root) {
        root = element;
        if (process.env.NODE_ENV !== 'production') {
          checkRootConstraints(root);
        }
      }

      if (!unary) {
        currentParent = element;
        stack.push(element);
      } else {
        closeElement(element);
      }
    },

    end: function end (tag, start, end$1) {
      var element = stack[stack.length - 1];
      // pop stack
      stack.length -= 1;
      currentParent = stack[stack.length - 1];
      if (process.env.NODE_ENV !== 'production' && options.outputSourceRange) {
        element.end = end$1;
      }
      closeElement(element);
    },

    chars: function chars (text, start, end) {
      if (!currentParent) {
        if (process.env.NODE_ENV !== 'production') {
          if (text === template) {
            warnOnce(
              'Component template requires a root element, rather than just text.',
              { start: start }
            );
          } else if ((text = text.trim())) {
            warnOnce(
              ("text \"" + text + "\" outside root element will be ignored."),
              { start: start }
            );
          }
        }
        return
      }
      // IE textarea placeholder bug
      /* istanbul ignore if */
      if (isIE &&
        currentParent.tag === 'textarea' &&
        currentParent.attrsMap.placeholder === text
      ) {
        return
      }
      var children = currentParent.children;
      if (inPre || text.trim()) {
        text = isTextTag(currentParent) ? text : decodeHTMLCached(text);
      } else if (!children.length) {
        // remove the whitespace-only node right after an opening tag
        text = '';
      } else if (whitespaceOption) {
        if (whitespaceOption === 'condense') {
          // in condense mode, remove the whitespace node if it contains
          // line break, otherwise condense to a single space
          text = lineBreakRE.test(text) ? '' : ' ';
        } else {
          text = ' ';
        }
      } else {
        text = preserveWhitespace ? ' ' : '';
      }
      if (text) {
        if (whitespaceOption === 'condense') {
          // condense consecutive whitespaces into single space
          text = text.replace(whitespaceRE$1, ' ');
        }
        var res;
        var child;
        if (!inVPre && text !== ' ' && (res = parseText(text, delimiters))) {
          child = {
            type: 2,
            expression: res.expression,
            tokens: res.tokens,
            text: text
          };
        } else if (text !== ' ' || !children.length || children[children.length - 1].text !== ' ') {
          child = {
            type: 3,
            text: text
          };
        }
        if (child) {
          if (process.env.NODE_ENV !== 'production' && options.outputSourceRange) {
            child.start = start;
            child.end = end;
          }
          children.push(child);
        }
      }
    },
    comment: function comment (text, start, end) {
      // adding anyting as a sibling to the root node is forbidden
      // comments should still be allowed, but ignored
      if (currentParent) {
        var child = {
          type: 3,
          text: text,
          isComment: true
        };
        if (process.env.NODE_ENV !== 'production' && options.outputSourceRange) {
          child.start = start;
          child.end = end;
        }
        currentParent.children.push(child);
      }
    }
  });
  return root
}

function processPre (el) {
  if (getAndRemoveAttr(el, 'v-pre') != null) {
    el.pre = true;
  }
}

function processRawAttrs (el) {
  var list = el.attrsList;
  var len = list.length;
  if (len) {
    var attrs = el.attrs = new Array(len);
    for (var i = 0; i < len; i++) {
      attrs[i] = {
        name: list[i].name,
        value: JSON.stringify(list[i].value)
      };
      if (list[i].start != null) {
        attrs[i].start = list[i].start;
        attrs[i].end = list[i].end;
      }
    }
  } else if (!el.pre) {
    // non root node in pre blocks with no attributes
    el.plain = true;
  }
}

function processElement (
  element,
  options
) {
  processKey(element);

  // determine whether this is a plain element after
  // removing structural attributes
  element.plain = (
    !element.key &&
    !element.scopedSlots &&
    !element.attrsList.length
  );

  processRef(element);
  processSlotContent(element);
  processSlotOutlet(element);
  processComponent(element);
  for (var i = 0; i < transforms.length; i++) {
    element = transforms[i](element, options) || element;
  }
  processAttrs(element);
  return element
}

function processKey (el) {
  var exp = getBindingAttr(el, 'key');
  if (exp) {
    if (process.env.NODE_ENV !== 'production') {
      if (el.tag === 'template') {
        warn$2(
          "<template> cannot be keyed. Place the key on real elements instead.",
          getRawBindingAttr(el, 'key')
        );
      }
      if (el.for) {
        var iterator = el.iterator2 || el.iterator1;
        var parent = el.parent;
        if (iterator && iterator === exp && parent && parent.tag === 'transition-group') {
          warn$2(
            "Do not use v-for index as key on <transition-group> children, " +
            "this is the same as not using keys.",
            getRawBindingAttr(el, 'key'),
            true /* tip */
          );
        }
      }
    }
    el.key = exp;
  }
}

function processRef (el) {
  var ref = getBindingAttr(el, 'ref');
  if (ref) {
    el.ref = ref;
    el.refInFor = checkInFor(el);
  }
}

function processFor (el) {
  var exp;
  if ((exp = getAndRemoveAttr(el, 'v-for'))) {
    var res = parseFor(exp);
    if (res) {
      extend(el, res);
    } else if (process.env.NODE_ENV !== 'production') {
      warn$2(
        ("Invalid v-for expression: " + exp),
        el.rawAttrsMap['v-for']
      );
    }
  }
}



function parseFor (exp) {
  var inMatch = exp.match(forAliasRE);
  if (!inMatch) { return }
  var res = {};
  res.for = inMatch[2].trim();
  var alias = inMatch[1].trim().replace(stripParensRE, '');
  var iteratorMatch = alias.match(forIteratorRE);
  if (iteratorMatch) {
    res.alias = alias.replace(forIteratorRE, '').trim();
    res.iterator1 = iteratorMatch[1].trim();
    if (iteratorMatch[2]) {
      res.iterator2 = iteratorMatch[2].trim();
    }
  } else {
    res.alias = alias;
  }
  return res
}

function processIf (el) {
  var exp = getAndRemoveAttr(el, 'v-if');
  if (exp) {
    el.if = exp;
    addIfCondition(el, {
      exp: exp,
      block: el
    });
  } else {
    if (getAndRemoveAttr(el, 'v-else') != null) {
      el.else = true;
    }
    var elseif = getAndRemoveAttr(el, 'v-else-if');
    if (elseif) {
      el.elseif = elseif;
    }
  }
}

function processIfConditions (el, parent) {
  var prev = findPrevElement(parent.children);
  if (prev && prev.if) {
    addIfCondition(prev, {
      exp: el.elseif,
      block: el
    });
  } else if (process.env.NODE_ENV !== 'production') {
    warn$2(
      "v-" + (el.elseif ? ('else-if="' + el.elseif + '"') : 'else') + " " +
      "used on element <" + (el.tag) + "> without corresponding v-if.",
      el.rawAttrsMap[el.elseif ? 'v-else-if' : 'v-else']
    );
  }
}

function findPrevElement (children) {
  var i = children.length;
  while (i--) {
    if (children[i].type === 1) {
      return children[i]
    } else {
      if (process.env.NODE_ENV !== 'production' && children[i].text !== ' ') {
        warn$2(
          "text \"" + (children[i].text.trim()) + "\" between v-if and v-else(-if) " +
          "will be ignored.",
          children[i]
        );
      }
      children.pop();
    }
  }
}

function addIfCondition (el, condition) {
  if (!el.ifConditions) {
    el.ifConditions = [];
  }
  el.ifConditions.push(condition);
}

function processOnce (el) {
  var once$$1 = getAndRemoveAttr(el, 'v-once');
  if (once$$1 != null) {
    el.once = true;
  }
}

// handle content being passed to a component as slot,
// e.g. <template slot="xxx">, <div slot-scope="xxx">
function processSlotContent (el) {
  var slotScope;
  if (el.tag === 'template') {
    slotScope = getAndRemoveAttr(el, 'scope');
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && slotScope) {
      warn$2(
        "the \"scope\" attribute for scoped slots have been deprecated and " +
        "replaced by \"slot-scope\" since 2.5. The new \"slot-scope\" attribute " +
        "can also be used on plain elements in addition to <template> to " +
        "denote scoped slots.",
        el.rawAttrsMap['scope'],
        true
      );
    }
    el.slotScope = slotScope || getAndRemoveAttr(el, 'slot-scope');
  } else if ((slotScope = getAndRemoveAttr(el, 'slot-scope'))) {
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && el.attrsMap['v-for']) {
      warn$2(
        "Ambiguous combined usage of slot-scope and v-for on <" + (el.tag) + "> " +
        "(v-for takes higher priority). Use a wrapper <template> for the " +
        "scoped slot to make it clearer.",
        el.rawAttrsMap['slot-scope'],
        true
      );
    }
    el.slotScope = slotScope;
  }

  // slot="xxx"
  var slotTarget = getBindingAttr(el, 'slot');
  if (slotTarget) {
    el.slotTarget = slotTarget === '""' ? '"default"' : slotTarget;
    el.slotTargetDynamic = !!(el.attrsMap[':slot'] || el.attrsMap['v-bind:slot']);
    // preserve slot as an attribute for native shadow DOM compat
    // only for non-scoped slots.
    if (el.tag !== 'template' && !el.slotScope) {
      addAttr(el, 'slot', slotTarget, getRawBindingAttr(el, 'slot'));
    }
  }

  // 2.6 v-slot syntax
  {
    if (el.tag === 'template') {
      // v-slot on <template>
      var slotBinding = getAndRemoveAttrByRegex(el, slotRE);
      if (slotBinding) {
        if (process.env.NODE_ENV !== 'production') {
          if (el.slotTarget || el.slotScope) {
            warn$2(
              "Unexpected mixed usage of different slot syntaxes.",
              el
            );
          }
          if (el.parent && !maybeComponent(el.parent)) {
            warn$2(
              "<template v-slot> can only appear at the root level inside " +
              "the receiving the component",
              el
            );
          }
        }
        var ref = getSlotName(slotBinding);
        var name = ref.name;
        var dynamic = ref.dynamic;
        el.slotTarget = name;
        el.slotTargetDynamic = dynamic;
        el.slotScope = slotBinding.value || emptySlotScopeToken; // force it into a scoped slot for perf
      }
    } else {
      // v-slot on component, denotes default slot
      var slotBinding$1 = getAndRemoveAttrByRegex(el, slotRE);
      if (slotBinding$1) {
        if (process.env.NODE_ENV !== 'production') {
          if (!maybeComponent(el)) {
            warn$2(
              "v-slot can only be used on components or <template>.",
              slotBinding$1
            );
          }
          if (el.slotScope || el.slotTarget) {
            warn$2(
              "Unexpected mixed usage of different slot syntaxes.",
              el
            );
          }
          if (el.scopedSlots) {
            warn$2(
              "To avoid scope ambiguity, the default slot should also use " +
              "<template> syntax when there are other named slots.",
              slotBinding$1
            );
          }
        }
        // add the component's children to its default slot
        var slots = el.scopedSlots || (el.scopedSlots = {});
        var ref$1 = getSlotName(slotBinding$1);
        var name$1 = ref$1.name;
        var dynamic$1 = ref$1.dynamic;
        var slotContainer = slots[name$1] = createASTElement('template', [], el);
        slotContainer.slotTarget = name$1;
        slotContainer.slotTargetDynamic = dynamic$1;
        slotContainer.children = el.children.filter(function (c) {
          if (!c.slotScope) {
            c.parent = slotContainer;
            return true
          }
        });
        slotContainer.slotScope = slotBinding$1.value || emptySlotScopeToken;
        // remove children as they are returned from scopedSlots now
        el.children = [];
        // mark el non-plain so data gets generated
        el.plain = false;
      }
    }
  }
}

function getSlotName (binding) {
  var name = binding.name.replace(slotRE, '');
  if (!name) {
    if (binding.name[0] !== '#') {
      name = 'default';
    } else if (process.env.NODE_ENV !== 'production') {
      warn$2(
        "v-slot shorthand syntax requires a slot name.",
        binding
      );
    }
  }
  return dynamicArgRE.test(name)
    // dynamic [name]
    ? { name: name.slice(1, -1), dynamic: true }
    // static name
    : { name: ("\"" + name + "\""), dynamic: false }
}

// handle <slot/> outlets
function processSlotOutlet (el) {
  if (el.tag === 'slot') {
    el.slotName = getBindingAttr(el, 'name');
    if (process.env.NODE_ENV !== 'production' && el.key) {
      warn$2(
        "`key` does not work on <slot> because slots are abstract outlets " +
        "and can possibly expand into multiple elements. " +
        "Use the key on a wrapping element instead.",
        getRawBindingAttr(el, 'key')
      );
    }
  }
}

function processComponent (el) {
  var binding;
  if ((binding = getBindingAttr(el, 'is'))) {
    el.component = binding;
  }
  if (getAndRemoveAttr(el, 'inline-template') != null) {
    el.inlineTemplate = true;
  }
}

function processAttrs (el) {
  var list = el.attrsList;
  var i, l, name, rawName, value, modifiers, syncGen, isDynamic;
  for (i = 0, l = list.length; i < l; i++) {
    name = rawName = list[i].name;
    value = list[i].value;
    if (dirRE.test(name)) {
      // mark element as dynamic
      el.hasBindings = true;
      // modifiers
      modifiers = parseModifiers(name.replace(dirRE, ''));
      // support .foo shorthand syntax for the .prop modifier
      if (modifiers) {
        name = name.replace(modifierRE, '');
      }
      if (bindRE.test(name)) { // v-bind
        name = name.replace(bindRE, '');
        value = parseFilters(value);
        isDynamic = dynamicArgRE.test(name);
        if (isDynamic) {
          name = name.slice(1, -1);
        }
        if (
          process.env.NODE_ENV !== 'production' &&
          value.trim().length === 0
        ) {
          warn$2(
            ("The value for a v-bind expression cannot be empty. Found in \"v-bind:" + name + "\"")
          );
        }
        if (modifiers) {
          if (modifiers.prop && !isDynamic) {
            name = camelize(name);
            if (name === 'innerHtml') { name = 'innerHTML'; }
          }
          if (modifiers.camel && !isDynamic) {
            name = camelize(name);
          }
          if (modifiers.sync) {
            syncGen = genAssignmentCode(value, "$event");
            if (!isDynamic) {
              addHandler(
                el,
                ("update:" + (camelize(name))),
                syncGen,
                null,
                false,
                warn$2,
                list[i]
              );
              if (hyphenate(name) !== camelize(name)) {
                addHandler(
                  el,
                  ("update:" + (hyphenate(name))),
                  syncGen,
                  null,
                  false,
                  warn$2,
                  list[i]
                );
              }
            } else {
              // handler w/ dynamic event name
              addHandler(
                el,
                ("\"update:\"+(" + name + ")"),
                syncGen,
                null,
                false,
                warn$2,
                list[i],
                true // dynamic
              );
            }
          }
        }
        if ((modifiers && modifiers.prop) || (
          !el.component && platformMustUseProp(el.tag, el.attrsMap.type, name)
        )) {
          addProp(el, name, value, list[i], isDynamic);
        } else {
          addAttr(el, name, value, list[i], isDynamic);
        }
      } else if (onRE.test(name)) { // v-on
        name = name.replace(onRE, '');
        isDynamic = dynamicArgRE.test(name);
        if (isDynamic) {
          name = name.slice(1, -1);
        }
        addHandler(el, name, value, modifiers, false, warn$2, list[i], isDynamic);
      } else { // normal directives
        name = name.replace(dirRE, '');
        // parse arg
        var argMatch = name.match(argRE);
        var arg = argMatch && argMatch[1];
        isDynamic = false;
        if (arg) {
          name = name.slice(0, -(arg.length + 1));
          if (dynamicArgRE.test(arg)) {
            arg = arg.slice(1, -1);
            isDynamic = true;
          }
        }
        addDirective(el, name, rawName, value, arg, isDynamic, modifiers, list[i]);
        if (process.env.NODE_ENV !== 'production' && name === 'model') {
          checkForAliasModel(el, value);
        }
      }
    } else {
      // literal attribute
      if (process.env.NODE_ENV !== 'production') {
        var res = parseText(value, delimiters);
        if (res) {
          warn$2(
            name + "=\"" + value + "\": " +
            'Interpolation inside attributes has been removed. ' +
            'Use v-bind or the colon shorthand instead. For example, ' +
            'instead of <div id="{{ val }}">, use <div :id="val">.',
            list[i]
          );
        }
      }
      addAttr(el, name, JSON.stringify(value), list[i]);
      // #6887 firefox doesn't update muted state if set via attribute
      // even immediately after element creation
      if (!el.component &&
          name === 'muted' &&
          platformMustUseProp(el.tag, el.attrsMap.type, name)) {
        addProp(el, name, 'true', list[i]);
      }
    }
  }
}

function checkInFor (el) {
  var parent = el;
  while (parent) {
    if (parent.for !== undefined) {
      return true
    }
    parent = parent.parent;
  }
  return false
}

function parseModifiers (name) {
  var match = name.match(modifierRE);
  if (match) {
    var ret = {};
    match.forEach(function (m) { ret[m.slice(1)] = true; });
    return ret
  }
}

function makeAttrsMap (attrs) {
  var map = {};
  for (var i = 0, l = attrs.length; i < l; i++) {
    if (
      process.env.NODE_ENV !== 'production' &&
      map[attrs[i].name] && !isIE && !isEdge
    ) {
      warn$2('duplicate attribute: ' + attrs[i].name, attrs[i]);
    }
    map[attrs[i].name] = attrs[i].value;
  }
  return map
}

// for script (e.g. type="x/template") or style, do not decode content
function isTextTag (el) {
  return el.tag === 'script' || el.tag === 'style'
}

function isForbiddenTag (el) {
  return (
    el.tag === 'style' ||
    (el.tag === 'script' && (
      !el.attrsMap.type ||
      el.attrsMap.type === 'text/javascript'
    ))
  )
}

var ieNSBug = /^xmlns:NS\d+/;
var ieNSPrefix = /^NS\d+:/;

/* istanbul ignore next */
function guardIESVGBug (attrs) {
  var res = [];
  for (var i = 0; i < attrs.length; i++) {
    var attr = attrs[i];
    if (!ieNSBug.test(attr.name)) {
      attr.name = attr.name.replace(ieNSPrefix, '');
      res.push(attr);
    }
  }
  return res
}

function checkForAliasModel (el, value) {
  var _el = el;
  while (_el) {
    if (_el.for && _el.alias === value) {
      warn$2(
        "<" + (el.tag) + " v-model=\"" + value + "\">: " +
        "You are binding v-model directly to a v-for iteration alias. " +
        "This will not be able to modify the v-for source array because " +
        "writing to the alias is like modifying a function local variable. " +
        "Consider using an array of objects and use v-model on an object property instead.",
        el.rawAttrsMap['v-model']
      );
    }
    _el = _el.parent;
  }
}

/*  */

function preTransformNode (el, options) {
  if (el.tag === 'input') {
    var map = el.attrsMap;
    if (!map['v-model']) {
      return
    }

    var typeBinding;
    if (map[':type'] || map['v-bind:type']) {
      typeBinding = getBindingAttr(el, 'type');
    }
    if (!map.type && !typeBinding && map['v-bind']) {
      typeBinding = "(" + (map['v-bind']) + ").type";
    }

    if (typeBinding) {
      var ifCondition = getAndRemoveAttr(el, 'v-if', true);
      var ifConditionExtra = ifCondition ? ("&&(" + ifCondition + ")") : "";
      var hasElse = getAndRemoveAttr(el, 'v-else', true) != null;
      var elseIfCondition = getAndRemoveAttr(el, 'v-else-if', true);
      // 1. checkbox
      var branch0 = cloneASTElement(el);
      // process for on the main node
      processFor(branch0);
      addRawAttr(branch0, 'type', 'checkbox');
      processElement(branch0, options);
      branch0.processed = true; // prevent it from double-processed
      branch0.if = "(" + typeBinding + ")==='checkbox'" + ifConditionExtra;
      addIfCondition(branch0, {
        exp: branch0.if,
        block: branch0
      });
      // 2. add radio else-if condition
      var branch1 = cloneASTElement(el);
      getAndRemoveAttr(branch1, 'v-for', true);
      addRawAttr(branch1, 'type', 'radio');
      processElement(branch1, options);
      addIfCondition(branch0, {
        exp: "(" + typeBinding + ")==='radio'" + ifConditionExtra,
        block: branch1
      });
      // 3. other
      var branch2 = cloneASTElement(el);
      getAndRemoveAttr(branch2, 'v-for', true);
      addRawAttr(branch2, ':type', typeBinding);
      processElement(branch2, options);
      addIfCondition(branch0, {
        exp: ifCondition,
        block: branch2
      });

      if (hasElse) {
        branch0.else = true;
      } else if (elseIfCondition) {
        branch0.elseif = elseIfCondition;
      }

      return branch0
    }
  }
}

function cloneASTElement (el) {
  return createASTElement(el.tag, el.attrsList.slice(), el.parent)
}

var model$1 = {
  preTransformNode: preTransformNode
};

var modules$1 = [
  klass$1,
  style$1,
  model$1
];

/*  */

function text (el, dir) {
  if (dir.value) {
    addProp(el, 'textContent', ("_s(" + (dir.value) + ")"), dir);
  }
}

/*  */

function html (el, dir) {
  if (dir.value) {
    addProp(el, 'innerHTML', ("_s(" + (dir.value) + ")"), dir);
  }
}

var directives$1 = {
  model: model,
  text: text,
  html: html
};

/*  */

var baseOptions = {
  expectHTML: true,
  modules: modules$1,
  directives: directives$1,
  isPreTag: isPreTag,
  isUnaryTag: isUnaryTag,
  mustUseProp: mustUseProp,
  canBeLeftOpenTag: canBeLeftOpenTag,
  isReservedTag: isReservedTag,
  getTagNamespace: getTagNamespace,
  staticKeys: genStaticKeys(modules$1)
};

/*  */

var isStaticKey;
var isPlatformReservedTag;

var genStaticKeysCached = cached(genStaticKeys$1);

/**
 * Goal of the optimizer: walk the generated template AST tree
 * and detect sub-trees that are purely static, i.e. parts of
 * the DOM that never needs to change.
 *
 * Once we detect these sub-trees, we can:
 *
 * 1. Hoist them into constants, so that we no longer need to
 *    create fresh nodes for them on each re-render;
 * 2. Completely skip them in the patching process.
 */
function optimize (root, options) {
  if (!root) { return }
  isStaticKey = genStaticKeysCached(options.staticKeys || '');
  isPlatformReservedTag = options.isReservedTag || no;
  // first pass: mark all non-static nodes.
  markStatic$1(root);
  // second pass: mark static roots.
  markStaticRoots(root, false);
}

function genStaticKeys$1 (keys) {
  return makeMap(
    'type,tag,attrsList,attrsMap,plain,parent,children,attrs,start,end,rawAttrsMap' +
    (keys ? ',' + keys : '')
  )
}

function markStatic$1 (node) {
  node.static = isStatic(node);
  if (node.type === 1) {
    // do not make component slot content static. this avoids
    // 1. components not able to mutate slot nodes
    // 2. static slot content fails for hot-reloading
    if (
      !isPlatformReservedTag(node.tag) &&
      node.tag !== 'slot' &&
      node.attrsMap['inline-template'] == null
    ) {
      return
    }
    for (var i = 0, l = node.children.length; i < l; i++) {
      var child = node.children[i];
      markStatic$1(child);
      if (!child.static) {
        node.static = false;
      }
    }
    if (node.ifConditions) {
      for (var i$1 = 1, l$1 = node.ifConditions.length; i$1 < l$1; i$1++) {
        var block = node.ifConditions[i$1].block;
        markStatic$1(block);
        if (!block.static) {
          node.static = false;
        }
      }
    }
  }
}

function markStaticRoots (node, isInFor) {
  if (node.type === 1) {
    if (node.static || node.once) {
      node.staticInFor = isInFor;
    }
    // For a node to qualify as a static root, it should have children that
    // are not just static text. Otherwise the cost of hoisting out will
    // outweigh the benefits and it's better off to just always render it fresh.
    if (node.static && node.children.length && !(
      node.children.length === 1 &&
      node.children[0].type === 3
    )) {
      node.staticRoot = true;
      return
    } else {
      node.staticRoot = false;
    }
    if (node.children) {
      for (var i = 0, l = node.children.length; i < l; i++) {
        markStaticRoots(node.children[i], isInFor || !!node.for);
      }
    }
    if (node.ifConditions) {
      for (var i$1 = 1, l$1 = node.ifConditions.length; i$1 < l$1; i$1++) {
        markStaticRoots(node.ifConditions[i$1].block, isInFor);
      }
    }
  }
}

function isStatic (node) {
  if (node.type === 2) { // expression
    return false
  }
  if (node.type === 3) { // text
    return true
  }
  return !!(node.pre || (
    !node.hasBindings && // no dynamic bindings
    !node.if && !node.for && // not v-if or v-for or v-else
    !isBuiltInTag(node.tag) && // not a built-in
    isPlatformReservedTag(node.tag) && // not a component
    !isDirectChildOfTemplateFor(node) &&
    Object.keys(node).every(isStaticKey)
  ))
}

function isDirectChildOfTemplateFor (node) {
  while (node.parent) {
    node = node.parent;
    if (node.tag !== 'template') {
      return false
    }
    if (node.for) {
      return true
    }
  }
  return false
}

/*  */

var fnExpRE = /^([\w$_]+|\([^)]*?\))\s*=>|^function\s*\(/;
var fnInvokeRE = /\([^)]*?\);*$/;
var simplePathRE = /^[A-Za-z_$][\w$]*(?:\.[A-Za-z_$][\w$]*|\['[^']*?']|\["[^"]*?"]|\[\d+]|\[[A-Za-z_$][\w$]*])*$/;

// KeyboardEvent.keyCode aliases
var keyCodes = {
  esc: 27,
  tab: 9,
  enter: 13,
  space: 32,
  up: 38,
  left: 37,
  right: 39,
  down: 40,
  'delete': [8, 46]
};

// KeyboardEvent.key aliases
var keyNames = {
  // #7880: IE11 and Edge use `Esc` for Escape key name.
  esc: ['Esc', 'Escape'],
  tab: 'Tab',
  enter: 'Enter',
  // #9112: IE11 uses `Spacebar` for Space key name.
  space: [' ', 'Spacebar'],
  // #7806: IE11 uses key names without `Arrow` prefix for arrow keys.
  up: ['Up', 'ArrowUp'],
  left: ['Left', 'ArrowLeft'],
  right: ['Right', 'ArrowRight'],
  down: ['Down', 'ArrowDown'],
  // #9112: IE11 uses `Del` for Delete key name.
  'delete': ['Backspace', 'Delete', 'Del']
};

// #4868: modifiers that prevent the execution of the listener
// need to explicitly return null so that we can determine whether to remove
// the listener for .once
var genGuard = function (condition) { return ("if(" + condition + ")return null;"); };

var modifierCode = {
  stop: '$event.stopPropagation();',
  prevent: '$event.preventDefault();',
  self: genGuard("$event.target !== $event.currentTarget"),
  ctrl: genGuard("!$event.ctrlKey"),
  shift: genGuard("!$event.shiftKey"),
  alt: genGuard("!$event.altKey"),
  meta: genGuard("!$event.metaKey"),
  left: genGuard("'button' in $event && $event.button !== 0"),
  middle: genGuard("'button' in $event && $event.button !== 1"),
  right: genGuard("'button' in $event && $event.button !== 2")
};

function genHandlers (
  events,
  isNative
) {
  var prefix = isNative ? 'nativeOn:' : 'on:';
  var staticHandlers = "";
  var dynamicHandlers = "";
  for (var name in events) {
    var handlerCode = genHandler(events[name]);
    if (events[name] && events[name].dynamic) {
      dynamicHandlers += name + "," + handlerCode + ",";
    } else {
      staticHandlers += "\"" + name + "\":" + handlerCode + ",";
    }
  }
  staticHandlers = "{" + (staticHandlers.slice(0, -1)) + "}";
  if (dynamicHandlers) {
    return prefix + "_d(" + staticHandlers + ",[" + (dynamicHandlers.slice(0, -1)) + "])"
  } else {
    return prefix + staticHandlers
  }
}

function genHandler (handler) {
  if (!handler) {
    return 'function(){}'
  }

  if (Array.isArray(handler)) {
    return ("[" + (handler.map(function (handler) { return genHandler(handler); }).join(',')) + "]")
  }

  var isMethodPath = simplePathRE.test(handler.value);
  var isFunctionExpression = fnExpRE.test(handler.value);
  var isFunctionInvocation = simplePathRE.test(handler.value.replace(fnInvokeRE, ''));

  if (!handler.modifiers) {
    if (isMethodPath || isFunctionExpression) {
      return handler.value
    }
    return ("function($event){" + (isFunctionInvocation ? ("return " + (handler.value)) : handler.value) + "}") // inline statement
  } else {
    var code = '';
    var genModifierCode = '';
    var keys = [];
    for (var key in handler.modifiers) {
      if (modifierCode[key]) {
        genModifierCode += modifierCode[key];
        // left/right
        if (keyCodes[key]) {
          keys.push(key);
        }
      } else if (key === 'exact') {
        var modifiers = (handler.modifiers);
        genModifierCode += genGuard(
          ['ctrl', 'shift', 'alt', 'meta']
            .filter(function (keyModifier) { return !modifiers[keyModifier]; })
            .map(function (keyModifier) { return ("$event." + keyModifier + "Key"); })
            .join('||')
        );
      } else {
        keys.push(key);
      }
    }
    if (keys.length) {
      code += genKeyFilter(keys);
    }
    // Make sure modifiers like prevent and stop get executed after key filtering
    if (genModifierCode) {
      code += genModifierCode;
    }
    var handlerCode = isMethodPath
      ? ("return " + (handler.value) + "($event)")
      : isFunctionExpression
        ? ("return (" + (handler.value) + ")($event)")
        : isFunctionInvocation
          ? ("return " + (handler.value))
          : handler.value;
    return ("function($event){" + code + handlerCode + "}")
  }
}

function genKeyFilter (keys) {
  return (
    // make sure the key filters only apply to KeyboardEvents
    // #9441: can't use 'keyCode' in $event because Chrome autofill fires fake
    // key events that do not have keyCode property...
    "if(!$event.type.indexOf('key')&&" +
    (keys.map(genFilterCode).join('&&')) + ")return null;"
  )
}

function genFilterCode (key) {
  var keyVal = parseInt(key, 10);
  if (keyVal) {
    return ("$event.keyCode!==" + keyVal)
  }
  var keyCode = keyCodes[key];
  var keyName = keyNames[key];
  return (
    "_k($event.keyCode," +
    (JSON.stringify(key)) + "," +
    (JSON.stringify(keyCode)) + "," +
    "$event.key," +
    "" + (JSON.stringify(keyName)) +
    ")"
  )
}

/*  */

function on (el, dir) {
  if (process.env.NODE_ENV !== 'production' && dir.modifiers) {
    warn("v-on without argument does not support modifiers.");
  }
  el.wrapListeners = function (code) { return ("_g(" + code + "," + (dir.value) + ")"); };
}

/*  */

function bind$1 (el, dir) {
  el.wrapData = function (code) {
    return ("_b(" + code + ",'" + (el.tag) + "'," + (dir.value) + "," + (dir.modifiers && dir.modifiers.prop ? 'true' : 'false') + (dir.modifiers && dir.modifiers.sync ? ',true' : '') + ")")
  };
}

/*  */

var baseDirectives = {
  on: on,
  bind: bind$1,
  cloak: noop
};

/*  */





var CodegenState = function CodegenState (options) {
  this.options = options;
  this.warn = options.warn || baseWarn;
  this.transforms = pluckModuleFunction(options.modules, 'transformCode');
  this.dataGenFns = pluckModuleFunction(options.modules, 'genData');
  this.directives = extend(extend({}, baseDirectives), options.directives);
  var isReservedTag = options.isReservedTag || no;
  this.maybeComponent = function (el) { return !!el.component || !isReservedTag(el.tag); };
  this.onceId = 0;
  this.staticRenderFns = [];
  this.pre = false;
};



function generate (
  ast,
  options
) {
  var state = new CodegenState(options);
  var code = ast ? genElement(ast, state) : '_c("div")';
  return {
    render: ("with(this){return " + code + "}"),
    staticRenderFns: state.staticRenderFns
  }
}

function genElement (el, state) {
  if (el.parent) {
    el.pre = el.pre || el.parent.pre;
  }

  if (el.staticRoot && !el.staticProcessed) {
    return genStatic(el, state)
  } else if (el.once && !el.onceProcessed) {
    return genOnce(el, state)
  } else if (el.for && !el.forProcessed) {
    return genFor(el, state)
  } else if (el.if && !el.ifProcessed) {
    return genIf(el, state)
  } else if (el.tag === 'template' && !el.slotTarget && !state.pre) {
    return genChildren(el, state) || 'void 0'
  } else if (el.tag === 'slot') {
    return genSlot(el, state)
  } else {
    // component or element
    var code;
    if (el.component) {
      code = genComponent(el.component, el, state);
    } else {
      var data;
      if (!el.plain || (el.pre && state.maybeComponent(el))) {
        data = genData$2(el, state);
      }

      var children = el.inlineTemplate ? null : genChildren(el, state, true);
      code = "_c('" + (el.tag) + "'" + (data ? ("," + data) : '') + (children ? ("," + children) : '') + ")";
    }
    // module transforms
    for (var i = 0; i < state.transforms.length; i++) {
      code = state.transforms[i](el, code);
    }
    return code
  }
}

// hoist static sub-trees out
function genStatic (el, state) {
  el.staticProcessed = true;
  // Some elements (templates) need to behave differently inside of a v-pre
  // node.  All pre nodes are static roots, so we can use this as a location to
  // wrap a state change and reset it upon exiting the pre node.
  var originalPreState = state.pre;
  if (el.pre) {
    state.pre = el.pre;
  }
  state.staticRenderFns.push(("with(this){return " + (genElement(el, state)) + "}"));
  state.pre = originalPreState;
  return ("_m(" + (state.staticRenderFns.length - 1) + (el.staticInFor ? ',true' : '') + ")")
}

// v-once
function genOnce (el, state) {
  el.onceProcessed = true;
  if (el.if && !el.ifProcessed) {
    return genIf(el, state)
  } else if (el.staticInFor) {
    var key = '';
    var parent = el.parent;
    while (parent) {
      if (parent.for) {
        key = parent.key;
        break
      }
      parent = parent.parent;
    }
    if (!key) {
      process.env.NODE_ENV !== 'production' && state.warn(
        "v-once can only be used inside v-for that is keyed. ",
        el.rawAttrsMap['v-once']
      );
      return genElement(el, state)
    }
    return ("_o(" + (genElement(el, state)) + "," + (state.onceId++) + "," + key + ")")
  } else {
    return genStatic(el, state)
  }
}

function genIf (
  el,
  state,
  altGen,
  altEmpty
) {
  el.ifProcessed = true; // avoid recursion
  return genIfConditions(el.ifConditions.slice(), state, altGen, altEmpty)
}

function genIfConditions (
  conditions,
  state,
  altGen,
  altEmpty
) {
  if (!conditions.length) {
    return altEmpty || '_e()'
  }

  var condition = conditions.shift();
  if (condition.exp) {
    return ("(" + (condition.exp) + ")?" + (genTernaryExp(condition.block)) + ":" + (genIfConditions(conditions, state, altGen, altEmpty)))
  } else {
    return ("" + (genTernaryExp(condition.block)))
  }

  // v-if with v-once should generate code like (a)?_m(0):_m(1)
  function genTernaryExp (el) {
    return altGen
      ? altGen(el, state)
      : el.once
        ? genOnce(el, state)
        : genElement(el, state)
  }
}

function genFor (
  el,
  state,
  altGen,
  altHelper
) {
  var exp = el.for;
  var alias = el.alias;
  var iterator1 = el.iterator1 ? ("," + (el.iterator1)) : '';
  var iterator2 = el.iterator2 ? ("," + (el.iterator2)) : '';

  if (process.env.NODE_ENV !== 'production' &&
    state.maybeComponent(el) &&
    el.tag !== 'slot' &&
    el.tag !== 'template' &&
    !el.key
  ) {
    state.warn(
      "<" + (el.tag) + " v-for=\"" + alias + " in " + exp + "\">: component lists rendered with " +
      "v-for should have explicit keys. " +
      "See https://vuejs.org/guide/list.html#key for more info.",
      el.rawAttrsMap['v-for'],
      true /* tip */
    );
  }

  el.forProcessed = true; // avoid recursion
  return (altHelper || '_l') + "((" + exp + ")," +
    "function(" + alias + iterator1 + iterator2 + "){" +
      "return " + ((altGen || genElement)(el, state)) +
    '})'
}

function genData$2 (el, state) {
  var data = '{';

  // directives first.
  // directives may mutate the el's other properties before they are generated.
  var dirs = genDirectives(el, state);
  if (dirs) { data += dirs + ','; }

  // key
  if (el.key) {
    data += "key:" + (el.key) + ",";
  }
  // ref
  if (el.ref) {
    data += "ref:" + (el.ref) + ",";
  }
  if (el.refInFor) {
    data += "refInFor:true,";
  }
  // pre
  if (el.pre) {
    data += "pre:true,";
  }
  // record original tag name for components using "is" attribute
  if (el.component) {
    data += "tag:\"" + (el.tag) + "\",";
  }
  // module data generation functions
  for (var i = 0; i < state.dataGenFns.length; i++) {
    data += state.dataGenFns[i](el);
  }
  // attributes
  if (el.attrs) {
    data += "attrs:" + (genProps(el.attrs)) + ",";
  }
  // DOM props
  if (el.props) {
    data += "domProps:" + (genProps(el.props)) + ",";
  }
  // event handlers
  if (el.events) {
    data += (genHandlers(el.events, false)) + ",";
  }
  if (el.nativeEvents) {
    data += (genHandlers(el.nativeEvents, true)) + ",";
  }
  // slot target
  // only for non-scoped slots
  if (el.slotTarget && !el.slotScope) {
    data += "slot:" + (el.slotTarget) + ",";
  }
  // scoped slots
  if (el.scopedSlots) {
    data += (genScopedSlots(el, el.scopedSlots, state)) + ",";
  }
  // component v-model
  if (el.model) {
    data += "model:{value:" + (el.model.value) + ",callback:" + (el.model.callback) + ",expression:" + (el.model.expression) + "},";
  }
  // inline-template
  if (el.inlineTemplate) {
    var inlineTemplate = genInlineTemplate(el, state);
    if (inlineTemplate) {
      data += inlineTemplate + ",";
    }
  }
  data = data.replace(/,$/, '') + '}';
  // v-bind dynamic argument wrap
  // v-bind with dynamic arguments must be applied using the same v-bind object
  // merge helper so that class/style/mustUseProp attrs are handled correctly.
  if (el.dynamicAttrs) {
    data = "_b(" + data + ",\"" + (el.tag) + "\"," + (genProps(el.dynamicAttrs)) + ")";
  }
  // v-bind data wrap
  if (el.wrapData) {
    data = el.wrapData(data);
  }
  // v-on data wrap
  if (el.wrapListeners) {
    data = el.wrapListeners(data);
  }
  return data
}

function genDirectives (el, state) {
  var dirs = el.directives;
  if (!dirs) { return }
  var res = 'directives:[';
  var hasRuntime = false;
  var i, l, dir, needRuntime;
  for (i = 0, l = dirs.length; i < l; i++) {
    dir = dirs[i];
    needRuntime = true;
    var gen = state.directives[dir.name];
    if (gen) {
      // compile-time directive that manipulates AST.
      // returns true if it also needs a runtime counterpart.
      needRuntime = !!gen(el, dir, state.warn);
    }
    if (needRuntime) {
      hasRuntime = true;
      res += "{name:\"" + (dir.name) + "\",rawName:\"" + (dir.rawName) + "\"" + (dir.value ? (",value:(" + (dir.value) + "),expression:" + (JSON.stringify(dir.value))) : '') + (dir.arg ? (",arg:" + (dir.isDynamicArg ? dir.arg : ("\"" + (dir.arg) + "\""))) : '') + (dir.modifiers ? (",modifiers:" + (JSON.stringify(dir.modifiers))) : '') + "},";
    }
  }
  if (hasRuntime) {
    return res.slice(0, -1) + ']'
  }
}

function genInlineTemplate (el, state) {
  var ast = el.children[0];
  if (process.env.NODE_ENV !== 'production' && (
    el.children.length !== 1 || ast.type !== 1
  )) {
    state.warn(
      'Inline-template components must have exactly one child element.',
      { start: el.start }
    );
  }
  if (ast && ast.type === 1) {
    var inlineRenderFns = generate(ast, state.options);
    return ("inlineTemplate:{render:function(){" + (inlineRenderFns.render) + "},staticRenderFns:[" + (inlineRenderFns.staticRenderFns.map(function (code) { return ("function(){" + code + "}"); }).join(',')) + "]}")
  }
}

function genScopedSlots (
  el,
  slots,
  state
) {
  // by default scoped slots are considered "stable", this allows child
  // components with only scoped slots to skip forced updates from parent.
  // but in some cases we have to bail-out of this optimization
  // for example if the slot contains dynamic names, has v-if or v-for on them...
  var needsForceUpdate = Object.keys(slots).some(function (key) {
    var slot = slots[key];
    return (
      slot.slotTargetDynamic ||
      slot.if ||
      slot.for ||
      containsSlotChild(slot) // is passing down slot from parent which may be dynamic
    )
  });
  // OR when it is inside another scoped slot (the reactivity is disconnected)
  // #9438
  if (!needsForceUpdate) {
    var parent = el.parent;
    while (parent) {
      if (parent.slotScope && parent.slotScope !== emptySlotScopeToken) {
        needsForceUpdate = true;
        break
      }
      parent = parent.parent;
    }
  }

  return ("scopedSlots:_u([" + (Object.keys(slots).map(function (key) {
      return genScopedSlot(slots[key], state)
    }).join(',')) + "]" + (needsForceUpdate ? ",true" : "") + ")")
}

function containsSlotChild (el) {
  if (el.type === 1) {
    if (el.tag === 'slot') {
      return true
    }
    return el.children.some(containsSlotChild)
  }
  return false
}

function genScopedSlot (
  el,
  state
) {
  var isLegacySyntax = el.attrsMap['slot-scope'];
  if (el.if && !el.ifProcessed && !isLegacySyntax) {
    return genIf(el, state, genScopedSlot, "null")
  }
  if (el.for && !el.forProcessed) {
    return genFor(el, state, genScopedSlot)
  }
  var slotScope = el.slotScope === emptySlotScopeToken
    ? ""
    : String(el.slotScope);
  var fn = "function(" + slotScope + "){" +
    "return " + (el.tag === 'template'
      ? el.if && isLegacySyntax
        ? ("(" + (el.if) + ")?" + (genChildren(el, state) || 'undefined') + ":undefined")
        : genChildren(el, state) || 'undefined'
      : genElement(el, state)) + "}";
  // reverse proxy v-slot without scope on this.$slots
  var reverseProxy = slotScope ? "" : ",proxy:true";
  return ("{key:" + (el.slotTarget || "\"default\"") + ",fn:" + fn + reverseProxy + "}")
}

function genChildren (
  el,
  state,
  checkSkip,
  altGenElement,
  altGenNode
) {
  var children = el.children;
  if (children.length) {
    var el$1 = children[0];
    // optimize single v-for
    if (children.length === 1 &&
      el$1.for &&
      el$1.tag !== 'template' &&
      el$1.tag !== 'slot'
    ) {
      var normalizationType = checkSkip
        ? state.maybeComponent(el$1) ? ",1" : ",0"
        : "";
      return ("" + ((altGenElement || genElement)(el$1, state)) + normalizationType)
    }
    var normalizationType$1 = checkSkip
      ? getNormalizationType(children, state.maybeComponent)
      : 0;
    var gen = altGenNode || genNode;
    return ("[" + (children.map(function (c) { return gen(c, state); }).join(',')) + "]" + (normalizationType$1 ? ("," + normalizationType$1) : ''))
  }
}

// determine the normalization needed for the children array.
// 0: no normalization needed
// 1: simple normalization needed (possible 1-level deep nested array)
// 2: full normalization needed
function getNormalizationType (
  children,
  maybeComponent
) {
  var res = 0;
  for (var i = 0; i < children.length; i++) {
    var el = children[i];
    if (el.type !== 1) {
      continue
    }
    if (needsNormalization(el) ||
        (el.ifConditions && el.ifConditions.some(function (c) { return needsNormalization(c.block); }))) {
      res = 2;
      break
    }
    if (maybeComponent(el) ||
        (el.ifConditions && el.ifConditions.some(function (c) { return maybeComponent(c.block); }))) {
      res = 1;
    }
  }
  return res
}

function needsNormalization (el) {
  return el.for !== undefined || el.tag === 'template' || el.tag === 'slot'
}

function genNode (node, state) {
  if (node.type === 1) {
    return genElement(node, state)
  } else if (node.type === 3 && node.isComment) {
    return genComment(node)
  } else {
    return genText(node)
  }
}

function genText (text) {
  return ("_v(" + (text.type === 2
    ? text.expression // no need for () because already wrapped in _s()
    : transformSpecialNewlines(JSON.stringify(text.text))) + ")")
}

function genComment (comment) {
  return ("_e(" + (JSON.stringify(comment.text)) + ")")
}

function genSlot (el, state) {
  var slotName = el.slotName || '"default"';
  var children = genChildren(el, state);
  var res = "_t(" + slotName + (children ? ("," + children) : '');
  var attrs = el.attrs || el.dynamicAttrs
    ? genProps((el.attrs || []).concat(el.dynamicAttrs || []).map(function (attr) { return ({
        // slot props are camelized
        name: camelize(attr.name),
        value: attr.value,
        dynamic: attr.dynamic
      }); }))
    : null;
  var bind$$1 = el.attrsMap['v-bind'];
  if ((attrs || bind$$1) && !children) {
    res += ",null";
  }
  if (attrs) {
    res += "," + attrs;
  }
  if (bind$$1) {
    res += (attrs ? '' : ',null') + "," + bind$$1;
  }
  return res + ')'
}

// componentName is el.component, take it as argument to shun flow's pessimistic refinement
function genComponent (
  componentName,
  el,
  state
) {
  var children = el.inlineTemplate ? null : genChildren(el, state, true);
  return ("_c(" + componentName + "," + (genData$2(el, state)) + (children ? ("," + children) : '') + ")")
}

function genProps (props) {
  var staticProps = "";
  var dynamicProps = "";
  for (var i = 0; i < props.length; i++) {
    var prop = props[i];
    var value = transformSpecialNewlines(prop.value);
    if (prop.dynamic) {
      dynamicProps += (prop.name) + "," + value + ",";
    } else {
      staticProps += "\"" + (prop.name) + "\":" + value + ",";
    }
  }
  staticProps = "{" + (staticProps.slice(0, -1)) + "}";
  if (dynamicProps) {
    return ("_d(" + staticProps + ",[" + (dynamicProps.slice(0, -1)) + "])")
  } else {
    return staticProps
  }
}

// #3895, #4268
function transformSpecialNewlines (text) {
  return text
    .replace(/\u2028/g, '\\u2028')
    .replace(/\u2029/g, '\\u2029')
}

/*  */



// these keywords should not appear inside expressions, but operators like
// typeof, instanceof and in are allowed
var prohibitedKeywordRE = new RegExp('\\b' + (
  'do,if,for,let,new,try,var,case,else,with,await,break,catch,class,const,' +
  'super,throw,while,yield,delete,export,import,return,switch,default,' +
  'extends,finally,continue,debugger,function,arguments'
).split(',').join('\\b|\\b') + '\\b');

// these unary operators should not be used as property/method names
var unaryOperatorsRE = new RegExp('\\b' + (
  'delete,typeof,void'
).split(',').join('\\s*\\([^\\)]*\\)|\\b') + '\\s*\\([^\\)]*\\)');

// strip strings in expressions
var stripStringRE = /'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*"|`(?:[^`\\]|\\.)*\$\{|\}(?:[^`\\]|\\.)*`|`(?:[^`\\]|\\.)*`/g;

// detect problematic expressions in a template
function detectErrors (ast, warn) {
  if (ast) {
    checkNode(ast, warn);
  }
}

function checkNode (node, warn) {
  if (node.type === 1) {
    for (var name in node.attrsMap) {
      if (dirRE.test(name)) {
        var value = node.attrsMap[name];
        if (value) {
          var range = node.rawAttrsMap[name];
          if (name === 'v-for') {
            checkFor(node, ("v-for=\"" + value + "\""), warn, range);
          } else if (onRE.test(name)) {
            checkEvent(value, (name + "=\"" + value + "\""), warn, range);
          } else {
            checkExpression(value, (name + "=\"" + value + "\""), warn, range);
          }
        }
      }
    }
    if (node.children) {
      for (var i = 0; i < node.children.length; i++) {
        checkNode(node.children[i], warn);
      }
    }
  } else if (node.type === 2) {
    checkExpression(node.expression, node.text, warn, node);
  }
}

function checkEvent (exp, text, warn, range) {
  var stipped = exp.replace(stripStringRE, '');
  var keywordMatch = stipped.match(unaryOperatorsRE);
  if (keywordMatch && stipped.charAt(keywordMatch.index - 1) !== '$') {
    warn(
      "avoid using JavaScript unary operator as property name: " +
      "\"" + (keywordMatch[0]) + "\" in expression " + (text.trim()),
      range
    );
  }
  checkExpression(exp, text, warn, range);
}

function checkFor (node, text, warn, range) {
  checkExpression(node.for || '', text, warn, range);
  checkIdentifier(node.alias, 'v-for alias', text, warn, range);
  checkIdentifier(node.iterator1, 'v-for iterator', text, warn, range);
  checkIdentifier(node.iterator2, 'v-for iterator', text, warn, range);
}

function checkIdentifier (
  ident,
  type,
  text,
  warn,
  range
) {
  if (typeof ident === 'string') {
    try {
      new Function(("var " + ident + "=_"));
    } catch (e) {
      warn(("invalid " + type + " \"" + ident + "\" in expression: " + (text.trim())), range);
    }
  }
}

function checkExpression (exp, text, warn, range) {
  try {
    new Function(("return " + exp));
  } catch (e) {
    var keywordMatch = exp.replace(stripStringRE, '').match(prohibitedKeywordRE);
    if (keywordMatch) {
      warn(
        "avoid using JavaScript keyword as property name: " +
        "\"" + (keywordMatch[0]) + "\"\n  Raw expression: " + (text.trim()),
        range
      );
    } else {
      warn(
        "invalid expression: " + (e.message) + " in\n\n" +
        "    " + exp + "\n\n" +
        "  Raw expression: " + (text.trim()) + "\n",
        range
      );
    }
  }
}

/*  */

var range = 2;

function generateCodeFrame (
  source,
  start,
  end
) {
  if ( start === void 0 ) start = 0;
  if ( end === void 0 ) end = source.length;

  var lines = source.split(/\r?\n/);
  var count = 0;
  var res = [];
  for (var i = 0; i < lines.length; i++) {
    count += lines[i].length + 1;
    if (count >= start) {
      for (var j = i - range; j <= i + range || end > count; j++) {
        if (j < 0 || j >= lines.length) { continue }
        res.push(("" + (j + 1) + (repeat$1(" ", 3 - String(j + 1).length)) + "|  " + (lines[j])));
        var lineLength = lines[j].length;
        if (j === i) {
          // push underline
          var pad = start - (count - lineLength) + 1;
          var length = end > count ? lineLength - pad : end - start;
          res.push("   |  " + repeat$1(" ", pad) + repeat$1("^", length));
        } else if (j > i) {
          if (end > count) {
            var length$1 = Math.min(end - count, lineLength);
            res.push("   |  " + repeat$1("^", length$1));
          }
          count += lineLength + 1;
        }
      }
      break
    }
  }
  return res.join('\n')
}

function repeat$1 (str, n) {
  var result = '';
  while (true) { // eslint-disable-line
    if (n & 1) { result += str; }
    n >>>= 1;
    if (n <= 0) { break }
    str += str;
  }
  return result
}

/*  */



function createFunction (code, errors) {
  try {
    return new Function(code)
  } catch (err) {
    errors.push({ err: err, code: code });
    return noop
  }
}

function createCompileToFunctionFn (compile) {
  var cache = Object.create(null);

  return function compileToFunctions (
    template,
    options,
    vm
  ) {
    options = extend({}, options);
    var warn$$1 = options.warn || warn;
    delete options.warn;

    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      // detect possible CSP restriction
      try {
        new Function('return 1');
      } catch (e) {
        if (e.toString().match(/unsafe-eval|CSP/)) {
          warn$$1(
            'It seems you are using the standalone build of Vue.js in an ' +
            'environment with Content Security Policy that prohibits unsafe-eval. ' +
            'The template compiler cannot work in this environment. Consider ' +
            'relaxing the policy to allow unsafe-eval or pre-compiling your ' +
            'templates into render functions.'
          );
        }
      }
    }

    // check cache
    var key = options.delimiters
      ? String(options.delimiters) + template
      : template;
    if (cache[key]) {
      return cache[key]
    }

    // compile
    var compiled = compile(template, options);

    // check compilation errors/tips
    if (process.env.NODE_ENV !== 'production') {
      if (compiled.errors && compiled.errors.length) {
        if (options.outputSourceRange) {
          compiled.errors.forEach(function (e) {
            warn$$1(
              "Error compiling template:\n\n" + (e.msg) + "\n\n" +
              generateCodeFrame(template, e.start, e.end),
              vm
            );
          });
        } else {
          warn$$1(
            "Error compiling template:\n\n" + template + "\n\n" +
            compiled.errors.map(function (e) { return ("- " + e); }).join('\n') + '\n',
            vm
          );
        }
      }
      if (compiled.tips && compiled.tips.length) {
        if (options.outputSourceRange) {
          compiled.tips.forEach(function (e) { return tip(e.msg, vm); });
        } else {
          compiled.tips.forEach(function (msg) { return tip(msg, vm); });
        }
      }
    }

    // turn code into functions
    var res = {};
    var fnGenErrors = [];
    res.render = createFunction(compiled.render, fnGenErrors);
    res.staticRenderFns = compiled.staticRenderFns.map(function (code) {
      return createFunction(code, fnGenErrors)
    });

    // check function generation errors.
    // this should only happen if there is a bug in the compiler itself.
    // mostly for codegen development use
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      if ((!compiled.errors || !compiled.errors.length) && fnGenErrors.length) {
        warn$$1(
          "Failed to generate render function:\n\n" +
          fnGenErrors.map(function (ref) {
            var err = ref.err;
            var code = ref.code;

            return ((err.toString()) + " in\n\n" + code + "\n");
        }).join('\n'),
          vm
        );
      }
    }

    return (cache[key] = res)
  }
}

/*  */

function createCompilerCreator (baseCompile) {
  return function createCompiler (baseOptions) {
    function compile (
      template,
      options
    ) {
      var finalOptions = Object.create(baseOptions);
      var errors = [];
      var tips = [];

      var warn = function (msg, range, tip) {
        (tip ? tips : errors).push(msg);
      };

      if (options) {
        if (process.env.NODE_ENV !== 'production' && options.outputSourceRange) {
          // $flow-disable-line
          var leadingSpaceLength = template.match(/^\s*/)[0].length;

          warn = function (msg, range, tip) {
            var data = { msg: msg };
            if (range) {
              if (range.start != null) {
                data.start = range.start + leadingSpaceLength;
              }
              if (range.end != null) {
                data.end = range.end + leadingSpaceLength;
              }
            }
            (tip ? tips : errors).push(data);
          };
        }
        // merge custom modules
        if (options.modules) {
          finalOptions.modules =
            (baseOptions.modules || []).concat(options.modules);
        }
        // merge custom directives
        if (options.directives) {
          finalOptions.directives = extend(
            Object.create(baseOptions.directives || null),
            options.directives
          );
        }
        // copy other options
        for (var key in options) {
          if (key !== 'modules' && key !== 'directives') {
            finalOptions[key] = options[key];
          }
        }
      }

      finalOptions.warn = warn;

      var compiled = baseCompile(template.trim(), finalOptions);
      if (process.env.NODE_ENV !== 'production') {
        detectErrors(compiled.ast, warn);
      }
      compiled.errors = errors;
      compiled.tips = tips;
      return compiled
    }

    return {
      compile: compile,
      compileToFunctions: createCompileToFunctionFn(compile)
    }
  }
}

/*  */

// `createCompilerCreator` allows creating compilers that use alternative
// parser/optimizer/codegen, e.g the SSR optimizing compiler.
// Here we just export a default compiler using the default parts.
var createCompiler = createCompilerCreator(function baseCompile (
  template,
  options
) {
  var ast = parse(template.trim(), options);
  if (options.optimize !== false) {
    optimize(ast, options);
  }
  var code = generate(ast, options);
  return {
    ast: ast,
    render: code.render,
    staticRenderFns: code.staticRenderFns
  }
});

/*  */

var ref$1 = createCompiler(baseOptions);
var compile = ref$1.compile;
var compileToFunctions = ref$1.compileToFunctions;

/*  */

// check whether current browser encodes a char inside attribute values
var div;
function getShouldDecode (href) {
  div = div || document.createElement('div');
  div.innerHTML = href ? "<a href=\"\n\"/>" : "<div a=\"\n\"/>";
  return div.innerHTML.indexOf('&#10;') > 0
}

// #3663: IE encodes newlines inside attribute values while other browsers don't
var shouldDecodeNewlines = inBrowser ? getShouldDecode(false) : false;
// #6828: chrome encodes content in a[href]
var shouldDecodeNewlinesForHref = inBrowser ? getShouldDecode(true) : false;

/*  */

var idToTemplate = cached(function (id) {
  var el = query(id);
  return el && el.innerHTML
});

var mount = Vue.prototype.$mount;
Vue.prototype.$mount = function (
  el,
  hydrating
) {
  el = el && query(el);

  /* istanbul ignore if */
  if (el === document.body || el === document.documentElement) {
    process.env.NODE_ENV !== 'production' && warn(
      "Do not mount Vue to <html> or <body> - mount to normal elements instead."
    );
    return this
  }

  var options = this.$options;
  // resolve template/el and convert to render function
  if (!options.render) {
    var template = options.template;
    if (template) {
      if (typeof template === 'string') {
        if (template.charAt(0) === '#') {
          template = idToTemplate(template);
          /* istanbul ignore if */
          if (process.env.NODE_ENV !== 'production' && !template) {
            warn(
              ("Template element not found or is empty: " + (options.template)),
              this
            );
          }
        }
      } else if (template.nodeType) {
        template = template.innerHTML;
      } else {
        if (process.env.NODE_ENV !== 'production') {
          warn('invalid template option:' + template, this);
        }
        return this
      }
    } else if (el) {
      template = getOuterHTML(el);
    }
    if (template) {
      /* istanbul ignore if */
      if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
        mark('compile');
      }

      var ref = compileToFunctions(template, {
        outputSourceRange: process.env.NODE_ENV !== 'production',
        shouldDecodeNewlines: shouldDecodeNewlines,
        shouldDecodeNewlinesForHref: shouldDecodeNewlinesForHref,
        delimiters: options.delimiters,
        comments: options.comments
      }, this);
      var render = ref.render;
      var staticRenderFns = ref.staticRenderFns;
      options.render = render;
      options.staticRenderFns = staticRenderFns;

      /* istanbul ignore if */
      if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
        mark('compile end');
        measure(("vue " + (this._name) + " compile"), 'compile', 'compile end');
      }
    }
  }
  return mount.call(this, el, hydrating)
};

/**
 * Get outerHTML of elements, taking care
 * of SVG elements in IE as well.
 */
function getOuterHTML (el) {
  if (el.outerHTML) {
    return el.outerHTML
  } else {
    var container = document.createElement('div');
    container.appendChild(el.cloneNode(true));
    return container.innerHTML
  }
}

Vue.compile = compileToFunctions;

/* harmony default export */ __webpack_exports__["default"] = (Vue);

/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(4), __webpack_require__(2), __webpack_require__(8).setImmediate))

/***/ }),
/* 4 */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(28)
__vue_template__ = __webpack_require__(29)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\api-key-form.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 6 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Url", function() { return Url; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Http", function() { return Http; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Resource", function() { return Resource; });
/*!
 * vue-resource v1.5.1
 * https://github.com/pagekit/vue-resource
 * Released under the MIT License.
 */

/**
 * Promises/A+ polyfill v1.1.4 (https://github.com/bramstein/promis)
 */

var RESOLVED = 0;
var REJECTED = 1;
var PENDING = 2;

function Promise$1(executor) {

    this.state = PENDING;
    this.value = undefined;
    this.deferred = [];

    var promise = this;

    try {
        executor(function (x) {
            promise.resolve(x);
        }, function (r) {
            promise.reject(r);
        });
    } catch (e) {
        promise.reject(e);
    }
}

Promise$1.reject = function (r) {
    return new Promise$1(function (resolve, reject) {
        reject(r);
    });
};

Promise$1.resolve = function (x) {
    return new Promise$1(function (resolve, reject) {
        resolve(x);
    });
};

Promise$1.all = function all(iterable) {
    return new Promise$1(function (resolve, reject) {
        var count = 0, result = [];

        if (iterable.length === 0) {
            resolve(result);
        }

        function resolver(i) {
            return function (x) {
                result[i] = x;
                count += 1;

                if (count === iterable.length) {
                    resolve(result);
                }
            };
        }

        for (var i = 0; i < iterable.length; i += 1) {
            Promise$1.resolve(iterable[i]).then(resolver(i), reject);
        }
    });
};

Promise$1.race = function race(iterable) {
    return new Promise$1(function (resolve, reject) {
        for (var i = 0; i < iterable.length; i += 1) {
            Promise$1.resolve(iterable[i]).then(resolve, reject);
        }
    });
};

var p = Promise$1.prototype;

p.resolve = function resolve(x) {
    var promise = this;

    if (promise.state === PENDING) {
        if (x === promise) {
            throw new TypeError('Promise settled with itself.');
        }

        var called = false;

        try {
            var then = x && x['then'];

            if (x !== null && typeof x === 'object' && typeof then === 'function') {
                then.call(x, function (x) {
                    if (!called) {
                        promise.resolve(x);
                    }
                    called = true;

                }, function (r) {
                    if (!called) {
                        promise.reject(r);
                    }
                    called = true;
                });
                return;
            }
        } catch (e) {
            if (!called) {
                promise.reject(e);
            }
            return;
        }

        promise.state = RESOLVED;
        promise.value = x;
        promise.notify();
    }
};

p.reject = function reject(reason) {
    var promise = this;

    if (promise.state === PENDING) {
        if (reason === promise) {
            throw new TypeError('Promise settled with itself.');
        }

        promise.state = REJECTED;
        promise.value = reason;
        promise.notify();
    }
};

p.notify = function notify() {
    var promise = this;

    nextTick(function () {
        if (promise.state !== PENDING) {
            while (promise.deferred.length) {
                var deferred = promise.deferred.shift(),
                    onResolved = deferred[0],
                    onRejected = deferred[1],
                    resolve = deferred[2],
                    reject = deferred[3];

                try {
                    if (promise.state === RESOLVED) {
                        if (typeof onResolved === 'function') {
                            resolve(onResolved.call(undefined, promise.value));
                        } else {
                            resolve(promise.value);
                        }
                    } else if (promise.state === REJECTED) {
                        if (typeof onRejected === 'function') {
                            resolve(onRejected.call(undefined, promise.value));
                        } else {
                            reject(promise.value);
                        }
                    }
                } catch (e) {
                    reject(e);
                }
            }
        }
    });
};

p.then = function then(onResolved, onRejected) {
    var promise = this;

    return new Promise$1(function (resolve, reject) {
        promise.deferred.push([onResolved, onRejected, resolve, reject]);
        promise.notify();
    });
};

p.catch = function (onRejected) {
    return this.then(undefined, onRejected);
};

/**
 * Promise adapter.
 */

if (typeof Promise === 'undefined') {
    window.Promise = Promise$1;
}

function PromiseObj(executor, context) {

    if (executor instanceof Promise) {
        this.promise = executor;
    } else {
        this.promise = new Promise(executor.bind(context));
    }

    this.context = context;
}

PromiseObj.all = function (iterable, context) {
    return new PromiseObj(Promise.all(iterable), context);
};

PromiseObj.resolve = function (value, context) {
    return new PromiseObj(Promise.resolve(value), context);
};

PromiseObj.reject = function (reason, context) {
    return new PromiseObj(Promise.reject(reason), context);
};

PromiseObj.race = function (iterable, context) {
    return new PromiseObj(Promise.race(iterable), context);
};

var p$1 = PromiseObj.prototype;

p$1.bind = function (context) {
    this.context = context;
    return this;
};

p$1.then = function (fulfilled, rejected) {

    if (fulfilled && fulfilled.bind && this.context) {
        fulfilled = fulfilled.bind(this.context);
    }

    if (rejected && rejected.bind && this.context) {
        rejected = rejected.bind(this.context);
    }

    return new PromiseObj(this.promise.then(fulfilled, rejected), this.context);
};

p$1.catch = function (rejected) {

    if (rejected && rejected.bind && this.context) {
        rejected = rejected.bind(this.context);
    }

    return new PromiseObj(this.promise.catch(rejected), this.context);
};

p$1.finally = function (callback) {

    return this.then(function (value) {
        callback.call(this);
        return value;
    }, function (reason) {
        callback.call(this);
        return Promise.reject(reason);
    }
    );
};

/**
 * Utility functions.
 */

var ref = {};
var hasOwnProperty = ref.hasOwnProperty;
var ref$1 = [];
var slice = ref$1.slice;
var debug = false, ntick;

var inBrowser = typeof window !== 'undefined';

function Util (ref) {
    var config = ref.config;
    var nextTick = ref.nextTick;

    ntick = nextTick;
    debug = config.debug || !config.silent;
}

function warn(msg) {
    if (typeof console !== 'undefined' && debug) {
        console.warn('[VueResource warn]: ' + msg);
    }
}

function error(msg) {
    if (typeof console !== 'undefined') {
        console.error(msg);
    }
}

function nextTick(cb, ctx) {
    return ntick(cb, ctx);
}

function trim(str) {
    return str ? str.replace(/^\s*|\s*$/g, '') : '';
}

function trimEnd(str, chars) {

    if (str && chars === undefined) {
        return str.replace(/\s+$/, '');
    }

    if (!str || !chars) {
        return str;
    }

    return str.replace(new RegExp(("[" + chars + "]+$")), '');
}

function toLower(str) {
    return str ? str.toLowerCase() : '';
}

function toUpper(str) {
    return str ? str.toUpperCase() : '';
}

var isArray = Array.isArray;

function isString(val) {
    return typeof val === 'string';
}

function isFunction(val) {
    return typeof val === 'function';
}

function isObject(obj) {
    return obj !== null && typeof obj === 'object';
}

function isPlainObject(obj) {
    return isObject(obj) && Object.getPrototypeOf(obj) == Object.prototype;
}

function isBlob(obj) {
    return typeof Blob !== 'undefined' && obj instanceof Blob;
}

function isFormData(obj) {
    return typeof FormData !== 'undefined' && obj instanceof FormData;
}

function when(value, fulfilled, rejected) {

    var promise = PromiseObj.resolve(value);

    if (arguments.length < 2) {
        return promise;
    }

    return promise.then(fulfilled, rejected);
}

function options(fn, obj, opts) {

    opts = opts || {};

    if (isFunction(opts)) {
        opts = opts.call(obj);
    }

    return merge(fn.bind({$vm: obj, $options: opts}), fn, {$options: opts});
}

function each(obj, iterator) {

    var i, key;

    if (isArray(obj)) {
        for (i = 0; i < obj.length; i++) {
            iterator.call(obj[i], obj[i], i);
        }
    } else if (isObject(obj)) {
        for (key in obj) {
            if (hasOwnProperty.call(obj, key)) {
                iterator.call(obj[key], obj[key], key);
            }
        }
    }

    return obj;
}

var assign = Object.assign || _assign;

function merge(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {
        _merge(target, source, true);
    });

    return target;
}

function defaults(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {

        for (var key in source) {
            if (target[key] === undefined) {
                target[key] = source[key];
            }
        }

    });

    return target;
}

function _assign(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {
        _merge(target, source);
    });

    return target;
}

function _merge(target, source, deep) {
    for (var key in source) {
        if (deep && (isPlainObject(source[key]) || isArray(source[key]))) {
            if (isPlainObject(source[key]) && !isPlainObject(target[key])) {
                target[key] = {};
            }
            if (isArray(source[key]) && !isArray(target[key])) {
                target[key] = [];
            }
            _merge(target[key], source[key], deep);
        } else if (source[key] !== undefined) {
            target[key] = source[key];
        }
    }
}

/**
 * Root Prefix Transform.
 */

function root (options$$1, next) {

    var url = next(options$$1);

    if (isString(options$$1.root) && !/^(https?:)?\//.test(url)) {
        url = trimEnd(options$$1.root, '/') + '/' + url;
    }

    return url;
}

/**
 * Query Parameter Transform.
 */

function query (options$$1, next) {

    var urlParams = Object.keys(Url.options.params), query = {}, url = next(options$$1);

    each(options$$1.params, function (value, key) {
        if (urlParams.indexOf(key) === -1) {
            query[key] = value;
        }
    });

    query = Url.params(query);

    if (query) {
        url += (url.indexOf('?') == -1 ? '?' : '&') + query;
    }

    return url;
}

/**
 * URL Template v2.0.6 (https://github.com/bramstein/url-template)
 */

function expand(url, params, variables) {

    var tmpl = parse(url), expanded = tmpl.expand(params);

    if (variables) {
        variables.push.apply(variables, tmpl.vars);
    }

    return expanded;
}

function parse(template) {

    var operators = ['+', '#', '.', '/', ';', '?', '&'], variables = [];

    return {
        vars: variables,
        expand: function expand(context) {
            return template.replace(/\{([^{}]+)\}|([^{}]+)/g, function (_, expression, literal) {
                if (expression) {

                    var operator = null, values = [];

                    if (operators.indexOf(expression.charAt(0)) !== -1) {
                        operator = expression.charAt(0);
                        expression = expression.substr(1);
                    }

                    expression.split(/,/g).forEach(function (variable) {
                        var tmp = /([^:*]*)(?::(\d+)|(\*))?/.exec(variable);
                        values.push.apply(values, getValues(context, operator, tmp[1], tmp[2] || tmp[3]));
                        variables.push(tmp[1]);
                    });

                    if (operator && operator !== '+') {

                        var separator = ',';

                        if (operator === '?') {
                            separator = '&';
                        } else if (operator !== '#') {
                            separator = operator;
                        }

                        return (values.length !== 0 ? operator : '') + values.join(separator);
                    } else {
                        return values.join(',');
                    }

                } else {
                    return encodeReserved(literal);
                }
            });
        }
    };
}

function getValues(context, operator, key, modifier) {

    var value = context[key], result = [];

    if (isDefined(value) && value !== '') {
        if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
            value = value.toString();

            if (modifier && modifier !== '*') {
                value = value.substring(0, parseInt(modifier, 10));
            }

            result.push(encodeValue(operator, value, isKeyOperator(operator) ? key : null));
        } else {
            if (modifier === '*') {
                if (Array.isArray(value)) {
                    value.filter(isDefined).forEach(function (value) {
                        result.push(encodeValue(operator, value, isKeyOperator(operator) ? key : null));
                    });
                } else {
                    Object.keys(value).forEach(function (k) {
                        if (isDefined(value[k])) {
                            result.push(encodeValue(operator, value[k], k));
                        }
                    });
                }
            } else {
                var tmp = [];

                if (Array.isArray(value)) {
                    value.filter(isDefined).forEach(function (value) {
                        tmp.push(encodeValue(operator, value));
                    });
                } else {
                    Object.keys(value).forEach(function (k) {
                        if (isDefined(value[k])) {
                            tmp.push(encodeURIComponent(k));
                            tmp.push(encodeValue(operator, value[k].toString()));
                        }
                    });
                }

                if (isKeyOperator(operator)) {
                    result.push(encodeURIComponent(key) + '=' + tmp.join(','));
                } else if (tmp.length !== 0) {
                    result.push(tmp.join(','));
                }
            }
        }
    } else {
        if (operator === ';') {
            result.push(encodeURIComponent(key));
        } else if (value === '' && (operator === '&' || operator === '?')) {
            result.push(encodeURIComponent(key) + '=');
        } else if (value === '') {
            result.push('');
        }
    }

    return result;
}

function isDefined(value) {
    return value !== undefined && value !== null;
}

function isKeyOperator(operator) {
    return operator === ';' || operator === '&' || operator === '?';
}

function encodeValue(operator, value, key) {

    value = (operator === '+' || operator === '#') ? encodeReserved(value) : encodeURIComponent(value);

    if (key) {
        return encodeURIComponent(key) + '=' + value;
    } else {
        return value;
    }
}

function encodeReserved(str) {
    return str.split(/(%[0-9A-Fa-f]{2})/g).map(function (part) {
        if (!/%[0-9A-Fa-f]/.test(part)) {
            part = encodeURI(part);
        }
        return part;
    }).join('');
}

/**
 * URL Template (RFC 6570) Transform.
 */

function template (options) {

    var variables = [], url = expand(options.url, options.params, variables);

    variables.forEach(function (key) {
        delete options.params[key];
    });

    return url;
}

/**
 * Service for URL templating.
 */

function Url(url, params) {

    var self = this || {}, options$$1 = url, transform;

    if (isString(url)) {
        options$$1 = {url: url, params: params};
    }

    options$$1 = merge({}, Url.options, self.$options, options$$1);

    Url.transforms.forEach(function (handler) {

        if (isString(handler)) {
            handler = Url.transform[handler];
        }

        if (isFunction(handler)) {
            transform = factory(handler, transform, self.$vm);
        }

    });

    return transform(options$$1);
}

/**
 * Url options.
 */

Url.options = {
    url: '',
    root: null,
    params: {}
};

/**
 * Url transforms.
 */

Url.transform = {template: template, query: query, root: root};
Url.transforms = ['template', 'query', 'root'];

/**
 * Encodes a Url parameter string.
 *
 * @param {Object} obj
 */

Url.params = function (obj) {

    var params = [], escape = encodeURIComponent;

    params.add = function (key, value) {

        if (isFunction(value)) {
            value = value();
        }

        if (value === null) {
            value = '';
        }

        this.push(escape(key) + '=' + escape(value));
    };

    serialize(params, obj);

    return params.join('&').replace(/%20/g, '+');
};

/**
 * Parse a URL and return its components.
 *
 * @param {String} url
 */

Url.parse = function (url) {

    var el = document.createElement('a');

    if (document.documentMode) {
        el.href = url;
        url = el.href;
    }

    el.href = url;

    return {
        href: el.href,
        protocol: el.protocol ? el.protocol.replace(/:$/, '') : '',
        port: el.port,
        host: el.host,
        hostname: el.hostname,
        pathname: el.pathname.charAt(0) === '/' ? el.pathname : '/' + el.pathname,
        search: el.search ? el.search.replace(/^\?/, '') : '',
        hash: el.hash ? el.hash.replace(/^#/, '') : ''
    };
};

function factory(handler, next, vm) {
    return function (options$$1) {
        return handler.call(vm, options$$1, next);
    };
}

function serialize(params, obj, scope) {

    var array = isArray(obj), plain = isPlainObject(obj), hash;

    each(obj, function (value, key) {

        hash = isObject(value) || isArray(value);

        if (scope) {
            key = scope + '[' + (plain || hash ? key : '') + ']';
        }

        if (!scope && array) {
            params.add(value.name, value.value);
        } else if (hash) {
            serialize(params, value, key);
        } else {
            params.add(key, value);
        }
    });
}

/**
 * XDomain client (Internet Explorer).
 */

function xdrClient (request) {
    return new PromiseObj(function (resolve) {

        var xdr = new XDomainRequest(), handler = function (ref) {
                var type = ref.type;


                var status = 0;

                if (type === 'load') {
                    status = 200;
                } else if (type === 'error') {
                    status = 500;
                }

                resolve(request.respondWith(xdr.responseText, {status: status}));
            };

        request.abort = function () { return xdr.abort(); };

        xdr.open(request.method, request.getUrl());

        if (request.timeout) {
            xdr.timeout = request.timeout;
        }

        xdr.onload = handler;
        xdr.onabort = handler;
        xdr.onerror = handler;
        xdr.ontimeout = handler;
        xdr.onprogress = function () {};
        xdr.send(request.getBody());
    });
}

/**
 * CORS Interceptor.
 */

var SUPPORTS_CORS = inBrowser && 'withCredentials' in new XMLHttpRequest();

function cors (request) {

    if (inBrowser) {

        var orgUrl = Url.parse(location.href);
        var reqUrl = Url.parse(request.getUrl());

        if (reqUrl.protocol !== orgUrl.protocol || reqUrl.host !== orgUrl.host) {

            request.crossOrigin = true;
            request.emulateHTTP = false;

            if (!SUPPORTS_CORS) {
                request.client = xdrClient;
            }
        }
    }

}

/**
 * Form data Interceptor.
 */

function form (request) {

    if (isFormData(request.body)) {
        request.headers.delete('Content-Type');
    } else if (isObject(request.body) && request.emulateJSON) {
        request.body = Url.params(request.body);
        request.headers.set('Content-Type', 'application/x-www-form-urlencoded');
    }

}

/**
 * JSON Interceptor.
 */

function json (request) {

    var type = request.headers.get('Content-Type') || '';

    if (isObject(request.body) && type.indexOf('application/json') === 0) {
        request.body = JSON.stringify(request.body);
    }

    return function (response) {

        return response.bodyText ? when(response.text(), function (text) {

            var type = response.headers.get('Content-Type') || '';

            if (type.indexOf('application/json') === 0 || isJson(text)) {

                try {
                    response.body = JSON.parse(text);
                } catch (e) {
                    response.body = null;
                }

            } else {
                response.body = text;
            }

            return response;

        }) : response;

    };
}

function isJson(str) {

    var start = str.match(/^\s*(\[|\{)/);
    var end = {'[': /]\s*$/, '{': /}\s*$/};

    return start && end[start[1]].test(str);
}

/**
 * JSONP client (Browser).
 */

function jsonpClient (request) {
    return new PromiseObj(function (resolve) {

        var name = request.jsonp || 'callback', callback = request.jsonpCallback || '_jsonp' + Math.random().toString(36).substr(2), body = null, handler, script;

        handler = function (ref) {
            var type = ref.type;


            var status = 0;

            if (type === 'load' && body !== null) {
                status = 200;
            } else if (type === 'error') {
                status = 500;
            }

            if (status && window[callback]) {
                delete window[callback];
                document.body.removeChild(script);
            }

            resolve(request.respondWith(body, {status: status}));
        };

        window[callback] = function (result) {
            body = JSON.stringify(result);
        };

        request.abort = function () {
            handler({type: 'abort'});
        };

        request.params[name] = callback;

        if (request.timeout) {
            setTimeout(request.abort, request.timeout);
        }

        script = document.createElement('script');
        script.src = request.getUrl();
        script.type = 'text/javascript';
        script.async = true;
        script.onload = handler;
        script.onerror = handler;

        document.body.appendChild(script);
    });
}

/**
 * JSONP Interceptor.
 */

function jsonp (request) {

    if (request.method == 'JSONP') {
        request.client = jsonpClient;
    }

}

/**
 * Before Interceptor.
 */

function before (request) {

    if (isFunction(request.before)) {
        request.before.call(this, request);
    }

}

/**
 * HTTP method override Interceptor.
 */

function method (request) {

    if (request.emulateHTTP && /^(PUT|PATCH|DELETE)$/i.test(request.method)) {
        request.headers.set('X-HTTP-Method-Override', request.method);
        request.method = 'POST';
    }

}

/**
 * Header Interceptor.
 */

function header (request) {

    var headers = assign({}, Http.headers.common,
        !request.crossOrigin ? Http.headers.custom : {},
        Http.headers[toLower(request.method)]
    );

    each(headers, function (value, name) {
        if (!request.headers.has(name)) {
            request.headers.set(name, value);
        }
    });

}

/**
 * XMLHttp client (Browser).
 */

function xhrClient (request) {
    return new PromiseObj(function (resolve) {

        var xhr = new XMLHttpRequest(), handler = function (event) {

                var response = request.respondWith(
                'response' in xhr ? xhr.response : xhr.responseText, {
                    status: xhr.status === 1223 ? 204 : xhr.status, // IE9 status bug
                    statusText: xhr.status === 1223 ? 'No Content' : trim(xhr.statusText)
                });

                each(trim(xhr.getAllResponseHeaders()).split('\n'), function (row) {
                    response.headers.append(row.slice(0, row.indexOf(':')), row.slice(row.indexOf(':') + 1));
                });

                resolve(response);
            };

        request.abort = function () { return xhr.abort(); };

        xhr.open(request.method, request.getUrl(), true);

        if (request.timeout) {
            xhr.timeout = request.timeout;
        }

        if (request.responseType && 'responseType' in xhr) {
            xhr.responseType = request.responseType;
        }

        if (request.withCredentials || request.credentials) {
            xhr.withCredentials = true;
        }

        if (!request.crossOrigin) {
            request.headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        // deprecated use downloadProgress
        if (isFunction(request.progress) && request.method === 'GET') {
            xhr.addEventListener('progress', request.progress);
        }

        if (isFunction(request.downloadProgress)) {
            xhr.addEventListener('progress', request.downloadProgress);
        }

        // deprecated use uploadProgress
        if (isFunction(request.progress) && /^(POST|PUT)$/i.test(request.method)) {
            xhr.upload.addEventListener('progress', request.progress);
        }

        if (isFunction(request.uploadProgress) && xhr.upload) {
            xhr.upload.addEventListener('progress', request.uploadProgress);
        }

        request.headers.forEach(function (value, name) {
            xhr.setRequestHeader(name, value);
        });

        xhr.onload = handler;
        xhr.onabort = handler;
        xhr.onerror = handler;
        xhr.ontimeout = handler;
        xhr.send(request.getBody());
    });
}

/**
 * Http client (Node).
 */

function nodeClient (request) {

    var client = __webpack_require__(55);

    return new PromiseObj(function (resolve) {

        var url = request.getUrl();
        var body = request.getBody();
        var method = request.method;
        var headers = {}, handler;

        request.headers.forEach(function (value, name) {
            headers[name] = value;
        });

        client(url, {body: body, method: method, headers: headers}).then(handler = function (resp) {

            var response = request.respondWith(resp.body, {
                status: resp.statusCode,
                statusText: trim(resp.statusMessage)
            });

            each(resp.headers, function (value, name) {
                response.headers.set(name, value);
            });

            resolve(response);

        }, function (error$$1) { return handler(error$$1.response); });
    });
}

/**
 * Base client.
 */

function Client (context) {

    var reqHandlers = [sendRequest], resHandlers = [];

    if (!isObject(context)) {
        context = null;
    }

    function Client(request) {
        while (reqHandlers.length) {

            var handler = reqHandlers.pop();

            if (isFunction(handler)) {

                var response = (void 0), next = (void 0);

                response = handler.call(context, request, function (val) { return next = val; }) || next;

                if (isObject(response)) {
                    return new PromiseObj(function (resolve, reject) {

                        resHandlers.forEach(function (handler) {
                            response = when(response, function (response) {
                                return handler.call(context, response) || response;
                            }, reject);
                        });

                        when(response, resolve, reject);

                    }, context);
                }

                if (isFunction(response)) {
                    resHandlers.unshift(response);
                }

            } else {
                warn(("Invalid interceptor of type " + (typeof handler) + ", must be a function"));
            }
        }
    }

    Client.use = function (handler) {
        reqHandlers.push(handler);
    };

    return Client;
}

function sendRequest(request) {

    var client = request.client || (inBrowser ? xhrClient : nodeClient);

    return client(request);
}

/**
 * HTTP Headers.
 */

var Headers = function Headers(headers) {
    var this$1 = this;


    this.map = {};

    each(headers, function (value, name) { return this$1.append(name, value); });
};

Headers.prototype.has = function has (name) {
    return getName(this.map, name) !== null;
};

Headers.prototype.get = function get (name) {

    var list = this.map[getName(this.map, name)];

    return list ? list.join() : null;
};

Headers.prototype.getAll = function getAll (name) {
    return this.map[getName(this.map, name)] || [];
};

Headers.prototype.set = function set (name, value) {
    this.map[normalizeName(getName(this.map, name) || name)] = [trim(value)];
};

Headers.prototype.append = function append (name, value) {

    var list = this.map[getName(this.map, name)];

    if (list) {
        list.push(trim(value));
    } else {
        this.set(name, value);
    }
};

Headers.prototype.delete = function delete$1 (name) {
    delete this.map[getName(this.map, name)];
};

Headers.prototype.deleteAll = function deleteAll () {
    this.map = {};
};

Headers.prototype.forEach = function forEach (callback, thisArg) {
        var this$1 = this;

    each(this.map, function (list, name) {
        each(list, function (value) { return callback.call(thisArg, value, name, this$1); });
    });
};

function getName(map, name) {
    return Object.keys(map).reduce(function (prev, curr) {
        return toLower(name) === toLower(curr) ? curr : prev;
    }, null);
}

function normalizeName(name) {

    if (/[^a-z0-9\-#$%&'*+.^_`|~]/i.test(name)) {
        throw new TypeError('Invalid character in header field name');
    }

    return trim(name);
}

/**
 * HTTP Response.
 */

var Response = function Response(body, ref) {
    var url = ref.url;
    var headers = ref.headers;
    var status = ref.status;
    var statusText = ref.statusText;


    this.url = url;
    this.ok = status >= 200 && status < 300;
    this.status = status || 0;
    this.statusText = statusText || '';
    this.headers = new Headers(headers);
    this.body = body;

    if (isString(body)) {

        this.bodyText = body;

    } else if (isBlob(body)) {

        this.bodyBlob = body;

        if (isBlobText(body)) {
            this.bodyText = blobText(body);
        }
    }
};

Response.prototype.blob = function blob () {
    return when(this.bodyBlob);
};

Response.prototype.text = function text () {
    return when(this.bodyText);
};

Response.prototype.json = function json () {
    return when(this.text(), function (text) { return JSON.parse(text); });
};

Object.defineProperty(Response.prototype, 'data', {

    get: function get() {
        return this.body;
    },

    set: function set(body) {
        this.body = body;
    }

});

function blobText(body) {
    return new PromiseObj(function (resolve) {

        var reader = new FileReader();

        reader.readAsText(body);
        reader.onload = function () {
            resolve(reader.result);
        };

    });
}

function isBlobText(body) {
    return body.type.indexOf('text') === 0 || body.type.indexOf('json') !== -1;
}

/**
 * HTTP Request.
 */

var Request = function Request(options$$1) {

    this.body = null;
    this.params = {};

    assign(this, options$$1, {
        method: toUpper(options$$1.method || 'GET')
    });

    if (!(this.headers instanceof Headers)) {
        this.headers = new Headers(this.headers);
    }
};

Request.prototype.getUrl = function getUrl () {
    return Url(this);
};

Request.prototype.getBody = function getBody () {
    return this.body;
};

Request.prototype.respondWith = function respondWith (body, options$$1) {
    return new Response(body, assign(options$$1 || {}, {url: this.getUrl()}));
};

/**
 * Service for sending network requests.
 */

var COMMON_HEADERS = {'Accept': 'application/json, text/plain, */*'};
var JSON_CONTENT_TYPE = {'Content-Type': 'application/json;charset=utf-8'};

function Http(options$$1) {

    var self = this || {}, client = Client(self.$vm);

    defaults(options$$1 || {}, self.$options, Http.options);

    Http.interceptors.forEach(function (handler) {

        if (isString(handler)) {
            handler = Http.interceptor[handler];
        }

        if (isFunction(handler)) {
            client.use(handler);
        }

    });

    return client(new Request(options$$1)).then(function (response) {

        return response.ok ? response : PromiseObj.reject(response);

    }, function (response) {

        if (response instanceof Error) {
            error(response);
        }

        return PromiseObj.reject(response);
    });
}

Http.options = {};

Http.headers = {
    put: JSON_CONTENT_TYPE,
    post: JSON_CONTENT_TYPE,
    patch: JSON_CONTENT_TYPE,
    delete: JSON_CONTENT_TYPE,
    common: COMMON_HEADERS,
    custom: {}
};

Http.interceptor = {before: before, method: method, jsonp: jsonp, json: json, form: form, header: header, cors: cors};
Http.interceptors = ['before', 'method', 'jsonp', 'json', 'form', 'header', 'cors'];

['get', 'delete', 'head', 'jsonp'].forEach(function (method$$1) {

    Http[method$$1] = function (url, options$$1) {
        return this(assign(options$$1 || {}, {url: url, method: method$$1}));
    };

});

['post', 'put', 'patch'].forEach(function (method$$1) {

    Http[method$$1] = function (url, body, options$$1) {
        return this(assign(options$$1 || {}, {url: url, method: method$$1, body: body}));
    };

});

/**
 * Service for interacting with RESTful services.
 */

function Resource(url, params, actions, options$$1) {

    var self = this || {}, resource = {};

    actions = assign({},
        Resource.actions,
        actions
    );

    each(actions, function (action, name) {

        action = merge({url: url, params: assign({}, params)}, options$$1, action);

        resource[name] = function () {
            return (self.$http || Http)(opts(action, arguments));
        };
    });

    return resource;
}

function opts(action, args) {

    var options$$1 = assign({}, action), params = {}, body;

    switch (args.length) {

        case 2:

            params = args[0];
            body = args[1];

            break;

        case 1:

            if (/^(POST|PUT|PATCH)$/i.test(options$$1.method)) {
                body = args[0];
            } else {
                params = args[0];
            }

            break;

        case 0:

            break;

        default:

            throw 'Expected up to 2 arguments [params, body], got ' + args.length + ' arguments';
    }

    options$$1.body = body;
    options$$1.params = assign({}, options$$1.params, params);

    return options$$1;
}

Resource.actions = {

    get: {method: 'GET'},
    save: {method: 'POST'},
    query: {method: 'GET'},
    update: {method: 'PUT'},
    remove: {method: 'DELETE'},
    delete: {method: 'DELETE'}

};

/**
 * Install plugin.
 */

function plugin(Vue) {

    if (plugin.installed) {
        return;
    }

    Util(Vue);

    Vue.url = Url;
    Vue.http = Http;
    Vue.resource = Resource;
    Vue.Promise = PromiseObj;

    Object.defineProperties(Vue.prototype, {

        $url: {
            get: function get() {
                return options(Vue.url, this, this.$options.url);
            }
        },

        $http: {
            get: function get() {
                return options(Vue.http, this, this.$options.http);
            }
        },

        $resource: {
            get: function get() {
                return Vue.resource.bind(this);
            }
        },

        $promise: {
            get: function get() {
                var this$1 = this;

                return function (executor) { return new Vue.Promise(executor, this$1); };
            }
        }

    });
}

if (typeof window !== 'undefined' && window.Vue) {
    window.Vue.use(plugin);
}

/* harmony default export */ __webpack_exports__["default"] = (plugin);



/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(3);

var _vue2 = _interopRequireDefault(_vue);

var _main = __webpack_require__(10);

var _main2 = _interopRequireDefault(_main);

var _store = __webpack_require__(53);

var _store2 = _interopRequireDefault(_store);

var _vueResize = __webpack_require__(58);

var _vueResize2 = _interopRequireDefault(_vueResize);

var _vueJsToggleButton = __webpack_require__(59);

var _vueJsToggleButton2 = _interopRequireDefault(_vueJsToggleButton);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vueJsToggleButton2.default); /*jshint esversion: 6 */


_vue2.default.use(_vueResize2.default);

window.addEventListener('load', function () {
	new _vue2.default({
		el: '#optimole-app',
		store: _store2.default,
		components: {
			App: _main2.default
		}
	});
});

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var scope = (typeof global !== "undefined" && global) ||
            (typeof self !== "undefined" && self) ||
            window;
var apply = Function.prototype.apply;

// DOM APIs, for completeness

exports.setTimeout = function() {
  return new Timeout(apply.call(setTimeout, scope, arguments), clearTimeout);
};
exports.setInterval = function() {
  return new Timeout(apply.call(setInterval, scope, arguments), clearInterval);
};
exports.clearTimeout =
exports.clearInterval = function(timeout) {
  if (timeout) {
    timeout.close();
  }
};

function Timeout(id, clearFn) {
  this._id = id;
  this._clearFn = clearFn;
}
Timeout.prototype.unref = Timeout.prototype.ref = function() {};
Timeout.prototype.close = function() {
  this._clearFn.call(scope, this._id);
};

// Does not start the time, just sets up the members needed.
exports.enroll = function(item, msecs) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = msecs;
};

exports.unenroll = function(item) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = -1;
};

exports._unrefActive = exports.active = function(item) {
  clearTimeout(item._idleTimeoutId);

  var msecs = item._idleTimeout;
  if (msecs >= 0) {
    item._idleTimeoutId = setTimeout(function onTimeout() {
      if (item._onTimeout)
        item._onTimeout();
    }, msecs);
  }
};

// setimmediate attaches itself to the global object
__webpack_require__(9);
// On some exotic environments, it's not clear which object `setimmediate` was
// able to install onto.  Search each possibility in the same order as the
// `setimmediate` library.
exports.setImmediate = (typeof self !== "undefined" && self.setImmediate) ||
                       (typeof global !== "undefined" && global.setImmediate) ||
                       (this && this.setImmediate);
exports.clearImmediate = (typeof self !== "undefined" && self.clearImmediate) ||
                         (typeof global !== "undefined" && global.clearImmediate) ||
                         (this && this.clearImmediate);

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(2)))

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global, process) {(function (global, undefined) {
    "use strict";

    if (global.setImmediate) {
        return;
    }

    var nextHandle = 1; // Spec says greater than zero
    var tasksByHandle = {};
    var currentlyRunningATask = false;
    var doc = global.document;
    var registerImmediate;

    function setImmediate(callback) {
      // Callback can either be a function or a string
      if (typeof callback !== "function") {
        callback = new Function("" + callback);
      }
      // Copy function arguments
      var args = new Array(arguments.length - 1);
      for (var i = 0; i < args.length; i++) {
          args[i] = arguments[i + 1];
      }
      // Store and register the task
      var task = { callback: callback, args: args };
      tasksByHandle[nextHandle] = task;
      registerImmediate(nextHandle);
      return nextHandle++;
    }

    function clearImmediate(handle) {
        delete tasksByHandle[handle];
    }

    function run(task) {
        var callback = task.callback;
        var args = task.args;
        switch (args.length) {
        case 0:
            callback();
            break;
        case 1:
            callback(args[0]);
            break;
        case 2:
            callback(args[0], args[1]);
            break;
        case 3:
            callback(args[0], args[1], args[2]);
            break;
        default:
            callback.apply(undefined, args);
            break;
        }
    }

    function runIfPresent(handle) {
        // From the spec: "Wait until any invocations of this algorithm started before this one have completed."
        // So if we're currently running a task, we'll need to delay this invocation.
        if (currentlyRunningATask) {
            // Delay by doing a setTimeout. setImmediate was tried instead, but in Firefox 7 it generated a
            // "too much recursion" error.
            setTimeout(runIfPresent, 0, handle);
        } else {
            var task = tasksByHandle[handle];
            if (task) {
                currentlyRunningATask = true;
                try {
                    run(task);
                } finally {
                    clearImmediate(handle);
                    currentlyRunningATask = false;
                }
            }
        }
    }

    function installNextTickImplementation() {
        registerImmediate = function(handle) {
            process.nextTick(function () { runIfPresent(handle); });
        };
    }

    function canUsePostMessage() {
        // The test against `importScripts` prevents this implementation from being installed inside a web worker,
        // where `global.postMessage` means something completely different and can't be used for this purpose.
        if (global.postMessage && !global.importScripts) {
            var postMessageIsAsynchronous = true;
            var oldOnMessage = global.onmessage;
            global.onmessage = function() {
                postMessageIsAsynchronous = false;
            };
            global.postMessage("", "*");
            global.onmessage = oldOnMessage;
            return postMessageIsAsynchronous;
        }
    }

    function installPostMessageImplementation() {
        // Installs an event handler on `global` for the `message` event: see
        // * https://developer.mozilla.org/en/DOM/window.postMessage
        // * http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html#crossDocumentMessages

        var messagePrefix = "setImmediate$" + Math.random() + "$";
        var onGlobalMessage = function(event) {
            if (event.source === global &&
                typeof event.data === "string" &&
                event.data.indexOf(messagePrefix) === 0) {
                runIfPresent(+event.data.slice(messagePrefix.length));
            }
        };

        if (global.addEventListener) {
            global.addEventListener("message", onGlobalMessage, false);
        } else {
            global.attachEvent("onmessage", onGlobalMessage);
        }

        registerImmediate = function(handle) {
            global.postMessage(messagePrefix + handle, "*");
        };
    }

    function installMessageChannelImplementation() {
        var channel = new MessageChannel();
        channel.port1.onmessage = function(event) {
            var handle = event.data;
            runIfPresent(handle);
        };

        registerImmediate = function(handle) {
            channel.port2.postMessage(handle);
        };
    }

    function installReadyStateChangeImplementation() {
        var html = doc.documentElement;
        registerImmediate = function(handle) {
            // Create a <script> element; its readystatechange event will be fired asynchronously once it is inserted
            // into the document. Do so, thus queuing up the task. Remember to clean up once it's been called.
            var script = doc.createElement("script");
            script.onreadystatechange = function () {
                runIfPresent(handle);
                script.onreadystatechange = null;
                html.removeChild(script);
                script = null;
            };
            html.appendChild(script);
        };
    }

    function installSetTimeoutImplementation() {
        registerImmediate = function(handle) {
            setTimeout(runIfPresent, 0, handle);
        };
    }

    // If supported, we should attach to the prototype of global, since that is where setTimeout et al. live.
    var attachTo = Object.getPrototypeOf && Object.getPrototypeOf(global);
    attachTo = attachTo && attachTo.setTimeout ? attachTo : global;

    // Don't get fooled by e.g. browserify environments.
    if ({}.toString.call(global.process) === "[object process]") {
        // For Node.js before 0.9
        installNextTickImplementation();

    } else if (canUsePostMessage()) {
        // For non-IE10 modern browsers
        installPostMessageImplementation();

    } else if (global.MessageChannel) {
        // For web workers, where supported
        installMessageChannelImplementation();

    } else if (doc && "onreadystatechange" in doc.createElement("script")) {
        // For IE 6–8
        installReadyStateChangeImplementation();

    } else {
        // For older browsers
        installSetTimeoutImplementation();
    }

    attachTo.setImmediate = setImmediate;
    attachTo.clearImmediate = clearImmediate;
}(typeof self === "undefined" ? typeof global === "undefined" ? this : global : self));

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(2), __webpack_require__(4)))

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(11)
__vue_script__ = __webpack_require__(13)
__vue_template__ = __webpack_require__(52)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\main.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(12);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-21c7f086&file=main.vue!../../../node_modules/sass-loader/lib/loader.js!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./main.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-21c7f086&file=main.vue!../../../node_modules/sass-loader/lib/loader.js!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./main.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "#optimole-app {\n  padding: 0 30px 0 20px;\n  /*! bulma.io v0.7.4 | MIT License | github.com/jgthms/bulma */\n  /*! minireset.css v0.0.4 | MIT License | github.com/jgthms/minireset.css */ }\n\n@keyframes spinAround {\n  from {\n    transform: rotate(0deg); }\n  to {\n    transform: rotate(359deg); } }\n  #optimole-app .delete, #optimole-app .modal-close, #optimole-app .is-unselectable, #optimole-app .button, #optimole-app .file, #optimole-app .breadcrumb, #optimole-app .pagination-previous,\n  #optimole-app .pagination-next,\n  #optimole-app .pagination-link,\n  #optimole-app .pagination-ellipsis, #optimole-app .tabs {\n    -webkit-touch-callout: none;\n    -webkit-user-select: none;\n    -moz-user-select: none;\n    -ms-user-select: none;\n    user-select: none; }\n  #optimole-app .select:not(.is-multiple):not(.is-loading)::after, #optimole-app .navbar-link:not(.is-arrowless)::after {\n    border: 3px solid transparent;\n    border-radius: 2px;\n    border-right: 0;\n    border-top: 0;\n    content: \" \";\n    display: block;\n    height: 0.625em;\n    margin-top: -0.4375em;\n    pointer-events: none;\n    position: absolute;\n    top: 50%;\n    transform: rotate(-45deg);\n    transform-origin: center;\n    width: 0.625em; }\n  #optimole-app .box:not(:last-child), #optimole-app .content:not(:last-child), #optimole-app .notification:not(:last-child), #optimole-app .progress:not(:last-child), #optimole-app .table:not(:last-child), #optimole-app .table-container:not(:last-child), #optimole-app .title:not(:last-child),\n  #optimole-app .subtitle:not(:last-child), #optimole-app .block:not(:last-child), #optimole-app .highlight:not(:last-child), #optimole-app .breadcrumb:not(:last-child), #optimole-app .level:not(:last-child), #optimole-app .list:not(:last-child), #optimole-app .message:not(:last-child), #optimole-app .tabs:not(:last-child) {\n    margin-bottom: 1.5rem; }\n  #optimole-app .delete, #optimole-app .modal-close {\n    -moz-appearance: none;\n    -webkit-appearance: none;\n    background-color: rgba(10, 10, 10, 0.2);\n    border: none;\n    border-radius: 290486px;\n    cursor: pointer;\n    pointer-events: auto;\n    display: inline-block;\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    font-size: 0;\n    height: 20px;\n    max-height: 20px;\n    max-width: 20px;\n    min-height: 20px;\n    min-width: 20px;\n    outline: none;\n    position: relative;\n    vertical-align: top;\n    width: 20px; }\n    #optimole-app .delete::before, #optimole-app .modal-close::before, #optimole-app .delete::after, #optimole-app .modal-close::after {\n      background-color: white;\n      content: \"\";\n      display: block;\n      left: 50%;\n      position: absolute;\n      top: 50%;\n      transform: translateX(-50%) translateY(-50%) rotate(45deg);\n      transform-origin: center center; }\n    #optimole-app .delete::before, #optimole-app .modal-close::before {\n      height: 2px;\n      width: 50%; }\n    #optimole-app .delete::after, #optimole-app .modal-close::after {\n      height: 50%;\n      width: 2px; }\n    #optimole-app .delete:hover, #optimole-app .modal-close:hover, #optimole-app .delete:focus, #optimole-app .modal-close:focus {\n      background-color: rgba(10, 10, 10, 0.3); }\n    #optimole-app .delete:active, #optimole-app .modal-close:active {\n      background-color: rgba(10, 10, 10, 0.4); }\n    #optimole-app .is-small.delete, #optimole-app .is-small.modal-close {\n      height: 16px;\n      max-height: 16px;\n      max-width: 16px;\n      min-height: 16px;\n      min-width: 16px;\n      width: 16px; }\n    #optimole-app .is-medium.delete, #optimole-app .is-medium.modal-close {\n      height: 24px;\n      max-height: 24px;\n      max-width: 24px;\n      min-height: 24px;\n      min-width: 24px;\n      width: 24px; }\n    #optimole-app .is-large.delete, #optimole-app .is-large.modal-close {\n      height: 32px;\n      max-height: 32px;\n      max-width: 32px;\n      min-height: 32px;\n      min-width: 32px;\n      width: 32px; }\n  #optimole-app .button.is-loading::after, #optimole-app .select.is-loading::after, #optimole-app .control.is-loading::after, #optimole-app .loader {\n    animation: spinAround 500ms infinite linear;\n    border: 2px solid #dbdbdb;\n    border-radius: 290486px;\n    border-right-color: transparent;\n    border-top-color: transparent;\n    content: \"\";\n    display: block;\n    height: 1em;\n    position: relative;\n    width: 1em; }\n  #optimole-app .is-overlay, #optimole-app .image.is-square img,\n  #optimole-app .image.is-square .has-ratio, #optimole-app .image.is-1by1 img,\n  #optimole-app .image.is-1by1 .has-ratio, #optimole-app .image.is-5by4 img,\n  #optimole-app .image.is-5by4 .has-ratio, #optimole-app .image.is-4by3 img,\n  #optimole-app .image.is-4by3 .has-ratio, #optimole-app .image.is-3by2 img,\n  #optimole-app .image.is-3by2 .has-ratio, #optimole-app .image.is-5by3 img,\n  #optimole-app .image.is-5by3 .has-ratio, #optimole-app .image.is-16by9 img,\n  #optimole-app .image.is-16by9 .has-ratio, #optimole-app .image.is-2by1 img,\n  #optimole-app .image.is-2by1 .has-ratio, #optimole-app .image.is-3by1 img,\n  #optimole-app .image.is-3by1 .has-ratio, #optimole-app .image.is-4by5 img,\n  #optimole-app .image.is-4by5 .has-ratio, #optimole-app .image.is-3by4 img,\n  #optimole-app .image.is-3by4 .has-ratio, #optimole-app .image.is-2by3 img,\n  #optimole-app .image.is-2by3 .has-ratio, #optimole-app .image.is-3by5 img,\n  #optimole-app .image.is-3by5 .has-ratio, #optimole-app .image.is-9by16 img,\n  #optimole-app .image.is-9by16 .has-ratio, #optimole-app .image.is-1by2 img,\n  #optimole-app .image.is-1by2 .has-ratio, #optimole-app .image.is-1by3 img,\n  #optimole-app .image.is-1by3 .has-ratio, #optimole-app .modal, #optimole-app .modal-background, #optimole-app .hero-video {\n    bottom: 0;\n    left: 0;\n    position: absolute;\n    right: 0;\n    top: 0; }\n  #optimole-app .button, #optimole-app .input,\n  #optimole-app .textarea, #optimole-app .select select, #optimole-app .file-cta,\n  #optimole-app .file-name, #optimole-app .pagination-previous,\n  #optimole-app .pagination-next,\n  #optimole-app .pagination-link,\n  #optimole-app .pagination-ellipsis {\n    -moz-appearance: none;\n    -webkit-appearance: none;\n    -ms-flex-align: center;\n        align-items: center;\n    border: 1px solid transparent;\n    border-radius: 4px;\n    box-shadow: none;\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    font-size: 1rem;\n    height: 2.25em;\n    -ms-flex-pack: start;\n        justify-content: flex-start;\n    line-height: 1.5;\n    padding-bottom: calc(0.375em - 1px);\n    padding-left: calc(0.625em - 1px);\n    padding-right: calc(0.625em - 1px);\n    padding-top: calc(0.375em - 1px);\n    position: relative;\n    vertical-align: top; }\n    #optimole-app .button:focus, #optimole-app .input:focus,\n    #optimole-app .textarea:focus, #optimole-app .select select:focus, #optimole-app .file-cta:focus,\n    #optimole-app .file-name:focus, #optimole-app .pagination-previous:focus,\n    #optimole-app .pagination-next:focus,\n    #optimole-app .pagination-link:focus,\n    #optimole-app .pagination-ellipsis:focus, #optimole-app .is-focused.button, #optimole-app .is-focused.input,\n    #optimole-app .is-focused.textarea, #optimole-app .select select.is-focused, #optimole-app .is-focused.file-cta,\n    #optimole-app .is-focused.file-name, #optimole-app .is-focused.pagination-previous,\n    #optimole-app .is-focused.pagination-next,\n    #optimole-app .is-focused.pagination-link,\n    #optimole-app .is-focused.pagination-ellipsis, #optimole-app .button:active, #optimole-app .input:active,\n    #optimole-app .textarea:active, #optimole-app .select select:active, #optimole-app .file-cta:active,\n    #optimole-app .file-name:active, #optimole-app .pagination-previous:active,\n    #optimole-app .pagination-next:active,\n    #optimole-app .pagination-link:active,\n    #optimole-app .pagination-ellipsis:active, #optimole-app .is-active.button, #optimole-app .is-active.input,\n    #optimole-app .is-active.textarea, #optimole-app .select select.is-active, #optimole-app .is-active.file-cta,\n    #optimole-app .is-active.file-name, #optimole-app .is-active.pagination-previous,\n    #optimole-app .is-active.pagination-next,\n    #optimole-app .is-active.pagination-link,\n    #optimole-app .is-active.pagination-ellipsis {\n      outline: none; }\n    #optimole-app .button[disabled], #optimole-app .input[disabled],\n    #optimole-app .textarea[disabled], #optimole-app .select select[disabled], #optimole-app .file-cta[disabled],\n    #optimole-app .file-name[disabled], #optimole-app .pagination-previous[disabled],\n    #optimole-app .pagination-next[disabled],\n    #optimole-app .pagination-link[disabled],\n    #optimole-app .pagination-ellipsis[disabled],\n    fieldset[disabled] #optimole-app .button,\n    fieldset[disabled] #optimole-app .input,\n    fieldset[disabled] #optimole-app .textarea,\n    fieldset[disabled] #optimole-app .select select,\n    fieldset[disabled] #optimole-app .file-cta,\n    fieldset[disabled] #optimole-app .file-name,\n    fieldset[disabled] #optimole-app .pagination-previous,\n    fieldset[disabled] #optimole-app .pagination-next,\n    fieldset[disabled] #optimole-app .pagination-link,\n    fieldset[disabled] #optimole-app .pagination-ellipsis {\n      cursor: not-allowed; }\n  #optimole-app html,\n  #optimole-app body,\n  #optimole-app p,\n  #optimole-app ol,\n  #optimole-app ul,\n  #optimole-app li,\n  #optimole-app dl,\n  #optimole-app dt,\n  #optimole-app dd,\n  #optimole-app blockquote,\n  #optimole-app figure,\n  #optimole-app fieldset,\n  #optimole-app legend,\n  #optimole-app textarea,\n  #optimole-app pre,\n  #optimole-app iframe,\n  #optimole-app hr,\n  #optimole-app h1,\n  #optimole-app h2,\n  #optimole-app h3,\n  #optimole-app h4,\n  #optimole-app h5,\n  #optimole-app h6 {\n    margin: 0;\n    padding: 0; }\n  #optimole-app h1,\n  #optimole-app h2,\n  #optimole-app h3,\n  #optimole-app h4,\n  #optimole-app h5,\n  #optimole-app h6 {\n    font-size: 100%;\n    font-weight: normal; }\n  #optimole-app ul {\n    list-style: none; }\n  #optimole-app button,\n  #optimole-app input,\n  #optimole-app select,\n  #optimole-app textarea {\n    margin: 0; }\n  #optimole-app html {\n    box-sizing: border-box; }\n  #optimole-app *, #optimole-app *::before, #optimole-app *::after {\n    box-sizing: inherit; }\n  #optimole-app img,\n  #optimole-app embed,\n  #optimole-app iframe,\n  #optimole-app object,\n  #optimole-app video {\n    height: auto;\n    max-width: 100%; }\n  #optimole-app audio {\n    max-width: 100%; }\n  #optimole-app iframe {\n    border: 0; }\n  #optimole-app table {\n    border-collapse: collapse;\n    border-spacing: 0; }\n  #optimole-app td,\n  #optimole-app th {\n    padding: 0;\n    text-align: left; }\n  #optimole-app html {\n    background-color: white;\n    font-size: 16px;\n    -moz-osx-font-smoothing: grayscale;\n    -webkit-font-smoothing: antialiased;\n    min-width: 300px;\n    overflow-x: hidden;\n    overflow-y: scroll;\n    text-rendering: optimizeLegibility;\n    -webkit-text-size-adjust: 100%;\n        -ms-text-size-adjust: 100%;\n            text-size-adjust: 100%; }\n  #optimole-app article,\n  #optimole-app aside,\n  #optimole-app figure,\n  #optimole-app footer,\n  #optimole-app header,\n  #optimole-app hgroup,\n  #optimole-app section {\n    display: block; }\n  #optimole-app body,\n  #optimole-app button,\n  #optimole-app input,\n  #optimole-app select,\n  #optimole-app textarea {\n    font-family: BlinkMacSystemFont, -apple-system, \"Segoe UI\", \"Roboto\", \"Oxygen\", \"Ubuntu\", \"Cantarell\", \"Fira Sans\", \"Droid Sans\", \"Helvetica Neue\", \"Helvetica\", \"Arial\", sans-serif; }\n  #optimole-app code,\n  #optimole-app pre {\n    -moz-osx-font-smoothing: auto;\n    -webkit-font-smoothing: auto;\n    font-family: monospace; }\n  #optimole-app body {\n    color: #4a4a4a;\n    font-size: 1rem;\n    font-weight: 400;\n    line-height: 1.5; }\n  #optimole-app a {\n    color: #3273dc;\n    cursor: pointer;\n    text-decoration: none; }\n    #optimole-app a strong {\n      color: currentColor; }\n    #optimole-app a:hover {\n      color: #363636; }\n  #optimole-app code {\n    background-color: whitesmoke;\n    color: #ff3860;\n    font-size: 0.875em;\n    font-weight: normal;\n    padding: 0.25em 0.5em 0.25em; }\n  #optimole-app hr {\n    background-color: whitesmoke;\n    border: none;\n    display: block;\n    height: 2px;\n    margin: 1.5rem 0; }\n  #optimole-app img {\n    height: auto;\n    max-width: 100%; }\n  #optimole-app input[type=\"checkbox\"],\n  #optimole-app input[type=\"radio\"] {\n    vertical-align: baseline; }\n  #optimole-app small {\n    font-size: 0.875em; }\n  #optimole-app span {\n    font-style: inherit;\n    font-weight: inherit; }\n  #optimole-app strong {\n    color: #363636;\n    font-weight: 700; }\n  #optimole-app fieldset {\n    border: none; }\n  #optimole-app pre {\n    -webkit-overflow-scrolling: touch;\n    background-color: whitesmoke;\n    color: #4a4a4a;\n    font-size: 0.875em;\n    overflow-x: auto;\n    padding: 1.25rem 1.5rem;\n    white-space: pre;\n    word-wrap: normal; }\n    #optimole-app pre code {\n      background-color: transparent;\n      color: currentColor;\n      font-size: 1em;\n      padding: 0; }\n  #optimole-app table td,\n  #optimole-app table th {\n    text-align: left;\n    vertical-align: top; }\n  #optimole-app table th {\n    color: #363636; }\n  #optimole-app .is-clearfix::after {\n    clear: both;\n    content: \" \";\n    display: table; }\n  #optimole-app .is-pulled-left {\n    float: left !important; }\n  #optimole-app .is-pulled-right {\n    float: right !important; }\n  #optimole-app .is-clipped {\n    overflow: hidden !important; }\n  #optimole-app .is-size-1 {\n    font-size: 3rem !important; }\n  #optimole-app .is-size-2 {\n    font-size: 2.5rem !important; }\n  #optimole-app .is-size-3 {\n    font-size: 2rem !important; }\n  #optimole-app .is-size-4 {\n    font-size: 1.5rem !important; }\n  #optimole-app .is-size-5 {\n    font-size: 1.25rem !important; }\n  #optimole-app .is-size-6 {\n    font-size: 1rem !important; }\n  #optimole-app .is-size-7 {\n    font-size: 0.75rem !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-size-1-mobile {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-mobile {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-mobile {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-mobile {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-mobile {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-mobile {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-mobile {\n      font-size: 0.75rem !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-size-1-tablet {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-tablet {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-tablet {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-tablet {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-tablet {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-tablet {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-tablet {\n      font-size: 0.75rem !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-size-1-touch {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-touch {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-touch {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-touch {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-touch {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-touch {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-touch {\n      font-size: 0.75rem !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-size-1-desktop {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-desktop {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-desktop {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-desktop {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-desktop {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-desktop {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-desktop {\n      font-size: 0.75rem !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-size-1-widescreen {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-widescreen {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-widescreen {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-widescreen {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-widescreen {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-widescreen {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-widescreen {\n      font-size: 0.75rem !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-size-1-fullhd {\n      font-size: 3rem !important; }\n    #optimole-app .is-size-2-fullhd {\n      font-size: 2.5rem !important; }\n    #optimole-app .is-size-3-fullhd {\n      font-size: 2rem !important; }\n    #optimole-app .is-size-4-fullhd {\n      font-size: 1.5rem !important; }\n    #optimole-app .is-size-5-fullhd {\n      font-size: 1.25rem !important; }\n    #optimole-app .is-size-6-fullhd {\n      font-size: 1rem !important; }\n    #optimole-app .is-size-7-fullhd {\n      font-size: 0.75rem !important; } }\n  #optimole-app .has-text-centered {\n    text-align: center !important; }\n  #optimole-app .has-text-justified {\n    text-align: justify !important; }\n  #optimole-app .has-text-left {\n    text-align: left !important; }\n  #optimole-app .has-text-right {\n    text-align: right !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .has-text-centered-mobile {\n      text-align: center !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .has-text-centered-tablet {\n      text-align: center !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .has-text-centered-tablet-only {\n      text-align: center !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .has-text-centered-touch {\n      text-align: center !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .has-text-centered-desktop {\n      text-align: center !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .has-text-centered-desktop-only {\n      text-align: center !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .has-text-centered-widescreen {\n      text-align: center !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .has-text-centered-widescreen-only {\n      text-align: center !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .has-text-centered-fullhd {\n      text-align: center !important; } }\n  @media screen and (max-width: 768px) {\n    #optimole-app .has-text-justified-mobile {\n      text-align: justify !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .has-text-justified-tablet {\n      text-align: justify !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .has-text-justified-tablet-only {\n      text-align: justify !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .has-text-justified-touch {\n      text-align: justify !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .has-text-justified-desktop {\n      text-align: justify !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .has-text-justified-desktop-only {\n      text-align: justify !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .has-text-justified-widescreen {\n      text-align: justify !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .has-text-justified-widescreen-only {\n      text-align: justify !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .has-text-justified-fullhd {\n      text-align: justify !important; } }\n  @media screen and (max-width: 768px) {\n    #optimole-app .has-text-left-mobile {\n      text-align: left !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .has-text-left-tablet {\n      text-align: left !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .has-text-left-tablet-only {\n      text-align: left !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .has-text-left-touch {\n      text-align: left !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .has-text-left-desktop {\n      text-align: left !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .has-text-left-desktop-only {\n      text-align: left !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .has-text-left-widescreen {\n      text-align: left !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .has-text-left-widescreen-only {\n      text-align: left !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .has-text-left-fullhd {\n      text-align: left !important; } }\n  @media screen and (max-width: 768px) {\n    #optimole-app .has-text-right-mobile {\n      text-align: right !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .has-text-right-tablet {\n      text-align: right !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .has-text-right-tablet-only {\n      text-align: right !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .has-text-right-touch {\n      text-align: right !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .has-text-right-desktop {\n      text-align: right !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .has-text-right-desktop-only {\n      text-align: right !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .has-text-right-widescreen {\n      text-align: right !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .has-text-right-widescreen-only {\n      text-align: right !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .has-text-right-fullhd {\n      text-align: right !important; } }\n  #optimole-app .is-capitalized {\n    text-transform: capitalize !important; }\n  #optimole-app .is-lowercase {\n    text-transform: lowercase !important; }\n  #optimole-app .is-uppercase {\n    text-transform: uppercase !important; }\n  #optimole-app .is-italic {\n    font-style: italic !important; }\n  #optimole-app .has-text-white {\n    color: white !important; }\n  #optimole-app a.has-text-white:hover, #optimole-app a.has-text-white:focus {\n    color: #e6e6e6 !important; }\n  #optimole-app .has-background-white {\n    background-color: white !important; }\n  #optimole-app .has-text-black {\n    color: #0a0a0a !important; }\n  #optimole-app a.has-text-black:hover, #optimole-app a.has-text-black:focus {\n    color: black !important; }\n  #optimole-app .has-background-black {\n    background-color: #0a0a0a !important; }\n  #optimole-app .has-text-light {\n    color: whitesmoke !important; }\n  #optimole-app a.has-text-light:hover, #optimole-app a.has-text-light:focus {\n    color: #dbdbdb !important; }\n  #optimole-app .has-background-light {\n    background-color: whitesmoke !important; }\n  #optimole-app .has-text-dark {\n    color: #363636 !important; }\n  #optimole-app a.has-text-dark:hover, #optimole-app a.has-text-dark:focus {\n    color: #1c1c1c !important; }\n  #optimole-app .has-background-dark {\n    background-color: #363636 !important; }\n  #optimole-app .has-text-primary {\n    color: #EF686B !important; }\n  #optimole-app a.has-text-primary:hover, #optimole-app a.has-text-primary:focus {\n    color: #ea3a3e !important; }\n  #optimole-app .has-background-primary {\n    background-color: #EF686B !important; }\n  #optimole-app .has-text-link {\n    color: #3273dc !important; }\n  #optimole-app a.has-text-link:hover, #optimole-app a.has-text-link:focus {\n    color: #205bbc !important; }\n  #optimole-app .has-background-link {\n    background-color: #3273dc !important; }\n  #optimole-app .has-text-info {\n    color: #5180C1 !important; }\n  #optimole-app a.has-text-info:hover, #optimole-app a.has-text-info:focus {\n    color: #3b67a4 !important; }\n  #optimole-app .has-background-info {\n    background-color: #5180C1 !important; }\n  #optimole-app .has-text-success {\n    color: #34a85e !important; }\n  #optimole-app a.has-text-success:hover, #optimole-app a.has-text-success:focus {\n    color: #288148 !important; }\n  #optimole-app .has-background-success {\n    background-color: #34a85e !important; }\n  #optimole-app .has-text-warning {\n    color: #ffdd57 !important; }\n  #optimole-app a.has-text-warning:hover, #optimole-app a.has-text-warning:focus {\n    color: #ffd324 !important; }\n  #optimole-app .has-background-warning {\n    background-color: #ffdd57 !important; }\n  #optimole-app .has-text-danger {\n    color: #D54222 !important; }\n  #optimole-app a.has-text-danger:hover, #optimole-app a.has-text-danger:focus {\n    color: #a9341b !important; }\n  #optimole-app .has-background-danger {\n    background-color: #D54222 !important; }\n  #optimole-app .has-text-black-bis {\n    color: #121212 !important; }\n  #optimole-app .has-background-black-bis {\n    background-color: #121212 !important; }\n  #optimole-app .has-text-black-ter {\n    color: #242424 !important; }\n  #optimole-app .has-background-black-ter {\n    background-color: #242424 !important; }\n  #optimole-app .has-text-grey-darker {\n    color: #363636 !important; }\n  #optimole-app .has-background-grey-darker {\n    background-color: #363636 !important; }\n  #optimole-app .has-text-grey-dark {\n    color: #4a4a4a !important; }\n  #optimole-app .has-background-grey-dark {\n    background-color: #4a4a4a !important; }\n  #optimole-app .has-text-grey {\n    color: #7a7a7a !important; }\n  #optimole-app .has-background-grey {\n    background-color: #7a7a7a !important; }\n  #optimole-app .has-text-grey-light {\n    color: #b5b5b5 !important; }\n  #optimole-app .has-background-grey-light {\n    background-color: #b5b5b5 !important; }\n  #optimole-app .has-text-grey-lighter {\n    color: #dbdbdb !important; }\n  #optimole-app .has-background-grey-lighter {\n    background-color: #dbdbdb !important; }\n  #optimole-app .has-text-white-ter {\n    color: whitesmoke !important; }\n  #optimole-app .has-background-white-ter {\n    background-color: whitesmoke !important; }\n  #optimole-app .has-text-white-bis {\n    color: #fafafa !important; }\n  #optimole-app .has-background-white-bis {\n    background-color: #fafafa !important; }\n  #optimole-app .has-text-weight-light {\n    font-weight: 300 !important; }\n  #optimole-app .has-text-weight-normal {\n    font-weight: 400 !important; }\n  #optimole-app .has-text-weight-semibold {\n    font-weight: 600 !important; }\n  #optimole-app .has-text-weight-bold {\n    font-weight: 700 !important; }\n  #optimole-app .is-family-primary {\n    font-family: BlinkMacSystemFont, -apple-system, \"Segoe UI\", \"Roboto\", \"Oxygen\", \"Ubuntu\", \"Cantarell\", \"Fira Sans\", \"Droid Sans\", \"Helvetica Neue\", \"Helvetica\", \"Arial\", sans-serif !important; }\n  #optimole-app .is-family-secondary {\n    font-family: BlinkMacSystemFont, -apple-system, \"Segoe UI\", \"Roboto\", \"Oxygen\", \"Ubuntu\", \"Cantarell\", \"Fira Sans\", \"Droid Sans\", \"Helvetica Neue\", \"Helvetica\", \"Arial\", sans-serif !important; }\n  #optimole-app .is-family-sans-serif {\n    font-family: BlinkMacSystemFont, -apple-system, \"Segoe UI\", \"Roboto\", \"Oxygen\", \"Ubuntu\", \"Cantarell\", \"Fira Sans\", \"Droid Sans\", \"Helvetica Neue\", \"Helvetica\", \"Arial\", sans-serif !important; }\n  #optimole-app .is-family-monospace {\n    font-family: monospace !important; }\n  #optimole-app .is-family-code {\n    font-family: monospace !important; }\n  #optimole-app .is-block {\n    display: block !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-block-mobile {\n      display: block !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-block-tablet {\n      display: block !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-block-tablet-only {\n      display: block !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-block-touch {\n      display: block !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-block-desktop {\n      display: block !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-block-desktop-only {\n      display: block !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-block-widescreen {\n      display: block !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-block-widescreen-only {\n      display: block !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-block-fullhd {\n      display: block !important; } }\n  #optimole-app .is-flex {\n    display: -ms-flexbox !important;\n    display: flex !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-flex-mobile {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-flex-tablet {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-flex-tablet-only {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-flex-touch {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-flex-desktop {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-flex-desktop-only {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-flex-widescreen {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-flex-widescreen-only {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-flex-fullhd {\n      display: -ms-flexbox !important;\n      display: flex !important; } }\n  #optimole-app .is-inline {\n    display: inline !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-inline-mobile {\n      display: inline !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-inline-tablet {\n      display: inline !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-inline-tablet-only {\n      display: inline !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-inline-touch {\n      display: inline !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-inline-desktop {\n      display: inline !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-inline-desktop-only {\n      display: inline !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-inline-widescreen {\n      display: inline !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-inline-widescreen-only {\n      display: inline !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-inline-fullhd {\n      display: inline !important; } }\n  #optimole-app .is-inline-block {\n    display: inline-block !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-inline-block-mobile {\n      display: inline-block !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-inline-block-tablet {\n      display: inline-block !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-inline-block-tablet-only {\n      display: inline-block !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-inline-block-touch {\n      display: inline-block !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-inline-block-desktop {\n      display: inline-block !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-inline-block-desktop-only {\n      display: inline-block !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-inline-block-widescreen {\n      display: inline-block !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-inline-block-widescreen-only {\n      display: inline-block !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-inline-block-fullhd {\n      display: inline-block !important; } }\n  #optimole-app .is-inline-flex {\n    display: -ms-inline-flexbox !important;\n    display: inline-flex !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-inline-flex-mobile {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-inline-flex-tablet {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-inline-flex-tablet-only {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-inline-flex-touch {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-inline-flex-desktop {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-inline-flex-desktop-only {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-inline-flex-widescreen {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-inline-flex-widescreen-only {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-inline-flex-fullhd {\n      display: -ms-inline-flexbox !important;\n      display: inline-flex !important; } }\n  #optimole-app .is-hidden {\n    display: none !important; }\n  #optimole-app .is-sr-only {\n    border: none !important;\n    clip: rect(0, 0, 0, 0) !important;\n    height: 0.01em !important;\n    overflow: hidden !important;\n    padding: 0 !important;\n    position: absolute !important;\n    white-space: nowrap !important;\n    width: 0.01em !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-hidden-mobile {\n      display: none !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-hidden-tablet {\n      display: none !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-hidden-tablet-only {\n      display: none !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-hidden-touch {\n      display: none !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-hidden-desktop {\n      display: none !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-hidden-desktop-only {\n      display: none !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-hidden-widescreen {\n      display: none !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-hidden-widescreen-only {\n      display: none !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-hidden-fullhd {\n      display: none !important; } }\n  #optimole-app .is-invisible {\n    visibility: hidden !important; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .is-invisible-mobile {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .is-invisible-tablet {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 769px) and (max-width: 1087px) {\n    #optimole-app .is-invisible-tablet-only {\n      visibility: hidden !important; } }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .is-invisible-touch {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .is-invisible-desktop {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 1088px) and (max-width: 1279px) {\n    #optimole-app .is-invisible-desktop-only {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 1280px) {\n    #optimole-app .is-invisible-widescreen {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 1280px) and (max-width: 1471px) {\n    #optimole-app .is-invisible-widescreen-only {\n      visibility: hidden !important; } }\n  @media screen and (min-width: 1472px) {\n    #optimole-app .is-invisible-fullhd {\n      visibility: hidden !important; } }\n  #optimole-app .is-marginless {\n    margin: 0 !important; }\n  #optimole-app .is-paddingless {\n    padding: 0 !important; }\n  #optimole-app .is-radiusless {\n    border-radius: 0 !important; }\n  #optimole-app .is-shadowless {\n    box-shadow: none !important; }\n  #optimole-app .box {\n    background-color: white;\n    border-radius: 6px;\n    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);\n    color: #4a4a4a;\n    display: block;\n    padding: 1.25rem; }\n  #optimole-app a.box:hover, #optimole-app a.box:focus {\n    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px #3273dc; }\n  #optimole-app a.box:active {\n    box-shadow: inset 0 1px 2px rgba(10, 10, 10, 0.2), 0 0 0 1px #3273dc; }\n  #optimole-app .button {\n    background-color: white;\n    border-color: #dbdbdb;\n    border-width: 1px;\n    color: #363636;\n    cursor: pointer;\n    -ms-flex-pack: center;\n        justify-content: center;\n    padding-bottom: calc(0.375em - 1px);\n    padding-left: 0.75em;\n    padding-right: 0.75em;\n    padding-top: calc(0.375em - 1px);\n    text-align: center;\n    white-space: nowrap; }\n    #optimole-app .button strong {\n      color: inherit; }\n    #optimole-app .button .icon, #optimole-app .button .icon.is-small, #optimole-app .button .icon.is-medium, #optimole-app .button .icon.is-large {\n      height: 1.5em;\n      width: 1.5em; }\n    #optimole-app .button .icon:first-child:not(:last-child) {\n      margin-left: calc(-0.375em - 1px);\n      margin-right: 0.1875em; }\n    #optimole-app .button .icon:last-child:not(:first-child) {\n      margin-left: 0.1875em;\n      margin-right: calc(-0.375em - 1px); }\n    #optimole-app .button .icon:first-child:last-child {\n      margin-left: calc(-0.375em - 1px);\n      margin-right: calc(-0.375em - 1px); }\n    #optimole-app .button:hover, #optimole-app .button.is-hovered {\n      border-color: #b5b5b5;\n      color: #363636; }\n    #optimole-app .button:focus, #optimole-app .button.is-focused {\n      border-color: #3273dc;\n      color: #363636; }\n      #optimole-app .button:focus:not(:active), #optimole-app .button.is-focused:not(:active) {\n        box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n    #optimole-app .button:active, #optimole-app .button.is-active {\n      border-color: #4a4a4a;\n      color: #363636; }\n    #optimole-app .button.is-text {\n      background-color: transparent;\n      border-color: transparent;\n      color: #4a4a4a;\n      text-decoration: underline; }\n      #optimole-app .button.is-text:hover, #optimole-app .button.is-text.is-hovered, #optimole-app .button.is-text:focus, #optimole-app .button.is-text.is-focused {\n        background-color: whitesmoke;\n        color: #363636; }\n      #optimole-app .button.is-text:active, #optimole-app .button.is-text.is-active {\n        background-color: #e8e8e8;\n        color: #363636; }\n      #optimole-app .button.is-text[disabled],\n      fieldset[disabled] #optimole-app .button.is-text {\n        background-color: transparent;\n        border-color: transparent;\n        box-shadow: none; }\n    #optimole-app .button.is-white {\n      background-color: white;\n      border-color: transparent;\n      color: #0a0a0a; }\n      #optimole-app .button.is-white:hover, #optimole-app .button.is-white.is-hovered {\n        background-color: #f9f9f9;\n        border-color: transparent;\n        color: #0a0a0a; }\n      #optimole-app .button.is-white:focus, #optimole-app .button.is-white.is-focused {\n        border-color: transparent;\n        color: #0a0a0a; }\n        #optimole-app .button.is-white:focus:not(:active), #optimole-app .button.is-white.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(255, 255, 255, 0.25); }\n      #optimole-app .button.is-white:active, #optimole-app .button.is-white.is-active {\n        background-color: #f2f2f2;\n        border-color: transparent;\n        color: #0a0a0a; }\n      #optimole-app .button.is-white[disabled],\n      fieldset[disabled] #optimole-app .button.is-white {\n        background-color: white;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-white.is-inverted {\n        background-color: #0a0a0a;\n        color: white; }\n        #optimole-app .button.is-white.is-inverted:hover {\n          background-color: black; }\n        #optimole-app .button.is-white.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-white.is-inverted {\n          background-color: #0a0a0a;\n          border-color: transparent;\n          box-shadow: none;\n          color: white; }\n      #optimole-app .button.is-white.is-loading::after {\n        border-color: transparent transparent #0a0a0a #0a0a0a !important; }\n      #optimole-app .button.is-white.is-outlined {\n        background-color: transparent;\n        border-color: white;\n        color: white; }\n        #optimole-app .button.is-white.is-outlined:hover, #optimole-app .button.is-white.is-outlined:focus {\n          background-color: white;\n          border-color: white;\n          color: #0a0a0a; }\n        #optimole-app .button.is-white.is-outlined.is-loading::after {\n          border-color: transparent transparent white white !important; }\n        #optimole-app .button.is-white.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-white.is-outlined {\n          background-color: transparent;\n          border-color: white;\n          box-shadow: none;\n          color: white; }\n      #optimole-app .button.is-white.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #0a0a0a;\n        color: #0a0a0a; }\n        #optimole-app .button.is-white.is-inverted.is-outlined:hover, #optimole-app .button.is-white.is-inverted.is-outlined:focus {\n          background-color: #0a0a0a;\n          color: white; }\n        #optimole-app .button.is-white.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-white.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #0a0a0a;\n          box-shadow: none;\n          color: #0a0a0a; }\n    #optimole-app .button.is-black {\n      background-color: #0a0a0a;\n      border-color: transparent;\n      color: white; }\n      #optimole-app .button.is-black:hover, #optimole-app .button.is-black.is-hovered {\n        background-color: #040404;\n        border-color: transparent;\n        color: white; }\n      #optimole-app .button.is-black:focus, #optimole-app .button.is-black.is-focused {\n        border-color: transparent;\n        color: white; }\n        #optimole-app .button.is-black:focus:not(:active), #optimole-app .button.is-black.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(10, 10, 10, 0.25); }\n      #optimole-app .button.is-black:active, #optimole-app .button.is-black.is-active {\n        background-color: black;\n        border-color: transparent;\n        color: white; }\n      #optimole-app .button.is-black[disabled],\n      fieldset[disabled] #optimole-app .button.is-black {\n        background-color: #0a0a0a;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-black.is-inverted {\n        background-color: white;\n        color: #0a0a0a; }\n        #optimole-app .button.is-black.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-black.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-black.is-inverted {\n          background-color: white;\n          border-color: transparent;\n          box-shadow: none;\n          color: #0a0a0a; }\n      #optimole-app .button.is-black.is-loading::after {\n        border-color: transparent transparent white white !important; }\n      #optimole-app .button.is-black.is-outlined {\n        background-color: transparent;\n        border-color: #0a0a0a;\n        color: #0a0a0a; }\n        #optimole-app .button.is-black.is-outlined:hover, #optimole-app .button.is-black.is-outlined:focus {\n          background-color: #0a0a0a;\n          border-color: #0a0a0a;\n          color: white; }\n        #optimole-app .button.is-black.is-outlined.is-loading::after {\n          border-color: transparent transparent #0a0a0a #0a0a0a !important; }\n        #optimole-app .button.is-black.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-black.is-outlined {\n          background-color: transparent;\n          border-color: #0a0a0a;\n          box-shadow: none;\n          color: #0a0a0a; }\n      #optimole-app .button.is-black.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: white;\n        color: white; }\n        #optimole-app .button.is-black.is-inverted.is-outlined:hover, #optimole-app .button.is-black.is-inverted.is-outlined:focus {\n          background-color: white;\n          color: #0a0a0a; }\n        #optimole-app .button.is-black.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-black.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: white;\n          box-shadow: none;\n          color: white; }\n    #optimole-app .button.is-light {\n      background-color: whitesmoke;\n      border-color: transparent;\n      color: #363636; }\n      #optimole-app .button.is-light:hover, #optimole-app .button.is-light.is-hovered {\n        background-color: #eeeeee;\n        border-color: transparent;\n        color: #363636; }\n      #optimole-app .button.is-light:focus, #optimole-app .button.is-light.is-focused {\n        border-color: transparent;\n        color: #363636; }\n        #optimole-app .button.is-light:focus:not(:active), #optimole-app .button.is-light.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(245, 245, 245, 0.25); }\n      #optimole-app .button.is-light:active, #optimole-app .button.is-light.is-active {\n        background-color: #e8e8e8;\n        border-color: transparent;\n        color: #363636; }\n      #optimole-app .button.is-light[disabled],\n      fieldset[disabled] #optimole-app .button.is-light {\n        background-color: whitesmoke;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-light.is-inverted {\n        background-color: #363636;\n        color: whitesmoke; }\n        #optimole-app .button.is-light.is-inverted:hover {\n          background-color: #292929; }\n        #optimole-app .button.is-light.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-light.is-inverted {\n          background-color: #363636;\n          border-color: transparent;\n          box-shadow: none;\n          color: whitesmoke; }\n      #optimole-app .button.is-light.is-loading::after {\n        border-color: transparent transparent #363636 #363636 !important; }\n      #optimole-app .button.is-light.is-outlined {\n        background-color: transparent;\n        border-color: whitesmoke;\n        color: whitesmoke; }\n        #optimole-app .button.is-light.is-outlined:hover, #optimole-app .button.is-light.is-outlined:focus {\n          background-color: whitesmoke;\n          border-color: whitesmoke;\n          color: #363636; }\n        #optimole-app .button.is-light.is-outlined.is-loading::after {\n          border-color: transparent transparent whitesmoke whitesmoke !important; }\n        #optimole-app .button.is-light.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-light.is-outlined {\n          background-color: transparent;\n          border-color: whitesmoke;\n          box-shadow: none;\n          color: whitesmoke; }\n      #optimole-app .button.is-light.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #363636;\n        color: #363636; }\n        #optimole-app .button.is-light.is-inverted.is-outlined:hover, #optimole-app .button.is-light.is-inverted.is-outlined:focus {\n          background-color: #363636;\n          color: whitesmoke; }\n        #optimole-app .button.is-light.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-light.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #363636;\n          box-shadow: none;\n          color: #363636; }\n    #optimole-app .button.is-dark {\n      background-color: #363636;\n      border-color: transparent;\n      color: whitesmoke; }\n      #optimole-app .button.is-dark:hover, #optimole-app .button.is-dark.is-hovered {\n        background-color: #2f2f2f;\n        border-color: transparent;\n        color: whitesmoke; }\n      #optimole-app .button.is-dark:focus, #optimole-app .button.is-dark.is-focused {\n        border-color: transparent;\n        color: whitesmoke; }\n        #optimole-app .button.is-dark:focus:not(:active), #optimole-app .button.is-dark.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(54, 54, 54, 0.25); }\n      #optimole-app .button.is-dark:active, #optimole-app .button.is-dark.is-active {\n        background-color: #292929;\n        border-color: transparent;\n        color: whitesmoke; }\n      #optimole-app .button.is-dark[disabled],\n      fieldset[disabled] #optimole-app .button.is-dark {\n        background-color: #363636;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-dark.is-inverted {\n        background-color: whitesmoke;\n        color: #363636; }\n        #optimole-app .button.is-dark.is-inverted:hover {\n          background-color: #e8e8e8; }\n        #optimole-app .button.is-dark.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-dark.is-inverted {\n          background-color: whitesmoke;\n          border-color: transparent;\n          box-shadow: none;\n          color: #363636; }\n      #optimole-app .button.is-dark.is-loading::after {\n        border-color: transparent transparent whitesmoke whitesmoke !important; }\n      #optimole-app .button.is-dark.is-outlined {\n        background-color: transparent;\n        border-color: #363636;\n        color: #363636; }\n        #optimole-app .button.is-dark.is-outlined:hover, #optimole-app .button.is-dark.is-outlined:focus {\n          background-color: #363636;\n          border-color: #363636;\n          color: whitesmoke; }\n        #optimole-app .button.is-dark.is-outlined.is-loading::after {\n          border-color: transparent transparent #363636 #363636 !important; }\n        #optimole-app .button.is-dark.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-dark.is-outlined {\n          background-color: transparent;\n          border-color: #363636;\n          box-shadow: none;\n          color: #363636; }\n      #optimole-app .button.is-dark.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: whitesmoke;\n        color: whitesmoke; }\n        #optimole-app .button.is-dark.is-inverted.is-outlined:hover, #optimole-app .button.is-dark.is-inverted.is-outlined:focus {\n          background-color: whitesmoke;\n          color: #363636; }\n        #optimole-app .button.is-dark.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-dark.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: whitesmoke;\n          box-shadow: none;\n          color: whitesmoke; }\n    #optimole-app .button.is-primary {\n      background-color: #EF686B;\n      border-color: transparent;\n      color: #fff; }\n      #optimole-app .button.is-primary:hover, #optimole-app .button.is-primary.is-hovered {\n        background-color: #ee5c60;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-primary:focus, #optimole-app .button.is-primary.is-focused {\n        border-color: transparent;\n        color: #fff; }\n        #optimole-app .button.is-primary:focus:not(:active), #optimole-app .button.is-primary.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(239, 104, 107, 0.25); }\n      #optimole-app .button.is-primary:active, #optimole-app .button.is-primary.is-active {\n        background-color: #ed5154;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-primary[disabled],\n      fieldset[disabled] #optimole-app .button.is-primary {\n        background-color: #EF686B;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-primary.is-inverted {\n        background-color: #fff;\n        color: #EF686B; }\n        #optimole-app .button.is-primary.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-primary.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-primary.is-inverted {\n          background-color: #fff;\n          border-color: transparent;\n          box-shadow: none;\n          color: #EF686B; }\n      #optimole-app .button.is-primary.is-loading::after {\n        border-color: transparent transparent #fff #fff !important; }\n      #optimole-app .button.is-primary.is-outlined {\n        background-color: transparent;\n        border-color: #EF686B;\n        color: #EF686B; }\n        #optimole-app .button.is-primary.is-outlined:hover, #optimole-app .button.is-primary.is-outlined:focus {\n          background-color: #EF686B;\n          border-color: #EF686B;\n          color: #fff; }\n        #optimole-app .button.is-primary.is-outlined.is-loading::after {\n          border-color: transparent transparent #EF686B #EF686B !important; }\n        #optimole-app .button.is-primary.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-primary.is-outlined {\n          background-color: transparent;\n          border-color: #EF686B;\n          box-shadow: none;\n          color: #EF686B; }\n      #optimole-app .button.is-primary.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #fff;\n        color: #fff; }\n        #optimole-app .button.is-primary.is-inverted.is-outlined:hover, #optimole-app .button.is-primary.is-inverted.is-outlined:focus {\n          background-color: #fff;\n          color: #EF686B; }\n        #optimole-app .button.is-primary.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-primary.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #fff;\n          box-shadow: none;\n          color: #fff; }\n    #optimole-app .button.is-link {\n      background-color: #3273dc;\n      border-color: transparent;\n      color: #fff; }\n      #optimole-app .button.is-link:hover, #optimole-app .button.is-link.is-hovered {\n        background-color: #276cda;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-link:focus, #optimole-app .button.is-link.is-focused {\n        border-color: transparent;\n        color: #fff; }\n        #optimole-app .button.is-link:focus:not(:active), #optimole-app .button.is-link.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n      #optimole-app .button.is-link:active, #optimole-app .button.is-link.is-active {\n        background-color: #2366d1;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-link[disabled],\n      fieldset[disabled] #optimole-app .button.is-link {\n        background-color: #3273dc;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-link.is-inverted {\n        background-color: #fff;\n        color: #3273dc; }\n        #optimole-app .button.is-link.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-link.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-link.is-inverted {\n          background-color: #fff;\n          border-color: transparent;\n          box-shadow: none;\n          color: #3273dc; }\n      #optimole-app .button.is-link.is-loading::after {\n        border-color: transparent transparent #fff #fff !important; }\n      #optimole-app .button.is-link.is-outlined {\n        background-color: transparent;\n        border-color: #3273dc;\n        color: #3273dc; }\n        #optimole-app .button.is-link.is-outlined:hover, #optimole-app .button.is-link.is-outlined:focus {\n          background-color: #3273dc;\n          border-color: #3273dc;\n          color: #fff; }\n        #optimole-app .button.is-link.is-outlined.is-loading::after {\n          border-color: transparent transparent #3273dc #3273dc !important; }\n        #optimole-app .button.is-link.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-link.is-outlined {\n          background-color: transparent;\n          border-color: #3273dc;\n          box-shadow: none;\n          color: #3273dc; }\n      #optimole-app .button.is-link.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #fff;\n        color: #fff; }\n        #optimole-app .button.is-link.is-inverted.is-outlined:hover, #optimole-app .button.is-link.is-inverted.is-outlined:focus {\n          background-color: #fff;\n          color: #3273dc; }\n        #optimole-app .button.is-link.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-link.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #fff;\n          box-shadow: none;\n          color: #fff; }\n    #optimole-app .button.is-info {\n      background-color: #5180C1;\n      border-color: transparent;\n      color: #fff; }\n      #optimole-app .button.is-info:hover, #optimole-app .button.is-info.is-hovered {\n        background-color: #4879be;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-info:focus, #optimole-app .button.is-info.is-focused {\n        border-color: transparent;\n        color: #fff; }\n        #optimole-app .button.is-info:focus:not(:active), #optimole-app .button.is-info.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(81, 128, 193, 0.25); }\n      #optimole-app .button.is-info:active, #optimole-app .button.is-info.is-active {\n        background-color: #4173b7;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-info[disabled],\n      fieldset[disabled] #optimole-app .button.is-info {\n        background-color: #5180C1;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-info.is-inverted {\n        background-color: #fff;\n        color: #5180C1; }\n        #optimole-app .button.is-info.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-info.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-info.is-inverted {\n          background-color: #fff;\n          border-color: transparent;\n          box-shadow: none;\n          color: #5180C1; }\n      #optimole-app .button.is-info.is-loading::after {\n        border-color: transparent transparent #fff #fff !important; }\n      #optimole-app .button.is-info.is-outlined {\n        background-color: transparent;\n        border-color: #5180C1;\n        color: #5180C1; }\n        #optimole-app .button.is-info.is-outlined:hover, #optimole-app .button.is-info.is-outlined:focus {\n          background-color: #5180C1;\n          border-color: #5180C1;\n          color: #fff; }\n        #optimole-app .button.is-info.is-outlined.is-loading::after {\n          border-color: transparent transparent #5180C1 #5180C1 !important; }\n        #optimole-app .button.is-info.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-info.is-outlined {\n          background-color: transparent;\n          border-color: #5180C1;\n          box-shadow: none;\n          color: #5180C1; }\n      #optimole-app .button.is-info.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #fff;\n        color: #fff; }\n        #optimole-app .button.is-info.is-inverted.is-outlined:hover, #optimole-app .button.is-info.is-inverted.is-outlined:focus {\n          background-color: #fff;\n          color: #5180C1; }\n        #optimole-app .button.is-info.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-info.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #fff;\n          box-shadow: none;\n          color: #fff; }\n    #optimole-app .button.is-success {\n      background-color: #34a85e;\n      border-color: transparent;\n      color: #fff; }\n      #optimole-app .button.is-success:hover, #optimole-app .button.is-success.is-hovered {\n        background-color: #319e59;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-success:focus, #optimole-app .button.is-success.is-focused {\n        border-color: transparent;\n        color: #fff; }\n        #optimole-app .button.is-success:focus:not(:active), #optimole-app .button.is-success.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(52, 168, 94, 0.25); }\n      #optimole-app .button.is-success:active, #optimole-app .button.is-success.is-active {\n        background-color: #2e9553;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-success[disabled],\n      fieldset[disabled] #optimole-app .button.is-success {\n        background-color: #34a85e;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-success.is-inverted {\n        background-color: #fff;\n        color: #34a85e; }\n        #optimole-app .button.is-success.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-success.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-success.is-inverted {\n          background-color: #fff;\n          border-color: transparent;\n          box-shadow: none;\n          color: #34a85e; }\n      #optimole-app .button.is-success.is-loading::after {\n        border-color: transparent transparent #fff #fff !important; }\n      #optimole-app .button.is-success.is-outlined {\n        background-color: transparent;\n        border-color: #34a85e;\n        color: #34a85e; }\n        #optimole-app .button.is-success.is-outlined:hover, #optimole-app .button.is-success.is-outlined:focus {\n          background-color: #34a85e;\n          border-color: #34a85e;\n          color: #fff; }\n        #optimole-app .button.is-success.is-outlined.is-loading::after {\n          border-color: transparent transparent #34a85e #34a85e !important; }\n        #optimole-app .button.is-success.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-success.is-outlined {\n          background-color: transparent;\n          border-color: #34a85e;\n          box-shadow: none;\n          color: #34a85e; }\n      #optimole-app .button.is-success.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #fff;\n        color: #fff; }\n        #optimole-app .button.is-success.is-inverted.is-outlined:hover, #optimole-app .button.is-success.is-inverted.is-outlined:focus {\n          background-color: #fff;\n          color: #34a85e; }\n        #optimole-app .button.is-success.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-success.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #fff;\n          box-shadow: none;\n          color: #fff; }\n    #optimole-app .button.is-warning {\n      background-color: #ffdd57;\n      border-color: transparent;\n      color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .button.is-warning:hover, #optimole-app .button.is-warning.is-hovered {\n        background-color: #ffdb4a;\n        border-color: transparent;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .button.is-warning:focus, #optimole-app .button.is-warning.is-focused {\n        border-color: transparent;\n        color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .button.is-warning:focus:not(:active), #optimole-app .button.is-warning.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(255, 221, 87, 0.25); }\n      #optimole-app .button.is-warning:active, #optimole-app .button.is-warning.is-active {\n        background-color: #ffd83d;\n        border-color: transparent;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .button.is-warning[disabled],\n      fieldset[disabled] #optimole-app .button.is-warning {\n        background-color: #ffdd57;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-warning.is-inverted {\n        background-color: rgba(0, 0, 0, 0.7);\n        color: #ffdd57; }\n        #optimole-app .button.is-warning.is-inverted:hover {\n          background-color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .button.is-warning.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-warning.is-inverted {\n          background-color: rgba(0, 0, 0, 0.7);\n          border-color: transparent;\n          box-shadow: none;\n          color: #ffdd57; }\n      #optimole-app .button.is-warning.is-loading::after {\n        border-color: transparent transparent rgba(0, 0, 0, 0.7) rgba(0, 0, 0, 0.7) !important; }\n      #optimole-app .button.is-warning.is-outlined {\n        background-color: transparent;\n        border-color: #ffdd57;\n        color: #ffdd57; }\n        #optimole-app .button.is-warning.is-outlined:hover, #optimole-app .button.is-warning.is-outlined:focus {\n          background-color: #ffdd57;\n          border-color: #ffdd57;\n          color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .button.is-warning.is-outlined.is-loading::after {\n          border-color: transparent transparent #ffdd57 #ffdd57 !important; }\n        #optimole-app .button.is-warning.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-warning.is-outlined {\n          background-color: transparent;\n          border-color: #ffdd57;\n          box-shadow: none;\n          color: #ffdd57; }\n      #optimole-app .button.is-warning.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: rgba(0, 0, 0, 0.7);\n        color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .button.is-warning.is-inverted.is-outlined:hover, #optimole-app .button.is-warning.is-inverted.is-outlined:focus {\n          background-color: rgba(0, 0, 0, 0.7);\n          color: #ffdd57; }\n        #optimole-app .button.is-warning.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-warning.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: rgba(0, 0, 0, 0.7);\n          box-shadow: none;\n          color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .button.is-danger {\n      background-color: #D54222;\n      border-color: transparent;\n      color: #fff; }\n      #optimole-app .button.is-danger:hover, #optimole-app .button.is-danger.is-hovered {\n        background-color: #ca3f20;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-danger:focus, #optimole-app .button.is-danger.is-focused {\n        border-color: transparent;\n        color: #fff; }\n        #optimole-app .button.is-danger:focus:not(:active), #optimole-app .button.is-danger.is-focused:not(:active) {\n          box-shadow: 0 0 0 0.125em rgba(213, 66, 34, 0.25); }\n      #optimole-app .button.is-danger:active, #optimole-app .button.is-danger.is-active {\n        background-color: #bf3b1e;\n        border-color: transparent;\n        color: #fff; }\n      #optimole-app .button.is-danger[disabled],\n      fieldset[disabled] #optimole-app .button.is-danger {\n        background-color: #D54222;\n        border-color: transparent;\n        box-shadow: none; }\n      #optimole-app .button.is-danger.is-inverted {\n        background-color: #fff;\n        color: #D54222; }\n        #optimole-app .button.is-danger.is-inverted:hover {\n          background-color: #f2f2f2; }\n        #optimole-app .button.is-danger.is-inverted[disabled],\n        fieldset[disabled] #optimole-app .button.is-danger.is-inverted {\n          background-color: #fff;\n          border-color: transparent;\n          box-shadow: none;\n          color: #D54222; }\n      #optimole-app .button.is-danger.is-loading::after {\n        border-color: transparent transparent #fff #fff !important; }\n      #optimole-app .button.is-danger.is-outlined {\n        background-color: transparent;\n        border-color: #D54222;\n        color: #D54222; }\n        #optimole-app .button.is-danger.is-outlined:hover, #optimole-app .button.is-danger.is-outlined:focus {\n          background-color: #D54222;\n          border-color: #D54222;\n          color: #fff; }\n        #optimole-app .button.is-danger.is-outlined.is-loading::after {\n          border-color: transparent transparent #D54222 #D54222 !important; }\n        #optimole-app .button.is-danger.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-danger.is-outlined {\n          background-color: transparent;\n          border-color: #D54222;\n          box-shadow: none;\n          color: #D54222; }\n      #optimole-app .button.is-danger.is-inverted.is-outlined {\n        background-color: transparent;\n        border-color: #fff;\n        color: #fff; }\n        #optimole-app .button.is-danger.is-inverted.is-outlined:hover, #optimole-app .button.is-danger.is-inverted.is-outlined:focus {\n          background-color: #fff;\n          color: #D54222; }\n        #optimole-app .button.is-danger.is-inverted.is-outlined[disabled],\n        fieldset[disabled] #optimole-app .button.is-danger.is-inverted.is-outlined {\n          background-color: transparent;\n          border-color: #fff;\n          box-shadow: none;\n          color: #fff; }\n    #optimole-app .button.is-small {\n      border-radius: 2px;\n      font-size: 0.75rem; }\n    #optimole-app .button.is-normal {\n      font-size: 1rem; }\n    #optimole-app .button.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .button.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .button[disabled],\n    fieldset[disabled] #optimole-app .button {\n      background-color: white;\n      border-color: #dbdbdb;\n      box-shadow: none;\n      opacity: 0.5; }\n    #optimole-app .button.is-fullwidth {\n      display: -ms-flexbox;\n      display: flex;\n      width: 100%; }\n    #optimole-app .button.is-loading {\n      color: transparent !important;\n      pointer-events: none; }\n      #optimole-app .button.is-loading::after {\n        position: absolute;\n        left: calc(50% - (1em / 2));\n        top: calc(50% - (1em / 2));\n        position: absolute !important; }\n    #optimole-app .button.is-static {\n      background-color: whitesmoke;\n      border-color: #dbdbdb;\n      color: #7a7a7a;\n      box-shadow: none;\n      pointer-events: none; }\n    #optimole-app .button.is-rounded {\n      border-radius: 290486px;\n      padding-left: 1em;\n      padding-right: 1em; }\n  #optimole-app .buttons {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-wrap: wrap;\n        flex-wrap: wrap;\n    -ms-flex-pack: start;\n        justify-content: flex-start; }\n    #optimole-app .buttons .button {\n      margin-bottom: 0.5rem; }\n      #optimole-app .buttons .button:not(:last-child):not(.is-fullwidth) {\n        margin-right: 0.5rem; }\n    #optimole-app .buttons:last-child {\n      margin-bottom: -0.5rem; }\n    #optimole-app .buttons:not(:last-child) {\n      margin-bottom: 1rem; }\n    #optimole-app .buttons.are-small .button:not(.is-normal):not(.is-medium):not(.is-large) {\n      border-radius: 2px;\n      font-size: 0.75rem; }\n    #optimole-app .buttons.are-medium .button:not(.is-small):not(.is-normal):not(.is-large) {\n      font-size: 1.25rem; }\n    #optimole-app .buttons.are-large .button:not(.is-small):not(.is-normal):not(.is-medium) {\n      font-size: 1.5rem; }\n    #optimole-app .buttons.has-addons .button:not(:first-child) {\n      border-bottom-left-radius: 0;\n      border-top-left-radius: 0; }\n    #optimole-app .buttons.has-addons .button:not(:last-child) {\n      border-bottom-right-radius: 0;\n      border-top-right-radius: 0;\n      margin-right: -1px; }\n    #optimole-app .buttons.has-addons .button:last-child {\n      margin-right: 0; }\n    #optimole-app .buttons.has-addons .button:hover, #optimole-app .buttons.has-addons .button.is-hovered {\n      z-index: 2; }\n    #optimole-app .buttons.has-addons .button:focus, #optimole-app .buttons.has-addons .button.is-focused, #optimole-app .buttons.has-addons .button:active, #optimole-app .buttons.has-addons .button.is-active, #optimole-app .buttons.has-addons .button.is-selected {\n      z-index: 3; }\n      #optimole-app .buttons.has-addons .button:focus:hover, #optimole-app .buttons.has-addons .button.is-focused:hover, #optimole-app .buttons.has-addons .button:active:hover, #optimole-app .buttons.has-addons .button.is-active:hover, #optimole-app .buttons.has-addons .button.is-selected:hover {\n        z-index: 4; }\n    #optimole-app .buttons.has-addons .button.is-expanded {\n      -ms-flex-positive: 1;\n          flex-grow: 1; }\n    #optimole-app .buttons.is-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .buttons.is-right {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n  #optimole-app .container {\n    margin: 0 auto;\n    position: relative; }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .container {\n        max-width: 960px;\n        width: 960px; }\n        #optimole-app .container.is-fluid {\n          margin-left: 64px;\n          margin-right: 64px;\n          max-width: none;\n          width: auto; } }\n    @media screen and (max-width: 1279px) {\n      #optimole-app .container.is-widescreen {\n        max-width: 1152px;\n        width: auto; } }\n    @media screen and (max-width: 1471px) {\n      #optimole-app .container.is-fullhd {\n        max-width: 1344px;\n        width: auto; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .container {\n        max-width: 1152px;\n        width: 1152px; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .container {\n        max-width: 1344px;\n        width: 1344px; } }\n  #optimole-app .content li + li {\n    margin-top: 0.25em; }\n  #optimole-app .content p:not(:last-child),\n  #optimole-app .content dl:not(:last-child),\n  #optimole-app .content ol:not(:last-child),\n  #optimole-app .content ul:not(:last-child),\n  #optimole-app .content blockquote:not(:last-child),\n  #optimole-app .content pre:not(:last-child),\n  #optimole-app .content table:not(:last-child) {\n    margin-bottom: 1em; }\n  #optimole-app .content h1,\n  #optimole-app .content h2,\n  #optimole-app .content h3,\n  #optimole-app .content h4,\n  #optimole-app .content h5,\n  #optimole-app .content h6 {\n    color: #363636;\n    font-weight: 600;\n    line-height: 1.125; }\n  #optimole-app .content h1 {\n    font-size: 2em;\n    margin-bottom: 0.5em; }\n    #optimole-app .content h1:not(:first-child) {\n      margin-top: 1em; }\n  #optimole-app .content h2 {\n    font-size: 1.75em;\n    margin-bottom: 0.5714em; }\n    #optimole-app .content h2:not(:first-child) {\n      margin-top: 1.1428em; }\n  #optimole-app .content h3 {\n    font-size: 1.5em;\n    margin-bottom: 0.6666em; }\n    #optimole-app .content h3:not(:first-child) {\n      margin-top: 1.3333em; }\n  #optimole-app .content h4 {\n    font-size: 1.25em;\n    margin-bottom: 0.8em; }\n  #optimole-app .content h5 {\n    font-size: 1.125em;\n    margin-bottom: 0.8888em; }\n  #optimole-app .content h6 {\n    font-size: 1em;\n    margin-bottom: 1em; }\n  #optimole-app .content blockquote {\n    background-color: whitesmoke;\n    border-left: 5px solid #dbdbdb;\n    padding: 1.25em 1.5em; }\n  #optimole-app .content ol {\n    list-style-position: outside;\n    margin-left: 2em;\n    margin-top: 1em; }\n    #optimole-app .content ol:not([type]) {\n      list-style-type: decimal; }\n      #optimole-app .content ol:not([type]).is-lower-alpha {\n        list-style-type: lower-alpha; }\n      #optimole-app .content ol:not([type]).is-lower-roman {\n        list-style-type: lower-roman; }\n      #optimole-app .content ol:not([type]).is-upper-alpha {\n        list-style-type: upper-alpha; }\n      #optimole-app .content ol:not([type]).is-upper-roman {\n        list-style-type: upper-roman; }\n  #optimole-app .content ul {\n    list-style: disc outside;\n    margin-left: 2em;\n    margin-top: 1em; }\n    #optimole-app .content ul ul {\n      list-style-type: circle;\n      margin-top: 0.5em; }\n      #optimole-app .content ul ul ul {\n        list-style-type: square; }\n  #optimole-app .content dd {\n    margin-left: 2em; }\n  #optimole-app .content figure {\n    margin-left: 2em;\n    margin-right: 2em;\n    text-align: center; }\n    #optimole-app .content figure:not(:first-child) {\n      margin-top: 2em; }\n    #optimole-app .content figure:not(:last-child) {\n      margin-bottom: 2em; }\n    #optimole-app .content figure img {\n      display: inline-block; }\n    #optimole-app .content figure figcaption {\n      font-style: italic; }\n  #optimole-app .content pre {\n    -webkit-overflow-scrolling: touch;\n    overflow-x: auto;\n    padding: 1.25em 1.5em;\n    white-space: pre;\n    word-wrap: normal; }\n  #optimole-app .content sup,\n  #optimole-app .content sub {\n    font-size: 75%; }\n  #optimole-app .content table {\n    width: 100%; }\n    #optimole-app .content table td,\n    #optimole-app .content table th {\n      border: 1px solid #dbdbdb;\n      border-width: 0 0 1px;\n      padding: 0.5em 0.75em;\n      vertical-align: top; }\n    #optimole-app .content table th {\n      color: #363636;\n      text-align: left; }\n    #optimole-app .content table thead td,\n    #optimole-app .content table thead th {\n      border-width: 0 0 2px;\n      color: #363636; }\n    #optimole-app .content table tfoot td,\n    #optimole-app .content table tfoot th {\n      border-width: 2px 0 0;\n      color: #363636; }\n    #optimole-app .content table tbody tr:last-child td,\n    #optimole-app .content table tbody tr:last-child th {\n      border-bottom-width: 0; }\n  #optimole-app .content.is-small {\n    font-size: 0.75rem; }\n  #optimole-app .content.is-medium {\n    font-size: 1.25rem; }\n  #optimole-app .content.is-large {\n    font-size: 1.5rem; }\n  #optimole-app .input,\n  #optimole-app .textarea {\n    background-color: white;\n    border-color: #dbdbdb;\n    color: #363636;\n    box-shadow: inset 0 1px 2px rgba(10, 10, 10, 0.1);\n    max-width: 100%;\n    width: 100%; }\n    #optimole-app .input::-moz-placeholder,\n    #optimole-app .textarea::-moz-placeholder {\n      color: rgba(54, 54, 54, 0.3); }\n    #optimole-app .input::-webkit-input-placeholder,\n    #optimole-app .textarea::-webkit-input-placeholder {\n      color: rgba(54, 54, 54, 0.3); }\n    #optimole-app .input:-moz-placeholder,\n    #optimole-app .textarea:-moz-placeholder {\n      color: rgba(54, 54, 54, 0.3); }\n    #optimole-app .input:-ms-input-placeholder,\n    #optimole-app .textarea:-ms-input-placeholder {\n      color: rgba(54, 54, 54, 0.3); }\n    #optimole-app .input:hover, #optimole-app .input.is-hovered,\n    #optimole-app .textarea:hover,\n    #optimole-app .textarea.is-hovered {\n      border-color: #b5b5b5; }\n    #optimole-app .input:focus, #optimole-app .input.is-focused, #optimole-app .input:active, #optimole-app .input.is-active,\n    #optimole-app .textarea:focus,\n    #optimole-app .textarea.is-focused,\n    #optimole-app .textarea:active,\n    #optimole-app .textarea.is-active {\n      border-color: #3273dc;\n      box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n    #optimole-app .input[disabled],\n    fieldset[disabled] #optimole-app .input,\n    #optimole-app .textarea[disabled],\n    fieldset[disabled]\n    #optimole-app .textarea {\n      background-color: whitesmoke;\n      border-color: whitesmoke;\n      box-shadow: none;\n      color: #7a7a7a; }\n      #optimole-app .input[disabled]::-moz-placeholder,\n      fieldset[disabled] #optimole-app .input::-moz-placeholder,\n      #optimole-app .textarea[disabled]::-moz-placeholder,\n      fieldset[disabled]\n      #optimole-app .textarea::-moz-placeholder {\n        color: rgba(122, 122, 122, 0.3); }\n      #optimole-app .input[disabled]::-webkit-input-placeholder,\n      fieldset[disabled] #optimole-app .input::-webkit-input-placeholder,\n      #optimole-app .textarea[disabled]::-webkit-input-placeholder,\n      fieldset[disabled]\n      #optimole-app .textarea::-webkit-input-placeholder {\n        color: rgba(122, 122, 122, 0.3); }\n      #optimole-app .input[disabled]:-moz-placeholder,\n      fieldset[disabled] #optimole-app .input:-moz-placeholder,\n      #optimole-app .textarea[disabled]:-moz-placeholder,\n      fieldset[disabled]\n      #optimole-app .textarea:-moz-placeholder {\n        color: rgba(122, 122, 122, 0.3); }\n      #optimole-app .input[disabled]:-ms-input-placeholder,\n      fieldset[disabled] #optimole-app .input:-ms-input-placeholder,\n      #optimole-app .textarea[disabled]:-ms-input-placeholder,\n      fieldset[disabled]\n      #optimole-app .textarea:-ms-input-placeholder {\n        color: rgba(122, 122, 122, 0.3); }\n    #optimole-app .input[readonly],\n    #optimole-app .textarea[readonly] {\n      box-shadow: none; }\n    #optimole-app .input.is-white,\n    #optimole-app .textarea.is-white {\n      border-color: white; }\n      #optimole-app .input.is-white:focus, #optimole-app .input.is-white.is-focused, #optimole-app .input.is-white:active, #optimole-app .input.is-white.is-active,\n      #optimole-app .textarea.is-white:focus,\n      #optimole-app .textarea.is-white.is-focused,\n      #optimole-app .textarea.is-white:active,\n      #optimole-app .textarea.is-white.is-active {\n        box-shadow: 0 0 0 0.125em rgba(255, 255, 255, 0.25); }\n    #optimole-app .input.is-black,\n    #optimole-app .textarea.is-black {\n      border-color: #0a0a0a; }\n      #optimole-app .input.is-black:focus, #optimole-app .input.is-black.is-focused, #optimole-app .input.is-black:active, #optimole-app .input.is-black.is-active,\n      #optimole-app .textarea.is-black:focus,\n      #optimole-app .textarea.is-black.is-focused,\n      #optimole-app .textarea.is-black:active,\n      #optimole-app .textarea.is-black.is-active {\n        box-shadow: 0 0 0 0.125em rgba(10, 10, 10, 0.25); }\n    #optimole-app .input.is-light,\n    #optimole-app .textarea.is-light {\n      border-color: whitesmoke; }\n      #optimole-app .input.is-light:focus, #optimole-app .input.is-light.is-focused, #optimole-app .input.is-light:active, #optimole-app .input.is-light.is-active,\n      #optimole-app .textarea.is-light:focus,\n      #optimole-app .textarea.is-light.is-focused,\n      #optimole-app .textarea.is-light:active,\n      #optimole-app .textarea.is-light.is-active {\n        box-shadow: 0 0 0 0.125em rgba(245, 245, 245, 0.25); }\n    #optimole-app .input.is-dark,\n    #optimole-app .textarea.is-dark {\n      border-color: #363636; }\n      #optimole-app .input.is-dark:focus, #optimole-app .input.is-dark.is-focused, #optimole-app .input.is-dark:active, #optimole-app .input.is-dark.is-active,\n      #optimole-app .textarea.is-dark:focus,\n      #optimole-app .textarea.is-dark.is-focused,\n      #optimole-app .textarea.is-dark:active,\n      #optimole-app .textarea.is-dark.is-active {\n        box-shadow: 0 0 0 0.125em rgba(54, 54, 54, 0.25); }\n    #optimole-app .input.is-primary,\n    #optimole-app .textarea.is-primary {\n      border-color: #EF686B; }\n      #optimole-app .input.is-primary:focus, #optimole-app .input.is-primary.is-focused, #optimole-app .input.is-primary:active, #optimole-app .input.is-primary.is-active,\n      #optimole-app .textarea.is-primary:focus,\n      #optimole-app .textarea.is-primary.is-focused,\n      #optimole-app .textarea.is-primary:active,\n      #optimole-app .textarea.is-primary.is-active {\n        box-shadow: 0 0 0 0.125em rgba(239, 104, 107, 0.25); }\n    #optimole-app .input.is-link,\n    #optimole-app .textarea.is-link {\n      border-color: #3273dc; }\n      #optimole-app .input.is-link:focus, #optimole-app .input.is-link.is-focused, #optimole-app .input.is-link:active, #optimole-app .input.is-link.is-active,\n      #optimole-app .textarea.is-link:focus,\n      #optimole-app .textarea.is-link.is-focused,\n      #optimole-app .textarea.is-link:active,\n      #optimole-app .textarea.is-link.is-active {\n        box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n    #optimole-app .input.is-info,\n    #optimole-app .textarea.is-info {\n      border-color: #5180C1; }\n      #optimole-app .input.is-info:focus, #optimole-app .input.is-info.is-focused, #optimole-app .input.is-info:active, #optimole-app .input.is-info.is-active,\n      #optimole-app .textarea.is-info:focus,\n      #optimole-app .textarea.is-info.is-focused,\n      #optimole-app .textarea.is-info:active,\n      #optimole-app .textarea.is-info.is-active {\n        box-shadow: 0 0 0 0.125em rgba(81, 128, 193, 0.25); }\n    #optimole-app .input.is-success,\n    #optimole-app .textarea.is-success {\n      border-color: #34a85e; }\n      #optimole-app .input.is-success:focus, #optimole-app .input.is-success.is-focused, #optimole-app .input.is-success:active, #optimole-app .input.is-success.is-active,\n      #optimole-app .textarea.is-success:focus,\n      #optimole-app .textarea.is-success.is-focused,\n      #optimole-app .textarea.is-success:active,\n      #optimole-app .textarea.is-success.is-active {\n        box-shadow: 0 0 0 0.125em rgba(52, 168, 94, 0.25); }\n    #optimole-app .input.is-warning,\n    #optimole-app .textarea.is-warning {\n      border-color: #ffdd57; }\n      #optimole-app .input.is-warning:focus, #optimole-app .input.is-warning.is-focused, #optimole-app .input.is-warning:active, #optimole-app .input.is-warning.is-active,\n      #optimole-app .textarea.is-warning:focus,\n      #optimole-app .textarea.is-warning.is-focused,\n      #optimole-app .textarea.is-warning:active,\n      #optimole-app .textarea.is-warning.is-active {\n        box-shadow: 0 0 0 0.125em rgba(255, 221, 87, 0.25); }\n    #optimole-app .input.is-danger,\n    #optimole-app .textarea.is-danger {\n      border-color: #D54222; }\n      #optimole-app .input.is-danger:focus, #optimole-app .input.is-danger.is-focused, #optimole-app .input.is-danger:active, #optimole-app .input.is-danger.is-active,\n      #optimole-app .textarea.is-danger:focus,\n      #optimole-app .textarea.is-danger.is-focused,\n      #optimole-app .textarea.is-danger:active,\n      #optimole-app .textarea.is-danger.is-active {\n        box-shadow: 0 0 0 0.125em rgba(213, 66, 34, 0.25); }\n    #optimole-app .input.is-small,\n    #optimole-app .textarea.is-small {\n      border-radius: 2px;\n      font-size: 0.75rem; }\n    #optimole-app .input.is-medium,\n    #optimole-app .textarea.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .input.is-large,\n    #optimole-app .textarea.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .input.is-fullwidth,\n    #optimole-app .textarea.is-fullwidth {\n      display: block;\n      width: 100%; }\n    #optimole-app .input.is-inline,\n    #optimole-app .textarea.is-inline {\n      display: inline;\n      width: auto; }\n  #optimole-app .input.is-rounded {\n    border-radius: 290486px;\n    padding-left: 1em;\n    padding-right: 1em; }\n  #optimole-app .input.is-static {\n    background-color: transparent;\n    border-color: transparent;\n    box-shadow: none;\n    padding-left: 0;\n    padding-right: 0; }\n  #optimole-app .textarea {\n    display: block;\n    max-width: 100%;\n    min-width: 100%;\n    padding: 0.625em;\n    resize: vertical; }\n    #optimole-app .textarea:not([rows]) {\n      max-height: 600px;\n      min-height: 120px; }\n    #optimole-app .textarea[rows] {\n      height: initial; }\n    #optimole-app .textarea.has-fixed-size {\n      resize: none; }\n  #optimole-app .checkbox,\n  #optimole-app .radio {\n    cursor: pointer;\n    display: inline-block;\n    line-height: 1.25;\n    position: relative; }\n    #optimole-app .checkbox input,\n    #optimole-app .radio input {\n      cursor: pointer; }\n    #optimole-app .checkbox:hover,\n    #optimole-app .radio:hover {\n      color: #363636; }\n    #optimole-app .checkbox[disabled],\n    fieldset[disabled] #optimole-app .checkbox,\n    #optimole-app .radio[disabled],\n    fieldset[disabled]\n    #optimole-app .radio {\n      color: #7a7a7a;\n      cursor: not-allowed; }\n  #optimole-app .radio + .radio {\n    margin-left: 0.5em; }\n  #optimole-app .select {\n    display: inline-block;\n    max-width: 100%;\n    position: relative;\n    vertical-align: top; }\n    #optimole-app .select:not(.is-multiple) {\n      height: 2.25em; }\n    #optimole-app .select:not(.is-multiple):not(.is-loading)::after {\n      border-color: #3273dc;\n      right: 1.125em;\n      z-index: 4; }\n    #optimole-app .select.is-rounded select {\n      border-radius: 290486px;\n      padding-left: 1em; }\n    #optimole-app .select select {\n      background-color: white;\n      border-color: #dbdbdb;\n      color: #363636;\n      cursor: pointer;\n      display: block;\n      font-size: 1em;\n      max-width: 100%;\n      outline: none; }\n      #optimole-app .select select::-moz-placeholder {\n        color: rgba(54, 54, 54, 0.3); }\n      #optimole-app .select select::-webkit-input-placeholder {\n        color: rgba(54, 54, 54, 0.3); }\n      #optimole-app .select select:-moz-placeholder {\n        color: rgba(54, 54, 54, 0.3); }\n      #optimole-app .select select:-ms-input-placeholder {\n        color: rgba(54, 54, 54, 0.3); }\n      #optimole-app .select select:hover, #optimole-app .select select.is-hovered {\n        border-color: #b5b5b5; }\n      #optimole-app .select select:focus, #optimole-app .select select.is-focused, #optimole-app .select select:active, #optimole-app .select select.is-active {\n        border-color: #3273dc;\n        box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n      #optimole-app .select select[disabled],\n      fieldset[disabled] #optimole-app .select select {\n        background-color: whitesmoke;\n        border-color: whitesmoke;\n        box-shadow: none;\n        color: #7a7a7a; }\n        #optimole-app .select select[disabled]::-moz-placeholder,\n        fieldset[disabled] #optimole-app .select select::-moz-placeholder {\n          color: rgba(122, 122, 122, 0.3); }\n        #optimole-app .select select[disabled]::-webkit-input-placeholder,\n        fieldset[disabled] #optimole-app .select select::-webkit-input-placeholder {\n          color: rgba(122, 122, 122, 0.3); }\n        #optimole-app .select select[disabled]:-moz-placeholder,\n        fieldset[disabled] #optimole-app .select select:-moz-placeholder {\n          color: rgba(122, 122, 122, 0.3); }\n        #optimole-app .select select[disabled]:-ms-input-placeholder,\n        fieldset[disabled] #optimole-app .select select:-ms-input-placeholder {\n          color: rgba(122, 122, 122, 0.3); }\n      #optimole-app .select select::-ms-expand {\n        display: none; }\n      #optimole-app .select select[disabled]:hover,\n      fieldset[disabled] #optimole-app .select select:hover {\n        border-color: whitesmoke; }\n      #optimole-app .select select:not([multiple]) {\n        padding-right: 2.5em; }\n      #optimole-app .select select[multiple] {\n        height: auto;\n        padding: 0; }\n        #optimole-app .select select[multiple] option {\n          padding: 0.5em 1em; }\n    #optimole-app .select:not(.is-multiple):not(.is-loading):hover::after {\n      border-color: #363636; }\n    #optimole-app .select.is-white:not(:hover)::after {\n      border-color: white; }\n    #optimole-app .select.is-white select {\n      border-color: white; }\n      #optimole-app .select.is-white select:hover, #optimole-app .select.is-white select.is-hovered {\n        border-color: #f2f2f2; }\n      #optimole-app .select.is-white select:focus, #optimole-app .select.is-white select.is-focused, #optimole-app .select.is-white select:active, #optimole-app .select.is-white select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(255, 255, 255, 0.25); }\n    #optimole-app .select.is-black:not(:hover)::after {\n      border-color: #0a0a0a; }\n    #optimole-app .select.is-black select {\n      border-color: #0a0a0a; }\n      #optimole-app .select.is-black select:hover, #optimole-app .select.is-black select.is-hovered {\n        border-color: black; }\n      #optimole-app .select.is-black select:focus, #optimole-app .select.is-black select.is-focused, #optimole-app .select.is-black select:active, #optimole-app .select.is-black select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(10, 10, 10, 0.25); }\n    #optimole-app .select.is-light:not(:hover)::after {\n      border-color: whitesmoke; }\n    #optimole-app .select.is-light select {\n      border-color: whitesmoke; }\n      #optimole-app .select.is-light select:hover, #optimole-app .select.is-light select.is-hovered {\n        border-color: #e8e8e8; }\n      #optimole-app .select.is-light select:focus, #optimole-app .select.is-light select.is-focused, #optimole-app .select.is-light select:active, #optimole-app .select.is-light select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(245, 245, 245, 0.25); }\n    #optimole-app .select.is-dark:not(:hover)::after {\n      border-color: #363636; }\n    #optimole-app .select.is-dark select {\n      border-color: #363636; }\n      #optimole-app .select.is-dark select:hover, #optimole-app .select.is-dark select.is-hovered {\n        border-color: #292929; }\n      #optimole-app .select.is-dark select:focus, #optimole-app .select.is-dark select.is-focused, #optimole-app .select.is-dark select:active, #optimole-app .select.is-dark select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(54, 54, 54, 0.25); }\n    #optimole-app .select.is-primary:not(:hover)::after {\n      border-color: #EF686B; }\n    #optimole-app .select.is-primary select {\n      border-color: #EF686B; }\n      #optimole-app .select.is-primary select:hover, #optimole-app .select.is-primary select.is-hovered {\n        border-color: #ed5154; }\n      #optimole-app .select.is-primary select:focus, #optimole-app .select.is-primary select.is-focused, #optimole-app .select.is-primary select:active, #optimole-app .select.is-primary select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(239, 104, 107, 0.25); }\n    #optimole-app .select.is-link:not(:hover)::after {\n      border-color: #3273dc; }\n    #optimole-app .select.is-link select {\n      border-color: #3273dc; }\n      #optimole-app .select.is-link select:hover, #optimole-app .select.is-link select.is-hovered {\n        border-color: #2366d1; }\n      #optimole-app .select.is-link select:focus, #optimole-app .select.is-link select.is-focused, #optimole-app .select.is-link select:active, #optimole-app .select.is-link select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25); }\n    #optimole-app .select.is-info:not(:hover)::after {\n      border-color: #5180C1; }\n    #optimole-app .select.is-info select {\n      border-color: #5180C1; }\n      #optimole-app .select.is-info select:hover, #optimole-app .select.is-info select.is-hovered {\n        border-color: #4173b7; }\n      #optimole-app .select.is-info select:focus, #optimole-app .select.is-info select.is-focused, #optimole-app .select.is-info select:active, #optimole-app .select.is-info select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(81, 128, 193, 0.25); }\n    #optimole-app .select.is-success:not(:hover)::after {\n      border-color: #34a85e; }\n    #optimole-app .select.is-success select {\n      border-color: #34a85e; }\n      #optimole-app .select.is-success select:hover, #optimole-app .select.is-success select.is-hovered {\n        border-color: #2e9553; }\n      #optimole-app .select.is-success select:focus, #optimole-app .select.is-success select.is-focused, #optimole-app .select.is-success select:active, #optimole-app .select.is-success select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(52, 168, 94, 0.25); }\n    #optimole-app .select.is-warning:not(:hover)::after {\n      border-color: #ffdd57; }\n    #optimole-app .select.is-warning select {\n      border-color: #ffdd57; }\n      #optimole-app .select.is-warning select:hover, #optimole-app .select.is-warning select.is-hovered {\n        border-color: #ffd83d; }\n      #optimole-app .select.is-warning select:focus, #optimole-app .select.is-warning select.is-focused, #optimole-app .select.is-warning select:active, #optimole-app .select.is-warning select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(255, 221, 87, 0.25); }\n    #optimole-app .select.is-danger:not(:hover)::after {\n      border-color: #D54222; }\n    #optimole-app .select.is-danger select {\n      border-color: #D54222; }\n      #optimole-app .select.is-danger select:hover, #optimole-app .select.is-danger select.is-hovered {\n        border-color: #bf3b1e; }\n      #optimole-app .select.is-danger select:focus, #optimole-app .select.is-danger select.is-focused, #optimole-app .select.is-danger select:active, #optimole-app .select.is-danger select.is-active {\n        box-shadow: 0 0 0 0.125em rgba(213, 66, 34, 0.25); }\n    #optimole-app .select.is-small {\n      border-radius: 2px;\n      font-size: 0.75rem; }\n    #optimole-app .select.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .select.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .select.is-disabled::after {\n      border-color: #7a7a7a; }\n    #optimole-app .select.is-fullwidth {\n      width: 100%; }\n      #optimole-app .select.is-fullwidth select {\n        width: 100%; }\n    #optimole-app .select.is-loading::after {\n      margin-top: 0;\n      position: absolute;\n      right: 0.625em;\n      top: 0.625em;\n      transform: none; }\n    #optimole-app .select.is-loading.is-small:after {\n      font-size: 0.75rem; }\n    #optimole-app .select.is-loading.is-medium:after {\n      font-size: 1.25rem; }\n    #optimole-app .select.is-loading.is-large:after {\n      font-size: 1.5rem; }\n  #optimole-app .file {\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: start;\n        justify-content: flex-start;\n    position: relative; }\n    #optimole-app .file.is-white .file-cta {\n      background-color: white;\n      border-color: transparent;\n      color: #0a0a0a; }\n    #optimole-app .file.is-white:hover .file-cta, #optimole-app .file.is-white.is-hovered .file-cta {\n      background-color: #f9f9f9;\n      border-color: transparent;\n      color: #0a0a0a; }\n    #optimole-app .file.is-white:focus .file-cta, #optimole-app .file.is-white.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(255, 255, 255, 0.25);\n      color: #0a0a0a; }\n    #optimole-app .file.is-white:active .file-cta, #optimole-app .file.is-white.is-active .file-cta {\n      background-color: #f2f2f2;\n      border-color: transparent;\n      color: #0a0a0a; }\n    #optimole-app .file.is-black .file-cta {\n      background-color: #0a0a0a;\n      border-color: transparent;\n      color: white; }\n    #optimole-app .file.is-black:hover .file-cta, #optimole-app .file.is-black.is-hovered .file-cta {\n      background-color: #040404;\n      border-color: transparent;\n      color: white; }\n    #optimole-app .file.is-black:focus .file-cta, #optimole-app .file.is-black.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(10, 10, 10, 0.25);\n      color: white; }\n    #optimole-app .file.is-black:active .file-cta, #optimole-app .file.is-black.is-active .file-cta {\n      background-color: black;\n      border-color: transparent;\n      color: white; }\n    #optimole-app .file.is-light .file-cta {\n      background-color: whitesmoke;\n      border-color: transparent;\n      color: #363636; }\n    #optimole-app .file.is-light:hover .file-cta, #optimole-app .file.is-light.is-hovered .file-cta {\n      background-color: #eeeeee;\n      border-color: transparent;\n      color: #363636; }\n    #optimole-app .file.is-light:focus .file-cta, #optimole-app .file.is-light.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(245, 245, 245, 0.25);\n      color: #363636; }\n    #optimole-app .file.is-light:active .file-cta, #optimole-app .file.is-light.is-active .file-cta {\n      background-color: #e8e8e8;\n      border-color: transparent;\n      color: #363636; }\n    #optimole-app .file.is-dark .file-cta {\n      background-color: #363636;\n      border-color: transparent;\n      color: whitesmoke; }\n    #optimole-app .file.is-dark:hover .file-cta, #optimole-app .file.is-dark.is-hovered .file-cta {\n      background-color: #2f2f2f;\n      border-color: transparent;\n      color: whitesmoke; }\n    #optimole-app .file.is-dark:focus .file-cta, #optimole-app .file.is-dark.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(54, 54, 54, 0.25);\n      color: whitesmoke; }\n    #optimole-app .file.is-dark:active .file-cta, #optimole-app .file.is-dark.is-active .file-cta {\n      background-color: #292929;\n      border-color: transparent;\n      color: whitesmoke; }\n    #optimole-app .file.is-primary .file-cta {\n      background-color: #EF686B;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-primary:hover .file-cta, #optimole-app .file.is-primary.is-hovered .file-cta {\n      background-color: #ee5c60;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-primary:focus .file-cta, #optimole-app .file.is-primary.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(239, 104, 107, 0.25);\n      color: #fff; }\n    #optimole-app .file.is-primary:active .file-cta, #optimole-app .file.is-primary.is-active .file-cta {\n      background-color: #ed5154;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-link .file-cta {\n      background-color: #3273dc;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-link:hover .file-cta, #optimole-app .file.is-link.is-hovered .file-cta {\n      background-color: #276cda;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-link:focus .file-cta, #optimole-app .file.is-link.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(50, 115, 220, 0.25);\n      color: #fff; }\n    #optimole-app .file.is-link:active .file-cta, #optimole-app .file.is-link.is-active .file-cta {\n      background-color: #2366d1;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-info .file-cta {\n      background-color: #5180C1;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-info:hover .file-cta, #optimole-app .file.is-info.is-hovered .file-cta {\n      background-color: #4879be;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-info:focus .file-cta, #optimole-app .file.is-info.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(81, 128, 193, 0.25);\n      color: #fff; }\n    #optimole-app .file.is-info:active .file-cta, #optimole-app .file.is-info.is-active .file-cta {\n      background-color: #4173b7;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-success .file-cta {\n      background-color: #34a85e;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-success:hover .file-cta, #optimole-app .file.is-success.is-hovered .file-cta {\n      background-color: #319e59;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-success:focus .file-cta, #optimole-app .file.is-success.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(52, 168, 94, 0.25);\n      color: #fff; }\n    #optimole-app .file.is-success:active .file-cta, #optimole-app .file.is-success.is-active .file-cta {\n      background-color: #2e9553;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-warning .file-cta {\n      background-color: #ffdd57;\n      border-color: transparent;\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .file.is-warning:hover .file-cta, #optimole-app .file.is-warning.is-hovered .file-cta {\n      background-color: #ffdb4a;\n      border-color: transparent;\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .file.is-warning:focus .file-cta, #optimole-app .file.is-warning.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(255, 221, 87, 0.25);\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .file.is-warning:active .file-cta, #optimole-app .file.is-warning.is-active .file-cta {\n      background-color: #ffd83d;\n      border-color: transparent;\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .file.is-danger .file-cta {\n      background-color: #D54222;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-danger:hover .file-cta, #optimole-app .file.is-danger.is-hovered .file-cta {\n      background-color: #ca3f20;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-danger:focus .file-cta, #optimole-app .file.is-danger.is-focused .file-cta {\n      border-color: transparent;\n      box-shadow: 0 0 0.5em rgba(213, 66, 34, 0.25);\n      color: #fff; }\n    #optimole-app .file.is-danger:active .file-cta, #optimole-app .file.is-danger.is-active .file-cta {\n      background-color: #bf3b1e;\n      border-color: transparent;\n      color: #fff; }\n    #optimole-app .file.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .file.is-medium {\n      font-size: 1.25rem; }\n      #optimole-app .file.is-medium .file-icon .fa {\n        font-size: 21px; }\n    #optimole-app .file.is-large {\n      font-size: 1.5rem; }\n      #optimole-app .file.is-large .file-icon .fa {\n        font-size: 28px; }\n    #optimole-app .file.has-name .file-cta {\n      border-bottom-right-radius: 0;\n      border-top-right-radius: 0; }\n    #optimole-app .file.has-name .file-name {\n      border-bottom-left-radius: 0;\n      border-top-left-radius: 0; }\n    #optimole-app .file.has-name.is-empty .file-cta {\n      border-radius: 4px; }\n    #optimole-app .file.has-name.is-empty .file-name {\n      display: none; }\n    #optimole-app .file.is-boxed .file-label {\n      -ms-flex-direction: column;\n          flex-direction: column; }\n    #optimole-app .file.is-boxed .file-cta {\n      -ms-flex-direction: column;\n          flex-direction: column;\n      height: auto;\n      padding: 1em 3em; }\n    #optimole-app .file.is-boxed .file-name {\n      border-width: 0 1px 1px; }\n    #optimole-app .file.is-boxed .file-icon {\n      height: 1.5em;\n      width: 1.5em; }\n      #optimole-app .file.is-boxed .file-icon .fa {\n        font-size: 21px; }\n    #optimole-app .file.is-boxed.is-small .file-icon .fa {\n      font-size: 14px; }\n    #optimole-app .file.is-boxed.is-medium .file-icon .fa {\n      font-size: 28px; }\n    #optimole-app .file.is-boxed.is-large .file-icon .fa {\n      font-size: 35px; }\n    #optimole-app .file.is-boxed.has-name .file-cta {\n      border-radius: 4px 4px 0 0; }\n    #optimole-app .file.is-boxed.has-name .file-name {\n      border-radius: 0 0 4px 4px;\n      border-width: 0 1px 1px; }\n    #optimole-app .file.is-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .file.is-fullwidth .file-label {\n      width: 100%; }\n    #optimole-app .file.is-fullwidth .file-name {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      max-width: none; }\n    #optimole-app .file.is-right {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n      #optimole-app .file.is-right .file-cta {\n        border-radius: 0 4px 4px 0; }\n      #optimole-app .file.is-right .file-name {\n        border-radius: 4px 0 0 4px;\n        border-width: 1px 0 1px 1px;\n        -ms-flex-order: -1;\n            order: -1; }\n  #optimole-app .file-label {\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex;\n    cursor: pointer;\n    -ms-flex-pack: start;\n        justify-content: flex-start;\n    overflow: hidden;\n    position: relative; }\n    #optimole-app .file-label:hover .file-cta {\n      background-color: #eeeeee;\n      color: #363636; }\n    #optimole-app .file-label:hover .file-name {\n      border-color: #d5d5d5; }\n    #optimole-app .file-label:active .file-cta {\n      background-color: #e8e8e8;\n      color: #363636; }\n    #optimole-app .file-label:active .file-name {\n      border-color: #cfcfcf; }\n  #optimole-app .file-input {\n    height: 100%;\n    left: 0;\n    opacity: 0;\n    outline: none;\n    position: absolute;\n    top: 0;\n    width: 100%; }\n  #optimole-app .file-cta,\n  #optimole-app .file-name {\n    border-color: #dbdbdb;\n    border-radius: 4px;\n    font-size: 1em;\n    padding-left: 1em;\n    padding-right: 1em;\n    white-space: nowrap; }\n  #optimole-app .file-cta {\n    background-color: whitesmoke;\n    color: #4a4a4a; }\n  #optimole-app .file-name {\n    border-color: #dbdbdb;\n    border-style: solid;\n    border-width: 1px 1px 1px 0;\n    display: block;\n    max-width: 16em;\n    overflow: hidden;\n    text-align: left;\n    text-overflow: ellipsis; }\n  #optimole-app .file-icon {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    height: 1em;\n    -ms-flex-pack: center;\n        justify-content: center;\n    margin-right: 0.5em;\n    width: 1em; }\n    #optimole-app .file-icon .fa {\n      font-size: 14px; }\n  #optimole-app .label {\n    color: #363636;\n    display: block;\n    font-size: 1rem;\n    font-weight: 700; }\n    #optimole-app .label:not(:last-child) {\n      margin-bottom: 0.5em; }\n    #optimole-app .label.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .label.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .label.is-large {\n      font-size: 1.5rem; }\n  #optimole-app .help {\n    display: block;\n    font-size: 0.75rem;\n    margin-top: 0.25rem; }\n    #optimole-app .help.is-white {\n      color: white; }\n    #optimole-app .help.is-black {\n      color: #0a0a0a; }\n    #optimole-app .help.is-light {\n      color: whitesmoke; }\n    #optimole-app .help.is-dark {\n      color: #363636; }\n    #optimole-app .help.is-primary {\n      color: #EF686B; }\n    #optimole-app .help.is-link {\n      color: #3273dc; }\n    #optimole-app .help.is-info {\n      color: #5180C1; }\n    #optimole-app .help.is-success {\n      color: #34a85e; }\n    #optimole-app .help.is-warning {\n      color: #ffdd57; }\n    #optimole-app .help.is-danger {\n      color: #D54222; }\n  #optimole-app .field:not(:last-child) {\n    margin-bottom: 0.75rem; }\n  #optimole-app .field.has-addons {\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: start;\n        justify-content: flex-start; }\n    #optimole-app .field.has-addons .control:not(:last-child) {\n      margin-right: -1px; }\n    #optimole-app .field.has-addons .control:not(:first-child):not(:last-child) .button,\n    #optimole-app .field.has-addons .control:not(:first-child):not(:last-child) .input,\n    #optimole-app .field.has-addons .control:not(:first-child):not(:last-child) .select select {\n      border-radius: 0; }\n    #optimole-app .field.has-addons .control:first-child:not(:only-child) .button,\n    #optimole-app .field.has-addons .control:first-child:not(:only-child) .input,\n    #optimole-app .field.has-addons .control:first-child:not(:only-child) .select select {\n      border-bottom-right-radius: 0;\n      border-top-right-radius: 0; }\n    #optimole-app .field.has-addons .control:last-child:not(:only-child) .button,\n    #optimole-app .field.has-addons .control:last-child:not(:only-child) .input,\n    #optimole-app .field.has-addons .control:last-child:not(:only-child) .select select {\n      border-bottom-left-radius: 0;\n      border-top-left-radius: 0; }\n    #optimole-app .field.has-addons .control .button:not([disabled]):hover, #optimole-app .field.has-addons .control .button:not([disabled]).is-hovered,\n    #optimole-app .field.has-addons .control .input:not([disabled]):hover,\n    #optimole-app .field.has-addons .control .input:not([disabled]).is-hovered,\n    #optimole-app .field.has-addons .control .select select:not([disabled]):hover,\n    #optimole-app .field.has-addons .control .select select:not([disabled]).is-hovered {\n      z-index: 2; }\n    #optimole-app .field.has-addons .control .button:not([disabled]):focus, #optimole-app .field.has-addons .control .button:not([disabled]).is-focused, #optimole-app .field.has-addons .control .button:not([disabled]):active, #optimole-app .field.has-addons .control .button:not([disabled]).is-active,\n    #optimole-app .field.has-addons .control .input:not([disabled]):focus,\n    #optimole-app .field.has-addons .control .input:not([disabled]).is-focused,\n    #optimole-app .field.has-addons .control .input:not([disabled]):active,\n    #optimole-app .field.has-addons .control .input:not([disabled]).is-active,\n    #optimole-app .field.has-addons .control .select select:not([disabled]):focus,\n    #optimole-app .field.has-addons .control .select select:not([disabled]).is-focused,\n    #optimole-app .field.has-addons .control .select select:not([disabled]):active,\n    #optimole-app .field.has-addons .control .select select:not([disabled]).is-active {\n      z-index: 3; }\n      #optimole-app .field.has-addons .control .button:not([disabled]):focus:hover, #optimole-app .field.has-addons .control .button:not([disabled]).is-focused:hover, #optimole-app .field.has-addons .control .button:not([disabled]):active:hover, #optimole-app .field.has-addons .control .button:not([disabled]).is-active:hover,\n      #optimole-app .field.has-addons .control .input:not([disabled]):focus:hover,\n      #optimole-app .field.has-addons .control .input:not([disabled]).is-focused:hover,\n      #optimole-app .field.has-addons .control .input:not([disabled]):active:hover,\n      #optimole-app .field.has-addons .control .input:not([disabled]).is-active:hover,\n      #optimole-app .field.has-addons .control .select select:not([disabled]):focus:hover,\n      #optimole-app .field.has-addons .control .select select:not([disabled]).is-focused:hover,\n      #optimole-app .field.has-addons .control .select select:not([disabled]):active:hover,\n      #optimole-app .field.has-addons .control .select select:not([disabled]).is-active:hover {\n        z-index: 4; }\n    #optimole-app .field.has-addons .control.is-expanded {\n      -ms-flex-positive: 1;\n          flex-grow: 1; }\n    #optimole-app .field.has-addons.has-addons-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .field.has-addons.has-addons-right {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n    #optimole-app .field.has-addons.has-addons-fullwidth .control {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 0;\n          flex-shrink: 0; }\n  #optimole-app .field.is-grouped {\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: start;\n        justify-content: flex-start; }\n    #optimole-app .field.is-grouped > .control {\n      -ms-flex-negative: 0;\n          flex-shrink: 0; }\n      #optimole-app .field.is-grouped > .control:not(:last-child) {\n        margin-bottom: 0;\n        margin-right: 0.75rem; }\n      #optimole-app .field.is-grouped > .control.is-expanded {\n        -ms-flex-positive: 1;\n            flex-grow: 1;\n        -ms-flex-negative: 1;\n            flex-shrink: 1; }\n    #optimole-app .field.is-grouped.is-grouped-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .field.is-grouped.is-grouped-right {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n    #optimole-app .field.is-grouped.is-grouped-multiline {\n      -ms-flex-wrap: wrap;\n          flex-wrap: wrap; }\n      #optimole-app .field.is-grouped.is-grouped-multiline > .control:last-child, #optimole-app .field.is-grouped.is-grouped-multiline > .control:not(:last-child) {\n        margin-bottom: 0.75rem; }\n      #optimole-app .field.is-grouped.is-grouped-multiline:last-child {\n        margin-bottom: -0.75rem; }\n      #optimole-app .field.is-grouped.is-grouped-multiline:not(:last-child) {\n        margin-bottom: 0; }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .field.is-horizontal {\n      display: -ms-flexbox;\n      display: flex; } }\n  #optimole-app .field-label .label {\n    font-size: inherit; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .field-label {\n      margin-bottom: 0.5rem; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .field-label {\n      -ms-flex-preferred-size: 0;\n          flex-basis: 0;\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 0;\n          flex-shrink: 0;\n      margin-right: 1.5rem;\n      text-align: right; }\n      #optimole-app .field-label.is-small {\n        font-size: 0.75rem;\n        padding-top: 0.375em; }\n      #optimole-app .field-label.is-normal {\n        padding-top: 0.375em; }\n      #optimole-app .field-label.is-medium {\n        font-size: 1.25rem;\n        padding-top: 0.375em; }\n      #optimole-app .field-label.is-large {\n        font-size: 1.5rem;\n        padding-top: 0.375em; } }\n  #optimole-app .field-body .field .field {\n    margin-bottom: 0; }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .field-body {\n      display: -ms-flexbox;\n      display: flex;\n      -ms-flex-preferred-size: 0;\n          flex-basis: 0;\n      -ms-flex-positive: 5;\n          flex-grow: 5;\n      -ms-flex-negative: 1;\n          flex-shrink: 1; }\n      #optimole-app .field-body .field {\n        margin-bottom: 0; }\n      #optimole-app .field-body > .field {\n        -ms-flex-negative: 1;\n            flex-shrink: 1; }\n        #optimole-app .field-body > .field:not(.is-narrow) {\n          -ms-flex-positive: 1;\n              flex-grow: 1; }\n        #optimole-app .field-body > .field:not(:last-child) {\n          margin-right: 0.75rem; } }\n  #optimole-app .control {\n    box-sizing: border-box;\n    clear: both;\n    font-size: 1rem;\n    position: relative;\n    text-align: left; }\n    #optimole-app .control.has-icons-left .input:focus ~ .icon,\n    #optimole-app .control.has-icons-left .select:focus ~ .icon, #optimole-app .control.has-icons-right .input:focus ~ .icon,\n    #optimole-app .control.has-icons-right .select:focus ~ .icon {\n      color: #7a7a7a; }\n    #optimole-app .control.has-icons-left .input.is-small ~ .icon,\n    #optimole-app .control.has-icons-left .select.is-small ~ .icon, #optimole-app .control.has-icons-right .input.is-small ~ .icon,\n    #optimole-app .control.has-icons-right .select.is-small ~ .icon {\n      font-size: 0.75rem; }\n    #optimole-app .control.has-icons-left .input.is-medium ~ .icon,\n    #optimole-app .control.has-icons-left .select.is-medium ~ .icon, #optimole-app .control.has-icons-right .input.is-medium ~ .icon,\n    #optimole-app .control.has-icons-right .select.is-medium ~ .icon {\n      font-size: 1.25rem; }\n    #optimole-app .control.has-icons-left .input.is-large ~ .icon,\n    #optimole-app .control.has-icons-left .select.is-large ~ .icon, #optimole-app .control.has-icons-right .input.is-large ~ .icon,\n    #optimole-app .control.has-icons-right .select.is-large ~ .icon {\n      font-size: 1.5rem; }\n    #optimole-app .control.has-icons-left .icon, #optimole-app .control.has-icons-right .icon {\n      color: #dbdbdb;\n      height: 2.25em;\n      pointer-events: none;\n      position: absolute;\n      top: 0;\n      width: 2.25em;\n      z-index: 4; }\n    #optimole-app .control.has-icons-left .input,\n    #optimole-app .control.has-icons-left .select select {\n      padding-left: 2.25em; }\n    #optimole-app .control.has-icons-left .icon.is-left {\n      left: 0; }\n    #optimole-app .control.has-icons-right .input,\n    #optimole-app .control.has-icons-right .select select {\n      padding-right: 2.25em; }\n    #optimole-app .control.has-icons-right .icon.is-right {\n      right: 0; }\n    #optimole-app .control.is-loading::after {\n      position: absolute !important;\n      right: 0.625em;\n      top: 0.625em;\n      z-index: 4; }\n    #optimole-app .control.is-loading.is-small:after {\n      font-size: 0.75rem; }\n    #optimole-app .control.is-loading.is-medium:after {\n      font-size: 1.25rem; }\n    #optimole-app .control.is-loading.is-large:after {\n      font-size: 1.5rem; }\n  #optimole-app .icon {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    -ms-flex-pack: center;\n        justify-content: center;\n    height: 1.5rem;\n    width: 1.5rem; }\n    #optimole-app .icon.is-small {\n      height: 1rem;\n      width: 1rem; }\n    #optimole-app .icon.is-medium {\n      height: 2rem;\n      width: 2rem; }\n    #optimole-app .icon.is-large {\n      height: 3rem;\n      width: 3rem; }\n  #optimole-app .image {\n    display: block;\n    position: relative; }\n    #optimole-app .image img {\n      display: block;\n      height: auto;\n      width: 100%; }\n      #optimole-app .image img.is-rounded {\n        border-radius: 290486px; }\n    #optimole-app .image.is-square img,\n    #optimole-app .image.is-square .has-ratio, #optimole-app .image.is-1by1 img,\n    #optimole-app .image.is-1by1 .has-ratio, #optimole-app .image.is-5by4 img,\n    #optimole-app .image.is-5by4 .has-ratio, #optimole-app .image.is-4by3 img,\n    #optimole-app .image.is-4by3 .has-ratio, #optimole-app .image.is-3by2 img,\n    #optimole-app .image.is-3by2 .has-ratio, #optimole-app .image.is-5by3 img,\n    #optimole-app .image.is-5by3 .has-ratio, #optimole-app .image.is-16by9 img,\n    #optimole-app .image.is-16by9 .has-ratio, #optimole-app .image.is-2by1 img,\n    #optimole-app .image.is-2by1 .has-ratio, #optimole-app .image.is-3by1 img,\n    #optimole-app .image.is-3by1 .has-ratio, #optimole-app .image.is-4by5 img,\n    #optimole-app .image.is-4by5 .has-ratio, #optimole-app .image.is-3by4 img,\n    #optimole-app .image.is-3by4 .has-ratio, #optimole-app .image.is-2by3 img,\n    #optimole-app .image.is-2by3 .has-ratio, #optimole-app .image.is-3by5 img,\n    #optimole-app .image.is-3by5 .has-ratio, #optimole-app .image.is-9by16 img,\n    #optimole-app .image.is-9by16 .has-ratio, #optimole-app .image.is-1by2 img,\n    #optimole-app .image.is-1by2 .has-ratio, #optimole-app .image.is-1by3 img,\n    #optimole-app .image.is-1by3 .has-ratio {\n      height: 100%;\n      width: 100%; }\n    #optimole-app .image.is-square, #optimole-app .image.is-1by1 {\n      padding-top: 100%; }\n    #optimole-app .image.is-5by4 {\n      padding-top: 80%; }\n    #optimole-app .image.is-4by3 {\n      padding-top: 75%; }\n    #optimole-app .image.is-3by2 {\n      padding-top: 66.6666%; }\n    #optimole-app .image.is-5by3 {\n      padding-top: 60%; }\n    #optimole-app .image.is-16by9 {\n      padding-top: 56.25%; }\n    #optimole-app .image.is-2by1 {\n      padding-top: 50%; }\n    #optimole-app .image.is-3by1 {\n      padding-top: 33.3333%; }\n    #optimole-app .image.is-4by5 {\n      padding-top: 125%; }\n    #optimole-app .image.is-3by4 {\n      padding-top: 133.3333%; }\n    #optimole-app .image.is-2by3 {\n      padding-top: 150%; }\n    #optimole-app .image.is-3by5 {\n      padding-top: 166.6666%; }\n    #optimole-app .image.is-9by16 {\n      padding-top: 177.7777%; }\n    #optimole-app .image.is-1by2 {\n      padding-top: 200%; }\n    #optimole-app .image.is-1by3 {\n      padding-top: 300%; }\n    #optimole-app .image.is-16x16 {\n      height: 16px;\n      width: 16px; }\n    #optimole-app .image.is-24x24 {\n      height: 24px;\n      width: 24px; }\n    #optimole-app .image.is-32x32 {\n      height: 32px;\n      width: 32px; }\n    #optimole-app .image.is-48x48 {\n      height: 48px;\n      width: 48px; }\n    #optimole-app .image.is-64x64 {\n      height: 64px;\n      width: 64px; }\n    #optimole-app .image.is-96x96 {\n      height: 96px;\n      width: 96px; }\n    #optimole-app .image.is-128x128 {\n      height: 128px;\n      width: 128px; }\n  #optimole-app .notification {\n    background-color: whitesmoke;\n    border-radius: 4px;\n    padding: 1.25rem 2.5rem 1.25rem 1.5rem;\n    position: relative; }\n    #optimole-app .notification a:not(.button):not(.dropdown-item) {\n      color: currentColor;\n      text-decoration: underline; }\n    #optimole-app .notification strong {\n      color: currentColor; }\n    #optimole-app .notification code,\n    #optimole-app .notification pre {\n      background: white; }\n    #optimole-app .notification pre code {\n      background: transparent; }\n    #optimole-app .notification > .delete {\n      position: absolute;\n      right: 0.5rem;\n      top: 0.5rem; }\n    #optimole-app .notification .title,\n    #optimole-app .notification .subtitle,\n    #optimole-app .notification .content {\n      color: currentColor; }\n    #optimole-app .notification.is-white {\n      background-color: white;\n      color: #0a0a0a; }\n    #optimole-app .notification.is-black {\n      background-color: #0a0a0a;\n      color: white; }\n    #optimole-app .notification.is-light {\n      background-color: whitesmoke;\n      color: #363636; }\n    #optimole-app .notification.is-dark {\n      background-color: #363636;\n      color: whitesmoke; }\n    #optimole-app .notification.is-primary {\n      background-color: #EF686B;\n      color: #fff; }\n    #optimole-app .notification.is-link {\n      background-color: #3273dc;\n      color: #fff; }\n    #optimole-app .notification.is-info {\n      background-color: #5180C1;\n      color: #fff; }\n    #optimole-app .notification.is-success {\n      background-color: #34a85e;\n      color: #fff; }\n    #optimole-app .notification.is-warning {\n      background-color: #ffdd57;\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .notification.is-danger {\n      background-color: #D54222;\n      color: #fff; }\n  #optimole-app .progress {\n    -moz-appearance: none;\n    -webkit-appearance: none;\n    border: none;\n    border-radius: 290486px;\n    display: block;\n    height: 1rem;\n    overflow: hidden;\n    padding: 0;\n    width: 100%; }\n    #optimole-app .progress::-webkit-progress-bar {\n      background-color: #dbdbdb; }\n    #optimole-app .progress::-webkit-progress-value {\n      background-color: #4a4a4a; }\n    #optimole-app .progress::-moz-progress-bar {\n      background-color: #4a4a4a; }\n    #optimole-app .progress::-ms-fill {\n      background-color: #4a4a4a;\n      border: none; }\n    #optimole-app .progress:indeterminate {\n      animation-duration: 1.5s;\n      animation-iteration-count: infinite;\n      animation-name: moveIndeterminate;\n      animation-timing-function: linear;\n      background-color: #dbdbdb;\n      background-image: linear-gradient(to right, #4a4a4a 30%, #dbdbdb 30%);\n      background-position: top left;\n      background-repeat: no-repeat;\n      background-size: 150% 150%; }\n      #optimole-app .progress:indeterminate::-webkit-progress-bar {\n        background-color: transparent; }\n      #optimole-app .progress:indeterminate::-moz-progress-bar {\n        background-color: transparent; }\n    #optimole-app .progress.is-white::-webkit-progress-value {\n      background-color: white; }\n    #optimole-app .progress.is-white::-moz-progress-bar {\n      background-color: white; }\n    #optimole-app .progress.is-white::-ms-fill {\n      background-color: white; }\n    #optimole-app .progress.is-white:indeterminate {\n      background-image: linear-gradient(to right, white 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-black::-webkit-progress-value {\n      background-color: #0a0a0a; }\n    #optimole-app .progress.is-black::-moz-progress-bar {\n      background-color: #0a0a0a; }\n    #optimole-app .progress.is-black::-ms-fill {\n      background-color: #0a0a0a; }\n    #optimole-app .progress.is-black:indeterminate {\n      background-image: linear-gradient(to right, #0a0a0a 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-light::-webkit-progress-value {\n      background-color: whitesmoke; }\n    #optimole-app .progress.is-light::-moz-progress-bar {\n      background-color: whitesmoke; }\n    #optimole-app .progress.is-light::-ms-fill {\n      background-color: whitesmoke; }\n    #optimole-app .progress.is-light:indeterminate {\n      background-image: linear-gradient(to right, whitesmoke 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-dark::-webkit-progress-value {\n      background-color: #363636; }\n    #optimole-app .progress.is-dark::-moz-progress-bar {\n      background-color: #363636; }\n    #optimole-app .progress.is-dark::-ms-fill {\n      background-color: #363636; }\n    #optimole-app .progress.is-dark:indeterminate {\n      background-image: linear-gradient(to right, #363636 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-primary::-webkit-progress-value {\n      background-color: #EF686B; }\n    #optimole-app .progress.is-primary::-moz-progress-bar {\n      background-color: #EF686B; }\n    #optimole-app .progress.is-primary::-ms-fill {\n      background-color: #EF686B; }\n    #optimole-app .progress.is-primary:indeterminate {\n      background-image: linear-gradient(to right, #EF686B 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-link::-webkit-progress-value {\n      background-color: #3273dc; }\n    #optimole-app .progress.is-link::-moz-progress-bar {\n      background-color: #3273dc; }\n    #optimole-app .progress.is-link::-ms-fill {\n      background-color: #3273dc; }\n    #optimole-app .progress.is-link:indeterminate {\n      background-image: linear-gradient(to right, #3273dc 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-info::-webkit-progress-value {\n      background-color: #5180C1; }\n    #optimole-app .progress.is-info::-moz-progress-bar {\n      background-color: #5180C1; }\n    #optimole-app .progress.is-info::-ms-fill {\n      background-color: #5180C1; }\n    #optimole-app .progress.is-info:indeterminate {\n      background-image: linear-gradient(to right, #5180C1 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-success::-webkit-progress-value {\n      background-color: #34a85e; }\n    #optimole-app .progress.is-success::-moz-progress-bar {\n      background-color: #34a85e; }\n    #optimole-app .progress.is-success::-ms-fill {\n      background-color: #34a85e; }\n    #optimole-app .progress.is-success:indeterminate {\n      background-image: linear-gradient(to right, #34a85e 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-warning::-webkit-progress-value {\n      background-color: #ffdd57; }\n    #optimole-app .progress.is-warning::-moz-progress-bar {\n      background-color: #ffdd57; }\n    #optimole-app .progress.is-warning::-ms-fill {\n      background-color: #ffdd57; }\n    #optimole-app .progress.is-warning:indeterminate {\n      background-image: linear-gradient(to right, #ffdd57 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-danger::-webkit-progress-value {\n      background-color: #D54222; }\n    #optimole-app .progress.is-danger::-moz-progress-bar {\n      background-color: #D54222; }\n    #optimole-app .progress.is-danger::-ms-fill {\n      background-color: #D54222; }\n    #optimole-app .progress.is-danger:indeterminate {\n      background-image: linear-gradient(to right, #D54222 30%, #dbdbdb 30%); }\n    #optimole-app .progress.is-small {\n      height: 0.75rem; }\n    #optimole-app .progress.is-medium {\n      height: 1.25rem; }\n    #optimole-app .progress.is-large {\n      height: 1.5rem; }\n\n@keyframes moveIndeterminate {\n  from {\n    background-position: 200% 0; }\n  to {\n    background-position: -200% 0; } }\n  #optimole-app .table {\n    background-color: white;\n    color: #363636; }\n    #optimole-app .table td,\n    #optimole-app .table th {\n      border: 1px solid #dbdbdb;\n      border-width: 0 0 1px;\n      padding: 0.5em 0.75em;\n      vertical-align: top; }\n      #optimole-app .table td.is-white,\n      #optimole-app .table th.is-white {\n        background-color: white;\n        border-color: white;\n        color: #0a0a0a; }\n      #optimole-app .table td.is-black,\n      #optimole-app .table th.is-black {\n        background-color: #0a0a0a;\n        border-color: #0a0a0a;\n        color: white; }\n      #optimole-app .table td.is-light,\n      #optimole-app .table th.is-light {\n        background-color: whitesmoke;\n        border-color: whitesmoke;\n        color: #363636; }\n      #optimole-app .table td.is-dark,\n      #optimole-app .table th.is-dark {\n        background-color: #363636;\n        border-color: #363636;\n        color: whitesmoke; }\n      #optimole-app .table td.is-primary,\n      #optimole-app .table th.is-primary {\n        background-color: #EF686B;\n        border-color: #EF686B;\n        color: #fff; }\n      #optimole-app .table td.is-link,\n      #optimole-app .table th.is-link {\n        background-color: #3273dc;\n        border-color: #3273dc;\n        color: #fff; }\n      #optimole-app .table td.is-info,\n      #optimole-app .table th.is-info {\n        background-color: #5180C1;\n        border-color: #5180C1;\n        color: #fff; }\n      #optimole-app .table td.is-success,\n      #optimole-app .table th.is-success {\n        background-color: #34a85e;\n        border-color: #34a85e;\n        color: #fff; }\n      #optimole-app .table td.is-warning,\n      #optimole-app .table th.is-warning {\n        background-color: #ffdd57;\n        border-color: #ffdd57;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .table td.is-danger,\n      #optimole-app .table th.is-danger {\n        background-color: #D54222;\n        border-color: #D54222;\n        color: #fff; }\n      #optimole-app .table td.is-narrow,\n      #optimole-app .table th.is-narrow {\n        white-space: nowrap;\n        width: 1%; }\n      #optimole-app .table td.is-selected,\n      #optimole-app .table th.is-selected {\n        background-color: #EF686B;\n        color: #fff; }\n        #optimole-app .table td.is-selected a,\n        #optimole-app .table td.is-selected strong,\n        #optimole-app .table th.is-selected a,\n        #optimole-app .table th.is-selected strong {\n          color: currentColor; }\n    #optimole-app .table th {\n      color: #363636;\n      text-align: left; }\n    #optimole-app .table tr.is-selected {\n      background-color: #EF686B;\n      color: #fff; }\n      #optimole-app .table tr.is-selected a,\n      #optimole-app .table tr.is-selected strong {\n        color: currentColor; }\n      #optimole-app .table tr.is-selected td,\n      #optimole-app .table tr.is-selected th {\n        border-color: #fff;\n        color: currentColor; }\n    #optimole-app .table thead {\n      background-color: transparent; }\n      #optimole-app .table thead td,\n      #optimole-app .table thead th {\n        border-width: 0 0 2px;\n        color: #363636; }\n    #optimole-app .table tfoot {\n      background-color: transparent; }\n      #optimole-app .table tfoot td,\n      #optimole-app .table tfoot th {\n        border-width: 2px 0 0;\n        color: #363636; }\n    #optimole-app .table tbody {\n      background-color: transparent; }\n      #optimole-app .table tbody tr:last-child td,\n      #optimole-app .table tbody tr:last-child th {\n        border-bottom-width: 0; }\n    #optimole-app .table.is-bordered td,\n    #optimole-app .table.is-bordered th {\n      border-width: 1px; }\n    #optimole-app .table.is-bordered tr:last-child td,\n    #optimole-app .table.is-bordered tr:last-child th {\n      border-bottom-width: 1px; }\n    #optimole-app .table.is-fullwidth {\n      width: 100%; }\n    #optimole-app .table.is-hoverable tbody tr:not(.is-selected):hover {\n      background-color: #fafafa; }\n    #optimole-app .table.is-hoverable.is-striped tbody tr:not(.is-selected):hover {\n      background-color: #fafafa; }\n      #optimole-app .table.is-hoverable.is-striped tbody tr:not(.is-selected):hover:nth-child(even) {\n        background-color: whitesmoke; }\n    #optimole-app .table.is-narrow td,\n    #optimole-app .table.is-narrow th {\n      padding: 0.25em 0.5em; }\n    #optimole-app .table.is-striped tbody tr:not(.is-selected):nth-child(even) {\n      background-color: #fafafa; }\n  #optimole-app .table-container {\n    -webkit-overflow-scrolling: touch;\n    overflow: auto;\n    overflow-y: hidden;\n    max-width: 100%; }\n  #optimole-app .tags {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-wrap: wrap;\n        flex-wrap: wrap;\n    -ms-flex-pack: start;\n        justify-content: flex-start; }\n    #optimole-app .tags .tag {\n      margin-bottom: 0.5rem; }\n      #optimole-app .tags .tag:not(:last-child) {\n        margin-right: 0.5rem; }\n    #optimole-app .tags:last-child {\n      margin-bottom: -0.5rem; }\n    #optimole-app .tags:not(:last-child) {\n      margin-bottom: 1rem; }\n    #optimole-app .tags.are-medium .tag:not(.is-normal):not(.is-large) {\n      font-size: 1rem; }\n    #optimole-app .tags.are-large .tag:not(.is-normal):not(.is-medium) {\n      font-size: 1.25rem; }\n    #optimole-app .tags.has-addons .tag {\n      margin-right: 0; }\n      #optimole-app .tags.has-addons .tag:not(:first-child) {\n        border-bottom-left-radius: 0;\n        border-top-left-radius: 0; }\n      #optimole-app .tags.has-addons .tag:not(:last-child) {\n        border-bottom-right-radius: 0;\n        border-top-right-radius: 0; }\n    #optimole-app .tags.is-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n      #optimole-app .tags.is-centered .tag {\n        margin-right: 0.25rem;\n        margin-left: 0.25rem; }\n    #optimole-app .tags.is-right {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n      #optimole-app .tags.is-right .tag:not(:first-child) {\n        margin-left: 0.5rem; }\n      #optimole-app .tags.is-right .tag:not(:last-child) {\n        margin-right: 0; }\n    #optimole-app .tags.has-addons .tag {\n      margin-right: 0; }\n      #optimole-app .tags.has-addons .tag:not(:first-child) {\n        margin-left: 0;\n        border-bottom-left-radius: 0;\n        border-top-left-radius: 0; }\n      #optimole-app .tags.has-addons .tag:not(:last-child) {\n        border-bottom-right-radius: 0;\n        border-top-right-radius: 0; }\n  #optimole-app .tag:not(body) {\n    -ms-flex-align: center;\n        align-items: center;\n    background-color: whitesmoke;\n    border-radius: 4px;\n    color: #4a4a4a;\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    font-size: 0.75rem;\n    height: 2em;\n    -ms-flex-pack: center;\n        justify-content: center;\n    line-height: 1.5;\n    padding-left: 0.75em;\n    padding-right: 0.75em;\n    white-space: nowrap; }\n    #optimole-app .tag:not(body) .delete {\n      margin-left: 0.25rem;\n      margin-right: -0.375rem; }\n    #optimole-app .tag:not(body).is-white {\n      background-color: white;\n      color: #0a0a0a; }\n    #optimole-app .tag:not(body).is-black {\n      background-color: #0a0a0a;\n      color: white; }\n    #optimole-app .tag:not(body).is-light {\n      background-color: whitesmoke;\n      color: #363636; }\n    #optimole-app .tag:not(body).is-dark {\n      background-color: #363636;\n      color: whitesmoke; }\n    #optimole-app .tag:not(body).is-primary {\n      background-color: #EF686B;\n      color: #fff; }\n    #optimole-app .tag:not(body).is-link {\n      background-color: #3273dc;\n      color: #fff; }\n    #optimole-app .tag:not(body).is-info {\n      background-color: #5180C1;\n      color: #fff; }\n    #optimole-app .tag:not(body).is-success {\n      background-color: #34a85e;\n      color: #fff; }\n    #optimole-app .tag:not(body).is-warning {\n      background-color: #ffdd57;\n      color: rgba(0, 0, 0, 0.7); }\n    #optimole-app .tag:not(body).is-danger {\n      background-color: #D54222;\n      color: #fff; }\n    #optimole-app .tag:not(body).is-normal {\n      font-size: 0.75rem; }\n    #optimole-app .tag:not(body).is-medium {\n      font-size: 1rem; }\n    #optimole-app .tag:not(body).is-large {\n      font-size: 1.25rem; }\n    #optimole-app .tag:not(body) .icon:first-child:not(:last-child) {\n      margin-left: -0.375em;\n      margin-right: 0.1875em; }\n    #optimole-app .tag:not(body) .icon:last-child:not(:first-child) {\n      margin-left: 0.1875em;\n      margin-right: -0.375em; }\n    #optimole-app .tag:not(body) .icon:first-child:last-child {\n      margin-left: -0.375em;\n      margin-right: -0.375em; }\n    #optimole-app .tag:not(body).is-delete {\n      margin-left: 1px;\n      padding: 0;\n      position: relative;\n      width: 2em; }\n      #optimole-app .tag:not(body).is-delete::before, #optimole-app .tag:not(body).is-delete::after {\n        background-color: currentColor;\n        content: \"\";\n        display: block;\n        left: 50%;\n        position: absolute;\n        top: 50%;\n        transform: translateX(-50%) translateY(-50%) rotate(45deg);\n        transform-origin: center center; }\n      #optimole-app .tag:not(body).is-delete::before {\n        height: 1px;\n        width: 50%; }\n      #optimole-app .tag:not(body).is-delete::after {\n        height: 50%;\n        width: 1px; }\n      #optimole-app .tag:not(body).is-delete:hover, #optimole-app .tag:not(body).is-delete:focus {\n        background-color: #e8e8e8; }\n      #optimole-app .tag:not(body).is-delete:active {\n        background-color: #dbdbdb; }\n    #optimole-app .tag:not(body).is-rounded {\n      border-radius: 290486px; }\n  #optimole-app a.tag:hover {\n    text-decoration: underline; }\n  #optimole-app .title,\n  #optimole-app .subtitle {\n    word-break: break-word; }\n    #optimole-app .title em,\n    #optimole-app .title span,\n    #optimole-app .subtitle em,\n    #optimole-app .subtitle span {\n      font-weight: inherit; }\n    #optimole-app .title sub,\n    #optimole-app .subtitle sub {\n      font-size: 0.75em; }\n    #optimole-app .title sup,\n    #optimole-app .subtitle sup {\n      font-size: 0.75em; }\n    #optimole-app .title .tag,\n    #optimole-app .subtitle .tag {\n      vertical-align: middle; }\n  #optimole-app .title {\n    color: #363636;\n    font-size: 2rem;\n    font-weight: 600;\n    line-height: 1.125; }\n    #optimole-app .title strong {\n      color: inherit;\n      font-weight: inherit; }\n    #optimole-app .title + .highlight {\n      margin-top: -0.75rem; }\n    #optimole-app .title:not(.is-spaced) + .subtitle {\n      margin-top: -1.25rem; }\n    #optimole-app .title.is-1 {\n      font-size: 3rem; }\n    #optimole-app .title.is-2 {\n      font-size: 2.5rem; }\n    #optimole-app .title.is-3 {\n      font-size: 2rem; }\n    #optimole-app .title.is-4 {\n      font-size: 1.5rem; }\n    #optimole-app .title.is-5 {\n      font-size: 1.25rem; }\n    #optimole-app .title.is-6 {\n      font-size: 1rem; }\n    #optimole-app .title.is-7 {\n      font-size: 0.75rem; }\n  #optimole-app .subtitle {\n    color: #4a4a4a;\n    font-size: 1.25rem;\n    font-weight: 400;\n    line-height: 1.25; }\n    #optimole-app .subtitle strong {\n      color: #363636;\n      font-weight: 600; }\n    #optimole-app .subtitle:not(.is-spaced) + .title {\n      margin-top: -1.25rem; }\n    #optimole-app .subtitle.is-1 {\n      font-size: 3rem; }\n    #optimole-app .subtitle.is-2 {\n      font-size: 2.5rem; }\n    #optimole-app .subtitle.is-3 {\n      font-size: 2rem; }\n    #optimole-app .subtitle.is-4 {\n      font-size: 1.5rem; }\n    #optimole-app .subtitle.is-5 {\n      font-size: 1.25rem; }\n    #optimole-app .subtitle.is-6 {\n      font-size: 1rem; }\n    #optimole-app .subtitle.is-7 {\n      font-size: 0.75rem; }\n  #optimole-app .heading {\n    display: block;\n    font-size: 11px;\n    letter-spacing: 1px;\n    margin-bottom: 5px;\n    text-transform: uppercase; }\n  #optimole-app .highlight {\n    font-weight: 400;\n    max-width: 100%;\n    overflow: hidden;\n    padding: 0; }\n    #optimole-app .highlight pre {\n      overflow: auto;\n      max-width: 100%; }\n  #optimole-app .number {\n    -ms-flex-align: center;\n        align-items: center;\n    background-color: whitesmoke;\n    border-radius: 290486px;\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    font-size: 1.25rem;\n    height: 2em;\n    -ms-flex-pack: center;\n        justify-content: center;\n    margin-right: 1.5rem;\n    min-width: 2.5em;\n    padding: 0.25rem 0.5rem;\n    text-align: center;\n    vertical-align: top; }\n  #optimole-app .breadcrumb {\n    font-size: 1rem;\n    white-space: nowrap; }\n    #optimole-app .breadcrumb a {\n      -ms-flex-align: center;\n          align-items: center;\n      color: #3273dc;\n      display: -ms-flexbox;\n      display: flex;\n      -ms-flex-pack: center;\n          justify-content: center;\n      padding: 0 0.75em; }\n      #optimole-app .breadcrumb a:hover {\n        color: #363636; }\n    #optimole-app .breadcrumb li {\n      -ms-flex-align: center;\n          align-items: center;\n      display: -ms-flexbox;\n      display: flex; }\n      #optimole-app .breadcrumb li:first-child a {\n        padding-left: 0; }\n      #optimole-app .breadcrumb li.is-active a {\n        color: #363636;\n        cursor: default;\n        pointer-events: none; }\n      #optimole-app .breadcrumb li + li::before {\n        color: #b5b5b5;\n        content: \"/\"; }\n    #optimole-app .breadcrumb ul,\n    #optimole-app .breadcrumb ol {\n      -ms-flex-align: start;\n          align-items: flex-start;\n      display: -ms-flexbox;\n      display: flex;\n      -ms-flex-wrap: wrap;\n          flex-wrap: wrap;\n      -ms-flex-pack: start;\n          justify-content: flex-start; }\n    #optimole-app .breadcrumb .icon:first-child {\n      margin-right: 0.5em; }\n    #optimole-app .breadcrumb .icon:last-child {\n      margin-left: 0.5em; }\n    #optimole-app .breadcrumb.is-centered ol,\n    #optimole-app .breadcrumb.is-centered ul {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .breadcrumb.is-right ol,\n    #optimole-app .breadcrumb.is-right ul {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n    #optimole-app .breadcrumb.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .breadcrumb.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .breadcrumb.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .breadcrumb.has-arrow-separator li + li::before {\n      content: \"\\2192\"; }\n    #optimole-app .breadcrumb.has-bullet-separator li + li::before {\n      content: \"\\2022\"; }\n    #optimole-app .breadcrumb.has-dot-separator li + li::before {\n      content: \"\\B7\"; }\n    #optimole-app .breadcrumb.has-succeeds-separator li + li::before {\n      content: \"\\227B\"; }\n  #optimole-app .card {\n    background-color: white;\n    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);\n    color: #4a4a4a;\n    max-width: 100%;\n    position: relative; }\n  #optimole-app .card-header {\n    background-color: transparent;\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    box-shadow: 0 1px 2px rgba(10, 10, 10, 0.1);\n    display: -ms-flexbox;\n    display: flex; }\n  #optimole-app .card-header-title {\n    -ms-flex-align: center;\n        align-items: center;\n    color: #363636;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    font-weight: 700;\n    padding: 0.75rem; }\n    #optimole-app .card-header-title.is-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n  #optimole-app .card-header-icon {\n    -ms-flex-align: center;\n        align-items: center;\n    cursor: pointer;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: center;\n        justify-content: center;\n    padding: 0.75rem; }\n  #optimole-app .card-image {\n    display: block;\n    position: relative; }\n  #optimole-app .card-content {\n    background-color: transparent;\n    padding: 1.5rem; }\n  #optimole-app .card-footer {\n    background-color: transparent;\n    border-top: 1px solid #dbdbdb;\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex; }\n  #optimole-app .card-footer-item {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-preferred-size: 0;\n        flex-basis: 0;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    -ms-flex-pack: center;\n        justify-content: center;\n    padding: 0.75rem; }\n    #optimole-app .card-footer-item:not(:last-child) {\n      border-right: 1px solid #dbdbdb; }\n  #optimole-app .card .media:not(:last-child) {\n    margin-bottom: 0.75rem; }\n  #optimole-app .dropdown {\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    position: relative;\n    vertical-align: top; }\n    #optimole-app .dropdown.is-active .dropdown-menu, #optimole-app .dropdown.is-hoverable:hover .dropdown-menu {\n      display: block; }\n    #optimole-app .dropdown.is-right .dropdown-menu {\n      left: auto;\n      right: 0; }\n    #optimole-app .dropdown.is-up .dropdown-menu {\n      bottom: 100%;\n      padding-bottom: 4px;\n      padding-top: initial;\n      top: auto; }\n  #optimole-app .dropdown-menu {\n    display: none;\n    left: 0;\n    min-width: 12rem;\n    padding-top: 4px;\n    position: absolute;\n    top: 100%;\n    z-index: 20; }\n  #optimole-app .dropdown-content {\n    background-color: white;\n    border-radius: 4px;\n    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);\n    padding-bottom: 0.5rem;\n    padding-top: 0.5rem; }\n  #optimole-app .dropdown-item {\n    color: #4a4a4a;\n    display: block;\n    font-size: 0.875rem;\n    line-height: 1.5;\n    padding: 0.375rem 1rem;\n    position: relative; }\n  #optimole-app a.dropdown-item,\n  #optimole-app button.dropdown-item {\n    padding-right: 3rem;\n    text-align: left;\n    white-space: nowrap;\n    width: 100%; }\n    #optimole-app a.dropdown-item:hover,\n    #optimole-app button.dropdown-item:hover {\n      background-color: whitesmoke;\n      color: #0a0a0a; }\n    #optimole-app a.dropdown-item.is-active,\n    #optimole-app button.dropdown-item.is-active {\n      background-color: #3273dc;\n      color: #fff; }\n  #optimole-app .dropdown-divider {\n    background-color: #dbdbdb;\n    border: none;\n    display: block;\n    height: 1px;\n    margin: 0.5rem 0; }\n  #optimole-app .level {\n    -ms-flex-align: center;\n        align-items: center;\n    -ms-flex-pack: justify;\n        justify-content: space-between; }\n    #optimole-app .level code {\n      border-radius: 4px; }\n    #optimole-app .level img {\n      display: inline-block;\n      vertical-align: top; }\n    #optimole-app .level.is-mobile {\n      display: -ms-flexbox;\n      display: flex; }\n      #optimole-app .level.is-mobile .level-left,\n      #optimole-app .level.is-mobile .level-right {\n        display: -ms-flexbox;\n        display: flex; }\n      #optimole-app .level.is-mobile .level-left + .level-right {\n        margin-top: 0; }\n      #optimole-app .level.is-mobile .level-item:not(:last-child) {\n        margin-bottom: 0;\n        margin-right: 0.75rem; }\n      #optimole-app .level.is-mobile .level-item:not(.is-narrow) {\n        -ms-flex-positive: 1;\n            flex-grow: 1; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .level {\n        display: -ms-flexbox;\n        display: flex; }\n        #optimole-app .level > .level-item:not(.is-narrow) {\n          -ms-flex-positive: 1;\n              flex-grow: 1; } }\n  #optimole-app .level-item {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-preferred-size: auto;\n        flex-basis: auto;\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    -ms-flex-pack: center;\n        justify-content: center; }\n    #optimole-app .level-item .title,\n    #optimole-app .level-item .subtitle {\n      margin-bottom: 0; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .level-item:not(:last-child) {\n        margin-bottom: 0.75rem; } }\n  #optimole-app .level-left,\n  #optimole-app .level-right {\n    -ms-flex-preferred-size: auto;\n        flex-basis: auto;\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0; }\n    #optimole-app .level-left .level-item.is-flexible,\n    #optimole-app .level-right .level-item.is-flexible {\n      -ms-flex-positive: 1;\n          flex-grow: 1; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .level-left .level-item:not(:last-child),\n      #optimole-app .level-right .level-item:not(:last-child) {\n        margin-right: 0.75rem; } }\n  #optimole-app .level-left {\n    -ms-flex-align: center;\n        align-items: center;\n    -ms-flex-pack: start;\n        justify-content: flex-start; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .level-left + .level-right {\n        margin-top: 1.5rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .level-left {\n        display: -ms-flexbox;\n        display: flex; } }\n  #optimole-app .level-right {\n    -ms-flex-align: center;\n        align-items: center;\n    -ms-flex-pack: end;\n        justify-content: flex-end; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .level-right {\n        display: -ms-flexbox;\n        display: flex; } }\n  #optimole-app .list {\n    background-color: white;\n    border-radius: 4px;\n    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1); }\n  #optimole-app .list-item {\n    display: block;\n    padding: 0.5em 1em; }\n    #optimole-app .list-item:not(a) {\n      color: #4a4a4a; }\n    #optimole-app .list-item:first-child {\n      border-top-left-radius: 4px;\n      border-top-right-radius: 4px; }\n    #optimole-app .list-item:last-child {\n      border-top-left-radius: 4px;\n      border-top-right-radius: 4px; }\n    #optimole-app .list-item:not(:last-child) {\n      border-bottom: 1px solid #dbdbdb; }\n    #optimole-app .list-item.is-active {\n      background-color: #3273dc;\n      color: #fff; }\n  #optimole-app a.list-item {\n    background-color: whitesmoke;\n    cursor: pointer; }\n  #optimole-app .media {\n    -ms-flex-align: start;\n        align-items: flex-start;\n    display: -ms-flexbox;\n    display: flex;\n    text-align: left; }\n    #optimole-app .media .content:not(:last-child) {\n      margin-bottom: 0.75rem; }\n    #optimole-app .media .media {\n      border-top: 1px solid rgba(219, 219, 219, 0.5);\n      display: -ms-flexbox;\n      display: flex;\n      padding-top: 0.75rem; }\n      #optimole-app .media .media .content:not(:last-child),\n      #optimole-app .media .media .control:not(:last-child) {\n        margin-bottom: 0.5rem; }\n      #optimole-app .media .media .media {\n        padding-top: 0.5rem; }\n        #optimole-app .media .media .media + .media {\n          margin-top: 0.5rem; }\n    #optimole-app .media + .media {\n      border-top: 1px solid rgba(219, 219, 219, 0.5);\n      margin-top: 1rem;\n      padding-top: 1rem; }\n    #optimole-app .media.is-large + .media {\n      margin-top: 1.5rem;\n      padding-top: 1.5rem; }\n  #optimole-app .media-left,\n  #optimole-app .media-right {\n    -ms-flex-preferred-size: auto;\n        flex-basis: auto;\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0; }\n  #optimole-app .media-left {\n    margin-right: 1rem; }\n  #optimole-app .media-right {\n    margin-left: 1rem; }\n  #optimole-app .media-content {\n    -ms-flex-preferred-size: auto;\n        flex-basis: auto;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 1;\n        flex-shrink: 1;\n    text-align: left; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .media-content {\n      overflow-x: auto; } }\n  #optimole-app .menu {\n    font-size: 1rem; }\n    #optimole-app .menu.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .menu.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .menu.is-large {\n      font-size: 1.5rem; }\n  #optimole-app .menu-list {\n    line-height: 1.25; }\n    #optimole-app .menu-list a {\n      border-radius: 2px;\n      color: #4a4a4a;\n      display: block;\n      padding: 0.5em 0.75em; }\n      #optimole-app .menu-list a:hover {\n        background-color: whitesmoke;\n        color: #363636; }\n      #optimole-app .menu-list a.is-active {\n        background-color: #3273dc;\n        color: #fff; }\n    #optimole-app .menu-list li ul {\n      border-left: 1px solid #dbdbdb;\n      margin: 0.75em;\n      padding-left: 0.75em; }\n  #optimole-app .menu-label {\n    color: #7a7a7a;\n    font-size: 0.75em;\n    letter-spacing: 0.1em;\n    text-transform: uppercase; }\n    #optimole-app .menu-label:not(:first-child) {\n      margin-top: 1em; }\n    #optimole-app .menu-label:not(:last-child) {\n      margin-bottom: 1em; }\n  #optimole-app .message {\n    background-color: whitesmoke;\n    border-radius: 4px;\n    font-size: 1rem; }\n    #optimole-app .message strong {\n      color: currentColor; }\n    #optimole-app .message a:not(.button):not(.tag):not(.dropdown-item) {\n      color: currentColor;\n      text-decoration: underline; }\n    #optimole-app .message.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .message.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .message.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .message.is-white {\n      background-color: white; }\n      #optimole-app .message.is-white .message-header {\n        background-color: white;\n        color: #0a0a0a; }\n      #optimole-app .message.is-white .message-body {\n        border-color: white;\n        color: #4d4d4d; }\n    #optimole-app .message.is-black {\n      background-color: #fafafa; }\n      #optimole-app .message.is-black .message-header {\n        background-color: #0a0a0a;\n        color: white; }\n      #optimole-app .message.is-black .message-body {\n        border-color: #0a0a0a;\n        color: #090909; }\n    #optimole-app .message.is-light {\n      background-color: #fafafa; }\n      #optimole-app .message.is-light .message-header {\n        background-color: whitesmoke;\n        color: #363636; }\n      #optimole-app .message.is-light .message-body {\n        border-color: whitesmoke;\n        color: #505050; }\n    #optimole-app .message.is-dark {\n      background-color: #fafafa; }\n      #optimole-app .message.is-dark .message-header {\n        background-color: #363636;\n        color: whitesmoke; }\n      #optimole-app .message.is-dark .message-body {\n        border-color: #363636;\n        color: #2a2a2a; }\n    #optimole-app .message.is-primary {\n      background-color: #fef6f6; }\n      #optimole-app .message.is-primary .message-header {\n        background-color: #EF686B;\n        color: #fff; }\n      #optimole-app .message.is-primary .message-body {\n        border-color: #EF686B;\n        color: #bd2124; }\n    #optimole-app .message.is-link {\n      background-color: #f6f9fe; }\n      #optimole-app .message.is-link .message-header {\n        background-color: #3273dc;\n        color: #fff; }\n      #optimole-app .message.is-link .message-body {\n        border-color: #3273dc;\n        color: #22509a; }\n    #optimole-app .message.is-info {\n      background-color: #f7fafc; }\n      #optimole-app .message.is-info .message-header {\n        background-color: #5180C1;\n        color: #fff; }\n      #optimole-app .message.is-info .message-body {\n        border-color: #5180C1;\n        color: #36537c; }\n    #optimole-app .message.is-success {\n      background-color: #f7fdf9; }\n      #optimole-app .message.is-success .message-header {\n        background-color: #34a85e;\n        color: #fff; }\n      #optimole-app .message.is-success .message-body {\n        border-color: #34a85e;\n        color: #1b432a; }\n    #optimole-app .message.is-warning {\n      background-color: #fffdf5; }\n      #optimole-app .message.is-warning .message-header {\n        background-color: #ffdd57;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .message.is-warning .message-body {\n        border-color: #ffdd57;\n        color: #3b3108; }\n    #optimole-app .message.is-danger {\n      background-color: #fef8f6; }\n      #optimole-app .message.is-danger .message-header {\n        background-color: #D54222;\n        color: #fff; }\n      #optimole-app .message.is-danger .message-body {\n        border-color: #D54222;\n        color: #8d311d; }\n  #optimole-app .message-header {\n    -ms-flex-align: center;\n        align-items: center;\n    background-color: #4a4a4a;\n    border-radius: 4px 4px 0 0;\n    color: #fff;\n    display: -ms-flexbox;\n    display: flex;\n    font-weight: 700;\n    -ms-flex-pack: justify;\n        justify-content: space-between;\n    line-height: 1.25;\n    padding: 0.75em 1em;\n    position: relative; }\n    #optimole-app .message-header .delete {\n      -ms-flex-positive: 0;\n          flex-grow: 0;\n      -ms-flex-negative: 0;\n          flex-shrink: 0;\n      margin-left: 0.75em; }\n    #optimole-app .message-header + .message-body {\n      border-width: 0;\n      border-top-left-radius: 0;\n      border-top-right-radius: 0; }\n  #optimole-app .message-body {\n    border-color: #dbdbdb;\n    border-radius: 4px;\n    border-style: solid;\n    border-width: 0 0 0 4px;\n    color: #4a4a4a;\n    padding: 1.25em 1.5em; }\n    #optimole-app .message-body code,\n    #optimole-app .message-body pre {\n      background-color: white; }\n    #optimole-app .message-body pre code {\n      background-color: transparent; }\n  #optimole-app .modal {\n    -ms-flex-align: center;\n        align-items: center;\n    display: none;\n    -ms-flex-direction: column;\n        flex-direction: column;\n    -ms-flex-pack: center;\n        justify-content: center;\n    overflow: hidden;\n    position: fixed;\n    z-index: 40; }\n    #optimole-app .modal.is-active {\n      display: -ms-flexbox;\n      display: flex; }\n  #optimole-app .modal-background {\n    background-color: rgba(10, 10, 10, 0.86); }\n  #optimole-app .modal-content,\n  #optimole-app .modal-card {\n    margin: 0 20px;\n    max-height: calc(100vh - 160px);\n    overflow: auto;\n    position: relative;\n    width: 100%; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .modal-content,\n      #optimole-app .modal-card {\n        margin: 0 auto;\n        max-height: calc(100vh - 40px);\n        width: 640px; } }\n  #optimole-app .modal-close {\n    background: none;\n    height: 40px;\n    position: fixed;\n    right: 20px;\n    top: 20px;\n    width: 40px; }\n  #optimole-app .modal-card {\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-direction: column;\n        flex-direction: column;\n    max-height: calc(100vh - 40px);\n    overflow: hidden;\n    -ms-overflow-y: visible; }\n  #optimole-app .modal-card-head,\n  #optimole-app .modal-card-foot {\n    -ms-flex-align: center;\n        align-items: center;\n    background-color: whitesmoke;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    -ms-flex-pack: start;\n        justify-content: flex-start;\n    padding: 20px;\n    position: relative; }\n  #optimole-app .modal-card-head {\n    border-bottom: 1px solid #dbdbdb;\n    border-top-left-radius: 6px;\n    border-top-right-radius: 6px; }\n  #optimole-app .modal-card-title {\n    color: #363636;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    font-size: 1.5rem;\n    line-height: 1; }\n  #optimole-app .modal-card-foot {\n    border-bottom-left-radius: 6px;\n    border-bottom-right-radius: 6px;\n    border-top: 1px solid #dbdbdb; }\n    #optimole-app .modal-card-foot .button:not(:last-child) {\n      margin-right: 10px; }\n  #optimole-app .modal-card-body {\n    -webkit-overflow-scrolling: touch;\n    background-color: white;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 1;\n        flex-shrink: 1;\n    overflow: auto;\n    padding: 20px; }\n  #optimole-app .navbar {\n    background-color: white;\n    min-height: 3.25rem;\n    position: relative;\n    z-index: 30; }\n    #optimole-app .navbar.is-white {\n      background-color: white;\n      color: #0a0a0a; }\n      #optimole-app .navbar.is-white .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-white .navbar-brand .navbar-link {\n        color: #0a0a0a; }\n      #optimole-app .navbar.is-white .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-white .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-white .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-white .navbar-brand .navbar-link.is-active {\n        background-color: #f2f2f2;\n        color: #0a0a0a; }\n      #optimole-app .navbar.is-white .navbar-brand .navbar-link::after {\n        border-color: #0a0a0a; }\n      #optimole-app .navbar.is-white .navbar-burger {\n        color: #0a0a0a; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-white .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-white .navbar-start .navbar-link,\n        #optimole-app .navbar.is-white .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-white .navbar-end .navbar-link {\n          color: #0a0a0a; }\n        #optimole-app .navbar.is-white .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-white .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-white .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-white .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-white .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-white .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-white .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-white .navbar-end .navbar-link.is-active {\n          background-color: #f2f2f2;\n          color: #0a0a0a; }\n        #optimole-app .navbar.is-white .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-white .navbar-end .navbar-link::after {\n          border-color: #0a0a0a; }\n        #optimole-app .navbar.is-white .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-white .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #f2f2f2;\n          color: #0a0a0a; }\n        #optimole-app .navbar.is-white .navbar-dropdown a.navbar-item.is-active {\n          background-color: white;\n          color: #0a0a0a; } }\n    #optimole-app .navbar.is-black {\n      background-color: #0a0a0a;\n      color: white; }\n      #optimole-app .navbar.is-black .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-black .navbar-brand .navbar-link {\n        color: white; }\n      #optimole-app .navbar.is-black .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-black .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-black .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-black .navbar-brand .navbar-link.is-active {\n        background-color: black;\n        color: white; }\n      #optimole-app .navbar.is-black .navbar-brand .navbar-link::after {\n        border-color: white; }\n      #optimole-app .navbar.is-black .navbar-burger {\n        color: white; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-black .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-black .navbar-start .navbar-link,\n        #optimole-app .navbar.is-black .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-black .navbar-end .navbar-link {\n          color: white; }\n        #optimole-app .navbar.is-black .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-black .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-black .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-black .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-black .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-black .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-black .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-black .navbar-end .navbar-link.is-active {\n          background-color: black;\n          color: white; }\n        #optimole-app .navbar.is-black .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-black .navbar-end .navbar-link::after {\n          border-color: white; }\n        #optimole-app .navbar.is-black .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-black .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: black;\n          color: white; }\n        #optimole-app .navbar.is-black .navbar-dropdown a.navbar-item.is-active {\n          background-color: #0a0a0a;\n          color: white; } }\n    #optimole-app .navbar.is-light {\n      background-color: whitesmoke;\n      color: #363636; }\n      #optimole-app .navbar.is-light .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-light .navbar-brand .navbar-link {\n        color: #363636; }\n      #optimole-app .navbar.is-light .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-light .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-light .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-light .navbar-brand .navbar-link.is-active {\n        background-color: #e8e8e8;\n        color: #363636; }\n      #optimole-app .navbar.is-light .navbar-brand .navbar-link::after {\n        border-color: #363636; }\n      #optimole-app .navbar.is-light .navbar-burger {\n        color: #363636; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-light .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-light .navbar-start .navbar-link,\n        #optimole-app .navbar.is-light .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-light .navbar-end .navbar-link {\n          color: #363636; }\n        #optimole-app .navbar.is-light .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-light .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-light .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-light .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-light .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-light .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-light .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-light .navbar-end .navbar-link.is-active {\n          background-color: #e8e8e8;\n          color: #363636; }\n        #optimole-app .navbar.is-light .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-light .navbar-end .navbar-link::after {\n          border-color: #363636; }\n        #optimole-app .navbar.is-light .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-light .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #e8e8e8;\n          color: #363636; }\n        #optimole-app .navbar.is-light .navbar-dropdown a.navbar-item.is-active {\n          background-color: whitesmoke;\n          color: #363636; } }\n    #optimole-app .navbar.is-dark {\n      background-color: #363636;\n      color: whitesmoke; }\n      #optimole-app .navbar.is-dark .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-dark .navbar-brand .navbar-link {\n        color: whitesmoke; }\n      #optimole-app .navbar.is-dark .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-dark .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-dark .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-dark .navbar-brand .navbar-link.is-active {\n        background-color: #292929;\n        color: whitesmoke; }\n      #optimole-app .navbar.is-dark .navbar-brand .navbar-link::after {\n        border-color: whitesmoke; }\n      #optimole-app .navbar.is-dark .navbar-burger {\n        color: whitesmoke; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-dark .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-dark .navbar-start .navbar-link,\n        #optimole-app .navbar.is-dark .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-dark .navbar-end .navbar-link {\n          color: whitesmoke; }\n        #optimole-app .navbar.is-dark .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-dark .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-dark .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-dark .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-dark .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-dark .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-dark .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-dark .navbar-end .navbar-link.is-active {\n          background-color: #292929;\n          color: whitesmoke; }\n        #optimole-app .navbar.is-dark .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-dark .navbar-end .navbar-link::after {\n          border-color: whitesmoke; }\n        #optimole-app .navbar.is-dark .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-dark .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #292929;\n          color: whitesmoke; }\n        #optimole-app .navbar.is-dark .navbar-dropdown a.navbar-item.is-active {\n          background-color: #363636;\n          color: whitesmoke; } }\n    #optimole-app .navbar.is-primary {\n      background-color: #EF686B;\n      color: #fff; }\n      #optimole-app .navbar.is-primary .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-primary .navbar-brand .navbar-link {\n        color: #fff; }\n      #optimole-app .navbar.is-primary .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-primary .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-primary .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-primary .navbar-brand .navbar-link.is-active {\n        background-color: #ed5154;\n        color: #fff; }\n      #optimole-app .navbar.is-primary .navbar-brand .navbar-link::after {\n        border-color: #fff; }\n      #optimole-app .navbar.is-primary .navbar-burger {\n        color: #fff; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-primary .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-primary .navbar-start .navbar-link,\n        #optimole-app .navbar.is-primary .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-primary .navbar-end .navbar-link {\n          color: #fff; }\n        #optimole-app .navbar.is-primary .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-primary .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-primary .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-primary .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-primary .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-primary .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-primary .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-primary .navbar-end .navbar-link.is-active {\n          background-color: #ed5154;\n          color: #fff; }\n        #optimole-app .navbar.is-primary .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-primary .navbar-end .navbar-link::after {\n          border-color: #fff; }\n        #optimole-app .navbar.is-primary .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-primary .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #ed5154;\n          color: #fff; }\n        #optimole-app .navbar.is-primary .navbar-dropdown a.navbar-item.is-active {\n          background-color: #EF686B;\n          color: #fff; } }\n    #optimole-app .navbar.is-link {\n      background-color: #3273dc;\n      color: #fff; }\n      #optimole-app .navbar.is-link .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-link .navbar-brand .navbar-link {\n        color: #fff; }\n      #optimole-app .navbar.is-link .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-link .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-link .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-link .navbar-brand .navbar-link.is-active {\n        background-color: #2366d1;\n        color: #fff; }\n      #optimole-app .navbar.is-link .navbar-brand .navbar-link::after {\n        border-color: #fff; }\n      #optimole-app .navbar.is-link .navbar-burger {\n        color: #fff; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-link .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-link .navbar-start .navbar-link,\n        #optimole-app .navbar.is-link .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-link .navbar-end .navbar-link {\n          color: #fff; }\n        #optimole-app .navbar.is-link .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-link .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-link .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-link .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-link .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-link .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-link .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-link .navbar-end .navbar-link.is-active {\n          background-color: #2366d1;\n          color: #fff; }\n        #optimole-app .navbar.is-link .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-link .navbar-end .navbar-link::after {\n          border-color: #fff; }\n        #optimole-app .navbar.is-link .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-link .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #2366d1;\n          color: #fff; }\n        #optimole-app .navbar.is-link .navbar-dropdown a.navbar-item.is-active {\n          background-color: #3273dc;\n          color: #fff; } }\n    #optimole-app .navbar.is-info {\n      background-color: #5180C1;\n      color: #fff; }\n      #optimole-app .navbar.is-info .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-info .navbar-brand .navbar-link {\n        color: #fff; }\n      #optimole-app .navbar.is-info .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-info .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-info .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-info .navbar-brand .navbar-link.is-active {\n        background-color: #4173b7;\n        color: #fff; }\n      #optimole-app .navbar.is-info .navbar-brand .navbar-link::after {\n        border-color: #fff; }\n      #optimole-app .navbar.is-info .navbar-burger {\n        color: #fff; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-info .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-info .navbar-start .navbar-link,\n        #optimole-app .navbar.is-info .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-info .navbar-end .navbar-link {\n          color: #fff; }\n        #optimole-app .navbar.is-info .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-info .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-info .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-info .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-info .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-info .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-info .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-info .navbar-end .navbar-link.is-active {\n          background-color: #4173b7;\n          color: #fff; }\n        #optimole-app .navbar.is-info .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-info .navbar-end .navbar-link::after {\n          border-color: #fff; }\n        #optimole-app .navbar.is-info .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-info .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #4173b7;\n          color: #fff; }\n        #optimole-app .navbar.is-info .navbar-dropdown a.navbar-item.is-active {\n          background-color: #5180C1;\n          color: #fff; } }\n    #optimole-app .navbar.is-success {\n      background-color: #34a85e;\n      color: #fff; }\n      #optimole-app .navbar.is-success .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-success .navbar-brand .navbar-link {\n        color: #fff; }\n      #optimole-app .navbar.is-success .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-success .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-success .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-success .navbar-brand .navbar-link.is-active {\n        background-color: #2e9553;\n        color: #fff; }\n      #optimole-app .navbar.is-success .navbar-brand .navbar-link::after {\n        border-color: #fff; }\n      #optimole-app .navbar.is-success .navbar-burger {\n        color: #fff; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-success .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-success .navbar-start .navbar-link,\n        #optimole-app .navbar.is-success .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-success .navbar-end .navbar-link {\n          color: #fff; }\n        #optimole-app .navbar.is-success .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-success .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-success .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-success .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-success .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-success .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-success .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-success .navbar-end .navbar-link.is-active {\n          background-color: #2e9553;\n          color: #fff; }\n        #optimole-app .navbar.is-success .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-success .navbar-end .navbar-link::after {\n          border-color: #fff; }\n        #optimole-app .navbar.is-success .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-success .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #2e9553;\n          color: #fff; }\n        #optimole-app .navbar.is-success .navbar-dropdown a.navbar-item.is-active {\n          background-color: #34a85e;\n          color: #fff; } }\n    #optimole-app .navbar.is-warning {\n      background-color: #ffdd57;\n      color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .navbar.is-warning .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-warning .navbar-brand .navbar-link {\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .navbar.is-warning .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-warning .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-warning .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-warning .navbar-brand .navbar-link.is-active {\n        background-color: #ffd83d;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .navbar.is-warning .navbar-brand .navbar-link::after {\n        border-color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .navbar.is-warning .navbar-burger {\n        color: rgba(0, 0, 0, 0.7); }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-warning .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-warning .navbar-start .navbar-link,\n        #optimole-app .navbar.is-warning .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-warning .navbar-end .navbar-link {\n          color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .navbar.is-warning .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-warning .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-warning .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-warning .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-warning .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-warning .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-warning .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-warning .navbar-end .navbar-link.is-active {\n          background-color: #ffd83d;\n          color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .navbar.is-warning .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-warning .navbar-end .navbar-link::after {\n          border-color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .navbar.is-warning .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-warning .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #ffd83d;\n          color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .navbar.is-warning .navbar-dropdown a.navbar-item.is-active {\n          background-color: #ffdd57;\n          color: rgba(0, 0, 0, 0.7); } }\n    #optimole-app .navbar.is-danger {\n      background-color: #D54222;\n      color: #fff; }\n      #optimole-app .navbar.is-danger .navbar-brand > .navbar-item,\n      #optimole-app .navbar.is-danger .navbar-brand .navbar-link {\n        color: #fff; }\n      #optimole-app .navbar.is-danger .navbar-brand > a.navbar-item:hover, #optimole-app .navbar.is-danger .navbar-brand > a.navbar-item.is-active,\n      #optimole-app .navbar.is-danger .navbar-brand .navbar-link:hover,\n      #optimole-app .navbar.is-danger .navbar-brand .navbar-link.is-active {\n        background-color: #bf3b1e;\n        color: #fff; }\n      #optimole-app .navbar.is-danger .navbar-brand .navbar-link::after {\n        border-color: #fff; }\n      #optimole-app .navbar.is-danger .navbar-burger {\n        color: #fff; }\n      @media screen and (min-width: 1088px) {\n        #optimole-app .navbar.is-danger .navbar-start > .navbar-item,\n        #optimole-app .navbar.is-danger .navbar-start .navbar-link,\n        #optimole-app .navbar.is-danger .navbar-end > .navbar-item,\n        #optimole-app .navbar.is-danger .navbar-end .navbar-link {\n          color: #fff; }\n        #optimole-app .navbar.is-danger .navbar-start > a.navbar-item:hover, #optimole-app .navbar.is-danger .navbar-start > a.navbar-item.is-active,\n        #optimole-app .navbar.is-danger .navbar-start .navbar-link:hover,\n        #optimole-app .navbar.is-danger .navbar-start .navbar-link.is-active,\n        #optimole-app .navbar.is-danger .navbar-end > a.navbar-item:hover,\n        #optimole-app .navbar.is-danger .navbar-end > a.navbar-item.is-active,\n        #optimole-app .navbar.is-danger .navbar-end .navbar-link:hover,\n        #optimole-app .navbar.is-danger .navbar-end .navbar-link.is-active {\n          background-color: #bf3b1e;\n          color: #fff; }\n        #optimole-app .navbar.is-danger .navbar-start .navbar-link::after,\n        #optimole-app .navbar.is-danger .navbar-end .navbar-link::after {\n          border-color: #fff; }\n        #optimole-app .navbar.is-danger .navbar-item.has-dropdown:hover .navbar-link,\n        #optimole-app .navbar.is-danger .navbar-item.has-dropdown.is-active .navbar-link {\n          background-color: #bf3b1e;\n          color: #fff; }\n        #optimole-app .navbar.is-danger .navbar-dropdown a.navbar-item.is-active {\n          background-color: #D54222;\n          color: #fff; } }\n    #optimole-app .navbar > .container {\n      -ms-flex-align: stretch;\n          align-items: stretch;\n      display: -ms-flexbox;\n      display: flex;\n      min-height: 3.25rem;\n      width: 100%; }\n    #optimole-app .navbar.has-shadow {\n      box-shadow: 0 2px 0 0 whitesmoke; }\n    #optimole-app .navbar.is-fixed-bottom, #optimole-app .navbar.is-fixed-top {\n      left: 0;\n      position: fixed;\n      right: 0;\n      z-index: 30; }\n    #optimole-app .navbar.is-fixed-bottom {\n      bottom: 0; }\n      #optimole-app .navbar.is-fixed-bottom.has-shadow {\n        box-shadow: 0 -2px 0 0 whitesmoke; }\n    #optimole-app .navbar.is-fixed-top {\n      top: 0; }\n  #optimole-app html.has-navbar-fixed-top,\n  #optimole-app body.has-navbar-fixed-top {\n    padding-top: 3.25rem; }\n  #optimole-app html.has-navbar-fixed-bottom,\n  #optimole-app body.has-navbar-fixed-bottom {\n    padding-bottom: 3.25rem; }\n  #optimole-app .navbar-brand,\n  #optimole-app .navbar-tabs {\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    min-height: 3.25rem; }\n  #optimole-app .navbar-brand a.navbar-item:hover {\n    background-color: transparent; }\n  #optimole-app .navbar-tabs {\n    -webkit-overflow-scrolling: touch;\n    max-width: 100vw;\n    overflow-x: auto;\n    overflow-y: hidden; }\n  #optimole-app .navbar-burger {\n    color: #4a4a4a;\n    cursor: pointer;\n    display: block;\n    height: 3.25rem;\n    position: relative;\n    width: 3.25rem;\n    margin-left: auto; }\n    #optimole-app .navbar-burger span {\n      background-color: currentColor;\n      display: block;\n      height: 1px;\n      left: calc(50% - 8px);\n      position: absolute;\n      transform-origin: center;\n      transition-duration: 86ms;\n      transition-property: background-color, opacity, transform;\n      transition-timing-function: ease-out;\n      width: 16px; }\n      #optimole-app .navbar-burger span:nth-child(1) {\n        top: calc(50% - 6px); }\n      #optimole-app .navbar-burger span:nth-child(2) {\n        top: calc(50% - 1px); }\n      #optimole-app .navbar-burger span:nth-child(3) {\n        top: calc(50% + 4px); }\n    #optimole-app .navbar-burger:hover {\n      background-color: rgba(0, 0, 0, 0.05); }\n    #optimole-app .navbar-burger.is-active span:nth-child(1) {\n      transform: translateY(5px) rotate(45deg); }\n    #optimole-app .navbar-burger.is-active span:nth-child(2) {\n      opacity: 0; }\n    #optimole-app .navbar-burger.is-active span:nth-child(3) {\n      transform: translateY(-5px) rotate(-45deg); }\n  #optimole-app .navbar-menu {\n    display: none; }\n  #optimole-app .navbar-item,\n  #optimole-app .navbar-link {\n    color: #4a4a4a;\n    display: block;\n    line-height: 1.5;\n    padding: 0.5rem 0.75rem;\n    position: relative; }\n    #optimole-app .navbar-item .icon:only-child,\n    #optimole-app .navbar-link .icon:only-child {\n      margin-left: -0.25rem;\n      margin-right: -0.25rem; }\n  #optimole-app a.navbar-item,\n  #optimole-app .navbar-link {\n    cursor: pointer; }\n    #optimole-app a.navbar-item:hover, #optimole-app a.navbar-item.is-active,\n    #optimole-app .navbar-link:hover,\n    #optimole-app .navbar-link.is-active {\n      background-color: #fafafa;\n      color: #3273dc; }\n  #optimole-app .navbar-item {\n    display: block;\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0; }\n    #optimole-app .navbar-item img {\n      max-height: 1.75rem; }\n    #optimole-app .navbar-item.has-dropdown {\n      padding: 0; }\n    #optimole-app .navbar-item.is-expanded {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 1;\n          flex-shrink: 1; }\n    #optimole-app .navbar-item.is-tab {\n      border-bottom: 1px solid transparent;\n      min-height: 3.25rem;\n      padding-bottom: calc(0.5rem - 1px); }\n      #optimole-app .navbar-item.is-tab:hover {\n        background-color: transparent;\n        border-bottom-color: #3273dc; }\n      #optimole-app .navbar-item.is-tab.is-active {\n        background-color: transparent;\n        border-bottom-color: #3273dc;\n        border-bottom-style: solid;\n        border-bottom-width: 3px;\n        color: #3273dc;\n        padding-bottom: calc(0.5rem - 3px); }\n  #optimole-app .navbar-content {\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 1;\n        flex-shrink: 1; }\n  #optimole-app .navbar-link:not(.is-arrowless) {\n    padding-right: 2.5em; }\n    #optimole-app .navbar-link:not(.is-arrowless)::after {\n      border-color: #3273dc;\n      margin-top: -0.375em;\n      right: 1.125em; }\n  #optimole-app .navbar-dropdown {\n    font-size: 0.875rem;\n    padding-bottom: 0.5rem;\n    padding-top: 0.5rem; }\n    #optimole-app .navbar-dropdown .navbar-item {\n      padding-left: 1.5rem;\n      padding-right: 1.5rem; }\n  #optimole-app .navbar-divider {\n    background-color: whitesmoke;\n    border: none;\n    display: none;\n    height: 2px;\n    margin: 0.5rem 0; }\n  @media screen and (max-width: 1087px) {\n    #optimole-app .navbar > .container {\n      display: block; }\n    #optimole-app .navbar-brand .navbar-item,\n    #optimole-app .navbar-tabs .navbar-item {\n      -ms-flex-align: center;\n          align-items: center;\n      display: -ms-flexbox;\n      display: flex; }\n    #optimole-app .navbar-link::after {\n      display: none; }\n    #optimole-app .navbar-menu {\n      background-color: white;\n      box-shadow: 0 8px 16px rgba(10, 10, 10, 0.1);\n      padding: 0.5rem 0; }\n      #optimole-app .navbar-menu.is-active {\n        display: block; }\n    #optimole-app .navbar.is-fixed-bottom-touch, #optimole-app .navbar.is-fixed-top-touch {\n      left: 0;\n      position: fixed;\n      right: 0;\n      z-index: 30; }\n    #optimole-app .navbar.is-fixed-bottom-touch {\n      bottom: 0; }\n      #optimole-app .navbar.is-fixed-bottom-touch.has-shadow {\n        box-shadow: 0 -2px 3px rgba(10, 10, 10, 0.1); }\n    #optimole-app .navbar.is-fixed-top-touch {\n      top: 0; }\n    #optimole-app .navbar.is-fixed-top .navbar-menu, #optimole-app .navbar.is-fixed-top-touch .navbar-menu {\n      -webkit-overflow-scrolling: touch;\n      max-height: calc(100vh - 3.25rem);\n      overflow: auto; }\n    #optimole-app html.has-navbar-fixed-top-touch,\n    #optimole-app body.has-navbar-fixed-top-touch {\n      padding-top: 3.25rem; }\n    #optimole-app html.has-navbar-fixed-bottom-touch,\n    #optimole-app body.has-navbar-fixed-bottom-touch {\n      padding-bottom: 3.25rem; } }\n  @media screen and (min-width: 1088px) {\n    #optimole-app .navbar,\n    #optimole-app .navbar-menu,\n    #optimole-app .navbar-start,\n    #optimole-app .navbar-end {\n      -ms-flex-align: stretch;\n          align-items: stretch;\n      display: -ms-flexbox;\n      display: flex; }\n    #optimole-app .navbar {\n      min-height: 3.25rem; }\n      #optimole-app .navbar.is-spaced {\n        padding: 1rem 2rem; }\n        #optimole-app .navbar.is-spaced .navbar-start,\n        #optimole-app .navbar.is-spaced .navbar-end {\n          -ms-flex-align: center;\n              align-items: center; }\n        #optimole-app .navbar.is-spaced a.navbar-item,\n        #optimole-app .navbar.is-spaced .navbar-link {\n          border-radius: 4px; }\n      #optimole-app .navbar.is-transparent a.navbar-item:hover, #optimole-app .navbar.is-transparent a.navbar-item.is-active,\n      #optimole-app .navbar.is-transparent .navbar-link:hover,\n      #optimole-app .navbar.is-transparent .navbar-link.is-active {\n        background-color: transparent !important; }\n      #optimole-app .navbar.is-transparent .navbar-item.has-dropdown.is-active .navbar-link, #optimole-app .navbar.is-transparent .navbar-item.has-dropdown.is-hoverable:hover .navbar-link {\n        background-color: transparent !important; }\n      #optimole-app .navbar.is-transparent .navbar-dropdown a.navbar-item:hover {\n        background-color: whitesmoke;\n        color: #0a0a0a; }\n      #optimole-app .navbar.is-transparent .navbar-dropdown a.navbar-item.is-active {\n        background-color: whitesmoke;\n        color: #3273dc; }\n    #optimole-app .navbar-burger {\n      display: none; }\n    #optimole-app .navbar-item,\n    #optimole-app .navbar-link {\n      -ms-flex-align: center;\n          align-items: center;\n      display: -ms-flexbox;\n      display: flex; }\n    #optimole-app .navbar-item {\n      display: -ms-flexbox;\n      display: flex; }\n      #optimole-app .navbar-item.has-dropdown {\n        -ms-flex-align: stretch;\n            align-items: stretch; }\n      #optimole-app .navbar-item.has-dropdown-up .navbar-link::after {\n        transform: rotate(135deg) translate(0.25em, -0.25em); }\n      #optimole-app .navbar-item.has-dropdown-up .navbar-dropdown {\n        border-bottom: 2px solid #dbdbdb;\n        border-radius: 6px 6px 0 0;\n        border-top: none;\n        bottom: 100%;\n        box-shadow: 0 -8px 8px rgba(10, 10, 10, 0.1);\n        top: auto; }\n      #optimole-app .navbar-item.is-active .navbar-dropdown, #optimole-app .navbar-item.is-hoverable:hover .navbar-dropdown {\n        display: block; }\n        .navbar.is-spaced #optimole-app .navbar-item.is-active .navbar-dropdown, #optimole-app .navbar-item.is-active .navbar-dropdown.is-boxed, .navbar.is-spaced #optimole-app .navbar-item.is-hoverable:hover .navbar-dropdown, #optimole-app .navbar-item.is-hoverable:hover .navbar-dropdown.is-boxed {\n          opacity: 1;\n          pointer-events: auto;\n          transform: translateY(0); }\n    #optimole-app .navbar-menu {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 0;\n          flex-shrink: 0; }\n    #optimole-app .navbar-start {\n      -ms-flex-pack: start;\n          justify-content: flex-start;\n      margin-right: auto; }\n    #optimole-app .navbar-end {\n      -ms-flex-pack: end;\n          justify-content: flex-end;\n      margin-left: auto; }\n    #optimole-app .navbar-dropdown {\n      background-color: white;\n      border-bottom-left-radius: 6px;\n      border-bottom-right-radius: 6px;\n      border-top: 2px solid #dbdbdb;\n      box-shadow: 0 8px 8px rgba(10, 10, 10, 0.1);\n      display: none;\n      font-size: 0.875rem;\n      left: 0;\n      min-width: 100%;\n      position: absolute;\n      top: 100%;\n      z-index: 20; }\n      #optimole-app .navbar-dropdown .navbar-item {\n        padding: 0.375rem 1rem;\n        white-space: nowrap; }\n      #optimole-app .navbar-dropdown a.navbar-item {\n        padding-right: 3rem; }\n        #optimole-app .navbar-dropdown a.navbar-item:hover {\n          background-color: whitesmoke;\n          color: #0a0a0a; }\n        #optimole-app .navbar-dropdown a.navbar-item.is-active {\n          background-color: whitesmoke;\n          color: #3273dc; }\n      .navbar.is-spaced #optimole-app .navbar-dropdown, #optimole-app .navbar-dropdown.is-boxed {\n        border-radius: 6px;\n        border-top: none;\n        box-shadow: 0 8px 8px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);\n        display: block;\n        opacity: 0;\n        pointer-events: none;\n        top: calc(100% + (-4px));\n        transform: translateY(-5px);\n        transition-duration: 86ms;\n        transition-property: opacity, transform; }\n      #optimole-app .navbar-dropdown.is-right {\n        left: auto;\n        right: 0; }\n    #optimole-app .navbar-divider {\n      display: block; }\n    #optimole-app .navbar > .container .navbar-brand,\n    #optimole-app .container > .navbar .navbar-brand {\n      margin-left: -.75rem; }\n    #optimole-app .navbar > .container .navbar-menu,\n    #optimole-app .container > .navbar .navbar-menu {\n      margin-right: -.75rem; }\n    #optimole-app .navbar.is-fixed-bottom-desktop, #optimole-app .navbar.is-fixed-top-desktop {\n      left: 0;\n      position: fixed;\n      right: 0;\n      z-index: 30; }\n    #optimole-app .navbar.is-fixed-bottom-desktop {\n      bottom: 0; }\n      #optimole-app .navbar.is-fixed-bottom-desktop.has-shadow {\n        box-shadow: 0 -2px 3px rgba(10, 10, 10, 0.1); }\n    #optimole-app .navbar.is-fixed-top-desktop {\n      top: 0; }\n    #optimole-app html.has-navbar-fixed-top-desktop,\n    #optimole-app body.has-navbar-fixed-top-desktop {\n      padding-top: 3.25rem; }\n    #optimole-app html.has-navbar-fixed-bottom-desktop,\n    #optimole-app body.has-navbar-fixed-bottom-desktop {\n      padding-bottom: 3.25rem; }\n    #optimole-app html.has-spaced-navbar-fixed-top,\n    #optimole-app body.has-spaced-navbar-fixed-top {\n      padding-top: 5.25rem; }\n    #optimole-app html.has-spaced-navbar-fixed-bottom,\n    #optimole-app body.has-spaced-navbar-fixed-bottom {\n      padding-bottom: 5.25rem; }\n    #optimole-app a.navbar-item.is-active,\n    #optimole-app .navbar-link.is-active {\n      color: #0a0a0a; }\n    #optimole-app a.navbar-item.is-active:not(:hover),\n    #optimole-app .navbar-link.is-active:not(:hover) {\n      background-color: transparent; }\n    #optimole-app .navbar-item.has-dropdown:hover .navbar-link, #optimole-app .navbar-item.has-dropdown.is-active .navbar-link {\n      background-color: #fafafa; } }\n  #optimole-app .hero.is-fullheight-with-navbar {\n    min-height: calc(100vh - 3.25rem); }\n  #optimole-app .pagination {\n    font-size: 1rem;\n    margin: -0.25rem; }\n    #optimole-app .pagination.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .pagination.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .pagination.is-large {\n      font-size: 1.5rem; }\n    #optimole-app .pagination.is-rounded .pagination-previous,\n    #optimole-app .pagination.is-rounded .pagination-next {\n      padding-left: 1em;\n      padding-right: 1em;\n      border-radius: 290486px; }\n    #optimole-app .pagination.is-rounded .pagination-link {\n      border-radius: 290486px; }\n  #optimole-app .pagination,\n  #optimole-app .pagination-list {\n    -ms-flex-align: center;\n        align-items: center;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: center;\n        justify-content: center;\n    text-align: center; }\n  #optimole-app .pagination-previous,\n  #optimole-app .pagination-next,\n  #optimole-app .pagination-link,\n  #optimole-app .pagination-ellipsis {\n    font-size: 1em;\n    padding-left: 0.5em;\n    padding-right: 0.5em;\n    -ms-flex-pack: center;\n        justify-content: center;\n    margin: 0.25rem;\n    text-align: center; }\n  #optimole-app .pagination-previous,\n  #optimole-app .pagination-next,\n  #optimole-app .pagination-link {\n    border-color: #dbdbdb;\n    color: #363636;\n    min-width: 2.25em; }\n    #optimole-app .pagination-previous:hover,\n    #optimole-app .pagination-next:hover,\n    #optimole-app .pagination-link:hover {\n      border-color: #b5b5b5;\n      color: #363636; }\n    #optimole-app .pagination-previous:focus,\n    #optimole-app .pagination-next:focus,\n    #optimole-app .pagination-link:focus {\n      border-color: #3273dc; }\n    #optimole-app .pagination-previous:active,\n    #optimole-app .pagination-next:active,\n    #optimole-app .pagination-link:active {\n      box-shadow: inset 0 1px 2px rgba(10, 10, 10, 0.2); }\n    #optimole-app .pagination-previous[disabled],\n    #optimole-app .pagination-next[disabled],\n    #optimole-app .pagination-link[disabled] {\n      background-color: #dbdbdb;\n      border-color: #dbdbdb;\n      box-shadow: none;\n      color: #7a7a7a;\n      opacity: 0.5; }\n  #optimole-app .pagination-previous,\n  #optimole-app .pagination-next {\n    padding-left: 0.75em;\n    padding-right: 0.75em;\n    white-space: nowrap; }\n  #optimole-app .pagination-link.is-current {\n    background-color: #3273dc;\n    border-color: #3273dc;\n    color: #fff; }\n  #optimole-app .pagination-ellipsis {\n    color: #b5b5b5;\n    pointer-events: none; }\n  #optimole-app .pagination-list {\n    -ms-flex-wrap: wrap;\n        flex-wrap: wrap; }\n  @media screen and (max-width: 768px) {\n    #optimole-app .pagination {\n      -ms-flex-wrap: wrap;\n          flex-wrap: wrap; }\n    #optimole-app .pagination-previous,\n    #optimole-app .pagination-next {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 1;\n          flex-shrink: 1; }\n    #optimole-app .pagination-list li {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 1;\n          flex-shrink: 1; } }\n  @media screen and (min-width: 769px), print {\n    #optimole-app .pagination-list {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 1;\n          flex-shrink: 1;\n      -ms-flex-pack: start;\n          justify-content: flex-start;\n      -ms-flex-order: 1;\n          order: 1; }\n    #optimole-app .pagination-previous {\n      -ms-flex-order: 2;\n          order: 2; }\n    #optimole-app .pagination-next {\n      -ms-flex-order: 3;\n          order: 3; }\n    #optimole-app .pagination {\n      -ms-flex-pack: justify;\n          justify-content: space-between; }\n      #optimole-app .pagination.is-centered .pagination-previous {\n        -ms-flex-order: 1;\n            order: 1; }\n      #optimole-app .pagination.is-centered .pagination-list {\n        -ms-flex-pack: center;\n            justify-content: center;\n        -ms-flex-order: 2;\n            order: 2; }\n      #optimole-app .pagination.is-centered .pagination-next {\n        -ms-flex-order: 3;\n            order: 3; }\n      #optimole-app .pagination.is-right .pagination-previous {\n        -ms-flex-order: 1;\n            order: 1; }\n      #optimole-app .pagination.is-right .pagination-next {\n        -ms-flex-order: 2;\n            order: 2; }\n      #optimole-app .pagination.is-right .pagination-list {\n        -ms-flex-pack: end;\n            justify-content: flex-end;\n        -ms-flex-order: 3;\n            order: 3; } }\n  #optimole-app .panel {\n    font-size: 1rem; }\n    #optimole-app .panel:not(:last-child) {\n      margin-bottom: 1.5rem; }\n  #optimole-app .panel-heading,\n  #optimole-app .panel-tabs,\n  #optimole-app .panel-block {\n    border-bottom: 1px solid #dbdbdb;\n    border-left: 1px solid #dbdbdb;\n    border-right: 1px solid #dbdbdb; }\n    #optimole-app .panel-heading:first-child,\n    #optimole-app .panel-tabs:first-child,\n    #optimole-app .panel-block:first-child {\n      border-top: 1px solid #dbdbdb; }\n  #optimole-app .panel-heading {\n    background-color: whitesmoke;\n    border-radius: 4px 4px 0 0;\n    color: #363636;\n    font-size: 1.25em;\n    font-weight: 300;\n    line-height: 1.25;\n    padding: 0.5em 0.75em; }\n  #optimole-app .panel-tabs {\n    -ms-flex-align: end;\n        align-items: flex-end;\n    display: -ms-flexbox;\n    display: flex;\n    font-size: 0.875em;\n    -ms-flex-pack: center;\n        justify-content: center; }\n    #optimole-app .panel-tabs a {\n      border-bottom: 1px solid #dbdbdb;\n      margin-bottom: -1px;\n      padding: 0.5em; }\n      #optimole-app .panel-tabs a.is-active {\n        border-bottom-color: #4a4a4a;\n        color: #363636; }\n  #optimole-app .panel-list a {\n    color: #4a4a4a; }\n    #optimole-app .panel-list a:hover {\n      color: #3273dc; }\n  #optimole-app .panel-block {\n    -ms-flex-align: center;\n        align-items: center;\n    color: #363636;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-pack: start;\n        justify-content: flex-start;\n    padding: 0.5em 0.75em; }\n    #optimole-app .panel-block input[type=\"checkbox\"] {\n      margin-right: 0.75em; }\n    #optimole-app .panel-block > .control {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 1;\n          flex-shrink: 1;\n      width: 100%; }\n    #optimole-app .panel-block.is-wrapped {\n      -ms-flex-wrap: wrap;\n          flex-wrap: wrap; }\n    #optimole-app .panel-block.is-active {\n      border-left-color: #3273dc;\n      color: #363636; }\n      #optimole-app .panel-block.is-active .panel-icon {\n        color: #3273dc; }\n  #optimole-app a.panel-block,\n  #optimole-app label.panel-block {\n    cursor: pointer; }\n    #optimole-app a.panel-block:hover,\n    #optimole-app label.panel-block:hover {\n      background-color: whitesmoke; }\n  #optimole-app .panel-icon {\n    display: inline-block;\n    font-size: 14px;\n    height: 1em;\n    line-height: 1em;\n    text-align: center;\n    vertical-align: top;\n    width: 1em;\n    color: #7a7a7a;\n    margin-right: 0.75em; }\n    #optimole-app .panel-icon .fa {\n      font-size: inherit;\n      line-height: inherit; }\n  #optimole-app .tabs {\n    -webkit-overflow-scrolling: touch;\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex;\n    font-size: 1rem;\n    -ms-flex-pack: justify;\n        justify-content: space-between;\n    overflow: hidden;\n    overflow-x: auto;\n    white-space: nowrap; }\n    #optimole-app .tabs a {\n      -ms-flex-align: center;\n          align-items: center;\n      border-bottom-color: #dbdbdb;\n      border-bottom-style: solid;\n      border-bottom-width: 1px;\n      color: #4a4a4a;\n      display: -ms-flexbox;\n      display: flex;\n      -ms-flex-pack: center;\n          justify-content: center;\n      margin-bottom: -1px;\n      padding: 0.5em 1em;\n      vertical-align: top; }\n      #optimole-app .tabs a:hover {\n        border-bottom-color: #363636;\n        color: #363636; }\n    #optimole-app .tabs li {\n      display: block; }\n      #optimole-app .tabs li.is-active a {\n        border-bottom-color: #3273dc;\n        color: #3273dc; }\n    #optimole-app .tabs ul {\n      -ms-flex-align: center;\n          align-items: center;\n      border-bottom-color: #dbdbdb;\n      border-bottom-style: solid;\n      border-bottom-width: 1px;\n      display: -ms-flexbox;\n      display: flex;\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 0;\n          flex-shrink: 0;\n      -ms-flex-pack: start;\n          justify-content: flex-start; }\n      #optimole-app .tabs ul.is-left {\n        padding-right: 0.75em; }\n      #optimole-app .tabs ul.is-center {\n        -ms-flex: none;\n            flex: none;\n        -ms-flex-pack: center;\n            justify-content: center;\n        padding-left: 0.75em;\n        padding-right: 0.75em; }\n      #optimole-app .tabs ul.is-right {\n        -ms-flex-pack: end;\n            justify-content: flex-end;\n        padding-left: 0.75em; }\n    #optimole-app .tabs .icon:first-child {\n      margin-right: 0.5em; }\n    #optimole-app .tabs .icon:last-child {\n      margin-left: 0.5em; }\n    #optimole-app .tabs.is-centered ul {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .tabs.is-right ul {\n      -ms-flex-pack: end;\n          justify-content: flex-end; }\n    #optimole-app .tabs.is-boxed a {\n      border: 1px solid transparent;\n      border-radius: 4px 4px 0 0; }\n      #optimole-app .tabs.is-boxed a:hover {\n        background-color: whitesmoke;\n        border-bottom-color: #dbdbdb; }\n    #optimole-app .tabs.is-boxed li.is-active a {\n      background-color: white;\n      border-color: #dbdbdb;\n      border-bottom-color: transparent !important; }\n    #optimole-app .tabs.is-fullwidth li {\n      -ms-flex-positive: 1;\n          flex-grow: 1;\n      -ms-flex-negative: 0;\n          flex-shrink: 0; }\n    #optimole-app .tabs.is-toggle a {\n      border-color: #dbdbdb;\n      border-style: solid;\n      border-width: 1px;\n      margin-bottom: 0;\n      position: relative; }\n      #optimole-app .tabs.is-toggle a:hover {\n        background-color: whitesmoke;\n        border-color: #b5b5b5;\n        z-index: 2; }\n    #optimole-app .tabs.is-toggle li + li {\n      margin-left: -1px; }\n    #optimole-app .tabs.is-toggle li:first-child a {\n      border-radius: 4px 0 0 4px; }\n    #optimole-app .tabs.is-toggle li:last-child a {\n      border-radius: 0 4px 4px 0; }\n    #optimole-app .tabs.is-toggle li.is-active a {\n      background-color: #3273dc;\n      border-color: #3273dc;\n      color: #fff;\n      z-index: 1; }\n    #optimole-app .tabs.is-toggle ul {\n      border-bottom: none; }\n    #optimole-app .tabs.is-toggle.is-toggle-rounded li:first-child a {\n      border-bottom-left-radius: 290486px;\n      border-top-left-radius: 290486px;\n      padding-left: 1.25em; }\n    #optimole-app .tabs.is-toggle.is-toggle-rounded li:last-child a {\n      border-bottom-right-radius: 290486px;\n      border-top-right-radius: 290486px;\n      padding-right: 1.25em; }\n    #optimole-app .tabs.is-small {\n      font-size: 0.75rem; }\n    #optimole-app .tabs.is-medium {\n      font-size: 1.25rem; }\n    #optimole-app .tabs.is-large {\n      font-size: 1.5rem; }\n  #optimole-app .column {\n    display: block;\n    -ms-flex-preferred-size: 0;\n        flex-basis: 0;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 1;\n        flex-shrink: 1;\n    padding: 0.75rem; }\n    .columns.is-mobile > #optimole-app .column.is-narrow {\n      -ms-flex: none;\n          flex: none; }\n    .columns.is-mobile > #optimole-app .column.is-full {\n      -ms-flex: none;\n          flex: none;\n      width: 100%; }\n    .columns.is-mobile > #optimole-app .column.is-three-quarters {\n      -ms-flex: none;\n          flex: none;\n      width: 75%; }\n    .columns.is-mobile > #optimole-app .column.is-two-thirds {\n      -ms-flex: none;\n          flex: none;\n      width: 66.6666%; }\n    .columns.is-mobile > #optimole-app .column.is-half {\n      -ms-flex: none;\n          flex: none;\n      width: 50%; }\n    .columns.is-mobile > #optimole-app .column.is-one-third {\n      -ms-flex: none;\n          flex: none;\n      width: 33.3333%; }\n    .columns.is-mobile > #optimole-app .column.is-one-quarter {\n      -ms-flex: none;\n          flex: none;\n      width: 25%; }\n    .columns.is-mobile > #optimole-app .column.is-one-fifth {\n      -ms-flex: none;\n          flex: none;\n      width: 20%; }\n    .columns.is-mobile > #optimole-app .column.is-two-fifths {\n      -ms-flex: none;\n          flex: none;\n      width: 40%; }\n    .columns.is-mobile > #optimole-app .column.is-three-fifths {\n      -ms-flex: none;\n          flex: none;\n      width: 60%; }\n    .columns.is-mobile > #optimole-app .column.is-four-fifths {\n      -ms-flex: none;\n          flex: none;\n      width: 80%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-three-quarters {\n      margin-left: 75%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-two-thirds {\n      margin-left: 66.6666%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-half {\n      margin-left: 50%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-one-third {\n      margin-left: 33.3333%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-one-quarter {\n      margin-left: 25%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-one-fifth {\n      margin-left: 20%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-two-fifths {\n      margin-left: 40%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-three-fifths {\n      margin-left: 60%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-four-fifths {\n      margin-left: 80%; }\n    .columns.is-mobile > #optimole-app .column.is-1 {\n      -ms-flex: none;\n          flex: none;\n      width: 8.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-1 {\n      margin-left: 8.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-2 {\n      -ms-flex: none;\n          flex: none;\n      width: 16.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-2 {\n      margin-left: 16.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-3 {\n      -ms-flex: none;\n          flex: none;\n      width: 25%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-3 {\n      margin-left: 25%; }\n    .columns.is-mobile > #optimole-app .column.is-4 {\n      -ms-flex: none;\n          flex: none;\n      width: 33.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-4 {\n      margin-left: 33.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-5 {\n      -ms-flex: none;\n          flex: none;\n      width: 41.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-5 {\n      margin-left: 41.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-6 {\n      -ms-flex: none;\n          flex: none;\n      width: 50%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-6 {\n      margin-left: 50%; }\n    .columns.is-mobile > #optimole-app .column.is-7 {\n      -ms-flex: none;\n          flex: none;\n      width: 58.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-7 {\n      margin-left: 58.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-8 {\n      -ms-flex: none;\n          flex: none;\n      width: 66.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-8 {\n      margin-left: 66.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-9 {\n      -ms-flex: none;\n          flex: none;\n      width: 75%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-9 {\n      margin-left: 75%; }\n    .columns.is-mobile > #optimole-app .column.is-10 {\n      -ms-flex: none;\n          flex: none;\n      width: 83.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-10 {\n      margin-left: 83.33333%; }\n    .columns.is-mobile > #optimole-app .column.is-11 {\n      -ms-flex: none;\n          flex: none;\n      width: 91.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-11 {\n      margin-left: 91.66667%; }\n    .columns.is-mobile > #optimole-app .column.is-12 {\n      -ms-flex: none;\n          flex: none;\n      width: 100%; }\n    .columns.is-mobile > #optimole-app .column.is-offset-12 {\n      margin-left: 100%; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .column.is-narrow-mobile {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters-mobile {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds-mobile {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half-mobile {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third-mobile {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter-mobile {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth-mobile {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths-mobile {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths-mobile {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths-mobile {\n        margin-left: 80%; }\n      #optimole-app .column.is-1-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1-mobile {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2-mobile {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3-mobile {\n        margin-left: 25%; }\n      #optimole-app .column.is-4-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4-mobile {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5-mobile {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6-mobile {\n        margin-left: 50%; }\n      #optimole-app .column.is-7-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7-mobile {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8-mobile {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9-mobile {\n        margin-left: 75%; }\n      #optimole-app .column.is-10-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10-mobile {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11-mobile {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12-mobile {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12-mobile {\n        margin-left: 100%; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .column.is-narrow, #optimole-app .column.is-narrow-tablet {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full, #optimole-app .column.is-full-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters, #optimole-app .column.is-three-quarters-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds, #optimole-app .column.is-two-thirds-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half, #optimole-app .column.is-half-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third, #optimole-app .column.is-one-third-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter, #optimole-app .column.is-one-quarter-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth, #optimole-app .column.is-one-fifth-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths, #optimole-app .column.is-two-fifths-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths, #optimole-app .column.is-three-fifths-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths, #optimole-app .column.is-four-fifths-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters, #optimole-app .column.is-offset-three-quarters-tablet {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds, #optimole-app .column.is-offset-two-thirds-tablet {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half, #optimole-app .column.is-offset-half-tablet {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third, #optimole-app .column.is-offset-one-third-tablet {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter, #optimole-app .column.is-offset-one-quarter-tablet {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth, #optimole-app .column.is-offset-one-fifth-tablet {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths, #optimole-app .column.is-offset-two-fifths-tablet {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths, #optimole-app .column.is-offset-three-fifths-tablet {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths, #optimole-app .column.is-offset-four-fifths-tablet {\n        margin-left: 80%; }\n      #optimole-app .column.is-1, #optimole-app .column.is-1-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1, #optimole-app .column.is-offset-1-tablet {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2, #optimole-app .column.is-2-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2, #optimole-app .column.is-offset-2-tablet {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3, #optimole-app .column.is-3-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3, #optimole-app .column.is-offset-3-tablet {\n        margin-left: 25%; }\n      #optimole-app .column.is-4, #optimole-app .column.is-4-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4, #optimole-app .column.is-offset-4-tablet {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5, #optimole-app .column.is-5-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5, #optimole-app .column.is-offset-5-tablet {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6, #optimole-app .column.is-6-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6, #optimole-app .column.is-offset-6-tablet {\n        margin-left: 50%; }\n      #optimole-app .column.is-7, #optimole-app .column.is-7-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7, #optimole-app .column.is-offset-7-tablet {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8, #optimole-app .column.is-8-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8, #optimole-app .column.is-offset-8-tablet {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9, #optimole-app .column.is-9-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9, #optimole-app .column.is-offset-9-tablet {\n        margin-left: 75%; }\n      #optimole-app .column.is-10, #optimole-app .column.is-10-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10, #optimole-app .column.is-offset-10-tablet {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11, #optimole-app .column.is-11-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11, #optimole-app .column.is-offset-11-tablet {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12, #optimole-app .column.is-12-tablet {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12, #optimole-app .column.is-offset-12-tablet {\n        margin-left: 100%; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .column.is-narrow-touch {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters-touch {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds-touch {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half-touch {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third-touch {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter-touch {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth-touch {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths-touch {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths-touch {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths-touch {\n        margin-left: 80%; }\n      #optimole-app .column.is-1-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1-touch {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2-touch {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3-touch {\n        margin-left: 25%; }\n      #optimole-app .column.is-4-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4-touch {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5-touch {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6-touch {\n        margin-left: 50%; }\n      #optimole-app .column.is-7-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7-touch {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8-touch {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9-touch {\n        margin-left: 75%; }\n      #optimole-app .column.is-10-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10-touch {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11-touch {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12-touch {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12-touch {\n        margin-left: 100%; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .column.is-narrow-desktop {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters-desktop {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds-desktop {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half-desktop {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third-desktop {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter-desktop {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth-desktop {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths-desktop {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths-desktop {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths-desktop {\n        margin-left: 80%; }\n      #optimole-app .column.is-1-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1-desktop {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2-desktop {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3-desktop {\n        margin-left: 25%; }\n      #optimole-app .column.is-4-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4-desktop {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5-desktop {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6-desktop {\n        margin-left: 50%; }\n      #optimole-app .column.is-7-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7-desktop {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8-desktop {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9-desktop {\n        margin-left: 75%; }\n      #optimole-app .column.is-10-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10-desktop {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11-desktop {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12-desktop {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12-desktop {\n        margin-left: 100%; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .column.is-narrow-widescreen {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters-widescreen {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds-widescreen {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half-widescreen {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third-widescreen {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter-widescreen {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth-widescreen {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths-widescreen {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths-widescreen {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths-widescreen {\n        margin-left: 80%; }\n      #optimole-app .column.is-1-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1-widescreen {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2-widescreen {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3-widescreen {\n        margin-left: 25%; }\n      #optimole-app .column.is-4-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4-widescreen {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5-widescreen {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6-widescreen {\n        margin-left: 50%; }\n      #optimole-app .column.is-7-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7-widescreen {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8-widescreen {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9-widescreen {\n        margin-left: 75%; }\n      #optimole-app .column.is-10-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10-widescreen {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11-widescreen {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12-widescreen {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12-widescreen {\n        margin-left: 100%; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .column.is-narrow-fullhd {\n        -ms-flex: none;\n            flex: none; }\n      #optimole-app .column.is-full-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-three-quarters-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-two-thirds-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 66.6666%; }\n      #optimole-app .column.is-half-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-one-third-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 33.3333%; }\n      #optimole-app .column.is-one-quarter-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-one-fifth-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 20%; }\n      #optimole-app .column.is-two-fifths-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 40%; }\n      #optimole-app .column.is-three-fifths-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 60%; }\n      #optimole-app .column.is-four-fifths-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 80%; }\n      #optimole-app .column.is-offset-three-quarters-fullhd {\n        margin-left: 75%; }\n      #optimole-app .column.is-offset-two-thirds-fullhd {\n        margin-left: 66.6666%; }\n      #optimole-app .column.is-offset-half-fullhd {\n        margin-left: 50%; }\n      #optimole-app .column.is-offset-one-third-fullhd {\n        margin-left: 33.3333%; }\n      #optimole-app .column.is-offset-one-quarter-fullhd {\n        margin-left: 25%; }\n      #optimole-app .column.is-offset-one-fifth-fullhd {\n        margin-left: 20%; }\n      #optimole-app .column.is-offset-two-fifths-fullhd {\n        margin-left: 40%; }\n      #optimole-app .column.is-offset-three-fifths-fullhd {\n        margin-left: 60%; }\n      #optimole-app .column.is-offset-four-fifths-fullhd {\n        margin-left: 80%; }\n      #optimole-app .column.is-1-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .column.is-offset-1-fullhd {\n        margin-left: 8.33333%; }\n      #optimole-app .column.is-2-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .column.is-offset-2-fullhd {\n        margin-left: 16.66667%; }\n      #optimole-app .column.is-3-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .column.is-offset-3-fullhd {\n        margin-left: 25%; }\n      #optimole-app .column.is-4-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .column.is-offset-4-fullhd {\n        margin-left: 33.33333%; }\n      #optimole-app .column.is-5-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .column.is-offset-5-fullhd {\n        margin-left: 41.66667%; }\n      #optimole-app .column.is-6-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .column.is-offset-6-fullhd {\n        margin-left: 50%; }\n      #optimole-app .column.is-7-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .column.is-offset-7-fullhd {\n        margin-left: 58.33333%; }\n      #optimole-app .column.is-8-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .column.is-offset-8-fullhd {\n        margin-left: 66.66667%; }\n      #optimole-app .column.is-9-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .column.is-offset-9-fullhd {\n        margin-left: 75%; }\n      #optimole-app .column.is-10-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .column.is-offset-10-fullhd {\n        margin-left: 83.33333%; }\n      #optimole-app .column.is-11-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .column.is-offset-11-fullhd {\n        margin-left: 91.66667%; }\n      #optimole-app .column.is-12-fullhd {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; }\n      #optimole-app .column.is-offset-12-fullhd {\n        margin-left: 100%; } }\n  #optimole-app .columns {\n    margin-left: -0.75rem;\n    margin-right: -0.75rem;\n    margin-top: -0.75rem; }\n    #optimole-app .columns:last-child {\n      margin-bottom: -0.75rem; }\n    #optimole-app .columns:not(:last-child) {\n      margin-bottom: calc(1.5rem - 0.75rem); }\n    #optimole-app .columns.is-centered {\n      -ms-flex-pack: center;\n          justify-content: center; }\n    #optimole-app .columns.is-gapless {\n      margin-left: 0;\n      margin-right: 0;\n      margin-top: 0; }\n      #optimole-app .columns.is-gapless > .column {\n        margin: 0;\n        padding: 0 !important; }\n      #optimole-app .columns.is-gapless:not(:last-child) {\n        margin-bottom: 1.5rem; }\n      #optimole-app .columns.is-gapless:last-child {\n        margin-bottom: 0; }\n    #optimole-app .columns.is-mobile {\n      display: -ms-flexbox;\n      display: flex; }\n    #optimole-app .columns.is-multiline {\n      -ms-flex-wrap: wrap;\n          flex-wrap: wrap; }\n    #optimole-app .columns.is-vcentered {\n      -ms-flex-align: center;\n          align-items: center; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns:not(.is-desktop) {\n        display: -ms-flexbox;\n        display: flex; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-desktop {\n        display: -ms-flexbox;\n        display: flex; } }\n  #optimole-app .columns.is-variable {\n    --columnGap: 0.75rem;\n    margin-left: calc(-1 * var(--columnGap));\n    margin-right: calc(-1 * var(--columnGap)); }\n    #optimole-app .columns.is-variable .column {\n      padding-left: var(--columnGap);\n      padding-right: var(--columnGap); }\n    #optimole-app .columns.is-variable.is-0 {\n      --columnGap: 0rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-0-mobile {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-0-tablet {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-0-tablet-only {\n        --columnGap: 0rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-0-touch {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-0-desktop {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-0-desktop-only {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-0-widescreen {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-0-widescreen-only {\n        --columnGap: 0rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-0-fullhd {\n        --columnGap: 0rem; } }\n    #optimole-app .columns.is-variable.is-1 {\n      --columnGap: 0.25rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-1-mobile {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-1-tablet {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-1-tablet-only {\n        --columnGap: 0.25rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-1-touch {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-1-desktop {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-1-desktop-only {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-1-widescreen {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-1-widescreen-only {\n        --columnGap: 0.25rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-1-fullhd {\n        --columnGap: 0.25rem; } }\n    #optimole-app .columns.is-variable.is-2 {\n      --columnGap: 0.5rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-2-mobile {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-2-tablet {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-2-tablet-only {\n        --columnGap: 0.5rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-2-touch {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-2-desktop {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-2-desktop-only {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-2-widescreen {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-2-widescreen-only {\n        --columnGap: 0.5rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-2-fullhd {\n        --columnGap: 0.5rem; } }\n    #optimole-app .columns.is-variable.is-3 {\n      --columnGap: 0.75rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-3-mobile {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-3-tablet {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-3-tablet-only {\n        --columnGap: 0.75rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-3-touch {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-3-desktop {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-3-desktop-only {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-3-widescreen {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-3-widescreen-only {\n        --columnGap: 0.75rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-3-fullhd {\n        --columnGap: 0.75rem; } }\n    #optimole-app .columns.is-variable.is-4 {\n      --columnGap: 1rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-4-mobile {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-4-tablet {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-4-tablet-only {\n        --columnGap: 1rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-4-touch {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-4-desktop {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-4-desktop-only {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-4-widescreen {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-4-widescreen-only {\n        --columnGap: 1rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-4-fullhd {\n        --columnGap: 1rem; } }\n    #optimole-app .columns.is-variable.is-5 {\n      --columnGap: 1.25rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-5-mobile {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-5-tablet {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-5-tablet-only {\n        --columnGap: 1.25rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-5-touch {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-5-desktop {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-5-desktop-only {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-5-widescreen {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-5-widescreen-only {\n        --columnGap: 1.25rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-5-fullhd {\n        --columnGap: 1.25rem; } }\n    #optimole-app .columns.is-variable.is-6 {\n      --columnGap: 1.5rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-6-mobile {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-6-tablet {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-6-tablet-only {\n        --columnGap: 1.5rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-6-touch {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-6-desktop {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-6-desktop-only {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-6-widescreen {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-6-widescreen-only {\n        --columnGap: 1.5rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-6-fullhd {\n        --columnGap: 1.5rem; } }\n    #optimole-app .columns.is-variable.is-7 {\n      --columnGap: 1.75rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-7-mobile {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-7-tablet {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-7-tablet-only {\n        --columnGap: 1.75rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-7-touch {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-7-desktop {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-7-desktop-only {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-7-widescreen {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-7-widescreen-only {\n        --columnGap: 1.75rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-7-fullhd {\n        --columnGap: 1.75rem; } }\n    #optimole-app .columns.is-variable.is-8 {\n      --columnGap: 2rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .columns.is-variable.is-8-mobile {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .columns.is-variable.is-8-tablet {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 769px) and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-8-tablet-only {\n        --columnGap: 2rem; } }\n    @media screen and (max-width: 1087px) {\n      #optimole-app .columns.is-variable.is-8-touch {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .columns.is-variable.is-8-desktop {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 1088px) and (max-width: 1279px) {\n      #optimole-app .columns.is-variable.is-8-desktop-only {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 1280px) {\n      #optimole-app .columns.is-variable.is-8-widescreen {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 1280px) and (max-width: 1471px) {\n      #optimole-app .columns.is-variable.is-8-widescreen-only {\n        --columnGap: 2rem; } }\n    @media screen and (min-width: 1472px) {\n      #optimole-app .columns.is-variable.is-8-fullhd {\n        --columnGap: 2rem; } }\n  #optimole-app .tile {\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: block;\n    -ms-flex-preferred-size: 0;\n        flex-basis: 0;\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 1;\n        flex-shrink: 1;\n    min-height: -webkit-min-content;\n    min-height: -moz-min-content;\n    min-height: min-content; }\n    #optimole-app .tile.is-ancestor {\n      margin-left: -0.75rem;\n      margin-right: -0.75rem;\n      margin-top: -0.75rem; }\n      #optimole-app .tile.is-ancestor:last-child {\n        margin-bottom: -0.75rem; }\n      #optimole-app .tile.is-ancestor:not(:last-child) {\n        margin-bottom: 0.75rem; }\n    #optimole-app .tile.is-child {\n      margin: 0 !important; }\n    #optimole-app .tile.is-parent {\n      padding: 0.75rem; }\n    #optimole-app .tile.is-vertical {\n      -ms-flex-direction: column;\n          flex-direction: column; }\n      #optimole-app .tile.is-vertical > .tile.is-child:not(:last-child) {\n        margin-bottom: 1.5rem !important; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .tile:not(.is-child) {\n        display: -ms-flexbox;\n        display: flex; }\n      #optimole-app .tile.is-1 {\n        -ms-flex: none;\n            flex: none;\n        width: 8.33333%; }\n      #optimole-app .tile.is-2 {\n        -ms-flex: none;\n            flex: none;\n        width: 16.66667%; }\n      #optimole-app .tile.is-3 {\n        -ms-flex: none;\n            flex: none;\n        width: 25%; }\n      #optimole-app .tile.is-4 {\n        -ms-flex: none;\n            flex: none;\n        width: 33.33333%; }\n      #optimole-app .tile.is-5 {\n        -ms-flex: none;\n            flex: none;\n        width: 41.66667%; }\n      #optimole-app .tile.is-6 {\n        -ms-flex: none;\n            flex: none;\n        width: 50%; }\n      #optimole-app .tile.is-7 {\n        -ms-flex: none;\n            flex: none;\n        width: 58.33333%; }\n      #optimole-app .tile.is-8 {\n        -ms-flex: none;\n            flex: none;\n        width: 66.66667%; }\n      #optimole-app .tile.is-9 {\n        -ms-flex: none;\n            flex: none;\n        width: 75%; }\n      #optimole-app .tile.is-10 {\n        -ms-flex: none;\n            flex: none;\n        width: 83.33333%; }\n      #optimole-app .tile.is-11 {\n        -ms-flex: none;\n            flex: none;\n        width: 91.66667%; }\n      #optimole-app .tile.is-12 {\n        -ms-flex: none;\n            flex: none;\n        width: 100%; } }\n  #optimole-app .hero {\n    -ms-flex-align: stretch;\n        align-items: stretch;\n    display: -ms-flexbox;\n    display: flex;\n    -ms-flex-direction: column;\n        flex-direction: column;\n    -ms-flex-pack: justify;\n        justify-content: space-between; }\n    #optimole-app .hero .navbar {\n      background: none; }\n    #optimole-app .hero .tabs ul {\n      border-bottom: none; }\n    #optimole-app .hero.is-white {\n      background-color: white;\n      color: #0a0a0a; }\n      #optimole-app .hero.is-white a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-white strong {\n        color: inherit; }\n      #optimole-app .hero.is-white .title {\n        color: #0a0a0a; }\n      #optimole-app .hero.is-white .subtitle {\n        color: rgba(10, 10, 10, 0.9); }\n        #optimole-app .hero.is-white .subtitle a:not(.button),\n        #optimole-app .hero.is-white .subtitle strong {\n          color: #0a0a0a; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-white .navbar-menu {\n          background-color: white; } }\n      #optimole-app .hero.is-white .navbar-item,\n      #optimole-app .hero.is-white .navbar-link {\n        color: rgba(10, 10, 10, 0.7); }\n      #optimole-app .hero.is-white a.navbar-item:hover, #optimole-app .hero.is-white a.navbar-item.is-active,\n      #optimole-app .hero.is-white .navbar-link:hover,\n      #optimole-app .hero.is-white .navbar-link.is-active {\n        background-color: #f2f2f2;\n        color: #0a0a0a; }\n      #optimole-app .hero.is-white .tabs a {\n        color: #0a0a0a;\n        opacity: 0.9; }\n        #optimole-app .hero.is-white .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-white .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-white .tabs.is-boxed a, #optimole-app .hero.is-white .tabs.is-toggle a {\n        color: #0a0a0a; }\n        #optimole-app .hero.is-white .tabs.is-boxed a:hover, #optimole-app .hero.is-white .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-white .tabs.is-boxed li.is-active a, #optimole-app .hero.is-white .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-white .tabs.is-toggle li.is-active a, #optimole-app .hero.is-white .tabs.is-toggle li.is-active a:hover {\n        background-color: #0a0a0a;\n        border-color: #0a0a0a;\n        color: white; }\n      #optimole-app .hero.is-white.is-bold {\n        background-image: linear-gradient(141deg, #e6e6e6 0%, white 71%, white 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-white.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #e6e6e6 0%, white 71%, white 100%); } }\n    #optimole-app .hero.is-black {\n      background-color: #0a0a0a;\n      color: white; }\n      #optimole-app .hero.is-black a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-black strong {\n        color: inherit; }\n      #optimole-app .hero.is-black .title {\n        color: white; }\n      #optimole-app .hero.is-black .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-black .subtitle a:not(.button),\n        #optimole-app .hero.is-black .subtitle strong {\n          color: white; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-black .navbar-menu {\n          background-color: #0a0a0a; } }\n      #optimole-app .hero.is-black .navbar-item,\n      #optimole-app .hero.is-black .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-black a.navbar-item:hover, #optimole-app .hero.is-black a.navbar-item.is-active,\n      #optimole-app .hero.is-black .navbar-link:hover,\n      #optimole-app .hero.is-black .navbar-link.is-active {\n        background-color: black;\n        color: white; }\n      #optimole-app .hero.is-black .tabs a {\n        color: white;\n        opacity: 0.9; }\n        #optimole-app .hero.is-black .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-black .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-black .tabs.is-boxed a, #optimole-app .hero.is-black .tabs.is-toggle a {\n        color: white; }\n        #optimole-app .hero.is-black .tabs.is-boxed a:hover, #optimole-app .hero.is-black .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-black .tabs.is-boxed li.is-active a, #optimole-app .hero.is-black .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-black .tabs.is-toggle li.is-active a, #optimole-app .hero.is-black .tabs.is-toggle li.is-active a:hover {\n        background-color: white;\n        border-color: white;\n        color: #0a0a0a; }\n      #optimole-app .hero.is-black.is-bold {\n        background-image: linear-gradient(141deg, black 0%, #0a0a0a 71%, #181616 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-black.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, black 0%, #0a0a0a 71%, #181616 100%); } }\n    #optimole-app .hero.is-light {\n      background-color: whitesmoke;\n      color: #363636; }\n      #optimole-app .hero.is-light a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-light strong {\n        color: inherit; }\n      #optimole-app .hero.is-light .title {\n        color: #363636; }\n      #optimole-app .hero.is-light .subtitle {\n        color: rgba(54, 54, 54, 0.9); }\n        #optimole-app .hero.is-light .subtitle a:not(.button),\n        #optimole-app .hero.is-light .subtitle strong {\n          color: #363636; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-light .navbar-menu {\n          background-color: whitesmoke; } }\n      #optimole-app .hero.is-light .navbar-item,\n      #optimole-app .hero.is-light .navbar-link {\n        color: rgba(54, 54, 54, 0.7); }\n      #optimole-app .hero.is-light a.navbar-item:hover, #optimole-app .hero.is-light a.navbar-item.is-active,\n      #optimole-app .hero.is-light .navbar-link:hover,\n      #optimole-app .hero.is-light .navbar-link.is-active {\n        background-color: #e8e8e8;\n        color: #363636; }\n      #optimole-app .hero.is-light .tabs a {\n        color: #363636;\n        opacity: 0.9; }\n        #optimole-app .hero.is-light .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-light .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-light .tabs.is-boxed a, #optimole-app .hero.is-light .tabs.is-toggle a {\n        color: #363636; }\n        #optimole-app .hero.is-light .tabs.is-boxed a:hover, #optimole-app .hero.is-light .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-light .tabs.is-boxed li.is-active a, #optimole-app .hero.is-light .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-light .tabs.is-toggle li.is-active a, #optimole-app .hero.is-light .tabs.is-toggle li.is-active a:hover {\n        background-color: #363636;\n        border-color: #363636;\n        color: whitesmoke; }\n      #optimole-app .hero.is-light.is-bold {\n        background-image: linear-gradient(141deg, #dfd8d9 0%, whitesmoke 71%, white 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-light.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #dfd8d9 0%, whitesmoke 71%, white 100%); } }\n    #optimole-app .hero.is-dark {\n      background-color: #363636;\n      color: whitesmoke; }\n      #optimole-app .hero.is-dark a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-dark strong {\n        color: inherit; }\n      #optimole-app .hero.is-dark .title {\n        color: whitesmoke; }\n      #optimole-app .hero.is-dark .subtitle {\n        color: rgba(245, 245, 245, 0.9); }\n        #optimole-app .hero.is-dark .subtitle a:not(.button),\n        #optimole-app .hero.is-dark .subtitle strong {\n          color: whitesmoke; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-dark .navbar-menu {\n          background-color: #363636; } }\n      #optimole-app .hero.is-dark .navbar-item,\n      #optimole-app .hero.is-dark .navbar-link {\n        color: rgba(245, 245, 245, 0.7); }\n      #optimole-app .hero.is-dark a.navbar-item:hover, #optimole-app .hero.is-dark a.navbar-item.is-active,\n      #optimole-app .hero.is-dark .navbar-link:hover,\n      #optimole-app .hero.is-dark .navbar-link.is-active {\n        background-color: #292929;\n        color: whitesmoke; }\n      #optimole-app .hero.is-dark .tabs a {\n        color: whitesmoke;\n        opacity: 0.9; }\n        #optimole-app .hero.is-dark .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-dark .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-dark .tabs.is-boxed a, #optimole-app .hero.is-dark .tabs.is-toggle a {\n        color: whitesmoke; }\n        #optimole-app .hero.is-dark .tabs.is-boxed a:hover, #optimole-app .hero.is-dark .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-dark .tabs.is-boxed li.is-active a, #optimole-app .hero.is-dark .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-dark .tabs.is-toggle li.is-active a, #optimole-app .hero.is-dark .tabs.is-toggle li.is-active a:hover {\n        background-color: whitesmoke;\n        border-color: whitesmoke;\n        color: #363636; }\n      #optimole-app .hero.is-dark.is-bold {\n        background-image: linear-gradient(141deg, #1f191a 0%, #363636 71%, #46403f 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-dark.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #1f191a 0%, #363636 71%, #46403f 100%); } }\n    #optimole-app .hero.is-primary {\n      background-color: #EF686B;\n      color: #fff; }\n      #optimole-app .hero.is-primary a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-primary strong {\n        color: inherit; }\n      #optimole-app .hero.is-primary .title {\n        color: #fff; }\n      #optimole-app .hero.is-primary .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-primary .subtitle a:not(.button),\n        #optimole-app .hero.is-primary .subtitle strong {\n          color: #fff; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-primary .navbar-menu {\n          background-color: #EF686B; } }\n      #optimole-app .hero.is-primary .navbar-item,\n      #optimole-app .hero.is-primary .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-primary a.navbar-item:hover, #optimole-app .hero.is-primary a.navbar-item.is-active,\n      #optimole-app .hero.is-primary .navbar-link:hover,\n      #optimole-app .hero.is-primary .navbar-link.is-active {\n        background-color: #ed5154;\n        color: #fff; }\n      #optimole-app .hero.is-primary .tabs a {\n        color: #fff;\n        opacity: 0.9; }\n        #optimole-app .hero.is-primary .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-primary .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-primary .tabs.is-boxed a, #optimole-app .hero.is-primary .tabs.is-toggle a {\n        color: #fff; }\n        #optimole-app .hero.is-primary .tabs.is-boxed a:hover, #optimole-app .hero.is-primary .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-primary .tabs.is-boxed li.is-active a, #optimole-app .hero.is-primary .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-primary .tabs.is-toggle li.is-active a, #optimole-app .hero.is-primary .tabs.is-toggle li.is-active a:hover {\n        background-color: #fff;\n        border-color: #fff;\n        color: #EF686B; }\n      #optimole-app .hero.is-primary.is-bold {\n        background-image: linear-gradient(141deg, #f52f54 0%, #EF686B 71%, #f58d7c 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-primary.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #f52f54 0%, #EF686B 71%, #f58d7c 100%); } }\n    #optimole-app .hero.is-link {\n      background-color: #3273dc;\n      color: #fff; }\n      #optimole-app .hero.is-link a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-link strong {\n        color: inherit; }\n      #optimole-app .hero.is-link .title {\n        color: #fff; }\n      #optimole-app .hero.is-link .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-link .subtitle a:not(.button),\n        #optimole-app .hero.is-link .subtitle strong {\n          color: #fff; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-link .navbar-menu {\n          background-color: #3273dc; } }\n      #optimole-app .hero.is-link .navbar-item,\n      #optimole-app .hero.is-link .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-link a.navbar-item:hover, #optimole-app .hero.is-link a.navbar-item.is-active,\n      #optimole-app .hero.is-link .navbar-link:hover,\n      #optimole-app .hero.is-link .navbar-link.is-active {\n        background-color: #2366d1;\n        color: #fff; }\n      #optimole-app .hero.is-link .tabs a {\n        color: #fff;\n        opacity: 0.9; }\n        #optimole-app .hero.is-link .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-link .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-link .tabs.is-boxed a, #optimole-app .hero.is-link .tabs.is-toggle a {\n        color: #fff; }\n        #optimole-app .hero.is-link .tabs.is-boxed a:hover, #optimole-app .hero.is-link .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-link .tabs.is-boxed li.is-active a, #optimole-app .hero.is-link .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-link .tabs.is-toggle li.is-active a, #optimole-app .hero.is-link .tabs.is-toggle li.is-active a:hover {\n        background-color: #fff;\n        border-color: #fff;\n        color: #3273dc; }\n      #optimole-app .hero.is-link.is-bold {\n        background-image: linear-gradient(141deg, #1577c6 0%, #3273dc 71%, #4366e5 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-link.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #1577c6 0%, #3273dc 71%, #4366e5 100%); } }\n    #optimole-app .hero.is-info {\n      background-color: #5180C1;\n      color: #fff; }\n      #optimole-app .hero.is-info a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-info strong {\n        color: inherit; }\n      #optimole-app .hero.is-info .title {\n        color: #fff; }\n      #optimole-app .hero.is-info .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-info .subtitle a:not(.button),\n        #optimole-app .hero.is-info .subtitle strong {\n          color: #fff; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-info .navbar-menu {\n          background-color: #5180C1; } }\n      #optimole-app .hero.is-info .navbar-item,\n      #optimole-app .hero.is-info .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-info a.navbar-item:hover, #optimole-app .hero.is-info a.navbar-item.is-active,\n      #optimole-app .hero.is-info .navbar-link:hover,\n      #optimole-app .hero.is-info .navbar-link.is-active {\n        background-color: #4173b7;\n        color: #fff; }\n      #optimole-app .hero.is-info .tabs a {\n        color: #fff;\n        opacity: 0.9; }\n        #optimole-app .hero.is-info .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-info .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-info .tabs.is-boxed a, #optimole-app .hero.is-info .tabs.is-toggle a {\n        color: #fff; }\n        #optimole-app .hero.is-info .tabs.is-boxed a:hover, #optimole-app .hero.is-info .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-info .tabs.is-boxed li.is-active a, #optimole-app .hero.is-info .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-info .tabs.is-toggle li.is-active a, #optimole-app .hero.is-info .tabs.is-toggle li.is-active a:hover {\n        background-color: #fff;\n        border-color: #fff;\n        color: #5180C1; }\n      #optimole-app .hero.is-info.is-bold {\n        background-image: linear-gradient(141deg, #2f7bb0 0%, #5180C1 71%, #5f7acd 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-info.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #2f7bb0 0%, #5180C1 71%, #5f7acd 100%); } }\n    #optimole-app .hero.is-success {\n      background-color: #34a85e;\n      color: #fff; }\n      #optimole-app .hero.is-success a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-success strong {\n        color: inherit; }\n      #optimole-app .hero.is-success .title {\n        color: #fff; }\n      #optimole-app .hero.is-success .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-success .subtitle a:not(.button),\n        #optimole-app .hero.is-success .subtitle strong {\n          color: #fff; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-success .navbar-menu {\n          background-color: #34a85e; } }\n      #optimole-app .hero.is-success .navbar-item,\n      #optimole-app .hero.is-success .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-success a.navbar-item:hover, #optimole-app .hero.is-success a.navbar-item.is-active,\n      #optimole-app .hero.is-success .navbar-link:hover,\n      #optimole-app .hero.is-success .navbar-link.is-active {\n        background-color: #2e9553;\n        color: #fff; }\n      #optimole-app .hero.is-success .tabs a {\n        color: #fff;\n        opacity: 0.9; }\n        #optimole-app .hero.is-success .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-success .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-success .tabs.is-boxed a, #optimole-app .hero.is-success .tabs.is-toggle a {\n        color: #fff; }\n        #optimole-app .hero.is-success .tabs.is-boxed a:hover, #optimole-app .hero.is-success .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-success .tabs.is-boxed li.is-active a, #optimole-app .hero.is-success .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-success .tabs.is-toggle li.is-active a, #optimole-app .hero.is-success .tabs.is-toggle li.is-active a:hover {\n        background-color: #fff;\n        border-color: #fff;\n        color: #34a85e; }\n      #optimole-app .hero.is-success.is-bold {\n        background-image: linear-gradient(141deg, #1f8a34 0%, #34a85e 71%, #34c27f 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-success.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #1f8a34 0%, #34a85e 71%, #34c27f 100%); } }\n    #optimole-app .hero.is-warning {\n      background-color: #ffdd57;\n      color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .hero.is-warning a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-warning strong {\n        color: inherit; }\n      #optimole-app .hero.is-warning .title {\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .hero.is-warning .subtitle {\n        color: rgba(0, 0, 0, 0.9); }\n        #optimole-app .hero.is-warning .subtitle a:not(.button),\n        #optimole-app .hero.is-warning .subtitle strong {\n          color: rgba(0, 0, 0, 0.7); }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-warning .navbar-menu {\n          background-color: #ffdd57; } }\n      #optimole-app .hero.is-warning .navbar-item,\n      #optimole-app .hero.is-warning .navbar-link {\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .hero.is-warning a.navbar-item:hover, #optimole-app .hero.is-warning a.navbar-item.is-active,\n      #optimole-app .hero.is-warning .navbar-link:hover,\n      #optimole-app .hero.is-warning .navbar-link.is-active {\n        background-color: #ffd83d;\n        color: rgba(0, 0, 0, 0.7); }\n      #optimole-app .hero.is-warning .tabs a {\n        color: rgba(0, 0, 0, 0.7);\n        opacity: 0.9; }\n        #optimole-app .hero.is-warning .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-warning .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-warning .tabs.is-boxed a, #optimole-app .hero.is-warning .tabs.is-toggle a {\n        color: rgba(0, 0, 0, 0.7); }\n        #optimole-app .hero.is-warning .tabs.is-boxed a:hover, #optimole-app .hero.is-warning .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-warning .tabs.is-boxed li.is-active a, #optimole-app .hero.is-warning .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-warning .tabs.is-toggle li.is-active a, #optimole-app .hero.is-warning .tabs.is-toggle li.is-active a:hover {\n        background-color: rgba(0, 0, 0, 0.7);\n        border-color: rgba(0, 0, 0, 0.7);\n        color: #ffdd57; }\n      #optimole-app .hero.is-warning.is-bold {\n        background-image: linear-gradient(141deg, #ffaf24 0%, #ffdd57 71%, #fffa70 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-warning.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #ffaf24 0%, #ffdd57 71%, #fffa70 100%); } }\n    #optimole-app .hero.is-danger {\n      background-color: #D54222;\n      color: #fff; }\n      #optimole-app .hero.is-danger a:not(.button):not(.dropdown-item):not(.tag),\n      #optimole-app .hero.is-danger strong {\n        color: inherit; }\n      #optimole-app .hero.is-danger .title {\n        color: #fff; }\n      #optimole-app .hero.is-danger .subtitle {\n        color: rgba(255, 255, 255, 0.9); }\n        #optimole-app .hero.is-danger .subtitle a:not(.button),\n        #optimole-app .hero.is-danger .subtitle strong {\n          color: #fff; }\n      @media screen and (max-width: 1087px) {\n        #optimole-app .hero.is-danger .navbar-menu {\n          background-color: #D54222; } }\n      #optimole-app .hero.is-danger .navbar-item,\n      #optimole-app .hero.is-danger .navbar-link {\n        color: rgba(255, 255, 255, 0.7); }\n      #optimole-app .hero.is-danger a.navbar-item:hover, #optimole-app .hero.is-danger a.navbar-item.is-active,\n      #optimole-app .hero.is-danger .navbar-link:hover,\n      #optimole-app .hero.is-danger .navbar-link.is-active {\n        background-color: #bf3b1e;\n        color: #fff; }\n      #optimole-app .hero.is-danger .tabs a {\n        color: #fff;\n        opacity: 0.9; }\n        #optimole-app .hero.is-danger .tabs a:hover {\n          opacity: 1; }\n      #optimole-app .hero.is-danger .tabs li.is-active a {\n        opacity: 1; }\n      #optimole-app .hero.is-danger .tabs.is-boxed a, #optimole-app .hero.is-danger .tabs.is-toggle a {\n        color: #fff; }\n        #optimole-app .hero.is-danger .tabs.is-boxed a:hover, #optimole-app .hero.is-danger .tabs.is-toggle a:hover {\n          background-color: rgba(10, 10, 10, 0.1); }\n      #optimole-app .hero.is-danger .tabs.is-boxed li.is-active a, #optimole-app .hero.is-danger .tabs.is-boxed li.is-active a:hover, #optimole-app .hero.is-danger .tabs.is-toggle li.is-active a, #optimole-app .hero.is-danger .tabs.is-toggle li.is-active a:hover {\n        background-color: #fff;\n        border-color: #fff;\n        color: #D54222; }\n      #optimole-app .hero.is-danger.is-bold {\n        background-image: linear-gradient(141deg, #b31311 0%, #D54222 71%, #e46c2c 100%); }\n        @media screen and (max-width: 768px) {\n          #optimole-app .hero.is-danger.is-bold .navbar-menu {\n            background-image: linear-gradient(141deg, #b31311 0%, #D54222 71%, #e46c2c 100%); } }\n    #optimole-app .hero.is-small .hero-body {\n      padding-bottom: 1.5rem;\n      padding-top: 1.5rem; }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .hero.is-medium .hero-body {\n        padding-bottom: 9rem;\n        padding-top: 9rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .hero.is-large .hero-body {\n        padding-bottom: 18rem;\n        padding-top: 18rem; } }\n    #optimole-app .hero.is-halfheight .hero-body, #optimole-app .hero.is-fullheight .hero-body, #optimole-app .hero.is-fullheight-with-navbar .hero-body {\n      -ms-flex-align: center;\n          align-items: center;\n      display: -ms-flexbox;\n      display: flex; }\n      #optimole-app .hero.is-halfheight .hero-body > .container, #optimole-app .hero.is-fullheight .hero-body > .container, #optimole-app .hero.is-fullheight-with-navbar .hero-body > .container {\n        -ms-flex-positive: 1;\n            flex-grow: 1;\n        -ms-flex-negative: 1;\n            flex-shrink: 1; }\n    #optimole-app .hero.is-halfheight {\n      min-height: 50vh; }\n    #optimole-app .hero.is-fullheight {\n      min-height: 100vh; }\n  #optimole-app .hero-video {\n    overflow: hidden; }\n    #optimole-app .hero-video video {\n      left: 50%;\n      min-height: 100%;\n      min-width: 100%;\n      position: absolute;\n      top: 50%;\n      transform: translate3d(-50%, -50%, 0); }\n    #optimole-app .hero-video.is-transparent {\n      opacity: 0.3; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .hero-video {\n        display: none; } }\n  #optimole-app .hero-buttons {\n    margin-top: 1.5rem; }\n    @media screen and (max-width: 768px) {\n      #optimole-app .hero-buttons .button {\n        display: -ms-flexbox;\n        display: flex; }\n        #optimole-app .hero-buttons .button:not(:last-child) {\n          margin-bottom: 0.75rem; } }\n    @media screen and (min-width: 769px), print {\n      #optimole-app .hero-buttons {\n        display: -ms-flexbox;\n        display: flex;\n        -ms-flex-pack: center;\n            justify-content: center; }\n        #optimole-app .hero-buttons .button:not(:last-child) {\n          margin-right: 1.5rem; } }\n  #optimole-app .hero-head,\n  #optimole-app .hero-foot {\n    -ms-flex-positive: 0;\n        flex-grow: 0;\n    -ms-flex-negative: 0;\n        flex-shrink: 0; }\n  #optimole-app .hero-body {\n    -ms-flex-positive: 1;\n        flex-grow: 1;\n    -ms-flex-negative: 0;\n        flex-shrink: 0;\n    padding: 3rem 1.5rem; }\n  #optimole-app .section {\n    padding: 3rem 1.5rem; }\n    @media screen and (min-width: 1088px) {\n      #optimole-app .section.is-medium {\n        padding: 9rem 1.5rem; }\n      #optimole-app .section.is-large {\n        padding: 18rem 1.5rem; } }\n  #optimole-app .footer {\n    background-color: #fafafa;\n    padding: 3rem 1.5rem 6rem; }\n  #optimole-app .card {\n    transition: all 750ms ease-in-out;\n    border: 0;\n    border-radius: .1875rem;\n    box-shadow: 0 1px 15px 1px rgba(39, 39, 39, 0.1); }\n  #optimole-app .logo {\n    margin-bottom: 10px; }\n    #optimole-app .logo img {\n      max-width: 180px;\n      margin: 0 auto; }\n  #optimole-app .vue-js-switch {\n    -ms-flex-item-align: center;\n        -ms-grid-row-align: center;\n        align-self: center; }\n  #optimole-app .api-key-control {\n    padding: 0; }\n  #optimole-app .api-key-field .button.is-danger {\n    padding-left: 20px;\n    padding-right: 20px; }\n  #optimole-app .api-key-label {\n    -ms-flex-item-align: center;\n        -ms-grid-row-align: center;\n        align-self: center;\n    margin: 0.5em 10px 0.5em 0;\n    font-size: 1em; }\n  #optimole-app .header {\n    padding: 0 1.5rem 0; }\n    #optimole-app .header.level {\n      margin-bottom: 0; }\n  #optimole-app .account img {\n    border-top-right-radius: 4px;\n    border-bottom-right-radius: 4px; }\n  #optimole-app .account .label {\n    margin-bottom: 0; }\n  #optimole-app .optimized-images table td, #optimole-app .optimized-images table th {\n    vertical-align: middle; }\n  #optimole-app .media-diff {\n    position: relative;\n    margin: 0 auto; }\n    #optimole-app .media-diff video, #optimole-app .media-diff img {\n      display: block;\n      position: absolute;\n      top: 0;\n      left: 0;\n      width: 100%;\n      height: 100%; }\n  #optimole-app .origin-wrapper {\n    position: absolute;\n    left: 0;\n    top: 0;\n    width: 100%;\n    height: 100%;\n    overflow: hidden;\n    z-index: 1;\n    transform: translateZ(0);\n    will-change: width; }\n  #optimole-app .handle {\n    position: absolute;\n    top: 0;\n    bottom: 0;\n    color: rgba(255, 255, 255, 0.8);\n    background-color: rgba(255, 255, 255, 0.8);\n    width: 2px;\n    cursor: ew-resize;\n    transform: translateX(-50%) translateZ(0);\n    z-index: 2;\n    will-change: left;\n    left: 200px; }\n  #optimole-app .cursor {\n    position: absolute;\n    top: 50%;\n    left: 50%;\n    transform: translateX(-50%) translateZ(0); }\n    #optimole-app .cursor .circle {\n      background-color: rgba(255, 255, 255, 0.8);\n      width: 24px;\n      height: 24px;\n      border-radius: 50%; }\n  #optimole-app .no-padding-right {\n    padding-right: 0px !important; }\n\n.fade-enter-active, .fade-leave-active {\n  transition: opacity .5s; }\n\n.fade-enter, .fade-leave-to {\n  opacity: 0; }\n\n.media_page_optimole #wpbody-content > * {\n  display: none !important; }\n\n.media_page_optimole #wpbody-content > #optimole-app {\n  display: block !important; }\n\n#optimole-app img.optml-image {\n  float: left;\n  max-width: 100px;\n  width: auto;\n  margin: auto; }\n\n#optimole-app img.optml-image-watermark {\n  width: 50px; }\n\n.optml-ratio-feedback .emoji {\n  font-size: 1.5em; }\n\n.optml-ratio-feedback {\n  float: right;\n  padding-right: 20px; }\n\n.optml-image-heading {\n  text-align: left; }\n\nth.optml-image-ratio-heading {\n  text-align: right !important;\n  font-size: 150%; }\n\n@media screen and (max-width: 768px) {\n  li:not(.is-active) > a > span:not(.icon) {\n    visibility: hidden;\n    position: absolute; }\n  nav.tabs li:not(.is-active) {\n    -ms-flex-positive: 0;\n    flex-grow: 0;\n    -ms-flex-negative: 1;\n    flex-shrink: 1; }\n  .tabs .icon {\n    margin-left: 0.5em; } }\n\n.tabs li {\n  transition: flex-grow 1s ease;\n  transition: flex-grow 1s ease, -ms-flex-positive 1s ease; }\n\n#optimole-app .tabs a {\n  margin-bottom: -4px; }\n\n#optimole-app .optml-upgrade {\n  min-width: 200px; }\n\n#optimole-app .is-tab.no-images {\n  min-height: 400px; }\n\n#optimole-app .is-tab {\n  min-height: 700px; }\n", ""]);

// exports


/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _appHeader = __webpack_require__(14);

var _appHeader2 = _interopRequireDefault(_appHeader);

var _cdnDetails = __webpack_require__(19);

var _cdnDetails2 = _interopRequireDefault(_cdnDetails);

var _connectLayout = __webpack_require__(24);

var _connectLayout2 = _interopRequireDefault(_connectLayout);

var _lastImages = __webpack_require__(31);

var _lastImages2 = _interopRequireDefault(_lastImages);

var _apiKeyForm = __webpack_require__(5);

var _apiKeyForm2 = _interopRequireDefault(_apiKeyForm);

var _options = __webpack_require__(36);

var _options2 = _interopRequireDefault(_options);

var _watermarks = __webpack_require__(46);

var _watermarks2 = _interopRequireDefault(_watermarks);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
	name: 'app',
	data: function data() {
		return {
			strings: optimoleDashboardApp.strings,
			home: optimoleDashboardApp.home_url,
			remove_images: optimoleDashboardApp.remove_latest_images === 'yes',
			fetchStatus: false,
			tab: 'dashboard'
		};
	},

	components: {
		AppHeader: _appHeader2.default,
		Options: _options2.default,
		Watermarks: _watermarks2.default,
		ConnectLayout: _connectLayout2.default,
		ApiKeyForm: _apiKeyForm2.default,
		CdnDetails: _cdnDetails2.default,
		LastImages: _lastImages2.default
	},
	mounted: function mounted() {
		var self = this;
		if (this.$store.state.connected) {
			this.$store.dispatch('retrieveOptimizedImages', { waitTime: 0, component: null });
			self.fetchStatus = true;
		}
	},

	methods: {
		changeTab: function changeTab(value) {
			this.tab = value;
		}
	}
	// </script>
	// <style lang="sass-loader">
	// 	@import '../../css/style.scss';
	//
	// 	#optimole-app .tabs a {
	// 		margin-bottom: -4px;
	// 	}
	//
	// 	#optimole-app .optml-upgrade {
	// 		min-width: 200px;
	// 	}
	//
	// 	#optimole-app .is-tab.no-images{
	// 		min-height: 400px;
	// 	}
	// 	#optimole-app .is-tab {
	// 		min-height: 700px;
	// 	}
	// </style>

}; // <template>
// 	<div class="columns is-desktop">
//
// 		<div class="column  ">
// 			<div class="card">
// 				<app-header></app-header>
// 				<div class="card-content">
// 					<div class="content">
// 						<connect-layout v-if="! this.$store.state.connected"></connect-layout>
// 						<transition name="fade" mode="out-in">
// 							<div v-if="this.$store.state.connected">
// 								<div class="tabs is-left is-boxed is-medium">
// 									<ul class="is-marginless">
// 										<li :class="tab === 'dashboard' ? 'is-active' : ''">
// 											<a @click="changeTab('dashboard')" class="is-size-6-mobile">
// 												<span class="icon is-size-6-mobile is-size-6-tablet  dashicons dashicons-admin-home"></span>
// 												<span class="is-size-6-mobile   is-size-6-touch ">{{strings.dashboard_menu_item}}</span>
// 											</a>
// 										</li>
//
// 										<li :class="tab === 'settings' ? 'is-active' : ''">
// 											<a @click="changeTab('settings')" class="is-size-6-mobile  ">
// 												<span class="icon is-size-6-mobile   is-size-6-tablet dashicons dashicons-admin-settings"></span>
// 												<span class="is-size-6-mobile  is-size-6-touch">{{strings.settings_menu_item}}</span>
// 											</a>
// 										</li>
// 										<li :class="tab === 'watermarks' ? 'is-active' : ''">
// 											<a @click="changeTab('watermarks')" class="is-size-6-mobile">
// 												<span class="icon is-size-6-mobile  is-size-6-tablet dashicons dashicons-tag"></span>
// 												<span class="is-size-6-mobile   is-size-6-touch">{{strings.watermarks_menu_item}}</span>
// 											</a>
// 										</li>
// 									</ul>
// 								</div>
//
// 								<div class="is-tab" v-if="tab === 'dashboard' " :class="remove_images ? 'no-images' : '' ">
// 									<div class="notification is-success" v-if="strings.notice_just_activated.length > 0"
// 									     v-html="strings.notice_just_activated"></div>
// 									<api-key-form></api-key-form>
// 									<cdn-details v-if="this.$store.state.userData"></cdn-details>
// 									<hr/>
// 									<last-images :status="fetchStatus" v-if="! remove_images"></last-images>
// 								</div>
// 								<div class="is-tab" v-if=" tab === 'settings'">
// 									<options></options>
// 								</div>
// 								<div class="is-tab" v-if=" tab === 'watermarks'">
// 									<watermarks></watermarks>
// 								</div>
// 							</div>
// 						</transition>
// 					</div>
// 				</div>
//
// 				<div class="level-right">
// 					<p class="level-item"><a href="https://optimole.com" target="_blank">Optimole
// 						v{{strings.version}}</a></p>
// 					<p class="level-item"><a href="https://optimole.com/terms/"
// 					                         target="_blank">{{strings.terms_menu}}</a></p>
// 					<p class="level-item"><a href="https://optimole.com/privacy-policy/" target="_blank">{{strings.privacy_menu}}</a>
// 					</p>
// 					<p class="level-item"><a :href="'https://speedtest.optimole.com/?url=' + home " target="_blank">{{strings.testdrive_menu}}</a>
// 					</p>
// 				</div>
// 			</div>
// 		</div>
// 		<div v-if="this.$store.state.connected && this.$store.state.userData.plan === 'free' " class="column is-narrow is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile">
// 			<div class="card optml-upgrade">
// 				<div class="card-header">
// 					<h3 class="is-size-5 card-header-title"><span class="dashicons dashicons-chart-line"></span>  {{strings.upgrade.title}}</h3>
// 				</div>
// 				<div class="card-content">
// 					<ul>
// 						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_1}}</li>
// 						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_2}}</li>
// 						<li><span class="dashicons dashicons-yes"></span>{{strings.upgrade.reason_3}}</li>
// 					</ul>
// 				</div>
// 				<div class="card-footer  ">
// 					<div class="card-footer-item">
// 					<a href="https://optimole.com#pricing" target="_blank" class="button is-centered is-small is-success"><span class="dashicons dashicons-external"></span>{{strings.upgrade.cta}}</a>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 	</div>
// </template>
//
// <script>

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(15)
__vue_script__ = __webpack_require__(17)
__vue_template__ = __webpack_require__(18)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\app-header.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(16);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-b80baa74&file=app-header.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./app-header.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-b80baa74&file=app-header.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./app-header.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\r\n\t@media ( min-width: 769px ) {\r\n\t\t#optimole-app hr[_v-b80baa74] {\r\n\t\t\tmargin: 0;\r\n\t\t}\r\n\t}\r\n", ""]);

// exports


/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
// <template>
// 	<div>
// 		<div class="header has-text-centered level">
//
// 			<div class="level-left">
// 				<a class="logo level-item" href="https://optimole.com" target="_blank">
// 					<figure class="image is-96x96 is-hidden-touch">
// 						<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" >
// 					</figure>
// 					<figure class="image is-48x48 is-hidden-desktop">
// 						<img :src="logo" :alt="strings.optimole + ' ' + strings.service_details" >
// 					</figure>
// 				</a>
// 				<h3 class="has-text-centered has-text-grey-dark is-size-5 is-size-4-widescreen is-size-6-touch level-item">
// 					<span class="has-text-weight-semibold">
// 					OptiMole - {{strings.service_details}}
// 					</span>
// 				</h3>
// 			</div>
// 			<div class="level-right">
// 				<div class="tags has-addons level-item">
// 					<span class="tag is-dark">{{strings.status}}</span>
// 					<span v-if="connected" class="tag is-success">{{strings.connected}}</span>
// 					<span v-else class="tag is-danger">{{strings.not_connected}}</span>
// 				</div>
// 			</div>
//
// 		</div>
// 		<hr/>
// 	</div>
// </template>
//
// <script>
exports.default = {
	name: "app-header",
	data: function data() {
		return {
			logo: optimoleDashboardApp.assets_url + 'img/logo.png',
			strings: optimoleDashboardApp.strings
		};
	},

	computed: {
		connected: function connected() {
			return this.$store.state.connected;
		}
	}
	// </script>
	//
	// <style scoped>
	// 	@media ( min-width: 769px ) {
	// 		#optimole-app hr {
	// 			margin: 0;
	// 		}
	// 	}
	// </style>

};

/***/ }),
/* 18 */
/***/ (function(module, exports) {

module.exports = "\n\t<div _v-b80baa74=\"\">\n\t\t<div class=\"header has-text-centered level\" _v-b80baa74=\"\">\n\t\t\t\n\t\t\t<div class=\"level-left\" _v-b80baa74=\"\">\n\t\t\t\t<a class=\"logo level-item\" href=\"https://optimole.com\" target=\"_blank\" _v-b80baa74=\"\">\n\t\t\t\t\t<figure class=\"image is-96x96 is-hidden-touch\" _v-b80baa74=\"\">\n\t\t\t\t\t\t<img :src=\"logo\" :alt=\"strings.optimole + ' ' + strings.service_details\" _v-b80baa74=\"\">\n\t\t\t\t\t</figure>\n\t\t\t\t\t<figure class=\"image is-48x48 is-hidden-desktop\" _v-b80baa74=\"\">\n\t\t\t\t\t\t<img :src=\"logo\" :alt=\"strings.optimole + ' ' + strings.service_details\" _v-b80baa74=\"\">\n\t\t\t\t\t</figure>\n\t\t\t\t</a>\n\t\t\t\t<h3 class=\"has-text-centered has-text-grey-dark is-size-5 is-size-4-widescreen is-size-6-touch level-item\" _v-b80baa74=\"\">\n\t\t\t\t\t<span class=\"has-text-weight-semibold\" _v-b80baa74=\"\">\n\t\t\t\t\tOptiMole - {{strings.service_details}}\n\t\t\t\t\t</span>\n\t\t\t\t</h3>\n\t\t\t</div>\n\t\t\t<div class=\"level-right\" _v-b80baa74=\"\">\n\t\t\t\t<div class=\"tags has-addons level-item\" _v-b80baa74=\"\">\n\t\t\t\t\t<span class=\"tag is-dark\" _v-b80baa74=\"\">{{strings.status}}</span>\n\t\t\t\t\t<span v-if=\"connected\" class=\"tag is-success\" _v-b80baa74=\"\">{{strings.connected}}</span>\n\t\t\t\t\t<span v-else=\"\" class=\"tag is-danger\" _v-b80baa74=\"\">{{strings.not_connected}}</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t</div>\n\t\t<hr _v-b80baa74=\"\">\n\t</div>\n";

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(20)
__vue_script__ = __webpack_require__(22)
__vue_template__ = __webpack_require__(23)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\cdn-details.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(21);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-5e7be7e5&file=cdn-details.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./cdn-details.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-5e7be7e5&file=cdn-details.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./cdn-details.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#optimole-app .label[_v-5e7be7e5] {\n\t\tmargin-top: 0;\n\t}\n", ""]);

// exports


/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
// <template>
// 	<div class="cdn-details">
// 		<hr/>
// 		<div class="account level has-text-centered">
// 			<div class="level-left">
// 				<span class="label level-item">{{strings.logged_in_as}}:</span>
// 				<p class="details level-item tags has-addons">
// 					<span class="tag is-light">{{userData.display_name}}</span>
// 					<span class="tag is-paddingless"><img :src="userData.picture" class="image is-24x24 is-rounded" :alt="userData.display_name"></span>
// 				</p>
// 			</div>
// 			<div class="level-right">
// 				<span class="label level-item">{{strings.private_cdn_url}}:</span>
// 				<p class="details level-item tag is-light">{{userData.cdn_key}}.i.optimole.com</p>
// 			</div>
// 		</div>
// 		<hr/>
// 		<div class="level stats">
// 			<div class="level-left">
// 				<div class="level-item">
// 					<div class="tags has-addons">
// 						<span class="tag is-info">{{strings.usage}}:</span>
// 						<span class="tag">{{this.userData.usage_pretty}}</span>
// 					</div>
// 				</div>
// 			</div>
// 			<h4 class="level-item is-size-5 is-marginless has-text-grey">
// 				{{computedPercentage()}}%
// 			</h4>
// 			<div class="level-right">
// 				<div class="level-item">
// 					<div class="tags has-addons">
// 						<span class="tag is-info">{{strings.quota}}:</span>
// 						<span class="tag">{{this.userData.quota_pretty}}</span>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 		<progress class="progress is-success" :value="this.userData.usage" :max="this.userData.quota">60%</progress>
//
// 	</div>
// </template>
//
// <script>
exports.default = {
	name: "cdn-details",
	data: function data() {
		return {
			userData: this.$store.state.userData,
			strings: optimoleDashboardApp.strings
		};
	},

	methods: {
		computedPercentage: function computedPercentage() {
			return (this.userData.usage / this.userData.quota * 100).toFixed(2);
		}
	}
	// </script>
	//
	// <style scoped>
	// 	#optimole-app .label {
	// 		margin-top: 0;
	// 	}
	// </style>

};

/***/ }),
/* 23 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"cdn-details\" _v-5e7be7e5=\"\">\n\t\t<hr _v-5e7be7e5=\"\">\n\t\t<div class=\"account level has-text-centered\" _v-5e7be7e5=\"\">\n\t\t\t<div class=\"level-left\" _v-5e7be7e5=\"\">\n\t\t\t\t<span class=\"label level-item\" _v-5e7be7e5=\"\">{{strings.logged_in_as}}:</span>\n\t\t\t\t<p class=\"details level-item tags has-addons\" _v-5e7be7e5=\"\">\n\t\t\t\t\t<span class=\"tag is-light\" _v-5e7be7e5=\"\">{{userData.display_name}}</span>\n\t\t\t\t\t<span class=\"tag is-paddingless\" _v-5e7be7e5=\"\"><img :src=\"userData.picture\" class=\"image is-24x24 is-rounded\" :alt=\"userData.display_name\" _v-5e7be7e5=\"\"></span>\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class=\"level-right\" _v-5e7be7e5=\"\">\n\t\t\t\t<span class=\"label level-item\" _v-5e7be7e5=\"\">{{strings.private_cdn_url}}:</span>\n\t\t\t\t<p class=\"details level-item tag is-light\" _v-5e7be7e5=\"\">{{userData.cdn_key}}.i.optimole.com</p>\n\t\t\t</div>\n\t\t</div>\n\t\t<hr _v-5e7be7e5=\"\">\n\t\t<div class=\"level stats\" _v-5e7be7e5=\"\">\n\t\t\t<div class=\"level-left\" _v-5e7be7e5=\"\">\n\t\t\t\t<div class=\"level-item\" _v-5e7be7e5=\"\">\n\t\t\t\t\t<div class=\"tags has-addons\" _v-5e7be7e5=\"\">\n\t\t\t\t\t\t<span class=\"tag is-info\" _v-5e7be7e5=\"\">{{strings.usage}}:</span>\n\t\t\t\t\t\t<span class=\"tag\" _v-5e7be7e5=\"\">{{this.userData.usage_pretty}}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<h4 class=\"level-item is-size-5 is-marginless has-text-grey\" _v-5e7be7e5=\"\">\n\t\t\t\t{{computedPercentage()}}%\n\t\t\t</h4>\n\t\t\t<div class=\"level-right\" _v-5e7be7e5=\"\">\n\t\t\t\t<div class=\"level-item\" _v-5e7be7e5=\"\">\n\t\t\t\t\t<div class=\"tags has-addons\" _v-5e7be7e5=\"\">\n\t\t\t\t\t\t<span class=\"tag is-info\" _v-5e7be7e5=\"\">{{strings.quota}}:</span>\n\t\t\t\t\t\t<span class=\"tag\" _v-5e7be7e5=\"\">{{this.userData.quota_pretty}}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<progress class=\"progress is-success\" :value=\"this.userData.usage\" :max=\"this.userData.quota\" _v-5e7be7e5=\"\">60%</progress>\n\n\t</div>\n";

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(25)
__vue_script__ = __webpack_require__(27)
__vue_template__ = __webpack_require__(30)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\connect-layout.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(26);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-c57b780c&file=connect-layout.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./connect-layout.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-c57b780c&file=connect-layout.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./connect-layout.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\tinput[_v-c57b780c], .notification .delete[_v-c57b780c], button[_v-c57b780c] {\n\t\tbox-sizing: border-box !important;\n\t}\n\n", ""]);

// exports


/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _apiKeyForm = __webpack_require__(5);

var _apiKeyForm2 = _interopRequireDefault(_apiKeyForm);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	name: 'connect-layout',
	components: { ApiKeyForm: _apiKeyForm2.default },
	data: function data() {
		return {
			email: optimoleDashboardApp.current_user.email,
			strings: optimoleDashboardApp.strings,
			showApiKey: false,
			error: false,
			showRegisterField: false,
			from_register: false

		};
	},

	computed: {
		isLoading: function isLoading() {
			return this.$store.state.loading;
		},
		isRestApiWorking: function isRestApiWorking() {
			return this.$store.state.apiError;
		}
	},
	methods: {
		toggleApiForm: function toggleApiForm() {
			this.error = false;
			this.from_register = false;
			this.showApiKey = !this.showApiKey;
		},
		registerAccount: function registerAccount() {
			var _this = this;

			if (!this.showRegisterField) {
				this.showRegisterField = true;
				return;
			}
			this.error = false;
			this.$store.dispatch('registerOptimole', {
				email: this.email
			}).then(function (response) {
				if (response.code === 'success') {
					_this.showApiKey = true;
					_this.from_register = true;
				} else {
					_this.error = true;
				}
			});
		}
	}
	// </script>
	// <style scoped>
	// 	input, .notification .delete, button {
	// 		box-sizing: border-box !important;
	// 	}
	//
	// </style>

}; // <template>
// 	<section class="is-clearfix">
// 		<nav class="breadcrumb" aria-label="breadcrumbs" v-if="showApiKey">
// 			<ul>
// 				<li><a @click="toggleApiForm" href="#">{{strings.back_to_register}}</a></li>
// 				<li class="is-active is-marginless" v-if="showApiKey"><a href="#" aria-current="page">{{strings.back_to_connect}}</a>
// 				</li>
// 			</ul>
// 		</nav>
// 		<div class="notification is-danger" v-if="isRestApiWorking" v-html="strings.notice_api_not_working"></div>
// 		<div class="section" v-if="showApiKey">
// 			<div class="notification is-success" v-if="from_register">
// 				{{strings.notification_message_register}}
// 			</div>
// 			<api-key-form></api-key-form>
// 			<hr/>
// 			<div class="columns">
//
// 				<div class="column  columns is-marginless  is-vcentered ">
//
// 					<span class="dashicons dashicons-share column is-2 is-size-3 is-paddingless"></span>
// 					<div class="is-pulled-left column is-10 is-paddingless">
// 						<p class="title is-size-5 ">1. {{strings.step_one_api_title}}</p>
// 						<p class="subtitle is-size-6" v-html="strings.step_one_api_desc"></p>
// 					</div>
// 				</div>
// 				<div class="column   is columns is-vcentered is-marginless">
//
// 					<span class="dashicons dashicons-admin-plugins column is-2 is-size-3 is-paddingless"></span>
// 					<div class="is-pulled-left column is-10 is-paddingless">
// 						<p class="title is-size-5">2. {{strings.step_two_api_title}}</p>
// 						<p class="subtitle is-size-6">{{strings.step_two_api_desc}}</p>
// 					</div>
// 				</div>
// 			</div>
//
// 		</div>
// 		<div class="columns   is-vcentered is-desktop " v-else>
// 			<div class="column  has-text-left is-fluid  is-hidden-touch">
// 				<div class="hero">
// 					<div class="hero-body content">
// 						<p class="title">{{strings.account_needed_heading}}</p>
// 						<p class="subtitle " v-html="strings.account_needed_title"></p>
// 						<div class="  is-hidden-touch">
// 							<div class="columns  is-vcentered  ">
// 								<div class=" is-narrow is-hidden-touch column">
// 									<span class="dashicons   icon dashicons-format-image is-size-4 "></span>
// 								</div>
// 								<div class="column">
// 									<p class="subtitle column is-size-6 is-vcentered has-text-left"
// 									   v-html="strings.account_needed_subtitle_1"></p>
// 								</div>
// 							</div>
// 							<div class="columns  is-vcentered">
// 								<div class=" is-narrow is-hidden-touch column">
// 									<span class="dashicons   icon dashicons-plus is-size-4 "></span>
// 								</div>
// 								<div class="column">
// 									<p class="subtitle column is-size-6 is-vcentered has-text-left"
// 									   v-html="strings.account_needed_subtitle_2"></p>
// 								</div>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
// 			<div class="column is-4-desktop is-full-touch  ">
// 				<p v-html="strings.account_needed_title" class="is-size-6 has-text-centered is-hidden-desktop"></p>
// 				<div class="field     " v-show="showRegisterField">
// 					<label for="optml-email" class="label title is-size-5   is-12">{{strings.email_address_label}}
// 						:</label>
// 					<div class="control   is-12 is-small has-icons-left ">
// 						<input name="optml-email" id="optml-email" class="input is-medium is-fullwidth is-danger"
// 						       type="email"
// 						       v-model="email"/>
// 						<span class="icon is-small is-left dashicons dashicons-email"></span>
//
// 					</div>
//
// 					<p class="help is-danger" v-if="error" v-html="strings.error_register"></p>
// 				</div>
// 				<div class="field   ">
// 					<div class="control ">
// 						<div class="    has-text-centered-mobile">
// 							<button @click="registerAccount" class="button is-fullwidth is-medium is-primary  "
// 							        :class="isLoading ? 'is-loading' :'' ">
// 								<span class="icon dashicons dashicons-admin-users"></span>
// 								<span>{{strings.register_btn}}</span>
// 							</button>
// 						</div>
// 						<hr/>
// 						<div class="   is-right has-text-centered-mobile has-text-right">
// 							<button @click="toggleApiForm" class="button  is-fullwidth is-medium  is-outlined is-info">
// 								<span class="icon dashicons dashicons-admin-network is-small"></span>
// 								<span>{{strings.api_exists}}</span>
// 							</button>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 	</section>
// </template>
//
// <script>

/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
// <template>
// 	<div>
// 		<div class="field has-addons api-key-field">
// 			<label v-if="isConnected"
// 					class="label api-key-label has-text-grey-dark">{{strings.api_key_placeholder}}:</label>
// 			<div class="control is-expanded api-key-control">
// 				<input :type="isConnected ? 'password' : 'text'" :disabled="isConnected" name="api_key" class="input  "
// 						:class="validKey ? '' : 'is-danger'" :placeholder="strings.api_key_placeholder"
// 						v-model="apiKey">
// 			</div>
// 			<div class="control">
// 				<button v-if="! isConnected" class="button button is-success  "
// 						@click="connect" :class="{ 'is-loading' : this.$store.state.isConnecting }">
// 					<span class="icon"><i class="dashicons dashicons-admin-plugins"></i></span>
// 					<span>{{strings.connect_btn}}</span>
// 				</button>
// 				<button v-else class="button is-danger " @click="disconnect"
// 						:class="{ 'is-loading' : this.$store.state.isConnecting }">
// 					<span class="icon"><i class="dashicons dashicons-dismiss"></i></span>
// 					<span>{{strings.disconnect_btn}}</span>
// 				</button>
// 			</div>
// 		</div>
// 		<p v-if="! validKey" class="help is-danger">
// 			{{strings.invalid_key}}
// 		</p>
// 	</div>
// </template>
//
// <script>
exports.default = {
	name: 'api-key-form',
	data: function data() {
		return {
			apiKey: this.$store.state.apiKey ? this.$store.state.apiKey : '',
			connected: this.$store.state.connected,
			strings: optimoleDashboardApp.strings,
			isLoading: false
		};
	},

	mounted: function mounted() {},

	computed: {
		validKey: function validKey() {
			return this.$store.state.apiKeyValidity;
		},
		isConnected: function isConnected() {
			return this.$store.state.connected;
		}
	},
	methods: {
		connect: function connect() {
			this.$store.dispatch('connectOptimole', {
				req: 'Connect to OptiMole',
				apiKey: this.apiKey
			});
		},
		disconnect: function disconnect() {
			this.apiKey = '';
			this.$store.dispatch('disconnectOptimole', {
				req: 'Disconnect from OptiMole'
			});
		}
	}
	// </script>

};

/***/ }),
/* 29 */
/***/ (function(module, exports) {

module.exports = "\r\n\t<div>\r\n\t\t<div class=\"field has-addons api-key-field\">\r\n\t\t\t<label v-if=\"isConnected\"\r\n\t\t\t\t\tclass=\"label api-key-label has-text-grey-dark\">{{strings.api_key_placeholder}}:</label>\r\n\t\t\t<div class=\"control is-expanded api-key-control\">\r\n\t\t\t\t<input :type=\"isConnected ? 'password' : 'text'\" :disabled=\"isConnected\" name=\"api_key\" class=\"input  \"\r\n\t\t\t\t\t\t:class=\"validKey ? '' : 'is-danger'\" :placeholder=\"strings.api_key_placeholder\"\r\n\t\t\t\t\t\tv-model=\"apiKey\">\r\n\t\t\t</div>\r\n\t\t\t<div class=\"control\">\r\n\t\t\t\t<button v-if=\"! isConnected\" class=\"button button is-success  \"\r\n\t\t\t\t\t\t@click=\"connect\" :class=\"{ 'is-loading' : this.$store.state.isConnecting }\">\r\n\t\t\t\t\t<span class=\"icon\"><i class=\"dashicons dashicons-admin-plugins\"></i></span>\r\n\t\t\t\t\t<span>{{strings.connect_btn}}</span>\r\n\t\t\t\t</button>\r\n\t\t\t\t<button v-else class=\"button is-danger \" @click=\"disconnect\"\r\n\t\t\t\t\t\t:class=\"{ 'is-loading' : this.$store.state.isConnecting }\">\r\n\t\t\t\t\t<span class=\"icon\"><i class=\"dashicons dashicons-dismiss\"></i></span>\r\n\t\t\t\t\t<span>{{strings.disconnect_btn}}</span>\r\n\t\t\t\t</button>\r\n\t\t\t</div>\r\n\t\t</div>\r\n\t\t<p v-if=\"! validKey\" class=\"help is-danger\">\r\n\t\t\t{{strings.invalid_key}}\r\n\t\t</p>\r\n\t</div>\r\n";

/***/ }),
/* 30 */
/***/ (function(module, exports) {

module.exports = "\n\t<section class=\"is-clearfix\" _v-c57b780c=\"\">\n\t\t<nav class=\"breadcrumb\" aria-label=\"breadcrumbs\" v-if=\"showApiKey\" _v-c57b780c=\"\">\n\t\t\t<ul _v-c57b780c=\"\">\n\t\t\t\t<li _v-c57b780c=\"\"><a @click=\"toggleApiForm\" href=\"#\" _v-c57b780c=\"\">{{strings.back_to_register}}</a></li>\n\t\t\t\t<li class=\"is-active is-marginless\" v-if=\"showApiKey\" _v-c57b780c=\"\"><a href=\"#\" aria-current=\"page\" _v-c57b780c=\"\">{{strings.back_to_connect}}</a>\n\t\t\t\t</li>\n\t\t\t</ul>\n\t\t</nav>\n\t\t<div class=\"notification is-danger\" v-if=\"isRestApiWorking\" v-html=\"strings.notice_api_not_working\" _v-c57b780c=\"\"></div>\n\t\t<div class=\"section\" v-if=\"showApiKey\" _v-c57b780c=\"\">\n\t\t\t<div class=\"notification is-success\" v-if=\"from_register\" _v-c57b780c=\"\">\n\t\t\t\t{{strings.notification_message_register}}\n\t\t\t</div>\n\t\t\t<api-key-form _v-c57b780c=\"\"></api-key-form>\n\t\t\t<hr _v-c57b780c=\"\">\n\t\t\t<div class=\"columns\" _v-c57b780c=\"\">\n\t\t\t\t\n\t\t\t\t<div class=\"column  columns is-marginless  is-vcentered \" _v-c57b780c=\"\">\n\t\t\t\t\t\n\t\t\t\t\t<span class=\"dashicons dashicons-share column is-2 is-size-3 is-paddingless\" _v-c57b780c=\"\"></span>\n\t\t\t\t\t<div class=\"is-pulled-left column is-10 is-paddingless\" _v-c57b780c=\"\">\n\t\t\t\t\t\t<p class=\"title is-size-5 \" _v-c57b780c=\"\">1. {{strings.step_one_api_title}}</p>\n\t\t\t\t\t\t<p class=\"subtitle is-size-6\" v-html=\"strings.step_one_api_desc\" _v-c57b780c=\"\"></p>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"column   is columns is-vcentered is-marginless\" _v-c57b780c=\"\">\n\t\t\t\t\t\n\t\t\t\t\t<span class=\"dashicons dashicons-admin-plugins column is-2 is-size-3 is-paddingless\" _v-c57b780c=\"\"></span>\n\t\t\t\t\t<div class=\"is-pulled-left column is-10 is-paddingless\" _v-c57b780c=\"\">\n\t\t\t\t\t\t<p class=\"title is-size-5\" _v-c57b780c=\"\">2. {{strings.step_two_api_title}}</p>\n\t\t\t\t\t\t<p class=\"subtitle is-size-6\" _v-c57b780c=\"\">{{strings.step_two_api_desc}}</p>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\n\t\t</div>\n\t\t<div class=\"columns   is-vcentered is-desktop \" v-else=\"\" _v-c57b780c=\"\">\n\t\t\t<div class=\"column  has-text-left is-fluid  is-hidden-touch\" _v-c57b780c=\"\">\n\t\t\t\t<div class=\"hero\" _v-c57b780c=\"\">\n\t\t\t\t\t<div class=\"hero-body content\" _v-c57b780c=\"\">\n\t\t\t\t\t\t<p class=\"title\" _v-c57b780c=\"\">{{strings.account_needed_heading}}</p>\n\t\t\t\t\t\t<p class=\"subtitle \" v-html=\"strings.account_needed_title\" _v-c57b780c=\"\"></p>\n\t\t\t\t\t\t<div class=\"  is-hidden-touch\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t<div class=\"columns  is-vcentered  \" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t<div class=\" is-narrow is-hidden-touch column\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t\t<span class=\"dashicons   icon dashicons-format-image is-size-4 \" _v-c57b780c=\"\"></span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"column\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t\t<p class=\"subtitle column is-size-6 is-vcentered has-text-left\" v-html=\"strings.account_needed_subtitle_1\" _v-c57b780c=\"\"></p>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"columns  is-vcentered\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t<div class=\" is-narrow is-hidden-touch column\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t\t<span class=\"dashicons   icon dashicons-plus is-size-4 \" _v-c57b780c=\"\"></span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"column\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t\t<p class=\"subtitle column is-size-6 is-vcentered has-text-left\" v-html=\"strings.account_needed_subtitle_2\" _v-c57b780c=\"\"></p>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"column is-4-desktop is-full-touch  \" _v-c57b780c=\"\">\n\t\t\t\t<p v-html=\"strings.account_needed_title\" class=\"is-size-6 has-text-centered is-hidden-desktop\" _v-c57b780c=\"\"></p>\n\t\t\t\t<div class=\"field     \" v-show=\"showRegisterField\" _v-c57b780c=\"\">\n\t\t\t\t\t<label for=\"optml-email\" class=\"label title is-size-5   is-12\" _v-c57b780c=\"\">{{strings.email_address_label}}\n\t\t\t\t\t\t:</label>\n\t\t\t\t\t<div class=\"control   is-12 is-small has-icons-left \" _v-c57b780c=\"\">\n\t\t\t\t\t\t<input name=\"optml-email\" id=\"optml-email\" class=\"input is-medium is-fullwidth is-danger\" type=\"email\" v-model=\"email\" _v-c57b780c=\"\">\n\t\t\t\t\t\t<span class=\"icon is-small is-left dashicons dashicons-email\" _v-c57b780c=\"\"></span>\n\t\t\t\t\t\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t\t<p class=\"help is-danger\" v-if=\"error\" v-html=\"strings.error_register\" _v-c57b780c=\"\"></p>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"field   \" _v-c57b780c=\"\">\n\t\t\t\t\t<div class=\"control \" _v-c57b780c=\"\">\n\t\t\t\t\t\t<div class=\"    has-text-centered-mobile\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t<button @click=\"registerAccount\" class=\"button is-fullwidth is-medium is-primary  \" :class=\"isLoading ? 'is-loading' :'' \" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-admin-users\" _v-c57b780c=\"\"></span>\n\t\t\t\t\t\t\t\t<span _v-c57b780c=\"\">{{strings.register_btn}}</span>\n\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-c57b780c=\"\">\n\t\t\t\t\t\t<div class=\"   is-right has-text-centered-mobile has-text-right\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t<button @click=\"toggleApiForm\" class=\"button  is-fullwidth is-medium  is-outlined is-info\" _v-c57b780c=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-admin-network is-small\" _v-c57b780c=\"\"></span>\n\t\t\t\t\t\t\t\t<span _v-c57b780c=\"\">{{strings.api_exists}}</span>\n\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t</section>\n";

/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(32)
__vue_script__ = __webpack_require__(34)
__vue_template__ = __webpack_require__(35)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\last-images.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(33);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-0659935c&file=last-images.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./last-images.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-0659935c&file=last-images.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./last-images.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t.loader[_v-0659935c] {\n\t\tmargin: 0 auto;\n\t\tfont-size: 10em;\n\t\tborder-left: 2px solid #888 !important;\n\t\tborder-bottom: 2px solid #888 !important;\n\t\tmargin-top: 0.2em;\n\t}\n\t\n\t.progress[_v-0659935c]::-webkit-progress-value {\n\t\ttransition: width 0.5s ease;\n\t}\n", ""]);

// exports


/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
// <template>
// 	<div>
// 		<div class="optimized-images" v-if="! loading ">
// 			<div v-if="!noImages">
// 				<h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
// 				<table class="table is-striped is-hoverable is-fullwidth">
// 					<thead>
// 					<tr>
// 						<th class="optml-image-heading">{{strings.image}}</th>
// 						<th class="optml-image-ratio-heading">{{strings.compression}}</th>
// 					</tr>
// 					</thead>
// 					<tbody>
// 					<tr v-for="(item, index) in imageData">
// 						<td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>
// 						<td><p
// 								class="optml-ratio-feedback"
// 								v-html="compressionRate(item.ex_size_raw, item.new_size_raw)"></p>
// 						</td>
// 					</tr>
// 					</tbody>
// 				</table>
// 			</div>
// 		</div>
// 		<div v-else>
// 			<iframe width="1" height="1" :src="home_url" style="visibility: hidden"></iframe>
// 			<h6 class="has-text-centered">{{strings.loading_latest_images}}</h6>
// 			<progress class="progress is-large" :value="startTime" :max="maxTime"></progress>
// 		</div>
// 		<table class="table is-striped is-hoverable is-fullwidth" v-if="noImages">
// 			<thead>
// 			<tr>
// 				<th class="optml-image-heading has-text-centered" v-html="strings.no_images_found"></th>
// 			</tr>
// 			</thead>
// 		</table>
// 	</div>
// </template>
//
// <script>

exports.default = {
	name: "last-images",
	data: function data() {
		return {
			loading: true,
			startTime: 0,
			maxTime: 20,
			noImages: false,
			home_url: optimoleDashboardApp.home_url,
			strings: optimoleDashboardApp.strings.latest_images
		};
	},

	props: {
		status: status
	},
	mounted: function mounted() {
		if (this.$store.state.optimizedImages.length > 0) {
			this.loading = false;
			return;
		}
		this.doProgressBar();
		this.$store.dispatch('retrieveOptimizedImages', { waitTime: this.maxTime * 1000, component: this });
	},

	watch: {
		imageData: function imageData() {
			var _this = this;

			if (this.imageData.length > 0) {
				this.startTime = this.maxTime;
				setTimeout(function () {
					_this.loading = false;
				}, 1000);
			}
		}
	},
	computed: {
		imageData: function imageData() {

			return this.$store.state.optimizedImages !== null ? this.$store.state.optimizedImages : [];
		}
	},
	methods: {
		doProgressBar: function doProgressBar() {
			if (this.startTime === this.maxTime) {
				return;
			}
			this.startTime++;
			//console.log(this.startTime);
			setTimeout(this.doProgressBar, 1000);
		},
		compressionRate: function compressionRate(oldSize, newSize) {
			var value = (parseFloat(oldSize / newSize * 100) - 100).toFixed(1);
			if (value < 1) {
				return this.strings.same_size;
			}
			if (value > 1 && value < 25) {
				return this.strings.small_optimization.replace('{ratio}', value.toString() + '%');
			}
			if (value > 25 && value < 100) {
				return this.strings.medium_optimization.replace('{ratio}', value.toString() + '%');
			}
			if (value > 100) {
				return this.strings.big_optimization.replace('{ratio}', (Math.floor(value / 10 + 10) / 10).toFixed(1).toString() + 'x');
			}
		}
	}
	// </script>
	//
	// <style scoped>
	// 	.loader {
	// 		margin: 0 auto;
	// 		font-size: 10em;
	// 		border-left: 2px solid #888 !important;
	// 		border-bottom: 2px solid #888 !important;
	// 		margin-top: 0.2em;
	// 	}
	//
	// 	.progress::-webkit-progress-value {
	// 		transition: width 0.5s ease;
	// 	}
	// </style>

};

/***/ }),
/* 35 */
/***/ (function(module, exports) {

module.exports = "\n\t<div _v-0659935c=\"\">\n\t\t<div class=\"optimized-images\" v-if=\"! loading \" _v-0659935c=\"\">\n\t\t\t<div v-if=\"!noImages\" _v-0659935c=\"\">\n\t\t\t\t<h3 class=\"has-text-centered\" _v-0659935c=\"\">{{strings.last}} {{strings.optimized_images}}</h3>\n\t\t\t\t<table class=\"table is-striped is-hoverable is-fullwidth\" _v-0659935c=\"\">\n\t\t\t\t\t<thead _v-0659935c=\"\">\n\t\t\t\t\t<tr _v-0659935c=\"\">\n\t\t\t\t\t\t<th class=\"optml-image-heading\" _v-0659935c=\"\">{{strings.image}}</th>\n\t\t\t\t\t\t<th class=\"optml-image-ratio-heading\" _v-0659935c=\"\">{{strings.compression}}</th>\n\t\t\t\t\t</tr>\n\t\t\t\t\t</thead>\n\t\t\t\t\t<tbody _v-0659935c=\"\">\n\t\t\t\t\t<tr v-for=\"(item, index) in imageData\" _v-0659935c=\"\">\n\t\t\t\t\t\t<td _v-0659935c=\"\"><a :href=\"item.url\" target=\"_blank\" _v-0659935c=\"\"><img :src=\"item.url\" class=\"optml-image\" _v-0659935c=\"\"></a></td>\n\t\t\t\t\t\t<td _v-0659935c=\"\"><p class=\"optml-ratio-feedback\" v-html=\"compressionRate(item.ex_size_raw, item.new_size_raw)\" _v-0659935c=\"\"></p>\n\t\t\t\t\t\t</td>\n\t\t\t\t\t</tr>\n\t\t\t\t\t</tbody>\n\t\t\t\t</table>\n\t\t\t</div>\n\t\t</div>\n\t\t<div v-else=\"\" _v-0659935c=\"\">\n\t\t\t<iframe width=\"1\" height=\"1\" :src=\"home_url\" style=\"visibility: hidden\" _v-0659935c=\"\"></iframe>\n\t\t\t<h6 class=\"has-text-centered\" _v-0659935c=\"\">{{strings.loading_latest_images}}</h6>\n\t\t\t<progress class=\"progress is-large\" :value=\"startTime\" :max=\"maxTime\" _v-0659935c=\"\"></progress>\n\t\t</div>\n\t\t<table class=\"table is-striped is-hoverable is-fullwidth\" v-if=\"noImages\" _v-0659935c=\"\">\n\t\t\t<thead _v-0659935c=\"\">\n\t\t\t<tr _v-0659935c=\"\">\n\t\t\t\t<th class=\"optml-image-heading has-text-centered\" v-html=\"strings.no_images_found\" _v-0659935c=\"\"></th>\n\t\t\t</tr>\n\t\t\t</thead>\n\t\t</table>\n\t</div>\n";

/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(37)
__vue_script__ = __webpack_require__(39)
__vue_template__ = __webpack_require__(45)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\options.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(38);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-546bc561&file=options.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./options.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-546bc561&file=options.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./options.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 38 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t.saving--option[_v-546bc561] {\n\t\topacity: .75;\n\t}\n\t\n\t#optimole-app .notification[_v-546bc561] {\n\t\tpadding: 0.5rem;\n\t}\n\t\n\t#optimole-app .image[_v-546bc561] {\n\t\ttext-align: center;\n\t}\n\t\n\t#optimole-app .visual-compare img[_v-546bc561] {\n\t\twidth: 100%;\n\t}\n\t\n\t#optimole-app .icon.dashicons.dashicons-controls-pause[_v-546bc561] {\n\t\ttransform: rotate(90deg);\n\t}\n\t\n\t#optimole-app .image img[_v-546bc561] {\n\t\t\n\t\tmax-height: 300px;\n\t\twidth: auto;\n\t\t\n\t}\n\t\n\t.field[_v-546bc561]:nth-child(even) {\n\t\t-ms-flex-pack: end;\n\t\t    justify-content: flex-end;\n\t}\n\t\n\t#optimole-app .button.is-selected:not(.is-info) span[_v-546bc561] {\n\t\tcolor: #008ec2;\n\t}\n\t\n\t#optimole-app p.compress-optimization-ratio-done strong[_v-546bc561] {\n\t\t\n\t\tcolor: #44464e;\n\t}\n\t\n\t#optimole-app p.compress-optimization-ratio-nothing[_v-546bc561],\n\t#optimole-app p.compress-optimization-ratio-done[_v-546bc561] {\n\t\tposition: absolute;\n\t\tright: 10px;\n\t\tcolor: #44464e;\n\t\t\n\t\tfont-size: 0.9rem !important;\n\t\tline-height: 1.4rem;\n\t}\n\t\n\t#optimole-app p.compress-optimization-ratio-nothing[_v-546bc561] {\n\t\tcolor: #fff;\n\t\tleft: 20px;\n\t}\n\t\n\t#optimole-app .progress-wrapper[_v-546bc561] {\n\t\tposition: relative;\n\t}\n", ""]);

// exports


/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _image_diff = __webpack_require__(40);

var _image_diff2 = _interopRequireDefault(_image_diff);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	name: "options",
	components: { Image_diff: _image_diff2.default },
	data: function data() {
		return {
			strings: optimoleDashboardApp.strings.options_strings,
			all_strings: optimoleDashboardApp.strings,
			showNotification: false,
			loading_images: false,
			showComparison: false,
			showSave: false,
			new_data: {}

		};
	},

	mounted: function mounted() {
		this.updateSampleImage(this.site_settings.quality);
	},
	methods: {
		newSample: function newSample() {
			this.updateSampleImage(this.new_data.quality, 'yes');
		},
		changeQuality: function changeQuality(value) {
			if (this.showComparison !== true) {
				this.showComparison = true;
			}
			this.updateSampleImage(value);
			this.qualityStatus = value;
		},
		updateSampleImage: function updateSampleImage(value) {
			var _this = this;

			var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'no';

			this.$store.dispatch('sampleRate', {
				quality: value,
				force: force,
				component: this
			}).then(function (response) {

				setTimeout(function () {
					_this.showNotification = false;
				}, 1000);
			}, function (response) {});
		},
		saveChanges: function saveChanges() {
			this.$store.dispatch('saveSettings', {
				settings: this.new_data
			});
		},
		isActiveQuality: function isActiveQuality(q) {
			if (this.new_data && this.new_data.quality) {
				return this.new_data.quality === q;
			}
			return this.site_settings.quality === q;
		}
	},
	computed: {
		site_settings: function site_settings() {
			return this.$store.state.site_settings;
		},

		getReplacerStatus: {
			get: function get() {
				return !(this.site_settings.image_replacer === 'disabled');
			},
			set: function set(value) {
				this.new_data.image_replacer = value ? 'enabled' : 'disabled';
			}
		},
		adminBarItemStatus: {
			set: function set(value) {
				this.new_data.admin_bar_item = value ? 'enabled' : 'disabled';
				if (value) {
					document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'block';
				} else {
					document.getElementById("wp-admin-bar-optml_image_quota").style.display = 'none';
				}
			},
			get: function get() {
				return !(this.site_settings.admin_bar_item === 'disabled');
			}
		},
		lazyLoadStatus: {
			set: function set(value) {
				this.new_data.lazyload = value ? 'enabled' : 'disabled';
			},
			get: function get() {
				return !(this.site_settings.lazyload === 'disabled');
			}
		},
		widthStatus: {
			set: function set(value) {
				this.new_data.max_width = value;
			},
			get: function get() {

				return this.site_settings.max_width;
			}
		},
		heightStatus: {
			set: function set(value) {
				this.new_data.max_height = value;
			},
			get: function get() {

				return this.site_settings.max_height;
			}
		},
		qualityStatus: {
			set: function set(value) {
				this.new_data.quality = value;
			},
			get: function get() {
				return this.site_settings.quality;
			}
		},
		compressionRatio: function compressionRatio() {
			return (parseFloat(this.sample_images.optimized_size / this.sample_images.original_size) * 100).toFixed(0);
		},
		sample_images: function sample_images() {
			return this.$store.state.sample_rate;
		},
		loading: function loading() {
			return this.$store.state.loading;
		}
	}
	// </script>
	//
	// <style scoped>
	// 	.saving--option {
	// 		opacity: .75;
	// 	}
	//
	// 	#optimole-app .notification {
	// 		padding: 0.5rem;
	// 	}
	//
	// 	#optimole-app .image {
	// 		text-align: center;
	// 	}
	//
	// 	#optimole-app .visual-compare img {
	// 		width: 100%;
	// 	}
	//
	// 	#optimole-app .icon.dashicons.dashicons-controls-pause {
	// 		transform: rotate(90deg);
	// 	}
	//
	// 	#optimole-app .image img {
	//
	// 		max-height: 300px;
	// 		width: auto;
	//
	// 	}
	//
	// 	.field:nth-child(even) {
	// 		justify-content: flex-end;
	// 	}
	//
	// 	#optimole-app .button.is-selected:not(.is-info) span {
	// 		color: #008ec2;
	// 	}
	//
	// 	#optimole-app p.compress-optimization-ratio-done strong {
	//
	// 		color: #44464e;
	// 	}
	//
	// 	#optimole-app p.compress-optimization-ratio-nothing,
	// 	#optimole-app p.compress-optimization-ratio-done {
	// 		position: absolute;
	// 		right: 10px;
	// 		color: #44464e;
	//
	// 		font-size: 0.9rem !important;
	// 		line-height: 1.4rem;
	// 	}
	//
	// 	#optimole-app p.compress-optimization-ratio-nothing {
	// 		color: #fff;
	// 		left: 20px;
	// 	}
	//
	// 	#optimole-app .progress-wrapper {
	// 		position: relative;
	// 	}
	// </style>

}; // <template>
// 	<div  :class="{ 'saving--option' : this.$store.state.loading }">
//
// 		<div class="field  columns">
// 			<label class="label column has-text-grey-dark">
// 				{{strings.enable_image_replace}}
// 				<p class="is-italic has-text-weight-normal">
// 					{{strings.replacer_desc}}
// 				</p>
// 			</label>
// 			<div class="column ">
// 				<toggle-button :class="'has-text-dark'"
// 				               v-model="getReplacerStatus"
// 				               :disabled="this.$store.state.loading"
// 				               :labels="{checked: strings.enabled, unchecked: strings.disabled}"
// 				               :width="80"
// 				               :height="25"
// 				               color="#008ec2"></toggle-button>
// 			</div>
//
// 		</div>
// 		<div class="field  is-fullwidth columns">
// 			<label class="label column has-text-grey-dark">
// 				{{strings.toggle_ab_item}}
// 				<p class="is-italic has-text-weight-normal">
// 					{{strings.admin_bar_desc}}
// 				</p>
// 			</label>
//
// 			<div class="column ">
// 				<toggle-button :class="'has-text-dark'"
// 				               v-model="adminBarItemStatus"
// 				               :disabled="this.$store.state.loading"
// 				               :labels="{checked: strings.show, unchecked: strings.hide}"
// 				               :width="80"
// 				               :height="25"
// 				               color="#008ec2"></toggle-button>
// 			</div>
// 		</div>
//
// 		<div class="field  is-fullwidth columns">
// 			<label class="label column has-text-grey-dark">
// 				{{strings.toggle_lazyload}}
// 				<p class="is-italic has-text-weight-normal">
// 					{{strings.lazyload_desc}}
// 				</p>
// 			</label>
//
// 			<div class="column ">
// 				<toggle-button :class="'has-text-dark'"
// 							   v-model="lazyLoadStatus"
// 							   :disabled="this.$store.state.loading"
// 							   :labels="{checked: strings.enabled, unchecked: strings.disabled}"
// 							   :width="80"
// 							   :height="25"
// 							   color="#008ec2"></toggle-button>
// 			</div>
// 		</div>
//
// 		<div class="field  is-fullwidth columns n">
// 			<label class="label is-half column has-text-grey-dark no-padding-right ">
// 				{{strings.size_title}}
// 				<p class="is-italic has-text-weight-normal">
// 					{{strings.size_desc}}
// 				</p>
// 			</label>
//
// 			<div class="column is-paddingless">
// 				<div class="columns">
// 					<div class="field column is-narrow has-addons">
// 						<p class="control">
// 							<a class="button is-small is-static">
// 								{{strings.width_field}}
// 							</a>
// 						</p>
// 						<p class="control ">
// 							<input v-model="widthStatus" class="input is-small" type="number" min="100"
// 							       max="10000">
// 						</p>
// 					</div>
// 					<div class="field column is-small has-addons">
// 						<p class="control">
// 							<a class="button is-small is-static">
// 								{{strings.height_field}}
// 							</a>
// 						</p>
// 						<p class="control  ">
// 							<input v-model="heightStatus" class="input is-small" type="number" min="100"
// 							       max="10000">
// 						</p>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
//
// 		<div class="field  columns">
// 			<label class="label column has-text-grey-dark">
// 				{{strings.quality_title}}
// 				<p class="is-italic has-text-weight-normal">
// 					{{strings.quality_desc}}
// 				</p>
// 			</label>
// 			<div class="column  buttons ">
// 				<div class="field columns  ">
// 					<div class="column  field has-addons">
// 						<p class="control">
// 							<a @click="changeQuality('auto')"
// 							   :class="{ 'is-info':isActiveQuality ( 'auto'), '  is-selected':site_settings.quality === 'auto'  }"
// 							   class="button   is-small is-rounded">
// 								<span class="icon dashicons dashicons-marker"></span>
// 								<span>{{strings.auto_q_title}}</span>
// 							</a>
// 						</p>
// 						<p class="control">
// 							<a @click="changeQuality('high_c')"
// 							   :class="{  'is-info': isActiveQuality ('high_c'), 'is-selected':site_settings.quality === 'high_c'   }"
// 							   class="button    is-rounded is-small">
// 								<span class="icon dashicons dashicons-menu"></span>
// 								<span>{{strings.high_q_title}}</span>
// 							</a>
// 						</p>
//
// 						<p class="control">
// 							<a @click="changeQuality('medium_c')"
// 							   :class="{  'is-info': isActiveQuality( 'medium_c' ), '  is-selected':site_settings.quality === 'medium_c'  }"
// 							   class="button   is-small">
// 								<span class="icon dashicons dashicons-controls-pause"></span>
// 								<span class=" ">{{strings.medium_q_title}}</span>
// 							</a>
// 						</p>
//
// 						<p class="control">
// 							<a @click="changeQuality('low_c')"
// 							   :class="{  'is-info':isActiveQuality( 'low_c' ), ' is-selected':site_settings.quality === 'low_c'  }"
// 							   class="button   is-small">
// 								<span class="icon dashicons dashicons-minus  "></span>
// 								<span>{{strings.low_q_title}}</span>
// 							</a>
// 						</p>
// 					</div>
// 					<p class="control column has-text-centered-desktop has-text-left-touch  ">
// 						<a @click="saveChanges()" class="button is-small is-success "
// 						   :class="{'is-loading':loading}">
// 							<span class="dashicons dashicons-yes icon"></span>
// 							<span>	{{strings.save_changes}}</span>
// 						</a>
// 					</p>
// 				</div>
// 			</div>
// 		</div>
//         <div v-if="showComparison">
//             <div v-if="loading_images" class="has-text-centered subtitle ">{{strings.sample_image_loading}}<span
//                     class="loader has-text-black-bis icon is-small"></span>
//             </div>
//             <div v-else-if="sample_images.id && sample_images.original_size > 0">
//                 <p class="title has-text-centered is-5 is-size-6-mobile">{{strings.quality_slider_desc}}</p>
//                 <div class="columns is-centered is-vcentered is-multiline is-mobile">
//                     <a @click="newSample()"
//                        class="button is-small is-pulled-right">
//                         <span class="icon dashicons dashicons-image-rotate"></span>
//                     </a>
//                     <div class="column visual-compare  is-half-fullhd is-half-desktop is-three-quarters-touch is-12-mobile  ">
//                         <div class="is-full progress-wrapper">
//                             <p class="subtitle is-size-6 compress-optimization-ratio-done has-text-centered"
//                                v-if="compressionRatio > 0">
//                                 <strong>{{( 100 - compressionRatio )}}%</strong> smaller </p>
//                             <p class="subtitle  compress-optimization-ratio-nothing is-size-6 has-text-centered" v-else>
//                                 {{all_strings.latest_images.same_size}}
//                             </p>
//                             <progress class="  progress is-large is-success "
//                                       :value="compressionRatio"
//                                       :max="100">
//                             </progress>
//                             <hr/>
//                         </div>
//                         <Image_diff class="is-fullwidth" value="50" :first_label="strings.image_1_label"
//                                     :second_label="strings.image_2_label">
//                             <img slot="first" :src="sample_images.optimized">
//                             <img slot="second" :src="sample_images.original">
//                         </Image_diff>
//                     </div>
//                 </div>
//             </div>
//             <div v-else-if=" sample_images.id < 0">
//                 <p class="title has-text-centered is-5 is-size-6-mobile">{{strings.no_images_found}}</p>
//             </div>
//         </div>
//
// 	</div>
//
// </template>
//
// <script>

/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(41)
__vue_script__ = __webpack_require__(43)
__vue_template__ = __webpack_require__(44)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\image_diff.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(42);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-1a7eb376&file=image_diff.vue!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./image_diff.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-1a7eb376&file=image_diff.vue!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./image_diff.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t:root {\n\t\t--handle-bg: #4a4a4a;\n\t\t--handle-width: 30px;\n\t\t--handle-height: 30px;\n\t\t--handle-chevron-size: 20px;\n\t\t\n\t\t--handle-line-bg: #4a4a4a;\n\t\t--handle-line-width: 2px;\n\t\t--handle-line-height: 100%;\n\t\t\n\t\t--z-index-handle: 5;\n\t\t--z-index-handle-line: 4;\n\t\t--z-index-range-input: 6;\n\t}\n\t\n\t.compare-wrapper {\n\t\tposition: relative;\n\t}\n\t\n\t.compare,\n\t.compare__content {\n\t\tposition: relative;\n\t\theight: 100%;\n\t}\n\t\n\t.compare-overlay.initial,\n\t.compare__content.initial {\n\t\topacity: 0.2;\n\t}\n\t\n\t.compare-overlay,\n\t.compare__content {\n\t\ttransition: opacity .3s ease-in-out;\n\t}\n\t\n\t.compare-overlay {\n\t\tposition: absolute;\n\t\toverflow: hidden;\n\t\theight: 100%;\n\t\ttop: 0;\n\t}\n\t\n\t.compare-overlay__content {\n\t\tposition: relative;\n\t\theight: 100%;\n\t\twidth: 100%;\n\t}\n\t\n\t.handle__arrow {\n\t\tposition: absolute;\n\t\twidth: var(--handle-chevron-size);\n\t}\n\t\n\t.handle__arrow--l {\n\t\tleft: 0;\n\t}\n\t\n\t.handle__arrow--r {\n\t\tright: 0;\n\t}\n\t\n\t.handle-wrap {\n\t\tdisplay: -ms-flexbox;\n\t\tdisplay: flex;\n\t\t-ms-flex-align: center;\n\t\t    align-items: center;\n\t\t-ms-flex-pack: center;\n\t\t    justify-content: center;\n\t\tposition: absolute;\n\t\ttop: 50%;\n\t\theight: 100%;\n\t\ttransform: translate(-50%, -50%);\n\t\tz-index: var(--z-index-handle);\n\t}\n\t\n\t.handle-icon {\n\t\tdisplay: -ms-flexbox;\n\t\tdisplay: flex;\n\t\t-ms-flex-align: center;\n\t\t    align-items: center;\n\t\t-ms-flex-pack: center;\n\t\t    justify-content: center;\n\t\tcolor: white;\n\t\tbackground: var(--handle-bg);\n\t\tborder-radius: 50%;\n\t\twidth: var(--handle-width);\n\t\theight: var(--handle-height);\n\t}\n\t\n\t.handle-line {\n\t\tcontent: '';\n\t\tposition: absolute;\n\t\ttop: 0;\n\t\twidth: var(--handle-line-width);\n\t\theight: 100%;\n\t\tbackground: var(--handle-line-bg);\n\t\tz-index: var(--z-index-handle-line);\n\t\tpointer-events: none;\n\t\t-webkit-user-select: none;\n\t\t   -moz-user-select: none;\n\t\t    -ms-user-select: none;\n\t\t        user-select: none;\n\t}\n\t\n\t.compare__range {\n\t\tposition: absolute;\n\t\tcursor: ew-resize;\n\t\tleft: calc(var(--handle-width) / -2);\n\t\twidth: calc(100% + var(--handle-width));\n\t\ttransform: translatey(-50%);\n\t\ttop: calc(50%);\n\t\tz-index: var(--z-index-range-input);\n\t\t-webkit-appearance: none;\n\t\theight: var(--handle-height);\n\t\t/* debugging purposes only */\n\t\tbackground: rgba(0, 0, 0, .4);\n\t\topacity: .0;\n\t}\n\t\n\t.object-fit-cover {\n\t\t-o-object-fit: cover;\n\t\t   object-fit: cover;\n\t}\n\t\n\t.compare_optimized {\n\t\tright: 5px;\n\t}\n\t\n\t.compare_original {\n\t\tleft: 5px;\n\t}\n\t\n\t.compare_label {\n\t\tposition: absolute;\n\t\tz-index: 999;\n\t\tbackground: #444;\n\t\topacity: 0.6;\n\t\tpadding: 0.5rem;\n\t\tborder-radius: 0.2rem;\n\t\tcolor: #fff;\n\t\ttop: 10px;\n\t\t\n\t}\n", ""]);

// exports


/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
// <template>
// 	<div class="compare-wrapper" @mouseenter="removeInitial">
// 		<div class="compare">
//
// 			<span class="compare_label compare_original">{{this.first_label}}</span>
// 			<span class="compare_label compare_optimized">{{this.second_label}}</span>
// 			<div class="compare__content " :class="{'initial':!initial}" :style="{'width': width}">
// 				<slot name="first"></slot>
//
// 			</div>
//
// 			<resize-observer @notify="handleResize" style="display: none"></resize-observer>
// 			<div class="handle-wrap" :style="{left:`calc(${compareWidth + '%'} - var(--handle-line-width) / 2`}">
// 				<div class="handle-icon">
// 					<svg class="handle__arrow handle__arrow--l" xmlns="http://www.w3.org/2000/svg" width="24"
// 					     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
// 					     stroke-linecap="round" stroke-linejoin="round">
// 						<polyline points="15 18 9 12 15 6"></polyline>
// 					</svg>
// 					<svg class="handle__arrow handle__arrow--r" xmlns="http://www.w3.org/2000/svg" width="24"
// 					     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
// 					     stroke-linecap="round" stroke-linejoin="round">
// 						<polyline points="9 18 15 12 9 6"></polyline>
// 					</svg>
//
// 				</div>
// 				<span class="handle-line"></span>
// 			</div>
//
// 			<div class="compare-overlay" :class="{'initial':!initial}" :style="{width:`calc(${compareWidth + '%'})`}">
// 				<div class="compare-overlay__content" :style="{ 'width': width}">
// 					<slot name="second"></slot>
// 				</div>
// 			</div>
//
// 		</div>
// 		<input type="range" min="0" max="100" :step="step" class="compare__range" :value="compareWidth"
// 		       @input="handleInput" tabindex="-1">
//
// 	</div>
// </template>
// <script>

exports.default = {
	name: 'image_diff',
	props: {
		value: { default: 50 },
		first_label: {
			type: String,
			default: ''
		},
		second_label: {
			type: String,
			default: ''
		},
		step: { default: '.1' }
	},
	data: function data() {
		return {
			width: null,
			initial: false,
			compareWidth: this.value
		};
	},

	watch: {
		value: function value() {
			this.compareWidth = this.value;
		}
	},
	mounted: function mounted() {
		this.width = this.getContainerWidth();
	},

	methods: {
		removeInitial: function removeInitial() {
			this.initial = true;
		},
		handleInput: function handleInput(e) {
			this.compareWidth = e.target.value;
			this.$emit('input', e.target.value);
		},
		handleResize: function handleResize() {
			var w = this.getContainerWidth();
			if (w === this.width) return;
			this.width = w;
		},
		getContainerWidth: function getContainerWidth() {
			return window.getComputedStyle(this.$el, null).getPropertyValue('width');
		}
	}
	// </script>
	// <style>
	// 	:root {
	// 		--handle-bg: #4a4a4a;
	// 		--handle-width: 30px;
	// 		--handle-height: 30px;
	// 		--handle-chevron-size: 20px;
	//
	// 		--handle-line-bg: #4a4a4a;
	// 		--handle-line-width: 2px;
	// 		--handle-line-height: 100%;
	//
	// 		--z-index-handle: 5;
	// 		--z-index-handle-line: 4;
	// 		--z-index-range-input: 6;
	// 	}
	//
	// 	.compare-wrapper {
	// 		position: relative;
	// 	}
	//
	// 	.compare,
	// 	.compare__content {
	// 		position: relative;
	// 		height: 100%;
	// 	}
	//
	// 	.compare-overlay.initial,
	// 	.compare__content.initial {
	// 		opacity: 0.2;
	// 	}
	//
	// 	.compare-overlay,
	// 	.compare__content {
	// 		-webkit-transition: opacity .3s ease-in-out;
	// 		-moz-transition: opacity .3s ease-in-out;
	// 		-ms-transition: opacity .3s ease-in-out;
	// 		-o-transition: opacity .3s ease-in-out;
	// 		transition: opacity .3s ease-in-out;
	// 	}
	//
	// 	.compare-overlay {
	// 		position: absolute;
	// 		overflow: hidden;
	// 		height: 100%;
	// 		top: 0;
	// 	}
	//
	// 	.compare-overlay__content {
	// 		position: relative;
	// 		height: 100%;
	// 		width: 100%;
	// 	}
	//
	// 	.handle__arrow {
	// 		position: absolute;
	// 		width: var(--handle-chevron-size);
	// 	}
	//
	// 	.handle__arrow--l {
	// 		left: 0;
	// 	}
	//
	// 	.handle__arrow--r {
	// 		right: 0;
	// 	}
	//
	// 	.handle-wrap {
	// 		display: flex;
	// 		align-items: center;
	// 		justify-content: center;
	// 		position: absolute;
	// 		top: 50%;
	// 		height: 100%;
	// 		transform: translate(-50%, -50%);
	// 		z-index: var(--z-index-handle);
	// 	}
	//
	// 	.handle-icon {
	// 		display: flex;
	// 		align-items: center;
	// 		justify-content: center;
	// 		color: white;
	// 		background: var(--handle-bg);
	// 		border-radius: 50%;
	// 		width: var(--handle-width);
	// 		height: var(--handle-height);
	// 	}
	//
	// 	.handle-line {
	// 		content: '';
	// 		position: absolute;
	// 		top: 0;
	// 		width: var(--handle-line-width);
	// 		height: 100%;
	// 		background: var(--handle-line-bg);
	// 		z-index: var(--z-index-handle-line);
	// 		pointer-events: none;
	// 		user-select: none;
	// 	}
	//
	// 	.compare__range {
	// 		position: absolute;
	// 		cursor: ew-resize;
	// 		left: calc(var(--handle-width) / -2);
	// 		width: calc(100% + var(--handle-width));
	// 		transform: translatey(-50%);
	// 		top: calc(50%);
	// 		z-index: var(--z-index-range-input);
	// 		-webkit-appearance: none;
	// 		height: var(--handle-height);
	// 		/* debugging purposes only */
	// 		background: rgba(0, 0, 0, .4);
	// 		opacity: .0;
	// 	}
	//
	// 	.object-fit-cover {
	// 		object-fit: cover;
	// 	}
	//
	// 	.compare_optimized {
	// 		right: 5px;
	// 	}
	//
	// 	.compare_original {
	// 		left: 5px;
	// 	}
	//
	// 	.compare_label {
	// 		position: absolute;
	// 		z-index: 999;
	// 		background: #444;
	// 		opacity: 0.6;
	// 		padding: 0.5rem;
	// 		border-radius: 0.2rem;
	// 		color: #fff;
	// 		top: 10px;
	//
	// 	}
	// </style>

};

/***/ }),
/* 44 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"compare-wrapper\" @mouseenter=\"removeInitial\">\n\t\t<div class=\"compare\">\n\t\t\t\n\t\t\t<span class=\"compare_label compare_original\">{{this.first_label}}</span>\n\t\t\t<span class=\"compare_label compare_optimized\">{{this.second_label}}</span>\n\t\t\t<div class=\"compare__content \" :class=\"{'initial':!initial}\" :style=\"{'width': width}\">\n\t\t\t\t<slot name=\"first\"></slot>\n\t\t\t\n\t\t\t</div>\n\t\t\t\n\t\t\t<resize-observer @notify=\"handleResize\" style=\"display: none\"></resize-observer>\n\t\t\t<div class=\"handle-wrap\" :style=\"{left:`calc(${compareWidth + '%'} - var(--handle-line-width) / 2`}\">\n\t\t\t\t<div class=\"handle-icon\">\n\t\t\t\t\t<svg class=\"handle__arrow handle__arrow--l\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\"\n\t\t\t\t\t     height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"\n\t\t\t\t\t     stroke-linecap=\"round\" stroke-linejoin=\"round\">\n\t\t\t\t\t\t<polyline points=\"15 18 9 12 15 6\"></polyline>\n\t\t\t\t\t</svg>\n\t\t\t\t\t<svg class=\"handle__arrow handle__arrow--r\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\"\n\t\t\t\t\t     height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"\n\t\t\t\t\t     stroke-linecap=\"round\" stroke-linejoin=\"round\">\n\t\t\t\t\t\t<polyline points=\"9 18 15 12 9 6\"></polyline>\n\t\t\t\t\t</svg>\n\t\t\t\t\n\t\t\t\t</div>\n\t\t\t\t<span class=\"handle-line\"></span>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"compare-overlay\" :class=\"{'initial':!initial}\" :style=\"{width:`calc(${compareWidth + '%'})`}\">\n\t\t\t\t<div class=\"compare-overlay__content\" :style=\"{ 'width': width}\">\n\t\t\t\t\t<slot name=\"second\"></slot>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\n\t\t</div>\n\t\t<input type=\"range\" min=\"0\" max=\"100\" :step=\"step\" class=\"compare__range\" :value=\"compareWidth\"\n\t\t       @input=\"handleInput\" tabindex=\"-1\">\n\t\n\t</div>\n";

/***/ }),
/* 45 */
/***/ (function(module, exports) {

module.exports = "\n\t<div :class=\"{ 'saving--option' : this.$store.state.loading }\" _v-546bc561=\"\">\n\t\t\n\t\t<div class=\"field  columns\" _v-546bc561=\"\">\n\t\t\t<label class=\"label column has-text-grey-dark\" _v-546bc561=\"\">\n\t\t\t\t{{strings.enable_image_replace}}\n\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-546bc561=\"\">\n\t\t\t\t\t{{strings.replacer_desc}}\n\t\t\t\t</p>\n\t\t\t</label>\n\t\t\t<div class=\"column \" _v-546bc561=\"\">\n\t\t\t\t<toggle-button :class=\"'has-text-dark'\" v-model=\"getReplacerStatus\" :disabled=\"this.$store.state.loading\" :labels=\"{checked: strings.enabled, unchecked: strings.disabled}\" :width=\"80\" :height=\"25\" color=\"#008ec2\" _v-546bc561=\"\"></toggle-button>\n\t\t\t</div>\n\t\t\n\t\t</div>\n\t\t<div class=\"field  is-fullwidth columns\" _v-546bc561=\"\">\n\t\t\t<label class=\"label column has-text-grey-dark\" _v-546bc561=\"\">\n\t\t\t\t{{strings.toggle_ab_item}}\n\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-546bc561=\"\">\n\t\t\t\t\t{{strings.admin_bar_desc}}\n\t\t\t\t</p>\n\t\t\t</label>\n\t\t\t\n\t\t\t<div class=\"column \" _v-546bc561=\"\">\n\t\t\t\t<toggle-button :class=\"'has-text-dark'\" v-model=\"adminBarItemStatus\" :disabled=\"this.$store.state.loading\" :labels=\"{checked: strings.show, unchecked: strings.hide}\" :width=\"80\" :height=\"25\" color=\"#008ec2\" _v-546bc561=\"\"></toggle-button>\n\t\t\t</div>\n\t\t</div>\n\n\t\t<div class=\"field  is-fullwidth columns\" _v-546bc561=\"\">\n\t\t\t<label class=\"label column has-text-grey-dark\" _v-546bc561=\"\">\n\t\t\t\t{{strings.toggle_lazyload}}\n\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-546bc561=\"\">\n\t\t\t\t\t{{strings.lazyload_desc}}\n\t\t\t\t</p>\n\t\t\t</label>\n\n\t\t\t<div class=\"column \" _v-546bc561=\"\">\n\t\t\t\t<toggle-button :class=\"'has-text-dark'\" v-model=\"lazyLoadStatus\" :disabled=\"this.$store.state.loading\" :labels=\"{checked: strings.enabled, unchecked: strings.disabled}\" :width=\"80\" :height=\"25\" color=\"#008ec2\" _v-546bc561=\"\"></toggle-button>\n\t\t\t</div>\n\t\t</div>\n\t\t\n\t\t<div class=\"field  is-fullwidth columns n\" _v-546bc561=\"\">\n\t\t\t<label class=\"label is-half column has-text-grey-dark no-padding-right \" _v-546bc561=\"\">\n\t\t\t\t{{strings.size_title}}\n\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-546bc561=\"\">\n\t\t\t\t\t{{strings.size_desc}}\n\t\t\t\t</p>\n\t\t\t</label>\n\t\t\t\n\t\t\t<div class=\"column is-paddingless\" _v-546bc561=\"\">\n\t\t\t\t<div class=\"columns\" _v-546bc561=\"\">\n\t\t\t\t\t<div class=\"field column is-narrow has-addons\" _v-546bc561=\"\">\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t{{strings.width_field}}\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t<p class=\"control \" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<input v-model=\"widthStatus\" class=\"input is-small\" type=\"number\" min=\"100\" max=\"10000\" _v-546bc561=\"\">\n\t\t\t\t\t\t</p>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"field column is-small has-addons\" _v-546bc561=\"\">\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t{{strings.height_field}}\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t<p class=\"control  \" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<input v-model=\"heightStatus\" class=\"input is-small\" type=\"number\" min=\"100\" max=\"10000\" _v-546bc561=\"\">\n\t\t\t\t\t\t</p>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t\n\t\t<div class=\"field  columns\" _v-546bc561=\"\">\n\t\t\t<label class=\"label column has-text-grey-dark\" _v-546bc561=\"\">\n\t\t\t\t{{strings.quality_title}}\n\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-546bc561=\"\">\n\t\t\t\t\t{{strings.quality_desc}}\n\t\t\t\t</p>\n\t\t\t</label>\n\t\t\t<div class=\"column  buttons \" _v-546bc561=\"\">\n\t\t\t\t<div class=\"field columns  \" _v-546bc561=\"\">\n\t\t\t\t\t<div class=\"column  field has-addons\" _v-546bc561=\"\">\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a @click=\"changeQuality('auto')\" :class=\"{ 'is-info':isActiveQuality ( 'auto'), '  is-selected':site_settings.quality === 'auto'  }\" class=\"button   is-small is-rounded\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-marker\" _v-546bc561=\"\"></span>\n\t\t\t\t\t\t\t\t<span _v-546bc561=\"\">{{strings.auto_q_title}}</span>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a @click=\"changeQuality('high_c')\" :class=\"{  'is-info': isActiveQuality ('high_c'), 'is-selected':site_settings.quality === 'high_c'   }\" class=\"button    is-rounded is-small\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-menu\" _v-546bc561=\"\"></span>\n\t\t\t\t\t\t\t\t<span _v-546bc561=\"\">{{strings.high_q_title}}</span>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a @click=\"changeQuality('medium_c')\" :class=\"{  'is-info': isActiveQuality( 'medium_c' ), '  is-selected':site_settings.quality === 'medium_c'  }\" class=\"button   is-small\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-controls-pause\" _v-546bc561=\"\"></span>\n\t\t\t\t\t\t\t\t<span class=\" \" _v-546bc561=\"\">{{strings.medium_q_title}}</span>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\n\t\t\t\t\t\t<p class=\"control\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<a @click=\"changeQuality('low_c')\" :class=\"{  'is-info':isActiveQuality( 'low_c' ), ' is-selected':site_settings.quality === 'low_c'  }\" class=\"button   is-small\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t\t<span class=\"icon dashicons dashicons-minus  \" _v-546bc561=\"\"></span>\n\t\t\t\t\t\t\t\t<span _v-546bc561=\"\">{{strings.low_q_title}}</span>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</p>\n\t\t\t\t\t</div>\n\t\t\t\t\t<p class=\"control column has-text-centered-desktop has-text-left-touch  \" _v-546bc561=\"\">\n\t\t\t\t\t\t<a @click=\"saveChanges()\" class=\"button is-small is-success \" :class=\"{'is-loading':loading}\" _v-546bc561=\"\">\n\t\t\t\t\t\t\t<span class=\"dashicons dashicons-yes icon\" _v-546bc561=\"\"></span>\n\t\t\t\t\t\t\t<span _v-546bc561=\"\">\t{{strings.save_changes}}</span>\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n        <div v-if=\"showComparison\" _v-546bc561=\"\">\n            <div v-if=\"loading_images\" class=\"has-text-centered subtitle \" _v-546bc561=\"\">{{strings.sample_image_loading}}<span class=\"loader has-text-black-bis icon is-small\" _v-546bc561=\"\"></span>\n            </div>\n            <div v-else-if=\"sample_images.id &amp;&amp; sample_images.original_size > 0\" _v-546bc561=\"\">\n                <p class=\"title has-text-centered is-5 is-size-6-mobile\" _v-546bc561=\"\">{{strings.quality_slider_desc}}</p>\n                <div class=\"columns is-centered is-vcentered is-multiline is-mobile\" _v-546bc561=\"\">\n                    <a @click=\"newSample()\" class=\"button is-small is-pulled-right\" _v-546bc561=\"\">\n                        <span class=\"icon dashicons dashicons-image-rotate\" _v-546bc561=\"\"></span>\n                    </a>\n                    <div class=\"column visual-compare  is-half-fullhd is-half-desktop is-three-quarters-touch is-12-mobile  \" _v-546bc561=\"\">\n                        <div class=\"is-full progress-wrapper\" _v-546bc561=\"\">\n                            <p class=\"subtitle is-size-6 compress-optimization-ratio-done has-text-centered\" v-if=\"compressionRatio > 0\" _v-546bc561=\"\">\n                                <strong _v-546bc561=\"\">{{( 100 - compressionRatio )}}%</strong> smaller </p>\n                            <p class=\"subtitle  compress-optimization-ratio-nothing is-size-6 has-text-centered\" v-else=\"\" _v-546bc561=\"\">\n                                {{all_strings.latest_images.same_size}}\n                            </p>\n                            <progress class=\"  progress is-large is-success \" :value=\"compressionRatio\" :max=\"100\" _v-546bc561=\"\">\n                            </progress>\n                            <hr _v-546bc561=\"\">\n                        </div>\n                        <image_diff class=\"is-fullwidth\" value=\"50\" :first_label=\"strings.image_1_label\" :second_label=\"strings.image_2_label\" _v-546bc561=\"\">\n                            <img slot=\"first\" :src=\"sample_images.optimized\" _v-546bc561=\"\">\n                            <img slot=\"second\" :src=\"sample_images.original\" _v-546bc561=\"\">\n                        </image_diff>\n                    </div>\n                </div>\n            </div>\n            <div v-else-if=\" sample_images.id < 0\" _v-546bc561=\"\">\n                <p class=\"title has-text-centered is-5 is-size-6-mobile\" _v-546bc561=\"\">{{strings.no_images_found}}</p>\n            </div>\n        </div>\n\t\n\t</div>\n\n";

/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(47)
__vue_script__ = __webpack_require__(49)
__vue_template__ = __webpack_require__(51)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "D:\\dev\\optimole-wp\\assets\\vue\\components\\watermarks.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 47 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(48);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-3fe3cec8&file=watermarks.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./watermarks.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-3fe3cec8&file=watermarks.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./watermarks.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\r\n\t.optml-layout-grid .grid-button.is-selected[_v-3fe3cec8] {\r\n\t\tbackground: #4a4a4a;\r\n\t}\r\n\t\r\n\t.optml-layout-grid .grid-button[_v-3fe3cec8] {\r\n\t\twidth: 50px;\r\n\t\theight: 50px;\r\n\t\tdisplay: inline-block;\r\n\t\tmargin: 0;\r\n\t\tpadding: 0;\r\n\t\tborder-radius: 9px;\r\n\t\tborder: 5px solid #4a4a4a;\r\n\t}\r\n\t\r\n\t.optml-layout-grid[_v-3fe3cec8] {\r\n\t\twidth: 200px;\r\n\t\t\r\n\t}\r\n\t#optimole-app .input.is-small.is-tiny[_v-3fe3cec8]{\r\n\t\twidth:60px;\r\n\t}\r\n", ""]);

// exports


/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _vueUploadComponent = __webpack_require__(50);

var _vueUploadComponent2 = _interopRequireDefault(_vueUploadComponent);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
	name: "watermarks",
	components: { FileUpload: _vueUploadComponent2.default },
	data: function data() {
		return {
			global: optimoleDashboardApp,
			startTime: 0,
			maxTime: 20,
			noImages: true,
			files: [],
			is_error: false,
			error_message: '',
			home_url: optimoleDashboardApp.home_url,
			strings: optimoleDashboardApp.strings.watermarks,
			watermarkData: [{
				ID: 1,
				guid: ''
			}],
			watermarkSettings: this.$store.state.site_settings.watermark,
			newData: {}
		};
	},
	mounted: function mounted() {
		if (this.$store.state.optimizedImages.length > 0) {
			return;
		}
		this.selectedWatermark = this.watermarkSettings.id;
		this.$store.dispatch('retrieveWatermarks', { component: this });
	},

	computed: {
		watermarkOpacity: {
			set: function set(value) {
				if (parseInt(value) < 0) {
					this.$store.commit('updateWatermark', { opacity: 0 });
					this.newData.wm_opacity = this.watermarkSettings.opacity;
					return;
				}
				if (parseInt(value) > 100) {
					opacity = 1;
					this.$store.commit('updateWatermark', { opacity: 1 });
					this.newData.wm_opacity = this.watermarkSettings.opacity;
					return;
				}

				this.$store.commit('updateWatermark', { opacity: parseFloat(parseInt(value) / 100) });
				this.newData.wm_opacity = this.watermarkSettings.opacity;
			},
			get: function get() {
				return Math.round(this.watermarkSettings.opacity * 100);
			}
		},
		watermarkScale: {
			set: function set(value) {
				if (parseInt(value) < 0) {
					this.$store.commit('updateWatermark', { scale: 0 });
					this.newData.wm_scale = this.watermarkSettings.scale;
					return;
				}
				if (parseInt(value) > 300) {
					this.$store.commit('updateWatermark', { scale: 3 });
					this.newData.wm_scale = this.watermarkSettings.scale;
					return;
				}

				this.$store.commit('updateWatermark', { scale: parseFloat(parseInt(value) / 100) });
				this.newData.wm_scale = this.watermarkSettings.scale;
			},
			get: function get() {
				return Math.round(this.watermarkSettings.scale * 100);
			}
		},
		watermarkX: {
			set: function set(value) {
				this.$store.commit('updateWatermark', { x_offset: parseInt(value) });
				this.newData.wm_x = this.watermarkSettings.x_offset;
			},
			get: function get() {
				return this.watermarkSettings.x_offset;
			}
		},
		watermarkY: {
			set: function set(value) {
				this.$store.commit('updateWatermark', { y_offset: parseInt(value) });
				this.newData.wm_y = this.watermarkSettings.y_offset;
			},
			get: function get() {
				return this.watermarkSettings.y_offset;
			}
		},
		selectedWatermark: {
			set: function set(value) {
				this.$store.commit('updateWatermark', { id: parseInt(value) });
				this.newData.wm_id = this.watermarkSettings.id;
			},
			get: function get() {
				return this.watermarkSettings.id;
			}
		},
		loading: function loading() {
			return this.$store.state.loading;
		}
	},
	methods: {
		saveChanges: function saveChanges() {
			this.$store.dispatch('saveSettings', {
				settings: this.newData
			});
		},
		changePosition: function changePosition(value) {
			this.$store.commit('updateWatermark', { position: value });
			this.newData.wm_position = this.watermarkSettings.position;
		},
		isSelectedWatermark: function isSelectedWatermark(id) {
			return this.$store.state.site_settings.watermark.id === id;
		},
		isActivePosition: function isActivePosition(pos) {
			return this.watermarkSettings.position === pos;
		},
		inputFilter: function inputFilter(newFile, oldFile, prevent) {
			if (newFile && !oldFile) {
				// Before adding a file
				// Filter system files or hide files
				if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
					return prevent();
				}
				// Filter php html js file
				if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
					return prevent();
				}
			}
		},
		inputFile: function inputFile(newFile, oldFile) {
			if (newFile && !oldFile) {
				// add
				this.is_error = false;
				this.error_message = '';
				this.$refs.upload.active = true;
			}
			if (newFile && oldFile) {
				if (newFile.response.code && newFile.response.code === 'error') {
					this.is_error = true;
					this.error_message = newFile.response.data;
					return;
				}
				this.$store.dispatch('retrieveWatermarks', { component: this });
			}
			if (newFile && oldFile && newFile.success !== oldFile.success) {
				this.$refs.upload.clear();
			}
			if (!newFile && oldFile) {
				// remove
			}
		},
		removeWatermark: function removeWatermark(postID) {
			this.$store.dispatch('removeWatermark', { postID: postID, component: this });
		}
	}
	// </script>
	//
	// <style scoped>
	// 	.optml-layout-grid .grid-button.is-selected {
	// 		background: #4a4a4a;
	// 	}
	//
	// 	.optml-layout-grid .grid-button {
	// 		width: 50px;
	// 		height: 50px;
	// 		display: inline-block;
	// 		margin: 0;
	// 		padding: 0;
	// 		border-radius: 9px;
	// 		border: 5px solid #4a4a4a;
	// 	}
	//
	// 	.optml-layout-grid {
	// 		width: 200px;
	//
	// 	}
	// 	#optimole-app .input.is-small.is-tiny{
	// 		width:60px;
	// 	}
	// </style>

}; // <template>
// 	<div>
// 		<h4>{{strings.add_desc}}</h4>
// 		<div class="field  columns">
// 			<div class="column" v-for="file in files">
//                 <span class="tag">
//                     <i>{{file.name}}</i>
//                     <i v-if="!file.active && !file.success && file.error === ''"
//                        class="dashicons dashicons-yes icon has-text-grey-light"></i>
//                     <i v-else-if="file.active" class="dashicons dashicons-marker icon spin has-text-warning"></i>
//                     <i v-else-if="!file.active && file.success"
//                        class="dashicons dashicons-yes icon has-text-success"></i>
//                     <i v-else class="dashicons dashicons-no-alt icon has-text-danger"></i>
//                 </span>
// 			</div>
// 		</div>
// 		<div class="column ">
// 			<file-upload
// 					class="button is-secondary is-rounded"
// 					:post-action=" global.root + '/add_watermark'"
// 					:headers="{'X-WP-Nonce': global.nonce}"
// 					extensions="gif,jpg,jpeg,png,webp"
// 					accept="image/png,image/gif,image/jpeg,image/webp"
// 					:size="1024 * 1024 * 10"
// 					v-model="files"
// 					@input-filter="inputFilter"
// 					@input-file="inputFile"
// 					:disabled="loading"
// 					ref="upload">
// 				<i class="dashicons dashicons-plus icon"></i>
// 				{{strings.upload}}
// 			</file-upload>
// 			<br/><br/><span class="tag is-danger" v-if="is_error">{{error_message}}</span>
// 		</div>
// 		<hr/>
// 		<div class="box">
// 			<h3><span class="dashicons dashicons-menu"></span> {{strings.list_header}} </h3>
// 			<small><i>{{strings.max_allowed}}</i></small>
//
// 			<div class="optimized-images">
// 				<div v-if="!noImages">
// 					<h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
// 					<table class="table is-striped is-hoverable is-fullwidth">
// 						<thead>
// 						<tr>
// 							<th class="optml-image-heading">{{strings.id}}</th>
// 							<th class="optml-image-heading">{{strings.image}}</th>
// 							<th class="optml-image-heading">{{strings.action}}</th>
// 						</tr>
// 						</thead>
// 						<tbody>
// 						<tr v-for="(item, index) in watermarkData">
// 							<td><code>#{{item.ID}}</code></td>
// 							<td><img :src="item.guid" class="optml-image-watermark" width="50"/></td>
// 							<td width="50">
// 								<a @click="removeWatermark(item.ID)" class="button is-small is-danger is-rounded"
// 								   :class="{'is-loading':loading}">
// 									<span class="dashicons dashicons-no-alt icon"></span>
// 								</a>
// 							</td>
// 						</tr>
// 						</tbody>
// 					</table>
//
// 					<span class="tag is-success" v-if="loading">
// 					{{strings.loading_remove_watermark}}
// 					</span>
// 				</div>
// 			</div>
// 			<table class="table is-striped is-hoverable is-fullwidth" v-if="noImages">
// 				<thead>
// 				<tr>
// 					<th class="optml-image-heading has-text-centered" v-html="strings.no_images_found"></th>
// 				</tr>
// 				</thead>
// 			</table>
// 			<hr/>
// 			<h3><span class="dashicons dashicons-grid-view"></span> {{strings.settings_header}} </h3>
// 			<br/>
// 			<div class="field  is-fullwidth columns">
// 				<label class="label is-half column has-text-grey-dark no-padding-right ">
// 					{{strings.wm_title}}
// 					<p class="is-italic has-text-weight-normal">
// 						{{strings.wm_desc}}
// 					</p>
// 				</label>
//
// 				<div class="column is-paddingless">
// 					<div class="columns">
// 						<div class="field column is-narrow">
// 							<div class="select">
// 								<select title="Watermark Selection" v-model="selectedWatermark">
// 									<option value="-1">No watermark</option>
// 									<option v-for="(item, index) in watermarkData" :value="item.ID">#({{item.ID}})
// 									</option>
// 								</select>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
//
// 			<div class="field  is-fullwidth columns">
// 				<label class="label is-half column has-text-grey-dark no-padding-right ">
// 					{{strings.opacity_title}}
// 					<p class="is-italic has-text-weight-normal">
// 						{{strings.opacity_desc}}
// 					</p>
// 				</label>
//
// 				<div class="column is-paddingless">
// 					<div class="columns">
// 						<div class="field column is-narrow has-addons">
// 							<p class="control">
// 								<a class="button is-small is-static">
// 									{{strings.opacity_field}}
// 								</a>
// 							</p>
// 							<p class="control ">
// 								<input v-model="watermarkOpacity" class="input is-small" type="number" min="0"
// 								       max="100">
// 							</p>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
//
// 			<div class="field  columns">
// 				<label class="label column has-text-grey-dark">
// 					{{strings.position_title}}
// 					<p class="is-italic has-text-weight-normal">
// 						{{strings.position_desc}}
// 					</p>
// 				</label>
// 				<div class="column  buttons ">
// 					<div class="field columns is-gapless is-marginless ">
// 						<div class="is-fullwidth  optml-layout-grid">
// 							<a @click="changePosition('nowe')"
// 							   :class="{ 'is-info':isActivePosition ('nowe'), '  is-selected':watermarkSettings.position === 'nowe'  }"
// 							   class="grid-button  " :title="strings.pos_nowe_title">
// 							</a>
// 							<a @click="changePosition('no')"
// 							   :class="{ 'is-info':isActivePosition ('no'), '  is-selected':watermarkSettings.position === 'no'  }"
// 							   class="grid-button  " :title="strings.pos_no_title">
// 							</a>
// 							<a @click="changePosition('noea')"
// 							   :class="{ 'is-info':isActivePosition ('noea'), '  is-selected':watermarkSettings.position === 'noea'  }"
// 							   class="grid-button" :title="strings.pos_noea_title">
// 							</a>
// 							<a @click="changePosition('we')"
// 							   :class="{ 'is-info':isActivePosition ('we'), '  is-selected':watermarkSettings.position === 'we'  }"
// 							   class="grid-button" :title="strings.pos_we_title">
// 							</a>
// 							<a @click="changePosition('ce')"
// 							   :class="{ 'is-info':isActivePosition ('ce'), '  is-selected':watermarkSettings.position === 'ce'  }"
// 							   class="grid-button" :title="strings.pos_ce_title">
// 							</a>
// 							<a @click="changePosition('ea')"
// 							   :class="{ 'is-info':isActivePosition ('ea'), '  is-selected':watermarkSettings.position === 'ea'  }"
// 							   class="grid-button" :title="strings.pos_ea_title">
// 							</a>
// 							<a @click="changePosition('sowe')"
// 							   :class="{ 'is-info':isActivePosition ('sowe'), '  is-selected':watermarkSettings.position === 'sowe'  }"
// 							   class="grid-button" :title="strings.pos_sowe_title">
// 							</a>
// 							<a @click="changePosition('so')"
// 							   :class="{ 'is-info':isActivePosition ('so'), '  is-selected':watermarkSettings.position === 'so'  }"
// 							   class="grid-button" :title="strings.pos_so_title">
// 							</a>
// 							<a @click="changePosition('soea')"
// 							   :class="{ 'is-info':isActivePosition ('soea'), '  is-selected':watermarkSettings.position === 'soea'  }"
// 							   class="grid-button" :title="strings.pos_soea_title">
// 							</a>
// 						</div>
// 					</div>
// 					<br/>
// 				</div>
// 			</div>
//
// 			<div class="field  is-fullwidth columns ">
// 				<label class="label is-half column has-text-grey-dark no-padding-right ">
// 					{{strings.offset_title}}
// 					<p class="is-italic has-text-weight-normal">
// 						{{strings.offset_desc}}
// 					</p>
// 				</label>
//
// 				<div class="column is-paddingless">
// 					<div class="columns">
// 						<div class="field column is-narrow   has-addons">
// 							<p class="control">
// 								<a class="button is-small is-static">
// 									{{strings.offset_x_field}}
// 								</a>
// 							</p>
// 							<p class="control ">
// 								<input v-model="watermarkX" class="input  is-tiny is-small" type="number">
// 							</p>
// 						</div>
// 						<div class="field column is-narrow  has-addons">
// 							<p class="control">
// 								<a class="button is-small is-static">
// 									{{strings.offset_y_field}}
// 								</a>
// 							</p>
// 							<p class="control ">
// 								<input v-model="watermarkY" class="input is-small is-tiny" type="number">
// 							</p>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
//
// 			<div class="field  is-fullwidth columns">
// 				<label class="label is-half column has-text-grey-dark no-padding-right ">
// 					{{strings.scale_title}}
// 					<p class="is-italic has-text-weight-normal">
// 						{{strings.scale_desc}}
// 					</p>
// 				</label>
//
// 				<div class="column is-paddingless">
// 					<div class="columns">
// 						<div class="field column is-narrow has-addons">
// 							<p class="control">
// 								<a class="button is-small is-static">
// 									{{strings.scale_field}}
// 								</a>
// 							</p>
// 							<p class="control ">
// 								<input v-model="watermarkScale" class="input is-small" type="number" min="0"
// 								       max="100">
// 							</p>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
//
// 			<p class="control column has-text-centered-desktop has-text-left-touch  ">
// 				<a @click="saveChanges()" class="button is-small is-success "
// 				   :class="{'is-loading':loading}">
// 					<span class="dashicons dashicons-yes icon"></span>
// 					<span>	{{strings.save_changes}}</span>
// 				</a>
// 			</p>
// 		</div>
// 	</div>
// </template>
//
// <script>

/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

/*!
 * Name: vue-upload-component
 * Version: 2.8.19
 * Author: LianYue
 */
(function (global, factory) {
   true ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global.VueUploadComponent = factory());
}(this, (function () { 'use strict';

  /**
   * Creates a XHR request
   *
   * @param {Object} options
   */
  var createRequest = function createRequest(options) {
    var xhr = new XMLHttpRequest();
    xhr.open(options.method || 'GET', options.url);
    xhr.responseType = 'json';
    if (options.headers) {
      Object.keys(options.headers).forEach(function (key) {
        xhr.setRequestHeader(key, options.headers[key]);
      });
    }

    return xhr;
  };

  /**
   * Sends a XHR request with certain body
   *
   * @param {XMLHttpRequest} xhr
   * @param {Object} body
   */
  var sendRequest = function sendRequest(xhr, body) {
    return new Promise(function (resolve, reject) {
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          var response;
          try {
            response = JSON.parse(xhr.response);
          } catch (err) {
            response = xhr.response;
          }
          resolve(response);
        } else {
          reject(xhr.response);
        }
      };
      xhr.onerror = function () {
        return reject(xhr.response);
      };
      xhr.send(JSON.stringify(body));
    });
  };

  /**
   * Sends a XHR request with certain form data
   *
   * @param {XMLHttpRequest} xhr
   * @param {Object} data
   */
  var sendFormRequest = function sendFormRequest(xhr, data) {
    var body = new FormData();
    for (var name in data) {
      body.append(name, data[name]);
    }

    return new Promise(function (resolve, reject) {
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          var response;
          try {
            response = JSON.parse(xhr.response);
          } catch (err) {
            response = xhr.response;
          }
          resolve(response);
        } else {
          reject(xhr.response);
        }
      };
      xhr.onerror = function () {
        return reject(xhr.response);
      };
      xhr.send(body);
    });
  };

  /**
   * Creates and sends XHR request
   *
   * @param {Object} options
   *
   * @returns Promise
   */
  function request (options) {
    var xhr = createRequest(options);

    return sendRequest(xhr, options.body);
  }

  var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

  function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

  var ChunkUploadHandler = function () {
    /**
     * Constructor
     *
     * @param {File} file
     * @param {Object} options
     */
    function ChunkUploadHandler(file, options) {
      _classCallCheck(this, ChunkUploadHandler);

      this.file = file;
      this.options = options;
    }

    /**
     * Gets the max retries from options
     */


    _createClass(ChunkUploadHandler, [{
      key: 'createChunks',


      /**
       * Creates all the chunks in the initial state
       */
      value: function createChunks() {
        this.chunks = [];

        var start = 0;
        var end = this.chunkSize;
        while (start < this.fileSize) {
          this.chunks.push({
            blob: this.file.file.slice(start, end),
            startOffset: start,
            active: false,
            retries: this.maxRetries
          });
          start = end;
          end = start + this.chunkSize;
        }
      }

      /**
       * Updates the progress of the file with the handler's progress
       */

    }, {
      key: 'updateFileProgress',
      value: function updateFileProgress() {
        this.file.progress = this.progress;
      }

      /**
       * Paues the upload process
       * - Stops all active requests
       * - Sets the file not active
       */

    }, {
      key: 'pause',
      value: function pause() {
        this.file.active = false;
        this.stopChunks();
      }

      /**
       * Stops all the current chunks
       */

    }, {
      key: 'stopChunks',
      value: function stopChunks() {
        this.chunksUploading.forEach(function (chunk) {
          chunk.xhr.abort();
          chunk.active = false;
        });
      }

      /**
       * Resumes the file upload
       * - Sets the file active
       * - Starts the following chunks
       */

    }, {
      key: 'resume',
      value: function resume() {
        this.file.active = true;
        this.startChunking();
      }

      /**
       * Starts the file upload
       *
       * @returns Promise
       * - resolve  The file was uploaded
       * - reject   The file upload failed
       */

    }, {
      key: 'upload',
      value: function upload() {
        var _this = this;

        this.promise = new Promise(function (resolve, reject) {
          _this.resolve = resolve;
          _this.reject = reject;
        });
        this.start();

        return this.promise;
      }

      /**
       * Start phase
       * Sends a request to the backend to initialise the chunks
       */

    }, {
      key: 'start',
      value: function start() {
        var _this2 = this;

        request({
          method: 'POST',
          headers: Object.assign({}, this.headers, {
            'Content-Type': 'application/json'
          }),
          url: this.action,
          body: Object.assign(this.startBody, {
            phase: 'start',
            mime_type: this.fileType,
            size: this.fileSize,
            name: this.fileName
          })
        }).then(function (res) {
          if (res.status !== 'success') {
            _this2.file.response = res;
            return _this2.reject('server');
          }

          _this2.sessionId = res.data.session_id;
          _this2.chunkSize = res.data.end_offset;

          _this2.createChunks();
          _this2.startChunking();
        }).catch(function (res) {
          _this2.file.response = res;
          _this2.reject('server');
        });
      }

      /**
       * Starts to upload chunks
       */

    }, {
      key: 'startChunking',
      value: function startChunking() {
        for (var i = 0; i < this.maxActiveChunks; i++) {
          this.uploadNextChunk();
        }
      }

      /**
       * Uploads the next chunk
       * - Won't do anything if the process is paused
       * - Will start finish phase if there are no more chunks to upload
       */

    }, {
      key: 'uploadNextChunk',
      value: function uploadNextChunk() {
        if (this.file.active) {
          if (this.hasChunksToUpload) {
            return this.uploadChunk(this.chunksToUpload[0]);
          }

          if (this.chunksUploading.length === 0) {
            return this.finish();
          }
        }
      }

      /**
       * Uploads a chunk
       * - Sends the chunk to the backend
       * - Sets the chunk as uploaded if everything went well
       * - Decreases the number of retries if anything went wrong
       * - Fails if there are no more retries
       *
       * @param {Object} chunk
       */

    }, {
      key: 'uploadChunk',
      value: function uploadChunk(chunk) {
        var _this3 = this;

        chunk.progress = 0;
        chunk.active = true;
        this.updateFileProgress();
        chunk.xhr = createRequest({
          method: 'POST',
          headers: this.headers,
          url: this.action
        });

        chunk.xhr.upload.addEventListener('progress', function (evt) {
          if (evt.lengthComputable) {
            chunk.progress = Math.round(evt.loaded / evt.total * 100);
          }
        }, false);

        sendFormRequest(chunk.xhr, Object.assign(this.uploadBody, {
          phase: 'upload',
          session_id: this.sessionId,
          start_offset: chunk.startOffset,
          chunk: chunk.blob
        })).then(function (res) {
          chunk.active = false;
          if (res.status === 'success') {
            chunk.uploaded = true;
          } else {
            if (chunk.retries-- <= 0) {
              _this3.stopChunks();
              return _this3.reject('upload');
            }
          }

          _this3.uploadNextChunk();
        }).catch(function () {
          chunk.active = false;
          if (chunk.retries-- <= 0) {
            _this3.stopChunks();
            return _this3.reject('upload');
          }

          _this3.uploadNextChunk();
        });
      }

      /**
       * Finish phase
       * Sends a request to the backend to finish the process
       */

    }, {
      key: 'finish',
      value: function finish() {
        var _this4 = this;

        this.updateFileProgress();

        request({
          method: 'POST',
          headers: Object.assign({}, this.headers, {
            'Content-Type': 'application/json'
          }),
          url: this.action,
          body: Object.assign(this.finishBody, {
            phase: 'finish',
            session_id: this.sessionId
          })
        }).then(function (res) {
          _this4.file.response = res;
          if (res.status !== 'success') {
            return _this4.reject('server');
          }

          _this4.resolve(res);
        }).catch(function (res) {
          _this4.file.response = res;
          _this4.reject('server');
        });
      }
    }, {
      key: 'maxRetries',
      get: function get() {
        return parseInt(this.options.maxRetries);
      }

      /**
       * Gets the max number of active chunks being uploaded at once from options
       */

    }, {
      key: 'maxActiveChunks',
      get: function get() {
        return parseInt(this.options.maxActive);
      }

      /**
       * Gets the file type
       */

    }, {
      key: 'fileType',
      get: function get() {
        return this.file.type;
      }

      /**
       * Gets the file size
       */

    }, {
      key: 'fileSize',
      get: function get() {
        return this.file.size;
      }

      /**
       * Gets the file name
       */

    }, {
      key: 'fileName',
      get: function get() {
        return this.file.name;
      }

      /**
       * Gets action (url) to upload the file
       */

    }, {
      key: 'action',
      get: function get() {
        return this.options.action || null;
      }

      /**
       * Gets the body to be merged when sending the request in start phase
       */

    }, {
      key: 'startBody',
      get: function get() {
        return this.options.startBody || {};
      }

      /**
       * Gets the body to be merged when sending the request in upload phase
       */

    }, {
      key: 'uploadBody',
      get: function get() {
        return this.options.uploadBody || {};
      }

      /**
       * Gets the body to be merged when sending the request in finish phase
       */

    }, {
      key: 'finishBody',
      get: function get() {
        return this.options.finishBody || {};
      }

      /**
       * Gets the headers of the requests from options
       */

    }, {
      key: 'headers',
      get: function get() {
        return this.options.headers || {};
      }

      /**
       * Whether it's ready to upload files or not
       */

    }, {
      key: 'readyToUpload',
      get: function get() {
        return !!this.chunks;
      }

      /**
       * Gets the progress of the chunk upload
       * - Gets all the completed chunks
       * - Gets the progress of all the chunks that are being uploaded
       */

    }, {
      key: 'progress',
      get: function get() {
        var _this5 = this;

        var completedProgress = this.chunksUploaded.length / this.chunks.length * 100;
        var uploadingProgress = this.chunksUploading.reduce(function (progress, chunk) {
          return progress + (chunk.progress | 0) / _this5.chunks.length;
        }, 0);

        return Math.min(completedProgress + uploadingProgress, 100);
      }

      /**
       * Gets all the chunks that are pending to be uploaded
       */

    }, {
      key: 'chunksToUpload',
      get: function get() {
        return this.chunks.filter(function (chunk) {
          return !chunk.active && !chunk.uploaded;
        });
      }

      /**
       * Whether there are chunks to upload or not
       */

    }, {
      key: 'hasChunksToUpload',
      get: function get() {
        return this.chunksToUpload.length > 0;
      }

      /**
       * Gets all the chunks that are uploading
       */

    }, {
      key: 'chunksUploading',
      get: function get() {
        return this.chunks.filter(function (chunk) {
          return !!chunk.xhr && !!chunk.active;
        });
      }

      /**
       * Gets all the chunks that have finished uploading
       */

    }, {
      key: 'chunksUploaded',
      get: function get() {
        return this.chunks.filter(function (chunk) {
          return !!chunk.uploaded;
        });
      }
    }]);

    return ChunkUploadHandler;
  }();

  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //

  var script = {
    methods: {
      change: function change(e) {
        this.$parent.addInputFile(e.target);
        if (e.target.files) {
          e.target.value = '';
          if (!/safari/i.test(navigator.userAgent)) {
            e.target.type = '';
            e.target.type = 'file';
          }
        } else {
          // ie9 fix #219
          this.$destroy();
          // eslint-disable-next-line
          new this.constructor({
            parent: this.$parent,
            el: this.$el
          });
        }
      }
    }
  };

  /* script */
  var __vue_script__ = script;

  /* template */
  var __vue_render__ = function __vue_render__() {
    var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;return _c('input', { attrs: { "type": "file", "name": _vm.$parent.name, "id": _vm.$parent.inputId || _vm.$parent.name, "accept": _vm.$parent.accept, "capture": _vm.$parent.capture, "disabled": _vm.$parent.disabled, "webkitdirectory": _vm.$parent.directory && _vm.$parent.features.directory, "directory": _vm.$parent.directory && _vm.$parent.features.directory, "multiple": _vm.$parent.multiple && _vm.$parent.features.html5 }, on: { "change": _vm.change } });
  };
  var __vue_staticRenderFns__ = [];

  /* style */
  var __vue_inject_styles__ = undefined;
  /* scoped */
  var __vue_scope_id__ = undefined;
  /* module identifier */
  var __vue_module_identifier__ = undefined;
  /* functional template */
  var __vue_is_functional_template__ = false;
  /* component normalizer */
  function __vue_normalize__(template, style, script$$1, scope, functional, moduleIdentifier, createInjector, createInjectorSSR) {
    var component = (typeof script$$1 === 'function' ? script$$1.options : script$$1) || {};

    if (!component.render) {
      component.render = template.render;
      component.staticRenderFns = template.staticRenderFns;
      component._compiled = true;

      if (functional) component.functional = true;
    }

    component._scopeId = scope;

    return component;
  }
  /* style inject */
  function __vue_create_injector__() {
    var head = document.head || document.getElementsByTagName('head')[0];
    var styles = __vue_create_injector__.styles || (__vue_create_injector__.styles = {});
    var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());

    return function addStyle(id, css) {
      if (document.querySelector('style[data-vue-ssr-id~="' + id + '"]')) return; // SSR styles are present.

      var group = isOldIE ? css.media || 'default' : id;
      var style = styles[group] || (styles[group] = { ids: [], parts: [], element: undefined });

      if (!style.ids.includes(id)) {
        var code = css.source;
        var index = style.ids.length;

        style.ids.push(id);

        if (css.map) {
          // https://developer.chrome.com/devtools/docs/javascript-debugging
          // this makes source maps inside style tags work properly in Chrome
          code += '\n/*# sourceURL=' + css.map.sources[0] + ' */';
          // http://stackoverflow.com/a/26603875
          code += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(css.map)))) + ' */';
        }

        if (isOldIE) {
          style.element = style.element || document.querySelector('style[data-group=' + group + ']');
        }

        if (!style.element) {
          var el = style.element = document.createElement('style');
          el.type = 'text/css';

          if (css.media) el.setAttribute('media', css.media);
          if (isOldIE) {
            el.setAttribute('data-group', group);
            el.setAttribute('data-next-index', '0');
          }

          head.appendChild(el);
        }

        if (isOldIE) {
          index = parseInt(style.element.getAttribute('data-next-index'));
          style.element.setAttribute('data-next-index', index + 1);
        }

        if (style.element.styleSheet) {
          style.parts.push(code);
          style.element.styleSheet.cssText = style.parts.filter(Boolean).join('\n');
        } else {
          var textNode = document.createTextNode(code);
          var nodes = style.element.childNodes;
          if (nodes[index]) style.element.removeChild(nodes[index]);
          if (nodes.length) style.element.insertBefore(textNode, nodes[index]);else style.element.appendChild(textNode);
        }
      }
    };
  }
  /* style inject SSR */

  var InputFile = __vue_normalize__({ render: __vue_render__, staticRenderFns: __vue_staticRenderFns__ }, __vue_inject_styles__, __vue_script__, __vue_scope_id__, __vue_is_functional_template__, __vue_module_identifier__, __vue_create_injector__, undefined);

  var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

  var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

  function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

  var CHUNK_DEFAULT_OPTIONS = {
    headers: {},
    action: '',
    minSize: 1048576,
    maxActive: 3,
    maxRetries: 5,

    handler: ChunkUploadHandler
  };

  var script$1 = {
    components: {
      InputFile: InputFile
    },
    props: {
      inputId: {
        type: String
      },

      name: {
        type: String,
        default: 'file'
      },

      accept: {
        type: String
      },

      capture: {},

      disabled: {},

      multiple: {
        type: Boolean
      },

      maximum: {
        type: Number,
        default: function _default() {
          return this.multiple ? 0 : 1;
        }
      },

      addIndex: {
        type: [Boolean, Number]
      },

      directory: {
        type: Boolean
      },

      postAction: {
        type: String
      },

      putAction: {
        type: String
      },

      customAction: {
        type: Function
      },

      headers: {
        type: Object,
        default: Object
      },

      data: {
        type: Object,
        default: Object
      },

      timeout: {
        type: Number,
        default: 0
      },

      drop: {
        default: false
      },

      dropDirectory: {
        type: Boolean,
        default: true
      },

      size: {
        type: Number,
        default: 0
      },

      extensions: {
        default: Array
      },

      value: {
        type: Array,
        default: Array
      },

      thread: {
        type: Number,
        default: 1
      },

      // Chunk upload enabled
      chunkEnabled: {
        type: Boolean,
        default: false
      },

      // Chunk upload properties
      chunk: {
        type: Object,
        default: function _default() {
          return CHUNK_DEFAULT_OPTIONS;
        }
      }
    },

    data: function data() {
      return {
        files: this.value,
        features: {
          html5: true,
          directory: false,
          drag: false
        },

        active: false,
        dropActive: false,

        uploading: 0,

        destroy: false
      };
    },


    /**
     * mounted
     * @return {[type]} [description]
     */
    mounted: function mounted() {
      var input = document.createElement('input');
      input.type = 'file';
      input.multiple = true;

      // html5 特征
      if (window.FormData && input.files) {
        // 上传目录特征
        if (typeof input.webkitdirectory === 'boolean' || typeof input.directory === 'boolean') {
          this.features.directory = true;
        }

        // 拖拽特征
        if (this.features.html5 && typeof input.ondrop !== 'undefined') {
          this.features.drop = true;
        }
      } else {
        this.features.html5 = false;
      }

      // files 定位缓存
      this.maps = {};
      if (this.files) {
        for (var i = 0; i < this.files.length; i++) {
          var file = this.files[i];
          this.maps[file.id] = file;
        }
      }

      this.$nextTick(function () {

        // 更新下父级
        if (this.$parent) {
          this.$parent.$forceUpdate();
        }

        // 拖拽渲染
        this.watchDrop(this.drop);
      });
    },


    /**
     * beforeDestroy
     * @return {[type]} [description]
     */
    beforeDestroy: function beforeDestroy() {
      // 已销毁
      this.destroy = true;

      // 设置成不激活
      this.active = false;
    },


    computed: {
      /**
       * uploading 正在上传的线程
       * @return {[type]} [description]
       */

      /**
       * uploaded 文件列表是否全部已上传
       * @return {[type]} [description]
       */
      uploaded: function uploaded() {
        var file = void 0;
        for (var i = 0; i < this.files.length; i++) {
          file = this.files[i];
          if (file.fileObject && !file.error && !file.success) {
            return false;
          }
        }
        return true;
      },
      chunkOptions: function chunkOptions() {
        return Object.assign(CHUNK_DEFAULT_OPTIONS, this.chunk);
      },
      className: function className() {
        return ['file-uploads', this.features.html5 ? 'file-uploads-html5' : 'file-uploads-html4', this.features.directory && this.directory ? 'file-uploads-directory' : undefined, this.features.drop && this.drop ? 'file-uploads-drop' : undefined, this.disabled ? 'file-uploads-disabled' : undefined];
      }
    },

    watch: {
      active: function active(_active) {
        this.watchActive(_active);
      },
      dropActive: function dropActive() {
        if (this.$parent) {
          this.$parent.$forceUpdate();
        }
      },
      drop: function drop(value) {
        this.watchDrop(value);
      },
      value: function value(files) {
        if (this.files === files) {
          return;
        }
        this.files = files;

        var oldMaps = this.maps;

        // 重写 maps 缓存
        this.maps = {};
        for (var i = 0; i < this.files.length; i++) {
          var file = this.files[i];
          this.maps[file.id] = file;
        }

        // add, update
        for (var key in this.maps) {
          var newFile = this.maps[key];
          var oldFile = oldMaps[key];
          if (newFile !== oldFile) {
            this.emitFile(newFile, oldFile);
          }
        }

        // delete
        for (var _key in oldMaps) {
          if (!this.maps[_key]) {
            this.emitFile(undefined, oldMaps[_key]);
          }
        }
      }
    },

    methods: {

      // 清空
      clear: function clear() {
        if (this.files.length) {
          var files = this.files;
          this.files = [];

          // 定位
          this.maps = {};

          // 事件
          this.emitInput();
          for (var i = 0; i < files.length; i++) {
            this.emitFile(undefined, files[i]);
          }
        }
        return true;
      },


      // 选择
      get: function get(id) {
        if (!id) {
          return false;
        }

        if ((typeof id === 'undefined' ? 'undefined' : _typeof(id)) === 'object') {
          return this.maps[id.id] || false;
        }

        return this.maps[id] || false;
      },


      // 添加
      add: function add(_files) {
        var index = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.addIndex;

        var files = _files;
        var isArray = files instanceof Array;

        // 不是数组整理成数组
        if (!isArray) {
          files = [files];
        }

        // 遍历规范对象
        var addFiles = [];
        for (var i = 0; i < files.length; i++) {
          var file = files[i];
          if (this.features.html5 && file instanceof Blob) {
            file = {
              file: file,
              size: file.size,
              name: file.webkitRelativePath || file.relativePath || file.name || 'unknown',
              type: file.type
            };
          }
          var fileObject = false;
          if (file.fileObject === false) ; else if (file.fileObject) {
            fileObject = true;
          } else if (typeof Element !== 'undefined' && file.el instanceof Element) {
            fileObject = true;
          } else if (typeof Blob !== 'undefined' && file.file instanceof Blob) {
            fileObject = true;
          }
          if (fileObject) {
            file = _extends({
              fileObject: true,
              size: -1,
              name: 'Filename',
              type: '',
              active: false,
              error: '',
              success: false,
              putAction: this.putAction,
              postAction: this.postAction,
              timeout: this.timeout
            }, file, {
              response: {},

              progress: '0.00', // 只读
              speed: 0 // 只读
              // xhr: false,                // 只读
              // iframe: false,             // 只读
            });

            file.data = _extends({}, this.data, file.data ? file.data : {});

            file.headers = _extends({}, this.headers, file.headers ? file.headers : {});
          }

          // 必须包含 id
          if (!file.id) {
            file.id = Math.random().toString(36).substr(2);
          }

          if (this.emitFilter(file, undefined)) {
            continue;
          }

          // 最大数量限制
          if (this.maximum > 1 && addFiles.length + this.files.length >= this.maximum) {
            break;
          }

          addFiles.push(file);

          // 最大数量限制
          if (this.maximum === 1) {
            break;
          }
        }

        // 没有文件
        if (!addFiles.length) {
          return false;
        }

        // 如果是 1 清空
        if (this.maximum === 1) {
          this.clear();
        }

        // 添加进去 files
        var newFiles = void 0;
        if (index === true || index === 0) {
          newFiles = addFiles.concat(this.files);
        } else if (index) {
          var _newFiles;

          newFiles = this.files.concat([]);
          (_newFiles = newFiles).splice.apply(_newFiles, [index, 0].concat(addFiles));
        } else {
          newFiles = this.files.concat(addFiles);
        }

        this.files = newFiles;

        // 定位
        for (var _i = 0; _i < addFiles.length; _i++) {
          var _file2 = addFiles[_i];
          this.maps[_file2.id] = _file2;
        }

        // 事件
        this.emitInput();
        for (var _i2 = 0; _i2 < addFiles.length; _i2++) {
          this.emitFile(addFiles[_i2], undefined);
        }

        return isArray ? addFiles : addFiles[0];
      },


      // 添加表单文件
      addInputFile: function addInputFile(el) {
        var files = [];
        if (el.files) {
          for (var i = 0; i < el.files.length; i++) {
            var file = el.files[i];
            files.push({
              size: file.size,
              name: file.webkitRelativePath || file.relativePath || file.name,
              type: file.type,
              file: file
            });
          }
        } else {
          var names = el.value.replace(/\\/g, '/').split('/');
          delete el.__vuex__;
          files.push({
            name: names[names.length - 1],
            el: el
          });
        }
        return this.add(files);
      },


      // 添加 DataTransfer
      addDataTransfer: function addDataTransfer(dataTransfer) {
        var _this = this;

        var files = [];
        if (dataTransfer.items && dataTransfer.items.length) {
          var items = [];
          for (var i = 0; i < dataTransfer.items.length; i++) {
            var item = dataTransfer.items[i];
            if (item.getAsEntry) {
              item = item.getAsEntry() || item.getAsFile();
            } else if (item.webkitGetAsEntry) {
              item = item.webkitGetAsEntry() || item.getAsFile();
            } else {
              item = item.getAsFile();
            }
            if (item) {
              items.push(item);
            }
          }

          return new Promise(function (resolve, reject) {
            var forEach = function forEach(i) {
              var item = items[i];
              // 结束 文件数量大于 最大数量
              if (!item || _this.maximum > 0 && files.length >= _this.maximum) {
                return resolve(_this.add(files));
              }
              _this.getEntry(item).then(function (results) {
                files.push.apply(files, _toConsumableArray(results));
                forEach(i + 1);
              });
            };
            forEach(0);
          });
        }

        if (dataTransfer.files.length) {
          for (var _i3 = 0; _i3 < dataTransfer.files.length; _i3++) {
            files.push(dataTransfer.files[_i3]);
            if (this.maximum > 0 && files.length >= this.maximum) {
              break;
            }
          }
          return Promise.resolve(this.add(files));
        }

        return Promise.resolve([]);
      },


      // 获得 entry
      getEntry: function getEntry(entry) {
        var _this2 = this;

        var path = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

        return new Promise(function (resolve, reject) {
          if (entry.isFile) {
            entry.file(function (file) {
              resolve([{
                size: file.size,
                name: path + file.name,
                type: file.type,
                file: file
              }]);
            });
          } else if (entry.isDirectory && _this2.dropDirectory) {
            var files = [];
            var dirReader = entry.createReader();
            var readEntries = function readEntries() {
              dirReader.readEntries(function (entries) {
                var forEach = function forEach(i) {
                  if (!entries[i] && i === 0 || _this2.maximum > 0 && files.length >= _this2.maximum) {
                    return resolve(files);
                  }
                  if (!entries[i]) {
                    return readEntries();
                  }
                  _this2.getEntry(entries[i], path + entry.name + '/').then(function (results) {
                    files.push.apply(files, _toConsumableArray(results));
                    forEach(i + 1);
                  });
                };
                forEach(0);
              });
            };
            readEntries();
          } else {
            resolve([]);
          }
        });
      },
      replace: function replace(id1, id2) {
        var file1 = this.get(id1);
        var file2 = this.get(id2);
        if (!file1 || !file2 || file1 === file2) {
          return false;
        }
        var files = this.files.concat([]);
        var index1 = files.indexOf(file1);
        var index2 = files.indexOf(file2);
        if (index1 === -1 || index2 === -1) {
          return false;
        }
        files[index1] = file2;
        files[index2] = file1;
        this.files = files;
        this.emitInput();
        return true;
      },


      // 移除
      remove: function remove(id) {
        var file = this.get(id);
        if (file) {
          if (this.emitFilter(undefined, file)) {
            return false;
          }
          var files = this.files.concat([]);
          var index = files.indexOf(file);
          if (index === -1) {
            console.error('remove', file);
            return false;
          }
          files.splice(index, 1);
          this.files = files;

          // 定位
          delete this.maps[file.id];

          // 事件
          this.emitInput();
          this.emitFile(undefined, file);
        }
        return file;
      },


      // 更新
      update: function update(id, data) {
        var file = this.get(id);
        if (file) {
          var newFile = _extends({}, file, data);
          // 停用必须加上错误
          if (file.fileObject && file.active && !newFile.active && !newFile.error && !newFile.success) {
            newFile.error = 'abort';
          }

          if (this.emitFilter(newFile, file)) {
            return false;
          }

          var files = this.files.concat([]);
          var index = files.indexOf(file);
          if (index === -1) {
            console.error('update', file);
            return false;
          }
          files.splice(index, 1, newFile);
          this.files = files;

          // 删除  旧定位 写入 新定位 （已便支持修改id)
          delete this.maps[file.id];
          this.maps[newFile.id] = newFile;

          // 事件
          this.emitInput();
          this.emitFile(newFile, file);
          return newFile;
        }
        return false;
      },


      // 预处理 事件 过滤器
      emitFilter: function emitFilter(newFile, oldFile) {
        var isPrevent = false;
        this.$emit('input-filter', newFile, oldFile, function () {
          isPrevent = true;
          return isPrevent;
        });
        return isPrevent;
      },


      // 处理后 事件 分发
      emitFile: function emitFile(newFile, oldFile) {
        this.$emit('input-file', newFile, oldFile);
        if (newFile && newFile.fileObject && newFile.active && (!oldFile || !oldFile.active)) {
          this.uploading++;
          // 激活
          this.$nextTick(function () {
            var _this3 = this;

            setTimeout(function () {
              _this3.upload(newFile).then(function () {
                // eslint-disable-next-line
                newFile = _this3.get(newFile);
                if (newFile && newFile.fileObject) {
                  _this3.update(newFile, {
                    active: false,
                    success: !newFile.error
                  });
                }
              }).catch(function (e) {
                _this3.update(newFile, {
                  active: false,
                  success: false,
                  error: e.code || e.error || e.message || e
                });
              });
            }, parseInt(Math.random() * 50 + 50, 10));
          });
        } else if ((!newFile || !newFile.fileObject || !newFile.active) && oldFile && oldFile.fileObject && oldFile.active) {
          // 停止
          this.uploading--;
        }

        // 自动延续激活
        if (this.active && (Boolean(newFile) !== Boolean(oldFile) || newFile.active !== oldFile.active)) {
          this.watchActive(true);
        }
      },
      emitInput: function emitInput() {
        this.$emit('input', this.files);
      },


      // 上传
      upload: function upload(id) {
        var file = this.get(id);

        // 被删除
        if (!file) {
          return Promise.reject('not_exists');
        }

        // 不是文件对象
        if (!file.fileObject) {
          return Promise.reject('file_object');
        }

        // 有错误直接响应
        if (file.error) {
          return Promise.reject(file.error);
        }

        // 已完成直接响应
        if (file.success) {
          return Promise.resolve(file);
        }

        // 后缀
        var extensions = this.extensions;
        if (extensions && (extensions.length || typeof extensions.length === 'undefined')) {
          if ((typeof extensions === 'undefined' ? 'undefined' : _typeof(extensions)) !== 'object' || !(extensions instanceof RegExp)) {
            if (typeof extensions === 'string') {
              extensions = extensions.split(',').map(function (value) {
                return value.trim();
              }).filter(function (value) {
                return value;
              });
            }
            extensions = new RegExp('\\.(' + extensions.join('|').replace(/\./g, '\\.') + ')$', 'i');
          }
          if (file.name.search(extensions) === -1) {
            return Promise.reject('extension');
          }
        }

        // 大小
        if (this.size > 0 && file.size >= 0 && file.size > this.size) {
          return Promise.reject('size');
        }

        if (this.customAction) {
          return this.customAction(file, this);
        }

        if (this.features.html5) {
          if (this.shouldUseChunkUpload(file)) {
            return this.uploadChunk(file);
          }
          if (file.putAction) {
            return this.uploadPut(file);
          }
          if (file.postAction) {
            return this.uploadHtml5(file);
          }
        }
        if (file.postAction) {
          return this.uploadHtml4(file);
        }
        return Promise.reject('No action configured');
      },


      /**
       * Whether this file should be uploaded using chunk upload or not
       *
       * @param Object file
       */
      shouldUseChunkUpload: function shouldUseChunkUpload(file) {
        return this.chunkEnabled && !!this.chunkOptions.handler && file.size > this.chunkOptions.minSize;
      },


      /**
       * Upload a file using Chunk method
       *
       * @param File file
       */
      uploadChunk: function uploadChunk(file) {
        var HandlerClass = this.chunkOptions.handler;
        file.chunk = new HandlerClass(file, this.chunkOptions);

        return file.chunk.upload();
      },
      uploadPut: function uploadPut(file) {
        var querys = [];
        var value = void 0;
        for (var key in file.data) {
          value = file.data[key];
          if (value !== null && value !== undefined) {
            querys.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
          }
        }
        var queryString = querys.length ? (file.putAction.indexOf('?') === -1 ? '?' : '&') + querys.join('&') : '';
        var xhr = new XMLHttpRequest();
        xhr.open('PUT', file.putAction + queryString);
        return this.uploadXhr(xhr, file, file.file);
      },
      uploadHtml5: function uploadHtml5(file) {
        var form = new window.FormData();
        var value = void 0;
        for (var key in file.data) {
          value = file.data[key];
          if (value && (typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' && typeof value.toString !== 'function') {
            if (value instanceof File) {
              form.append(key, value, value.name);
            } else {
              form.append(key, JSON.stringify(value));
            }
          } else if (value !== null && value !== undefined) {
            form.append(key, value);
          }
        }
        form.append(this.name, file.file, file.file.filename || file.name);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', file.postAction);
        return this.uploadXhr(xhr, file, form);
      },
      uploadXhr: function uploadXhr(xhr, _file, body) {
        var _this4 = this;

        var file = _file;
        var speedTime = 0;
        var speedLoaded = 0;

        // 进度条
        xhr.upload.onprogress = function (e) {
          // 还未开始上传 已删除 未激活
          file = _this4.get(file);
          if (!e.lengthComputable || !file || !file.fileObject || !file.active) {
            return;
          }

          // 进度 速度 每秒更新一次
          var speedTime2 = Math.round(Date.now() / 1000);
          if (speedTime2 === speedTime) {
            return;
          }
          speedTime = speedTime2;

          file = _this4.update(file, {
            progress: (e.loaded / e.total * 100).toFixed(2),
            speed: e.loaded - speedLoaded
          });
          speedLoaded = e.loaded;
        };

        // 检查激活状态
        var interval = setInterval(function () {
          file = _this4.get(file);
          if (file && file.fileObject && !file.success && !file.error && file.active) {
            return;
          }

          if (interval) {
            clearInterval(interval);
            interval = false;
          }

          try {
            xhr.abort();
            xhr.timeout = 1;
          } catch (e) {}
        }, 100);

        return new Promise(function (resolve, reject) {
          var complete = void 0;
          var fn = function fn(e) {
            // 已经处理过了
            if (complete) {
              return;
            }
            complete = true;
            if (interval) {
              clearInterval(interval);
              interval = false;
            }

            file = _this4.get(file);

            // 不存在直接响应
            if (!file) {
              return reject('not_exists');
            }

            // 不是文件对象
            if (!file.fileObject) {
              return reject('file_object');
            }

            // 有错误自动响应
            if (file.error) {
              return reject(file.error);
            }

            // 未激活
            if (!file.active) {
              return reject('abort');
            }

            // 已完成 直接相应
            if (file.success) {
              return resolve(file);
            }

            var data = {};

            switch (e.type) {
              case 'timeout':
              case 'abort':
                data.error = e.type;
                break;
              case 'error':
                if (!xhr.status) {
                  data.error = 'network';
                } else if (xhr.status >= 500) {
                  data.error = 'server';
                } else if (xhr.status >= 400) {
                  data.error = 'denied';
                }
                break;
              default:
                if (xhr.status >= 500) {
                  data.error = 'server';
                } else if (xhr.status >= 400) {
                  data.error = 'denied';
                } else {
                  data.progress = '100.00';
                }
            }

            if (xhr.responseText) {
              var contentType = xhr.getResponseHeader('Content-Type');
              if (contentType && contentType.indexOf('/json') !== -1) {
                data.response = JSON.parse(xhr.responseText);
              } else {
                data.response = xhr.responseText;
              }
            }

            // 更新
            file = _this4.update(file, data);

            // 相应错误
            if (file.error) {
              return reject(file.error);
            }

            // 响应
            return resolve(file);
          };

          // 事件
          xhr.onload = fn;
          xhr.onerror = fn;
          xhr.onabort = fn;
          xhr.ontimeout = fn;

          // 超时
          if (file.timeout) {
            xhr.timeout = file.timeout;
          }

          // headers
          for (var key in file.headers) {
            xhr.setRequestHeader(key, file.headers[key]);
          }

          // 更新 xhr
          file = _this4.update(file, { xhr: xhr });

          // 开始上传
          xhr.send(body);
        });
      },
      uploadHtml4: function uploadHtml4(_file) {
        var _this5 = this;

        var file = _file;
        var onKeydown = function onKeydown(e) {
          if (e.keyCode === 27) {
            e.preventDefault();
          }
        };

        var iframe = document.createElement('iframe');
        iframe.id = 'upload-iframe-' + file.id;
        iframe.name = 'upload-iframe-' + file.id;
        iframe.src = 'about:blank';
        iframe.setAttribute('style', 'width:1px;height:1px;top:-999em;position:absolute; margin-top:-999em;');

        var form = document.createElement('form');

        form.action = file.postAction;

        form.name = 'upload-form-' + file.id;

        form.setAttribute('method', 'POST');
        form.setAttribute('target', 'upload-iframe-' + file.id);
        form.setAttribute('enctype', 'multipart/form-data');

        var value = void 0;
        var input = void 0;
        for (var key in file.data) {
          value = file.data[key];
          if (value && (typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' && typeof value.toString !== 'function') {
            value = JSON.stringify(value);
          }
          if (value !== null && value !== undefined) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
          }
        }
        form.appendChild(file.el);

        document.body.appendChild(iframe).appendChild(form);

        var getResponseData = function getResponseData() {
          var doc = void 0;
          try {
            if (iframe.contentWindow) {
              doc = iframe.contentWindow.document;
            }
          } catch (err) {}
          if (!doc) {
            try {
              doc = iframe.contentDocument ? iframe.contentDocument : iframe.document;
            } catch (err) {
              doc = iframe.document;
            }
          }
          if (doc && doc.body) {
            return doc.body.innerHTML;
          }
          return null;
        };

        return new Promise(function (resolve, reject) {
          setTimeout(function () {
            file = _this5.update(file, { iframe: iframe });

            // 不存在
            if (!file) {
              return reject('not_exists');
            }

            // 定时检查
            var interval = setInterval(function () {
              file = _this5.get(file);
              if (file && file.fileObject && !file.success && !file.error && file.active) {
                return;
              }

              if (interval) {
                clearInterval(interval);
                interval = false;
              }

              iframe.onabort({ type: file ? 'abort' : 'not_exists' });
            }, 100);

            var complete = void 0;
            var fn = function fn(e) {
              // 已经处理过了
              if (complete) {
                return;
              }
              complete = true;

              if (interval) {
                clearInterval(interval);
                interval = false;
              }

              // 关闭 esc 事件
              document.body.removeEventListener('keydown', onKeydown);

              file = _this5.get(file);

              // 不存在直接响应
              if (!file) {
                return reject('not_exists');
              }

              // 不是文件对象
              if (!file.fileObject) {
                return reject('file_object');
              }

              // 有错误自动响应
              if (file.error) {
                return reject(file.error);
              }

              // 未激活
              if (!file.active) {
                return reject('abort');
              }

              // 已完成 直接相应
              if (file.success) {
                return resolve(file);
              }

              var response = getResponseData();
              var data = {};
              switch (e.type) {
                case 'abort':
                  data.error = 'abort';
                  break;
                case 'error':
                  if (file.error) {
                    data.error = file.error;
                  } else if (response === null) {
                    data.error = 'network';
                  } else {
                    data.error = 'denied';
                  }
                  break;
                default:
                  if (file.error) {
                    data.error = file.error;
                  } else if (data === null) {
                    data.error = 'network';
                  } else {
                    data.progress = '100.00';
                  }
              }

              if (response !== null) {
                if (response && response.substr(0, 1) === '{' && response.substr(response.length - 1, 1) === '}') {
                  try {
                    response = JSON.parse(response);
                  } catch (err) {}
                }
                data.response = response;
              }

              // 更新
              file = _this5.update(file, data);

              if (file.error) {
                return reject(file.error);
              }

              // 响应
              return resolve(file);
            };

            // 添加事件
            iframe.onload = fn;
            iframe.onerror = fn;
            iframe.onabort = fn;

            // 禁止 esc 键
            document.body.addEventListener('keydown', onKeydown);

            // 提交
            form.submit();
          }, 50);
        }).then(function (res) {
          iframe.parentNode && iframe.parentNode.removeChild(iframe);
          return res;
        }).catch(function (res) {
          iframe.parentNode && iframe.parentNode.removeChild(iframe);
          return res;
        });
      },
      watchActive: function watchActive(active) {
        var file = void 0;
        var index = 0;
        while (file = this.files[index]) {
          index++;
          if (!file.fileObject) ; else if (active && !this.destroy) {
            if (this.uploading >= this.thread || this.uploading && !this.features.html5) {
              break;
            }
            if (!file.active && !file.error && !file.success) {
              this.update(file, { active: true });
            }
          } else {
            if (file.active) {
              this.update(file, { active: false });
            }
          }
        }
        if (this.uploading === 0) {
          this.active = false;
        }
      },
      watchDrop: function watchDrop(_el) {
        var el = _el;
        if (!this.features.drop) {
          return;
        }

        // 移除挂载
        if (this.dropElement) {
          try {
            document.removeEventListener('dragenter', this.onDragenter, false);
            document.removeEventListener('dragleave', this.onDragleave, false);
            document.removeEventListener('drop', this.onDocumentDrop, false);
            this.dropElement.removeEventListener('dragover', this.onDragover, false);
            this.dropElement.removeEventListener('drop', this.onDrop, false);
          } catch (e) {}
        }

        if (!el) {
          el = false;
        } else if (typeof el === 'string') {
          el = document.querySelector(el) || this.$root.$el.querySelector(el);
        } else if (el === true) {
          el = this.$parent.$el;
        }

        this.dropElement = el;

        if (this.dropElement) {
          document.addEventListener('dragenter', this.onDragenter, false);
          document.addEventListener('dragleave', this.onDragleave, false);
          document.addEventListener('drop', this.onDocumentDrop, false);
          this.dropElement.addEventListener('dragover', this.onDragover, false);
          this.dropElement.addEventListener('drop', this.onDrop, false);
        }
      },
      onDragenter: function onDragenter(e) {
        e.preventDefault();
        if (this.dropActive) {
          return;
        }
        if (!e.dataTransfer) {
          return;
        }
        var dt = e.dataTransfer;
        if (dt.files && dt.files.length) {
          this.dropActive = true;
        } else if (!dt.types) {
          this.dropActive = true;
        } else if (dt.types.indexOf && dt.types.indexOf('Files') !== -1) {
          this.dropActive = true;
        } else if (dt.types.contains && dt.types.contains('Files')) {
          this.dropActive = true;
        }
      },
      onDragleave: function onDragleave(e) {
        e.preventDefault();
        if (!this.dropActive) {
          return;
        }
        if (e.target.nodeName === 'HTML' || e.target === e.explicitOriginalTarget || !e.fromElement && (e.clientX <= 0 || e.clientY <= 0 || e.clientX >= window.innerWidth || e.clientY >= window.innerHeight)) {
          this.dropActive = false;
        }
      },
      onDragover: function onDragover(e) {
        e.preventDefault();
      },
      onDocumentDrop: function onDocumentDrop() {
        this.dropActive = false;
      },
      onDrop: function onDrop(e) {
        e.preventDefault();
        this.addDataTransfer(e.dataTransfer);
      }
    }
  };

  /* script */
  var __vue_script__$1 = script$1;

  /* template */
  var __vue_render__$1 = function __vue_render__() {
    var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;return _c('span', { class: _vm.className }, [_vm._t("default"), _vm._v(" "), _c('label', { attrs: { "for": _vm.inputId || _vm.name } }), _vm._v(" "), _c('input-file')], 2);
  };
  var __vue_staticRenderFns__$1 = [];

  /* style */
  var __vue_inject_styles__$1 = function (inject) {
    if (!inject) return;
    inject("data-v-595958af_0", { source: "\n.file-uploads{overflow:hidden;position:relative;text-align:center;display:inline-block\n}\n.file-uploads.file-uploads-html4 input,.file-uploads.file-uploads-html5 label{background:#fff;opacity:0;font-size:20em;z-index:1;top:0;left:0;right:0;bottom:0;position:absolute;width:100%;height:100%\n}\n.file-uploads.file-uploads-html4 label,.file-uploads.file-uploads-html5 input{background:rgba(255,255,255,0);overflow:hidden;position:fixed;width:1px;height:1px;z-index:-1;opacity:0\n}", map: undefined, media: undefined });
  };
  /* scoped */
  var __vue_scope_id__$1 = undefined;
  /* module identifier */
  var __vue_module_identifier__$1 = undefined;
  /* functional template */
  var __vue_is_functional_template__$1 = false;
  /* component normalizer */
  function __vue_normalize__$1(template, style, script, scope, functional, moduleIdentifier, createInjector, createInjectorSSR) {
    var component = (typeof script === 'function' ? script.options : script) || {};

    if (!component.render) {
      component.render = template.render;
      component.staticRenderFns = template.staticRenderFns;
      component._compiled = true;

      if (functional) component.functional = true;
    }

    component._scopeId = scope;

    {
      var hook = void 0;
      if (style) {
        hook = function hook(context) {
          style.call(this, createInjector(context));
        };
      }

      if (hook !== undefined) {
        if (component.functional) {
          // register for functional component in vue file
          var originalRender = component.render;
          component.render = function renderWithStyleInjection(h, context) {
            hook.call(context);
            return originalRender(h, context);
          };
        } else {
          // inject component registration as beforeCreate hook
          var existing = component.beforeCreate;
          component.beforeCreate = existing ? [].concat(existing, hook) : [hook];
        }
      }
    }

    return component;
  }
  /* style inject */
  function __vue_create_injector__$1() {
    var head = document.head || document.getElementsByTagName('head')[0];
    var styles = __vue_create_injector__$1.styles || (__vue_create_injector__$1.styles = {});
    var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());

    return function addStyle(id, css) {
      if (document.querySelector('style[data-vue-ssr-id~="' + id + '"]')) return; // SSR styles are present.

      var group = isOldIE ? css.media || 'default' : id;
      var style = styles[group] || (styles[group] = { ids: [], parts: [], element: undefined });

      if (!style.ids.includes(id)) {
        var code = css.source;
        var index = style.ids.length;

        style.ids.push(id);

        if (css.map) {
          // https://developer.chrome.com/devtools/docs/javascript-debugging
          // this makes source maps inside style tags work properly in Chrome
          code += '\n/*# sourceURL=' + css.map.sources[0] + ' */';
          // http://stackoverflow.com/a/26603875
          code += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(css.map)))) + ' */';
        }

        if (isOldIE) {
          style.element = style.element || document.querySelector('style[data-group=' + group + ']');
        }

        if (!style.element) {
          var el = style.element = document.createElement('style');
          el.type = 'text/css';

          if (css.media) el.setAttribute('media', css.media);
          if (isOldIE) {
            el.setAttribute('data-group', group);
            el.setAttribute('data-next-index', '0');
          }

          head.appendChild(el);
        }

        if (isOldIE) {
          index = parseInt(style.element.getAttribute('data-next-index'));
          style.element.setAttribute('data-next-index', index + 1);
        }

        if (style.element.styleSheet) {
          style.parts.push(code);
          style.element.styleSheet.cssText = style.parts.filter(Boolean).join('\n');
        } else {
          var textNode = document.createTextNode(code);
          var nodes = style.element.childNodes;
          if (nodes[index]) style.element.removeChild(nodes[index]);
          if (nodes.length) style.element.insertBefore(textNode, nodes[index]);else style.element.appendChild(textNode);
        }
      }
    };
  }
  /* style inject SSR */

  var FileUpload = __vue_normalize__$1({ render: __vue_render__$1, staticRenderFns: __vue_staticRenderFns__$1 }, __vue_inject_styles__$1, __vue_script__$1, __vue_scope_id__$1, __vue_is_functional_template__$1, __vue_module_identifier__$1, __vue_create_injector__$1, undefined);

  var FileUpload$1 = /*#__PURE__*/Object.freeze({
    default: FileUpload
  });

  var require$$0 = ( FileUpload$1 && FileUpload ) || FileUpload$1;

  var src = require$$0;

  return src;

})));
//# sourceMappingURL=vue-upload-component.js.map


/***/ }),
/* 51 */
/***/ (function(module, exports) {

module.exports = "\n\t<div _v-3fe3cec8=\"\">\n\t\t<h4 _v-3fe3cec8=\"\">{{strings.add_desc}}</h4>\n\t\t<div class=\"field  columns\" _v-3fe3cec8=\"\">\n\t\t\t<div class=\"column\" v-for=\"file in files\" _v-3fe3cec8=\"\">\n                <span class=\"tag\" _v-3fe3cec8=\"\">\n                    <i _v-3fe3cec8=\"\">{{file.name}}</i>\n                    <i v-if=\"!file.active &amp;&amp; !file.success &amp;&amp; file.error === ''\" class=\"dashicons dashicons-yes icon has-text-grey-light\" _v-3fe3cec8=\"\"></i>\n                    <i v-else-if=\"file.active\" class=\"dashicons dashicons-marker icon spin has-text-warning\" _v-3fe3cec8=\"\"></i>\n                    <i v-else-if=\"!file.active &amp;&amp; file.success\" class=\"dashicons dashicons-yes icon has-text-success\" _v-3fe3cec8=\"\"></i>\n                    <i v-else=\"\" class=\"dashicons dashicons-no-alt icon has-text-danger\" _v-3fe3cec8=\"\"></i>\n                </span>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"column \" _v-3fe3cec8=\"\">\n\t\t\t<file-upload class=\"button is-secondary is-rounded\" :post-action=\" global.root + '/add_watermark'\" :headers=\"{'X-WP-Nonce': global.nonce}\" extensions=\"gif,jpg,jpeg,png,webp\" accept=\"image/png,image/gif,image/jpeg,image/webp\" :size=\"1024 * 1024 * 10\" v-model=\"files\" @input-filter=\"inputFilter\" @input-file=\"inputFile\" :disabled=\"loading\" ref=\"upload\" _v-3fe3cec8=\"\">\n\t\t\t\t<i class=\"dashicons dashicons-plus icon\" _v-3fe3cec8=\"\"></i>\n\t\t\t\t{{strings.upload}}\n\t\t\t</file-upload>\n\t\t\t<br _v-3fe3cec8=\"\"><br _v-3fe3cec8=\"\"><span class=\"tag is-danger\" v-if=\"is_error\" _v-3fe3cec8=\"\">{{error_message}}</span>\n\t\t</div>\n\t\t<hr _v-3fe3cec8=\"\">\n\t\t<div class=\"box\" _v-3fe3cec8=\"\">\n\t\t\t<h3 _v-3fe3cec8=\"\"><span class=\"dashicons dashicons-menu\" _v-3fe3cec8=\"\"></span> {{strings.list_header}} </h3>\n\t\t\t<small _v-3fe3cec8=\"\"><i _v-3fe3cec8=\"\">{{strings.max_allowed}}</i></small>\n\t\t\t\n\t\t\t<div class=\"optimized-images\" _v-3fe3cec8=\"\">\n\t\t\t\t<div v-if=\"!noImages\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<h3 class=\"has-text-centered\" _v-3fe3cec8=\"\">{{strings.last}} {{strings.optimized_images}}</h3>\n\t\t\t\t\t<table class=\"table is-striped is-hoverable is-fullwidth\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<thead _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<tr _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<th class=\"optml-image-heading\" _v-3fe3cec8=\"\">{{strings.id}}</th>\n\t\t\t\t\t\t\t<th class=\"optml-image-heading\" _v-3fe3cec8=\"\">{{strings.image}}</th>\n\t\t\t\t\t\t\t<th class=\"optml-image-heading\" _v-3fe3cec8=\"\">{{strings.action}}</th>\n\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</thead>\n\t\t\t\t\t\t<tbody _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<tr v-for=\"(item, index) in watermarkData\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<td _v-3fe3cec8=\"\"><code _v-3fe3cec8=\"\">#{{item.ID}}</code></td>\n\t\t\t\t\t\t\t<td _v-3fe3cec8=\"\"><img :src=\"item.guid\" class=\"optml-image-watermark\" width=\"50\" _v-3fe3cec8=\"\"></td>\n\t\t\t\t\t\t\t<td width=\"50\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<a @click=\"removeWatermark(item.ID)\" class=\"button is-small is-danger is-rounded\" :class=\"{'is-loading':loading}\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t<span class=\"dashicons dashicons-no-alt icon\" _v-3fe3cec8=\"\"></span>\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t\t\n\t\t\t\t\t<span class=\"tag is-success\" v-if=\"loading\" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.loading_remove_watermark}}\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<table class=\"table is-striped is-hoverable is-fullwidth\" v-if=\"noImages\" _v-3fe3cec8=\"\">\n\t\t\t\t<thead _v-3fe3cec8=\"\">\n\t\t\t\t<tr _v-3fe3cec8=\"\">\n\t\t\t\t\t<th class=\"optml-image-heading has-text-centered\" v-html=\"strings.no_images_found\" _v-3fe3cec8=\"\"></th>\n\t\t\t\t</tr>\n\t\t\t\t</thead>\n\t\t\t</table>\n\t\t\t<hr _v-3fe3cec8=\"\">\n\t\t\t<h3 _v-3fe3cec8=\"\"><span class=\"dashicons dashicons-grid-view\" _v-3fe3cec8=\"\"></span> {{strings.settings_header}} </h3>\n\t\t\t<br _v-3fe3cec8=\"\">\n\t\t\t<div class=\"field  is-fullwidth columns\" _v-3fe3cec8=\"\">\n\t\t\t\t<label class=\"label is-half column has-text-grey-dark no-padding-right \" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.wm_title}}\n\t\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t{{strings.wm_desc}}\n\t\t\t\t\t</p>\n\t\t\t\t</label>\n\t\t\t\t\n\t\t\t\t<div class=\"column is-paddingless\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<div class=\"columns\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<div class=\"field column is-narrow\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<div class=\"select\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<select title=\"Watermark Selection\" v-model=\"selectedWatermark\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t<option value=\"-1\" _v-3fe3cec8=\"\">No watermark</option>\n\t\t\t\t\t\t\t\t\t<option v-for=\"(item, index) in watermarkData\" :value=\"item.ID\" _v-3fe3cec8=\"\">#({{item.ID}})\n\t\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"field  is-fullwidth columns\" _v-3fe3cec8=\"\">\n\t\t\t\t<label class=\"label is-half column has-text-grey-dark no-padding-right \" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.opacity_title}}\n\t\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t{{strings.opacity_desc}}\n\t\t\t\t\t</p>\n\t\t\t\t</label>\n\t\t\t\t\n\t\t\t\t<div class=\"column is-paddingless\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<div class=\"columns\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<div class=\"field column is-narrow has-addons\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<p class=\"control\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t{{strings.opacity_field}}\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t<p class=\"control \" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<input v-model=\"watermarkOpacity\" class=\"input is-small\" type=\"number\" min=\"0\" max=\"100\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"field  columns\" _v-3fe3cec8=\"\">\n\t\t\t\t<label class=\"label column has-text-grey-dark\" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.position_title}}\n\t\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t{{strings.position_desc}}\n\t\t\t\t\t</p>\n\t\t\t\t</label>\n\t\t\t\t<div class=\"column  buttons \" _v-3fe3cec8=\"\">\n\t\t\t\t\t<div class=\"field columns is-gapless is-marginless \" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<div class=\"is-fullwidth  optml-layout-grid\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<a @click=\"changePosition('nowe')\" :class=\"{ 'is-info':isActivePosition ('nowe'), '  is-selected':watermarkSettings.position === 'nowe'  }\" class=\"grid-button  \" :title=\"strings.pos_nowe_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('no')\" :class=\"{ 'is-info':isActivePosition ('no'), '  is-selected':watermarkSettings.position === 'no'  }\" class=\"grid-button  \" :title=\"strings.pos_no_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('noea')\" :class=\"{ 'is-info':isActivePosition ('noea'), '  is-selected':watermarkSettings.position === 'noea'  }\" class=\"grid-button\" :title=\"strings.pos_noea_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('we')\" :class=\"{ 'is-info':isActivePosition ('we'), '  is-selected':watermarkSettings.position === 'we'  }\" class=\"grid-button\" :title=\"strings.pos_we_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('ce')\" :class=\"{ 'is-info':isActivePosition ('ce'), '  is-selected':watermarkSettings.position === 'ce'  }\" class=\"grid-button\" :title=\"strings.pos_ce_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('ea')\" :class=\"{ 'is-info':isActivePosition ('ea'), '  is-selected':watermarkSettings.position === 'ea'  }\" class=\"grid-button\" :title=\"strings.pos_ea_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('sowe')\" :class=\"{ 'is-info':isActivePosition ('sowe'), '  is-selected':watermarkSettings.position === 'sowe'  }\" class=\"grid-button\" :title=\"strings.pos_sowe_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('so')\" :class=\"{ 'is-info':isActivePosition ('so'), '  is-selected':watermarkSettings.position === 'so'  }\" class=\"grid-button\" :title=\"strings.pos_so_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t<a @click=\"changePosition('soea')\" :class=\"{ 'is-info':isActivePosition ('soea'), '  is-selected':watermarkSettings.position === 'soea'  }\" class=\"grid-button\" :title=\"strings.pos_soea_title\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<br _v-3fe3cec8=\"\">\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"field  is-fullwidth columns \" _v-3fe3cec8=\"\">\n\t\t\t\t<label class=\"label is-half column has-text-grey-dark no-padding-right \" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.offset_title}}\n\t\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t{{strings.offset_desc}}\n\t\t\t\t\t</p>\n\t\t\t\t</label>\n\t\t\t\t\n\t\t\t\t<div class=\"column is-paddingless\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<div class=\"columns\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<div class=\"field column is-narrow   has-addons\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<p class=\"control\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t{{strings.offset_x_field}}\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t<p class=\"control \" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<input v-model=\"watermarkX\" class=\"input  is-tiny is-small\" type=\"number\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"field column is-narrow  has-addons\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<p class=\"control\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t{{strings.offset_y_field}}\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t<p class=\"control \" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<input v-model=\"watermarkY\" class=\"input is-small is-tiny\" type=\"number\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"field  is-fullwidth columns\" _v-3fe3cec8=\"\">\n\t\t\t\t<label class=\"label is-half column has-text-grey-dark no-padding-right \" _v-3fe3cec8=\"\">\n\t\t\t\t\t{{strings.scale_title}}\n\t\t\t\t\t<p class=\"is-italic has-text-weight-normal\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t{{strings.scale_desc}}\n\t\t\t\t\t</p>\n\t\t\t\t</label>\n\t\t\t\t\n\t\t\t\t<div class=\"column is-paddingless\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<div class=\"columns\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t<div class=\"field column is-narrow has-addons\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t<p class=\"control\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<a class=\"button is-small is-static\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t\t{{strings.scale_field}}\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t<p class=\"control \" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t\t<input v-model=\"watermarkScale\" class=\"input is-small\" type=\"number\" min=\"0\" max=\"100\" _v-3fe3cec8=\"\">\n\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<p class=\"control column has-text-centered-desktop has-text-left-touch  \" _v-3fe3cec8=\"\">\n\t\t\t\t<a @click=\"saveChanges()\" class=\"button is-small is-success \" :class=\"{'is-loading':loading}\" _v-3fe3cec8=\"\">\n\t\t\t\t\t<span class=\"dashicons dashicons-yes icon\" _v-3fe3cec8=\"\"></span>\n\t\t\t\t\t<span _v-3fe3cec8=\"\">\t{{strings.save_changes}}</span>\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 52 */
/***/ (function(module, exports) {

module.exports = "\r\n\t<div class=\"columns is-desktop\">\r\n\t\t\r\n\t\t<div class=\"column  \">\r\n\t\t\t<div class=\"card\">\r\n\t\t\t\t<app-header></app-header>\r\n\t\t\t\t<div class=\"card-content\">\r\n\t\t\t\t\t<div class=\"content\">\r\n\t\t\t\t\t\t<connect-layout v-if=\"! this.$store.state.connected\"></connect-layout>\r\n\t\t\t\t\t\t<transition name=\"fade\" mode=\"out-in\">\r\n\t\t\t\t\t\t\t<div v-if=\"this.$store.state.connected\">\r\n\t\t\t\t\t\t\t\t<div class=\"tabs is-left is-boxed is-medium\">\r\n\t\t\t\t\t\t\t\t\t<ul class=\"is-marginless\">\r\n\t\t\t\t\t\t\t\t\t\t<li :class=\"tab === 'dashboard' ? 'is-active' : ''\">\r\n\t\t\t\t\t\t\t\t\t\t\t<a @click=\"changeTab('dashboard')\" class=\"is-size-6-mobile\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"icon is-size-6-mobile is-size-6-tablet  dashicons dashicons-admin-home\"></span>\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"is-size-6-mobile   is-size-6-touch \">{{strings.dashboard_menu_item}}</span>\r\n\t\t\t\t\t\t\t\t\t\t\t</a>\r\n\t\t\t\t\t\t\t\t\t\t</li>\r\n\t\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\t\t<li :class=\"tab === 'settings' ? 'is-active' : ''\">\r\n\t\t\t\t\t\t\t\t\t\t\t<a @click=\"changeTab('settings')\" class=\"is-size-6-mobile  \">\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"icon is-size-6-mobile   is-size-6-tablet dashicons dashicons-admin-settings\"></span>\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"is-size-6-mobile  is-size-6-touch\">{{strings.settings_menu_item}}</span>\r\n\t\t\t\t\t\t\t\t\t\t\t</a>\r\n\t\t\t\t\t\t\t\t\t\t</li>\r\n\t\t\t\t\t\t\t\t\t\t<li :class=\"tab === 'watermarks' ? 'is-active' : ''\">\r\n\t\t\t\t\t\t\t\t\t\t\t<a @click=\"changeTab('watermarks')\" class=\"is-size-6-mobile\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"icon is-size-6-mobile  is-size-6-tablet dashicons dashicons-tag\"></span>\r\n\t\t\t\t\t\t\t\t\t\t\t\t<span class=\"is-size-6-mobile   is-size-6-touch\">{{strings.watermarks_menu_item}}</span>\r\n\t\t\t\t\t\t\t\t\t\t\t</a>\r\n\t\t\t\t\t\t\t\t\t\t</li>\r\n\t\t\t\t\t\t\t\t\t</ul>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t<div class=\"is-tab\" v-if=\"tab === 'dashboard' \" :class=\"remove_images ? 'no-images' : '' \">\r\n\t\t\t\t\t\t\t\t\t<div class=\"notification is-success\" v-if=\"strings.notice_just_activated.length > 0\"\r\n\t\t\t\t\t\t\t\t\t     v-html=\"strings.notice_just_activated\"></div>\r\n\t\t\t\t\t\t\t\t\t<api-key-form></api-key-form>\r\n\t\t\t\t\t\t\t\t\t<cdn-details v-if=\"this.$store.state.userData\"></cdn-details>\r\n\t\t\t\t\t\t\t\t\t<hr/>\r\n\t\t\t\t\t\t\t\t\t<last-images :status=\"fetchStatus\" v-if=\"! remove_images\"></last-images>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<div class=\"is-tab\" v-if=\" tab === 'settings'\">\r\n\t\t\t\t\t\t\t\t\t<options></options>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<div class=\"is-tab\" v-if=\" tab === 'watermarks'\">\r\n\t\t\t\t\t\t\t\t\t<watermarks></watermarks>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t</transition>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t\r\n\t\t\t\t<div class=\"level-right\">\r\n\t\t\t\t\t<p class=\"level-item\"><a href=\"https://optimole.com\" target=\"_blank\">Optimole\r\n\t\t\t\t\t\tv{{strings.version}}</a></p>\r\n\t\t\t\t\t<p class=\"level-item\"><a href=\"https://optimole.com/terms/\"\r\n\t\t\t\t\t                         target=\"_blank\">{{strings.terms_menu}}</a></p>\r\n\t\t\t\t\t<p class=\"level-item\"><a href=\"https://optimole.com/privacy-policy/\" target=\"_blank\">{{strings.privacy_menu}}</a>\r\n\t\t\t\t\t</p>\r\n\t\t\t\t\t<p class=\"level-item\"><a :href=\"'https://speedtest.optimole.com/?url=' + home \" target=\"_blank\">{{strings.testdrive_menu}}</a>\r\n\t\t\t\t\t</p>\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t</div>\r\n\t\t<div v-if=\"this.$store.state.connected && this.$store.state.userData.plan === 'free' \" class=\"column is-narrow is-hidden-desktop-only is-hidden-tablet-only is-hidden-mobile\">\r\n\t\t\t<div class=\"card optml-upgrade\">\r\n\t\t\t\t<div class=\"card-header\">\r\n\t\t\t\t\t<h3 class=\"is-size-5 card-header-title\"><span class=\"dashicons dashicons-chart-line\"></span>  {{strings.upgrade.title}}</h3>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"card-content\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li><span class=\"dashicons dashicons-yes\"></span>{{strings.upgrade.reason_1}}</li>\r\n\t\t\t\t\t\t<li><span class=\"dashicons dashicons-yes\"></span>{{strings.upgrade.reason_2}}</li>\r\n\t\t\t\t\t\t<li><span class=\"dashicons dashicons-yes\"></span>{{strings.upgrade.reason_3}}</li>\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"card-footer  \">\r\n\t\t\t\t\t<div class=\"card-footer-item\">\r\n\t\t\t\t\t<a href=\"https://optimole.com#pricing\" target=\"_blank\" class=\"button is-centered is-small is-success\"><span class=\"dashicons dashicons-external\"></span>{{strings.upgrade.cta}}</a>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t</div>\r\n\t</div>\r\n";

/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _vue = __webpack_require__(3);

var _vue2 = _interopRequireDefault(_vue);

var _vuex = __webpack_require__(54);

var _vuex2 = _interopRequireDefault(_vuex);

var _vueResource = __webpack_require__(6);

var _vueResource2 = _interopRequireDefault(_vueResource);

var _mutations = __webpack_require__(56);

var _mutations2 = _interopRequireDefault(_mutations);

var _actions = __webpack_require__(57);

var _actions2 = _interopRequireDefault(_actions);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vuex2.default); /* global optimoleDashboardApp */
/*jshint esversion: 6 */

_vue2.default.use(_vueResource2.default);

var store = new _vuex2.default.Store({
	strict: true,
	state: {
		isConnecting: false,
		loading: false,
		site_settings: optimoleDashboardApp.site_settings,
		connected: optimoleDashboardApp.connection_status === 'yes',
		apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
		apiKeyValidity: true,
		sample_rate: {},
		apiError: false,
		userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
		optimizedImages: [],
		watermarks: []
	},
	mutations: _mutations2.default,
	actions: _actions2.default
});

exports.default = store;

/***/ }),
/* 54 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(process) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Store", function() { return Store; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "install", function() { return install; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapState", function() { return mapState; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapMutations", function() { return mapMutations; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapGetters", function() { return mapGetters; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapActions", function() { return mapActions; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createNamespacedHelpers", function() { return createNamespacedHelpers; });
/**
 * vuex v2.5.0
 * (c) 2017 Evan You
 * @license MIT
 */
var applyMixin = function (Vue) {
  var version = Number(Vue.version.split('.')[0]);

  if (version >= 2) {
    Vue.mixin({ beforeCreate: vuexInit });
  } else {
    // override init and inject vuex init procedure
    // for 1.x backwards compatibility.
    var _init = Vue.prototype._init;
    Vue.prototype._init = function (options) {
      if ( options === void 0 ) options = {};

      options.init = options.init
        ? [vuexInit].concat(options.init)
        : vuexInit;
      _init.call(this, options);
    };
  }

  /**
   * Vuex init hook, injected into each instances init hooks list.
   */

  function vuexInit () {
    var options = this.$options;
    // store injection
    if (options.store) {
      this.$store = typeof options.store === 'function'
        ? options.store()
        : options.store;
    } else if (options.parent && options.parent.$store) {
      this.$store = options.parent.$store;
    }
  }
};

var devtoolHook =
  typeof window !== 'undefined' &&
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

function devtoolPlugin (store) {
  if (!devtoolHook) { return }

  store._devtoolHook = devtoolHook;

  devtoolHook.emit('vuex:init', store);

  devtoolHook.on('vuex:travel-to-state', function (targetState) {
    store.replaceState(targetState);
  });

  store.subscribe(function (mutation, state) {
    devtoolHook.emit('vuex:mutation', mutation, state);
  });
}

/**
 * Get the first item that pass the test
 * by second argument function
 *
 * @param {Array} list
 * @param {Function} f
 * @return {*}
 */
/**
 * Deep copy the given object considering circular structure.
 * This function caches all nested objects and its copies.
 * If it detects circular structure, use cached copy to avoid infinite loop.
 *
 * @param {*} obj
 * @param {Array<Object>} cache
 * @return {*}
 */


/**
 * forEach for object
 */
function forEachValue (obj, fn) {
  Object.keys(obj).forEach(function (key) { return fn(obj[key], key); });
}

function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

function isPromise (val) {
  return val && typeof val.then === 'function'
}

function assert (condition, msg) {
  if (!condition) { throw new Error(("[vuex] " + msg)) }
}

var Module = function Module (rawModule, runtime) {
  this.runtime = runtime;
  this._children = Object.create(null);
  this._rawModule = rawModule;
  var rawState = rawModule.state;
  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
};

var prototypeAccessors$1 = { namespaced: { configurable: true } };

prototypeAccessors$1.namespaced.get = function () {
  return !!this._rawModule.namespaced
};

Module.prototype.addChild = function addChild (key, module) {
  this._children[key] = module;
};

Module.prototype.removeChild = function removeChild (key) {
  delete this._children[key];
};

Module.prototype.getChild = function getChild (key) {
  return this._children[key]
};

Module.prototype.update = function update (rawModule) {
  this._rawModule.namespaced = rawModule.namespaced;
  if (rawModule.actions) {
    this._rawModule.actions = rawModule.actions;
  }
  if (rawModule.mutations) {
    this._rawModule.mutations = rawModule.mutations;
  }
  if (rawModule.getters) {
    this._rawModule.getters = rawModule.getters;
  }
};

Module.prototype.forEachChild = function forEachChild (fn) {
  forEachValue(this._children, fn);
};

Module.prototype.forEachGetter = function forEachGetter (fn) {
  if (this._rawModule.getters) {
    forEachValue(this._rawModule.getters, fn);
  }
};

Module.prototype.forEachAction = function forEachAction (fn) {
  if (this._rawModule.actions) {
    forEachValue(this._rawModule.actions, fn);
  }
};

Module.prototype.forEachMutation = function forEachMutation (fn) {
  if (this._rawModule.mutations) {
    forEachValue(this._rawModule.mutations, fn);
  }
};

Object.defineProperties( Module.prototype, prototypeAccessors$1 );

var ModuleCollection = function ModuleCollection (rawRootModule) {
  // register root module (Vuex.Store options)
  this.register([], rawRootModule, false);
};

ModuleCollection.prototype.get = function get (path) {
  return path.reduce(function (module, key) {
    return module.getChild(key)
  }, this.root)
};

ModuleCollection.prototype.getNamespace = function getNamespace (path) {
  var module = this.root;
  return path.reduce(function (namespace, key) {
    module = module.getChild(key);
    return namespace + (module.namespaced ? key + '/' : '')
  }, '')
};

ModuleCollection.prototype.update = function update$1 (rawRootModule) {
  update([], this.root, rawRootModule);
};

ModuleCollection.prototype.register = function register (path, rawModule, runtime) {
    var this$1 = this;
    if ( runtime === void 0 ) runtime = true;

  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, rawModule);
  }

  var newModule = new Module(rawModule, runtime);
  if (path.length === 0) {
    this.root = newModule;
  } else {
    var parent = this.get(path.slice(0, -1));
    parent.addChild(path[path.length - 1], newModule);
  }

  // register nested modules
  if (rawModule.modules) {
    forEachValue(rawModule.modules, function (rawChildModule, key) {
      this$1.register(path.concat(key), rawChildModule, runtime);
    });
  }
};

ModuleCollection.prototype.unregister = function unregister (path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  if (!parent.getChild(key).runtime) { return }

  parent.removeChild(key);
};

function update (path, targetModule, newModule) {
  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, newModule);
  }

  // update target module
  targetModule.update(newModule);

  // update nested modules
  if (newModule.modules) {
    for (var key in newModule.modules) {
      if (!targetModule.getChild(key)) {
        if (process.env.NODE_ENV !== 'production') {
          console.warn(
            "[vuex] trying to add a new module '" + key + "' on hot reloading, " +
            'manual reload is needed'
          );
        }
        return
      }
      update(
        path.concat(key),
        targetModule.getChild(key),
        newModule.modules[key]
      );
    }
  }
}

var functionAssert = {
  assert: function (value) { return typeof value === 'function'; },
  expected: 'function'
};

var objectAssert = {
  assert: function (value) { return typeof value === 'function' ||
    (typeof value === 'object' && typeof value.handler === 'function'); },
  expected: 'function or object with "handler" function'
};

var assertTypes = {
  getters: functionAssert,
  mutations: functionAssert,
  actions: objectAssert
};

function assertRawModule (path, rawModule) {
  Object.keys(assertTypes).forEach(function (key) {
    if (!rawModule[key]) { return }

    var assertOptions = assertTypes[key];

    forEachValue(rawModule[key], function (value, type) {
      assert(
        assertOptions.assert(value),
        makeAssertionMessage(path, key, type, value, assertOptions.expected)
      );
    });
  });
}

function makeAssertionMessage (path, key, type, value, expected) {
  var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";
  if (path.length > 0) {
    buf += " in module \"" + (path.join('.')) + "\"";
  }
  buf += " is " + (JSON.stringify(value)) + ".";
  return buf
}

var Vue; // bind on install

var Store = function Store (options) {
  var this$1 = this;
  if ( options === void 0 ) options = {};

  // Auto install if it is not done yet and `window` has `Vue`.
  // To allow users to avoid auto-installation in some cases,
  // this code should be placed here. See #731
  if (!Vue && typeof window !== 'undefined' && window.Vue) {
    install(window.Vue);
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(Vue, "must call Vue.use(Vuex) before creating a store instance.");
    assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
    assert(this instanceof Store, "Store must be called with the new operator.");
  }

  var plugins = options.plugins; if ( plugins === void 0 ) plugins = [];
  var strict = options.strict; if ( strict === void 0 ) strict = false;

  var state = options.state; if ( state === void 0 ) state = {};
  if (typeof state === 'function') {
    state = state() || {};
  }

  // store internal state
  this._committing = false;
  this._actions = Object.create(null);
  this._actionSubscribers = [];
  this._mutations = Object.create(null);
  this._wrappedGetters = Object.create(null);
  this._modules = new ModuleCollection(options);
  this._modulesNamespaceMap = Object.create(null);
  this._subscribers = [];
  this._watcherVM = new Vue();

  // bind commit and dispatch to self
  var store = this;
  var ref = this;
  var dispatch = ref.dispatch;
  var commit = ref.commit;
  this.dispatch = function boundDispatch (type, payload) {
    return dispatch.call(store, type, payload)
  };
  this.commit = function boundCommit (type, payload, options) {
    return commit.call(store, type, payload, options)
  };

  // strict mode
  this.strict = strict;

  // init root module.
  // this also recursively registers all sub-modules
  // and collects all module getters inside this._wrappedGetters
  installModule(this, state, [], this._modules.root);

  // initialize the store vm, which is responsible for the reactivity
  // (also registers _wrappedGetters as computed properties)
  resetStoreVM(this, state);

  // apply plugins
  plugins.forEach(function (plugin) { return plugin(this$1); });

  if (Vue.config.devtools) {
    devtoolPlugin(this);
  }
};

var prototypeAccessors = { state: { configurable: true } };

prototypeAccessors.state.get = function () {
  return this._vm._data.$$state
};

prototypeAccessors.state.set = function (v) {
  if (process.env.NODE_ENV !== 'production') {
    assert(false, "Use store.replaceState() to explicit replace store state.");
  }
};

Store.prototype.commit = function commit (_type, _payload, _options) {
    var this$1 = this;

  // check object-style commit
  var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var options = ref.options;

  var mutation = { type: type, payload: payload };
  var entry = this._mutations[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown mutation type: " + type));
    }
    return
  }
  this._withCommit(function () {
    entry.forEach(function commitIterator (handler) {
      handler(payload);
    });
  });
  this._subscribers.forEach(function (sub) { return sub(mutation, this$1.state); });

  if (
    process.env.NODE_ENV !== 'production' &&
    options && options.silent
  ) {
    console.warn(
      "[vuex] mutation type: " + type + ". Silent option has been removed. " +
      'Use the filter functionality in the vue-devtools'
    );
  }
};

Store.prototype.dispatch = function dispatch (_type, _payload) {
    var this$1 = this;

  // check object-style dispatch
  var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;

  var action = { type: type, payload: payload };
  var entry = this._actions[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown action type: " + type));
    }
    return
  }

  this._actionSubscribers.forEach(function (sub) { return sub(action, this$1.state); });

  return entry.length > 1
    ? Promise.all(entry.map(function (handler) { return handler(payload); }))
    : entry[0](payload)
};

Store.prototype.subscribe = function subscribe (fn) {
  return genericSubscribe(fn, this._subscribers)
};

Store.prototype.subscribeAction = function subscribeAction (fn) {
  return genericSubscribe(fn, this._actionSubscribers)
};

Store.prototype.watch = function watch (getter, cb, options) {
    var this$1 = this;

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof getter === 'function', "store.watch only accepts a function.");
  }
  return this._watcherVM.$watch(function () { return getter(this$1.state, this$1.getters); }, cb, options)
};

Store.prototype.replaceState = function replaceState (state) {
    var this$1 = this;

  this._withCommit(function () {
    this$1._vm._data.$$state = state;
  });
};

Store.prototype.registerModule = function registerModule (path, rawModule, options) {
    if ( options === void 0 ) options = {};

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
    assert(path.length > 0, 'cannot register the root module by using registerModule.');
  }

  this._modules.register(path, rawModule);
  installModule(this, this.state, path, this._modules.get(path), options.preserveState);
  // reset store to update getters...
  resetStoreVM(this, this.state);
};

Store.prototype.unregisterModule = function unregisterModule (path) {
    var this$1 = this;

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
  }

  this._modules.unregister(path);
  this._withCommit(function () {
    var parentState = getNestedState(this$1.state, path.slice(0, -1));
    Vue.delete(parentState, path[path.length - 1]);
  });
  resetStore(this);
};

Store.prototype.hotUpdate = function hotUpdate (newOptions) {
  this._modules.update(newOptions);
  resetStore(this, true);
};

Store.prototype._withCommit = function _withCommit (fn) {
  var committing = this._committing;
  this._committing = true;
  fn();
  this._committing = committing;
};

Object.defineProperties( Store.prototype, prototypeAccessors );

function genericSubscribe (fn, subs) {
  if (subs.indexOf(fn) < 0) {
    subs.push(fn);
  }
  return function () {
    var i = subs.indexOf(fn);
    if (i > -1) {
      subs.splice(i, 1);
    }
  }
}

function resetStore (store, hot) {
  store._actions = Object.create(null);
  store._mutations = Object.create(null);
  store._wrappedGetters = Object.create(null);
  store._modulesNamespaceMap = Object.create(null);
  var state = store.state;
  // init all modules
  installModule(store, state, [], store._modules.root, true);
  // reset vm
  resetStoreVM(store, state, hot);
}

function resetStoreVM (store, state, hot) {
  var oldVm = store._vm;

  // bind store public getters
  store.getters = {};
  var wrappedGetters = store._wrappedGetters;
  var computed = {};
  forEachValue(wrappedGetters, function (fn, key) {
    // use computed to leverage its lazy-caching mechanism
    computed[key] = function () { return fn(store); };
    Object.defineProperty(store.getters, key, {
      get: function () { return store._vm[key]; },
      enumerable: true // for local getters
    });
  });

  // use a Vue instance to store the state tree
  // suppress warnings just in case the user has added
  // some funky global mixins
  var silent = Vue.config.silent;
  Vue.config.silent = true;
  store._vm = new Vue({
    data: {
      $$state: state
    },
    computed: computed
  });
  Vue.config.silent = silent;

  // enable strict mode for new vm
  if (store.strict) {
    enableStrictMode(store);
  }

  if (oldVm) {
    if (hot) {
      // dispatch changes in all subscribed watchers
      // to force getter re-evaluation for hot reloading.
      store._withCommit(function () {
        oldVm._data.$$state = null;
      });
    }
    Vue.nextTick(function () { return oldVm.$destroy(); });
  }
}

function installModule (store, rootState, path, module, hot) {
  var isRoot = !path.length;
  var namespace = store._modules.getNamespace(path);

  // register in namespace map
  if (module.namespaced) {
    store._modulesNamespaceMap[namespace] = module;
  }

  // set state
  if (!isRoot && !hot) {
    var parentState = getNestedState(rootState, path.slice(0, -1));
    var moduleName = path[path.length - 1];
    store._withCommit(function () {
      Vue.set(parentState, moduleName, module.state);
    });
  }

  var local = module.context = makeLocalContext(store, namespace, path);

  module.forEachMutation(function (mutation, key) {
    var namespacedType = namespace + key;
    registerMutation(store, namespacedType, mutation, local);
  });

  module.forEachAction(function (action, key) {
    var type = action.root ? key : namespace + key;
    var handler = action.handler || action;
    registerAction(store, type, handler, local);
  });

  module.forEachGetter(function (getter, key) {
    var namespacedType = namespace + key;
    registerGetter(store, namespacedType, getter, local);
  });

  module.forEachChild(function (child, key) {
    installModule(store, rootState, path.concat(key), child, hot);
  });
}

/**
 * make localized dispatch, commit, getters and state
 * if there is no namespace, just use root ones
 */
function makeLocalContext (store, namespace, path) {
  var noNamespace = namespace === '';

  var local = {
    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._actions[type]) {
          console.error(("[vuex] unknown local action type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      return store.dispatch(type, payload)
    },

    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._mutations[type]) {
          console.error(("[vuex] unknown local mutation type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      store.commit(type, payload, options);
    }
  };

  // getters and state object must be gotten lazily
  // because they will be changed by vm update
  Object.defineProperties(local, {
    getters: {
      get: noNamespace
        ? function () { return store.getters; }
        : function () { return makeLocalGetters(store, namespace); }
    },
    state: {
      get: function () { return getNestedState(store.state, path); }
    }
  });

  return local
}

function makeLocalGetters (store, namespace) {
  var gettersProxy = {};

  var splitPos = namespace.length;
  Object.keys(store.getters).forEach(function (type) {
    // skip if the target getter is not match this namespace
    if (type.slice(0, splitPos) !== namespace) { return }

    // extract local getter type
    var localType = type.slice(splitPos);

    // Add a port to the getters proxy.
    // Define as getter property because
    // we do not want to evaluate the getters in this time.
    Object.defineProperty(gettersProxy, localType, {
      get: function () { return store.getters[type]; },
      enumerable: true
    });
  });

  return gettersProxy
}

function registerMutation (store, type, handler, local) {
  var entry = store._mutations[type] || (store._mutations[type] = []);
  entry.push(function wrappedMutationHandler (payload) {
    handler.call(store, local.state, payload);
  });
}

function registerAction (store, type, handler, local) {
  var entry = store._actions[type] || (store._actions[type] = []);
  entry.push(function wrappedActionHandler (payload, cb) {
    var res = handler.call(store, {
      dispatch: local.dispatch,
      commit: local.commit,
      getters: local.getters,
      state: local.state,
      rootGetters: store.getters,
      rootState: store.state
    }, payload, cb);
    if (!isPromise(res)) {
      res = Promise.resolve(res);
    }
    if (store._devtoolHook) {
      return res.catch(function (err) {
        store._devtoolHook.emit('vuex:error', err);
        throw err
      })
    } else {
      return res
    }
  });
}

function registerGetter (store, type, rawGetter, local) {
  if (store._wrappedGetters[type]) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] duplicate getter key: " + type));
    }
    return
  }
  store._wrappedGetters[type] = function wrappedGetter (store) {
    return rawGetter(
      local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
    )
  };
}

function enableStrictMode (store) {
  store._vm.$watch(function () { return this._data.$$state }, function () {
    if (process.env.NODE_ENV !== 'production') {
      assert(store._committing, "Do not mutate vuex store state outside mutation handlers.");
    }
  }, { deep: true, sync: true });
}

function getNestedState (state, path) {
  return path.length
    ? path.reduce(function (state, key) { return state[key]; }, state)
    : state
}

function unifyObjectStyle (type, payload, options) {
  if (isObject(type) && type.type) {
    options = payload;
    payload = type;
    type = type.type;
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof type === 'string', ("Expects string as the type, but found " + (typeof type) + "."));
  }

  return { type: type, payload: payload, options: options }
}

function install (_Vue) {
  if (Vue && _Vue === Vue) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(
        '[vuex] already installed. Vue.use(Vuex) should be called only once.'
      );
    }
    return
  }
  Vue = _Vue;
  applyMixin(Vue);
}

var mapState = normalizeNamespace(function (namespace, states) {
  var res = {};
  normalizeMap(states).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedState () {
      var state = this.$store.state;
      var getters = this.$store.getters;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapState', namespace);
        if (!module) {
          return
        }
        state = module.context.state;
        getters = module.context.getters;
      }
      return typeof val === 'function'
        ? val.call(this, state, getters)
        : state[val]
    };
    // mark vuex getter for devtools
    res[key].vuex = true;
  });
  return res
});

var mapMutations = normalizeNamespace(function (namespace, mutations) {
  var res = {};
  normalizeMap(mutations).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedMutation () {
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      var commit = this.$store.commit;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);
        if (!module) {
          return
        }
        commit = module.context.commit;
      }
      return typeof val === 'function'
        ? val.apply(this, [commit].concat(args))
        : commit.apply(this.$store, [val].concat(args))
    };
  });
  return res
});

var mapGetters = normalizeNamespace(function (namespace, getters) {
  var res = {};
  normalizeMap(getters).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    val = namespace + val;
    res[key] = function mappedGetter () {
      if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
        return
      }
      if (process.env.NODE_ENV !== 'production' && !(val in this.$store.getters)) {
        console.error(("[vuex] unknown getter: " + val));
        return
      }
      return this.$store.getters[val]
    };
    // mark vuex getter for devtools
    res[key].vuex = true;
  });
  return res
});

var mapActions = normalizeNamespace(function (namespace, actions) {
  var res = {};
  normalizeMap(actions).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedAction () {
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      var dispatch = this.$store.dispatch;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapActions', namespace);
        if (!module) {
          return
        }
        dispatch = module.context.dispatch;
      }
      return typeof val === 'function'
        ? val.apply(this, [dispatch].concat(args))
        : dispatch.apply(this.$store, [val].concat(args))
    };
  });
  return res
});

var createNamespacedHelpers = function (namespace) { return ({
  mapState: mapState.bind(null, namespace),
  mapGetters: mapGetters.bind(null, namespace),
  mapMutations: mapMutations.bind(null, namespace),
  mapActions: mapActions.bind(null, namespace)
}); };

function normalizeMap (map) {
  return Array.isArray(map)
    ? map.map(function (key) { return ({ key: key, val: key }); })
    : Object.keys(map).map(function (key) { return ({ key: key, val: map[key] }); })
}

function normalizeNamespace (fn) {
  return function (namespace, map) {
    if (typeof namespace !== 'string') {
      map = namespace;
      namespace = '';
    } else if (namespace.charAt(namespace.length - 1) !== '/') {
      namespace += '/';
    }
    return fn(namespace, map)
  }
}

function getModuleByNamespace (store, helper, namespace) {
  var module = store._modulesNamespaceMap[namespace];
  if (process.env.NODE_ENV !== 'production' && !module) {
    console.error(("[vuex] module namespace not found in " + helper + "(): " + namespace));
  }
  return module
}

var index_esm = {
  Store: Store,
  install: install,
  version: '2.5.0',
  mapState: mapState,
  mapMutations: mapMutations,
  mapGetters: mapGetters,
  mapActions: mapActions,
  createNamespacedHelpers: createNamespacedHelpers
};


/* harmony default export */ __webpack_exports__["default"] = (index_esm);

/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(4)))

/***/ }),
/* 55 */
/***/ (function(module, exports) {

/* (ignored) */

/***/ }),
/* 56 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
/* jshint esversion: 6 */
var toggleLoading = function toggleLoading(state, data) {
	state.loading = data;
};
var toggleConnecting = function toggleConnecting(state, data) {
	state.isConnecting = data;
};
var toggleKeyValidity = function toggleKeyValidity(state, data) {
	state.apiKeyValidity = data;
};
var toggleConnectedToOptml = function toggleConnectedToOptml(state, data) {
	state.connected = data;
};
var updateUserData = function updateUserData(state, data) {
	state.userData = data;
};
var updateApiKey = function updateApiKey(state, data) {
	state.apiKey = data;
};
var updateOptimizedImages = function updateOptimizedImages(state, data) {
	state.optimizedImages = data.body.data;
};
var updateSampleRate = function updateSampleRate(state, data) {
	state.sample_rate = data;
};
var restApiNotWorking = function restApiNotWorking(state, data) {
	state.apiError = data;
};
var updateSettings = function updateSettings(state, data) {

	for (var setting in data) {
		state.site_settings[setting] = data[setting];
	}
};

var updateWatermark = function updateWatermark(state, data) {

	for (var key in data) {
		state.site_settings.watermark[key] = data[key];
	}
};

exports.default = {
	toggleLoading: toggleLoading,
	toggleConnecting: toggleConnecting,
	toggleKeyValidity: toggleKeyValidity,
	toggleConnectedToOptml: toggleConnectedToOptml,
	updateUserData: updateUserData,
	updateApiKey: updateApiKey,
	updateSampleRate: updateSampleRate,
	restApiNotWorking: restApiNotWorking,
	updateSettings: updateSettings,
	updateWatermark: updateWatermark,
	updateOptimizedImages: updateOptimizedImages
};

/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _vue = __webpack_require__(3);

var _vue2 = _interopRequireDefault(_vue);

var _vueResource = __webpack_require__(6);

var _vueResource2 = _interopRequireDefault(_vueResource);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/* jshint esversion: 6 */
/* global optimoleDashboardApp */
_vue2.default.use(_vueResource2.default);

var connectOptimole = function connectOptimole(_ref, data) {
	var commit = _ref.commit,
	    state = _ref.state;

	commit('toggleConnecting', true);
	commit('restApiNotWorking', false);
	_vue2.default.http({
		url: optimoleDashboardApp.root + '/connect',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': data.req },
		body: {
			'api_key': data.apiKey
		},
		responseType: 'json',
		emulateJSON: true
	}).then(function (response) {
		commit('toggleConnecting', false);
		if (response.body.code === 'success') {
			commit('toggleKeyValidity', true);
			commit('toggleConnectedToOptml', true);
			commit('updateApiKey', data.apiKey);
			commit('updateUserData', response.body.data);
			console.log('%c OptiMole API connection successful.', 'color: #59B278');
		} else {
			commit('toggleKeyValidity', false);
			console.log('%c Invalid API Key.', 'color: #E7602A');
		}
	}, function () {
		commit('toggleConnecting', false);
		commit('restApiNotWorking', true);
	});
};

var registerOptimole = function registerOptimole(_ref2, data) {
	var commit = _ref2.commit,
	    state = _ref2.state;


	commit('restApiNotWorking', false);
	commit('toggleLoading', true);
	return _vue2.default.http({
		url: optimoleDashboardApp.root + '/register',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': data.req },
		body: {
			'email': data.email
		},
		emulateJSON: true,
		responseType: 'json'
	}).then(function (response) {
		commit('toggleLoading', false);
		return response.data;
	}, function (response) {
		commit('toggleLoading', false);
		commit('restApiNotWorking', true);
		return response.data;
	});
};

var disconnectOptimole = function disconnectOptimole(_ref3, data) {
	var commit = _ref3.commit,
	    state = _ref3.state;

	commit('toggleLoading', true, 'loading');
	_vue2.default.http({
		url: optimoleDashboardApp.root + '/disconnect',
		method: 'GET',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': data.req },
		emulateJSON: true,
		responseType: 'json'
	}).then(function (response) {
		commit('updateUserData', null);
		commit('toggleLoading', false);
		commit('updateApiKey', '');
		if (response.ok) {
			commit('toggleConnectedToOptml', false);
			console.log('%c Disconnected from OptiMole API.', 'color: #59B278');
		} else {
			console.error(response);
		}
	});
};

var saveSettings = function saveSettings(_ref4, data) {
	var commit = _ref4.commit,
	    state = _ref4.state;

	commit('updateSettings', data.settings);
	commit('toggleLoading', true);
	return _vue2.default.http({
		url: optimoleDashboardApp.root + '/update_option',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		emulateJSON: true,
		body: {
			'settings': data.settings
		},
		responseType: 'json'
	}).then(function (response) {
		if (response.body.code === 'success') {
			commit('updateSettings', response.body.data);
		}
		commit('toggleLoading', false);
	});
};
var sampleRate = function sampleRate(_ref5, data) {
	var commit = _ref5.commit,
	    state = _ref5.state;


	data.component.loading_images = true;
	return _vue2.default.http({
		url: optimoleDashboardApp.root + '/images-sample-rate',
		method: 'POST',
		emulateJSON: true,
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: {
			'quality': data.quality,
			'force': data.force
		},
		responseType: 'json'
	}).then(function (response) {

		data.component.loading_images = false;
		if (response.body.code === 'success') {
			commit('updateSampleRate', response.body.data);
		}
	});
};

var retrieveOptimizedImages = function retrieveOptimizedImages(_ref6, data) {
	var commit = _ref6.commit,
	    state = _ref6.state;

	var self = this;

	setTimeout(function () {
		if (self.state.optimizedImages.length > 0) {
			console.log('%c Images already exsist.', 'color: #59B278');
			return false;
		}
		_vue2.default.http({
			url: optimoleDashboardApp.root + '/poll_optimized_images',
			method: 'GET',
			emulateJSON: true,
			headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
			params: { 'req': 'Get Optimized Images' },
			responseType: 'json',
			timeout: 10000
		}).then(function (response) {
			if (response.body.code === 'success') {
				commit('updateOptimizedImages', response);
				if (data.component !== null) {
					data.component.loading = false;
					data.component.startTime = data.component.maxTime;
					if (response.body.data.length === 0) {
						data.component.noImages = true;
					}
				}
				console.log('%c Images Fetched.', 'color: #59B278');
			} else {
				component.noImages = true;
				data.component.loading = false;
				console.log('%c No images available.', 'color: #E7602A');
			}
		});
	}, data.waitTime);
};

var retrieveWatermarks = function retrieveWatermarks(_ref7, data) {
	var commit = _ref7.commit,
	    state = _ref7.state;


	commit('toggleLoading', true);
	_vue2.default.http({
		url: optimoleDashboardApp.root + '/poll_watermarks',
		method: 'GET',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': 'Get Watermarks' },
		responseType: 'json'
	}).then(function (response) {
		commit('toggleLoading', false);
		if (response.status === 200) {
			data.component.watermarkData = [];

			for (var row in response.data.data) {
				var tmp = response.data.data[row];
				var item = {
					ID: tmp.ID,
					post_title: tmp.post_title,
					post_mime_type: tmp.post_mime_type,
					guid: tmp.post_content || tmp.guid
				};
				data.component.watermarkData.push(item);
				data.component.noImages = false;
			}
		}
	});
};

var removeWatermark = function removeWatermark(_ref8, data) {
	var commit = _ref8.commit,
	    state = _ref8.state;

	var self = this;
	commit('toggleLoading', true);
	_vue2.default.http({
		url: optimoleDashboardApp.root + '/remove_watermark',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': 'Get Watermarks', 'postID': data.postID },
		responseType: 'json'
	}).then(function (response) {

		commit('toggleLoading', false);
		retrieveWatermarks({ commit: commit, state: state }, data);
	});
};

exports.default = {
	connectOptimole: connectOptimole,
	registerOptimole: registerOptimole,
	disconnectOptimole: disconnectOptimole,
	saveSettings: saveSettings,
	sampleRate: sampleRate,
	retrieveOptimizedImages: retrieveOptimizedImages,
	retrieveWatermarks: retrieveWatermarks,
	removeWatermark: removeWatermark
};

/***/ }),
/* 58 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(global) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "install", function() { return install; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ResizeObserver", function() { return ResizeObserver; });
function getInternetExplorerVersion() {
	var ua = window.navigator.userAgent;

	var msie = ua.indexOf('MSIE ');
	if (msie > 0) {
		// IE 10 or older => return version number
		return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
	}

	var trident = ua.indexOf('Trident/');
	if (trident > 0) {
		// IE 11 => return version number
		var rv = ua.indexOf('rv:');
		return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
	}

	var edge = ua.indexOf('Edge/');
	if (edge > 0) {
		// Edge (IE 12+) => return version number
		return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
	}

	// other browser
	return -1;
}

var isIE = void 0;

function initCompat() {
	if (!initCompat.init) {
		initCompat.init = true;
		isIE = getInternetExplorerVersion() !== -1;
	}
}

var ResizeObserver = { render: function render() {
		var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;return _c('div', { staticClass: "resize-observer", attrs: { "tabindex": "-1" } });
	}, staticRenderFns: [], _scopeId: 'data-v-b329ee4c',
	name: 'resize-observer',

	methods: {
		compareAndNotify: function compareAndNotify() {
			if (this._w !== this.$el.offsetWidth || this._h !== this.$el.offsetHeight) {
				this._w = this.$el.offsetWidth;
				this._h = this.$el.offsetHeight;
				this.$emit('notify');
			}
		},
		addResizeHandlers: function addResizeHandlers() {
			this._resizeObject.contentDocument.defaultView.addEventListener('resize', this.compareAndNotify);
			this.compareAndNotify();
		},
		removeResizeHandlers: function removeResizeHandlers() {
			if (this._resizeObject && this._resizeObject.onload) {
				if (!isIE && this._resizeObject.contentDocument) {
					this._resizeObject.contentDocument.defaultView.removeEventListener('resize', this.compareAndNotify);
				}
				delete this._resizeObject.onload;
			}
		}
	},

	mounted: function mounted() {
		var _this = this;

		initCompat();
		this.$nextTick(function () {
			_this._w = _this.$el.offsetWidth;
			_this._h = _this.$el.offsetHeight;
		});
		var object = document.createElement('object');
		this._resizeObject = object;
		object.setAttribute('aria-hidden', 'true');
		object.setAttribute('tabindex', -1);
		object.onload = this.addResizeHandlers;
		object.type = 'text/html';
		if (isIE) {
			this.$el.appendChild(object);
		}
		object.data = 'about:blank';
		if (!isIE) {
			this.$el.appendChild(object);
		}
	},
	beforeDestroy: function beforeDestroy() {
		this.removeResizeHandlers();
	}
};

// Install the components
function install(Vue) {
	Vue.component('resize-observer', ResizeObserver);
	Vue.component('ResizeObserver', ResizeObserver);
}

// Plugin
var plugin = {
	// eslint-disable-next-line no-undef
	version: "0.4.5",
	install: install
};

// Auto-install
var GlobalVue = null;
if (typeof window !== 'undefined') {
	GlobalVue = window.Vue;
} else if (typeof global !== 'undefined') {
	GlobalVue = global.Vue;
}
if (GlobalVue) {
	GlobalVue.use(plugin);
}


/* harmony default export */ __webpack_exports__["default"] = (plugin);

/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(2)))

/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

(function webpackUniversalModuleDefinition(root, factory) {
	if(true)
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["vue-js-toggle-button"] = factory();
	else
		root["vue-js-toggle-button"] = factory();
})(this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/dist/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {


/* styles */
__webpack_require__(7)

var Component = __webpack_require__(5)(
  /* script */
  __webpack_require__(1),
  /* template */
  __webpack_require__(6),
  /* scopeId */
  "data-v-25adc6c0",
  /* cssModules */
  null
)

module.exports = Component.exports


/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

var DEFAULT_COLOR_CHECKED = '#75c791';
var DEFAULT_COLOR_UNCHECKED = '#bfcbd9';
var DEFAULT_LABEL_CHECKED = 'on';
var DEFAULT_LABEL_UNCHECKED = 'off';
var DEFAULT_SWITCH_COLOR = '#fff';
var MARGIN = 3;

var contains = function contains(object, title) {
  return (typeof object === 'undefined' ? 'undefined' : _typeof(object)) === 'object' && object.hasOwnProperty(title);
};

var px = function px(v) {
  return v + 'px';
};

/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'ToggleButton',
  props: {
    value: {
      type: Boolean,
      default: false
    },
    name: {
      type: String
    },
    disabled: {
      type: Boolean,
      default: false
    },
    sync: {
      type: Boolean,
      default: false
    },
    speed: {
      type: Number,
      default: 300
    },
    color: {
      type: [String, Object],
      validator: function validator(value) {
        return (typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' ? value.checked || value.unchecked || value.disabled : typeof value === 'string';
      }
    },
    switchColor: {
      type: [String, Object],
      validator: function validator(value) {
        return (typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' ? value.checked || value.unchecked : typeof value === 'string';
      }
    },
    cssColors: {
      type: Boolean,
      default: false
    },
    labels: {
      type: [Boolean, Object],
      default: false,
      validator: function validator(value) {
        return (typeof value === 'undefined' ? 'undefined' : _typeof(value)) === 'object' ? value.checked || value.unchecked : typeof value === 'boolean';
      }
    },
    height: {
      type: Number,
      default: 22
    },
    width: {
      type: Number,
      default: 50
    },
    fontSize: {
      type: Number
    }
  },
  computed: {
    className: function className() {
      var toggled = this.toggled,
          disabled = this.disabled;


      return ['vue-js-switch', { toggled: toggled, disabled: disabled }];
    },
    coreStyle: function coreStyle() {
      return {
        width: px(this.width),
        height: px(this.height),
        backgroundColor: this.cssColors ? null : this.disabled ? this.colorDisabled : this.colorCurrent,
        borderRadius: px(Math.round(this.height / 2))
      };
    },
    buttonRadius: function buttonRadius() {
      return this.height - MARGIN * 2;
    },
    distance: function distance() {
      return px(this.width - this.height + MARGIN);
    },
    buttonStyle: function buttonStyle() {
      var transition = 'transform ' + this.speed + 'ms';

      var transform = this.toggled ? 'translate3d(' + this.distance + ', 3px, 0px)' : null;

      var background = this.switchColor ? this.switchColorCurrent : null;

      return {
        width: px(this.buttonRadius),
        height: px(this.buttonRadius),
        transition: transition,
        transform: transform,
        background: background
      };
    },
    labelStyle: function labelStyle() {
      return {
        lineHeight: px(this.height),
        fontSize: this.fontSize ? px(this.fontSize) : null
      };
    },
    colorChecked: function colorChecked() {
      var color = this.color;


      if ((typeof color === 'undefined' ? 'undefined' : _typeof(color)) !== 'object') {
        return color || DEFAULT_COLOR_CHECKED;
      }

      return contains(color, 'checked') ? color.checked : DEFAULT_COLOR_CHECKED;
    },
    colorUnchecked: function colorUnchecked() {
      var color = this.color;


      return contains(color, 'unchecked') ? color.unchecked : DEFAULT_COLOR_UNCHECKED;
    },
    colorDisabled: function colorDisabled() {
      var color = this.color;


      return contains(color, 'disabled') ? color.disabled : this.colorCurrent;
    },
    colorCurrent: function colorCurrent() {
      return this.toggled ? this.colorChecked : this.colorUnchecked;
    },
    labelChecked: function labelChecked() {
      var labels = this.labels;


      return contains(labels, 'checked') ? labels.checked : DEFAULT_LABEL_CHECKED;
    },
    labelUnchecked: function labelUnchecked() {
      var labels = this.labels;


      return contains(labels, 'unchecked') ? labels.unchecked : DEFAULT_LABEL_UNCHECKED;
    },
    switchColorChecked: function switchColorChecked() {
      var switchColor = this.switchColor;


      return contains(switchColor, 'checked') ? switchColor.checked : DEFAULT_SWITCH_COLOR;
    },
    switchColorUnchecked: function switchColorUnchecked() {
      var switchColor = this.switchColor;


      return contains(switchColor, 'unchecked') ? switchColor.unchecked : DEFAULT_SWITCH_COLOR;
    },
    switchColorCurrent: function switchColorCurrent() {
      var switchColor = this.switchColor;


      if ((typeof switchColor === 'undefined' ? 'undefined' : _typeof(switchColor)) !== 'object') {
        return switchColor || DEFAULT_SWITCH_COLOR;
      }

      return this.toggled ? this.switchColorChecked : this.switchColorUnchecked;
    }
  },
  watch: {
    value: function value(_value) {
      if (this.sync) {
        this.toggled = !!_value;
      }
    }
  },
  data: function data() {
    return {
      toggled: !!this.value
    };
  },

  methods: {
    toggle: function toggle(event) {
      this.toggled = !this.toggled;
      this.$emit('input', this.toggled);
      this.$emit('change', {
        value: this.toggled,
        srcEvent: event
      });
    }
  }
});

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Button_vue__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Button_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__Button_vue__);
/* harmony reexport (default from non-hamory) */ __webpack_require__.d(__webpack_exports__, "ToggleButton", function() { return __WEBPACK_IMPORTED_MODULE_0__Button_vue___default.a; });


var installed = false;

/* harmony default export */ __webpack_exports__["default"] = ({
  install: function install(Vue) {
    if (installed) {
      return;
    }

    Vue.component('ToggleButton', __WEBPACK_IMPORTED_MODULE_0__Button_vue___default.a);
    installed = true;
  }
});



/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(4)();
// imports


// module
exports.push([module.i, ".vue-js-switch[data-v-25adc6c0]{display:inline-block;position:relative;overflow:hidden;vertical-align:middle;user-select:none;font-size:10px;cursor:pointer}.vue-js-switch .v-switch-input[data-v-25adc6c0]{opacity:0;position:absolute;width:1px;height:1px}.vue-js-switch .v-switch-label[data-v-25adc6c0]{position:absolute;top:0;font-weight:600;color:#fff;z-index:1}.vue-js-switch .v-switch-label.v-left[data-v-25adc6c0]{left:10px}.vue-js-switch .v-switch-label.v-right[data-v-25adc6c0]{right:10px}.vue-js-switch .v-switch-core[data-v-25adc6c0]{display:block;position:relative;box-sizing:border-box;outline:0;margin:0;transition:border-color .3s,background-color .3s;user-select:none}.vue-js-switch .v-switch-core .v-switch-button[data-v-25adc6c0]{display:block;position:absolute;overflow:hidden;top:0;left:0;transform:translate3d(3px,3px,0);border-radius:100%;background-color:#fff;z-index:2}.vue-js-switch.disabled[data-v-25adc6c0]{pointer-events:none;opacity:.6}", ""]);

// exports


/***/ }),
/* 4 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function() {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		var result = [];
		for(var i = 0; i < this.length; i++) {
			var item = this[i];
			if(item[2]) {
				result.push("@media " + item[2] + "{" + item[1] + "}");
			} else {
				result.push(item[1]);
			}
		}
		return result.join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};


/***/ }),
/* 5 */
/***/ (function(module, exports) {

// this module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  scopeId,
  cssModules
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  // inject cssModules
  if (cssModules) {
    var computed = Object.create(options.computed || null)
    Object.keys(cssModules).forEach(function (key) {
      var module = cssModules[key]
      computed[key] = function () { return module }
    })
    options.computed = computed
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 6 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('label', {
    class: _vm.className
  }, [_c('input', {
    staticClass: "v-switch-input",
    attrs: {
      "type": "checkbox",
      "name": _vm.name
    },
    domProps: {
      "checked": _vm.value
    },
    on: {
      "change": function($event) {
        $event.stopPropagation();
        return _vm.toggle($event)
      }
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "v-switch-core",
    style: (_vm.coreStyle)
  }, [_c('div', {
    staticClass: "v-switch-button",
    style: (_vm.buttonStyle)
  })]), _vm._v(" "), (_vm.labels) ? [(_vm.toggled) ? _c('span', {
    staticClass: "v-switch-label v-left",
    style: (_vm.labelStyle)
  }, [_vm._t("checked", [
    [_vm._v(_vm._s(_vm.labelChecked))]
  ])], 2) : _c('span', {
    staticClass: "v-switch-label v-right",
    style: (_vm.labelStyle)
  }, [_vm._t("unchecked", [
    [_vm._v(_vm._s(_vm.labelUnchecked))]
  ])], 2)] : _vm._e()], 2)
},staticRenderFns: []}

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(3);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(8)("2283861f", content, true);

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
  Modified by Evan You @yyx990803
*/

var hasDocument = typeof document !== 'undefined'

if (typeof DEBUG !== 'undefined' && DEBUG) {
  if (!hasDocument) {
    throw new Error(
    'vue-style-loader cannot be used in a non-browser environment. ' +
    "Use { target: 'node' } in your Webpack config to indicate a server-rendering environment."
  ) }
}

var listToStyles = __webpack_require__(9)

/*
type StyleObject = {
  id: number;
  parts: Array<StyleObjectPart>
}

type StyleObjectPart = {
  css: string;
  media: string;
  sourceMap: ?string
}
*/

var stylesInDom = {/*
  [id: number]: {
    id: number,
    refs: number,
    parts: Array<(obj?: StyleObjectPart) => void>
  }
*/}

var head = hasDocument && (document.head || document.getElementsByTagName('head')[0])
var singletonElement = null
var singletonCounter = 0
var isProduction = false
var noop = function () {}

// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
// tags it will allow on a page
var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase())

module.exports = function (parentId, list, _isProduction) {
  isProduction = _isProduction

  var styles = listToStyles(parentId, list)
  addStylesToDom(styles)

  return function update (newList) {
    var mayRemove = []
    for (var i = 0; i < styles.length; i++) {
      var item = styles[i]
      var domStyle = stylesInDom[item.id]
      domStyle.refs--
      mayRemove.push(domStyle)
    }
    if (newList) {
      styles = listToStyles(parentId, newList)
      addStylesToDom(styles)
    } else {
      styles = []
    }
    for (var i = 0; i < mayRemove.length; i++) {
      var domStyle = mayRemove[i]
      if (domStyle.refs === 0) {
        for (var j = 0; j < domStyle.parts.length; j++) {
          domStyle.parts[j]()
        }
        delete stylesInDom[domStyle.id]
      }
    }
  }
}

function addStylesToDom (styles /* Array<StyleObject> */) {
  for (var i = 0; i < styles.length; i++) {
    var item = styles[i]
    var domStyle = stylesInDom[item.id]
    if (domStyle) {
      domStyle.refs++
      for (var j = 0; j < domStyle.parts.length; j++) {
        domStyle.parts[j](item.parts[j])
      }
      for (; j < item.parts.length; j++) {
        domStyle.parts.push(addStyle(item.parts[j]))
      }
      if (domStyle.parts.length > item.parts.length) {
        domStyle.parts.length = item.parts.length
      }
    } else {
      var parts = []
      for (var j = 0; j < item.parts.length; j++) {
        parts.push(addStyle(item.parts[j]))
      }
      stylesInDom[item.id] = { id: item.id, refs: 1, parts: parts }
    }
  }
}

function createStyleElement () {
  var styleElement = document.createElement('style')
  styleElement.type = 'text/css'
  head.appendChild(styleElement)
  return styleElement
}

function addStyle (obj /* StyleObjectPart */) {
  var update, remove
  var styleElement = document.querySelector('style[data-vue-ssr-id~="' + obj.id + '"]')

  if (styleElement) {
    if (isProduction) {
      // has SSR styles and in production mode.
      // simply do nothing.
      return noop
    } else {
      // has SSR styles but in dev mode.
      // for some reason Chrome can't handle source map in server-rendered
      // style tags - source maps in <style> only works if the style tag is
      // created and inserted dynamically. So we remove the server rendered
      // styles and inject new ones.
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  if (isOldIE) {
    // use singleton mode for IE9.
    var styleIndex = singletonCounter++
    styleElement = singletonElement || (singletonElement = createStyleElement())
    update = applyToSingletonTag.bind(null, styleElement, styleIndex, false)
    remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true)
  } else {
    // use multi-style-tag mode in all other cases
    styleElement = createStyleElement()
    update = applyToTag.bind(null, styleElement)
    remove = function () {
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  update(obj)

  return function updateStyle (newObj /* StyleObjectPart */) {
    if (newObj) {
      if (newObj.css === obj.css &&
          newObj.media === obj.media &&
          newObj.sourceMap === obj.sourceMap) {
        return
      }
      update(obj = newObj)
    } else {
      remove()
    }
  }
}

var replaceText = (function () {
  var textStore = []

  return function (index, replacement) {
    textStore[index] = replacement
    return textStore.filter(Boolean).join('\n')
  }
})()

function applyToSingletonTag (styleElement, index, remove, obj) {
  var css = remove ? '' : obj.css

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = replaceText(index, css)
  } else {
    var cssNode = document.createTextNode(css)
    var childNodes = styleElement.childNodes
    if (childNodes[index]) styleElement.removeChild(childNodes[index])
    if (childNodes.length) {
      styleElement.insertBefore(cssNode, childNodes[index])
    } else {
      styleElement.appendChild(cssNode)
    }
  }
}

function applyToTag (styleElement, obj) {
  var css = obj.css
  var media = obj.media
  var sourceMap = obj.sourceMap

  if (media) {
    styleElement.setAttribute('media', media)
  }

  if (sourceMap) {
    // https://developer.chrome.com/devtools/docs/javascript-debugging
    // this makes source maps inside style tags work properly in Chrome
    css += '\n/*# sourceURL=' + sourceMap.sources[0] + ' */'
    // http://stackoverflow.com/a/26603875
    css += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + ' */'
  }

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild)
    }
    styleElement.appendChild(document.createTextNode(css))
  }
}


/***/ }),
/* 9 */
/***/ (function(module, exports) {

/**
 * Translates the list format produced by css-loader into something
 * easier to manipulate.
 */
module.exports = function listToStyles (parentId, list) {
  var styles = []
  var newStyles = {}
  for (var i = 0; i < list.length; i++) {
    var item = list[i]
    var id = item[0]
    var css = item[1]
    var media = item[2]
    var sourceMap = item[3]
    var part = {
      id: parentId + ':' + i,
      css: css,
      media: media,
      sourceMap: sourceMap
    }
    if (!newStyles[id]) {
      styles.push(newStyles[id] = { id: id, parts: [part] })
    } else {
      newStyles[id].parts.push(part)
    }
  }
  return styles
}


/***/ })
/******/ ]);
});
//# sourceMappingURL=index.js.map

/***/ })
/******/ ]);