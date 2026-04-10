<?php

use Sil\SspBase\Features\fakes\FakeIdBrokerClient;

/**
 * Spy broker client for MFA unit tests. Records each updateUserLastLogin()
 * call in a static array so tests can assert that the right employee IDs
 * were passed.
 *
 * Extends FakeIdBrokerClient so it satisfies the IdBrokerClient|FakeIdBrokerClient
 * return type on Mfa::getIdBrokerClient(). Injected via the idBrokerClientClass
 * AuthProc config option — no production code changes required.
 */
class SpyIdBrokerClient extends FakeIdBrokerClient
{
    /** @var string[] employee ids passed to updateUserLastLogin(), in call order */
    public static array $updateLastLoginCalls = [];

    public function __construct(string $baseUri, string $accessToken, array $config = [])
    {
        // No-op: tests don't need a real HTTP connection.
    }

    public static function reset(): void
    {
        self::$updateLastLoginCalls = [];
    }

    public function updateUserLastLogin(string $employeeId): array
    {
        self::$updateLastLoginCalls[] = $employeeId;
        return [
            'employee_id' => $employeeId,
            'last_login_utc' => gmdate('Y-m-d H:i:s'),
        ];
    }
}
