describe("Elementor Check Homepage", function () {
  beforeEach(function () {
    cy.visit("/");
  });
  const nativeLazy = "loading" in HTMLImageElement.prototype;
  it("Header should not have lazyload applied", function () {
    cy.get("header").should("not.have.attr", "data-opt-src");
  });
  it("Elementor images should have replaced srcs", function () {
    cy.get(".elementor-image > img")
      .should("have.attr", "src")
      .and("include", "i.optimole.com");
  });

  it("Elementor images should data-opt-src attribute", function () {
    if (nativeLazy) {
      this.skip();
    }
    cy.get(".elementor-image > img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com");
  });

  it("Elementor images should have no script tag", function () {
    cy.get(".elementor-image > noscript").should("exist");
    cy.get(".elementor-image > noscript")
      .should("contain", "img")
      .should("contain", "i.optimole.com");
  });

  it("Custom crop should have rt fill resize", function () {
    if (nativeLazy) {
      this.skip();
    }
    cy.get(".custom-crop .elementor-image > img")
      .should("have.attr", "data-opt-src")
      .and("include", "i.optimole.com")
      .and("include", "rt:fill");
  });

  it("Elementor gallery should be properly replaced", function () {
    cy.get(".elementor-image-gallery  a ")
      .should("have.attr", "href")
      .and("include", "i.optimole.com");
    cy.get(".elementor-image-gallery  a > img")
      .should("have.attr", "src")
      .and("include", "i.optimole.com");
  });
  it("Elementor background image should be properly replaced", function () {
    cy.get(".div-with-background .elementor-column-wrap ")
      .should("have.css", "background-image")
      .and("include", "i.optimole.com");
  });
});
