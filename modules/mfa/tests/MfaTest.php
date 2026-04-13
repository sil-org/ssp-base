<?php

require_once __DIR__ . '/SpyIdBrokerClient.php';

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\Module\mfa\Auth\Process\Mfa;

class MfaTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    protected function setUp(): void
    {
        SpyIdBrokerClient::reset();
    }

    public function testMaskEmail()
    {
        $this->assertEquals("j**n@e******.c**", Mfa::maskEmail("john@example.com"));
        $this->assertEquals("j***_s***h@e******.c**", Mfa::maskEmail("john_smith@example.com"));
        $this->assertEquals("t**t@t***.e******.c**", Mfa::maskEmail("test@test.example.com"));
        $this->assertEquals("t@e.c*", Mfa::maskEmail("t@e.cc"));

        // just to be sure it doesn't throw an exception...
        $this->assertEquals("t**t@e******..c**", Mfa::maskEmail("test@example..com"));
        $this->assertEquals("@", Mfa::maskEmail("@"));
    }

    /**
     * Scenario 5 from IDP-1807: a user who has provided correct credentials
     * and is being walked to the MFA setup URL should have last_login_utc
     * updated. Exercises Mfa::redirectToMfaSetup() directly.
     */
    public function testRedirectToMfaSetup_UpdatesLastLogin(): void
    {
        $state = [
            'employeeId' => 'EMP-100',
            'mfaSetupUrl' => 'https://example.org/mfa-setup',
            'loggerClass' => NullLogger::class,
            'idBrokerConfig' => $this->buildIdBrokerConfig(),
        ];

        // redirectToMfaSetup() ends with a call to redirectTrustedURL(),
        // which throws/exits in a non-HTTP test environment. The
        // updateUserLastLogin() call happens BEFORE that redirect, so we
        // catch the redirect exception and then assert on the spy.
        try {
            Mfa::redirectToMfaSetup($state);
        } catch (\Throwable $t) {
            // Expected: SimpleSAMLphp's redirect helpers throw under PHPUnit.
        }

        $this->assertSame(
            ['EMP-100'],
            SpyIdBrokerClient::$updateLastLoginCalls,
            'Expected redirectToMfaSetup() to call updateUserLastLogin() exactly once with the employee id.'
        );
    }

    /**
     * Scenario 2 from IDP-1807: correct credentials and no 2SV required ->
     * last_login_utc should be updated. Exercises Mfa::process() falling
     * through to the post-MFA-block updateUserLastLogin() call.
     */
    public function testProcess_NoMfaRequired_UpdatesLastLogin(): void
    {
        $mfa = $this->buildMfaFilter();

        $state = [
            'Attributes' => [
                'employeeNumber' => ['EMP-200'],
                'mfa' => [
                    'prompt' => 'no',
                    'options' => [],
                ],
            ],
        ];

        $mfa->process($state);

        $this->assertSame(
            ['EMP-200'],
            SpyIdBrokerClient::$updateLastLoginCalls,
            'Expected process() with mfa.prompt=no to call updateUserLastLogin() exactly once.'
        );
    }

    /**
     * Scenario 3 from IDP-1807: a user who fails 2-step verification
     * (wrong code) should NOT have last_login_utc updated.
     */
    public function testValidateMfaSubmission_WrongCode_DoesNotUpdateLastLogin(): void
    {
        $state = [
            'employeeId' => 'EMP-300',
            'idBrokerConfig' => $this->buildIdBrokerConfig(),
        ];

        $result = Mfa::validateMfaSubmission(
            1,
            'EMP-300',
            'wrong-code',
            $state,
            false,
            new NullLogger(),
            'totp',
            'https://example.org'
        );

        $this->assertNotEmpty($result, 'Expected an error message for incorrect MFA code.');
        $this->assertSame(
            [],
            SpyIdBrokerClient::$updateLastLoginCalls,
            'Expected validateMfaSubmission() NOT to call updateUserLastLogin() when MFA verification fails.'
        );
    }

    /**
     * Remember-me cookie edge case from IDP-1807: when a valid remember-me
     * cookie bypasses the MFA prompt, last_login_utc should still be updated.
     */
    public function testIsRememberMeCookieValid_ValidCookie_UpdatesLastLogin(): void
    {
        $employeeId = 'EMP-400';
        $expireDate = time() + 86400;
        $mfaOptions = [
            ['id' => 101, 'type' => 'totp'],
        ];
        $state = [
            'employeeId' => $employeeId,
            'idBrokerConfig' => $this->buildIdBrokerConfig(),
        ];

        putenv('REMEMBER_ME_SECRET=test-secret-for-unit-test');
        try {
            $expectedString = Mfa::generateRememberMeCookieString(
                'test-secret-for-unit-test',
                $employeeId,
                $expireDate,
                $mfaOptions
            );
            $cookieHash = password_hash($expectedString, PASSWORD_DEFAULT);

            $isValid = Mfa::isRememberMeCookieValid(
                $cookieHash,
                (string)$expireDate,
                $mfaOptions,
                $state
            );

            $this->assertTrue($isValid, 'Expected isRememberMeCookieValid() to return true for a valid cookie.');
            $this->assertSame(
                ['EMP-400'],
                SpyIdBrokerClient::$updateLastLoginCalls,
                'Expected isRememberMeCookieValid() to call updateUserLastLogin() exactly once for a valid cookie.'
            );
        } finally {
            putenv('REMEMBER_ME_SECRET');
        }
    }

    /**
     * Scenario 4 from IDP-1807: a user who successfully completes 2-step
     * verification should have last_login_utc updated.
     */
    public function testValidateMfaSubmission_SuccessfulMfa_UpdatesLastLogin(): void
    {
        $state = [
            'employeeId' => 'EMP-300',
            'idBrokerConfig' => $this->buildIdBrokerConfig(),
            'mfaOptions' => [],
            'Attributes' => [],
        ];

        try {
            Mfa::validateMfaSubmission(
                1,
                'EMP-300',
                '111111',
                $state,
                false,
                new NullLogger(),
                'totp',
                'https://example.org'
            );
        } catch (\Throwable $t) {
            // Expected: the success path calls ProcessingChain::resumeProcessing()
            // and clearRememberMeCookies(), both of which throw under PHPUnit.
        }

        $this->assertSame(
            ['EMP-300'],
            SpyIdBrokerClient::$updateLastLoginCalls,
            'Expected validateMfaSubmission() to call updateUserLastLogin() exactly once after successful MFA verification.'
        );
    }

    private function buildMfaFilter(): Mfa
    {
        return new Mfa([
            'mfaSetupUrl' => 'https://example.org/mfa-setup',
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => 'fake-token',
            'idBrokerBaseUri' => 'https://example.org/broker',
            'idBrokerClientClass' => SpyIdBrokerClient::class,
            'idpDomainName' => 'example.org',
            'loggerClass' => NullLogger::class,
        ], null);
    }

    private function buildIdBrokerConfig(): array
    {
        return [
            'accessToken' => 'fake-token',
            'assertValidIp' => false,
            'baseUri' => 'https://example.org/broker',
            'clientClass' => SpyIdBrokerClient::class,
            'trustedIpRanges' => [],
        ];
    }
}
