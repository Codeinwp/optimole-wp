/**
 * WordPress dependencies.
 */
import { useState } from "@wordpress/element";

/**
 * Internal dependencies.
 */
import Menu from "./Menu";
import General from "./General";

const Settings = () => {
	const [ tab, setTab ] = useState( 'general' );

	return (
		<div className="flex bg-white p-8 border-0 rounded-lg shadow-md gap-8">
			<Menu
				tab={ tab }
				setTab={ setTab }
			/>

			<div className="basis-4/5">
				{ tab === 'general' && <General /> }
			</div>
		</div>
	);
};

export default Settings;
