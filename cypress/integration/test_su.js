describe("Check Shortcode ultimate page", function () {
  it("successfully loads", function () {
    cy.visit("/blog/test/");
  });
  it("Carousel should have proper resize type", function () {
    cy.get(".su-carousel-slide img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com")
      .and("include", "rt:fill");
  });
  it("Slider should have proper resize type", function () {
    cy.get(".su-slider img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com")
      .and("include", "rt:fill");
  });
  it("Gallery should have proper resize type", function () {
    cy.get(".su-custom-gallery img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com")
      .and("include", "rt:fill");
  });
});
