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
/******/ 	return __webpack_require__(__webpack_require__.s = 55);
/******/ })
/************************************************************************/
/******/ ({

/***/ 5:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _regenerator = __webpack_require__(56);

var _regenerator2 = _interopRequireDefault(_regenerator);

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var _class = function () {
	function _class() {
		_classCallCheck(this, _class);

		this._cdnKey = function () {
			return this.serviceData.key.toLowerCase();
		};

		this._sortByKey = function (unordered) {
			var ordered = {};
			Object.keys(unordered).sort().forEach(function (key) {
				ordered[key] = unordered[key];
			});
			return ordered;
		};

		this.serviceDomain = '.opt.loc';

		this.serviceData = {
			key: "undefined",
			quality: "auto"
		};

		if (typeof optimoleData !== 'undefined' && optimoleData === Object(optimoleData)) {
			this.serviceData = _extends({}, this.serviceData, optimoleData);
		}
	}

	_createClass(_class, [{
		key: "getImageCDNUrl",
		value: function getImageCDNUrl(url, args) {
			if (url.includes(this.serviceDomain)) {
				return url;
			}

			var cdnUrl = "http://" + this._cdnKey() + this.serviceDomain;
			var compressLevel = this.serviceData.quality;
			var urlParts = url.split('://');

			var scheme = urlParts[0];
			var path = urlParts[1];

			if (args.width !== 'auto') {
				args.width = Math.round(args.width);
			}

			if (args.height !== 'auto') {
				args.height = Math.round(args.height);
			}

			var payload = {
				'path': encodeURIComponent(path.replace(/\/$/, '')),
				'scheme': scheme,
				'width': args.width.toString(),
				'height': args.height.toString(),
				'quality': compressLevel.toString()
			};
			payload = this._sortByKey(payload);

			var newUrl = [cdnUrl, payload.width, payload.height, payload.quality, payload.scheme, path];

			return newUrl.join('/');
		}

		/**
   * Update images
   *
   */

	}, {
		key: "updateImages",
		value: function updateImages() {
			var images = document.getElementsByTagName('img');
			var _iteratorNormalCompletion = true;
			var _didIteratorError = false;
			var _iteratorError = undefined;

			try {
				for (var _iterator = images[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
					var image = _step.value;

					this.lazyLoadImage(image);
				}
			} catch (err) {
				_didIteratorError = true;
				_iteratorError = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion && _iterator.return) {
						_iterator.return();
					}
				} finally {
					if (_didIteratorError) {
						throw _iteratorError;
					}
				}
			}
		}

		/**
   * Load remaining images after images listed by the IntersectionObserver
   * have been loaded.
   *
   * @public
   * @param entries
   */

	}, {
		key: "deferImages",
		value: function deferImages(entries) {
			var loaded = 0;
			var inView = 0;

			var self = this;

			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					inView++;
				}
				if (entry.target.dataset.optLazyLoaded === "true") {
					loaded++;
				}
			});

			if (loaded >= inView) {
				entries.forEach(function (entry) {
					var isLazyLoaded = entry.target.dataset.optLazyLoaded;
					if (!entry.isIntersecting && (isLazyLoaded === undefined || isLazyLoaded !== "true")) {
						self.lazyLoadImage(entry.target);
					}
				});
			}
		}

		/**
   * Check if the available image is of worst quality than required
   * by the client sizes.
   *
   * @public
   * @param image
   * @returns {boolean}
   */

	}, {
		key: "requiresBetterQuality",
		value: function requiresBetterQuality(image) {
			if (image.dataset.optOtimizedWidth === undefined || image.dataset.optOptimizedHeight === undefined) {
				return true;
			}
			if (parseInt(image.dataset.optOtimizedWidth) <= parseInt(image.clientWidth)) {
				return true;
			}

			if (parseInt(image.dataset.optOtimizedHeight) <= parseInt(image.clientHeight)) {
				return true;
			}

			return false;
		}

		/**
   * Lazy load image, replace src once new image is available.
   *
   * @public
   * @param image
   * @param entries
   */

	}, {
		key: "lazyLoadImage",
		value: function lazyLoadImage(image) {
			var entries = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

			var self = this;

			var containerWidth = image.clientWidth;
			var optWidth = image.attributes.width ? image.attributes.width.value : 'auto';
			var optHeight = image.attributes.height ? image.attributes.height.value : 'auto';

			var containerHeight = optWidth === 'auto' || optHeight === 'auto' ? image.clientHeight : parseInt(optHeight / optWidth * containerWidth);

			image.style.width = containerWidth + "px";
			image.style.height = containerHeight + "px";

			if (image.src === undefined || image.src === '') {
				image.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
			}

			if (image.dataset.optSrc !== undefined && this.requiresBetterQuality(image)) {
				var optSrc = this.getImageCDNUrl(image.dataset.optSrc, { "width": containerWidth, "height": containerHeight });
				var downloadingImage = new Image();

				downloadingImage.onload = _asyncToGenerator( /*#__PURE__*/_regenerator2.default.mark(function _callee() {
					return _regenerator2.default.wrap(function _callee$(_context) {
						while (1) {
							switch (_context.prev = _context.next) {
								case 0:
									if (this.complete) {
										image.classList.add('optml_lazyload_img');
										image.src = this.src;
										image.style.removeProperty('width');
										image.style.removeProperty('height');
										image.dataset.optOtimizedWidth = "" + containerWidth;
										image.dataset.optOptimizedHeight = "" + containerHeight;
										image.dataset.optLazyLoaded = "true";
										if (entries != null) {
											self.deferImages(entries);
										}
									}

								case 1:
								case "end":
									return _context.stop();
							}
						}
					}, _callee, this);
				}));
				optSrc = optSrc.replace("/" + optWidth + "/", "/" + containerWidth + "/");
				optSrc = optSrc.replace("/" + optHeight + "/", "/" + containerHeight + "/");
				downloadingImage.src = optSrc;
			}
		}
	}]);

	return _class;
}();

