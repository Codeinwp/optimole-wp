import OptimoleService from './../optimole/optimole_service'

/**
 * Method invoked when object changes MutationObserver's targetNode.
 *
 * @param mutationsList
 * @param observer
 */
function handleMutationOptimole( mutationsList, observer ) {
	let optimoleService = new OptimoleService();
	for( let mutation of mutationsList ) {
		if ( mutation.type === 'childList' ) { // A child node has been added or removed
			optimoleService.updateImages();
		}
	}
}

function createMutationObserver() {
	let targetNode = document.getElementsByTagName( 'body' )[0];
	let config = { attributes: false, childList: true, subtree: true };

	let observer = new MutationObserver( handleMutationOptimole );
	observer.observe( targetNode, config );
}

document.addEventListener( "DOMContentLoaded", function( event ) {
	createMutationObserver();
} );

