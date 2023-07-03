/**
 * External dependencies.
 */
import { info } from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	TextareaControl,
	Tooltip
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import {
	useEffect,
	useState
} from '@wordpress/element';

/**
 * Internal dependencies.
 */
import CSAT from './csat/index';
import useSettings from '../../utils/use-settings';

const CSATList = () => {
	const [ isVisible, setIsVisible ] = useState( false );
	const [ getOption, updateOption, status ] = useSettings();

	const settings = ( 'loaded' === status && undefined !== getOption( 'optml_csat' ) ) && JSON.parse( getOption( 'optml_csat' ) );

	const {
		hasImages,
		isFirstSite
	} = useSelect( select => {
		const { getUserData } = select( 'optimole' );

		return {
			hasImages: 0 < getUserData()?.images_number,
			isFirstSite: 1 === getUserData()?.whitelist?.length
		};
	});

	const visits = localStorage.getItem( 'optimole_settings_visits' );

	useEffect( () => {
		setIsVisible( hasImages && isFirstSite && 3 <= parseInt( visits ) && false !== settings && true !== settings?.firstImpressions );
	}, [ visits, settings ]);

	return (
		<>
			<CSAT
				id="first-impressions"
				show={ isVisible }
				onDismiss={ () => updateOption( 'optml_csat', JSON.stringify({ ...settings, firstImpressions: true }) ) }
				strings={ {
					title: optimoleDashboardApp.strings.csat.title,
					close: optimoleDashboardApp.strings.csat.close
				} }
				pages={ [
					{
						showHeader: true,
						content: ( props ) => (
							<div className="flex flex-col gap-5 px-8 pb-8">
								<div className="font-bold text-lg">
									{ optimoleDashboardApp.strings.csat.heading_one }
								</div>

								<div className="flex gap-5">
									{ [ 1, 2, 3, 4, 5 ].map( value => (
										<Button
											key={ value }
											onClick={ () => props.changeData({ score: value }, true ) }
											className="optml__button basis-full justify-center rounded font-bold min-h-40"
										>
											{ value }
										</Button>
									) ) }
								</div>

								<div className="flex justify-between">
									<span className="text-purple-gray text-sm">{ optimoleDashboardApp.strings.csat.low }</span>
									<span className="text-purple-gray text-sm">{ optimoleDashboardApp.strings.csat.high }</span>
								</div>
							</div>
						)
					},
					{
						showHeader: true,
						content: ( props ) => (
							<div className="flex flex-col gap-5 px-8 pb-8">
								<div className="font-bold text-lg">
									{ optimoleDashboardApp.strings.csat.heading_two }
								</div>

								<TextareaControl
									label={ optimoleDashboardApp.strings.csat.heading_two }
									hideLabelFromVision={ true }
									value={ props.data?.feedback }
									placeholder={ optimoleDashboardApp.strings.csat.feedback_placeholder }
									onChange={ feedback => props.changeData({ feedback }) }
								/>

								<div className="flex justify-between">
									<Tooltip
										text={ optimoleDashboardApp.strings.csat.privacy_tooltip }
									>
										<div className="flex items-center cursor-pointer text-info">
											<Icon icon={ info } className="mr-2 fill-info" />

											{ optimoleDashboardApp.strings.csat.privacy }
										</div>
									</Tooltip>

									<div className="flex gap-2">
										<Button
											onClick={ () => props.onSubmit() }
											className="optml__button basis-full justify-center rounded font-bold min-h-40"
										>
											{ optimoleDashboardApp.strings.csat.skip }
										</Button>

										<Button
											variant="primary"
											onClick={ () => props.onSubmit() }
											className="optml__button basis-full justify-center rounded font-bold min-h-40"
										>
											{ optimoleDashboardApp.strings.csat.submit }
										</Button>
									</div>
								</div>
							</div>
						)
					}
				] }
			/>
		</>
	);
};

export default CSATList;
