import { Icon } from '@wordpress/components';
import { external } from '@wordpress/icons';

import { quota } from '../../dashboard/utils/icons';
import ProgressBar from '../../dashboard/parts/components/ProgressBar';

export default function Usage() {

	const { serviceData } = optimoleDashboardWidget;

	const {
		visitors_pretty,
		visitors_limit_pretty,
		visitors_limit,
		visitors
	} = serviceData;

	const progress = Math.round( ( visitors / visitors_limit ) * 100 );

	return (
		<div className="flex items-center gap-4 p-4 bg-light-blue border border-blue-300 rounded-md">
			<span className="flex items-center justify-center w-12 h-12">
				<Icon icon={ quota } />
			</span>

			<div className="flex flex-col w-full gap-2">
				<div className="flex items-center gap-2">
					<span className="text-lg text-gray-800 font-bold">
						{ visitors_pretty } / { visitors_limit_pretty }
					</span>

					<span className="text-sm text-gray-600">
						{ optimoleDashboardWidget.i18n.monthlyVisitsQuota }
					</span>
				</div>


				<div className="w-full relative flex items-center gap-2">
					<ProgressBar value={ visitors } max={ visitors_limit } colorOverage={true} />
					<span className="font-semibold text-sm text-gray-800">
						{ progress }%
					</span>
				</div>

				{ 70 < progress && (
					<a href={ optimoleDashboardWidget.billingURL } target="_blank" rel="noopener noreferrer" className="font-semibold text-sm hover:text-dark-blue text-info flex items-center gap-1">
						{ optimoleDashboardWidget.i18n.upgrade }
						<Icon icon={ external } className="w-5 h-5 fill-current transition-colors duration-300"/>
					</a>
				)}
			</div>
		</div>
	);
}
