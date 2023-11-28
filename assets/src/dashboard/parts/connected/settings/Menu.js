/**
 * External dependencies.
 */
import classnames from 'classnames';

import {
	chevronDown,
	chevronUp
} from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import { Icon } from '@wordpress/components';

const { strings } = optimoleDashboardApp;

const menuItems = [
	{
		label: strings.general_settings_menu_item,
		value: 'general'
	},
	{
		label: strings.advanced_settings_menu_item,
		value: 'compression',
		children: [
			{
				label: strings.settings_compression_menu_item,
				value: 'compression'
			},
			{
				label: strings.settings_resize_menu_item,
				value: 'resize'
			},
			{
				label: strings.lazyload_settings_menu_item,
				value: 'lazyload'
			},
			{
				label: strings.settings_exclusions_menu_item,
				value: 'exclusions'
			}
		]
	},
	{
		label: strings.cloud_library,
		value: 'cloud_library'
	},
	{
		label: strings.image_storage,
		value: 'offload_media'
	}
];

const SubMenu = ({
	children,
	tab,
	setTab
}) => {
	return (
		<ul className="flex flex-col gap-1 m-0 mt-3">
			{ children.map( item => (
				<li
					key={ item.value }
					className={ classnames(
						'not-italic font-normal text-base text-purple-gray cursor-pointer rounded px-4 py-2 m-0 hover:text-info',
						{
							'bg-light-blue hover:text-purple-gray': tab === item.value
						}
					) }
					onClick={ e => {
						e.stopPropagation();
						setTab( item.value );
					} }
				>
					{ item.label }
				</li>
			) ) }
		</ul>
	);
};

const Menu = ({
	tab,
	setTab
}) => {
	return (
		<div className="basis-1/5">
			<ul className="flex flex-col m-0 gap-3">
				{ menuItems.map( item => {
					const isActive = tab === item.value || ( item.children && item.children.some( child => child.value === tab ) );

					return (
						<li
							key={ item.value }
							className={ classnames(
								'not-italic font-semibold text-base text-purple-gray cursor-pointer hover:text-info',
								{
									'!text-info': isActive
								}
							) }
							onClick={ () => setTab( item.value ) }
						>
							<div className="flex items-center">
								{ item.label }

								{ item.children && (
									<Icon
										icon={ isActive ? chevronUp : chevronDown }
										className="inline-block ml-2"
									/>
								) }
							</div>

							{ ( item.children && isActive ) && (
								<SubMenu
									children={ item.children }
									tab={ tab }
									setTab={ setTab }
								/>
							) }
						</li>
					);
				}) }
			</ul>
		</div>
	);
};

export default Menu;
