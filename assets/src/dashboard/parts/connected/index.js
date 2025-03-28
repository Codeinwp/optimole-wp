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
import formbricks from '@formbricks/js/app';
import BlackFridayBanner from '../components/BlackFridayBanner';
if ( 'undefined' !== typeof window && optimoleDashboardApp.user_data.plan ) {
	formbricks.init({
		environmentId: 'clo8wxwzj44orpm0gjchurujm',
		apiHost: 'https://app.formbricks.com',
		userId: 'optml_' + ( optimoleDashboardApp.user_data.id ),
		attributes: {
			plan: optimoleDashboardApp.user_data.plan,
			status: optimoleDashboardApp.user_data.status,
			language: optimoleDashboardApp.language,
			cname_assigned: optimoleDashboardApp.user_data.is_cname_assigned || 'no',
			connected_websites: optimoleDashboardApp.user_data.whitelist && optimoleDashboardApp.user_data.whitelist.length.toString(),
			traffic: convertToCategory( optimoleDashboardApp.user_data.traffic || 0, 500 ).toString(),
			images_number: convertToCategory( optimoleDashboardApp.user_data.images_number || 0, 100 ).toString(),
			days_since_install: convertToCategory( optimoleDashboardApp.days_since_install ).toString()
		}
	});

}
function convertToCategory( number, scale = 1 ) {

	const normalizedNumber = Math.round( number / scale );
	if ( 0 === normalizedNumber || 1 === normalizedNumber ) {
		return 0;
	} else if ( 1 < normalizedNumber && 8 > normalizedNumber ) {
		return 7;
	} else if ( 8 <= normalizedNumber && 31 > normalizedNumber ) {
		return 30;
	} else if ( 30 < normalizedNumber && 90 > normalizedNumber ) {
		return 90;
	} else if ( 90 < normalizedNumber ) {
		return 91;
	}
}
const ConnectedLayout = ({
	tab,
	setTab
}) => {
	const {
		isConnected,
		hasApplication,
		hasConflicts,
		siteSettings,
		extraVisits,
		plan
	} = useSelect( select => {
		const {
			isConnected,
			hasApplication,
			getConflicts,
			getSiteSettings,
			getUserData
		} = select( 'optimole' );

		const conflicts = getConflicts();
		const siteSettings = getSiteSettings();
		const user = getUserData();

		return {
			plan: user?.plan,
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

	const showBFBanner = 'free' === plan && optimoleDashboardApp?.bf_notices?.banner;

	return (
		<div className="optml-connected 2xl:max-w-screen-xl max-w-screen px-4 mx-auto">
			{showBFBanner && <BlackFridayBanner/>}

			<div className="flex flex-col xl:flex-row mx-auto gap-5">
				<div
					className="flex flex-col justify-between mt-8 xl:mb-5 p-0 transition-all ease-in-out duration-700 relative text-gray-700 basis-8/12 2xl:basis-9/12"
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
		</div>
	);
};

export default ConnectedLayout;
