describe("Check gutenberg page", function () {
  beforeEach(function () {
    cy.visit("/gutenberg");
  });
  const nativeLazy = "loading" in HTMLImageElement.prototype;
  it("Gutenberg images should have replaced srcs", function () {
    cy.get(".wp-block-image > img")
      .should("have.attr", "src")
      .and("include", "i.optimole.com");
  });
  it("Gutenberg images should data-opt-src attribute", function () {
    if (nativeLazy) {
      this.skip();
    }
    cy.get(".wp-block-image:eq(2) > img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com");
  });
  it("Gutenberg images should have no script tag", function () {
    cy.get(".wp-block-media-text__media > noscript").should("exist");
    cy.get(".wp-block-media-text__media > noscript")
      .should("contain", "img")
      .should("contain", "i.optimole.com");
  });
  it("Gutenberg block cover background image should be properly replaced", function () {
    cy.get(".wp-block-cover ")
      .scrollIntoView()
      .should("have.css", "background-image")
      .and("include", "i.optimole.com");
  });
});
