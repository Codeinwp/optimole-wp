describe( 'Check Elementor Background Page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/elementor/' )
	} );
	it( 'Elementor widgets should have background lazyloaded', function () {
		cy.get( '.elementor-inner' ).find( '.elementor-widget-container' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Elementor widgets should have background lazyloaded', function () {
		cy.get( '.elementor-inner' ).find( '.elementor-widget-container' ).eq( 1 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Elementor widgets  image not in view should have no background', function () {
		cy.get( '.elementor-inner' ).find( '.elementor-widget-container' ).eq( 3 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'Elementor background images should have background lazyloaded', function () {
		cy.get( '.elementor-inner' ).find( '.elementor-background-overlay' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Elementor background image not in view should have no background', function () {
		cy.get( '.elementor-inner' ).find( '.elementor-background-overlay' ).eq( 1 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'After scroll the background images that come in view should be loaded', function () {
		cy.scrollTo( 0, 3650 )

		cy.get( '.elementor-inner' ).find( '.elementor-background-overlay' ).eq( 1 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );

		cy.get( '.elementor-inner' ).find( '.elementor-widget-container' ).eq( 3 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	// need to add div-with-background to page
	// it( 'Elementor background image should be properly replaced', function () {
	// 	cy.get('.div-with-background .elementor-column-wrap ').should('have.css', 'background-image').and('include', 'i.optimole.com');
	// } );

} );
