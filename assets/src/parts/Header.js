/**
 * External dependencies.
 */
import classnames from "classnames";

import { rotateRight } from "@wordpress/icons";

/**
 * WordPress dependencies.
 */
import { 
	DropdownMenu,
	Icon
} from "@wordpress/components";

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
import { connected, disconnected } from "../utils/icons";

const Header = ({
	tab,
	setTab
}) => {
	const [ showConflicts, setShowConflicts ] = useState( false );

	const {
		requestStatsUpdate,
		setShowDisconnect
	} = useDispatch( 'optimole' );

	const {
		isConnected,
		isLoading,
		hasApplication,
		hasDashboardLoaded,
		hasConflicts,
	} = useSelect( select => {
		const {
			isConnected,
			isLoading,
			hasApplication,
			hasDashboardLoaded,
			getConflicts
		} = select( 'optimole' );

		const conflicts = getConflicts();

		return {
			isConnected: isConnected(),
			isLoading: isLoading(),
			hasApplication: hasApplication(),
			hasDashboardLoaded: hasDashboardLoaded(),
			hasConflicts: conflicts.count > 0 || 0
		};
	} );

	useEffect( () => {
		if ( hasConflicts ) {
			setShowConflicts( true );
		}
	}, [ hasConflicts ] );

	const tabs = [
		{
			label: optimoleDashboardApp.strings.dashboard_menu_item,
			value: 'dashboard'
		},
		{
			label: optimoleDashboardApp.strings.conflicts_menu_item,
			value: 'conflicts',
			isDisabled: ! showConflicts
		},
		{
			label: optimoleDashboardApp.strings.settings_menu_item,
			value: 'settings'
		},
		{
			label: optimoleDashboardApp.strings.help_menu_item,
			value: 'help'
		}
	];

	return (
		<header className="bg-white shadow-sm px-2.5 py-5 pb-0">
			<div className="flex justify-between items-center max-w-screen-xl mx-auto my-0 pb-5">
				<div className="items-center flex justify-start cursor-default">
					<img
						className="max-width-64 w-16 h-16 mr-3"
						src={ `${ optimoleDashboardApp.assets_url }/img/logo.png` }
						alt={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
						title={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
					/>
					<h3 className="text-gray-800 text-2 font-serif font-bold">{ optimoleDashboardApp.strings.optimole }</h3>
				</div>

				<div className="flex items-center">
					{ isConnected ? (
						<>
							<DropdownMenu
								icon={ isLoading ? <Icon icon={ rotateRight } className="animate-spin fill-success" /> : <Icon icon={ connected } /> }
								toggleProps={ {
									className: 'optml-header__dropdown uppercase font-bold text-xs text-success hover:text-success active:text-success focus:text-success mr-3 border border-gray-300 rounded-md border-solid gap-2',
									iconPosition: 'right',
								} }
								popoverProps={ {
									className: 'optml-header__dropdown__popover',
								} }
								text={ optimoleDashboardApp.strings.connected }
								controls={ [
									{
										title: optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.dashboard_menu_item,
										onClick: () => window.open( 'https://dashboard.optimole.com/', '_blank' ),
									},
									{
										title: optimoleDashboardApp.strings.refresh_stats_cta,
										onClick: requestStatsUpdate,
									},
									{
										title: optimoleDashboardApp.strings.disconnect_btn,
										onClick: () => setShowDisconnect( true ),
									},
								] }
							/>
						</>
					) : (
						<>
							<div className="uppercase font-bold text-xs text-danger mr-3">
								{ optimoleDashboardApp.strings.not_connected }
							</div>

							<Icon icon={ disconnected } />
						</>
					) }
				</div>
			</div>

			{ ( isConnected && hasApplication && hasDashboardLoaded ) && (
				<ul className="flex gap-8 items-center max-w-screen-xl mx-auto my-0">
					{ tabs.filter( tab => ! tab?.isDisabled ).map( ( { label, value } ) => (
						<li
							key={ value }
							className={ classnames(
								'cursor-pointer text-purple-gray font-bold text-lg m-0 py-2 px-3 transition-colors duration-200 ease-in-out border-0 border-b-4 border-b-white border-solid',
								{
									'text-black !border-b-info': tab === value,
								}
							) }
							onClick={ () => setTab( value ) }
						>
							{ label }
						</li>
					) ) }
				</ul>
			) }
		</header>
	)
}

export default Header;
