import './../polyfill/intersectionObserverPolyfill'
import OptimoleService from './../optimole/optimole_service'

/**
 * Method invoked when object intersects with the observer.
 *
 * @private
 * @param entries
 * @param observer
 */
function handleIntersectOptimole( entries, observer ) {
	let optimoleService = new OptimoleService();
	entries.forEach( function( entry ) {
		let isLazyLoaded = entry.target.dataset.optLazyLoaded;
		if ( entry.isIntersecting && ( isLazyLoaded === undefined || isLazyLoaded !== "true" ) ) {
			optimoleService.lazyLoadImage( entry.target, entries )
		}
	} );
}

/**
 * Boilerplate to build threshold array
 * change the numSteps from 0 to 100
 * translates in a list of thresholds from 0 to 1
 *
 * @returns {Array}
 */
function buildThresholdList() {
	let thresholds = [];
	let numSteps = 5;

	for ( let i=1.0; i<=numSteps; i++ ) {
		let ratio = i/numSteps;
		thresholds.push( ratio );
	}

	thresholds.push( 0 );
	return thresholds;
}

/**
 * Builds the intersection Observer
 */
function createIntersectionObserverOptimole() {
	let observer;

	let options = {
		root: null,
		rootMargin: "0px 0px 25% 0px",
		threshold: buildThresholdList(),
		classTarget: this
	};

	observer = new IntersectionObserver( handleIntersectOptimole, options );

	let images = document.getElementsByTagName( 'img' );
	for( let image of images ) {
		observer.observe( image );
	}
}

document.addEventListener( "DOMContentLoaded", function( event ) {
	createIntersectionObserverOptimole();
} );