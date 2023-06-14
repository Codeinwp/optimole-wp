/**
 * Internal dependencies.
 */
import actions from './actions';
import reducer from './reducer';
import selectors from './selectors';

if ( undefined !== wp.data.createReduxStore ) {
	const store = wp.data.createReduxStore( 'optimole', {
		reducer,
		actions,
		selectors
	});

	wp.data.register( store );
} else {
	wp.data.registerStore( 'optimole', {
		reducer,
		actions,
		selectors
	});
}
