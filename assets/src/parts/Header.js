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

import { rotateRight } from "@wordpress/icons";

/**
 * Internal dependencies.
 */
import { connected, disconnected } from "../utils/icons";

const Header = () => {
	const {
		requestStatsUpdate,
		setShowDisconnect
	} = useDispatch( 'optimole' );

	const {
		isConnected,
		isLoading
	} = useSelect( select => {
		const {
			isConnected,
			isLoading
		} = select( 'optimole' );

		return {
			isConnected: isConnected(),
			isLoading: isLoading(),
		};
	} );

	return (
		<header className="bg-white shadow-sm px-2.5 py-5">
			<div className="flex justify-between items-center max-w-screen-xl mx-auto my-0">
				<div className="items-center flex justify-start cursor-default">
					<img
						className="max-width-64 mr-3"
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
		</header>
	)
}

export default Header;
