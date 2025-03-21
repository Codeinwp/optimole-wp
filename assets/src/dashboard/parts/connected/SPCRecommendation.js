import { useState, useEffect } from '@wordpress/element';
import { close, check } from '@wordpress/icons';
import { Button, Icon } from '@wordpress/components';
import classNames from 'classnames';


import { installPlugin, activatePlugin } from '../../utils/plugin-install';
import { dismissNotice } from '../../utils/api';

const STATUSES = {
	IDLE: 'idle',
	INSTALLING: 'installing',
	ACTIVATING: 'activating',
	ACTIVE: 'active',
	ERROR: 'error'
};

const SPC_SLUG = 'wp-cloudflare-page-cache';

const SPCRecommendation = () => {
	const {
		i18n,
		activate_url: spcActivateURL,
		banner_dismiss_key: bannerDismissKey,
		status: initialStatus
	} = optimoleDashboardApp.spc_banner;

	const [ status, setStatus ] = useState( 'installed' === initialStatus ? STATUSES.INSTALLED : STATUSES.IDLE );
	const [ isVisible, setIsVisible ] = useState( true );
	const [ shouldRender, setShouldRender ] = useState( true );

	const isLoading = status === STATUSES.INSTALLING || status === STATUSES.ACTIVATING;

	const installActivePlugin = ( e ) => {
		e.preventDefault();

		if ( status === STATUSES.INSTALLED ) {
			setStatus( STATUSES.ACTIVATING );
			activatePlugin( spcActivateURL ).then( ( response ) => {
				if ( response.success ) {
					setStatus( STATUSES.ACTIVE );
				} else {
					setStatus( STATUSES.ERROR );
				}
			});
			return;
		}

		setStatus( STATUSES.INSTALLING );

		installPlugin( SPC_SLUG ).then( ( response ) => {
			if ( response.success ) {
				setStatus( STATUSES.ACTIVATING );

				activatePlugin( spcActivateURL ).then( ( response ) => {
					if ( response.success ) {
						setStatus( STATUSES.ACTIVE );
					} else {
						setStatus( STATUSES.ERROR );
					}
				});
			} else {
				setStatus( STATUSES.ERROR );
			}
		});
	};


	const onDismiss = () => {
		dismissNotice( bannerDismissKey, () => {
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

	if ( ! shouldRender ) {
		return null;
	}

	const wrapClasses = classNames(
		'bg-white flex flex-col text-gray-700 border-0 rounded-lg overflow-hidden shadow-md relative transition-opacity duration-300',
		{
			'opacity-0': ! isVisible,
			'opacity-100': isVisible
		}
	);

	return (
		<div className={ wrapClasses }>
			<button onClick={ onDismiss } className="absolute top-2 right-2 p-1 bg-transparent border-0 text-red z-10 cursor-pointer rounded-full flex items-center justify-center hover:opacity-75 transition-opacity text-gray-500 hover:text-info">
				<span className="sr-only">{i18n.dismiss}</span>
				<Icon icon={ close } className="fill-current" size={ 18 } />
			</button>
			<div className="p-8 flex flex-col gap-2">
				<h3 className="text-gray-800 text-lg font-bold m-0">{ i18n.title }</h3>
				<p className="text-gray-600 m-0">{ i18n.byline }</p>
				<ul className="grid gap-2">
					{ i18n.features.map( ( feature, index ) => (
						<li className="flex items-center gap-2" key={ index }>
							<Icon icon={ check } className="fill-info bg-info/20 rounded-full" size={ 20 } />
							<span className="text-gray-700 font-normal">{ feature }</span>
						</li>
					) ) }
				</ul>
				{ ! [ STATUSES.ACTIVE, STATUSES.ERROR ].includes( status ) && (
					<Button
						variant="primary"
						onClick={installActivePlugin}
						className="flex w-full justify-center font-bold rounded hover:opacity-90 transition-opacity"
						disabled={ isLoading }
						isBusy={ isLoading }
					>
						{ status === STATUSES.IDLE && i18n.cta }
						{ status === STATUSES.INSTALLED && i18n.activate }
						{ status === STATUSES.INSTALLING && i18n.installing }
						{ status === STATUSES.ACTIVATING && i18n.activating }
					</Button>
				) }

				{ status === STATUSES.ACTIVE && (
					<div className="flex items-center justify-center gap-3 py-3 px-4 rounded-md bg-emerald-100 border border-solid border-emerald-200">
						<span className="font-medium text-sm text-emerald-700">{ i18n.activated }</span>
					</div>
				) }

				{ status === STATUSES.ERROR && (
					<div className="text-center py-3 px-4 rounded-md bg-red-100 border border-solid border-red-200">
						<span className="font-medium text-sm text-red-700">{ i18n.error }</span>
					</div>
				) }
			</div>
		</div>
	);
};

export default SPCRecommendation;