exports.default = _class;

/***/ }),

/***/ 55:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _optimole_service = __webpack_require__(5);

var _optimole_service2 = _interopRequireDefault(_optimole_service);

__webpack_require__(59);

__webpack_require__(61);

__webpack_require__(62);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var lastX = window.innerWidth;
var lastY = window.innerHeight;

/**
 * Resize listener for when window changes sizes
 * We check if we need a better quality image.
 */
document.addEventListener('DOMContentLoaded', function () {
	var resizeEnd = void 0;
	window.addEventListener('resize', function () {
		clearTimeout(resizeEnd);
		resizeEnd = setTimeout(function () {
			var evt = new Event('resize-end');
			window.dispatchEvent(evt);
		}, 500);
	});
});

window.addEventListener('resize-end', function () {
	var newX = window.innerWidth;
	var newY = window.innerHeight;

	// Fetch new images if resize window by 15% larger,
	// does not fetch when resize is smaller since we already have better quality images loaded.
	if (lastX * 1.15 <= newX) {
		var optimoleService = new _optimole_service2.default();
		optimoleService.updateImages();
	}
	lastX = newX;
	lastY = newY;
});

/***/ }),

/***/ 56:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(57);


/***/ }),

/***/ 57:
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

// This method of obtaining a reference to the global object needs to be
// kept identical to the way it is obtained in runtime.js
var g = (function() { return this })() || Function("return this")();

// Use `getOwnPropertyNames` because not all browsers support calling
// `hasOwnProperty` on the global `self` object in a worker. See #183.
var hadRuntime = g.regeneratorRuntime &&
  Object.getOwnPropertyNames(g).indexOf("regeneratorRuntime") >= 0;

// Save the old regeneratorRuntime in case it needs to be restored later.
var oldRuntime = hadRuntime && g.regeneratorRuntime;

// Force reevalutation of runtime.js.
g.regeneratorRuntime = undefined;

module.exports = __webpack_require__(58);

if (hadRuntime) {
  // Restore the original runtime.
  g.regeneratorRuntime = oldRuntime;
} else {
  // Remove the global property added by runtime.js.
  try {
    delete g.regeneratorRuntime;
  } catch(e) {
    g.regeneratorRuntime = undefined;
  }
}


/***/ }),

