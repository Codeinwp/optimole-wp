describe( 'Check MasterSlider Background Page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/masterslider/' )
	} );
	it( 'Slider background images in view should be lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.master-slider' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' )
	} );
	it( 'Scroll the page', function () {
		cy.scrollTo( 0, 2500 )
		cy.get( '.entry-content' ).find( '.master-slider' ).eq( 1 ).should( 'have.css', 'background-image' ).and( 'match', /none/ )
	} );

} );