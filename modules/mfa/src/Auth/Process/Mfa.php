<?php

namespace SimpleSAML\Module\mfa\Auth\Process;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\Idp\IdBroker\Client\ServiceException;
use Sil\PhpEnv\Env;
use Sil\PhpEnv\EnvVarNotFoundException;
use Sil\Psr3Adapters\Psr3SamlLogger;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;
use SimpleSAML\Auth\ProcessingChain;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Auth\State;
use SimpleSAML\Module;
use SimpleSAML\Module\mfa\ApiClient;
use SimpleSAML\Module\mfa\LoggerFactory;
use SimpleSAML\Utils\HTTP;
use Throwable;

/**
 * Filter which prompts the user for MFA credentials.
 *
 * See README.md for sample (and explanation of) expected configuration.
 */
class Mfa extends ProcessingFilter
{
    const STAGE_SENT_TO_LOW_ON_BACKUP_CODES_NAG = 'mfa:sent_to_low_on_backup_codes_nag';
    const STAGE_SENT_TO_MFA_NEEDED_MESSAGE = 'mfa:sent_to_mfa_needed_message';
    const STAGE_SENT_TO_MFA_PROMPT = 'mfa:sent_to_mfa_prompt';
    const STAGE_SENT_TO_NEW_BACKUP_CODES_PAGE = 'mfa:sent_to_new_backup_codes_page';
    const STAGE_SENT_TO_OUT_OF_BACKUP_CODES_MESSAGE = 'mfa:sent_to_out_of_backup_codes_message';

    private string|null $employeeIdAttr = null;
    private string|null $idpDomainName = null;
    private string|null $mfaSetupUrl = null;

    private string|null $idBrokerAccessToken = null;
    private bool $idBrokerAssertValidIp;
    private string|null $idBrokerBaseUri = null;
    private string|null $idBrokerClientClass = null;
    private array $idBrokerTrustedIpRanges = [];

    protected LoggerInterface $logger;

    protected string $loggerClass;

    private string $recoveryContactsApi;
    private string $recoveryContactsApiKey;
    private string $recoveryContactsFallbackName;
    private string $recoveryContactsFallbackEmail;

    /**
     * Initialize this filter.
     *
     * @param array $config Configuration information about this filter.
     * @param mixed $reserved For future use.
     * @throws Exception
     */
    public function __construct(array $config, mixed $reserved)
    {
        parent::__construct($config, $reserved);
        $this->initComposerAutoloader();
        assert('is_array($config)');

        $this->loggerClass = $config['loggerClass'] ?? Psr3SamlLogger::class;
        $this->logger = LoggerFactory::get($this->loggerClass);

        $this->loadValuesFromConfig($config, [
            'mfaSetupUrl',
            'employeeIdAttr',
            'idBrokerAccessToken',
            'idBrokerBaseUri',
            'idpDomainName',
        ]);

        $tempTrustedIpRanges = $config['idBrokerTrustedIpRanges'] ?? '';
        if (!empty($tempTrustedIpRanges)) {
            $this->idBrokerTrustedIpRanges = explode(',', $tempTrustedIpRanges);
        }
        $this->idBrokerAssertValidIp = (bool)($config['idBrokerAssertValidIp'] ?? true);
        $this->idBrokerClientClass = $config['idBrokerClientClass'] ?? IdBrokerClient::class;

        $this->recoveryContactsApi = $config['recoveryContactsApi'] ?? '';
        $this->recoveryContactsApiKey = $config['recoveryContactsApiKey'] ?? '';
        $this->recoveryContactsFallbackName = $config['recoveryContactsFallbackName'] ?? '';
        $this->recoveryContactsFallbackEmail = $config['recoveryContactsFallbackEmail'] ?? '';
    }

