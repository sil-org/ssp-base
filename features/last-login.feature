Feature: Last-login tracking (IDP-1807)
  In order to preserve accurate last-login records
  When a user supplies a wrong password
  The broker's last_login_utc value must not change

  Scenario: Scenario 1 - wrong password does not update last_login_utc
    Given I record the current last_login_utc for user "10001"
    When I go to the SP1 login page
    And I click on the "IDP 2" tile
    And I attempt to log in as "sildisco_idp2" with a bad password
    Then the last_login_utc for user "10001" should be unchanged
