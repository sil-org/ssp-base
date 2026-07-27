Feature: Prompt to review profile information

  Background:
    Given I go to the SP1 login page
    And I click on the "IDP 1" tile

  Scenario: Don't ask for review
    Given I provide credentials that do not need review
    When I log in
    Then I should end up at my intended destination

  Scenario Outline: Present reminder as required by the user profile
    Given I provide credentials that are due for a <category> <nag type> reminder
    When I log in
    Then I should see the message: <message>
    And I should see the message: "Requires reauthentication to profile management application."
    And there should be a way to go update my profile now
    And there should be a way to continue to my intended destination

    Examples:
      | category | nag type | message                   |
      | mfa      | add      | "2-Step Verification"     |
      | method   | add      | "alternate email address" |

  Scenario: Present profile review as required by the user profile
    Given I provide credentials that are due for a profile review
    When I log in
    Then I should see the message: "Are these still correct?"
    And there should be a way to go review my profile now
    And there should be a way to continue to my intended destination

  Scenario Outline: Obeying a reminder
    Given I provide credentials that are due for a <category> <nag type> reminder
    And I have logged in
    When I click the update profile button
    Then I should end up at the update profile URL

    Examples:
      | category | nag type |
      | mfa      | add      |
      | method   | add      |

  Scenario: Obeying a profile review reminder
    Given I provide credentials that are due for a profile review
    And I have logged in
    When I click the "Some of these need updating" link
    Then I should end up at the update profile URL on a new tab

  Scenario Outline: Ignoring a reminder
    Given I provide credentials that are due for a <category> <nag type> reminder
    And I have logged in
    When I click the remind-me-later button
    Then I should end up at my intended destination

    Examples:
      | category | nag type |
      | mfa      | add      |
      | method   | add      |

  Scenario: Ignoring a profile review reminder
    Given I provide credentials that are due for a profile review
    And I have logged in
    When I click the remind-me-later button
    Then I should end up at my intended destination

  Scenario: Ensuring that manager mfa data is not displayed to the user
    Given I provide credentials for a user that has used the manager mfa option
    And I have logged in
    Then I should see the message: "Are these still correct?"
    And I should not see any manager mfa information
