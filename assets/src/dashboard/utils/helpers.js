const links = window.optimoleDashboardApp.submenu_links;

const toggleDamSidebarLink = ( show = true ) => {

	const { dam_url, strings } = window.optimoleDashboardApp;
	const { cloud_library } = strings;

	const existingPage = document.querySelector( 'a[href*="page=optimole-dam"]' );

	// Bail on first call when dashboard is loaded.
	if ( ! show && ! existingPage ) {
		return;
	}

	// Remove the DAM link.
	if ( ! show ) {
		if ( ! existingPage ) {
			return;
		}
		existingPage.style.display = 'none';

		return;
	}

	if ( existingPage ) {
		existingPage.style = {};
		return;
	}

	// Create a new link and add it to the menu.
	const li = document.createElement( 'li' );
	const a = document.createElement( 'a' );

	li.appendChild( a );
	a.href = dam_url;
	a.innerHTML = cloud_library;

	const existingSubmenu = document.querySelector( '#toplevel_page_optimole ul' );
	if ( ! existingSubmenu ) {
		return;
	}

	existingSubmenu.insertBefore( li, existingSubmenu.firstChild );
};

const highlightSidebarLink = () => {
	links.forEach( ( link ) => {
		const existingLink = document.querySelector( `a[href="${link.href}"]` );

		if ( ! existingLink ) {
			return;
		}

		const parent = existingLink.parentNode;

		if ( link.hash && link.hash === window.location.hash ) {
			existingLink.classList.add( 'current' );
			parent.classList.add( 'current' );
			return;
		}

		existingLink.classList.remove( 'current' );
		parent.classList.remove( 'current' );
	});
};

const toggleDashboardSidebarSubmenu = ( show = true ) => {
	console.log( '%c toggleDashboardSidebarSubmenu', 'color: #fff; background: #f00; font-size: 16px; padding: 4px 8px; border-radius: 4px;' );

	const topLevel = document.querySelector( 'li#toplevel_page_optimole' );
	let existingList = document.querySelector( 'li#toplevel_page_optimole .wp-submenu' );

	if ( ! existingList ) {
		existingList = document.createElement( 'ul' );
		existingList.classList.add( 'wp-submenu', 'wp-submenu-wrap' );
		topLevel.classList.add( 'wp-has-submenu', 'wp-has-current-submenu', 'wp-menu-open' );

		topLevel.appendChild( existingList );
	}

	// Hiding it - display: none;
	if ( ! show ) {
		existingList.style.display = 'none';

		return;
	}


	links.forEach( ( link ) => {
		const existingLink = document.querySelector( `li#toplevel_page_optimole a[href="${link.href}"]` );

		if ( existingLink ) {
			return;
		}

		const li = document.createElement( 'li' );
		const a = document.createElement( 'a' );

		a.href = link.href;
		a.innerHTML = link.text;

		li.appendChild( a );

		if ( link.hash && link.hash === window.location.hash ) {
			li.classList.add( 'current' );
			a.classList.add( 'current' );
		}

		existingList.appendChild( li );
	});

	existingList.style.display = 'block';
};

window.x = toggleDashboardSidebarSubmenu;

export { toggleDamSidebarLink, highlightSidebarLink, toggleDashboardSidebarSubmenu };
