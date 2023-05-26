/**
 * WordPress dependencies.
 */
import { Icon } from "@wordpress/components";
import { useSelect } from "@wordpress/data";

/**
 * Internal dependencies.
 */
import { connected, disconnected } from "../utils/icons";

const Header = () => {
	const { isConnected } = useSelect( select => {
		const { isConnected } = select( 'optimole' );
		return {
			isConnected: isConnected(),
		};
	} );

	return (
		<header className="bg-white shadow-sm px-2.5 py-5">
			<div className="flex justify-between items-center max-w-screen-xl mx-auto my-0">
				<div className="items-center flex justify-start cursor-default">
					<img
						class="max-width-64 mr-3"
						src={ `${ optimoleDashboardApp.assets_url }/img/logo.png` }
						alt={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
						title={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
					/>
					<h3 className="text-gray-800 text-2 font-serif font-bold">{ optimoleDashboardApp.strings.optimole }</h3>
				</div>

				<div className="flex items-center">
					{ isConnected ? (
						<>
							<div className="uppercase font-bold text-xs text-success mr-3">
								{ optimoleDashboardApp.strings.connected }
							</div>

							<Icon icon={ connected } />
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