/***/ 58:
/***/ (function(module, exports) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

!(function(global) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined; // More compressible than void 0.
  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  var inModule = typeof module === "object";
  var runtime = global.regeneratorRuntime;
  if (runtime) {
    if (inModule) {
      // If regeneratorRuntime is defined globally and we're in a module,
      // make the exports object identical to regeneratorRuntime.
      module.exports = runtime;
    }
    // Don't bother evaluating the rest of this file if the runtime was
    // already defined globally.
    return;
  }

  // Define the runtime globally (as expected by generated code) as either
  // module.exports (if we're in a module) or a new, empty object.
  runtime = global.regeneratorRuntime = inModule ? module.exports : {};

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []);

    // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.
    generator._invoke = makeInvokeMethod(innerFn, self, context);

    return generator;
  }
  runtime.wrap = wrap;

  // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.
  function tryCatch(fn, obj, arg) {
    try {
      return { type: "normal", arg: fn.call(obj, arg) };
    } catch (err) {
      return { type: "throw", arg: err };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed";

  // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.
  var ContinueSentinel = {};

  // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}

  // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.
  var IteratorPrototype = {};
  IteratorPrototype[iteratorSymbol] = function () {
    return this;
  };

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  if (NativeIteratorPrototype &&
      NativeIteratorPrototype !== Op &&
      hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype =
    Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
  GeneratorFunctionPrototype.constructor = GeneratorFunction;
  GeneratorFunctionPrototype[toStringTagSymbol] =
    GeneratorFunction.displayName = "GeneratorFunction";

  // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function(method) {
      prototype[method] = function(arg) {
        return this._invoke(method, arg);
      };
    });
  }

  runtime.isGeneratorFunction = function(genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor
      ? ctor === GeneratorFunction ||
        // For the native GeneratorFunction constructor, the best we can
        // do is to check its .name property.
        (ctor.displayName || ctor.name) === "GeneratorFunction"
      : false;
  };

  runtime.mark = function(genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      if (!(toStringTagSymbol in genFun)) {
        genFun[toStringTagSymbol] = "GeneratorFunction";
      }
    }
    genFun.prototype = Object.create(Gp);
    return genFun;
  };

  // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.
  runtime.awrap = function(arg) {
    return { __await: arg };
  };

  function AsyncIterator(generator) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;
        if (value &&
            typeof value === "object" &&
            hasOwn.call(value, "__await")) {
          return Promise.resolve(value.__await).then(function(value) {
            invoke("next", value, resolve, reject);
          }, function(err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return Promise.resolve(value).then(function(unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration. If the Promise is rejected, however, the
          // result for this iteration will be rejected with the same
          // reason. Note that rejections of yielded Promises are not
          // thrown back into the generator function, as is the case
          // when an awaited Promise is rejected. This difference in
          // behavior between yield and await is important, because it
          // allows the consumer to decide what to do with the yielded
          // rejection (swallow it and continue, manually .throw it back
          // into the generator, abandon iteration, whatever). With
          // await, by contrast, there is no opportunity to examine the
          // rejection reason outside the generator function, so the
          // only option is to throw it from the await expression, and
          // let the generator function handle the exception.
          result.value = unwrapped;
          resolve(result);
        }, reject);
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new Promise(function(resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise =
        // If enqueue has been called before, then we want to wait until
        // all previous Promises have been resolved before calling invoke,
        // so that results are always delivered in the correct order. If
        // enqueue has not been called before, then it is important to
        // call invoke immediately, without waiting on a callback to fire,
        // so that the async generator function has the opportunity to do
        // any necessary setup in a predictable way. This predictability
        // is why the Promise constructor synchronously invokes its
        // executor callback, and why async functions synchronously
        // execute code before the first await. Since we implement simple
        // async functions in terms of async generators, it is especially
        // important to get this right, even though it requires care.
        previousPromise ? previousPromise.then(
          callInvokeWithMethodAndArg,
          // Avoid propagating failures to Promises returned by later
          // invocations of the iterator.
          callInvokeWithMethodAndArg
        ) : callInvokeWithMethodAndArg();
    }

    // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).
    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);
  AsyncIterator.prototype[asyncIteratorSymbol] = function () {
    return this;
  };
  runtime.AsyncIterator = AsyncIterator;

  // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.
  runtime.async = function(innerFn, outerFn, self, tryLocsList) {
    var iter = new AsyncIterator(
      wrap(innerFn, outerFn, self, tryLocsList)
    );

    return runtime.isGeneratorFunction(outerFn)
      ? iter // If outerFn is a generator, return the full iterator.
      : iter.next().then(function(result) {
          return result.done ? result.value : iter.next();
        });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;

    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        }

        // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;

        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);

        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;

        var record = tryCatch(innerFn, self, context);
        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done
            ? GenStateCompleted
            : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };

        } else if (record.type === "throw") {
          state = GenStateCompleted;
          // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.
          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  }

  // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.
  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];
    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        if (delegate.iterator.return) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError(
          "The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (! info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value;

      // Resume execution at the desired location (see delegateYield).
      context.next = delegate.nextLoc;

      // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.
      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }

    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    }

    // The delegate iterator is finished, so forget it and continue with
    // the outer generator.
    context.delegate = null;
    return ContinueSentinel;
  }

  // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.
  defineIteratorMethods(Gp);

  Gp[toStringTagSymbol] = "Generator";

  // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.
  Gp[iteratorSymbol] = function() {
    return this;
  };

  Gp.toString = function() {
    return "[object Generator]";
  };

  function pushTryEntry(locs) {
    var entry = { tryLoc: locs[0] };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{ tryLoc: "root" }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  runtime.keys = function(object) {
    var keys = [];
    for (var key in object) {
      keys.push(key);
    }
    keys.reverse();

    // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.
    return function next() {
      while (keys.length) {
        var key = keys.pop();
        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      }

      // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.
      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1, next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;

          return next;
        };

        return next.next = next;
      }
    }

    // Return an iterator with no values.
    return { next: doneResult };
  }
  runtime.values = values;

  function doneResult() {
    return { value: undefined, done: true };
  }

  Context.prototype = {
    constructor: Context,

    reset: function(skipTempReset) {
      this.prev = 0;
      this.next = 0;
      // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.
      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;

      this.method = "next";
      this.arg = undefined;

      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" &&
              hasOwn.call(this, name) &&
              !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },

    stop: function() {
      this.done = true;

      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;
      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },

    dispatchException: function(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;
      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !! caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }

          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },

    abrupt: function(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev &&
            hasOwn.call(entry, "finallyLoc") &&
            this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry &&
          (type === "break" ||
           type === "continue") &&
          finallyEntry.tryLoc <= arg &&
          arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },

    complete: function(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" ||
          record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },

    finish: function(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },

    "catch": function(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }

      // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.
      throw new Error("illegal catch attempt");
    },

    delegateYield: function(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  };
})(
  // In sloppy mode, unbound `this` refers to the global object, fallback to
  // Function constructor if we're in global strict mode. That is sadly a form
  // of indirect eval which violates Content Security Policy.
  (function() { return this })() || Function("return this")()
);


/***/ }),

