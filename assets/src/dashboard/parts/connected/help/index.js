/**
 * External dependencies.
 */
import {
	commentContent,
	formatListBullets,
	lifesaver
} from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import {
	ExternalLink,
	Icon
} from '@wordpress/components';

const HELP_SECTION = [
	{
		title: optimoleDashboardApp.strings.help.get_support_title,
		description: optimoleDashboardApp.strings.help.get_support_desc,
		cta: optimoleDashboardApp.strings.help.get_support_cta,
		link: 'https://wordpress.org/support/plugin/optimole-wp/',
		icon: lifesaver
	},
	{
		title: optimoleDashboardApp.strings.help.feat_request_title,
		description: optimoleDashboardApp.strings.help.feat_request_desc,
		cta: optimoleDashboardApp.strings.help.feat_request_cta,
		link: 'https://github.com/Codeinwp/optimole-wp/discussions',
		icon: commentContent
	},
	{
		title: optimoleDashboardApp.strings.help.feedback_title,
		description: optimoleDashboardApp.strings.help.feedback_desc,
		cta: optimoleDashboardApp.strings.help.feedback_cta,
		link: 'https://wordpress.org/plugins/optimole-wp/#developers',
		icon: formatListBullets
	}
];

const DOCS_SECTION = [
	{
		title: optimoleDashboardApp.strings.help.account_title,
		items: [
			{
				label: optimoleDashboardApp.strings.help.account_item_one,
				value: optimoleDashboardApp.docs_links.visitors_count
			},
			{
				label: optimoleDashboardApp.strings.help.account_item_two,
				value: optimoleDashboardApp.docs_links.plan_limits
			},
			{
				label: optimoleDashboardApp.strings.help.account_item_three,
				value: optimoleDashboardApp.docs_links.visitors_plan
			}
		]
	},
	{
		title: optimoleDashboardApp.strings.help.image_processing_title,
		items: [
			{
				label: optimoleDashboardApp.strings.help.image_processing_item_one,
				value: optimoleDashboardApp.docs_links.getting_started
			},
			{
				label: optimoleDashboardApp.strings.help.image_processing_item_two,
				value: optimoleDashboardApp.docs_links.webp
			},
			{
				label: optimoleDashboardApp.strings.help.image_processing_item_three,
				value: optimoleDashboardApp.docs_links.watermarks
			}
		]
	},
	{
		title: optimoleDashboardApp.strings.help.api_title,
		items: [
			{
				label: optimoleDashboardApp.strings.help.api_item_one,
				value: optimoleDashboardApp.docs_links.cloud_library
			},
			{
				label: optimoleDashboardApp.strings.help.api_item_two,
				value: optimoleDashboardApp.docs_links.exclusions
			},
			{
				label: optimoleDashboardApp.strings.help.api_item_three,
				value: optimoleDashboardApp.docs_links.custom_integration
			}
		]
	}
];

const cardClasses = 'flex p-6 border-2 border-solid border-grayish-blue rounded-md items-start gap-8 basis-1/3';

const Help = () => {
	return (
		<div className="bg-white p-8 border-0 rounded-lg shadow-md">
			<p className="font-bold text-base">{ optimoleDashboardApp.strings.help.section_one_title }</p>

			<div className="flex gap-5 flex-col md:flex-row">
				{ HELP_SECTION.map( ( card, i ) => {
					return (
						<div
							key={ i }
							className={ cardClasses }
						>
							<div className="flex w-full flex-col gap-2">
								<Icon
									icon={ card.icon }
									className="fill-info"
									size={ 42 }
								/>

								<div className="font-bold text-base py-2 text-gray-800">
									{ card.title }
								</div>

								<div className="not-italic font-normal text-s text-gray-800">
									{ card.description }
								</div>

								<ExternalLink href={ card.link }>{ card.cta }</ExternalLink>
							</div>
						</div>
					);
				}) }
			</div>

			<hr className="my-8 border-grayish-blue"/>

			<div className="flex items-center justify-between">
				<p className="font-bold text-base">{ optimoleDashboardApp.strings.help.section_two_title }</p>

				<ExternalLink href={ optimoleDashboardApp.docs_links.home }>{ optimoleDashboardApp.strings.help.section_two_sub }</ExternalLink>
			</div>

			<div className="flex gap-5 flex-col md:flex-row">
				{ DOCS_SECTION.map( ( card, i ) => {
					return (
						<div
							key={ i }
							className={ cardClasses }
						>
							<div className="flex w-full flex-col gap-2">
								<div className="font-bold text-base py-2 text-gray-800">
									{ card.title }
								</div>

								{ card.items.map( ( item, i ) => {
									return (
										<a
											key={ i }
											href={ item.value }
											target="_blank"
										>
											{ item.label }
										</a>
									);
								}) }
							</div>
						</div>
					);
				}) }
			</div>
		</div>
	);
};

export default Help;
