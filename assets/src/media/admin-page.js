import './scss/admin-page.scss';

const { optmlAdminPage } = window;

const { siteUrl } = optmlAdminPage;

// This runs in the head, before #om-dam exists: `load` does not bubble, so catch it in the capture phase.
document.addEventListener( 'load', function( event ) {
	if ( 'om-dam' !== event.target.id ) {
		return;
	}

	document.querySelector( '.om-dam-loader' ).style.display = 'none';
	event.target.style.display = '';
}, true );

window.addEventListener( 'message', function( event ) {
	if ( ! event.data ) {
		return;
	}

	if ( ! event.data.type ) {
		return;
	}

	if ( 'om-dam' !== event.data.type ) {
		return;
	}

	if ( ! event.data.action ) {
		return;
	}

	if ( 'getUrl' !== event.data.action ) {
		return;
	}

	document.getElementById( 'om-dam' ).contentWindow.postMessage({ siteUrl, type: 'om-dam', context: 'browse' }, '*' );
});
