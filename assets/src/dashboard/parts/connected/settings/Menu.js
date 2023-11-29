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
		<ul className="flex flex-col gap-1 m-0 mt-1">
			{children.map( item => {
				const { value, label } = item;
				const classes = classnames(
					{
						'bg-light-blue hover:text-purple-gray': tab === value,
						'bg-transparent hover:text-info': tab !== value
					},
					'w-full text-left border-0 font-normal text-base text-purple-gray cursor-pointer rounded px-4 py-2 m-0 hover:text-info'
				);
				return (
					<li key={value}>
						<button className={classes} onClick={() => setTab( value )}>
							{label}
						</button>
					</li>
				);
			})}
		</ul>
	);
};

const Menu = ({
	tab,
	setTab
}) => {
	return (
		<div className="basis-1/5">
			<ul className="grid m-0 gap-1">
				{menuItems.map( item => {
					const isActive = tab === item.value || ( item.children && item.children.some( child => child.value === tab ) );
					const buttonClasses = classnames({ '!text-info': isActive }, 'w-full bg-transparent border-0 flex items-center appearance-none not-italic font-semibold text-base text-purple-gray cursor-pointer hover:text-info py-2' );
					return (
						<li key={item.value} className='m-0'>
							<button
								className={buttonClasses}
								onClick={() => setTab( item.value )}
							>
								{item.label}
								{item.children && (
									<Icon
										icon={isActive ? chevronUp : chevronDown}
										className="inline-block ml-2 fill-current"
									/>
								)}
							</button>


							{( item.children && isActive ) && (
								<SubMenu
									children={item.children}
									tab={tab}
									setTab={setTab}
								/>
							)}
						</li>
					);
				})}
			</ul>
		</div>
	);
};

export default Menu;
