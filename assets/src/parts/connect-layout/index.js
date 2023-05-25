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

const ConnectLayout = () => {
	const [ email, setEmail ] = useState( optimoleDashboardApp.current_user.email );
	const [ method, setMethod ] = useState( 'email' );
	const [ errors, setErrors ] = useState( {} );

	const { registerAccount } = useDispatch( 'optimole' );

	const {
		autConnectError,
		isConnecting
	} = useSelect( select => {
		const {
			getAutoConnectError,
			isConnecting
		} = select( 'optimole' );

		return {
			autConnectError: getAutoConnectError(),
			isConnecting: isConnecting(),
		};
	} );

	useEffect( () => {
		if ( autConnectError ) {
			setErrors( {
				'error_autoconnect': autConnectError
			} );
		}
	}, [ autConnectError ] );

	const onConnect = async () => {
		setErrors( {} );

		await registerAccount(
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
			<div className="optml-connect card">
				<div className="optml-connect__container">
					<APIForm
						setMethod={ setMethod }
					/>
				</div>
			</div>
		);
	}

	return (
		<div className="optml-connect card">
			<div className="optml-connect__container">
				<div className="optml-connect__content">
					<h2 className="text__font optml-connect__content__title">{ optimoleDashboardApp.strings.account_needed_heading }</h2>
					<p
						className="optml-connect__content__description"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_title } }
					/>

					<div className="flex spacing__vertical">
						<Icon icon="yes-alt" />
						<p
							className="optml-connect__content__description__bullet"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_1 } }
						/>
					</div>

					<div className="flex spacing__vertical">
						<Icon icon="yes-alt" />
						<p
							className="optml-connect__content__description__bullet"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_2 } }
						/>
					</div>
				</div>

				<div className="optml-connect__form card__light-background">
					<TextControl
						label={ optimoleDashboardApp.strings.email_address_label }
						placeholder={ optimoleDashboardApp.strings.email }
						value={ email }
						onChange={ setEmail }
						className="optml-connect__input"
					/>

					{ Object.keys( errors ).length > 0 && Object.keys( errors ).map( key => {
						return (
							<p
								key={ key }
								className="text__help color__danger"
								dangerouslySetInnerHTML={ { __html: errors[ key ] } }
							/>
						);
					} ) || <br/> }

					<Button
						variant="primary"
						className="optml-connect__button"
						onClick={ onConnect }
						isBusy={ isConnecting }
					>
						{ optimoleDashboardApp.strings.register_btn }
					</Button>

					<br/><br/>

					<div className="components-base-control__label">
						{ optimoleDashboardApp.strings.existing_user }
					</div>

					<Button
						variant="primary"
						className="optml-connect__button__secondary"
						onClick={ () => setMethod( 'key' ) }
					>
						{ optimoleDashboardApp.strings.api_exists }
					</Button>
				</div>
			</div>

			<div className="optml-connect__footer">
				<p
					dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_footer } }
				/>
			</div>
		</div>
	);
};

export default ConnectLayout;