<?php

namespace SimpleSAML\Module\loginfinalizer\Auth\Process;

use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Logger;
use Throwable;

/**
 * Marks the current user's last login time in the ID Broker.
 *
 * Intended to run as (one of) the last AuthProc filter(s) in the chain, so it
 * only fires once a login has fully succeeded, regardless of which earlier
 * filter(s) the request branched through to get there.
 *
 * It is safe to wire this into every IdP unconditionally: it no-ops silently
 * if it isn't configured, or if the current user doesn't have the configured
 * employee-id attribute (e.g. a Hub authenticating a federated user has no
 * ID Broker employee record at all).
 */
class MarkLastLogin extends ProcessingFilter
{
    private ?string $employeeIdAttr;

    private ?string $idBrokerAccessToken;

    private bool $idBrokerAssertValidIp;

    private ?string $idBrokerBaseUri;

    private string $idBrokerClientClass;

    private array $idBrokerTrustedIpRanges;

    public function __construct(array $config, mixed $reserved)
    {
        parent::__construct($config, $reserved);

        $this->employeeIdAttr = $config['employeeIdAttr'] ?? null;
        $this->idBrokerAccessToken = $config['idBrokerAccessToken'] ?? null;
        $this->idBrokerBaseUri = $config['idBrokerBaseUri'] ?? null;
        $this->idBrokerClientClass = $config['idBrokerClientClass'] ?? IdBrokerClient::class;
        $this->idBrokerAssertValidIp = (bool)($config['idBrokerAssertValidIp'] ?? true);

        $trustedIpRanges = $config['idBrokerTrustedIpRanges'] ?? '';
        $this->idBrokerTrustedIpRanges = empty($trustedIpRanges) ? [] : explode(',', $trustedIpRanges);
    }

    public function process(array &$state): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $employeeId = $state['Attributes'][$this->employeeIdAttr][0] ?? null;
        if (empty($employeeId)) {
            return;
        }

        try {
            $this->getIdBrokerClient()->updateUserLastLogin($employeeId);
        } catch (Throwable $t) {
            Logger::error(sprintf(
                'loginfinalizer: Failed to update last login for Employee ID %s: %s',
                var_export($employeeId, true),
                $t->getMessage()
            ));
        }
    }

    private function isConfigured(): bool
    {
        return !empty($this->employeeIdAttr)
            && !empty($this->idBrokerBaseUri)
            && !empty($this->idBrokerAccessToken);
    }

    private function getIdBrokerClient(): IdBrokerClient|FakeIdBrokerClient
    {
        $clientClass = $this->idBrokerClientClass;

        return new $clientClass($this->idBrokerBaseUri, $this->idBrokerAccessToken, [
            'http_client_options' => [
                'timeout' => 4,
            ],
            IdBrokerClient::TRUSTED_IPS_CONFIG => $this->idBrokerTrustedIpRanges,
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $this->idBrokerAssertValidIp,
        ]);
    }
}
