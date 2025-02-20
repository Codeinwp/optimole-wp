import classnames from 'classnames';

import { close } from '@wordpress/icons';
import { useViewportMatch } from '@wordpress/compose';
import { Button, Icon, Modal as CoreModal } from '@wordpress/components';

export default function Modal({	icon, labels = {}, onRequestClose = () => {}, onConfirm = () => {}, variant = 'default' }) {

	const isMobileViewport = useViewportMatch( 'small', '<' );

	const iconClasses = classnames({
		'bg-stale-yellow': 'warning' === variant,
		'bg-light-blue': 'default' === variant
	},
	'p-2 rounded-full flex items-center justify-center'
	);

	const actionButtonClasses = classnames(
		{
			'bg-mango-yellow': 'warning' === variant
		},
		'optml__button flex justify-center px-5 py-3 rounded font-bold min-h-40 basis-1/5'
	);

	return (
		<CoreModal
			__experimentalHideHeader={ true }
			className="max-w-xl antialiased"
			onRequestClose={ onRequestClose }
			isFullScreen={ isMobileViewport }
		>
			<Button
				icon={ close }
				onClick={ onRequestClose }
				label={ optimoleDashboardApp.strings.csat.close }
				className="fixed right-3 top-3 cursor-pointer"
			/>

			<div className="flex flex-col items-center">
				<span className={iconClasses}>
					<Icon
						icon={ icon }
						size={ 24 }
					/>
				</span>

				<h2
					className="mb-0"
					dangerouslySetInnerHTML={ { __html: labels.title } }
				/>

				<p
					className="text-center mx-0 my-4 text-gray-700"
					dangerouslySetInnerHTML={ { __html: labels.description } }
				/>

				<Button variant="primary" className={ actionButtonClasses } onClick={ onConfirm }>
					{ labels.action }
				</Button>
			</div>
		</CoreModal>
	);
}
