describe("Check Otter Background Lazyload", function () {
    it("successfully loads", function () {
      cy.visit("/otter/background-lazyload/");
    });
    it("Otter Flip block should have background lazyloaded", function () {
      cy.get(".wp-block-themeisle-blocks-flip")
        .find(".o-flip-content")
        .eq(0)
        .should("have.attr", "class")
        .and("include", "optml-bg-lazyloaded");
    });
    it("Otter Flip block should have background image url optimized (ie. external css is processed)", function () {
        cy.get(".wp-block-themeisle-blocks-flip")
        .find(".o-flip-content")
        .eq(0)
        .should("have.css", "background-image")
        .and("match", /url\(.*\.i\.optimole\.com.*\)/);
    });
  });
  