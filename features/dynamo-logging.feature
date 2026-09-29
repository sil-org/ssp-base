Feature: DynamoDB login logging on the authoritative IdP
  In order to have an accurate audit trail of who authenticated where
  loginfinalizer:LogToDynamo must log the TRUE originating SP
  Even when it runs on the authoritative IdP behind a Hub

  # The plain-IdP (no Hub, no saml:RequesterID) SP-resolution branch is covered at the unit
  # level by SpEntityIdTest -- see features/mfa.feature's NOTE comment (and sil-org/ssp-base#482)
  # for why a live "SP direct to IdP, no Hub" Behat scenario isn't wired up in this dev topology.
  Scenario: A login proxied through the Hub logs the true originating SP, not the Hub
    When I go to the SP1 login page
    And I click on the "IDP 2" tile
    And I log in to the Hub as the sildisco IDP 2 test user
    Then a Dynamo log entry for SP "https://ssp-sp1.local" and employee "10001" should exist
