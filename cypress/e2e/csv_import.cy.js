describe('CSV import', () => {
  it('Maintains siblings order', () => {
    cy.login()

    cy.visit('/object/importSelect?type=csv')
    cy.get('input[name=file]').selectFile('cypress/fixtures/import_order.csv')
    cy.get('input[type=submit]').click()

    cy.contains('Import file initiated')
  })
})
