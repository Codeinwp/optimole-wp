describe( 'Check amp page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/amp-mode/?amp' )
	} );

	it( 'AMP body has Optimole no script class', function () {
		cy.get( 'body' ).should( 'have.attr', 'class' ).and( 'include', 'optimole-no-script' );
	} );

	it( 'AMP images should have replaced srcs', function () {
		cy.get( 'amp-img' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
	} );
} );
