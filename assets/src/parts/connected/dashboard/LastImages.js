/**
 * External dependencies.
 */
import { useElementSize } from "usehooks-ts";

/**
 * WordPress dependencies.
 */
import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import {
	useEffect,
	useState
} from "@wordpress/element";

const isInitialLoading = optimoleDashboardApp.connection_status !== 'yes';

const Image = ({
	src,
	oldSize,
	newSize,
}) => {
	const [ squareRef, { width } ] = useElementSize();

	const getSize = () => {
		let value = ( (1 - newSize / oldSize ) * 100 ).toFixed( 1 );
		if ( value < 1 ) {
			return '1';
		}

		if ( value > 100 ) {
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
					height: width,
				} }
				ref={ squareRef }
			/>
			<p className="optml__bullet w-full h-4">{ getSize() }% { optimoleDashboardApp.strings.latest_images.saved } </p>
		</div>
	);
};

const ImagePlaceholder = () => {
	const [ squareRef, { width } ] = useElementSize();

	return (
		<div className="basis-1/4">
			<div
				className="optml__loader w-full rounded-md"
				style={{
					height: width,
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
			setTimeout(() => {
				updateProgress();
			}, 1000 );
		}
	}, [ timer ] );

	const {
		requestStatsUpdate,
		retrieveOptimizedImages
	} = useDispatch( 'optimole' );

	const { images } = useSelect( select => {
		const { getOptimizedImages } = select( 'optimole' );
	
		return {
			images: getOptimizedImages() || [],
		};
	} );

	useEffect( () => {
		if ( images.length >= 8 ) {
			setIsLoaded( true );
			return;
		}

		if ( ! isLoaded && ! isInitialLoading ) {
			retrieveOptimizedImages( () => {
				setIsLoaded( true );
			} );
		}
	}, [] );

	const updateProgress = () => {
		setTimer( timer + 1 );

		if ( timer >= maxTime ) {
			retrieveOptimizedImages( () => {
				setIsLoaded( true );
				setTimer( 0 );
				setStep( 0 );
				setProgress( 0 );
				requestStatsUpdate();
			} );
			return;
		}

		setProgress( Math.floor( ( timer / maxTime ) * 100 ) );

		if ( progress > ( ( step + 1 ) * 30 ) ) {
			setStep( step + 1 );
		}
	};

	return (
		<div className="hidden lg:block pt-5 border-grayish-blue border-0 border-t-2 border-solid">
			<h3 className="text-base m-0">{ optimoleDashboardApp.strings.latest_images.last } { optimoleDashboardApp.strings.latest_images.optimized_images }</h3>

			{ ( isInitialLoading && ! isLoaded ) && (
				<div className="flex items-center flex-col py-2">
					<p class="font-semibold">{ optimoleDashboardApp.strings.latest_images.loading_latest_images }</p>
					<progress className="primary" max={ maxTime } value={ timer } />
				</div>
			) }

			{ ( ! isInitialLoading && ! isLoaded ) && (
				<>
					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( () => <ImagePlaceholder/> ) }
					</div>

					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( () => <ImagePlaceholder/> ) }
					</div>
				</>
			) }

			{ ( isLoaded && images?.length < 4 ) && (
				<div className="text-center py-12">
					<p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.latest_images.no_images_found } } />
				</div>
			) }

			{ ( isLoaded && images?.length >= 4 ) && (
				<>
					<div className="flex justify-between mt-5 gap-5">
						{ [ ...Array( 4 ) ].map( ( i, key ) => {
							return (
								<Image
									src={ images[ key ].url }
									oldSize={ images[ key ].ex_size_raw }
									newSize={ images[ key ].new_size_raw }
								/>
							);
						} ) }
					</div>

					{ images?.length >= 8 && (
						<div className="flex justify-between mt-5 gap-5">
							{ [ ...Array( 4 ) ].map( ( i, key ) => {
								key = key + 4;

								return (
									<Image
										src={ images[ key ].url }
										oldSize={ images[ key ].ex_size_raw }
										newSize={ images[ key ].new_size_raw }
									/>
								);
							} ) }
						</div>
					) }
				</>
			) }
		</div>
	);
}

export default LastImages;
