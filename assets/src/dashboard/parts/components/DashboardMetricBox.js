import { Icon } from '@wordpress/components';

import classnames from 'classnames';

export default function DashboardMetricBox({ value, label, description, icon, compact = false }) {
	const wrapClasses = classnames(
		'flex bg-light-blue border border-blue-300 rounded-md basis-1/4 flex-col items-start',
		{
			'p-4': compact,
			'p-6': ! compact
		}
	);

	return (
		<div className={wrapClasses}>
			<Icon icon={ icon } />

			<div className="flex w-full flex-col">
				<div className="not-italic font-bold text-xl py-2 text-gray-800">
					{ value }
				</div>

				<div className="not-italic font-normal text-sm text-gray-800">
					{ label }
				</div>

				<div className="font-normal text-xs text-gray-600">
					{ description }
				</div>
			</div>
		</div>
	);
}
