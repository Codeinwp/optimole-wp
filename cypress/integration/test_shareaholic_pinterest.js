// describe( 'Shareaholic', function () {
// 	it( 'successfully loads', function () {
// 		cy.visit( '/shareaholic/' )
// 	} );
// 	it( 'click on button', function () {
// 		cy.get( 'li.shareaholic-share-button[data-service=\"pinterest\"]' ).click( { multiple: true, force: true  } )
// 	} );
// 	it( 'images should not have quality:eco', function () {
// 		cy.get( 'img' ).each( ( $el, index, $list ) => {
// 			 if( $el.attr( 'src' ).includes( 'optimole' ) ) {
// 				 expect( $el ).to.have.attr( 'src' ).and.to.not.contain( 'eco' );
// 			 }
// 		} );
// 	} );
// } );
