import OptimoleService from './optimole/optimole_service'

import  './observers/intersection';
import  './observers/mutation';

import  './inject_css'

let lastX = window.innerWidth;
let lastY = window.innerHeight;

/**
 * Resize listener for when window changes sizes
 * We check if we need a better quality image.
 */
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
		let optimoleService = new OptimoleService();
		optimoleService.updateImages();
	}
	lastX = newX;
	lastY = newY;
} );