describe("Sassy Social Share", function () {
  beforeEach(function () {
    cy.visit("/sassy-social-share/");
  });
  it("click on button", function () {
    cy.get(".heateorSssSharing.heateorSssPinterestBackground").click({
      multiple: true,
    });
  });
  it("images should not have quality:eco", function () {
    cy.scrollTo(0, 2500);
    cy.get("img").should(($imgs) => {
      expect($imgs).to.have.length(5);
      expect($imgs.eq(0)).to.have.attr("src").and.to.not.contain("eco");
      expect($imgs.eq(1)).to.have.attr("src").and.to.not.contain("eco");
      expect($imgs.eq(2)).to.have.attr("src").and.to.not.contain("eco");
      expect($imgs.eq(3)).to.have.attr("src").and.to.not.contain("eco");
    });
  });
});
