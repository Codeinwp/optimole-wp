import { Icon, external } from '@wordpress/icons';

export default function WidgetFooter() {
	const { i18n, dashboardURL, adminPageURL } = optimoleDashboardWidget;

	return (
		<div className="flex justify-between gap-4 items-center px-4 py-2 bg-gray-50">
			<a href={dashboardURL} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 no-underline cursor-pointer group">
				<img src={ optimoleDashboardWidget.assetsURL + 'img/logo.svg' } alt="Optimole" className="w-8 h-8"/>
				<span className="text-base font-bold text-gray-800 group-hover:text-dark-blue transition-colors duration-300">Optimole</span>
			</a>

			<a href={ adminPageURL } className="text-sm text-info font-medium cursor-pointer hover:text-dark-blue no-underline transition-colors duration-300">
				{ i18n.viewAllStats }
			</a>
		</div>
	);
}
