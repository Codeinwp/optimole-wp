/* global optimoleDashboardApp */

import ProgressBar from './ProgressBar';
import { sync } from '../../utils/icons';
import { Icon } from '@wordpress/icons';

export default function ProgressTile({ title, progress, description, onCancel, hideCancel = false }) {
	const { strings } = optimoleDashboardApp;
	return (
		<div className="bg-gray-50 rounded-md p-4 space-y-4">
			<div className="flex gap-3 items-center">
				<Icon icon={sync} className="text-info animate-spin -scale-y-100"/>
				{title && <p className="uppercase text-s font-semibold text-light-black m-0">{title}</p>}

				{! hideCancel && <button onClick={onCancel} className="appearance-none border border-info text-info bg-transparent rounded px-4 py-2 ml-auto text-s hover:border-red-500 hover:bg-red-500 hover:text-white cursor-pointer">{strings.cancel}</button>}
			</div>

			<ProgressBar value={progress} />

			{description && <p className="text-xs text-gray-500">{description}</p>}
		</div>
	);
}
