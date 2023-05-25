/**
 * WordPress dependencies.
 */
import { useSelect } from "@wordpress/data";

/**
 * Internal dependencies.
 */
import Header from './Header';
import ConectLayout from './connect-layout';

const Main = () => {
	const { isConnected } = useSelect( select => {
		const { isConnected } = select( 'optimole' );
		return {
			isConnected: isConnected(),
		};
	} );

    return (
        <>
            <Header/>

            { ! isConnected && (
                <ConectLayout />
            ) }
        </>
    );
};

export default Main;
