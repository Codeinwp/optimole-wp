function addStyleToHead() {
	let css = `
			.optml_lazyload_img {
			    animation: lazyloadTransition 1s;
			}
			
			@-webkit-keyframes lazyloadTransition {
			    0%   {
			        filter: grayscale(0.9) blur(10px);
			    }
			    100% {
			        filter: grayscale(0) blur(0);
			    }
			}
		`;

	let head = document.getElementsByTagName( 'head' )[0];
	let styleTag = document.createElement( 'style' );
	styleTag.setAttribute( 'type', 'text/css' );
	if ( styleTag.styleSheet ) {   // IE
		styleTag.styleSheet.cssText = css;
	} else {                // the world
		styleTag.appendChild( document.createTextNode( css ) );
	}
	head.appendChild( styleTag );
}

addStyleToHead();