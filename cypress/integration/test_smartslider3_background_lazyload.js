describe( 'Check Smart Slider Background Page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/smartslider3/' )
	} );
	it( 'Slider background images in view should be lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Slider background images not in view have background-image:none', function () {
		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 1 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'Slider background images not in view have background-image:none', function () {
		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 2 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'Slider background images not in view have background-image:none', function () {
		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 3 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'After scroll the background images that come in view should be loaded', function () {
		cy.scrollTo( 0, 2000 )

		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 3 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );

		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 4 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );

		cy.get( '.entry-content' ).find( '.n2-ss-slide-background-image' ).eq( 5 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );

	} );
} );