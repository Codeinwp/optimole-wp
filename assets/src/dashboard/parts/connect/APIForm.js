/**
 * WordPress dependencies.
 */
import {
	Button,
	SelectControl,
	TextControl
} from '@wordpress/components';

import {
	useDispatch,
	useSelect
} from '@wordpress/data';

import { useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import {
	connectAccount,
	selectDomain
} from '../../utils/api';

const APIForm = ({
	setMethod
}) => {
	const [ apiKey, setApiKey ] = useState( '' );
	const [ errors, setErrors ] = useState({});
	const [ selectedApp, setApp ] = useState( 0 );

	const { setShowDisconnect } = useDispatch( 'optimole' );

	const {
		availableApps,
		hasValidKey,
		isConnected,
		isConnecting,
		isLoading
	} = useSelect( select => {
		const {
			getAvailableApps,
			hasValidKey,
			isConnected,
			isConnecting,
			isLoading
		} = select( 'optimole' );

		return {
			availableApps: getAvailableApps(),
			hasValidKey: hasValidKey(),
			isConnected: isConnected(),
			isConnecting: isConnecting(),
			isLoading: isLoading()
		};
	});

	const onConnect = () => {
		setErrors({});

		connectAccount(
			{
				// eslint-disable-next-line camelcase
				api_key: apiKey
			},
			response => {
				if ( 'success' !== response.code )  {
					setErrors({
						'error_connect': response.data
					});
				}
			}
		);
	};

	const getActiveApp = () => {
		if ( null !== availableApps && availableApps !== undefined && availableApps.available_apps !== undefined ) {
			return availableApps.available_apps[ selectedApp ].key;
		}

		return '';
	};

	const onSelectDomain = () => {
		selectDomain(
			{
				// eslint-disable-next-line camelcase
				api_key: apiKey,
				application: getActiveApp()
			},
			response => {
				if ( 'success' !== response.code )  {
					setErrors({
						'error_connect': response.data
					});
				}
			}
		);
	};

	return (
		<div className="text-2 font-bold m-0 text-gray-700">
			<div className="text-gray-800 font-serif leading-7">{ optimoleDashboardApp.strings.connect_btn }</div>
			<p
				className="text-xl font-normal text-gray-800"
				dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.steps_connect_api_desc } }
			/>

			<div className="flex flex-col gap-8 p-8 bg-light-blue border border-blue-300 rounded-md">
				<div className="flex flex-col sm:flex-row gap-8 items-stretch sm:items-end">
					<TextControl
						label={ isConnected ? optimoleDashboardApp.strings.your_api_key : optimoleDashboardApp.strings.add_api }
						placeholder={ optimoleDashboardApp.strings.email }
						disabled={ isConnected }
						value={ apiKey }
						onChange={ setApiKey }
						className="optml__input basis-4/5"
					/>

					{ isConnected ? (
						<Button
							variant="default"
							isBusy={ isLoading }
							disabled={ isLoading }
							className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
							onClick={ setShowDisconnect }
						>
							{ optimoleDashboardApp.strings.disconnect_btn }
						</Button>
					) : (
						<Button
							variant="primary"
							isBusy={ isConnecting }
							disabled={ isConnecting || 0 === apiKey.length }
							className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
							onClick={ onConnect }
						>
							{ optimoleDashboardApp.strings.connect_btn }
						</Button>
					) }
				</div>

				{ availableApps && (
					<div className="flex flex-col sm:flex-row gap-8 items-stretch sm:items-end">
						<div className="basis-4/5">
							<SelectControl
								label={ optimoleDashboardApp.strings.select + ' ' + optimoleDashboardApp.strings.your_domain }
								value={ selectedApp }
								options={ availableApps.available_apps.map( ( app, index ) => {
									return {
										label: app.domain,
										value: index
									};
								}) }
								onChange={ setApp }
								className="optml__select has_large_title"
								__nextHasNoMarginBottom={ true }
							/>
						</div>

						<Button
							variant="primary"
							isBusy={ isConnecting }
							disabled={ isConnecting }
							className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
							onClick={ onSelectDomain }
						>
							{ optimoleDashboardApp.strings.select }
						</Button>
					</div>
				) }
			</div>

			{ ! hasValidKey && undefined !== errors['error_connect'] && (
				<p className="block text-xs mt-1 text-danger">{ errors['error_connect'] }</p>
			) }

			<div className="text-center py-3">
				<Button
					variant="link"
					onClick={ () => setMethod( 'email' ) }
				>
					{ optimoleDashboardApp.strings.back_to_connect }
				</Button>
			</div>
		</div>
	);
};

export default APIForm;
