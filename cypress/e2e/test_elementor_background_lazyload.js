describe("Check Elementor Background Page", function () {
  beforeEach(function () {
    cy.visit("/elementor/");
  });
  it("Elementor widgets should have background lazyloaded", function () {
    cy.get(".elementor-inner")
      .find(".elementor-widget-container")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Elementor widgets should have background image url optimized (ie. external css is processed)", function () {
    cy.get(".elementor-inner")
      .find(".elementor-widget-container")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /url\(.*\.i\.optimole\.com.*\)/);
  });
  it("Elementor widgets should have background lazyloaded", function () {
    cy.get(".elementor-inner")
      .find(".elementor-widget-container")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Elementor widgets  image not in view should have no background", function () {
    cy.get(".elementor-inner")
      .find(".elementor-widget-container")
      .eq(4)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Elementor background images should have background lazyloaded", function () {
    cy.get(".elementor-inner")
      .find(".elementor-background-overlay")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Elementor background image not in view should have no background", function () {
    cy.get(".elementor-inner")
      .find(".elementor-background-overlay")
      .eq(1)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Elementor background gallery item image that is in view should have background lazyloaded", function () {
    cy.scrollTo(0, 200);
    for (let i = 0; i < 6; i++) {
      cy.get(".elementor-inner")
        .find(".elementor-background-slideshow__slide__image")
        .eq(i)
        .should("have.attr", "class")
        .and("include", "optml-bg-lazyloaded");
    }
  });
  it("Elementor background gallery item image that is not in view should have no background", function () {
    for (let i = 6; i < 12; i++) {
      cy.get(".elementor-inner")
        .find(".elementor-background-slideshow__slide__image")
        .eq(i)
        .should("have.css", "background-image")
        .and("match", /none/);
    }
  });
  it("After scroll the background images that come in view should be loaded", function () {
    cy.scrollTo(200, 4100);

    cy.get(".elementor-inner")
      .find(".elementor-background-overlay")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".elementor-inner")
      .find(".elementor-widget-container")
      .eq(4)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    for (let i = 6; i < 12; i++) {
      cy.get(".elementor-inner")
        .find(".elementor-background-slideshow__slide__image")
        .eq(i)
        .should("have.attr", "class")
        .and("include", "optml-bg-lazyloaded");
    }
  });
});
