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
