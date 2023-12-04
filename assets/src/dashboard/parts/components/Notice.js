import classNames from 'classnames';
import { Icon, cancelCircleFilled, warning, info, check, closeSmall } from '@wordpress/icons';

import { checkmark } from '../../utils/icons';

export default function Notice({
	text,
	title,
	disableIcon = false,
	type = 'info',
	className = '',
	smallTitle = false,
	children,
	onDismiss
}) {
	const noticeClasses = classNames({
		'bg-yellow-50 text-yellow-700': 'warning' === type,
		'bg-green-50 text-green-700': 'success' === type,
		'bg-red-50 text-red-700': 'error' === type,
		'bg-blue-50 text-blue-700': 'info' === type,
		'bg-info text-white': 'primary' === type
	},
	`rounded-md my-5 text-sm p-4 om-notice om-notice-${type} relative` );

	const iconClasses = classNames({
		'fill-yellow-500': 'warning' === type,
		'fill-green-500': 'success' === type,
		'fill-red-500': 'error' === type,
		'fill-blue-500': 'info' === type,
		'fill-white': 'primary' === type,
		'h-6 w-6': 'primary' !== type,
		'h-12 w-12': 'primary' === type
	});

	const contentWrapClasses = classNames({
		'ml-3 mt-0.5': ! disableIcon && ! title,
		'mt-2 mx-1.5': title
	});

	const titleClasses = classNames({
		'text-yellow-800': 'warning' === type,
		'text-green-800': 'success' === type,
		'text-red-800': 'error' === type,
		'text-blue-800': 'info' === type,
		'font-bold text-base': 'primary' !== type,
		'text-white text-sm': 'primary' === type,
		'text-s': smallTitle
	}, 'm-0' );

	const icons = {
		'warning': warning,
		'success': check,
		'error': cancelCircleFilled,
		'info': info,
		'primary': checkmark
	};


	return (
		<div className={noticeClasses}>

			{onDismiss && (
				<button
					className="flex items-center justify-center absolute bg-transparent text-current right-1 top-1 border-0 rounded-full p-0 cursor-pointer hover:bg-white transition-all hover:text-info"
					onClick={onDismiss}>
					<Icon className="fill-current" icon={closeSmall}/>
				</button>
			)}

			<div className={title ? 'grid' : 'flex'}>
				<div className={classNames( 'flex-shrink-0 flex gap-2', { 'items-center': title })}>
					{! disableIcon && (
						<Icon icon={icons[type] || null} className={iconClasses} />
					)}
					{title && <div className={titleClasses} dangerouslySetInnerHTML={{ __html: title }} />}
				</div>
				{children || text && (
					<div className={contentWrapClasses}>
						{text && <p className="m-0" dangerouslySetInnerHTML={{ __html: text }} />}
						{children}
					</div>
				)}
			</div>
		</div>
	);

}
