/**
 * WordPress dependencies.
 */
import { Icon } from '@wordpress/components';
import { closeSmall, check } from '@wordpress/icons';

import { useSelect } from '@wordpress/data';

const OptimizationStatus = () => {
	const {
		statuses
	} = useSelect( select => {
		const { getSiteSettings } = select( 'optimole' );

		const siteSettings = getSiteSettings();

		const statuses = [
			{
				active: 'enabled' === siteSettings?.image_replacer,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle1,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle1
			},
			{
				active: 'enabled' === siteSettings?.lazyload,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle2,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle2
			},
			{
				active: 'enabled' === siteSettings?.scale,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle3,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle3
			}
		];

		return {
			statuses: statuses
		};
	});

	return (
		<div className="bg-white flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
			<h3 className="text-base m-0">{ optimoleDashboardApp.strings.optimization_status.title }</h3>
			<ul>
				{ statuses.map( ( status, index ) => {
					let statusClass = status.active ? 'success' : 'danger';
					return (
						<li
							key={ index }
							className="flex items-start gap-2"
						>
							{ status.active ? (
								<Icon icon={check} className="fill-success bg-success/20 rounded-full" size={20} />
							) : (
								<Icon icon={closeSmall} className="fill-danger bg-danger/20 rounded-full" size={20} />
							) }

							<div>
								<span className='text-gray-700 font-normal font-semibold'>
									{ status.label }
								</span>
								<p className="m-0">{ status.description }</p>
							</div>
						</li>
					);
				}) }
			</ul>
			<p
				className="m-0 mt-3"
				dangerouslySetInnerHTML={ {
					__html: optimoleDashboardApp.strings.optimization_tips
				} }
			/>
		</div>
	);
};

export default OptimizationStatus;
