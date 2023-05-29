/**
 * WordPress dependencies.
 */
import { useSelect } from "@wordpress/data";

import { useState } from "@wordpress/element";

/**
 * Internal dependencies.
 */
import Header from './Header';
import DisconnectLayout from './disconnect';
import ConnectLayout from './connect';
import ConnectingLayout from "./connecting";
import ConnectedLayout from "./connected";
import Footer from './Footer';

const Main = () => {
	const [ tab, setTab ] = useState( 'dashboard' );

	const {
		autoConnect,
		showDisconnect,
		isConnected,
		hasApplication,
		hasDashboardLoaded
	} = useSelect( select => {
		const {
			getAutoConnect,
			showDisconnect,
			isConnected,
			hasApplication,
			hasDashboardLoaded
		} = select( 'optimole' );

		return {
			autoConnect: getAutoConnect(),
			showDisconnect: showDisconnect(),
			isConnected: isConnected(),
			hasApplication: hasApplication(),
			hasDashboardLoaded: hasDashboardLoaded()
		};
	} );

	return (
		<>
			<Header
				tab={ tab }
				setTab={ setTab }
			/>

			{ ( ! isConnected && ! autoConnect ) && (
				<ConnectLayout />
			) }

			{ ( ( isConnected || autoConnect ) && ! hasDashboardLoaded ) && (
				<ConnectingLayout />
			) }

			{ ( isConnected && showDisconnect ) && (
				<DisconnectLayout />
			) }

			{ ( isConnected && hasApplication && hasDashboardLoaded ) && (
				<ConnectedLayout
					tab={ tab }
				/>
			) }

			<Footer/>
		</>
	);
};

export default Main;
