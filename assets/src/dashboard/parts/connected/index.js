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
import BlackFridayBanner from '../components/BlackFridayBanner';

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
					className="flex flex-col mt-8 xl:mb-5 p-0 transition-all ease-in-out duration-700 relative text-gray-700 grow"
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

				<Sidebar settings={settings}/>
			</div>

			<CSAT />
		</div>
	);
};

export default ConnectedLayout;
