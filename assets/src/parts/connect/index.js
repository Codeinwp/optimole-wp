/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	TextControl
} from "@wordpress/components";

import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import {
	useEffect,
	useState
} from "@wordpress/element";

/**
 * Internal dependencies.
 */
import APIForm from './APIForm';

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
	const [ errors, setErrors ] = useState( {} );

	const {
		registerAccount,
		setAutoConnect
	} = useDispatch( 'optimole' );

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
			isConnecting: isConnecting(),
		};
	} );

	useEffect( () => {
		if ( 'no' !== autoConnect ) {
			registerAccount(
				{
					email: autoConnect,
					auto_connect: true,
				},
				response => {
					setAutoConnect( 'no' );

					if ( response.code === 'email_registered') {
						setErrors( {
							'email_registered': response.message
						} );
						return;
					}
	
					if ( response.code !== 'success')  {
						if ( response.message ) {
							setErrors( {
								'error_autoconnect': response.message
							} );
						}
					}
				}
			);
		}
	}, [] );

	const onConnect = () => {
		setErrors( {} );

		registerAccount(
			{
				email
			},
			response => {
				if ( response.code === 'email_registered') {
					setErrors( {
						'email_registered': response.message
					} );
					return;
				}

				if ( response.code !== 'success')  {
					setErrors( {
						'error_register': optimoleDashboardApp.strings.error_register
					} );
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
					<div className="text-gray-700 font-serif text-2 font-bold leading-7 m-0">{ optimoleDashboardApp.strings.account_needed_heading }</div>
					<p
						className="text-xl font-normal text-gray-800"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_title } }
					/>

					<div className="flex py-3">
						<Icon icon="yes-alt" />
						<p
							className="text-base ml-3 m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_1 } }
						/>
					</div>

					<div className="flex py-3">
						<Icon icon="yes-alt" />
						<p
							className="text-base ml-3 m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_2 } }
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
					/>

					{ Object.keys( errors ).length > 0 && Object.keys( errors ).map( key => {
						return (
							<p
								key={ key }
								className="block text-xs mt-1 text-danger"
								dangerouslySetInnerHTML={ { __html: errors[ key ] } }
							/>
						);
					} ) || <br/> }

					<Button
						variant="primary"
						isBusy={ isConnecting }
						disabled={ isConnecting }
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
						className="optml__button flex w-full justify-center rounded font-bold mt-4 min-h-40"
						onClick={ () => setMethod( 'key' ) }
					>
						{ optimoleDashboardApp.strings.api_exists }
					</Button>
				</div>
			</div>

			<div className="bg-grayish-blue text-gray-800 text-center font-bold uppercase p-2.5 rounded-b-lg">
				<p
					dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_footer } }
				/>
			</div>
		</div>
	);
};

export default ConnectLayout;