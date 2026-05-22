<?php

namespace Sil\IdPHubTests;

include __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Sil\PhpEnv\Env;
use Sil\SspUtils\Utils;
use SimpleSAML\Configuration;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\sildisco\IdPDisco;

class MetadataTest extends TestCase
{
    // SP Metadata entry to request that certain tests be skipped
    const SkipTestsKey = "SkipTests";

    const IdpCode = 'IDPNamespace';

    // Required for IdP's when in Hub mode. The logo caption is the
    // text that goes under an IdP's logo on the discovery page.
    const LogoCaptionKey = 'logoCaption';

    const SPNameKey = 'name';

    public $metadataPath = __DIR__ . '/../vendor/simplesamlphp/simplesamlphp/metadata';

    public static function setUpBeforeClass(): void
    {
        // override configuration to bypass the ssp-base config file that has required environment variables
        Configuration::setPreLoadedConfig(Configuration::loadFromArray([
            'module.enable' => ['sildisco' => true], // for IdPDisco::getIdpsForSp utility function
        ]));
    }

    public function testIDPRemoteMetadataIDPCode()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();

        foreach ($idpEntries as $entityId => $entry) {
            $this->assertTrue(isset($entry[self::IdpCode]), 'Metadata entry does not ' .
                'include an ' . self::IdpCode . ' element as expected. IDP: ' . $entityId);

            $nextCode = $entry[self::IdpCode];
            $this->assertIsString($nextCode, 'Metadata entry has an ' .
                self::IdpCode . 'element that is not a string. IDP: ' . $entityId);
            $this->assertMatchesRegularExpression("/^[A-Za-z0-9_-]+$/", $nextCode, 'Metadata entry has an ' .
                self::IdpCode . ' element that has something other than letters, ' .
                'numbers, hyphens and underscores. IDP: ' . $entityId);
        }
    }

    public function testIDPRemoteMetadataBadSPList()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }

        $badIdps = [];

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();
        $spListKey = Utils::SP_LIST_KEY;

        foreach ($idpEntries as $entityId => $entry) {
            if (isset($entry[$spListKey]) && !is_array($entry[$spListKey])) {
                $badIdps[] = $entityId;
            }
        }

        $this->assertEmpty($badIdps,
            "At least one IdP has an " .
            $spListKey . " entry that is not an array ... " . PHP_EOL .
            var_export($badIdps, True));
    }

    public function testIDPRemoteMetadataMissingLogoCaption()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }

        $badIdps = [];

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();

        foreach ($idpEntries as $entityId => $entry) {
            if (!isset($entry[self::LogoCaptionKey])) {
                $badIdps[] = $entityId;
            }
        }

        $this->assertEmpty($badIdps,
            "At least one IdP is missing a " .
            self::LogoCaptionKey . " entry ... " . PHP_EOL .
            var_export($badIdps, True));
    }


    public function testIDPRemoteMetadataBadSPListEntry()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        $idpEntries = $metadata->getList();
        $spListKey = Utils::SP_LIST_KEY;

        foreach ($idpEntries as $entityId => $entry) {
            if (isset($entry[$spListKey]) && is_array($entry[$spListKey])) {
                foreach ($entry[$spListKey] as $nextSp) {
                    if (!isset($spEntries[$nextSp])) {
                        $badSps[] = $nextSp;
                    }
                }
            }
        }

        $this->assertEmpty($badSps,
            "At least one non-existent SP is listed in an IdP's " .
            $spListKey . " entry ... " . PHP_EOL .
            var_export($badSps, True));
    }


    public function testIDPRemoteMetadataNoDuplicateIDPCode()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();
        $codes = [];

        foreach ($idpEntries as $entityId => $entry) {
            $nextCode = $entry[self::IdpCode];
            $this->assertNotContains($nextCode, $codes,
                "Metadata has a duplicate " . self::IdpCode . " entry: " . $nextCode);
            $codes[] = $nextCode;
        }
    }

    public function testMetadataNoDuplicateEntities()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');
        $entities = [];
        foreach ($spEntries as $entityId => $entity) {
            $this->assertNotContains($entityId, $entities, 'Duplicate SP entityId found: ' . $entityId);
            $entities[] = $entityId;
        }

        $idpEntries = $metadata->getList();
        foreach ($idpEntries as $entityId => $entity) {
            $this->assertNotContains($entityId, $entities, 'Duplicate IdP entityId found: ' . $entityId);
            $entities[] = $entityId;
        }
    }

    public function testMetadataNoSpsWithoutAnIdp()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];
        foreach ($spEntries as $spEntityId => $spEntry) {
            $results = IdPDisco::getIdpsForSp($spEntityId);

            if (empty($results)) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            "At least one SP does not have an IdP it is allowed to use ... " .
            var_export($badSps, True));
    }

    public function testMetadataBadIdpName()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();

        $badNames = [];

        foreach ($idpEntries as $idpEntityId => $idpEntry) {
            if (empty($idpEntry['name']['en'])) {
                $badNames[] = $idpEntityId;
            }
        }

        $this->assertEmpty($badNames,
            "The following Idp's do not have a 'name' entry as an array with an 'en' entry ... " .
            var_export($badNames, True));
    }

    public function testMetadataMissingLogoURL()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();

        $badLogos = [];

        foreach ($idpEntries as $idpEntityId => $idpEntry) {
            if (empty($idpEntry['logoURL'])) {
                $badLogos[] = $idpEntityId;
            }
        }

        $this->assertEmpty($badLogos,
            "The following Idp's do not have a 'logoURL' entry ... " .
            var_export($badLogos, True));
    }

    public function testMetadataSPWithBadIDPList()
    {
        $idpListKey = Utils::IDP_LIST_KEY;
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            $nextBad = [];
            if (!empty($spEntry[$idpListKey])) {
                foreach ($spEntry[$idpListKey] as $nextIdp) {
                    if (empty($idpEntries[$nextIdp])) {
                        $nextBad[] = $nextIdp;
                    }
                }
                if (!empty($nextBad)) {
                    $badSps[$spEntityId] = $nextBad;
                }
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has an IDPList with a bad IDP entity id ... ' . var_export($badSps, True));

    }

    public function testMetadataSPWithNoIDPList()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }
        $idpListKey = Utils::IDP_LIST_KEY;
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            if (empty($spEntry[$idpListKey])) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has an empty IDPList entry (required) ... ' .
            var_export($badSps, True));
    }

    public function testMetadataSPWithNoName()
    {
        $hubMode = Env::get('HUB_MODE', true);
        if (!$hubMode) {
            $this->markTestSkipped('Skipping test because HUB_MODE = false');
            return;
        }

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            if (empty($spEntry[self::SPNameKey])) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has an empty "' . self::SPNameKey . '" entry (required) ... ' .
            var_export($badSps, True));
    }

    public function testMetadataCerts()
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {

            if (!empty($spEntry[self::SkipTestsKey])) {
                continue;
            }

            if (empty($spEntry['certData'])) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has no certData entry ... ' .
            var_export($badSps, True));

    }

    public function testMetadataSignResponse()
    {
        // $this->markTestSkipped('Disabled for testing/verification');
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            if (!empty($spEntry[self::SkipTestsKey])) {
                continue;
            }

            if (isset($spEntry['saml20.sign.response']) &&
                $spEntry['saml20.sign.response'] === False) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has saml20.sign.response set to false ... ' .
            var_export($badSps, True));
    }

    public function testMetadataSignAssertion()
    {
        // $this->markTestSkipped('Disabled for testing/verification');
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            if (!empty($spEntry[self::SkipTestsKey])) {
                continue;
            }

            if (isset($spEntry['saml20.sign.assertion']) &&
                $spEntry['saml20.sign.assertion'] === False) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP has saml20.sign.assertion set to false ... ' .
            var_export($badSps, True));
    }

    public function testMetadataEncryption()
    {
        // $this->markTestSkipped('Wait until we require encryption.');
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spEntries = $metadata->getList('saml20-sp-remote');

        $badSps = [];

        foreach ($spEntries as $spEntityId => $spEntry) {
            if (!empty($spEntry[self::SkipTestsKey])) {
                continue;
            }

            if (empty($spEntry['assertion.encryption'])) {
                $badSps[] = $spEntityId;
            }
        }

        $this->assertEmpty($badSps,
            'At least one SP does not have assertion.encryption set to True ... ' .
            var_export($badSps, True));
    }

    public function testAdminModule()
    {
        $config = Configuration::getConfig();

        $moduleEnable = $config->getArray('module.enable');
        var_dump(['module.enable' => $moduleEnable]);
        $this->assertNotEmpty($moduleEnable);

        $this->assertFalse($moduleEnable['admin'] ?? false);
    }
}
