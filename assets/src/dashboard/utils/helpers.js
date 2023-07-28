const toggleDamSidebarLink = ( show = true ) => {
	const { dam_url, strings } = window.optimoleDashboardApp;
	const { cloud_library } = strings;

	const existingPage = document.querySelector( 'a[href*="page=optimole-dam"]' );

	// Bail on first call when dashboard is loaded.
	if ( show && existingPage || ! show && ! existingPage ) {
		return;
	}

	// Remove the DAM link.
	if ( ! show ) {
		if ( ! existingPage ) {
			return;
		}

		existingPage.parentNode.remove();

		return;
	}

	// Add the DAM link.
	const currentPage = document.querySelector( 'a.current[href*="page=optimole"]' );

	if ( ! currentPage ) {
		return;
	}

	const li = currentPage.parentNode.cloneNode( true );
	li.classList.remove( 'current', 'wp-first-item' );

	const a = li.querySelector( 'a' );
	a.href = dam_url;
	a.innerHTML = cloud_library;
	a.classList.remove( 'current', 'wp-first-item' );

	currentPage.parentNode.parentNode.insertBefore( li, currentPage.parentNode.nextSibling );
};

const toggleSettingsHighlight = ( status = true ) => {
	const menuLink = document.querySelector( 'a[href*="page=optimole#settings"]' );

	if ( ! menuLink ) {
		return;
	}

	const parent = menuLink.parentNode;

	if ( status ) {
		parent.classList.add( 'current' );
		menuLink.classList.add( 'current' );

		return;
	}

	parent.classList.remove( 'current' );
	menuLink.classList.remove( 'current' );
};

export { toggleDamSidebarLink, toggleSettingsHighlight };
