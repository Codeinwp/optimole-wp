describe("Check amp page", function () {
  beforeEach(function () {
    cy.visit("/amp-mode/?amp");
  });

  it("AMP body has Optimole no script class", function () {
    cy.get("html").not(".optml_no_js");
  });

  it("AMP images should have replaced srcs", function () {
    cy.get("amp-img")
      .should("have.attr", "src")
      .and("include", "i.optimole.com");
  });

  it("AMP no script lazyload", function () {
    cy.get("script")
      .contains("d5jmkjjpb7yfg.cloudfront.net")
      .should("not.exist");
  });
});
