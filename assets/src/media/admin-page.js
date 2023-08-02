import './scss/admin-page.scss';

const { optmlAdminPage } = window;

const { siteUrl } = optmlAdminPage;
const iframe = document.getElementById( 'om-dam' );

iframe.addEventListener( 'load', function() {
	document.querySelector( '.om-dam-loader' ).style.display = 'none';
	iframe.style.display = '';
});

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

	iframe.contentWindow.postMessage({ siteUrl, type: 'om-dam', context: 'browse' }, '*' );
});
