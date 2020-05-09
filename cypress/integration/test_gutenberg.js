describe( 'Check gutenberg page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/gutenberg' )
	} );
	it( 'Gutenberg images should have replaced srcs', function () {
		cy.get( '.wp-block-image > img' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
	} );
	it( 'Gutenberg images should data-opt-src attribute', function () {
		cy.get( '.wp-block-image > img' ).should( 'have.attr', 'data-opt-src' ).and( 'include', 'i.optimole.com' );
	} );
	it( 'Gutenberg images should have no script tag', function () {
		cy.get( '.wp-block-image > noscript' ).should( 'exist' );
		cy.get( '.wp-block-image > noscript' ).should( 'contain', 'img' ).should( 'contain', 'i.optimole.com' )
	} );
	it( 'Gutenberg block cover background image should be properly replaced', function () {

		cy.get( '.wp-block-cover ' ).scrollIntoView().should( 'have.css','background-image' ).and( 'include', 'i.optimole.com' );
	} );
} );
