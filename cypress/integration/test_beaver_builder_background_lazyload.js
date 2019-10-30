describe( 'Check Homepage', function () {
	it( 'successfully loads', function () {
		cy.visit( '/beaver-background-lazyload/' )
	} );
	it( 'Beaver column should have background lazyloaded', function () {
		cy.get( '#content' ).find( '.fl-col-content' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Beaver column should have background lazyloaded', function () {
		cy.get( '#content' ).find( '.fl-col-content' ).eq( 1 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Beaver column should have background lazyloaded', function () {
		cy.get( '#content' ).find( '.fl-col-content' ).eq( 2 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Beaver row content should have background lazyloaded', function () {
		cy.get( '#content' ).find( '.fl-row-bg-photo > .fl-row-content-wrap' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Elementor background image not in view should have no background', function () {
		cy.get( '#content' ).find( '.fl-col-content' ).eq( 3 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
} );