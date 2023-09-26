describe("Check foo gallery page", function () {
  beforeEach(function () {
    cy.visit("/foo-gallery/");
  });
  it("Gallery images all attributes replaced properly", function () {
    cy.get(".foogallery img")
      .should("have.attr", "data-src-fg")
      .and("contain", "i.optimole.com");
  });

  it("Gallery images should never have lazyload on", function () {
    cy.get(".foogallery img").not("have.attr", "data-opt-src");
  });
});