    /**
     * @throws Exception
     */
    protected function loadValuesFromConfig(array $config, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $this->$attribute = $config[$attribute] ?? null;

            self::validateConfigValue(
                $attribute,
                $this->$attribute,
                $this->logger
            );
        }
    }

    /**
     * Validate the given config value
     *
     * @param string $attribute The name of the attribute.
     * @param mixed $value The value to check.
     * @param LoggerInterface $logger The logger.
     * @throws Exception
     */
    public static function validateConfigValue(string $attribute, mixed $value, LoggerInterface $logger): void
    {
        if (empty($value) || !is_string($value)) {
            $exception = new Exception(sprintf(
                'The value we have for %s (%s) is empty or is not a string',
                $attribute,
                var_export($value, true)
            ), 1507146042);

            $logger->critical($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * Abbreviate the provided name so that someone who knows them would still
     * recognize it, but someone who does not know them will hopefully not have
     * enough information to figure out exactly who it is.
     *
     * @param string $name Example: 'John Smith'
     * @return string Example: 'J. Smith' (though how it is abbreviated may
     *     change in the future)
     */
    public static function abbreviateName(string $name): string
    {
        return preg_replace(
            '/^(\w)(\w*) (\w+)/',
            '$1. $3',
            $name
        );
    }

    /**
     * Get the specified attribute from the given state data.
     *
     * NOTE: If the attribute's data is an array, the first value will be
     *       returned. Otherwise, the attribute's data will simply be returned
     *       as-is.
     *
     * @param string $attributeName The name of the attribute.
     * @param array $state The state data.
     * @return mixed The attribute value, or null if not found.
     */
    protected function getAttribute(string $attributeName, array $state): mixed
    {
        $attributeData = $state['Attributes'][$attributeName] ?? null;

        if (is_array($attributeData)) {
            return $attributeData[0] ?? null;
        }

        return $attributeData;
    }

    /**
     * Get all of the values for the specified attribute from the given state
     * data.
     *
     * NOTE: If the attribute's data is an array, it will be returned as-is.
     *       Otherwise, it will be returned as a single-entry array of the data.
     *
     * @param string $attributeName The name of the attribute.
     * @param array $state The state data.
     * @return array|null The attribute's value(s), or null if the attribute was
     *     not found.
     */
    protected function getAttributeAllValues(string $attributeName, array $state): ?array
    {
        $attributeData = $state['Attributes'][$attributeName] ?? null;

        return is_null($attributeData) ? null : (array)$attributeData;
    }

    /**
     * Get an ID Broker client.
     *
     * @param array $idBrokerConfig
     * @return IdBrokerClient|FakeIdBrokerClient
     */
    private static function getIdBrokerClient(array $idBrokerConfig): IdBrokerClient|FakeIdBrokerClient
    {
        $clientClass = $idBrokerConfig['clientClass'];
        $baseUri = $idBrokerConfig['baseUri'];
        $accessToken = $idBrokerConfig['accessToken'];
        $trustedIpRanges = $idBrokerConfig['trustedIpRanges'];
        $assertValidIp = $idBrokerConfig['assertValidIp'];

        return new $clientClass($baseUri, $accessToken, [
            'http_client_options' => [
                'timeout' => 10,
            ],
            IdBrokerClient::TRUSTED_IPS_CONFIG => $trustedIpRanges,
            IdBrokerClient::ASSERT_VALID_BROKER_IP_CONFIG => $assertValidIp,
        ]);
    }

    /**
     * Get the MFA type to use based on the available options.
     *
     * @param array[] $mfaOptions The available MFA options.
     * @param int $mfaId The ID of the desired MFA option.
     * @return array The MFA option to use.
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function getMfaOptionById(array $mfaOptions, int $mfaId): array
    {
        if (empty($mfaId)) {
            throw new Exception('No MFA ID was provided.');
        }

        foreach ($mfaOptions as $mfaOption) {
            if ((int)$mfaOption['id'] === $mfaId) {
                return $mfaOption;
            }
        }

        throw new Exception(
            'No MFA option has an ID of ' . var_export($mfaId, true)
        );
    }

    /**
     * Get the MFA type to use based on the available options.
     *
     * @param array[] $mfaOptions The available MFA options.
     * @return array The MFA option to use.
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function getMfaOptionToUse(array $mfaOptions): array
    {
        if (empty($mfaOptions)) {
            throw new Exception('No MFA options were provided.');
        }

        $recentMfa = self::getMostRecentUsedMfaOption($mfaOptions);
        $mfaTypePriority = ['recovery', 'manager'];

        if (isset($recentMfa['type'])) {
            $mfaTypePriority[] = $recentMfa['type'];
        }
        // Doubling up a type shouldn't be a problem.
        array_push($mfaTypePriority, 'webauthn', 'totp', 'backupcode');

        foreach ($mfaTypePriority as $mfaType) {
            foreach ($mfaOptions as $mfaOption) {
                if ($mfaOption['type'] === $mfaType) {
                    return $mfaOption;
                }
            }
        }

        return $mfaOptions[0];
    }

    /**
     * Get the MFA to use based on the one used most recently.
     *
     * @param array[] $mfaOptions The available MFA options.
     * @return ?array The MFA option to use.
     */
    private static function getMostRecentUsedMfaOption(array $mfaOptions): ?array
    {
        $recentMfa = null;
        $recentDate = '1991-01-01T00:00:00Z';

        foreach ($mfaOptions as $mfaOption) {
            if (isset($mfaOption['last_used_utc']) && $mfaOption['last_used_utc'] > $recentDate) {
                $recentMfa = $mfaOption;
                $recentDate = $mfaOption['last_used_utc'];
            }
        }
        return $recentMfa;
    }

    /**
     * Get the number of backup codes that the user had left PRIOR to this login.
     *
     * @param array $mfaOptions The list of MFA options.
     * @return int The number of backup codes that the user HAD (prior to this login).
     */
    public static function getNumBackupCodesUserHad(array $mfaOptions): int
    {
        $numBackupCodes = 0;
        foreach ($mfaOptions as $mfaOption) {
            $mfaType = $mfaOption['type'] ?? null;
            if ($mfaType === 'backupcode') {
                $numBackupCodes += intval($mfaOption['data']['count'] ?? 0);
            }
        }

        return $numBackupCodes;
    }

    /**
     * Get the list of recovery contacts for the current user, keyed on their
     * name, with their email as the value.
     *
     * @throws GuzzleException
     */
    public static function getRecoveryContactsByName(?array $state): array
    {
        $recoveryConfig = $state['recoveryConfig'] ?? [];
        $emailAddressValues = $state['Attributes']['mail'] ?? [''];
        $emailAddress = $emailAddressValues[0] ?? '';

        $recoveryContactsApiUrl = $recoveryConfig['api'] ?? '';
        $recoveryContactsApiKey = $recoveryConfig['apiKey'] ?? '';

        $recoveryContactsApiClient = new ApiClient($recoveryContactsApiKey);
        $recoveryContactsFromApi = $recoveryContactsApiClient->call(
            $recoveryContactsApiUrl,
            ['email' => $emailAddress]
        );

        $recoveryContactsByName = [];
        foreach ($recoveryContactsFromApi as $recoveryContact) {
            $name = $recoveryContact['name'];
            $emailAddress = $recoveryContact['email'];
            $recoveryContactsByName[$name] = $emailAddress;
        }

        return $recoveryContactsByName;
    }

    /**
     * Get the template identifier (string) to use for the specified MFA type.
     *
     * @param string $mfaType The desired MFA type, such as 'webauthn', 'totp', or 'backupcode'.
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getTemplateFor(string $mfaType): string
    {
        $mfaOptionTemplates = [
            'backupcode' => 'mfa:prompt-for-mfa-backupcode',
            'totp' => 'mfa:prompt-for-mfa-totp',
            'webauthn' => 'mfa:prompt-for-mfa-webauthn',
            'manager' => 'mfa:prompt-for-mfa-manager',
            'recovery' => 'mfa:prompt-for-mfa-recovery',
        ];
        $template = $mfaOptionTemplates[$mfaType] ?? null;

        if ($template === null) {
            throw new InvalidArgumentException(sprintf(
                'No %s MFA template is available.',
                var_export($mfaType, true)
            ), 1507219338);
        }
        return $template;
    }

    /**
     * Return the saml:RelayState if it begins with "http" or "https". Otherwise
     * return an empty string.
     *
     * @param array $state
     * @return string
     */
    protected static function getRelayStateUrl(array $state): string
    {
        if (array_key_exists('saml:RelayState', $state)) {
            $samlRelayState = $state['saml:RelayState'];

            if (str_starts_with($samlRelayState, "http://")) {
                return $samlRelayState;
            }

            if (str_starts_with($samlRelayState, "https://")) {
                return $samlRelayState;
            }
        }
        return '';
    }

    /**
     * Get new Printable Backup Codes for the user, then redirect the user to a
     * page showing the user their new codes.
     *
     * NOTE: This function never returns.
     *
     * @param array $state The state data.
     * @param LoggerInterface $logger A PSR-3 compatible logger.
     */
    public static function giveUserNewBackupCodes(array &$state, LoggerInterface $logger): void
    {
        try {
            $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);
            $newMfaRecord = $idBrokerClient->mfaCreate(
                $state['employeeId'],
                'backupcode'
            );
            $newBackupCodes = $newMfaRecord['data'];

            $logger->warning(json_encode([
                'event' => 'New backup codes result: succeeded',
                'employeeId' => $state['employeeId'],
            ]));
        } catch (Throwable $t) {
            $logger->error(json_encode([
                'event' => 'New backup codes result: failed',
                'employeeId' => $state['employeeId'],
                'error' => $t->getCode() . ': ' . $t->getMessage(),
            ]));
        }

        self::updateStateWithNewMfaData($state, $logger);

        $state['newBackupCodes'] = $newBackupCodes ?? null;
        $stateId = State::saveState($state, self::STAGE_SENT_TO_NEW_BACKUP_CODES_PAGE);
        $url = Module::getModuleURL('mfa/new-backup-codes.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['StateId' => $stateId]);
    }

    protected static function hasMfaOptions(array $mfa): bool
    {
        return (count($mfa['options']) > 0);
    }

    /**
     * See if the user has any MFA options other than the specified type.
     *
     * @param string $excludedMfaType
     * @param array $state
     * @return bool
     */
    public static function hasMfaOptionsOtherThan(string $excludedMfaType, array $state): bool
    {
        $mfaOptions = $state['mfaOptions'] ?? [];
        foreach ($mfaOptions as $mfaOption) {
            if (strval($mfaOption['type']) !== $excludedMfaType) {
                return true;
            }
        }
        return false;
    }

    protected function initComposerAutoloader(): void
    {
        $path = __DIR__ . '/../../../vendor/autoload.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }

    protected static function isHeadedToMfaSetupUrl($state, $mfaSetupUrl): bool
    {
        if (array_key_exists('saml:RelayState', $state)) {
            $currentDestination = self::getRelayStateUrl($state);
            if (!empty($currentDestination)) {
                return (str_starts_with($currentDestination, $mfaSetupUrl));
            }
        }
        return false;
    }

    /**
     * Validate the given MFA submission. If successful, this function
     * will NOT return. If the submission does not pass validation, an error
     * message will be returned.
     *
     * @param int $mfaId The ID of the MFA option used.
     * @param string $employeeId The Employee ID that this MFA option belongs to.
     * @param string|array $mfaSubmission The value of the MFA submission.
     * @param array $state The array of state information.
     * @param bool $rememberMe Whether or not to set remember me cookies
     * @param LoggerInterface $logger A PSR-3 compatible logger.
     * @param string $mfaType The type of the MFA ('webauthn', 'totp', 'backupcode').
     * @param string $rpOrigin The Relying Party Origin (for WebAuthn)
     * @return string If validation fails, an error message to show to the
     *     end user will be returned.
     * @throws EnvVarNotFoundException
     * @throws Exception
     */
    public static function validateMfaSubmission(
        int             $mfaId,
        string          $employeeId,
        string|array    $mfaSubmission,
        array           $state,
        bool            $rememberMe,
        LoggerInterface $logger,
        string          $mfaType,
        string          $rpOrigin
    ): string {
        if (empty($mfaId)) {
            return 'No MFA ID was provided.';
        } elseif (empty($employeeId)) {
            return 'No Employee ID was provided.';
        } elseif (empty($mfaSubmission)) {
            return 'No MFA submission was provided.';
        } elseif (empty($rpOrigin)) {
            return 'No RP Origin was provided.';
        }

        try {
            $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);
            $mfaDataFromBroker = $idBrokerClient->mfaVerify(
                $mfaId,
                $employeeId,
                $mfaSubmission,
                $rpOrigin
            );

            if ($mfaDataFromBroker === true || count($mfaDataFromBroker) > 0) {
                $idBrokerClient->updateUserLastLogin($employeeId);
            }
        } catch (Throwable $t) {
            $message = 'Something went wrong while we were trying to do the '
                . '2-step verification.';
            if ($t instanceof ServiceException) {
                if ($t->httpStatusCode === 400) {
                    if ($mfaType === 'backupcode') {
                        return 'Incorrect 2-step verification code. Printable backup '
                            . 'codes can only be used once, please try a different code.';
                    }
                    return 'Incorrect 2-step verification code.';
                } elseif ($t->httpStatusCode === 429) {
                    $logger->error(json_encode([
                        'event' => 'MFA is rate-limited',
                        'employeeId' => $employeeId,
                        'mfaId' => $mfaId,
                        'mfaType' => $mfaType,
                    ]));
                    return 'There have been too many wrong answers recently. '
                        . 'Please wait a minute, then try again.';
                } else {
                    $message .= ' (code ' . $t->httpStatusCode . ')';
                    return $message;
                }
            }

            $logger->critical($t->getCode() . ': ' . $t->getMessage());
            return $message;
        }

        self::updateStateWithNewMfaData($state, $logger);

        // Set remember me cookies if requested
        if ($rememberMe) {
            self::setRememberMeCookies($state['employeeId'], $state['mfaOptions']);
        } else {
            self::clearRememberMeCookies();
        }

        $logger->warning(json_encode([
            'event' => 'MFA validation result: success',
            'employeeId' => $employeeId,
            'mfaType' => $mfaType,
        ]));

        // Handle situations where the user is running low on backup codes.
        if ($mfaType === 'backupcode') {
            $numBackupCodesUserHad = self::getNumBackupCodesUserHad(
                $state['mfaOptions'] ?? []
            );
            $numBackupCodesRemaining = $numBackupCodesUserHad - 1;

            if ($numBackupCodesRemaining <= 0) {
                self::redirectToOutOfBackupCodesMessage($state, $employeeId);
                throw new Exception('Failed to send user to out-of-backup-codes page.');
            } elseif ($numBackupCodesRemaining < 4) {
                self::redirectToLowOnBackupCodesNag(
                    $state,
                    $employeeId,
                    $numBackupCodesRemaining
                );
                throw new Exception('Failed to send user to low-on-backup-codes page.');
            }
        }

        /*
         * If the user had to use a manager code, show the profile review page.
         */
        if ($mfaType === 'manager' && isset($state['Attributes']['profile_review'])) {
            $state['Attributes']['profile_review'] = 'yes';
        }

        unset($state['Attributes']['manager_email']);

        // The following function call will never return.
        ProcessingChain::resumeProcessing($state);
        throw new Exception('Failed to resume processing auth proc chain.');
    }

    /**
     * Redirect the user to set up MFA.
     *
     * @param array $state
     */
    public static function redirectToMfaSetup(array $state): void
    {
        $mfaSetupUrl = $state['mfaSetupUrl'];

        $httpUtils = new HTTP();

        // Tell the MFA-setup URL where the user is ultimately trying to go (if known).
        $currentDestination = self::getRelayStateUrl($state);
        if (!empty($currentDestination)) {
            $mfaSetupUrl = $httpUtils->addURLParameters(
                $mfaSetupUrl,
                ['returnTo' => $currentDestination]
            );
        }

        $logger = LoggerFactory::getAccordingToState($state);
        $logger->warning(sprintf(
            'mfa: Sending Employee ID %s to set up MFA at %s',
            var_export($state['employeeId'] ?? null, true),
            var_export($mfaSetupUrl, true)
        ));

        $httpUtils->redirectTrustedURL($mfaSetupUrl);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function process(array &$state): void
    {
        // Get the necessary info from the state data.
        $employeeId = $this->getAttribute($this->employeeIdAttr, $state);
        $mfa = $this->getAttributeAllValues('mfa', $state);
        $isHeadedToMfaSetupUrl = self::isHeadedToMfaSetupUrl(
            $state,
            $this->mfaSetupUrl
        );

        $this->logger->debug(json_encode([
            'module' => 'mfa',
            'event' => 'process',
            'mfa' => $mfa,
            'isHeadedToMfaSetupUrl' => $isHeadedToMfaSetupUrl,
            'employeeId' => $employeeId,
        ]));


        // Record to the state what logger class to use.
        $state['loggerClass'] = $this->loggerClass;

        // Add to the state any config data we may need for the low-on/out-of
        // backup codes pages.
        $state['mfaSetupUrl'] = $this->mfaSetupUrl;

        if (self::shouldPromptForMfa($mfa)) {
            if (self::hasMfaOptions($mfa)) {
                $this->redirectToMfaPrompt($state, $employeeId, $mfa['options']);
                return;
            }

            if (!$isHeadedToMfaSetupUrl) {
                $this->redirectToMfaNeededMessage($state, $employeeId, $this->mfaSetupUrl);
                return;
            }
        }

        unset($state['Attributes']['manager_email']);
    }

    /**
     * Redirect the user to a page telling them they must set up MFA.
     *
     * @param array $state The state data.
     * @param string $employeeId The Employee ID of the user account.
     * @param string $mfaSetupUrl URL to MFA setup process
     */
    protected function redirectToMfaNeededMessage(array &$state, string $employeeId, string $mfaSetupUrl): void
    {
        assert('is_array($state)');

        $this->logger->info(sprintf(
            'mfa: Redirecting Employee ID %s to must-set-up-MFA message.',
            var_export($employeeId, true)
        ));

        /* Save state and redirect. */
        $state['employeeId'] = $employeeId;
        $state['mfaSetupUrl'] = $mfaSetupUrl;

        $stateId = State::saveState($state, self::STAGE_SENT_TO_MFA_NEEDED_MESSAGE);
        $url = Module::getModuleURL('mfa/must-set-up-mfa.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['StateId' => $stateId]);
    }

    /**
     * Redirect the user to the appropriate MFA-prompt page.
     *
     * @param array $state The state data.
     * @param string $employeeId The Employee ID of the user account.
     * @param array $mfaOptions Array of MFA options
     * @throws Exception
     */
    protected function redirectToMfaPrompt(array &$state, string $employeeId, array $mfaOptions): void
    {
        assert('is_array($state)');

        /** @todo Check for valid remember-me cookies here rather doing a redirect first. */

        $state['mfaOptions'] = $mfaOptions;
        $state['maskedManagerEmail'] = self::getMaskedManagerEmail($state);
        $state['unmaskedManagerEmail'] = self::getManagerEmail($state);
        $state['idBrokerConfig'] = [
            'accessToken' => $this->idBrokerAccessToken,
            'assertValidIp' => $this->idBrokerAssertValidIp,
            'baseUri' => $this->idBrokerBaseUri,
            'clientClass' => $this->idBrokerClientClass,
            'trustedIpRanges' => $this->idBrokerTrustedIpRanges,
        ];
        $state['recoveryConfig'] = [
            'api' => $this->recoveryContactsApi,
            'apiKey' => $this->recoveryContactsApiKey,
            'fallbackEmail' => $this->recoveryContactsFallbackEmail,
            'fallbackName' => $this->recoveryContactsFallbackName,
        ];

        $this->logger->info(sprintf(
            'mfa: Redirecting Employee ID %s to MFA prompt.',
            var_export($employeeId, true)
        ));

        /* Save state and redirect. */
        $state['employeeId'] = $employeeId;
        $state['rpOrigin'] = 'https://' . $this->idpDomainName;

        $id = State::saveState($state, self::STAGE_SENT_TO_MFA_PROMPT);
        $url = Module::getModuleURL('mfa/prompt-for-mfa.php');
        $mfaOption = self::getMfaOptionToUse($mfaOptions);

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, [
            'mfaId' => $mfaOption['id'],
            'StateId' => $id,
        ]);
    }

    /**
     * Validate that remember me cookie values are legit and valid
     * @param string $cookieHash
     * @param string $expireDate
     * @param array $mfaOptions
     * @param array $state
     * @return bool
     * @throws EnvVarNotFoundException|ServiceException
     */
    public static function isRememberMeCookieValid(
        string $cookieHash,
        string $expireDate,
        array  $mfaOptions,
        array  $state
    ): bool {
        $rememberSecret = Env::requireEnv('REMEMBER_ME_SECRET');
        if (!empty($cookieHash) && !empty($expireDate) && is_numeric($expireDate)) {
            // Check if value of expireDate is in future
            if ((int)$expireDate > time()) {
                $expectedString = self::generateRememberMeCookieString($rememberSecret, $state['employeeId'], $expireDate, $mfaOptions);
                $isValid = password_verify($expectedString, $cookieHash);

                if ($isValid) {
                    $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);
                    $idBrokerClient->updateUserLastLogin($state['employeeId']);
                }

                return $isValid;
            }
        }

        return false;
    }

    /**
     * Generate and return a string to be hashed for remember me cookie
     * @param string $rememberSecret
     * @param string $employeeId
     * @param int $expireDate
     * @param array $mfaOptions
     * @return string
     */
    public static function generateRememberMeCookieString(
        string $rememberSecret,
        string $employeeId,
        int    $expireDate,
        array  $mfaOptions
    ): string {
        $allMfaIds = '';
        foreach ($mfaOptions as $opt) {
            if ($opt['type'] !== 'manager') {
                $allMfaIds .= $opt['id'];
            }
        }

        return $rememberSecret . $employeeId . $expireDate . $allMfaIds;
    }

    /**
     * Redirect the user to a page telling them they are running low on backup
     * codes and encouraging them to create more now.
     *
     * NOTE: This function never returns.
     *
     * @param array $state The state data.
     * @param string $employeeId The Employee ID of the user account.
     * @param int $numBackupCodesRemaining The number of backup codes that the
     *     user has left (now that they have used up one for this login).
     */
    protected static function redirectToLowOnBackupCodesNag(
        array  &$state,
        string $employeeId,
        int    $numBackupCodesRemaining
    ): void {
        $state['employeeId'] = $employeeId;
        $state['numBackupCodesRemaining'] = (string)$numBackupCodesRemaining;

        $stateId = State::saveState($state, self::STAGE_SENT_TO_LOW_ON_BACKUP_CODES_NAG);
        $url = Module::getModuleURL('mfa/low-on-backup-codes.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['StateId' => $stateId]);
    }

    /**
     * Redirect the user to a page telling them they just used up their last
     * backup code.
     *
     * NOTE: This function never returns.
     *
     * @param array $state The state data.
     * @param string $employeeId The Employee ID of the user account.
     */
    protected static function redirectToOutOfBackupCodesMessage(array &$state, string $employeeId): void
    {
        $state['employeeId'] = $employeeId;

        $stateId = State::saveState($state, self::STAGE_SENT_TO_OUT_OF_BACKUP_CODES_MESSAGE);
        $url = Module::getModuleURL('mfa/out-of-backup-codes.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['StateId' => $stateId]);
    }

    /**
     * Set cookies c1 and c2
     * @param string $employeeId
     * @param array $mfaOptions
     * @param string $rememberDuration
     * @throws EnvVarNotFoundException
     */
    public static function setRememberMeCookies(
        string $employeeId,
        array  $mfaOptions,
        string $rememberDuration = '+30 days'
    ): void {
        $rememberSecret = Env::requireEnv('REMEMBER_ME_SECRET');
        $secureCookie = Env::get('SECURE_COOKIE', true);
        $expireDate = strtotime($rememberDuration);
        $cookieString = self::generateRememberMeCookieString($rememberSecret, $employeeId, $expireDate, $mfaOptions);
        $cookieHash = password_hash($cookieString, PASSWORD_DEFAULT);
        setcookie('c1', base64_encode($cookieHash), $expireDate, '/', null, $secureCookie, true);
        setcookie('c2', $expireDate, $expireDate, '/', null, $secureCookie, true);
    }

    /**
     * Clear remember_me cookies (c1 and c2)
     */
    public static function clearRememberMeCookies(): void
    {
        $secureCookie = Env::get('SECURE_COOKIE', true);
        setcookie('c1', '', time() - 3600, '/', null, $secureCookie, true);
        setcookie('c2', '', time() - 3600, '/', null, $secureCookie, true);
    }

    protected static function shouldPromptForMfa(array $mfa): bool
    {
        return (strtolower($mfa['prompt']) !== 'no');
    }

    /**
     * Send a rescue code to the manager, then redirect the user to a page where they
     * can enter the code.
     *
     * If successful, this function triggers a redirect and does not return
     *
     * @param array $state The state data.
     * @param LoggerInterface $logger A PSR-3 compatible logger.
     * @throws Exception
     */
    public static function sendManagerCode(array &$state, LoggerInterface $logger): void
    {
        try {
            $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);
            $mfaOption = $idBrokerClient->mfaCreate($state['employeeId'], 'manager');
            $mfaOption['type'] = 'manager';

            $logger->warning(json_encode([
                'event' => 'Manager rescue code sent',
                'employeeId' => $state['employeeId'],
            ]));
        } catch (Throwable $t) {
            $logger->error(json_encode([
                'event' => 'Manager rescue code: failed',
                'employeeId' => $state['employeeId'],
                'error' => $t->getCode() . ': ' . $t->getMessage(),
            ]));
            throw new Exception('There was an unexpected error. Please try again ' .
                'or contact tech support for assistance');
        }

        $mfaOptions = $state['mfaOptions'];

        /*
         * Add this option into the list, giving it a key so `mfaOptions` doesn't get multiple entries
         * if the user tries multiple times.
         */
        $mfaOptions['manager'] = $mfaOption;
        $state['mfaOptions'] = $mfaOptions;
        $state['maskedManagerEmail'] = self::getMaskedManagerEmail($state);
        $stateId = State::saveState($state, self::STAGE_SENT_TO_MFA_PROMPT);

        $url = Module::getModuleURL('mfa/prompt-for-mfa.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['mfaId' => $mfaOption['id'], 'StateId' => $stateId]);
    }

    /**
     * Send a rescue code to the specified recovery contact, then redirect the
     * user to a page where they can enter the code.
     *
     * If successful, this function triggers a redirect and does not return
     *
     * @param array $state The state data.
     * @param string $recoveryContactEmail The email address for the selected
     *     MFA recovery contact.
     * @param LoggerInterface $logger A PSR-3 compatible logger.
     * @throws Exception
     */
    public static function sendRecoveryCode(
        array &$state,
        string $recoveryContactEmail,
        LoggerInterface $logger
    ): void {
        try {
            $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);
            $mfaOption = $idBrokerClient->mfaCreate(
                $state['employeeId'],
                'recovery',
                'Recovery contact',
                '',
                $recoveryContactEmail
            );
            $mfaOption['type'] = 'recovery';

            $logger->warning(json_encode([
                'event' => 'Recovery code sent',
                'employeeId' => $state['employeeId'],
                'recipient' => $recoveryContactEmail,
            ]));
        } catch (Throwable $t) {
            $logger->error(json_encode([
                'event' => 'Recovery code: failed',
                'employeeId' => $state['employeeId'],
                'recipient' => $recoveryContactEmail,
                'error' => $t->getCode() . ': ' . $t->getMessage(),
            ]));
            throw new Exception(
                'There was an unexpected error. Please try again or contact ' .
                'tech support for assistance'
            );
        }

        $mfaOptions = $state['mfaOptions'];

        /*
         * Add this option into the list, giving it a key so `mfaOptions` doesn't get multiple entries
         * if the user tries multiple times.
         */
        $mfaOptions['recovery'] = $mfaOption;
        $state['mfaOptions'] = $mfaOptions;
        $stateId = State::saveState($state, self::STAGE_SENT_TO_MFA_PROMPT);

        $url = Module::getModuleURL('mfa/prompt-for-mfa.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, ['mfaId' => $mfaOption['id'], 'StateId' => $stateId]);
    }

    /**
     * Get masked copy of manager_email, or null if it isn't available.
     *
     * @param array $state
     * @return string|null
     */
    public static function getMaskedManagerEmail(array $state): ?string
    {
        $managerEmail = self::getManagerEmail($state);
        if (empty($managerEmail)) {
            return null;
        }
        return self::maskEmail($managerEmail);
    }

    /**
     * Get manager_email, or null if it isn't available.
     *
     * @param array $state
     * @return string|null
     */
    public static function getManagerEmail(array $state): ?string
    {
        $managerEmail = $state['Attributes']['manager_email'] ?? [''];
        if (empty($managerEmail[0])) {
            return null;
        }
        return $managerEmail[0];
    }

    /**
     * @param string $email an email address
     * @return string with most letters changed to asterisks
     */
    public static function maskEmail(string $email): string
    {
        list($part1, $domain) = explode('@', $email);
        $newEmail = '';
        $useRealChar = true;

        /*
         * Replace all characters with '*', except
         * the first one, the last one, underscores and each
         * character that follows and underscore.
         */
        foreach (str_split($part1) as $nextChar) {
            if ($useRealChar) {
                $newEmail .= $nextChar;
                $useRealChar = false;
            } else if ($nextChar === '_') {
                $newEmail .= $nextChar;
                $useRealChar = true;
            } else {
                $newEmail .= '*';
            }
        }

        // replace the last * with the last real character
        $newEmail = substr($newEmail, 0, -1);
        $newEmail .= substr($part1, -1);
        $newEmail .= '@';

        /*
         * Add an '*' for each of the characters of the domain, except
         * for the first character of each part and the .
         */
        $domainParts = explode('.', $domain);
        $maskedDomain = '';

        foreach ($domainParts as $part) {
            $firstCharacter = substr($part, 0, 1);
            $maskedPart = $firstCharacter . str_repeat('*', max(strlen($part) - 1, 0));
            $maskedDomain .= $maskedPart . '.';
        }
        $maskedDomain = rtrim($maskedDomain, '.');
        $newEmail .= $maskedDomain;
        return $newEmail;
    }

    /**
     * @param array $state
     * @param LoggerInterface $logger
     */
    protected static function updateStateWithNewMfaData(array &$state, LoggerInterface $logger): void
    {
        $idBrokerClient = self::getIdBrokerClient($state['idBrokerConfig']);

        $log = [
            'event' => 'Update state with new mfa data',
        ];

        try {
            $newMfaOptions = $idBrokerClient->mfaList($state['employeeId']);
        } catch (Exception) {
            $log['status'] = 'failed: id-broker exception';
            $logger->error(json_encode($log));
            return;
        }

        if (empty($newMfaOptions)) {
            $log['status'] = 'failed: no data provided';
            $logger->warning(json_encode($log));
            return;
        }

        $state['Attributes']['mfa']['options'] = $newMfaOptions;

        $log['data'] = $newMfaOptions;
        $log['status'] = 'updated';
        $logger->warning(json_encode($log));
    }
}
