/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import Header from './Header';
import DisconnectLayout from './disconnect';
import ConnectLayout from './connect';
import ConnectingLayout from './connecting';
import ConnectedLayout from './connected';
import Footer from './Footer';

const Main = () => {
	const [ tab, setTab ] = useState( 'dashboard' );

	const {
		showDisconnect,
		isConnected,
		hasApplication,
		hasDashboardLoaded
	} = useSelect( select => {
		const {
			showDisconnect,
			isConnected,
			hasApplication,
			hasDashboardLoaded
		} = select( 'optimole' );

		return {
			showDisconnect: showDisconnect(),
			isConnected: isConnected(),
			hasApplication: hasApplication(),
			hasDashboardLoaded: hasDashboardLoaded()
		};
	});

	return (
		<>
			<Header
				tab={ tab }
				setTab={ setTab }
			/>

			{ ( ! ( isConnected && hasApplication ) ) && (
				<>
					<ConnectLayout />
					<Footer/>
				</>
			) }

			{ ( isConnected && hasApplication && ! hasDashboardLoaded ) && (
				<ConnectingLayout />
			) }

			{ ( isConnected && showDisconnect ) && (
				<DisconnectLayout />
			) }

			{ ( isConnected && hasApplication && hasDashboardLoaded ) && (
				<ConnectedLayout
					tab={ tab }
					setTab={ setTab }
				/>
			) }
		</>
	);
};

export default Main;
