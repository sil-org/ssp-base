<?php

namespace SimpleSAML\Module\silauth\Auth\Source\system;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\Configuration;
use SimpleSAML\Module\silauth\Auth\Source\models\FailedLoginIpAddress;
use Throwable;

class System
{
    protected LoggerInterface|NullLogger $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger (Optional:) A PSR-3 compatible logger.
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    protected function isDatabaseOkay(): bool
    {
        try {
            FailedLoginIpAddress::getMostRecentFailedLoginFor('');
            return true;
        } catch (Throwable $t) {
            $this->logError('isDatabaseOkay database check returned error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * @throws Exception
     */
    protected function isRequiredConfigPresent(): bool
    {
        $globalConfig = Configuration::getInstance();

        /*
         * If 'trusted.url.domains' is not NULL, the 'baseurlpath' check can be skipped. This is explicitly checked
         * against null because an empty string tells SimpleSAMLphp to not trust URLs from any domain but its own.
         */
        $trustedUrlDomains = $globalConfig->getOptionalString('trusted.url.domains', '');
        if ($trustedUrlDomains !== null) {
            return true;
        }

        /*
         * NOTE: We require that SSP's baseurlpath configuration is set (and
         *       matches the corresponding RegExp) in order to prevent a
         *       security hole in \SimpleSAML\Utils\HTTP::getBaseURL() where the
         *       HTTP_HOST value (provided by the user's request) is used to
         *       build a trusted URL (see SimpleSaml\Module::authenticate()).
         */
        $baseURL = $globalConfig->getOptionalString('baseurlpath', '');
        $avoidsSecurityHole = (preg_match('#^https?://.*/$#D', $baseURL) === 1);
        if (!$avoidsSecurityHole) {
            $this->logError('isRequiredConfigPresent failed: baseurlpath (' . $baseURL . ') does not meet requirements');
        }
        return $avoidsSecurityHole;
    }

    /**
     * Check the status of the system, and throw an exception (that is safe to
     * show to the public) if any serious error conditions are found. Log any
     * problems, even if recoverable.
     *
     * @throws Exception
     */
    public function reportStatus(): void
    {
        if (!$this->isRequiredConfigPresent()) {
            $this->reportError('Config problem', 1485984755);
        }

        if (!$this->isDatabaseOkay()) {
            $this->reportError('Database problem', 1485284407);
        }

        echo 'OK';
    }

    /**
     * Add an entry to our log about this error.
     *
     * @param string $message The error message.
     */
    protected function logError(string $message): void
    {
        $this->logger->error($message);
    }

    /**
     * Log this error and throw an exception (with an error message) for the
     * calling code to handle.
     *
     * @param string $message The error message.
     * @param int $code An error code.
     * @throws Exception
     */
    protected function reportError(string $message, int $code): void
    {
        $this->logError($message);
        throw new Exception($message, $code);
    }
}
