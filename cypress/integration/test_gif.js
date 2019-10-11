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
		cy.get( 'video' ).should( 'have.attr', 'poster' ).and( 'contain', 'f:png' );
		cy.get( 'video' ).should( 'have.attr', 'original-src' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video > source' ).should( 'have.attr', 'src' ).and( 'contain', 'f:webm' );
		cy.get( 'video > source' ).should( 'have.attr', 'src' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video > source' ).should( 'have.attr', 'type' ).and( 'contain', 'video/webm' );
		cy.get( 'video > source' ).eq( 1 ).should( 'have.attr', 'src' ).and( 'contain', 'f:mp4' );
		cy.get( 'video > source' ).eq( 1 ).should( 'have.attr', 'src' ).and( 'contain', 'i.optimole.com' );
		cy.get( 'video > source' ).eq( 1 ).should( 'have.attr', 'type' ).and( 'contain', 'video/mp4' );
	} );
	it( 'successfully loads', function () {
		cy.visit( '/gif-test/' )
	} );
	it( 'Images with gifs has proper tags', function () {
		cy.get( '.wp-block-image img' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
		cy.get( '.wp-block-image img' ).should( 'have.attr', 'data-opt-src' ).and( 'include', 'i.optimole.com' );
	} );
} );