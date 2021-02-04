describe("Check envira page", function () {
  it("successfully loads", function () {
    cy.visit("/envira-test-page/");
  });
  it("Gallery images all attributes replaced properly", function () {
    cy.get("#envira-gallery-wrap-92 .envira-gallery-image")
      .should("have.attr", "src")
      .and("contain", "i.optimole.com");
    cy.get("#envira-gallery-wrap-92 .envira-gallery-image")
      .should("have.attr", "data-envira-src")
      .and("contain", "i.optimole.com");
    cy.get("#envira-gallery-wrap-92 .envira-gallery-image")
      .should("have.attr", "data-envira-srcset")
      .and("contain", "i.optimole.com");

    cy.get("#envira-gallery-wrap-97 .envira-gallery-image")
      .should("have.attr", "data-safe-src")
      .and("contain", "i.optimole.com");

    cy.get(".envira-gallery-link")
      .should("have.attr", "href")
      .and("contain", "i.optimole.com");
  });

  it("Gallery images should never have lazyload on", function () {
    cy.get(".envira-gallery-image").not("have.attr", "data-opt-src");
  });

  it("Gallery should have proper crop", function () {
    cy.get("#envira-gallery-wrap-98 img")
      .should("have.attr", "data-envira-src")
      .and("contain", "rt:fill");
  });
});
