import { createRoot, render } from '@wordpress/element';
import App from './App';
import './style.scss';

const container = document.getElementById( 'optimole-dashboard-widget-root' );

// createRoot ships with WordPress 6.2 (React 18).
if ( createRoot ) {
	createRoot( container ).render( <App /> );
} else {
	render( <App />, container );
}
