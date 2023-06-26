/**
 * External dependencies.
 */
import {
	check,
	closeSmall
} from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon
} from '@wordpress/components';

import {
	useEffect,
	useState
} from '@wordpress/element';

const Header = ({
	strings,
	onClose
}) => {
	return (
		<div className="flex justify-between px-8 pt-8">
			<p className="text-purple-gray text-sm m-0 py-2">
				{ strings.title }
			</p>

			<Button
				label={ strings.close }
				icon={ closeSmall }
				onClick={ onClose }
				className="text-gray-950"
			/>
		</div>
	);
};

window.optimoleCSAT = {
	hasSubmitted: false,
	data: {}
};

const CSAT = ({
	id,
	show = false,
	strings = {},
	pages = [],
	onDismiss
}) => {
	const [ isVisibile, setIsVisible ] = useState( false );
	const [ currentPage, setCurrentPage ] = useState( 0 );
	const [ hasDismissed, setHasDismissed ] = useState( false );
	const [ hasSubmitted, setHasSubmitted ] = useState( false );
	const [ data, setData ] = useState({});

	useEffect( () => {
		const beforeUnload = () => {
			if ( ! window.optimoleCSAT.hasSubmitted && window.optimoleCSAT.data?.score ) {
				localStorage.setItem( `optimole_csat_data_${ id }`, JSON.stringify( window.optimoleCSAT.data ) );
			}
		};

		window.addEventListener( 'beforeunload', beforeUnload );

		return () => {
			window.removeEventListener( 'beforeunload', beforeUnload );
		};
	}, []);

	useEffect( () => {
		if ( show && ! hasDismissed ) {
			setIsVisible( show );
		}
	}, [ show ]);

	useEffect( () => {
		window.optimoleCSAT = {
			hasSubmitted,
			data
		};
	}, [ hasSubmitted, data ]);

	const onSubmit = ( dismiss = true, params = data ) => {
		if ( hasSubmitted || ! params?.score ) {
			setHasDismissed( true );
			return null;
		}

		const body = {
			email: optimoleDashboardApp.user_data.user_email,
			product: 'Optimole',
			site: optimoleDashboardApp.home_url,
			...params
		};

		try {
			fetch( `https://api.themeisle.com/tracking/csat/${ params?.score }`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json, */*;q=0.1',
					'Cache-Control': 'no-cache'
				},
				body: JSON.stringify( body )
			});
		} catch ( e ) {
			console.warn( e );
		}

		setHasSubmitted( true );
		setCurrentPage( pages.length );
		setIsVisible( false );
		setHasDismissed( true );

		if ( dismiss ) {
			onDismiss();
		}
	};

	const changeData = ( obj, skip = false ) => {
		setData({
			...data,
			...obj
		});

		if ( skip ) {
			setCurrentPage( currentPage + 1 );
		}
	};

	// If localStorage has data, use it to send the request.
	useEffect( () => {
		const localState = localStorage.getItem( `optimole_csat_data_${ id }` );

		if ( localState ) {
			onSubmit( true, JSON.parse( localState ) );

			localStorage.removeItem( `optimole_csat_data_${ id }` );
		}
	}, []);

	const onClose = () => {
		setIsVisible( false );
		setHasDismissed( true );
		onSubmit( false );
		onDismiss();
	};

	if ( ! isVisibile ) {
		return null;
	}

	if ( 0 === pages.length ) {
		return null;
	}

	const canGoForward = currentPage < pages.length;

	const Content = pages[ currentPage ]?.content;

	return (
		<div className="bg-white fixed right-0 bottom-0 m-12 gap-2 max-w-lg w-full flex flex-col text-gray-700 border-0 rounded-lg shadow-lg z-50">

			{ pages[ currentPage ] && (
				<>
					{ pages[ currentPage ]?.showHeader && (
						<Header
							onClose={ onClose }
							strings={ strings }
						/>
					) }

					<Content
						data={ data }
						changeData={ changeData }
						onSubmit={ onSubmit }
					/>
				</>
			) }

			{ ! canGoForward && (
				<div className="flex flex-col gap-5 p-8 items-center">
					<Icon
						icon={ check }
						size={ 40 }
						className="bg-success rounded-full fill-white p-2"
					/>

					<div className="font-bold text-lg">
						{ optimoleDashboardApp.strings.csat.heading_three }
					</div>

					<div className="text-sm text-center">
						{ optimoleDashboardApp.strings.csat.thank_you }
					</div>

					<Button
						variant="primary"
						onClick={ () => setIsVisible( false ) }
						className="optml__button basis-full justify-center rounded font-bold min-h-40"
					>
						{ optimoleDashboardApp.strings.csat.close }
					</Button>
				</div>
			) }
		</div>
	);
};

export default CSAT;
