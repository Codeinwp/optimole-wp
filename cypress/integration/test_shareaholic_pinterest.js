describe( 'Shareaholic', function () {
	it( 'successfully loads', function () {
		cy.visit( '/shareaholic/' )
	} );
	it( 'click on button', function () {
		cy.get( 'li.shareaholic-share-button[data-service=\"pinterest\"]' ).click( { multiple: true, force: true  } )
	} );
	it( 'images should not have quality:eco', function () {
		cy.get( 'img' ).should( ( $imgs ) => {
			expect( $imgs ).to.have.length( 8 )
			expect( $imgs.eq( 0 ) ).to.have.attr( 'src' ).and.to.not.contain( 'eco' )
			expect( $imgs.eq( 1 ) ).to.have.attr( 'src' ).and.to.not.contain( 'eco' )
			expect( $imgs.eq( 2 ) ).to.have.attr( 'src' ).and.to.not.contain( 'eco' )
			expect( $imgs.eq( 3 ) ).to.have.attr( 'src' ).and.to.not.contain( 'eco' )
			expect( $imgs.eq( 4 ) ).to.have.attr( 'src' ).and.to.not.contain( 'eco' )
			// expect( $imgs.eq( 5 ) ).to.have.attr( 'src' ).and.to.contain( 'make error to log' )
			// expect( $imgs.eq( 6 ) ).to.have.attr( 'src' ).and.to.contain( 'make error to log' )
			expect( $imgs.eq( 7 ) ).to.have.attr( 'src' ).and.to.contain( 'make error to log' )
		} );
	} );
} );