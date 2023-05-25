/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import './style.scss';
import './store';
import App from './parts/Main';

render( <App />, document.getElementById( 'optimole-app' ));
