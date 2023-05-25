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
		<header className="optml-header">
			<div className="optml-header__container">
				<div className="optml-header__logo .cursor">
					<img
						class="spacing__right"
						src={ `${ optimoleDashboardApp.assets_url }/img/logo.png` }
						alt={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
						title={ optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.service_details }
					/>
					<h3 className="text__font text__size-3 text__bold">{ optimoleDashboardApp.strings.optimole }</h3>
				</div>

				<div className="optml-header__status">
					{ isConnected ? (
						<>
							<div className="text text__uppercase text__bold text__size-7 color__success spacing__right">
								{ optimoleDashboardApp.strings.connected }
							</div>

							<Icon icon={ connected } />
						</>
					) : (
						<>
							<div className="text text__uppercase text__bold text__size-7 color__danger spacing__right">
								{ optimoleDashboardApp.strings.not_connected }
							</div>

							<Icon icon={ disconnected } />
						</>
					) }
				</div>

				{/* <nav class="otter-navigation">
					<button class="is-active"><span>Dashboard</span></button>
					<button class=""><span>Integrations</span></button>
					<button class=""><span>Free vs PRO</span></button>
					<button class=""><span>Feedback</span></button>
				</nav> */}
			</div>
		</header>
	)
}

export default Header;
