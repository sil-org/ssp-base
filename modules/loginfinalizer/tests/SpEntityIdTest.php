<?php

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\loginfinalizer\SpEntityId;

class SpEntityIdTest extends TestCase
{
    public function testPrefersRequesterIdWhenPresent(): void
    {
        $state = [
            'saml:RequesterID' => ['https://true-sp.example'],
            'SPMetadata' => ['entityid' => 'https://ssp-hub.local'],
        ];

        $this->assertSame('https://true-sp.example', SpEntityId::resolve($state));
    }

    public function testFallsBackToSpMetadataOnPlainIdp(): void
    {
        $state = [
            'SPMetadata' => ['entityid' => 'https://plain-sp.example'],
        ];

        $this->assertSame('https://plain-sp.example', SpEntityId::resolve($state));
    }

    public function testFallsBackToDestinationWhenSpMetadataMissing(): void
    {
        $state = [
            'Destination' => ['entityID' => 'https://dest-sp.example'],
        ];

        $this->assertSame('https://dest-sp.example', SpEntityId::resolve($state));
    }

    public function testReturnsNullWhenNothingAvailable(): void
    {
        $this->assertNull(SpEntityId::resolve([]));
    }

    public function testIgnoresEmptyRequesterIdList(): void
    {
        $state = [
            'saml:RequesterID' => [],
            'SPMetadata' => ['entityid' => 'https://plain-sp.example'],
        ];

        $this->assertSame('https://plain-sp.example', SpEntityId::resolve($state));
    }
}
