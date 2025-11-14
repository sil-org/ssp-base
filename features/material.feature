Feature: Material theme

  Scenario: Hub (disco) page
    When I go to the SP1 login page
    Then I should see our material theme

  Scenario: Error page
    When I go to the Hub but specify an invalid authentication source
    And I log in as a hub administrator
    Then I should see an "Error" page
    And I should see our material theme

  Scenario: Login page
    When I go to the SP1 login page
    And I click on the "IDP 2" tile
    Then I should see a "Sign in to IDP 2" page
    And I should see our material theme

  Scenario: Login error
    When I go to the SP1 login page
    And I click on the "IDP 2" tile
    And I provide a username and an incorrect password
    And I log in
    Then I should see "There was a problem with that username or password"
