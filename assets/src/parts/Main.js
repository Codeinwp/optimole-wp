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
import Footer from './Footer';

const Main = () => {
	const {
        autoConnect,
        showDisconnect,
        isConnected,
        hasDashboardLoaded
    } = useSelect( select => {
		const {
            getAutoConnect,
            showDisconnect,
            isConnected,
            hasDashboardLoaded
        } = select( 'optimole' );

		return {
            autoConnect: getAutoConnect(),
            showDisconnect: showDisconnect(),
			isConnected: isConnected(),
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

            { isConnected && showDisconnect && (
                <DisconnectLayout />
            ) }

            <Footer/>
        </>
    );
};

export default Main;

