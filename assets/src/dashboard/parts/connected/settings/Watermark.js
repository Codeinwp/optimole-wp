/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button
} from '@wordpress/components';

import { Icon, external } from '@wordpress/icons';

const Watermark = () => {
	const { options_strings } = optimoleDashboardApp.strings;

	const containerStyle = {
		border: '2px solid #DCDCDC',
		borderRadius: '8px',
		padding: '16px'
	};

	return (
		<>
			<div>
				<h1 className="text-xl font-bold">
					{options_strings.watermark_media_title}
				</h1>
				<p className="text-gray-600 mt-2 mb-2" >
					{options_strings.watermark_media_desc}
				</p>
			</div>

			<div style={containerStyle}>
				<div className="mb-4 space-y-2">
					<div className="flex items-center gap-2 align-center">
						<div className="w-2 h-2 bg-gray-900 rounded-full flex-shrink-0"></div>
						<span className="text-gray-700 text-left">{options_strings.watermark_info_1}</span>
					</div>
					<div className="flex items-center gap-2 align-center">
						<div className="w-2 h-2 bg-gray-900 rounded-full flex-shrink-0"></div>
						<span className="text-gray-700 text-left ">{options_strings.watermark_info_2}</span>
					</div>
					<div className="flex items-center gap-2 align-center">
						<div className="w-2 h-2 bg-gray-900 rounded-full flex-shrink-0"></div>
						<span className="text-gray-700 text-left">{options_strings.watermark_info_3}</span>
					</div>
				</div>

				<div className="flex gap-4 mb-4">
					<Button
						variant="primary"
						className="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2 hover:bg-blue-700"
						target="_blank"
						rel="noopener noreferrer"
						href="https://dashboard.optimole.com/watermark"
					>
						<Icon
							icon={external}
							className="w-4 h-4"
						/>
						{options_strings.open_optimole_dashboard}
					</Button>
					<Button
						variant="secondary"
						className="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50"
						target="_blank"
						rel="noopener noreferrer"
						href="https://docs.optimole.com/article/1475-adding-watermarks-to-your-images"
					>
						{options_strings.learn_more}
					</Button>
				</div>
				<span className="text-gray-500">
					{options_strings.watermark_footer}
				</span>
			</div>
		</>
	);
};

export default Watermark;
