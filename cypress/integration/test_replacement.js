describe( 'Check Homepage', function () {
	it( 'successfully loads', function () {
		cy.visit( '/' )

		cy.get( 'img' ).should( 'have.attr','src' ).and( 'include','i.optimole.com' );
	} )
} );
