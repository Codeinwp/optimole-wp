/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import Header from './Header';
import DisconnectLayout from './disconnect';
import ConnectLayout from './connect';
import ConnectingLayout from './connecting';
import ConnectedLayout from './connected';
import Footer from './Footer';
import { highlightSidebarLink } from '../utils/helpers';
import Toasts from './connected/Toasts';

const Main = () => {
	const allowedTabs = [ 'dashboard', 'settings', 'help' ];
	const hash = window.location.hash.replace( '#', '' );
	let defaultTab = allowedTabs.includes( hash ) ? hash : 'dashboard';

	const [ tab, setTab ] = useState( defaultTab );

	const switchToSettings = ( e ) => {
		e.preventDefault();
		setTab( 'settings' );
	};

	const switchToDashboard = ( e ) => {
		e.preventDefault();
		setTab( 'dashboard' );
	};

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


	useEffect( () => {
		if ( ! isConnected ) {
			return;
		}
		window.location.hash = `#${ tab }`;
		highlightSidebarLink();
	}, [ tab ]);

	useEffect( () => {

		if ( ! isConnected ) {
			return;
		}

		const dashLink = document.querySelector( 'a[href*="page=optimole"]' );
		const settingsLink = document.querySelector( 'a[href*="page=optimole#settings"]' );

		if ( ! dashLink || ! settingsLink ) {
			return;
		}

		dashLink.addEventListener( 'click', switchToDashboard );
		settingsLink.addEventListener( 'click', switchToSettings );

		return () => {
			if ( ! dashLink || ! settingsLink ) {
				return;
			}
			dashLink.removeEventListener( 'click', switchToDashboard );
			settingsLink.removeEventListener( 'click', switchToSettings );
		};
	}, []);


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

			<Toasts />
		</>
	);
};

export default Main;