/***/ 59:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(60);

var _optimole_service = __webpack_require__(5);

var _optimole_service2 = _interopRequireDefault(_optimole_service);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Method invoked when object intersects with the observer.
 *
 * @private
 * @param entries
 * @param observer
 */
function handleIntersectOptimole(entries, observer) {
	var optimoleService = new _optimole_service2.default();
	entries.forEach(function (entry) {
		var isLazyLoaded = entry.target.dataset.optLazyLoaded;
		if (entry.isIntersecting && (isLazyLoaded === undefined || isLazyLoaded !== "true")) {
			optimoleService.lazyLoadImage(entry.target, entries);
		}
	});
}

/**
 * Boilerplate to build threshold array
 * change the numSteps from 0 to 100
 * translates in a list of thresholds from 0 to 1
 *
 * @returns {Array}
 */
function buildThresholdList() {
	var thresholds = [];
	var numSteps = 5;

	for (var i = 1.0; i <= numSteps; i++) {
		var ratio = i / numSteps;
		thresholds.push(ratio);
	}

	thresholds.push(0);
	return thresholds;
}

/**
 * Builds the intersection Observer
 */
function createIntersectionObserverOptimole() {
	var observer = void 0;

	var options = {
		root: null,
		rootMargin: "0px 0px 25% 0px",
		threshold: buildThresholdList(),
		classTarget: this
	};

	observer = new IntersectionObserver(handleIntersectOptimole, options);

	var images = document.getElementsByTagName('img');
	var _iteratorNormalCompletion = true;
	var _didIteratorError = false;
	var _iteratorError = undefined;

	try {
		for (var _iterator = images[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
			var image = _step.value;

			observer.observe(image);
		}
	} catch (err) {
		_didIteratorError = true;
		_iteratorError = err;
	} finally {
		try {
			if (!_iteratorNormalCompletion && _iterator.return) {
				_iterator.return();
			}
		} finally {
			if (_didIteratorError) {
				throw _iteratorError;
			}
		}
	}
}

document.addEventListener("DOMContentLoaded", function (event) {
	createIntersectionObserverOptimole();
});

/***/ }),

/***/ 60:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the W3C SOFTWARE AND DOCUMENT NOTICE AND LICENSE.
 *
 *  https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 */
