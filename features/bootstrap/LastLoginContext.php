<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\Assert;

class LastLoginContext extends FeatureContext
{
    private const BROKER_URL = 'http://broker';
    private const BROKER_KEY = 'test-cli-abc123';
    private const BAD_PASSWORD = 'definitely-not-the-right-password';

    // Seeded by development/m991231_235959_insert_test_users.php for employee_id "10001".
    private const CORRECT_PASSWORD = 'sildisco_password';

    private ?string $recordedLastLoginUtc = null;

    /**
     * @Given I record the current last_login_utc for user :employeeId
     */
    public function iRecordTheCurrentLastLoginUtcForUser(string $employeeId): void
    {
        $this->recordedLastLoginUtc = $this->fetchLastLoginUtc($employeeId);
    }

    /**
     * @When I attempt to log in as :username with a bad password
     */
    public function iAttemptToLogInAsWithABadPassword(string $username): void
    {
        $this->username = $username;
        $this->password = self::BAD_PASSWORD;
        $this->iLogIn();

        $page = $this->getSession()->getPage();
        $pageText = $page->getText();
        Assert::assertTrue(
            $page->has('css', '.alert-danger')
            || stripos($pageText, 'invalid login') !== false
            || stripos($pageText, 'incorrect') !== false
            || stripos($pageText, 'error') !== false
            || stripos($pageText, 'wrong') !== false
            || stripos($pageText, 'problem') !== false,
            'Expected an invalid-login error after submitting a wrong password; '
            . 'without a visible error the "unchanged" assertion below would be meaningless.'
        );
    }

    /**
     * @Then the last_login_utc for user :employeeId should be unchanged
     */
    public function theLastLoginUtcForUserShouldBeUnchanged(string $employeeId): void
    {
        $current = $this->fetchLastLoginUtc($employeeId);
        Assert::assertSame(
            $this->recordedLastLoginUtc,
            $current,
            sprintf(
                'last_login_utc for user %s changed: before=%s, after=%s',
                $employeeId,
                var_export($this->recordedLastLoginUtc, true),
                var_export($current, true)
            )
        );
    }

    /**
     * @When I log in as :username with the correct password
     */
    public function iLogInAsWithTheCorrectPassword(string $username): void
    {
        $this->username = $username;
        $this->password = self::CORRECT_PASSWORD;
        $this->iLogIn();
    }

    /**
     * @Then the last_login_utc for user :employeeId should be updated
     */
    public function theLastLoginUtcForUserShouldBeUpdated(string $employeeId): void
    {
        $current = $this->fetchLastLoginUtc($employeeId);
        Assert::assertNotSame(
            $this->recordedLastLoginUtc,
            $current,
            sprintf(
                'Expected last_login_utc for user %s to change, but it did not (still %s).',
                $employeeId,
                var_export($current, true)
            )
        );
    }

    private function fetchLastLoginUtc(string $employeeId): ?string
    {
        $client = new Client(['http_errors' => false]);
        $response = $client->get(
            sprintf('%s/user/%s', self::BROKER_URL, $employeeId),
            ['headers' => [
                'Authorization' => 'Bearer ' . self::BROKER_KEY,
                'Accept' => 'application/json',
            ]]
        );
        Assert::assertSame(
            200,
            $response->getStatusCode(),
            sprintf('Broker returned HTTP %d when fetching user %s', $response->getStatusCode(), $employeeId)
        );
        $data = json_decode((string)$response->getBody(), true);
        return $data['last_login_utc'] ?? null;
    }
}
