<?php

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use PHPUnit\Framework\Assert;

/**
 * Verifies loginfinalizer:LogToDynamo logs the TRUE originating SP when
 * proxied through the Hub (via the SAML Scoping/RequesterID mechanism),
 * rather than the Hub's own entity ID. The plain-IdP (no Hub) SP-resolution
 * branch is covered at the unit level by SpEntityIdTest -- see
 * features/dynamo-logging.feature for why a live "no Hub" Behat scenario
 * isn't wired up in this dev topology.
 */
class DynamoLoggingContext extends FeatureContext
{
    private const DYNAMO_ENDPOINT = 'http://dynamo:8000';
    private const DYNAMO_TABLE = 'sildisco_local_user-log';

    // Seeded by development/m991231_235959_insert_test_users.php.
    private const HUB_LOGIN_USERNAME = 'sildisco_idp2';
    private const HUB_LOGIN_PASSWORD = 'sildisco_password';

    /**
     * @When I log in to the Hub as the sildisco IDP 2 test user
     */
    public function iLogInToTheHubAsTheSildiscoIdp2TestUser(): void
    {
        $this->username = self::HUB_LOGIN_USERNAME;
        $this->password = self::HUB_LOGIN_PASSWORD;
        $this->iLogIn();
    }

    /**
     * @Then a Dynamo log entry for SP :spEntityId and employee :employeeNumber should exist
     */
    public function aDynamoLogEntryShouldExist(string $spEntityId, string $employeeNumber): void
    {
        $item = $this->findLogEntry($spEntityId, $employeeNumber);

        Assert::assertNotNull(
            $item,
            sprintf(
                'Expected a Dynamo log entry with SP=%s and EmployeeNumber=%s, found none.',
                var_export($spEntityId, true),
                var_export($employeeNumber, true)
            )
        );
    }

    private function findLogEntry(string $spEntityId, string $employeeNumber): ?array
    {
        $client = new DynamoDbClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => self::DYNAMO_ENDPOINT,
            'credentials' => ['key' => 'local', 'secret' => 'local'],
        ]);

        $result = $client->scan([
            'TableName' => self::DYNAMO_TABLE,
            'FilterExpression' => 'SP = :sp AND EmployeeNumber = :emp',
            'ExpressionAttributeValues' => [
                ':sp' => ['S' => $spEntityId],
                ':emp' => ['S' => $employeeNumber],
            ],
        ]);

        $items = $result['Items'] ?? [];
        if (empty($items)) {
            return null;
        }

        $marshaler = new Marshaler();
        return $marshaler->unmarshalItem($items[0]);
    }
}