(function (window, document) {
	'use strict';

	// Exits early if all IntersectionObserver and IntersectionObserverEntry
	// features are natively supported.

	if ('IntersectionObserver' in window && 'IntersectionObserverEntry' in window && 'intersectionRatio' in window.IntersectionObserverEntry.prototype) {

		// Minimal polyfill for Edge 15's lack of `isIntersecting`
		// See: https://github.com/w3c/IntersectionObserver/issues/211
		if (!('isIntersecting' in window.IntersectionObserverEntry.prototype)) {
			Object.defineProperty(window.IntersectionObserverEntry.prototype, 'isIntersecting', {
				get: function get() {
					return this.intersectionRatio > 0;
				}
			});
		}
		return;
	}

	/**
  * An IntersectionObserver registry. This registry exists to hold a strong
  * reference to IntersectionObserver instances currently observering a target
  * element. Without this registry, instances without another reference may be
  * garbage collected.
  */
	var registry = [];

	/**
  * Creates the global IntersectionObserverEntry constructor.
  * https://w3c.github.io/IntersectionObserver/#intersection-observer-entry
  * @param {Object} entry A dictionary of instance properties.
  * @constructor
  */
	function IntersectionObserverEntry(entry) {
		this.time = entry.time;
		this.target = entry.target;
		this.rootBounds = entry.rootBounds;
		this.boundingClientRect = entry.boundingClientRect;
		this.intersectionRect = entry.intersectionRect || getEmptyRect();
		this.isIntersecting = !!entry.intersectionRect;

		// Calculates the intersection ratio.
		var targetRect = this.boundingClientRect;
		var targetArea = targetRect.width * targetRect.height;
		var intersectionRect = this.intersectionRect;
		var intersectionArea = intersectionRect.width * intersectionRect.height;

		// Sets intersection ratio.
		if (targetArea) {
			this.intersectionRatio = intersectionArea / targetArea;
		} else {
			// If area is zero and is intersecting, sets to 1, otherwise to 0
			this.intersectionRatio = this.isIntersecting ? 1 : 0;
		}
	}

	/**
  * Creates the global IntersectionObserver constructor.
  * https://w3c.github.io/IntersectionObserver/#intersection-observer-interface
  * @param {Function} callback The function to be invoked after intersection
  *     changes have queued. The function is not invoked if the queue has
  *     been emptied by calling the `takeRecords` method.
  * @param {Object=} opt_options Optional configuration options.
  * @constructor
  */
	function IntersectionObserver(callback, opt_options) {

		var options = opt_options || {};

		if (typeof callback != 'function') {
			throw new Error('callback must be a function');
		}

		if (options.root && options.root.nodeType != 1) {
			throw new Error('root must be an Element');
		}

		// Binds and throttles `this._checkForIntersections`.
		this._checkForIntersections = throttle(this._checkForIntersections.bind(this), this.THROTTLE_TIMEOUT);

		// Private properties.
		this._callback = callback;
		this._observationTargets = [];
		this._queuedEntries = [];
		this._rootMarginValues = this._parseRootMargin(options.rootMargin);

		// Public properties.
		this.thresholds = this._initThresholds(options.threshold);
		this.root = options.root || null;
		this.rootMargin = this._rootMarginValues.map(function (margin) {
			return margin.value + margin.unit;
		}).join(' ');
	}

	/**
  * The minimum interval within which the document will be checked for
  * intersection changes.
  */
	IntersectionObserver.prototype.THROTTLE_TIMEOUT = 100;

	/**
  * The frequency in which the polyfill polls for intersection changes.
  * this can be updated on a per instance basis and must be set prior to
  * calling `observe` on the first target.
  */
	IntersectionObserver.prototype.POLL_INTERVAL = null;

	/**
  * Use a mutation observer on the root element
  * to detect intersection changes.
  */
	IntersectionObserver.prototype.USE_MUTATION_OBSERVER = true;

	/**
  * Starts observing a target element for intersection changes based on
  * the thresholds values.
  * @param {Element} target The DOM element to observe.
  */
	IntersectionObserver.prototype.observe = function (target) {
		var isTargetAlreadyObserved = this._observationTargets.some(function (item) {
			return item.element == target;
		});

		if (isTargetAlreadyObserved) {
			return;
		}

		if (!(target && target.nodeType == 1)) {
			throw new Error('target must be an Element');
		}

		this._registerInstance();
		this._observationTargets.push({ element: target, entry: null });
		this._monitorIntersections();
		this._checkForIntersections();
	};

	/**
  * Stops observing a target element for intersection changes.
  * @param {Element} target The DOM element to observe.
  */
	IntersectionObserver.prototype.unobserve = function (target) {
		this._observationTargets = this._observationTargets.filter(function (item) {

			return item.element != target;
		});
		if (!this._observationTargets.length) {
			this._unmonitorIntersections();
			this._unregisterInstance();
		}
	};

	/**
  * Stops observing all target elements for intersection changes.
  */
	IntersectionObserver.prototype.disconnect = function () {
		this._observationTargets = [];
		this._unmonitorIntersections();
		this._unregisterInstance();
	};

	/**
  * Returns any queue entries that have not yet been reported to the
  * callback and clears the queue. This can be used in conjunction with the
  * callback to obtain the absolute most up-to-date intersection information.
  * @return {Array} The currently queued entries.
  */
	IntersectionObserver.prototype.takeRecords = function () {
		var records = this._queuedEntries.slice();
		this._queuedEntries = [];
		return records;
	};

	/**
  * Accepts the threshold value from the user configuration object and
  * returns a sorted array of unique threshold values. If a value is not
  * between 0 and 1 and error is thrown.
  * @private
  * @param {Array|number=} opt_threshold An optional threshold value or
  *     a list of threshold values, defaulting to [0].
  * @return {Array} A sorted list of unique and valid threshold values.
  */
	IntersectionObserver.prototype._initThresholds = function (opt_threshold) {
		var threshold = opt_threshold || [0];
		if (!Array.isArray(threshold)) threshold = [threshold];

		return threshold.sort().filter(function (t, i, a) {
			if (typeof t != 'number' || isNaN(t) || t < 0 || t > 1) {
				throw new Error('threshold must be a number between 0 and 1 inclusively');
			}
			return t !== a[i - 1];
		});
	};

	/**
  * Accepts the rootMargin value from the user configuration object
  * and returns an array of the four margin values as an object containing
  * the value and unit properties. If any of the values are not properly
  * formatted or use a unit other than px or %, and error is thrown.
  * @private
  * @param {string=} opt_rootMargin An optional rootMargin value,
  *     defaulting to '0px'.
  * @return {Array<Object>} An array of margin objects with the keys
  *     value and unit.
  */
	IntersectionObserver.prototype._parseRootMargin = function (opt_rootMargin) {
		var marginString = opt_rootMargin || '0px';
		var margins = marginString.split(/\s+/).map(function (margin) {
			var parts = /^(-?\d*\.?\d+)(px|%)$/.exec(margin);
			if (!parts) {
				throw new Error('rootMargin must be specified in pixels or percent');
			}
			return { value: parseFloat(parts[1]), unit: parts[2] };
		});

		// Handles shorthand.
		margins[1] = margins[1] || margins[0];
		margins[2] = margins[2] || margins[0];
		margins[3] = margins[3] || margins[1];

		return margins;
	};

	/**
  * Starts polling for intersection changes if the polling is not already
  * happening, and if the page's visibilty state is visible.
  * @private
  */
	IntersectionObserver.prototype._monitorIntersections = function () {
		if (!this._monitoringIntersections) {
			this._monitoringIntersections = true;

			// If a poll interval is set, use polling instead of listening to
			// resize and scroll events or DOM mutations.
			if (this.POLL_INTERVAL) {
				this._monitoringInterval = setInterval(this._checkForIntersections, this.POLL_INTERVAL);
			} else {
				addEvent(window, 'resize', this._checkForIntersections, true);
				addEvent(document, 'scroll', this._checkForIntersections, true);

				if (this.USE_MUTATION_OBSERVER && 'MutationObserver' in window) {
					this._domObserver = new MutationObserver(this._checkForIntersections);
					this._domObserver.observe(document, {
						attributes: true,
						childList: true,
						characterData: true,
						subtree: true
					});
				}
			}
		}
	};

	/**
  * Stops polling for intersection changes.
  * @private
  */
	IntersectionObserver.prototype._unmonitorIntersections = function () {
		if (this._monitoringIntersections) {
			this._monitoringIntersections = false;

			clearInterval(this._monitoringInterval);
			this._monitoringInterval = null;

			removeEvent(window, 'resize', this._checkForIntersections, true);
			removeEvent(document, 'scroll', this._checkForIntersections, true);

			if (this._domObserver) {
				this._domObserver.disconnect();
				this._domObserver = null;
			}
		}
	};

	/**
  * Scans each observation target for intersection changes and adds them
  * to the internal entries queue. If new entries are found, it
  * schedules the callback to be invoked.
  * @private
  */
	IntersectionObserver.prototype._checkForIntersections = function () {
		var rootIsInDom = this._rootIsInDom();
		var rootRect = rootIsInDom ? this._getRootRect() : getEmptyRect();

		this._observationTargets.forEach(function (item) {
			var target = item.element;
			var targetRect = getBoundingClientRect(target);
			var rootContainsTarget = this._rootContainsTarget(target);
			var oldEntry = item.entry;
			var intersectionRect = rootIsInDom && rootContainsTarget && this._computeTargetAndRootIntersection(target, rootRect);

			var newEntry = item.entry = new IntersectionObserverEntry({
				time: now(),
				target: target,
				boundingClientRect: targetRect,
				rootBounds: rootRect,
				intersectionRect: intersectionRect
			});

			if (!oldEntry) {
				this._queuedEntries.push(newEntry);
			} else if (rootIsInDom && rootContainsTarget) {
				// If the new entry intersection ratio has crossed any of the
				// thresholds, add a new entry.
				if (this._hasCrossedThreshold(oldEntry, newEntry)) {
					this._queuedEntries.push(newEntry);
				}
			} else {
				// If the root is not in the DOM or target is not contained within
				// root but the previous entry for this target had an intersection,
				// add a new record indicating removal.
				if (oldEntry && oldEntry.isIntersecting) {
					this._queuedEntries.push(newEntry);
				}
			}
		}, this);

		if (this._queuedEntries.length) {
			this._callback(this.takeRecords(), this);
		}
	};

	/**
  * Accepts a target and root rect computes the intersection between then
  * following the algorithm in the spec.
  * TODO(philipwalton): at this time clip-path is not considered.
  * https://w3c.github.io/IntersectionObserver/#calculate-intersection-rect-algo
  * @param {Element} target The target DOM element
  * @param {Object} rootRect The bounding rect of the root after being
  *     expanded by the rootMargin value.
  * @return {?Object} The final intersection rect object or undefined if no
  *     intersection is found.
  * @private
  */
	IntersectionObserver.prototype._computeTargetAndRootIntersection = function (target, rootRect) {

		// If the element isn't displayed, an intersection can't happen.
		if (window.getComputedStyle(target).display == 'none') return;

		var targetRect = getBoundingClientRect(target);
		var intersectionRect = targetRect;
		var parent = getParentNode(target);
		var atRoot = false;

		while (!atRoot) {
			var parentRect = null;
			var parentComputedStyle = parent.nodeType == 1 ? window.getComputedStyle(parent) : {};

			// If the parent isn't displayed, an intersection can't happen.
			if (parentComputedStyle.display == 'none') return;

			if (parent == this.root || parent == document) {
				atRoot = true;
				parentRect = rootRect;
			} else {
				// If the element has a non-visible overflow, and it's not the <body>
				// or <html> element, update the intersection rect.
				// Note: <body> and <html> cannot be clipped to a rect that's not also
				// the document rect, so no need to compute a new intersection.
				if (parent != document.body && parent != document.documentElement && parentComputedStyle.overflow != 'visible') {
					parentRect = getBoundingClientRect(parent);
				}
			}

			// If either of the above conditionals set a new parentRect,
			// calculate new intersection data.
			if (parentRect) {
				intersectionRect = computeRectIntersection(parentRect, intersectionRect);

				if (!intersectionRect) break;
			}
			parent = getParentNode(parent);
		}
		return intersectionRect;
	};

	/**
  * Returns the root rect after being expanded by the rootMargin value.
  * @return {Object} The expanded root rect.
  * @private
  */
	IntersectionObserver.prototype._getRootRect = function () {
		var rootRect;
		if (this.root) {
			rootRect = getBoundingClientRect(this.root);
		} else {
			// Use <html>/<body> instead of window since scroll bars affect size.
			var html = document.documentElement;
			var body = document.body;
			rootRect = {
				top: 0,
				left: 0,
				right: html.clientWidth || body.clientWidth,
				width: html.clientWidth || body.clientWidth,
				bottom: html.clientHeight || body.clientHeight,
				height: html.clientHeight || body.clientHeight
			};
		}
		return this._expandRectByRootMargin(rootRect);
	};

	/**
  * Accepts a rect and expands it by the rootMargin value.
  * @param {Object} rect The rect object to expand.
  * @return {Object} The expanded rect.
  * @private
  */
	IntersectionObserver.prototype._expandRectByRootMargin = function (rect) {
		var margins = this._rootMarginValues.map(function (margin, i) {
			return margin.unit == 'px' ? margin.value : margin.value * (i % 2 ? rect.width : rect.height) / 100;
		});
		var newRect = {
			top: rect.top - margins[0],
			right: rect.right + margins[1],
			bottom: rect.bottom + margins[2],
			left: rect.left - margins[3]
		};
		newRect.width = newRect.right - newRect.left;
		newRect.height = newRect.bottom - newRect.top;

		return newRect;
	};

	/**
  * Accepts an old and new entry and returns true if at least one of the
  * threshold values has been crossed.
  * @param {?IntersectionObserverEntry} oldEntry The previous entry for a
  *    particular target element or null if no previous entry exists.
  * @param {IntersectionObserverEntry} newEntry The current entry for a
  *    particular target element.
  * @return {boolean} Returns true if a any threshold has been crossed.
  * @private
  */
	IntersectionObserver.prototype._hasCrossedThreshold = function (oldEntry, newEntry) {

		// To make comparing easier, an entry that has a ratio of 0
		// but does not actually intersect is given a value of -1
		var oldRatio = oldEntry && oldEntry.isIntersecting ? oldEntry.intersectionRatio || 0 : -1;
		var newRatio = newEntry.isIntersecting ? newEntry.intersectionRatio || 0 : -1;

		// Ignore unchanged ratios
		if (oldRatio === newRatio) return;

		for (var i = 0; i < this.thresholds.length; i++) {
			var threshold = this.thresholds[i];

			// Return true if an entry matches a threshold or if the new ratio
			// and the old ratio are on the opposite sides of a threshold.
			if (threshold == oldRatio || threshold == newRatio || threshold < oldRatio !== threshold < newRatio) {
				return true;
			}
		}
	};

	/**
  * Returns whether or not the root element is an element and is in the DOM.
  * @return {boolean} True if the root element is an element and is in the DOM.
  * @private
  */
	IntersectionObserver.prototype._rootIsInDom = function () {
		return !this.root || containsDeep(document, this.root);
	};

	/**
  * Returns whether or not the target element is a child of root.
  * @param {Element} target The target element to check.
  * @return {boolean} True if the target element is a child of root.
  * @private
  */
	IntersectionObserver.prototype._rootContainsTarget = function (target) {
		return containsDeep(this.root || document, target);
	};

	/**
  * Adds the instance to the global IntersectionObserver registry if it isn't
  * already present.
  * @private
  */
	IntersectionObserver.prototype._registerInstance = function () {
		if (registry.indexOf(this) < 0) {
			registry.push(this);
		}
	};

	/**
  * Removes the instance from the global IntersectionObserver registry.
  * @private
  */
	IntersectionObserver.prototype._unregisterInstance = function () {
		var index = registry.indexOf(this);
		if (index != -1) registry.splice(index, 1);
	};

	/**
  * Returns the result of the performance.now() method or null in browsers
  * that don't support the API.
  * @return {number} The elapsed time since the page was requested.
  */
	function now() {
		return window.performance && performance.now && performance.now();
	}

	/**
  * Throttles a function and delays its executiong, so it's only called at most
  * once within a given time period.
  * @param {Function} fn The function to throttle.
  * @param {number} timeout The amount of time that must pass before the
  *     function can be called again.
  * @return {Function} The throttled function.
  */
	function throttle(fn, timeout) {
		var timer = null;
		return function () {
			if (!timer) {
				timer = setTimeout(function () {
					fn();
					timer = null;
				}, timeout);
			}
		};
	}

	/**
  * Adds an event handler to a DOM node ensuring cross-browser compatibility.
  * @param {Node} node The DOM node to add the event handler to.
  * @param {string} event The event name.
  * @param {Function} fn The event handler to add.
  * @param {boolean} opt_useCapture Optionally adds the even to the capture
  *     phase. Note: this only works in modern browsers.
  */
	function addEvent(node, event, fn, opt_useCapture) {
		if (typeof node.addEventListener == 'function') {
			node.addEventListener(event, fn, opt_useCapture || false);
		} else if (typeof node.attachEvent == 'function') {
			node.attachEvent('on' + event, fn);
		}
	}

	/**
  * Removes a previously added event handler from a DOM node.
  * @param {Node} node The DOM node to remove the event handler from.
  * @param {string} event The event name.
  * @param {Function} fn The event handler to remove.
  * @param {boolean} opt_useCapture If the event handler was added with this
  *     flag set to true, it should be set to true here in order to remove it.
  */
	function removeEvent(node, event, fn, opt_useCapture) {
		if (typeof node.removeEventListener == 'function') {
			node.removeEventListener(event, fn, opt_useCapture || false);
		} else if (typeof node.detatchEvent == 'function') {
			node.detatchEvent('on' + event, fn);
		}
	}

	/**
  * Returns the intersection between two rect objects.
  * @param {Object} rect1 The first rect.
  * @param {Object} rect2 The second rect.
  * @return {?Object} The intersection rect or undefined if no intersection
  *     is found.
  */
	function computeRectIntersection(rect1, rect2) {
		var top = Math.max(rect1.top, rect2.top);
		var bottom = Math.min(rect1.bottom, rect2.bottom);
		var left = Math.max(rect1.left, rect2.left);
		var right = Math.min(rect1.right, rect2.right);
		var width = right - left;
		var height = bottom - top;

		return width >= 0 && height >= 0 && {
			top: top,
			bottom: bottom,
			left: left,
			right: right,
			width: width,
			height: height
		};
	}

	/**
  * Shims the native getBoundingClientRect for compatibility with older IE.
  * @param {Element} el The element whose bounding rect to get.
  * @return {Object} The (possibly shimmed) rect of the element.
  */
	function getBoundingClientRect(el) {
		var rect;

		try {
			rect = el.getBoundingClientRect();
		} catch (err) {
			// Ignore Windows 7 IE11 "Unspecified error"
			// https://github.com/w3c/IntersectionObserver/pull/205
		}

		if (!rect) return getEmptyRect();

		// Older IE
		if (!(rect.width && rect.height)) {
			rect = {
				top: rect.top,
				right: rect.right,
				bottom: rect.bottom,
				left: rect.left,
				width: rect.right - rect.left,
				height: rect.bottom - rect.top
			};
		}
		return rect;
	}

	/**
  * Returns an empty rect object. An empty rect is returned when an element
  * is not in the DOM.
  * @return {Object} The empty rect.
  */
	function getEmptyRect() {
		return {
			top: 0,
			bottom: 0,
			left: 0,
			right: 0,
			width: 0,
			height: 0
		};
	}

	/**
  * Checks to see if a parent element contains a child elemnt (including inside
  * shadow DOM).
  * @param {Node} parent The parent element.
  * @param {Node} child The child element.
  * @return {boolean} True if the parent node contains the child node.
  */
	function containsDeep(parent, child) {
		var node = child;
		while (node) {
			if (node == parent) return true;

			node = getParentNode(node);
		}
		return false;
	}

	/**
  * Gets the parent node of an element or its host element if the parent node
  * is a shadow root.
  * @param {Node} node The node whose parent to get.
  * @return {Node|null} The parent node or null if no parent exists.
  */
	function getParentNode(node) {
		var parent = node.parentNode;

		if (parent && parent.nodeType == 11 && parent.host) {
			// If the parent is a shadow root, return the host element.
			return parent.host;
		}
		return parent;
	}

	// Exposes the constructors globally.
	window.IntersectionObserver = IntersectionObserver;
	window.IntersectionObserverEntry = IntersectionObserverEntry;
})(window, document);

