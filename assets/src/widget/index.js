import { createRoot } from '@wordpress/element';
import App from './App';
import './style.scss';

const root = createRoot( document.getElementById( 'optimole-dashboard-widget-root' ) );

root.render( <App /> );
