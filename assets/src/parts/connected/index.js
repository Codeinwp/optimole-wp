/**
 * WordPress dependencies.
 */
import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import {
	useEffect,
	useState
} from "@wordpress/element";

/**
 * Internal dependencies.
 */
import Dashboard from "./dashboard";
import Conflicts from "./conflicts";
import Settings from "./settings";
import Help from "./help";
import Sidebar from "./Sidebar";
import { retrieveConflicts } from "../../utils/api";

const ConnectedLayout = ({
	tab,
	setTab
}) => {
	const [ showConflicts, setShowConflicts ] = useState( false );
	const [ menu, setMenu ] = useState( 'general' );

	const {
		isConnected,
		hasApplication,
		hasConflicts
	} = useSelect( select => {
		const {
			isConnected,
			hasApplication,
			getConflicts,
		} = select( 'optimole' );

		const conflicts = getConflicts();

		return {
			isConnected: isConnected(),
			hasApplication: hasApplication(),
			hasConflicts: conflicts.count > 0 || 0
		};
	} );

	const { setQueryArgs } = useDispatch( 'optimole' );

	useEffect( () => {
		const urlSearchParams = new URLSearchParams( window.location.search );
		const params = Object.fromEntries( urlSearchParams.entries() );
		let images = [];

		if ( Object.prototype.hasOwnProperty.call( params, 'optimole_action') ) {
			for ( let i = 0; i < 20; i++ ) {
				if ( Object.prototype.hasOwnProperty.call( params, i ) ) {
					if ( !isNaN( params[i]) ) {
						images[i] = parseInt( params[i], 10 );
					}
				}
			}

			params.images = images;
			params.url = window.location.href.split('?')[0] + ( Object.prototype.hasOwnProperty.call( params, 'paged' ) ? '?paged=' + params['paged'] : '' );
			setQueryArgs( params );
			setTab( 'settings' );
			setMenu( 'offload_media' );
		}
	}, []);

	useEffect( () => {
		if ( isConnected && hasApplication ) {
			retrieveConflicts();
		}
	}, [] );

	useEffect( () => {
		if ( hasConflicts ) {
			setShowConflicts( true );
		}
	}, [ hasConflicts ] );

	return (
		<div className="optml-connected max-w-screen-xl flex flex-col lg:flex-row mx-auto gap-5">
			<div
				className="flex flex-col justify-between mt-8 mb-5 p-0 transition-all ease-in-out duration-700 relative text-gray-700 basis-9/12"
			>
				{ tab === 'dashboard' && <Dashboard /> }

				{ ( tab === 'conflicts' && showConflicts ) && <Conflicts /> }

				{ tab === 'settings' && (
					<Settings
						tab={ menu }
						setTab={ setMenu }
					/>
				) }

				{ tab === 'help' && <Help /> }
			</div>

			<Sidebar/>
		</div>
	);
}

export default ConnectedLayout;
