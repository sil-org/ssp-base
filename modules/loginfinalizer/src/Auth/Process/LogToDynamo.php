<?php

namespace SimpleSAML\Module\loginfinalizer\Auth\Process;

use Aws\DynamoDb\Marshaler;
use Aws\Sdk;
use Exception;
use Sil\PhpEnv\Env;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Logger;
use SimpleSAML\Module\loginfinalizer\SpEntityId;

/**
 * Logs information about each successful login to an AWS DynamoDB table.
 *
 * Intended to run on the authoritative IdP -- not on a Hub/proxy in front of
 * it -- so the SP field reflects the true originating SP via
 * SpEntityId::resolve(), rather than the Hub's own entity ID.
 *
 * Required config: 'dynamoRegion' (e.g. 'us-east-1'), 'dynamoLogTable'
 * (e.g. 'sildisco_prod_user-log'). Optional: 'dynamoEndpoint', only needed
 * for local dynamodb-local testing.
 */
class LogToDynamo extends ProcessingFilter
{
    private const SECONDS_PER_YEAR = 31536000; // 60 * 60 * 24 * 365

    private ?string $dynamoEndpoint;

    private ?string $dynamoRegion;

    private ?string $dynamoLogTable;

    public function __construct(array $config, mixed $reserved)
    {
        parent::__construct($config, $reserved);

        $this->dynamoEndpoint = $config['dynamoEndpoint'] ?? null;
        $this->dynamoRegion = $config['dynamoRegion'] ?? null;
        $this->dynamoLogTable = $config['dynamoLogTable'] ?? null;
    }

    public function process(array &$state): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $logContents = $this->buildLogContents($state);

        try {
            $this->getDynamoDbClient()->putItem([
                'TableName' => $this->dynamoLogTable,
                'Item' => (new Marshaler())->marshalJson(json_encode($logContents)),
            ]);
        } catch (Exception $e) {
            Logger::error('loginfinalizer: Unable to log to Dynamo: ' . $e->getMessage());
        }
    }

    /**
     * Build the log item for the current state. Public (despite having no
     * external callers besides tests) because it's pure/side-effect-free and
     * is the part of this filter worth unit testing without a real Dynamo
     * connection.
     */
    public function buildLogContents(array $state): array
    {
        return array_merge(
            $this->getUserAttributes($state),
            [
                'ID' => uniqid(),
                'IDP' => $this->getIdp($state),
                'SP' => SpEntityId::resolve($state) ?? 'SP entity ID not available',
                'Time' => date('Y-m-d H:i:s'),
                'ExpiresAt' => time() + self::SECONDS_PER_YEAR,
            ]
        );
    }

    private function isConfigured(): bool
    {
        return !empty($this->dynamoRegion) && !empty($this->dynamoLogTable);
    }

    private function getDynamoDbClient()
    {
        $sdkConfig = [
            'region' => $this->dynamoRegion,
        ];

        $awsKey = Env::getString('AWS_ACCESS_KEY_ID', Env::getString('DYNAMO_ACCESS_KEY_ID', ''));
        $awsSecret = Env::getString('AWS_SECRET_ACCESS_KEY', Env::getString('DYNAMO_SECRET_ACCESS_KEY', ''));
        if (!empty($awsKey) && !empty($awsSecret)) {
            $sdkConfig['credentials'] = [
                'key' => $awsKey,
                'secret' => $awsSecret,
            ];
        }

        if (!empty($this->dynamoEndpoint)) {
            $sdkConfig['endpoint'] = $this->dynamoEndpoint;
        }

        return (new Sdk($sdkConfig))->createDynamoDb();
    }

    private function getIdp(array $state): string
    {
        return $state['Source']['entityid']
            ?? $state['Source']['entityID']
            ?? 'IDP entity ID not available';
    }

    // Get the current user's common name attribute and/or eduPersonPrincipalName and/or employeeNumber
    private function getUserAttributes(array $state): array
    {
        $attributes = $state['Attributes'] ?? [];

        $userAttrs = [];
        $userAttrs = $this->addUserAttribute(
            $userAttrs,
            'CN',
            $this->getAttributeFrom($attributes, 'urn:oid:2.5.4.3', 'cn')
        );
        $userAttrs = $this->addUserAttribute(
            $userAttrs,
            'EduPersonPrincipalName',
            $this->getAttributeFrom($attributes, 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6', 'eduPersonPrincipalName')
        );
        return $this->addUserAttribute(
            $userAttrs,
            'EmployeeNumber',
            $this->getAttributeFrom($attributes, 'urn:oid:2.16.840.1.113730.3.1.3', 'employeeNumber')
        );
    }

    private function getAttributeFrom(array $attributes, string $oidKey, string $friendlyKey): string
    {
        if (!empty($attributes[$oidKey])) {
            return $attributes[$oidKey][0];
        }

        if (!empty($attributes[$friendlyKey])) {
            return $attributes[$friendlyKey][0];
        }

        return '';
    }

    // Dynamodb rejects empty-string values, so only include attributes that have a value.
    private function addUserAttribute(array $attributes, string $attrKey, string $attr): array
    {
        if (!empty($attr)) {
            $attributes[$attrKey] = $attr;
        }

        return $attributes;
    }
}
