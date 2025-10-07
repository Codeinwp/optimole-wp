import { Icon, Button } from '@wordpress/components';
import { closeSmall, check } from '@wordpress/icons';
import { useSelect } from '@wordpress/data';
import { saveSettings } from '../../utils/api';

const OptimizationStatus = ({ settings, setSettings, setCanSave, setTab }) => {

	const { isLoading } = useSelect( select => {
		const { isLoading } = select( 'optimole' );
		return {
			isLoading: isLoading()
		};
	}, []);

	const lazyloadEnabled = 'enabled' === settings?.lazyload;
	const imageHandlingEnabled = 'enabled' === settings?.image_replacer;

	const directUpdate = ( option, value ) => {
		if ( setCanSave && setSettings ) {
			setCanSave( true );
			const data = { ...settings };

			data[ option ] = value ? 'enabled' : 'disabled';

			if ( 'scale' === option && data.scale && 'disabled' === data.scale && 'disabled' === data.lazyload ) {
				data.lazyload = 'enabled';
			}

			if ( 'lazyload' === option && data.lazyload && 'disabled' === data.lazyload && 'disabled' === data.scale ) {
				data.scale = 'enabled';
			}

			setSettings( data );

			saveSettings(
				data,
				false,
				false,
				() => {
					setCanSave( false );
				}
			);
		}
	};

	const handleSettingsNavigation = ( ) => {
		if ( setTab ) {
			setTab( 'settings' );
		}
	};

	const {
		optimization_status
	} = optimoleDashboardApp.strings;

	const statuses = [
		{
			settingType: 'image_replacer',
			active: imageHandlingEnabled,
			label: optimization_status.statusTitle1,
			description: optimization_status.statusSubTitle1,
			buttonText: imageHandlingEnabled ? optimization_status.manage : optimization_status.enable
		},
		{
			settingType: 'lazyload',
			active: lazyloadEnabled && imageHandlingEnabled,
			label: optimization_status.statusTitle2,
			description: optimization_status.statusSubTitle2,
			buttonText: imageHandlingEnabled ? ( lazyloadEnabled ? optimization_status.disable : optimization_status.enable ) : ''
		},
		{
			settingType: 'scale',
			active: ( lazyloadEnabled && 'disabled' === settings?.scale ) && imageHandlingEnabled,
			label: optimization_status.statusTitle3,
			description: optimization_status.statusSubTitle3,
			buttonText: imageHandlingEnabled ? ( ( lazyloadEnabled && 'disabled' === settings?.scale ) ? optimization_status.disable : optimization_status.enable ) : ''
		}
	];

	return (
		<div className="bg-white flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
			<h3 className="text-lg mt-0">{ optimoleDashboardApp.strings.optimization_status.title }</h3>
			<ul className="grid gap-3 m-0">
				{ statuses.map( ( status, index ) => (
					<li
						key={index}
						className="flex items-start gap-2 justify-between"
					>
						<div className="flex items-start gap-2">
							{status.active ? (
								<Icon icon={check} className="fill-success bg-success/20 rounded-full" size={20} />
							) : (
								<Icon icon={closeSmall} className="fill-danger bg-danger/20 rounded-full" size={20} />
							)}
							<div>
								<span className='text-gray-700 font-semibold'>
									{status.label}
								</span>
								<p className="m-0">{status.description}</p>
							</div>
						</div>
						<Button
							variant="link"
							className="text-info text-sm font-medium"
							style={{ textDecoration: 'none' }}
							onClick={() => {
								if ( 'image_replacer' === status.settingType && status.active ) {
									handleSettingsNavigation( );
									return;
								}

								if ( 'scale' === status.settingType  ) {
									status.active = ! status.active;
								}

								if ( 'disabled' === settings?.image_replacer && 'image_replacer' !== status.settingType ) {
									return;
								}

								directUpdate( status.settingType, ! status.active );
							}}

							isBusy={isLoading}
							disabled={isLoading}
						>
							{status.buttonText}
						</Button>
					</li>
				) ) }
			</ul>
			<p
				className="m-0 mt-5"
				dangerouslySetInnerHTML={ {
					__html: optimoleDashboardApp.strings.optimization_tips
				} }
			/>
		</div>
	);
};

export default OptimizationStatus;
