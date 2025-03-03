Feature: Prompt for MFA credentials

  Background:
    Given I go to the SP1 login page
    And I click on the "IDP 1" tile

  Scenario: Don't prompt for MFA
    Given I provide credentials that do not need MFA
    When I log in
    Then I should end up at my intended destination

  Scenario: Needs MFA, but no MFA options are available
    Given I provide credentials that need MFA but have no MFA options available
    When I log in
    Then I should see a message that I have to set up MFA
    And there should be a way to go set up MFA now
    And there should NOT be a way to continue to my intended destination

  Scenario: Following the requirement to go set up MFA
    Given I provide credentials that need MFA but have no MFA options available
    And I log in
    When I click the set-up-MFA button
    Then I should end up at the mfa-setup URL
    And I should NOT be able to get to my intended destination

  Scenario: Needs MFA, has backup code option available
    Given I provide credentials that need MFA and have backup codes available
    When I log in
    Then I should see a prompt for a backup code

  Scenario: Needs MFA, has TOTP option available
    Given I provide credentials that need MFA and have TOTP available
    When I log in
    Then I should see a prompt for a TOTP code

  Scenario: Needs MFA, has WebAuthn option available
    Given I provide credentials that need MFA and have WebAuthn available
    When I log in
    Then I should see a prompt for a WebAuthn security key

  Scenario: Accepting a (non-rate-limited) correct MFA value
    Given I provide credentials that need MFA and have backup codes available
    And I have logged in
    When I submit a correct backup code
    Then I should end up at my intended destination

  Scenario: Rejecting a (non-rate-limited) wrong MFA value
    Given I provide credentials that need MFA and have backup codes available
    And I have logged in
    When I submit an incorrect backup code
    Then I should see a message that it was incorrect

  Scenario: Blocking an incorrect MFA value while rate-limited
    Given I provide credentials that have a rate-limited MFA
    And I have logged in
    When I submit an incorrect backup code
    Then I should see a message that I have to wait before trying again

  Scenario: Blocking a correct MFA value while rate-limited
    Given I provide credentials that have a rate-limited MFA
    And I have logged in
    When I submit a correct backup code
    Then I should see a message that I have to wait before trying again

  Scenario: Warning when running low on backup codes
    Given I provide credentials that need MFA and have 4 backup codes available
    And I have logged in
    When I submit a correct backup code
    Then I should see a message that I am running low on backup codes
    And I should be told I only have 3 backup codes left
    And there should be a way to get more backup codes now
    And there should be a way to continue to my intended destination

  Scenario: Requiring user to set up more backup codes when they run out and have no other MFA
    Given I provide credentials that need MFA and have 1 backup code available and no other MFA
    And I have logged in
    When I submit a correct backup code
    Then I should see a message that I have used up my backup codes
    And there should be a way to get more backup codes now
    And there should NOT be a way to continue to my intended destination

  Scenario: Warning user when they run out of backup codes but have other MFA options
    Given I provide credentials that need MFA and have 1 backup code available plus some other MFA
    And I have logged in
    When I submit a correct backup code
    Then I should see a message that I have used up my backup codes
    And there should be a way to get more backup codes now
    And there should be a way to continue to my intended destination

  Scenario: Obeying the nag to set up more backup codes when low
    Given I provide credentials that need MFA and have 4 backup codes available
    And I have logged in
    And I submit a correct backup code
    When I click the get-more-backup-codes button
    Then I should be given more backup codes
    And there should be a way to continue to my intended destination

  Scenario: Ignoring the nag to set up more backup codes when low
    Given I provide credentials that need MFA and have 4 backup codes available
    And I have logged in
    And I submit a correct backup code
    When I click the remind-me-later button
    Then I should end up at my intended destination

  Scenario: Obeying the requirement to set up more backup codes when out
    Given I provide credentials that need MFA and have 1 backup code available and no other MFA
    And I have logged in
    And I submit a correct backup code
    When I click the get-more-backup-codes button
    Then I should be given more backup codes
    And there should be a way to continue to my intended destination

  Scenario: Obeying the nag to set up more backup codes when out
    Given I provide credentials that need MFA and have 1 backup code available plus some other MFA
    And I have logged in
    And I submit a correct backup code
    When I click the get-more-backup-codes button
    Then I should be given more backup codes
    And there should be a way to continue to my intended destination

  Scenario: Ignoring the nag to set up more backup codes when out
    Given I provide credentials that need MFA and have 1 backup code available plus some other MFA
    And I have logged in
    And I submit a correct backup code
    When I click the remind-me-later button
    Then I should end up at my intended destination

  Scenario Outline: Defaulting to the most recently used mfa option
    Given I provide credentials that have a used <MFA type>
    And and I have a more recently used <recent MFA type>
    When I log in
    Then I should see a prompt for a <default MFA type>

    Examples:
      | MFA type    | recent MFA type | default MFA type |
      | WebAuthn    | TOTP            | TOTP             |
      | TOTP        | WebAuthn        | WebAuthn         |
      | TOTP        | backup code     | backup code      |
      | backup code | TOTP            | TOTP             |

  Scenario: Defaulting to the manager code despite having a used mfa
    Given I provide credentials that have a manager code, a WebAuthn and a more recently used TOTP
    When I log in
    Then I should see a prompt for a manager rescue code

  Scenario Outline: When to show the link to send a manager rescue code
    Given I provide credentials that have <WebAuthn?><TOTP?><backup codes?>
    And the user <has or does not have> a manager email
    When I log in
    Then I <should or should not> see a link to send a code to the user's manager

    Examples:
      | WebAuthn? | TOTP?  | backup codes?  | has or does not have | should or should not |
      | WebAuthn  |        |                | has                  | should               |
      | WebAuthn  | , TOTP |                | has                  | should               |
      | WebAuthn  |        | , backup codes | has                  | should               |
      | WebAuthn  | , TOTP | , backup codes | has                  | should               |
      |           | TOTP   |                | has                  | should               |
      |           | TOTP   | , backup codes | has                  | should               |
      |           |        | backup codes   | has                  | should               |
      | WebAuthn  |        |                | does not have        | should not           |
      | WebAuthn  | , TOTP |                | does not have        | should not           |
      | WebAuthn  |        | , backup codes | does not have        | should not           |
      | WebAuthn  | , TOTP | , backup codes | does not have        | should not           |
      |           | TOTP   |                | does not have        | should not           |
      |           | TOTP   | , backup codes | does not have        | should not           |
      |           |        | backup codes   | does not have        | should not           |

  Scenario: Ask for a code to be sent to my manager
    Given I provide credentials that have backup codes
    And the user has a manager email
    And I log in
    When I click the Request Assistance link
    Then there should be a way to request a manager code

  Scenario: Submit a code sent to my manager at an earlier time
    Given I provide credentials that have a manager code
    And I log in
    When I submit the correct manager code
      # because profile review is required after using a manager code:
    And I click the remind-me-later button
    Then I should end up at my intended destination

  Scenario: Submit a correct manager code
    Given I provide credentials that have backup codes
    And the user has a manager email
    And I log in
    And I click the Request Assistance link
    And I click the Send a code link
    When I submit the correct manager code
      # because profile review is required after using a manager code:
    And I click the remind-me-later button
    Then I should end up at my intended destination

  Scenario: Submit an incorrect manager code
    Given I provide credentials that have backup codes
    And the user has a manager email
    And I log in
    And I click the Request Assistance link
    And I click the Send a code link
    When I submit an incorrect manager code
    Then I should see a message that it was incorrect

  Scenario: Ask for assistance, but change my mind
    Given I provide credentials that have backup codes
    And the user has a manager email
    And I log in
    And I click the Request Assistance link
    When I click the Cancel button
    Then I should see a prompt for a backup code

  Scenario: Offer 2SV recovery when we have recovery config
    Given I provide credentials that have backup codes
    And we have recovery-contacts config
    When I log in
    Then I should see a link to send a code to a recovery contact
