<?php

require_once __DIR__ . '/SpyIdBrokerClient.php';

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\loginfinalizer\Auth\Process\MarkLastLogin;

class MarkLastLoginTest extends TestCase
{
    protected function setUp(): void
    {
        SpyIdBrokerClient::reset();
    }

    public function testProcess_EmployeeIdPresent_UpdatesLastLogin(): void
    {
        $filter = $this->buildFilter();
        $state = [
            'Attributes' => [
                'employeeNumber' => ['EMP-100'],
            ],
        ];

        $filter->process($state);

        $this->assertSame(['EMP-100'], SpyIdBrokerClient::$updateLastLoginCalls);
    }

    public function testProcess_NoEmployeeIdAttribute_DoesNotCallBroker(): void
    {
        $filter = $this->buildFilter();
        $state = [
            'Attributes' => [],
        ];

        $filter->process($state);

        $this->assertSame([], SpyIdBrokerClient::$updateLastLoginCalls);
    }

    public function testProcess_MissingBaseUri_DoesNotCallBroker(): void
    {
        $filter = new MarkLastLogin([
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => 'fake-token',
            'idBrokerBaseUri' => null,
            'idBrokerClientClass' => SpyIdBrokerClient::class,
        ], null);

        $state = [
            'Attributes' => [
                'employeeNumber' => ['EMP-200'],
            ],
        ];

        $filter->process($state);

        $this->assertSame([], SpyIdBrokerClient::$updateLastLoginCalls);
    }

    public function testProcess_BrokerThrows_DoesNotThrowFromFilter(): void
    {
        $filter = new MarkLastLogin([
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => 'fake-token',
            'idBrokerBaseUri' => 'https://example.org/broker',
            'idBrokerClientClass' => ThrowingIdBrokerClient::class,
        ], null);

        $state = [
            'Attributes' => [
                'employeeNumber' => ['EMP-300'],
            ],
        ];

        // Should swallow the exception and simply not update anything.
        $filter->process($state);
        $this->addToAssertionCount(1);
    }

    private function buildFilter(): MarkLastLogin
    {
        return new MarkLastLogin([
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => 'fake-token',
            'idBrokerBaseUri' => 'https://example.org/broker',
            'idBrokerClientClass' => SpyIdBrokerClient::class,
        ], null);
    }
}

class ThrowingIdBrokerClient extends \Sil\SspBase\Features\fakes\FakeIdBrokerClient
{
    public function __construct(string $baseUri, string $accessToken, array $config = [])
    {
        // No-op
    }

    public function updateUserLastLogin(string $employeeId): array
    {
        throw new \RuntimeException('broker unavailable');
    }
}
