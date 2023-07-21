const toggleDamSidebarLink = ( show = true ) => {
	const { dam_url, strings } = window.optimoleDashboardApp;
	const { optimole_assets } = strings;

	const existingPage =  document.querySelector( 'a[href*="page=optimole-dam"]' );

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
	li.classList.remove( 'current' );

	const a = li.querySelector( 'a' );
	a.href = dam_url;
	a.innerHTML = optimole_assets;
	a.classList.remove( 'current' );

	currentPage.parentNode.parentNode.insertBefore( li, currentPage.parentNode.nextSibling );
};

export { toggleDamSidebarLink };
