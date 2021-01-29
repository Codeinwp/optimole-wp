describe('Check Metaslider Background Page', function () {
  it('successfully loads', function () {
    cy.visit('/metaslider/');
  });
  it('Slider background images in view should be lazyloaded', function () {
    cy.get('.entry-content')
      .find('.coin-slider > .coin-slider')
      .eq(0)
      .should('have.attr', 'class')
      .and('include', 'optml-bg-lazyloaded');
  });
  it('Slider background images in view should be lazyloaded', function () {
    for (let i = 4; i <= 30; i++) {
      cy.get('.entry-content')
        .find('.coin-slider > .coin-slider > a')
        .eq(i)
        .should('have.attr', 'class')
        .and('include', 'optml-bg-lazyloaded');
    }
  });

  it('Scroll the page', function () {
    cy.scrollTo(0, 2500);
  });
  it('After scroll slider background images not in view should be lazyloaded', function () {
    cy.get('.entry-content')
      .find('.coin-slider > .coin-slider')
      .eq(1)
      .should('have.css', 'background-image')
      .and('match', /none/);
  });
  it('After scroll slider background images not in view should be lazyloaded', function () {
    for (let i = 35; i <= 67; i++) {
      cy.get('.entry-content')
        .find('.coin-slider > .coin-slider > a')
        .eq(i)
        .should('have.css', 'background-image')
        .and('match', /none/);
    }
  });
});
