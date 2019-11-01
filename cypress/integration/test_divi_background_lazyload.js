describe( 'Check Divi Background Page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/test_divi_background_lazyload/' )
	} );
	//from here
	it( 'Divi slide that is in view should have optml-bg-lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.et_pb_slides > .et_pb_slide_0' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Divi slide that is not in view should have background none', function () {
		cy.get( '.entry-content' ).find( '.et_pb_slides > .et_pb_slide_1' ).eq( 0 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	it( 'Divi slide that is not in view should have background none', function () {
		cy.get( '.entry-content' ).find( '.et_pb_slides > .et_pb_slide_2' ).eq( 0 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
	//to here we test the slider
	//from here we test the standard divi module where you can have background images
	it( 'Divi module that is in view should have optml-bg-lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.et_pb_module' ).eq( 0 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Divi module that is in view should have optml-bg-lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.et_pb_module' ).eq( 1 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Divi module that is in view should have optml-bg-lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.et_pb_module' ).eq( 2 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Divi module that is in view should have optml-bg-lazyloaded', function () {
		cy.get( '.entry-content' ).find( '.et_pb_module' ).eq( 3 ).should( 'have.attr', 'class' ).and( 'include', 'optml-bg-lazyloaded' );
	} );
	it( 'Divi module that is not in view should have background', function () {
		cy.get( '.entry-content' ).find( '.et_pb_module' ).eq( 4 ).should( 'have.css', 'background-image' ).and( 'match', /none/ );
	} );
} );