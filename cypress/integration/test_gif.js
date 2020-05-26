// Added as per. https://docs.cypress.io/api/events/catalog-of-events.html#Uncaught-Exceptions
// https://docs.cypress.io/guides/references/error-messages.html#Uncaught-exceptions-from-your-application
Cypress.on( 'uncaught:exception', ( err, runnable ) => {
	// returning false here prevents Cypress from
	// failing the test
	return false
} );

describe( 'Check gif page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/no-builder/testing-gif-with-video/' )
	} );
	it( 'Gallery images all attributes replaced properly', function () {
		cy.get( 'video' ).should( 'have.attr', 'autoplay' ).and( 'contain', '' );
		cy.get( 'video' ).should( 'have.attr', 'muted' ).and( 'contain', '' );
		cy.get( 'video' ).should( 'have.attr', 'loop' ).and( 'contain', '' );
		cy.get( 'video' ).should( 'have.attr', 'playsinline' ).and( 'contain', '' );
		cy.get( 'video' ).should( 'have.attr', 'poster' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video' ).should( 'have.attr', 'poster' ).and( 'contain', 'q:eco' );
		cy.get( 'video' ).should( 'have.attr', 'original-src' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video > source' ).should( 'have.attr', 'src' ).and( 'contain', 'f:mp4' );
		cy.get( 'video > source' ).should( 'have.attr', 'src' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video > source' ).should( 'have.attr', 'type' ).and( 'contain', 'video/mp4' );
	} );
	it( 'successfully loads', function () {
		cy.visit( '/gif-test/' );
	} );
	it( 'Images with gifs has proper tags', function () {
		cy.get( '.wp-block-image img' ).should( 'have.attr', 'src' ).and( 'include', 'data:image/svg+xml' );
		cy.get( '.wp-block-image img' ).should( 'have.attr', 'data-opt-src' ).and( 'include', 'i.optimole.com' );
	} );
} );