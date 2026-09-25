<?php

namespace SimpleSAML\Module\expirychecker\Auth\Process;

use Exception;
use Psr\Log\LoggerInterface;
use Sil\Psr3Adapters\Psr3SamlLogger;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Auth\State;
use SimpleSAML\Module;
use SimpleSAML\Module\expirychecker\Validator;
use SimpleSAML\Session;
use SimpleSAML\Utils\HTTP;

/**
 * Filter which either warns the user that their password is "about to expire"
 * (giving them the option of changing it now or later) or tells them that it
 * has expired (only allowing them to go change their password).
 *
 * See README.md for sample (and explanation of) expected configuration.
 */
class ExpiryDate extends ProcessingFilter
{
    const HAS_SEEN_SPLASH_PAGE = 'has_seen_splash_page';
    const SESSION_TYPE = 'expirychecker';

    private int $warnDaysBefore = 14;
    private string|null $passwordChangeUrl = null;
    private string|null $accountNameAttr = null;
    private string $employeeIdAttr = 'employeeNumber';
    private string|null $expiryDateAttr = null;

    protected LoggerInterface $logger;

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

        assert('is_array($config)');

        $this->initLogger($config);

        $this->loadValuesFromConfig($config, [
            'warnDaysBefore' => [
                Validator::INT,
            ],
            'passwordChangeUrl' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'accountNameAttr' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
            'expiryDateAttr' => [
                Validator::STRING,
                Validator::NOT_EMPTY,
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    protected function loadValuesFromConfig(array $config, array $attributeRules): void
    {
        foreach ($attributeRules as $attribute => $rules) {
            if (array_key_exists($attribute, $config)) {
                $this->$attribute = $config[$attribute];
            }

            Validator::validate($this->$attribute, $rules, $this->logger, $attribute);
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
     * Calculate how many days remain between now and when the password will
     * expire.
     *
     * @param int $expiryTimestamp The timestamp for when the password will
     *     expire.
     * @return int The number of days remaining
     */
    protected function getDaysLeftBeforeExpiry(int $expiryTimestamp): int
    {
        $now = time();
        $end = $expiryTimestamp;
        return round(($end - $now) / (24 * 60 * 60));
    }

    /**
     * Get the timestamp for when the user's password will expire, throwing an
     * exception if unable to do so.
     *
     * @param string $expiryDateAttr The name of the attribute where the
     *     expiration date (as a string) is stored.
     * @param array $state The state data.
     * @return int The expiration timestamp.
     * @throws Exception
     */
    protected function getExpiryTimestamp(string $expiryDateAttr, array $state): int
    {
        $expiryDateString = $this->getAttribute($expiryDateAttr, $state);

        // Ensure that EVERY user login provides a usable password expiration date.
        $expiryTimestamp = strtotime($expiryDateString) ?: null;
        if (empty($expiryTimestamp)) {
            throw new Exception(sprintf(
                "We could not understand the expiration date (%s, from %s) for "
                . "the user's password, so we do not know whether their "
                . "password is still valid.",
                var_export($expiryDateString, true),
                var_export($expiryDateAttr, true)
            ), 1496843359);
        }
        return $expiryTimestamp;
    }

    /**
     * @throws Exception
     */
    public static function hasSeenSplashPageRecently(): bool
    {
        $session = Session::getSessionFromRequest();
        return (bool)$session->getData(
            self::SESSION_TYPE,
            self::HAS_SEEN_SPLASH_PAGE
        );
    }

    /**
     * @throws Exception
     */
    public static function skipSplashPagesFor(int $seconds): void
    {
        $session = Session::getSessionFromRequest();
        $session->setData(
            self::SESSION_TYPE,
            self::HAS_SEEN_SPLASH_PAGE,
            true,
            $seconds
        );
        $session->save();
    }

    /**
     * @throws Exception
     */
    protected static function requireFreshLogin(string $authSource): void
    {
        $session = Session::getSessionFromRequest();
        $session->deleteData(self::SESSION_TYPE, self::HAS_SEEN_SPLASH_PAGE);
        $session->doLogout($authSource);
        $session->save();
    }

    /**
     * @throws Exception
     */
    protected function initLogger(array $config): void
    {
        $loggerClass = $config['loggerClass'] ?? Psr3SamlLogger::class;
        $this->logger = new $loggerClass();
        if (!$this->logger instanceof LoggerInterface) {
            throw new Exception(sprintf(
                'The specified loggerClass (%s) does not implement '
                . '\\Psr\\Log\\LoggerInterface.',
                var_export($loggerClass, true)
            ), 1496928725);
        }
    }

    /**
     * See if the given timestamp is in the past.
     *
     * @param int $timestamp The timestamp to check.
     * @return bool
     */
    public function isDateInPast(int $timestamp): bool
    {
        return ($timestamp < time());
    }

    /**
     * Check whether the user's password has expired.
     *
     * @param int $expiryTimestamp The timestamp for when the user's password
     *     will expire.
     * @return bool
     */
    public function isExpired(int $expiryTimestamp): bool
    {
        return $this->isDateInPast($expiryTimestamp);
    }

    /**
     * Check whether it's time to warn the user that they will need to change
     * their password soon.
     *
     * @param int $expiryTimestamp The timestamp for when the password expires.
     * @param int $warnDaysBefore How many days before the expiration we should
     *     warn the user.
     * @return boolean
     */
    public function isTimeToWarn(int $expiryTimestamp, int $warnDaysBefore): bool
    {
        $daysLeft = $this->getDaysLeftBeforeExpiry($expiryTimestamp);
        return ($daysLeft <= $warnDaysBefore);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function process(array &$state): void
    {
        $employeeId = $this->getAttribute($this->employeeIdAttr, $state);

        /* If the user has already seen a splash page from this AuthProc
         * recently, simply let them pass on through (so they can get into the
         * change-password website, for example).  */
        if (self::hasSeenSplashPageRecently()) {
            $this->logger->warning(json_encode([
                'event' => 'expirychecker: skip message, seen recently',
                'employeeId' => $employeeId,
            ]));

            /* Attributes only refresh at login, so they would still show this
             * password as expired after it is changed. Let this request through
             * but require a new login next time.  */
            if ($this->isExpired($this->getExpiryTimestamp($this->expiryDateAttr, $state))) {
                $this->logger->warning(json_encode([
                    'event' => 'expirychecker: password expired, requiring fresh login',
                    'employeeId' => $employeeId,
                ]));
                self::requireFreshLogin($state['Source']['auth']);
            }
            return;
        }

        // Get the necessary info from the state data.
        $accountName = $this->getAttribute($this->accountNameAttr, $state);
        $expiryTimestamp = $this->getExpiryTimestamp($this->expiryDateAttr, $state);

        $this->logger->warning(json_encode([
            'event' => 'expirychecker: will check expiration date',
            'employeeId' => $employeeId,
            'accountName' => $accountName,
            'expiryDateAttrValue' => $this->getAttribute($this->expiryDateAttr, $state),
            'expiryTimestamp' => $expiryTimestamp,
        ]));

        if ($this->isExpired($expiryTimestamp)) {
            $this->redirectToExpiredPage($state, $accountName);
        }

        // Display a password expiration warning page if it's time to do so.
        if ($this->isTimeToWarn($expiryTimestamp, $this->warnDaysBefore)) {
            $this->redirectToWarningPage($state, $accountName, $expiryTimestamp);
        }

        $this->logger->warning(json_encode([
            'event' => 'expirychecker: no action necessary',
            'employeeId' => $employeeId,
        ]));
    }

    /**
     * Redirect the user to the expired-password page.
     *
     * @param array $state The state data.
     * @param string $accountName The name of the user account.
     */
    public function redirectToExpiredPage(array &$state, string $accountName): void
    {
        assert('is_array($state)');

        $this->logger->warning(json_encode([
            'event' => 'expirychecker: password expired',
            'accountName' => $accountName,
        ]));

        /* Save state and redirect. */
        $state['passwordChangeUrl'] = $this->passwordChangeUrl;

        $id = State::saveState($state, 'expirychecker:expired');
        $url = Module::getModuleURL('expirychecker/expired.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, array('StateId' => $id));
    }

    /**
     * Redirect the user to the warning page.
     *
     * @param array $state The state data.
     * @param string $accountName The name of the user account.
     * @param int $expiryTimestamp When the password will expire.
     */
    protected function redirectToWarningPage(array &$state, string $accountName, int $expiryTimestamp): void
    {
        assert('is_array($state)');

        $this->logger->warning(json_encode([
            'event' => 'expirychecker: about to expire',
            'accountName' => $accountName,
        ]));

        $daysLeft = $this->getDaysLeftBeforeExpiry($expiryTimestamp);
        $state['daysLeft'] = (string)$daysLeft;

        if (isset($state['isPassive']) && $state['isPassive'] === true) {
            /* We have a passive request. Skip the warning. */
            return;
        }

        /* Save state and redirect. */
        $state['passwordChangeUrl'] = $this->passwordChangeUrl;

        $id = State::saveState($state, 'expirychecker:about2expire');
        $url = Module::getModuleURL('expirychecker/about2expire.php');

        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, array('StateId' => $id));
    }
}
