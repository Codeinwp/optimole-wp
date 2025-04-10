import { Icon } from '@wordpress/components';
import { closeSmall, check } from '@wordpress/icons';

const OptimizationStatus = ({ settings }) => {
	const lazyloadEnabled = 'enabled' === settings?.lazyload;
	const imageHandlingEnabled = 'enabled' === settings?.image_replacer;
	const statuses = [
		{
			active: imageHandlingEnabled,
			label: optimoleDashboardApp.strings.optimization_status.statusTitle1,
			description: optimoleDashboardApp.strings.optimization_status.statusSubTitle1
		},
		{
			active: lazyloadEnabled,
			label: optimoleDashboardApp.strings.optimization_status.statusTitle2,
			description: optimoleDashboardApp.strings.optimization_status.statusSubTitle2
		},
		{
			active: lazyloadEnabled && 'disabled' === settings?.scale,
			label: optimoleDashboardApp.strings.optimization_status.statusTitle3,
			description: optimoleDashboardApp.strings.optimization_status.statusSubTitle3
		}
	].map( el => ({
		...el, active: imageHandlingEnabled && el.active
	}) );

	return (
		<div className="bg-white flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
			<h3 className="text-lg mt-0">{ optimoleDashboardApp.strings.optimization_status.title }</h3>
			<ul className="grid gap-3 m-0">
				{ statuses.map( ( status, index ) => (
					<li
						key={index}
						className="flex items-start gap-2"
					>
						{status.active ? (
							<Icon icon={check} className="fill-success bg-success/20 rounded-full" size={20} />
						) : (
							<Icon icon={closeSmall} className="fill-danger bg-danger/20 rounded-full" size={20} />
						)}
						<div>
							<span className='text-gray-700 font-normal font-semibold'>
								{status.label}
							</span>

							<p className="m-0">{status.description}</p>
						</div>
					</li>
				) ) }
			</ul>
			<p
				className="m-0 mt-5"
				dangerouslySetInnerHTML={ {
					__html: optimoleDashboardApp.strings.optimization_tips
				} }
			/>
		</div>
	);
};

export default OptimizationStatus;
