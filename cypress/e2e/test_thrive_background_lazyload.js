describe("Check Thrive Background Page", function () {
  beforeEach(function () {
    cy.visit("/thrive/");
  });
  it("tve-content-box-background that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".tve-content-box-background")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });

  it("thrv_text_element that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".thrv_text_element")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("tve-page-section-out that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".tve-page-section-out")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("tve-content-box-background that is not in view should have background nonde", function () {
    cy.get(".entry-content")
      .find(".tve-content-box-background")
      .eq(1)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("tve-page-section-out that is not in view should have background nonde", function () {
    cy.get(".entry-content")
      .find(".tve-page-section-out")
      .eq(1)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("thrv_text_element that is not in view should have background nonde", function () {
    cy.get(".entry-content")
      .find(".thrv_text_element")
      .eq(2)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("After scroll the background images that come in view should be loaded", function () {
    cy.scrollTo(0, 3000);

    cy.get(".entry-content")
      .find(".tve-content-box-background")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".entry-content")
      .find(".tve-page-section-out")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".entry-content")
      .find(".thrv_text_element")
      .eq(2)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
});
