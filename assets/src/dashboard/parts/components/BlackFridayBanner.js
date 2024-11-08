import { Button } from '@wordpress/components';
import { close } from '@wordpress/icons';
import { useEffect, useState } from '@wordpress/element';
import classNames from 'classnames';
import { dismissNotice } from '../../utils/api';

export default () => {
	const { urgency, title, subtitle, cta_link, cta_text, dismiss_key } = optimoleDashboardApp.bf_notices.banner;
	const [ isVisible, setIsVisible ] = useState( true );
	const [ shouldRender, setShouldRender ] = useState( true );

	const onClose = () => {
		dismissNotice( dismiss_key, () => {
			setIsVisible( false );
		});
	};

	useEffect( () => {
		if ( ! isVisible ) {
			const timer = setTimeout( () => {
				setShouldRender( false );
			}, 300 );
			return () => clearTimeout( timer );
		}
	}, [ isVisible ]);

	const wrapClasses = classNames(
		'relative flex flex-col items-center text-center xl:flex-row gap-5 justify-between xl:items-center bg-black text-white p-5 py-4 pr-7 box-border rounded-lg mt-5 bg-no-repeat transition-all duration-300',
		{
			'opacity-0': ! isVisible,
			'opacity-100': isVisible
		}
	);

	if ( ! shouldRender ) {
		return null;
	}

	return (
		<div className={wrapClasses}
			style={{
				backgroundImage: `url(${optimoleDashboardApp.assets_url}/img/bf-bg.jpg)`,
				backgroundPosition: 'right 0',
				backgroundSize: 'auto 100%'
			}}
		>
			<Button
				icon={close}
				onClick={onClose}
				label={optimoleDashboardApp.strings.csat.close}
				className="absolute right-1 top-1 cursor-pointer text-white hover:text-promo-orange"
			/>
			<div className="flex flex-col gap-3 xl:items-start items-center">
				<div className="text-sm lg:text-base border-b border-0 border-dashed uppercase pb-1 font-semibold">{urgency}</div>
				<div className="text-4xl lg:text-5xl italic uppercase font-extrabold" dangerouslySetInnerHTML={{ __html: title }}/>
				<div className="text-sm lg:text-base font-extrabold" dangerouslySetInnerHTML={{ __html: subtitle }}/>
			</div>

			<a href={cta_link} target="_blank" className="bg-promo-orange text-white px-7 py-3 uppercase text-base font-bold grow flex justify-center max-w-[150px] text-center cursor-pointer hover:bg-white hover:text-orange-400 transition-colors">{cta_text}</a>
		</div>
	);
};
