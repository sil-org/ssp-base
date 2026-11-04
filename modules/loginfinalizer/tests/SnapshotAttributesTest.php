<?php

require_once __DIR__ . '/SpyIdBrokerClient.php';

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\core\Auth\Process\AttributeLimit;
use SimpleSAML\Module\loginfinalizer\Auth\Process\LogToDynamo;
use SimpleSAML\Module\loginfinalizer\Auth\Process\MarkLastLogin;
use SimpleSAML\Module\loginfinalizer\Auth\Process\SnapshotAttributes;

class SnapshotAttributesTest extends TestCase
{
    protected function setUp(): void
    {
        SpyIdBrokerClient::reset();
    }

    public function testProcess_CopiesAttributes(): void
    {
        $state = ['Attributes' => ['cn' => ['jdoe']]];

        $this->snapshot($state);

        $this->assertSame(['cn' => ['jdoe']], $state[SnapshotAttributes::STATE_KEY]);
    }

    public function testGet_PrefersSnapshotOverCurrentAttributes(): void
    {
        $state = [
            SnapshotAttributes::STATE_KEY => ['cn' => ['jdoe']],
            'Attributes' => ['mail' => ['jdoe@example.org']],
        ];

        $this->assertSame(['cn' => ['jdoe']], SnapshotAttributes::get($state));
    }

    public function testGet_FallsBackToCurrentAttributesWithoutSnapshot(): void
    {
        $state = ['Attributes' => ['cn' => ['jdoe']]];

        $this->assertSame(['cn' => ['jdoe']], SnapshotAttributes::get($state));
    }

    public function testGet_EmptyWhenNothingAvailable(): void
    {
        $this->assertSame([], SnapshotAttributes::get([]));
    }

    /**
     * Regression: a direct login to an SP whose metadata limits it to 'mail'
     * (e.g. Google) used to log no user and skip the last-login update,
     * because core:AttributeLimit (priority 50) strips the identifying
     * attributes before the finalizers (900/901) run.
     */
    public function testFinalizersStillIdentifyUserAfterAttributeLimit(): void
    {
        $state = $this->runChain(withSnapshot: true);

        // The SP still only receives what it's allowed to...
        $this->assertSame(['mail'], array_keys($state['Attributes']));

        // ...but the finalizers can still see who logged in.
        $item = $this->buildLogToDynamo()->buildLogContents($state);
        $this->assertSame('EMP-100', $item['EmployeeNumber']);
        $this->assertSame('jdoe', $item['CN']);
        $this->assertSame('jdoe@example.org', $item['EduPersonPrincipalName']);

        $this->buildMarkLastLogin()->process($state);
        $this->assertSame(['EMP-100'], SpyIdBrokerClient::$updateLastLoginCalls);
    }

    /** Documents the bug the snapshot fixes. */
    public function testWithoutSnapshot_AttributeLimitHidesTheUser(): void
    {
        $state = $this->runChain(withSnapshot: false);

        $item = $this->buildLogToDynamo()->buildLogContents($state);
        $this->assertArrayNotHasKey('EmployeeNumber', $item);
        $this->assertArrayNotHasKey('CN', $item);
        $this->assertArrayNotHasKey('EduPersonPrincipalName', $item);

        $this->buildMarkLastLogin()->process($state);
        $this->assertSame([], SpyIdBrokerClient::$updateLastLoginCalls);
    }

    /**
     * Runs SnapshotAttributes (optionally) then core:AttributeLimit configured
     * as in dockerbuild/config/config.php, for an SP that may receive only 'mail'.
     */
    private function runChain(bool $withSnapshot): array
    {
        $state = [
            'Attributes' => [
                'cn' => ['jdoe'],
                'eduPersonPrincipalName' => ['jdoe@example.org'],
                'employeeNumber' => ['EMP-100'],
                'mail' => ['jdoe@example.org'],
            ],
            'Destination' => ['entityid' => 'https://accounts.google.com/samlrp/x', 'attributes' => ['mail']],
            'Source' => ['entityid' => 'idp.example.org'],
            'SPMetadata' => ['entityid' => 'https://accounts.google.com/samlrp/x'],
        ];

        if ($withSnapshot) {
            $this->snapshot($state);
        }

        $limitConfig = ['default' => true, 'cn', 'eduPersonPrincipalName', 'employeeNumber', 'mail'];
        (new AttributeLimit($limitConfig, null))->process($state);

        return $state;
    }

    private function snapshot(array &$state): void
    {
        $config = []; // ProcessingFilter's constructor takes its config by reference
        (new SnapshotAttributes($config, null))->process($state);
    }

    private function buildLogToDynamo(): LogToDynamo
    {
        return new LogToDynamo(['dynamoRegion' => 'us-east-1', 'dynamoLogTable' => 'test-table'], null);
    }

    private function buildMarkLastLogin(): MarkLastLogin
    {
        return new MarkLastLogin([
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => 'fake-token',
            'idBrokerBaseUri' => 'https://example.org/broker',
            'idBrokerClientClass' => SpyIdBrokerClient::class,
        ], null);
    }
}
