/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	TextControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { addQueryArgs } from '@wordpress/url';

const reasons = [
	optimoleDashboardApp.strings.upgrade.reason_1,
	optimoleDashboardApp.strings.upgrade.reason_2,
	optimoleDashboardApp.strings.upgrade.reason_3,
	optimoleDashboardApp.strings.upgrade.reason_4
];

const Sidebar = () => {
	const {
		name,
		domain,
		plan
	} = useSelect( select => {
		const { getUserData } = select( 'optimole' );

		const user = getUserData();

		let domain = user?.cdn_key + '.i.optimole.com';
		if ( user?.domain !== undefined && '' !== user?.domain ) {
			domain = user?.domain;
		}

		return {
			name: user?.display_name,
			domain,
			plan: user?.plan
		};
	});

	return (
		<div className="grid md:grid-cols-2 xl:flex xl:flex-col xl:mt-8 xl:mb-5 p-0 transition-all ease-in-out duration-700 gap-5 basis-4/12 2xl:basis-3/12">
			<div className="bg-white gap-5 flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
				<TextControl
					label={ optimoleDashboardApp.strings.logged_in_as }
					value={ name }
					className="optml__placeholder"
					disabled
				/>

				<TextControl
					label={ optimoleDashboardApp.strings.private_cdn_url }
					value={ domain }
					className="optml__placeholder"
					disabled
				/>

				<hr className="m-0 border-grayish-blue"/>

				<p className="font-semibold text-xs text-light-black m-0">{ optimoleDashboardApp.strings.looking_for_api_key }</p>

				<p
					className="m-0 -mt-3"
					dangerouslySetInnerHTML={ {
						__html: optimoleDashboardApp.strings.optml_dashboard
					} }
				/>
			</div>

			{ 'free' === plan ? (
				<div
					className="bg-info flex flex-col text-white border-0 rounded-lg overflow-hidden shadow-md bg-promo bg-no-repeat"
					style={ {
						backgroundImage: `url( ${ optimoleDashboardApp.assets_url }/img/logo2.png )`
					} }
				>
					{optimoleDashboardApp?.bf_notices?.sidebar && (
						<a href={optimoleDashboardApp.bf_notices.sidebar.cta_link} className="flex flex-col gap-3 p-3 bg-black !no-underline text-center cursor-pointer hover:opacity-90 transition-opacity" target="_blank">
							<span className="font-extrabold text-[17px] uppercase italic" dangerouslySetInnerHTML={{ __html: optimoleDashboardApp.bf_notices.sidebar.title }}/>
							<span className="text-[11px] font-bold" dangerouslySetInnerHTML={{ __html: optimoleDashboardApp.bf_notices.sidebar.subtitle }}/>
						</a>
					)}

					<div className="p-8 flex flex-col">
						<h3 className="mt-0 text-white text-lg">{ optimoleDashboardApp.strings.upgrade.title_long }</h3>
						<ul>
							{ reasons.map( ( reason, index ) => (
								<li
									key={ index }
									className="flex items-center gap-2"
								>
									<Icon icon="yes-alt" className="text-white" />
									<span className="text-white font-normal text-base">{ reason }</span>
								</li>
							) ) }
						</ul>

						<Button
							variant="link"
							className="optml__button flex w-full justify-center font-bold min-h-40 !no-underline !text-white !bg-opaque-black !rounded"
							href={ optimoleDashboardApp.optimoleHome +  'pricing' }
							target="_blank"
						>
							{ optimoleDashboardApp.strings.upgrade.cta }
						</Button>
					</div>
				</div>
			) : (
				<Button
					variant="default"
					className="bg-white flex font-bold border-0 rounded-lg shadow-md p-8 text-sm justify-center"
					href={ addQueryArgs( optimoleDashboardApp.optimoleHome + 'contact/', {
						contact_name: window.optimoleDashboardApp.user_data.display_name,
						contact_email: optimoleDashboardApp.user_data.user_email,
						contact_website: optimoleDashboardApp.home_url
					}) }
					target="_blank"
				>
					{ optimoleDashboardApp.strings.premium_support }
				</Button>
			) }
		</div>
	);
};

export default Sidebar;
