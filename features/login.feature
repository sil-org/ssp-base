Feature: User login
  In order to log in
  As a user
  I need to provide an acceptable username and password

  Rules
  - Username and password are both required.
  - The user is only allowed through if all of the necessary checks pass.

  Scenario: Failing to provide a username
    Given I provide a password
    But I do not provide a username
    When I try to log in
    Then I should see an error message with "username" in it
    And I should not be allowed through

  Scenario: Failing to provide a password
    Given I provide a username
    But I do not provide a password
    When I try to log in
    Then I should see an error message with "password" in it
    And I should not be allowed through

  Scenario: Enough failed logins to require a captcha for a username
    Given I provide a username
    And I provide the correct password for that username
    But that username has enough failed logins to require a captcha
    And I fail the captcha
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through

  Scenario: Enough failed logins to require a captcha for an IP address
    Given my request comes from IP address "11.22.33.44"
    And I provide a username
    And I provide the correct password for that username
    And that username does not have enough failed logins to require a captcha
    But my IP address has enough failed logins to require a captcha
    And I fail the captcha
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through

  Scenario: Trying to log in with a rate-limited username
    Given I provide a username
    And I provide a password
    But that username has enough failed logins to be blocked by the rate limit
    When I try to log in
    Then I should see an error message telling me to wait
    And that username should be blocked for awhile
    And I should not be allowed through

  Scenario: Trying to log in with a rate-limited IP address
    Given I provide a username
    And I provide a password
    And my request comes from IP address "11.22.33.44"
    And that IP address has triggered the rate limit
    When I try to log in
    Then I should see an error message telling me to wait
    And that IP address should be blocked for awhile
    And I should not be allowed through

  Scenario: Providing unacceptable credentials
    Given I provide a username
    And that username has no recent failed login attempts
    But I provide an incorrect password
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through

  Scenario: Providing unacceptable credentials that trigger a rate limit
    Given I provide a username
    And that username will be rate limited after one more failed attempt
    And I pass the captcha
    But I provide an incorrect password
    When I try to log in
    Then I should see an error message telling me to wait
    And that username should be blocked for awhile
    And I should not be allowed through

  Scenario: Providing a correct username-password combination
    Given I provide a username
    And I provide the correct password for that username
    And that username has no recent failed login attempts
    When I try to log in
    Then I should not see an error message
    And I should be allowed through
    And I should have the correct attributes

  Scenario: Providing too many incorrect username-password combinations
    Given I provide a username
    And I provide an incorrect password
    And I pass any captchas
    When I try to log in enough times to trigger the rate limit
    Then I should see an error message telling me to wait
    And that username should be blocked for awhile
    And I should not be allowed through

  Scenario: Providing correct credentials after one failed login attempt
    Given I provide a username
    And I provide an incorrect password
    And I try to log in
    But I then provide the correct password for that username
    When I try to log in
    Then I should not see an error message
    And I should be allowed through
    And that username's failed login attempts should be at 0

  Scenario: Being told about how long to wait (due to rate limiting bad logins)
    Given I provide a username
    And I provide the correct password for that username
    But that username has 5 more recent failed logins than the limit
    When I try to log in
    Then I should see an error message with "30" and "seconds" in it
    And that username should be blocked for awhile
    And I should not be allowed through

  Scenario: Logging in right before a username rate limit has expired
    Given I provide a username
    And I provide the correct password for that username
    And 24 seconds ago that username had 5 more failed logins than the limit
    And I pass any captchas
    When I try to log in
    Then I should see an error message with "5" and "seconds" in it
    And I should not be allowed through

  Scenario: Logging in right after a username rate limit has expired
    Given I provide a username
    And I provide the correct password for that username
    And 26 seconds ago that username had 5 more failed logins than the limit
    And I pass any captchas
    When I try to log in
    Then I should not see an error message
    And I should be allowed through

  Scenario: Logging in right before an IP address rate limit has expired
    Given my request comes from IP address "11.22.33.44"
    And I provide a username
    And I provide the correct password for that username
    And 24 seconds ago the IP address "11.22.33.44" had 5 more failed logins than the limit
    And I pass any captchas
    When I try to log in
    Then I should see an error message with "5" and "seconds" in it
    And I should not be allowed through

  Scenario: Logging in right after an IP address rate limit has expired
    Given my request comes from IP address "11.22.33.44"
    And I provide a username
    And I provide the correct password for that username
    And 26 seconds ago the IP address "11.22.33.44" had 5 more failed logins than the limit
    And I pass any captchas
    When I try to log in
    Then I should not see an error message
    And I should be allowed through

  Scenario: No failed logins (and thus no captcha requirement)
    Given I provide a username
    When that username has no recent failed login attempts
    Then I should not have to pass a captcha test for that user

  Scenario: Not restricting requests from a trusted IPv4 address
    Given I provide a username
    And I provide an incorrect password
    And my request comes from IP address "11.22.33.44"
    But "11.22.33.44" is a trusted IP address
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through
    But the IP address "11.22.33.44" should not have any failed login attempts

  Scenario: Not restricting requests from a trusted IPv6 address
    Given I provide a username
    And I provide an incorrect password
    And my request comes from IP address "2001:0db8:85a3:0000:0000:8a2e:0370:7334"
    But "2001:0db8:85a3::8a2e:0370:7334" is a trusted IP address
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through
    But the IP address "2001:0db8:85a3:0000:0000:8a2e:0370:7334" should not have any failed login attempts

  Scenario: Not restricting requests from an IP address in a trusted range
    Given I provide a username
    And I provide an incorrect password
    And my request comes from IP address "11.22.33.44"
    But "11.22.33.0/24" is a trusted IP address range
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through
    But the IP address "11.22.33.44" should not have any failed login attempts

  Scenario: Restricting requests from an IP address NOT in a trusted range
    Given I provide a username
    And I provide an incorrect password
    And my request comes from IP address "11.22.33.44"
    And "11.22.55.0/24" is a trusted IP address range
    When I try to log in
    Then I should see a generic invalid-login error message
    And I should not be allowed through
    And the IP address "11.22.33.44" should have a failed login attempt

  Scenario: Invalid response from the ID Broker
    Given I provide a username
    And I provide the correct password for that username
    And that username has no recent failed login attempts
    But the ID Broker is returning invalid responses
    When I try to log in
    Then I should see a generic try-later error message
    And I should not be allowed through
