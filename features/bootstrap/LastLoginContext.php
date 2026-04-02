<?php

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit\Framework\Assert;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;

/**
 * Behat context for testing when last_login_utc is (and is not) updated.
 */
class LastLoginContext extends MfaContext
{
    /**
     * @BeforeScenario
     */
    public function clearLastLoginTracking(BeforeScenarioScope $scope): void
    {
        FakeIdBrokerClient::clearLastLoginUpdatedFile();
    }

    /**
     * @Then the last login should have been updated
     */
    public function theLastLoginShouldHaveBeenUpdated(): void
    {
        $updatedEmployeeIds = FakeIdBrokerClient::getUpdatedLastLoginEmployeeIds();
        Assert::assertNotEmpty(
            $updatedEmployeeIds,
            'Expected last login to have been updated, but updateUserLastLogin was not called.'
        );
    }

    /**
     * @Then the last login should NOT have been updated
     */
    public function theLastLoginShouldNotHaveBeenUpdated(): void
    {
        $updatedEmployeeIds = FakeIdBrokerClient::getUpdatedLastLoginEmployeeIds();
        Assert::assertEmpty(
            $updatedEmployeeIds,
            sprintf(
                'Expected last login NOT to have been updated, but updateUserLastLogin was called for: %s',
                implode(', ', $updatedEmployeeIds)
            )
        );
    }
}
