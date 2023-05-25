/**
 * WordPress dependencies.
 */
import {
	Button,
	TextControl
} from "@wordpress/components";

import { useState } from "@wordpress/element";

const APIForm = ({
	setMethod
}) => {
	const [ apiKey, setApiKey ] = useState( '' );

	return (
		<div className="optml-connect__key">
			<h2 className="text__font optml-connect__key__title">{ optimoleDashboardApp.strings.connect_btn }</h2>
			<p
				className="optml-connect__content__description"
				dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.steps_connect_api_desc } }
			/>

			<div className="optml-connect__form__horizontal align__end card__light-background">
				<TextControl
					label={ optimoleDashboardApp.strings.add_api }
					placeholder={ optimoleDashboardApp.strings.email }
					value={ apiKey }
					onChange={ setApiKey }
					className="optml-connect__input basis__80"
				/>

				<Button
					variant="primary"
					className="optml-connect__button basis__20"
				>
					{ optimoleDashboardApp.strings.connect_btn }
				</Button>
			</div>

			<p className="text__help color__danger">
				Can not connect to Optimole service
			</p>

			<div className="text__center spacing__vertical">
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