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
import Sidebar from "./Sidebar";

const ConnectedLayout = ({
	tab
}) => {
	const [ showConflicts, setShowConflicts ] = useState( false );

	const {
		isConnected,
		hasApplication,
		hasConflicts
	} = useSelect( select => {
		const {
			isConnected,
			hasApplication,
			getConflicts
		} = select( 'optimole' );

		const conflicts = getConflicts();

		return {
			isConnected: isConnected(),
			hasApplication: hasApplication(),
			hasConflicts: conflicts.count > 0 || 0
		};
	} );

	const { retrieveConflicts } = useDispatch( 'optimole' );

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

				{ tab === 'settings' && <Settings /> }
			</div>

			<Sidebar/>
		</div>
	);
}

export default ConnectedLayout;

// TODO: Show upgrade based on should_show_upgrade
// TODO: Add Help Layout
// TODO: Responsiveness Testing
// TODO: Add Autoconnect
