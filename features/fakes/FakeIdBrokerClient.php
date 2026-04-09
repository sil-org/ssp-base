<?php

namespace Sil\SspBase\Features\fakes;

use Exception;
use InvalidArgumentException;
use Sil\Idp\IdBroker\Client\ServiceException;

/**
 * FAKE IdP ID Broker API client, used for testing.
 */
class FakeIdBrokerClient
{
    const CORRECT_VALUE = '111111';
    const INCORRECT_VALUE = '999999';

    const RATE_LIMITED_MFA_ID = 987;

    /**
     * Constructor.
     *
     * @param string $baseUri - The base of the API's URL.
     *     Example: 'https://api.example.com/'.
     * @param string $accessToken - Your authorization access (bearer) token.
     * @param array $config - Any other configuration settings.
     */
    public function __construct(
        string $baseUri,
        string $accessToken,
        array  $config = []
    ) {
        // No-op
    }

    /**
     * Verify an MFA value
     * @throws ServiceException
     */
    public function mfaVerify(int $id, string $employeeId, string|array $value): array
    {
        if ($id === self::RATE_LIMITED_MFA_ID) {
            throw new ServiceException('Too many recent failures for this MFA', 0, 429);
        }

        if ($value !== self::CORRECT_VALUE) {
            throw new ServiceException('Incorrect code', 0, 400);
        }

        return [
            'id' => $id,
            'type' => 'backupcode',
            'label' => 'Printable Codes',
            'created_utc' => '2019-01-02T03:04:05Z',
            'data' => [
                'count' => 4,
            ],
        ];
    }

    /**
     * Update the User last_login_utc by calling the internal method
     * @param string $employeeId
     * @return array An array of information about the updated user only including last_login_utc and employee_id
     * @throws Exception
     */
    public function updateUserLastLogin(string $employeeId): array
    {
        if (empty($employeeId)) {
            throw new InvalidArgumentException('employeeId is required');
        }

        $nowUtc = gmdate('Y-m-d H:i:s');

        return [
            'employee_id' => $employeeId,
            'last_login_utc' => $nowUtc,
        ];
    }

    /**
     * Create a new MFA configuration
     * @param string $employee_id
     * @param string $type
     * @param string|null $label
     * @param string $rpOrigin
     * @param string $recovery_email
     * @return array|null
     * @throws Exception
     */
    public function mfaCreate(
        string $employee_id,
        string $type,
        string $label = null,
        string $rpOrigin = '',
        string $recovery_email = ''
    ): ?array {
        if (empty($employee_id)) {
            throw new InvalidArgumentException('employee_id is required');
        }

        if ($type === 'backupcode') {
            return [
                "id" => 1234,
                "data" => [
                    "00000000",
                    "11111111",
                    "22222222",
                    "33333333",
                    "44444444",
                    "55555555",
                    "66666666",
                    "77777777",
                    "88888888",
                    "99999999"
                ],
            ];
        }

        if ($type === 'manager') {
            return [
                "id" => 5678,
                "data" => [],
            ];
        }

        if ($type === 'recovery') {
            if (empty($recovery_email)) {
                throw new InvalidArgumentException(
                    'A recovery_email is required for MFA recovery codes'
                );
            }
            return [
                "id" => 7890,
                "data" => [],
            ];
        }

        throw new InvalidArgumentException(sprintf(
            'This Fake ID Broker class does not support creating %s MFA records.',
            $type
        ));
    }

    /**
     * Get a list of MFA configurations for given user
     * @param string $employee_id
     * @return array
     */
    public function mfaList(string $employee_id): array
    {
        return [
            [
                'id' => 1,
                'type' => 'backupcode',
                'label' => 'Printable Codes',
                'created_utc' => '2019-04-02T16:02:14Z',
                'last_used_utc' => '2019-04-01T00:00:00Z',
                'data' => [
                    'count' => 10
                ],
            ],
            [
                'id' => 2,
                'type' => 'totp',
                'label' => 'Authenticator App',
                'created_utc' => '2019-04-02T16:02:14Z',
                'last_used_utc' => '2019-04-01T00:00:00Z',
                'data' => [
                ],
            ],
        ];
    }
}
