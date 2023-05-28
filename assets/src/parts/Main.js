/**
 * WordPress dependencies.
 */
import { useSelect } from "@wordpress/data";

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
			<Header/>

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
				<ConnectedLayout />
			) }

			<Footer/>
		</>
	);
};

export default Main;
