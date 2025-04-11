/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	TextControl
} from '@wordpress/components';

import {
	useDispatch,
	useSelect
} from '@wordpress/data';

import {
	useEffect,
	useState
} from '@wordpress/element';

import { isEmail } from '@wordpress/url';

/**
 * Internal dependencies.
 */
import APIForm from './APIForm';
import { registerAccount } from '../../utils/api';

const connectClasses = 'optml-connect flex flex-col justify-between max-w-screen-xl mt-8 mb-5 mx-auto p-0 transition-all ease-in-out duration-700 relative bg-white text-gray-700 border-0 rounded-lg shadow-md';
const connectContainerClasses = 'flex gap-8 p-8 flex-col md:flex-row';

const RestError = () => (
	<div
		className="bg-danger text-white rounded relative px-6 py-5 mb-0 m-12"
		dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.notice_api_not_working } }
	/>
);

const ConnectLayout = () => {
	const [ email, setEmail ] = useState( optimoleDashboardApp.current_user.email );
	const [ method, setMethod ] = useState( 'email' );
	const [ errors, setErrors ] = useState({});

	const { setAutoConnect } = useDispatch( 'optimole' );

	const {
		autoConnect,
		hasRestError,
		isConnecting
	} = useSelect( select => {
		const {
			getAutoConnect,
			hasRestError,
			isConnecting
		} = select( 'optimole' );

		return {
			autoConnect: getAutoConnect(),
			hasRestError: hasRestError(),
			isConnecting: isConnecting()
		};
	});

	useEffect( () => {
		if ( 'no' !== autoConnect ) {
			registerAccount(
				{
					email: autoConnect,
					// eslint-disable-next-line camelcase
					auto_connect: true
				},
				response => {
					setAutoConnect( 'no' );

					if ( 'email_registered' === response.code ) {
						setErrors({
							'email_registered': response.message
						});
						return;
					}

					if ( 'site_exists' === response.code ) {
						setErrors({
							'site_exists': response.message
						});
						return;
					}

					if ( 'success' !== response.code )  {
						if ( response.message ) {
							setErrors({
								'error_autoconnect': response.message
							});
						}
					}
				}
			);
		}
	}, []);

	useEffect( () => {
		if ( 0 === email.length || isEmail( email ) ) {

			// Removing invalid email notice if a valid email is set.
			if ( errors.invalid_email ) {
				delete errors.invalid_email;
				setErrors( errors );
			}
		}
	}, [ email ]);

	const onConnect = () => {
		setErrors({});

		registerAccount(
			{
				email
			},
			response => {
				if ( 'email_registered' === response.code ) {
					setErrors({
						'email_registered': response.message
					});
					return;
				}

				if ( 'site_exists' === response.code ) {
					setErrors({
						'site_exists': response.message
					});
					return;
				}


				if ( 'domain_not_accessible' === response.code ) {
					setErrors({
						'domain_not_accessible': response.message
					});
					return;
				}
				if ( 'success' !== response.code )  {
					setErrors({
						'error_register': response.message
					});
				}
			}
		);
	};

	if ( 'key' === method ) {
		return (
			<div className={ connectClasses }>
				{ hasRestError && <RestError /> }

				<div className={ connectContainerClasses }>
					<APIForm
						setMethod={ setMethod }
					/>
				</div>
			</div>
		);
	}

	return (
		<div className={ connectClasses }>
			{ hasRestError && <RestError /> }

			<div className={ connectContainerClasses }>
				<div className="optml-connect__content basis-8/12">
					<div className="inline-block bg-light-blue text-gray-500 text-sm font-medium px-4 py-1.5 rounded-full mb-4">{ optimoleDashboardApp.strings.account_needed_trust_badge }</div>

					<div className="text-gray-700 font-serif text-2xl font-bold leading-7 m-0">{ optimoleDashboardApp.strings.account_needed_heading }</div>
					<p
						className="text-base font-normal text-gray-500"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_sub_heading } }
					/>

					<div className="inline-flex items-center bg-light-blue px-4 py-1.5 rounded-full mb-5 text-sm text-gray-800">
						<span className="mr-2">⏱️</span>{ optimoleDashboardApp.strings.account_needed_setup_time }
					</div>

					<p
						className="text-sm text-gray-700 mb-5 leading-normal"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_title } }
					/>


					<div className="flex py-3 items-center">
						<Icon icon="yes-alt" />
						<p
							className="text-base ml-3 m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_1 } }
						/>
					</div>

					<div className="flex py-3 items-center">
						<Icon icon="yes-alt" />
						<p
							className="text-base ml-3 m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_2 } }
						/>
					</div>

					<div className="flex py-3 items-center">
						<Icon icon="yes-alt" />
						<p
							className="text-base ml-3 m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_4 } }
						/>
					</div>
				</div>

				<div className="optml-connect__form basis-4/12 p-8 bg-light-blue border border-blue-300 rounded-md">
					<TextControl
						label={ optimoleDashboardApp.strings.email_address_label }
						placeholder={ optimoleDashboardApp.strings.email }
						value={ email }
						onChange={ setEmail }
						className="optml__input"
						onBlur={ () => {
							if ( 0 !== email.length && ! isEmail( email ) ) {
								setErrors({
									'invalid_email': optimoleDashboardApp.strings.invalid_email
								});
							}
						}}
					/>

					{ 0 < Object.keys( errors ).length && Object.keys( errors ).map( key => {
						return (
							<p
								key={ key }
								className="block text-xs mt-1 text-danger"
								dangerouslySetInnerHTML={ { __html: errors[ key ] } }
							/>
						);
					}) || <br/> }

					<Button
						variant="primary"
						isBusy={ isConnecting }
						disabled={ isConnecting || 0 === email.length || ! isEmail( email ) }
						className="optml__button flex w-full justify-center rounded font-bold min-h-40"
						onClick={ onConnect }
					>
						{ optimoleDashboardApp.strings.register_btn }
					</Button>

					<br/><br/>

					<div className="base-control-label">
						{ optimoleDashboardApp.strings.existing_user }
					</div>

					<Button
						variant="secondary"
						className="optml__button flex w-full justify-center rounded font-bold mt-4 min-h-40 mb-4"
						onClick={ () => setMethod( 'key' ) }
					>
						{ optimoleDashboardApp.strings.api_exists }
					</Button>

					<div class="flex items-center justify-center text-gray-600 mb-4">
						<span class="mr-1.5">🔒</span>{ optimoleDashboardApp.strings.secure_connection }
					</div>

					<p
						className="mb-0"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.signup_terms } }
					/>
				</div>
			</div>
		</div>
	);
};

export default ConnectLayout;
