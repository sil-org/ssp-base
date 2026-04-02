Feature: Update last login timestamp
  The last_login_utc field for a user should be updated at the right
  times in the various paths through the login process.

  Background:
    Given I go to the SP1 login page
    And I click on the "IDP 1" tile

  Scenario: No 2SV required - last login is updated
    Given I provide credentials that do not need MFA
    When I log in
    Then I should end up at my intended destination
    And the last login should have been updated

  Scenario: 2SV required and passed - last login is updated
    Given I provide credentials that need MFA and have backup codes available
    And I have logged in
    When I submit a correct backup code
    Then I should end up at my intended destination
    And the last login should have been updated

  Scenario: 2SV required but failed - last login is NOT updated
    Given I provide credentials that need MFA and have backup codes available
    And I have logged in
    When I submit an incorrect backup code
    Then I should see a message that it was incorrect
    And the last login should NOT have been updated

  Scenario: 2SV required but not yet set up, user sent to set up 2SV - last login is updated
    Given I provide credentials that need MFA but have no MFA options available
    And I log in
    When I click the set-up-MFA button
    Then I should end up at the mfa-setup URL
    And the last login should have been updated
