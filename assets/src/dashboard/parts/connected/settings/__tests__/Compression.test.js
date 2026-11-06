/**
 * The compression mode control on WordPress versions whose @wordpress/components has no
 * ToggleGroupControl (below 5.9).
 */
import { act } from 'react-dom/test-utils';
import { createRoot } from 'react-dom/client';

jest.mock( '@wordpress/components', () => ({
	BaseControl: ({ children }) => children,
	Button: ({ children }) => children,
	Icon: () => null,
	RangeControl: () => null,
	ToggleControl: () => null,
	RadioControl: ({ options, selected, onChange }) => options.map( option => (
		<input
			key={ option.value }
			type="radio"
			value={ option.value }
			checked={ selected === option.value }
			onChange={ () => onChange( option.value ) }
		/>
	) )
}) );
jest.mock( '@wordpress/data', () => ({
	useSelect: () => ({ sampleImages: {}, isLoading: false })
}) );
jest.mock( '@wordpress/icons', () => ({ rotateRight: null }) );
jest.mock( 'react-compare-image', () => () => null );
jest.mock( '../../../../utils/api', () => ({ sampleRate: jest.fn() }) );
jest.mock( '../../../components/ProgressBar', () => () => null );

global.IS_REACT_ACT_ENVIRONMENT = true;
global.optimoleDashboardApp = {
	strings: {
		options_strings: new Proxy({}, { get: ( target, name ) => name })
	}
};

const Compression = require( '../Compression' ).default;

describe( 'Compression', () => {
	let container;
	let root;

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
		root = createRoot( container );
	});

	afterEach( () => {
		act( () => root.unmount() );
		container.remove();
	});

	test( 'offers the three compression modes without ToggleGroupControl', () => {
		const setSettings = jest.fn();

		act( () => {
			root.render(
				<Compression
					settings={ { compression_mode: 'quality_optimized' } }
					setSettings={ setSettings }
					setCanSave={ jest.fn() }
					showSample={ false }
					isSampleLoading={ false }
					setIsSampleLoading={ jest.fn() }
				/>
			);
		});

		const radios = container.querySelectorAll( 'input[type="radio"]' );
		expect( Array.from( radios, radio => radio.value ) ).toEqual([ 'speed_optimized', 'quality_optimized', 'custom' ]);
		expect( container.querySelector( 'input:checked' ).value ).toBe( 'quality_optimized' );

		act( () => radios[0].click() );

		expect( setSettings ).toHaveBeenCalledWith( expect.objectContaining({
			compression_mode: 'speed_optimized',
			retina_images: 'disabled'
		}) );
	});
});
