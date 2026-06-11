<?php

namespace SimpleSAML\Module\silauth\Auth\Source\auth;

use Exception;
use Psr\Log\LoggerInterface;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;
use SimpleSAML\Module\silauth\Auth\Source\saml\User as SamlUser;

class IdBroker
{
    protected IdBrokerClient|FakeIdBrokerClient $client;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    protected string $idpDomainName;

    /**
     *
     * @param string $baseUri The base of the API's URL.
     *     Example: 'https://api.example.com/'.
     * @param string $accessToken Your authorization access (bearer) token.
     * @param LoggerInterface $logger A PSR-3 compliant logger.
     * @param string $idpDomainName Unique identifier for this IdP-in-a-Box
     *     instance. This is used for assembling the eduPersonPrincipalName for
     *     users (e.g. "username@idp.domain.name").
     *     EXAMPLE: idp.domain.name
     * @param array $trustedIpRanges List of valid IP address ranges (CIDR) for
     *     the ID Broker API.
     * @param bool $assertValidIp (Optional:) Whether or not to assert that the
     *     IP address for the ID Broker API is trusted.
     * @throws Exception
     */
    public function __construct(
        string          $baseUri,
        string          $accessToken,
        LoggerInterface $logger,
        string          $idpDomainName,
        array           $trustedIpRanges,
        bool            $assertValidIp = true
    ) {
        $this->logger = $logger;
        $this->idpDomainName = $idpDomainName;
        $this->client = new IdBrokerClient($baseUri, $accessToken, [
            'http_client_options' => [
                'timeout' => 10,
            ],
            IdBrokerClient::TRUSTED_IPS_CONFIG => $trustedIpRanges,
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $assertValidIp,
        ]);
    }

    /**
     * Attempt to authenticate with the given username and password, returning
     * the attributes for that user if the credentials were acceptable (or null
     * if they were not acceptable, since there is no authenticated user in that
     * situation). If an unexpected response is received, an exception will be
     * thrown.
     *
     * NOTE: The attributes names used (if any) in the response will be SAML
     *       field names, not ID Broker field names.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return array|null The user's attributes (if successful), otherwise null.
     * @throws Exception
     */
    public function getAuthenticatedUser(string $username, string $password): ?array
    {
        $rpOrigin = 'https://' . $this->idpDomainName;
        $userInfo = $this->client->authenticate($username, $password, $rpOrigin);

        if ($userInfo === null) {
            return null;
        }

        $pwExpDate = $userInfo['password']['expires_on'] ?? null;
        if ($pwExpDate !== null) {
            $schacExpiryDate = gmdate('YmdHis\Z', strtotime($pwExpDate));
        }

        return SamlUser::convertToSamlFieldNames(
            $userInfo['employee_id'],
            $userInfo['first_name'],
            $userInfo['last_name'],
            $userInfo['username'],
            $userInfo['email'],
            $userInfo['uuid'],
            $this->idpDomainName,
            $schacExpiryDate ?? null,
            $userInfo['mfa'],
            $userInfo['method'],
            $userInfo['manager_email'] ?? null,
            $userInfo['profile_review'] ?? 'no',
            $userInfo['member'] ?? [],
            $userInfo['display_name'],
        );
    }
}
