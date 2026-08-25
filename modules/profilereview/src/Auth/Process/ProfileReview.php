<?php

namespace SimpleSAML\Module\profilereview\Auth\Process;

use Exception;
use Psr\Log\LoggerInterface;
use Sil\Psr3Adapters\Psr3SamlLogger;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Auth\State;
use SimpleSAML\Module;
use SimpleSAML\Module\profilereview\LoggerFactory;
use SimpleSAML\Session;
use SimpleSAML\Utils\HTTP;

/**
 * Filter which prompts the user for profile review.
 *
 * See README.md for sample (and explanation of) expected configuration.
 */
class ProfileReview extends ProcessingFilter
{
    const SESSION_TYPE = 'profilereview';
    const STAGE_SENT_TO_NAG = 'profilereview:sent_to_nag';

    const REVIEW_PAGE = 'review.php';
    const MFA_ADD_PAGE = 'nag-for-mfa';
    const METHOD_ADD_PAGE = 'nag-for-method';

    private string|null $employeeIdAttr = null;
    private string|null $mfaLearnMoreUrl = null;
    private string|null $profileUrl = null;

    protected LoggerInterface $logger;

    protected string $loggerClass;

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
            'profileUrl',
            'employeeIdAttr',
        ]);

        $this->mfaLearnMoreUrl = $config['mfaLearnMoreUrl'] ?? null;
        $this->profileUrl = $config['profileUrl'] ?? null;
    }

    /**
     * @param array $config
     * @param array $attributes
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
     * Return the saml:RelayState if it begins with "http" or "https". Otherwise
     * return an empty string.
     *
     * @param array $state
     * @returns string
     * @return mixed|string
     */
    protected static function getRelayStateUrl(array $state): mixed
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

    protected function initComposerAutoloader(): void
    {
        $path = __DIR__ . '/../../../vendor/autoload.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }

    protected static function isHeadedToProfileUrl($state, $ProfileUrl): bool
    {
        if (array_key_exists('saml:RelayState', $state)) {
            $currentDestination = self::getRelayStateUrl($state);
            if (!empty($currentDestination)) {
                return (str_starts_with($currentDestination, $ProfileUrl));
            }
        }
        return false;
    }

    /**
     * Redirect the user to set up profile.
     *
     * @param array $state
     */
    public static function redirectToProfile(array $state): void
    {
        $profileUrl = $state['ProfileUrl'];
        // Tell the profile-setup URL where the user is ultimately trying to go (if known).
        $currentDestination = self::getRelayStateUrl($state);
        $httpUtils = new HTTP();
        if (!empty($currentDestination)) {
            $profileUrl = $httpUtils->addURLParameters(
                $profileUrl,
                ['returnTo' => $currentDestination]
            );
        }

        $logger = LoggerFactory::getAccordingToState($state);
        $logger->warning(json_encode([
            'module' => 'profilereview',
            'event' => 'redirect to profile',
            'employeeId' => $state['employeeId'],
            'profileUrl' => $profileUrl,
        ]));

        $httpUtils->redirectTrustedURL($profileUrl);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function process(array &$state): void
    {
        // Get the necessary info from the state data.
        $employeeId = $this->getAttribute($this->employeeIdAttr, $state);
        $isHeadedToProfileUrl = self::isHeadedToProfileUrl($state, $this->profileUrl);

        $mfa = $this->getAttributeAllValues('mfa', $state);
        $method = $this->getMethod($state);
        $profileReview = $this->getAttribute('profile_review', $state);

        if (!$isHeadedToProfileUrl) {
            // Record to the state what logger class to use.
            $state['loggerClass'] = $this->loggerClass;

            $state['ProfileUrl'] = $this->profileUrl;

            if (self::needToShow($mfa['add'], self::MFA_ADD_PAGE)) {
                $this->redirectToNag($state, $employeeId, self::MFA_ADD_PAGE);
            }

            if (self::needToShow($method['add'], self::METHOD_ADD_PAGE)) {
                $this->redirectToNag($state, $employeeId, self::METHOD_ADD_PAGE);
            }

            if (self::needToShow($profileReview, self::REVIEW_PAGE)) {
                $this->redirectToProfileReview($state, $employeeId);
            }
        }

        $this->logger->warning(json_encode([
            'module' => 'profilereview',
            'event' => 'no nag/review needed',
            'isHeadedToProfileUrl' => $isHeadedToProfileUrl,
            'profileReview' => $profileReview,
            'mfa.add' => $mfa['add'],
            'method.add' => $method['add'],
            'employeeId' => $employeeId,
        ]));

        unset($state['Attributes']['method']);
        unset($state['Attributes']['mfa']);
    }

    /**
     * Redirect user to profile review page unless there is nothing to review
     *
     * @param array $state The state data.
     * @param string $employeeId The Employee ID of the user account.
     */
    protected function redirectToProfileReview(array &$state, string $employeeId): void
    {
        $mfaOptions = $this->getAllMfaOptionsExceptManager($state);
        $methodOptions = $this->getMethod($state)['options'];

        if (count($mfaOptions) == 0 && count($methodOptions) == 0) {
            return;
        }

        $this->redirectToNag($state, $employeeId, 'review');
    }

    /**
     * Redirect user to a template
     *
     * @param array $state
     * @param string $employeeId
     * @param string $template
     */
    protected function redirectToNag(array &$state, string $employeeId, string $template): void
    {
        $mfaOptions = $this->getAllMfaOptionsExceptManager($state);
        $methodOptions = $this->getMethod($state)['options'];

        /* Save state and redirect. */
        $state['employeeId'] = $employeeId;
        $state['mfaLearnMoreUrl'] = $this->mfaLearnMoreUrl;
        $state['profileUrl'] = $this->profileUrl;
        $state['mfaOptions'] = $mfaOptions;
        $state['methodOptions'] = $methodOptions;
        $state['template'] = $template;

        $stateId = State::saveState($state, self::STAGE_SENT_TO_NAG);
        $url = Module::getModuleURL('profilereview/nag.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, array('StateId' => $stateId));
    }

    public function getMethod(array $state): ?array
    {
        return $this->getAttributeAllValues('method', $state);
    }

    protected function getAllMfaOptionsExceptManager(array $state): array
    {
        $mfaOptions = $this->getAttributeAllValues('mfa', $state)['options'];
        foreach ($mfaOptions as $key => $mfaOption) {
            if ($mfaOption['type'] === 'manager') {
                unset($mfaOptions[$key]);
            } elseif ($mfaOption['type'] === 'webauthn') {
                $mfaOptions[$key]['count'] = self::getWebAuthnKeyCount($mfaOption);
                $mfaOptions[$key]['last_used_utc'] = self::getWebAuthnLastUsedUtc($mfaOption);
            }
        }
        return array_values($mfaOptions);
    }

    public static function getWebAuthnKeyCount(array $mfaOption): int
    {
        $data = $mfaOption['data'] ?? null;
        if (!is_array($data) || empty($data)) {
            return 0;
        }
        if (isset($data['count'])) {
            return (int)$data['count'];
        }
        if (array_is_list($data)) {
            return count($data);
        }
        return 1;
    }

    public static function getWebAuthnLastUsedUtc(array $mfaOption): ?string
    {
        $lastUsed = $mfaOption['last_used_utc'] ?? null;
        $data = $mfaOption['data'] ?? null;

        if (!is_array($data) || empty($data)) {
            return $lastUsed;
        }

        $keys = array_is_list($data) ? $data : [$data];
        foreach ($keys as $key) {
            if (is_array($key) && !empty($key['last_used_utc'])) {
                if ($lastUsed === null || $key['last_used_utc'] > $lastUsed) {
                    $lastUsed = $key['last_used_utc'];
                }
            }
        }

        return $lastUsed;
    }

    /**
     * @throws Exception
     */
    public static function hasSeenSplashPageRecently(string $page): bool
    {
        $session = Session::getSessionFromRequest();
        return (bool)$session->getData(
            self::SESSION_TYPE,
            $page
        );
    }

    /**
     * @throws Exception
     */
    public static function skipSplashPagesFor(int $seconds, string $page): void
    {
        $session = Session::getSessionFromRequest();
        $session->setData(
            self::SESSION_TYPE,
            $page,
            true,
            $seconds
        );
        $session->save();
    }

    /**
     * @throws Exception
     */
    public static function needToShow(?string $flag, string $page): bool
    {
        $oneDay = 24 * 60 * 60;
        if ($flag === 'yes' && !self::hasSeenSplashPageRecently($page)) {
            self::skipSplashPagesFor($oneDay, $page);
            return true;
        }
        return false;
    }
}
