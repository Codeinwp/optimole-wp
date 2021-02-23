describe("Check Homepage", function () {
  it("successfully loads", function () {
    cy.visit("/beaver/");
  });
  it("Beaver column should have background lazyloaded", function () {
    cy.get(".entry-content")
      .find(".fl-col-content")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Beaver column should have background lazyloaded", function () {
    cy.get(".entry-content")
      .find(".fl-col-content")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Beaver column should have background lazyloaded", function () {
    cy.get(".entry-content")
      .find(".fl-col-content")
      .eq(2)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Beaver row content should have background lazyloaded", function () {
    cy.get(".entry-content")
      .find(".fl-row-bg-photo > .fl-row-content-wrap")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Beaver row content not in view should  have no background", function () {
    cy.get(".entry-content")
      .find(".fl-row-bg-photo > .fl-row-content-wrap")
      .eq(1)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Beaver background image not in view should have no background", function () {
    cy.get(".entry-content")
      .find(".fl-col-content")
      .eq(4)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("After scroll the background images that come in view should be loaded", function () {
    cy.scrollTo(0, 2500);

    cy.get(".entry-content")
      .find(".fl-col-content")
      .eq(4)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
    cy.get(".entry-content")
      .find(".fl-row-bg-photo > .fl-row-content-wrap")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
});
