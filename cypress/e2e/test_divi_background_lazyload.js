describe("Check Divi Background Page", function () {
  it("successfully loads", function () {
    cy.visit("/divi/");
  });
  //from here
  it("Divi slide that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_0")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Divi slide that is not in view should have background none", function () {
    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_1")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Divi slide that is not in view should have background none", function () {
    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_2")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  //to here we test the slider
  //from here we test the standard divi module where you can have background images
  //and the row background, section background
  it("Divi row that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".et_pb_row_0")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Divi module that is in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".et_pb_module")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
  it("Divi slide that is not in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_3")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Divi slide that is not in view should have optml-bg-lazyloaded", function () {
    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_4")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Divi row that is not in view should have background", function () {
    cy.get(".entry-content")
      .find(".et_pb_module")
      .eq(4)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Divi module that is not in view should have background", function () {
    cy.get(".entry-content")
      .find(".et_pb_row_3")
      .eq(0)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("Divi module that is not in view should have background", function () {
    cy.get(".entry-content")
      .find(".et_pb_with_background")
      .eq(1)
      .should("have.css", "background-image")
      .and("match", /none/);
  });
  it("After scroll the background images that come in view should be loaded", function () {
    cy.scrollTo(0, 4250);

    cy.get(".entry-content")
      .find(".et_pb_slides > .et_pb_slide_3")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".entry-content")
      .find(".et_pb_module")
      .eq(4)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".entry-content")
      .find(".et_pb_row_3")
      .eq(0)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");

    cy.get(".entry-content")
      .find(".et_pb_with_background")
      .eq(1)
      .should("have.attr", "class")
      .and("include", "optml-bg-lazyloaded");
  });
});
