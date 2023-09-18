/**
 * WordPress dependencies.
 */
import {
	useDispatch,
	useSelect
} from '@wordpress/data';

import {
	useEffect,
	useState
} from '@wordpress/element';

/**
 * Internal dependencies.
 */
import Dashboard from './dashboard';
import Conflicts from './conflicts';
import Settings from './settings';
import Help from './help';
import Sidebar from './Sidebar';
import CSAT from './CSAT';
import { retrieveConflicts } from '../../utils/api';

const ConnectedLayout = ({
	tab,
	setTab
}) => {
	const {
		isConnected,
		hasApplication,
		hasConflicts,
		siteSettings,
		extraVisits
	} = useSelect( select => {
		const {
			isConnected,
			hasApplication,
			getConflicts,
			getSiteSettings
		} = select( 'optimole' );

		const conflicts = getConflicts();
		const siteSettings = getSiteSettings();

		return {
			isConnected: isConnected(),
			hasApplication: hasApplication(),
			hasConflicts: 0 < conflicts.count || 0,
			siteSettings,
			extraVisits: siteSettings['banner_frontend']
		};
	});

	const { setQueryArgs } = useDispatch( 'optimole' );

	const [ showConflicts, setShowConflicts ] = useState( false );
	const [ menu, setMenu ] = useState( 'general' );
	const [ canSave, setCanSave ] = useState( false );
	const [ settings, setSettings ] = useState( siteSettings );

	useEffect( () => {
		const urlSearchParams = new URLSearchParams( window.location.search );
		const params = Object.fromEntries( urlSearchParams.entries() );
		let images = [];

		if ( Object.prototype.hasOwnProperty.call( params, 'optimole_action' ) ) {
			for ( let i = 0; 20 > i; i++ ) {
				if ( Object.prototype.hasOwnProperty.call( params, i ) ) {
					if ( ! isNaN( params[i]) ) {
						images[i] = parseInt( params[i], 10 );
					}
				}
			}

			params.images = images;
			params.url = window.location.href.split( '?' )[0] + ( Object.prototype.hasOwnProperty.call( params, 'paged' ) ? '?paged=' + params.paged : '' );
			setQueryArgs( params );
			setTab( 'settings' );
			setMenu( 'offload_media' );
		}
	}, []);

	useEffect( () => {
		if ( isConnected && hasApplication ) {
			retrieveConflicts();
		}
	}, []);

	useEffect( () => {
		if ( hasConflicts ) {
			setShowConflicts( true );
		}
	}, [ hasConflicts ]);

	useEffect( () => {
		setSettings({
			...settings,
			banner_frontend: extraVisits
		});
	}, [ extraVisits ]);

	useEffect( () => {

		// Confirmation message saying changes might not be saved if user leaves the page.
		if ( canSave ) {
			window.onbeforeunload = () => true;
		} else {
			window.onbeforeunload = null;
		}
	}, [ canSave ]);

	return (
		<>
			<div className="optml-connected max-w-screen-xl flex flex-col lg:flex-row mx-auto gap-5">
				<div
					className="flex flex-col justify-between mt-8 mb-5 p-0 transition-all ease-in-out duration-700 relative text-gray-700 basis-9/12"
				>
					{ 'dashboard' === tab && <Dashboard /> }

					{ ( 'conflicts' === tab && showConflicts ) && <Conflicts /> }

					{ 'settings' === tab && (
						<Settings
							tab={ menu }
							setTab={ setMenu }
							canSave={ canSave }
							setCanSave={ setCanSave }
							settings={ settings }
							setSettings={ setSettings }
						/>
					) }

					{ 'help' === tab && <Help /> }
				</div>

				<Sidebar/>
			</div>

			<CSAT />
		</>
	);
};

export default ConnectedLayout;
