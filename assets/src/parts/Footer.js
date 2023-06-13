import { ExternalLink } from '@wordpress/components';

const footerMenu = [
	{
		label: optimoleDashboardApp.strings.optimole + ' ' + optimoleDashboardApp.strings.version,
		href: 'https://optimole.com/',
	},
	{
		label: optimoleDashboardApp.strings.terms_menu,
		href: 'https://optimole.com/terms/',
	},
	{
		label: optimoleDashboardApp.strings.privacy_menu,
		href: 'https://optimole.com/privacy-policy/',
	},
	{
		label: optimoleDashboardApp.strings.testdrive_menu,
		href: 'https://optimole.com/test-drive?url=' + optimoleDashboardApp.home_url,
	},
];

const Footer = () => {
	return (
		<footer className="max-w-screen-xl gap-3 md:gap-0 mx-auto my-5 flex justify-between text-base flex-col md:flex-row">
			<div dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.account_needed_subtitle_3 } }/>
			
			<nav className="flex gap-0 sm:gap-3 flex-wrap md:flex-nowrap">
				{ footerMenu.map( ( item, index ) => (
					<ExternalLink
						key={ index }
						href={ item.href }
						className="basis-1/2 sm:basis-auto"
					>
						{ item.label }
					</ExternalLink>
				) ) }
			</nav> 
		</footer>
	);
}

export default Footer;
