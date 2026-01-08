/**
 * External dependencies.
 */
import { useElementSize } from 'usehooks-ts';

/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

import {
	useEffect,
	useState
} from '@wordpress/element';

/**
 * Internal dependencies.
 */
import {
	retrieveOptimizedImages,
	requestStatsUpdate
} from '../../../utils/api';

import ProgressBar from '../../components/ProgressBar';

const isInitialLoading = 'yes' !== optimoleDashboardApp.connection_status;

const Image = ({
	src,
	oldSize,
	newSize
}) => {
	const [ squareRef, { width }] = useElementSize();

	const getSize = () => {
		let value = ( ( 1 - newSize / oldSize ) * 100 ).toFixed( 1 );
		if ( 1 > value ) {
			return '1';
		}

		if ( 100 < value ) {
			return ( Math.floor( ( value / 10 ) + 10 ) / 10 ).toFixed( 1 ).toString();
		}

		return value.toString();
	};

	return (
		<div className="basis-1/4">
			<img
				src={ src }
				className="w-full border border-slate-200 rounded-md border-solid"
				style={{
					height: width
				} }
				ref={ squareRef }
			/>
			<p className="optml__bullet w-full h-4">{ optimoleDashboardApp.strings.latest_images.percentage_saved.replace( '{ratio}', getSize() ) } </p>
		</div>
	);
};

const ImagePlaceholder = () => {
	const [ squareRef, { width }] = useElementSize();

	return (
		<div className="basis-1/4">
			<div
				className="optml__loader w-full rounded-md"
				style={{
					height: width
				} }
				ref={ squareRef }
			/>
			<p className="optml__loader w-full h-4" />
		</div>
	);
};

const LastImages = () => {
	const [ isLoaded, setIsLoaded ] = useState( false );
	const [ progress, setProgress ] = useState( 0 );
	const [ step, setStep ] = useState( 0 );
	const [ timer, setTimer ] = useState( 0 );
	const maxTime = 20;

	useEffect( () => {
		if ( timer <= maxTime ) {
			setTimeout( () => {
				updateProgress();
			}, 1000 );
		}
	}, [ timer ]);

	const { images } = useSelect( select => {
		const { getOptimizedImages } = select( 'optimole' );

		return {
			images: getOptimizedImages() || []
		};
	});

	useEffect( () => {
		if ( 8 <= images.length ) {
			setIsLoaded( true );
			return;
		}

		if ( ! isLoaded && ! isInitialLoading ) {
			retrieveOptimizedImages( () => {
				setIsLoaded( true );
			});
		}
	}, []);

	const updateProgress = () => {
		setTimer( timer + 1 );

		if ( timer >= maxTime ) {
			retrieveOptimizedImages( () => {
				setIsLoaded( true );
				setTimer( 0 );
				setStep( 0 );
				setProgress( 0 );
				requestStatsUpdate();
			});
			return;
		}

		setProgress( Math.floor( ( timer / maxTime ) * 100 ) );

		if ( progress > ( ( step + 1 ) * 30 ) ) {
			setStep( step + 1 );
		}
	};

	return (
		<div>
			<h3 className="text-gray-800 text-xl font-semibold mb-5 m-0">{ optimoleDashboardApp.strings.latest_images.last_optimized_images }</h3>

			{ ( isInitialLoading && ! isLoaded ) && (
				<div className="flex items-center flex-col py-2">
					<p className="font-semibold">{ optimoleDashboardApp.strings.latest_images.loading_latest_images }</p>
					<ProgressBar max={ maxTime } value={ timer } />
				</div>
			) }

			{ ( ! isInitialLoading && ! isLoaded ) && (
				<>
					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( ( _, index ) => <ImagePlaceholder key={ index }/> ) }
					</div>

					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( ( _, index ) => <ImagePlaceholder key={ `placeholder-2-${ index }` }/> ) }
					</div>
				</>
			) }

			{ ( isLoaded && 4 > images?.length ) && (
				<div className="text-center py-12">
					<p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.latest_images.no_images_found } } />
				</div>
			) }

			{ ( isLoaded && 4 <= images?.length ) && (
				<>
					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( ( i, key ) => {
							return (
								<Image
									key={ key }
									src={ images[ key ].url }
									oldSize={ images[ key ].ex_size_raw }
									newSize={ images[ key ].new_size_raw }
								/>
							);
						}) }
					</div>

					{ 8 <= images?.length && (
						<div className="flex justify-between mt-5 gap-5">
							{ [ ...Array( 4 ) ].map( ( i, key ) => {
								key = key + 4;

								return (
									<Image
										key={ key }
										src={ images[ key ].url }
										oldSize={ images[ key ].ex_size_raw }
										newSize={ images[ key ].new_size_raw }
									/>
								);
							}) }
						</div>
					) }
				</>
			) }
		</div>
	);
};

export default LastImages;
