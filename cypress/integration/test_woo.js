describe( 'Check product page', function () {
	it( 'successfully loads', function () {
		cy.visit( '/product/test-product/' )
	} );
	it( 'Gallery wrapper should have proper data-thumb', function () {
		cy.get( '.woocommerce-product-gallery__wrapper > div' ).should( 'have.attr', 'data-thumb' ).and( 'include', 'i.optimole.com' );
	} );
	it( 'Gallery wrapper link should have auto sizes', function () {
		cy.get( '.woocommerce-product-gallery__wrapper > div > a' ).should( 'have.attr', 'href' ).and( 'include', 'i.optimole.com' ).and( 'include', 'w:auto/h:auto' );
	} );
	it( 'Gallery wrapper image from link should have proper images', function () {
		cy.get( '.woocommerce-product-gallery__wrapper > div > a > img' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
		cy.get( '.woocommerce-product-gallery__wrapper > div > a > img' ).should( 'have.attr', 'data-src' ).and( 'include', 'i.optimole.com' );
		cy.get( '.woocommerce-product-gallery__wrapper > div > a > img' ).should( 'have.attr', 'data-large_image' ).and( 'include', 'i.optimole.com' );
	} );
	it( 'Zoom img should have auto size', function () {
		cy.get( '.woocommerce-product-gallery__wrapper .zoomImg' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' ).and( 'include', 'w:auto/h:auto' );
	} );
	it( 'All images should have proper tags', function () {
		cy.get( 'img:not(.emoji)' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
	} );
} );
describe( 'Test quick view', function () {
	it( 'successfully loads', function () {
		cy.visit( '/shop' )
		cy.get( '.yith-wcqv-button:first' ).click();
	} );
	it( 'Quick view have optimole images', function () {
		cy.get( '#yith-quick-view-content .woocommerce-product-gallery__wrapper img' ).should( 'have.attr', 'src' ).and( 'include', 'i.optimole.com' );
	} );
} );