/***/ }),

/***/ 61:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _optimole_service = __webpack_require__(5);

var _optimole_service2 = _interopRequireDefault(_optimole_service);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Method invoked when object changes MutationObserver's targetNode.
 *
 * @param mutationsList
 * @param observer
 */
function handleMutationOptimole(mutationsList, observer) {
	var optimoleService = new _optimole_service2.default();
	var _iteratorNormalCompletion = true;
	var _didIteratorError = false;
	var _iteratorError = undefined;

	try {
		for (var _iterator = mutationsList[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
			var mutation = _step.value;

			if (mutation.type === 'childList') {
				// A child node has been added or removed
				optimoleService.updateImages();
			}
		}
	} catch (err) {
		_didIteratorError = true;
		_iteratorError = err;
	} finally {
		try {
			if (!_iteratorNormalCompletion && _iterator.return) {
				_iterator.return();
			}
		} finally {
			if (_didIteratorError) {
				throw _iteratorError;
			}
		}
	}
}

function createMutationObserver() {
	var targetNode = document.getElementsByTagName('body')[0];
	var config = { attributes: false, childList: true, subtree: true };

	var observer = new MutationObserver(handleMutationOptimole);
	observer.observe(targetNode, config);
}

document.addEventListener("DOMContentLoaded", function (event) {
	createMutationObserver();
});

/***/ }),

/***/ 62:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function addStyleToHead() {
	var css = '\n\t\t\t.optml_lazyload_img {\n\t\t\t    animation: lazyloadTransition 1s;\n\t\t\t}\n\t\t\t\n\t\t\t@-webkit-keyframes lazyloadTransition {\n\t\t\t    0%   {\n\t\t\t        filter: grayscale(0.9) blur(10px);\n\t\t\t    }\n\t\t\t    100% {\n\t\t\t        filter: grayscale(0) blur(0);\n\t\t\t    }\n\t\t\t}\n\t\t';

	var head = document.getElementsByTagName('head')[0];
	var styleTag = document.createElement('style');
	styleTag.setAttribute('type', 'text/css');
	if (styleTag.styleSheet) {
		// IE
		styleTag.styleSheet.cssText = css;
	} else {
		// the world
		styleTag.appendChild(document.createTextNode(css));
	}
	head.appendChild(styleTag);
}

addStyleToHead();

/***/ })

/******/ });