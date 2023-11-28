import { Icon, cancelCircleFilled, warning, info, check } from '@wordpress/icons';
import classNames from 'classnames';

export default function Notice({ text, title, disableIcon = false, type = 'info', className = '', children }) {
	const noticeClasses = classNames({
		'bg-yellow-50 text-yellow-700': 'warning' === type,
		'bg-green-50 text-green-700': 'success' === type,
		'bg-red-50 text-red-700': 'error' === type,
		'bg-blue-50 text-blue-700': 'info' === type
	},
	`rounded-md my-5 text-sm p-4 om-notice om-notice-${type}` );

	const iconClasses = classNames({
		'fill-yellow-500': 'warning' === type,
		'fill-green-500': 'success' === type,
		'fill-red-500': 'error' === type,
		'fill-blue-500': 'info' === type
	},
	'h-6 w-6' );

	const contentWrapClasses = classNames({
		'ml-3 mt-0.5': ! disableIcon && ! title,
		'mt-2 mx-1.5': title
	});

	const titleClasses = classNames({
		'text-yellow-800': 'warning' === type,
		'text-green-800': 'success' === type,
		'text-red-800': 'error' === type,
		'text-blue-800': 'info' === type
	}, 'm-0 font-bold text-base' );

	const icons = {
		'warning': warning,
		'success': check,
		'error': cancelCircleFilled,
		'info': info
	};


	return (
		<div className={noticeClasses}>
			<div className={title ? 'grid' : 'flex'}>
				<div className={classNames( 'flex-shrink-0 flex gap-2', { 'items-center': title })}>
					{! disableIcon && (
						<Icon icon={icons[type]} className={iconClasses}/>
					)}
					{title && <h3 className={titleClasses}>{title}</h3>}
				</div>
				<div className={contentWrapClasses}>
					{text && <p className="m-0" dangerouslySetInnerHTML={{ __html: text }}/>}
					{children}
				</div>
			</div>
		</div>
	);


}
