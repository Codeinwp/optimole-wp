
let lastX = window.innerWidth;
let lastY = window.innerHeight;

document.addEventListener( 'DOMContentLoaded', function() {
	let resizeEnd;
	window.addEventListener( 'resize', function() {
		clearTimeout( resizeEnd );
		resizeEnd = setTimeout( function() {
			let evt = new Event( 'resize-end' );
			window.dispatchEvent( evt );
		}, 500 );
	} );
} );

window.addEventListener( 'resize-end', function() {
	let newX = window.innerWidth
	let newY = window.innerHeight

	// Fetch new images if resize window by 15% larger,
	// does not fetch when resize is smaller since we already have better quality images loaded.
	if ( lastX * 1.15 <= newX ) {
		updateImages();
	}
	lastX = newX;
	lastY = newY;

} );

function updateImages() {
	let images = document.getElementsByTagName( 'img' );
	for( let image of images ) {
		lazyLoadImage( image );
	}
}

function deferImages( entries ) {
	let loaded = 0;
	let inView = 0;
	entries.forEach( function( entry ) {
		if ( entry.isIntersecting ) {
			inView++;
		}
		if ( entry.target.dataset.optLazyLoaded === "true" ) {
			loaded++;
		}
	} );

	if ( loaded >= inView ) {
		entries.forEach( function( entry ) {
			let isLazyLoaded = entry.target.dataset.optLazyLoaded;
			if ( ! entry.isIntersecting && ( isLazyLoaded === undefined || isLazyLoaded !== "true" ) ) {
				lazyLoadImage( entry.target );
			}
		} );
	}
}

function requiresBetterQuality( image ) {
	if ( image.dataset.optOtimizedWidth === undefined || image.dataset.optOptimizedHeight === undefined ) {
		return true;
	}
	if ( parseInt( image.dataset.optOtimizedWidth ) <= parseInt( image.clientWidth ) ) {
		return true
	}

	if ( parseInt( image.dataset.optOtimizedHeight ) <= parseInt( image.clientHeight ) ) {
		return true
	}

	return false
}

function lazyLoadImage( image, entries = null ) {
	let containerWidth = image.clientWidth
	let optWidth = image.attributes.width.value
	let optHeight = image.attributes.height.value

	let containerHeight = parseInt( optHeight/optWidth * containerWidth )

	image.style.width = containerWidth + "px";
	image.style.height = containerHeight + "px";

	if ( image.dataset.optSrc !== undefined && requiresBetterQuality( image ) ) {
		let optSrc = image.dataset.optSrc
		let downloadingImage = new Image();

		downloadingImage.onload = async function(){
			if ( this.complete ) {
				image.classList.add( 'optml_lazyload_img' );
				image.src = this.src;
				image.style.removeProperty( 'width' );
				image.style.removeProperty( 'height' );
				image.dataset.optOtimizedWidth = `${containerWidth}`;
				image.dataset.optOptimizedHeight = `${containerHeight}`;
				image.dataset.optLazyLoaded = "true";
				if ( entries != null ) {
					deferImages( entries );
				}
			}
		};
		optSrc = optSrc.replace( `/${optWidth}/`, `/${containerWidth}/` )
		optSrc = optSrc.replace( `/${optHeight}/`, `/${containerHeight}/` )
		downloadingImage.src = optSrc;
	}
}

function handleIntersect( entries, observer ) {
	entries.forEach( function( entry ) {
		let isLazyLoaded = entry.target.dataset.optLazyLoaded;
		if ( entry.isIntersecting && ( isLazyLoaded === undefined || isLazyLoaded !== "true" ) ) {
			lazyLoadImage( entry.target, entries );
		}
	} );
}

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

function createObserver() {
	let observer;

	let options = {
		root: null,
		rootMargin: "0px 0px 25% 0px",
		threshold: buildThresholdList()
	};

	observer = new IntersectionObserver( handleIntersect, options );

	let images = document.getElementsByTagName( 'img' );
	for( let image of images ) {
		observer.observe( image );
	}
}

createObserver();