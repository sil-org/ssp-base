Feature: SIL IdP discovery (sildisco) module

  Scenario: Expect discovery page and login prompt
    When I go to the SP1 login page
    And the url should match "sildisco/disco.php"
    And I should see "to continue to SP1"
    And I click on the "IDP 1" tile
    And I log in using my "IDP 1" credentials
    Then I should end up at my intended destination on SP1

  Scenario: Skip discovery page with only one approved IdP
    Given I go to the SP2 login page
    And I log in using my "IDP 2" credentials
    Then I should end up at my intended destination on SP2

  Scenario: Skip login prompt with existing authentication session
    Given I have authenticated with IDP1 for SP1
    When I go to the SP3 login page
    And the url should match "sildisco/disco.php"
    And I should see "to continue to SP3"
    And I click on the "IDP 1" tile
    Then I should end up at my intended destination on SP3

  Scenario: Skip discovery and login prompt
    Given I have authenticated with IDP2 for SP1
    When I go to the SP2 login page
    Then I should end up at my intended destination on SP2

  Scenario: Show discovery AND login prompt with existing session on different IdP
    Given I have authenticated with IDP2 for SP1
    When I go to the SP3 login page
    And I should see "to continue to SP3"
    And I click on the "IDP 1" tile
    And I log in using my "IDP 1" credentials
    Then I should end up at my intended destination on SP3

  Scenario: IdP Logout
    Given I have authenticated with IDP1 for SP1
    When I log out of IDP1
    And I go to the SP1 login page
    And I click on the "IDP 1" tile
    Then I should be prompted for a username and password

  Scenario: Expect login prompt for SP4's ForceAuthn with only one approved IdP
    Given I have authenticated with IDP2 for SP4
    And I am visiting SP4
    And I remove session cookies for the current SP
    When I go to the SP4 login page
    Then I should be prompted for a username and password on IDP2

  Scenario: Expect discovery page and login prompt for SP5's ForceAuthn with multiple approved IdPs
    Given I have authenticated with IDP2 for SP5
    And I am visiting SP5
    And I remove session cookies for the current SP
    When I go to the SP5 login page
    And the url should match "sildisco/disco.php"
    And I should see "to continue to SP5"
    And I click on the "IDP 2" tile
    Then I should be prompted for a username and password on IDP2
