/**
 * WordPress dependencies.
 */
import {
	Button,
	TextControl
} from "@wordpress/components";

import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import { useState } from "@wordpress/element";

const APIForm = ({
	setMethod
}) => {
	const [ apiKey, setApiKey ] = useState( '' );
	const [ errors, setErrors ] = useState( {} );

	const { connectAccount } = useDispatch( 'optimole' );

	const {
		hasValidKey,
		isConnecting
	} = useSelect( select => {
		const {
			hasValidKey,
			isConnecting
		} = select( 'optimole' );

		return {
			hasValidKey: hasValidKey(),
			isConnecting: isConnecting(),
		};
	} );

	const onConnect = () => {
		setErrors( {} );

		connectAccount(
			{
				api_key: apiKey,
			},
			response => {
				if ( response.code !== 'success')  {
					setErrors( {
						'error_connect': response.data
					} );
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

			<div className="flex gap-8 p-8 items-end bg-light-blue border border-blue-300 rounded-md">
				<TextControl
					label={ optimoleDashboardApp.strings.add_api }
					placeholder={ optimoleDashboardApp.strings.email }
					value={ apiKey }
					onChange={ setApiKey }
					className="optml__input basis-4/5"
				/>

				<Button
					variant="primary"
					isBusy={ isConnecting }
					disabled={ isConnecting }
					className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
					onClick={ onConnect }
				>
					{ optimoleDashboardApp.strings.connect_btn }
				</Button>
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