<?php

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\loginfinalizer\Auth\Process\LogToDynamo;

class LogToDynamoTest extends TestCase
{
    public function testBuildLogContents_UsesRequesterIdForSpWhenBehindAHub(): void
    {
        $filter = $this->buildFilter();
        $state = [
            'saml:RequesterID' => ['https://true-sp.example'],
            'SPMetadata' => ['entityid' => 'https://ssp-hub.local'],
            'Source' => ['entityid' => 'https://ssp-idp1.local'],
            'Attributes' => [
                'cn' => ['Jane Doe'],
                'employeeNumber' => ['12345'],
            ],
        ];

        $item = $filter->buildLogContents($state);

        $this->assertSame('https://true-sp.example', $item['SP']);
        $this->assertSame('https://ssp-idp1.local', $item['IDP']);
        $this->assertSame('Jane Doe', $item['CN']);
        $this->assertSame('12345', $item['EmployeeNumber']);
        $this->assertArrayNotHasKey('EduPersonPrincipalName', $item);
    }

    public function testBuildLogContents_FallsBackToSpMetadataOnPlainIdp(): void
    {
        $filter = $this->buildFilter();
        $state = [
            'SPMetadata' => ['entityid' => 'https://plain-sp.example'],
            'Source' => ['entityid' => 'https://ssp-idp1.local'],
            'Attributes' => [],
        ];

        $item = $filter->buildLogContents($state);

        $this->assertSame('https://plain-sp.example', $item['SP']);
    }

    public function testBuildLogContents_ReportsPlaceholdersWhenUnavailable(): void
    {
        $filter = $this->buildFilter();
        $state = ['Attributes' => []];

        $item = $filter->buildLogContents($state);

        $this->assertSame('SP entity ID not available', $item['SP']);
        $this->assertSame('IDP entity ID not available', $item['IDP']);
    }

    public function testProcess_MissingConfig_NoOpsWithoutThrowing(): void
    {
        $filter = new LogToDynamo([], null);
        $state = ['Attributes' => []];

        $filter->process($state);
        $this->addToAssertionCount(1);
    }

    private function buildFilter(): LogToDynamo
    {
        return new LogToDynamo([
            'dynamoRegion' => 'us-east-1',
            'dynamoLogTable' => 'test-table',
        ], null);
    }
}
