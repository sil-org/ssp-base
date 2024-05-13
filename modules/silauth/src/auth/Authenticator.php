<?php
namespace Sil\SilAuth\auth;

use Psr\Log\LoggerInterface;
use Sil\SilAuth\auth\AuthError;
use Sil\SilAuth\auth\IdBroker;
use Sil\SilAuth\captcha\Captcha;
use Sil\SilAuth\time\UtcTime;
use Sil\SilAuth\time\WaitTime;
use Sil\SilAuth\models\FailedLoginIpAddress;
use Sil\SilAuth\models\FailedLoginUsername;
use Sil\SilAuth\http\Request;

/**
 * An immutable class for making a single attempt to authenticate using a given
 * username and password.
 */
class Authenticator
{
    const REQUIRE_CAPTCHA_AFTER_NTH_FAILED_LOGIN = 10;
    const BLOCK_AFTER_NTH_FAILED_LOGIN = 50;
    const MAX_SECONDS_TO_BLOCK = 3600; // 3600 seconds = 1 hour
    
    /** @var AuthError|null */
    private $authError = null;
    
    /** @var LoggerInterface */
    protected $logger;
    
    private $userAttributes = null;
    
    /**
     * Attempt to authenticate using the given username and password. Check
     * isAuthenticated() to see whether authentication was successful.
     * 
     * @param string $username The username to check.
     * @param string $password The password to check.
     * @param Request $request An object representing the HTTP request.
     * @param Captcha $captcha A way to check the submitted captcha.
     * @param IdBroker $idBroker An object for communicating with the ID Broker.
     * @param LoggerInterface $logger A PSR-3 compliant logger.
     */
    public function __construct(
            $username,
            $password,
            Request $request,
            Captcha $captcha,
            IdBroker $idBroker,
            LoggerInterface $logger
    ) {
        $this->logger = $logger;
        
        /** @todo Check CSRF here, too, if feasible. */
        
        if (empty($username)) {
            $this->setErrorUsernameRequired();
            return;
        }
        
        if (empty($password)) {
            $this->setErrorPasswordRequired();
            return;
        }
        
        $ipAddresses = $request->getUntrustedIpAddresses();
        
        if ($this->isBlockedByRateLimit($username, $ipAddresses)) {
            $logger->warning(json_encode([
                'event' => 'Preventing login attempt due to existing rate limit',
                'username' => $username,
                'ipAddresses' => join(',', $ipAddresses),
            ]));
            $this->setErrorBlockedByRateLimit(
                $this->getWaitTimeUntilUnblocked($username, $ipAddresses)
            );
            return;
        }
        
        if (self::isCaptchaRequired($username, $ipAddresses)) {
            $logger->warning(json_encode([
                'event' => 'Requiring captcha',
                'username' => $username,
                'ipAddresses' => join(',', $ipAddresses),
            ]));
            if ( ! $captcha->isValidIn($request)) {
                $logger->warning(json_encode([
                    'event' => 'Invalid/missing captcha',
                    'username' => $username,
                    'ipAddresses' => join(',', $ipAddresses),
                ]));
                $this->setErrorInvalidLogin();
                return;
            }
        }
        
        try {
            $authenticatedUser = $idBroker->getAuthenticatedUser(
                $username,
                $password
            );
        } catch (\Exception $e) {
            $logger->critical(json_encode([
                'event' => 'Problem communicating with ID Broker',
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage(),
                'username' => $username,
                'ipAddresses' => join(',', $ipAddresses),
            ]));
            $this->setErrorGenericTryLater();
            return;
        }
        
        if ($authenticatedUser === null) {
            $this->recordFailedLoginBy($username, $ipAddresses);
            
            if ($this->isBlockedByRateLimit($username, $ipAddresses)) {
                $logger->warning(json_encode([
                    'event' => 'Activating rate-limit block',
                    'username' => $username,
                    'ipAddresses' => join(',', $ipAddresses),
                ]));
                $this->setErrorBlockedByRateLimit(
                    $this->getWaitTimeUntilUnblocked($username, $ipAddresses)
                );
            } else {
                $this->setErrorInvalidLogin();
            }
            return;
        }
        
        // NOTE: If we reach this point, the user successfully authenticated.
        
        $this->resetFailedLoginsBy($username, $ipAddresses);
        
        $this->setUserAttributes($authenticatedUser);
    }
    
    /**
     * Calculate how many seconds of delay should be required for the given
     * number of recent failed login attempts.
     *
     * @param int $numRecentFailures The number of recent failed login attempts.
     * @return int The number of seconds to delay before allowing another such
     *     login attempt.
     */
    public static function calculateSecondsToDelay($numRecentFailures)
    {
        if ( ! self::isEnoughFailedLoginsToBlock($numRecentFailures)) {
            return 0;
        }
        
        $limit = self::BLOCK_AFTER_NTH_FAILED_LOGIN;
        $numFailuresPastLimit = $numRecentFailures - $limit;
        $numberToUse = max($numFailuresPastLimit, 3);
        
        return min(
            $numberToUse * $numberToUse,
            self::MAX_SECONDS_TO_BLOCK
        );
    }
    
    /**
     * Get the error information (if any).
     * 
     * @return AuthError|null
     */
    public function getAuthError()
    {
        return $this->authError;
    }
    
    /**
     * Get the number of seconds to continue blocking, based on the given number
     * of recent failures and the given date/time of the most recent failed
     * login attempt.
     *
     * @param int $numRecentFailures The number of recent failed login attempts.
     * @param string|null $mostRecentFailureAt A date/time string for when the
     *     most recent failed login attempt occurred. If null (meaning there
     *     have been no recent failures), then zero (0) will be returned.
     * @return int The number of seconds
     * @throws Exception If an invalid (but non-null) date/time string is given
     *     for `$mostRecentFailureAt`.
     */
    public static function getSecondsUntilUnblocked(
        int $numRecentFailures,
        $mostRecentFailureAt
    ) {
        if ($mostRecentFailureAt === null) {
            return 0;
        }
        
        $totalSecondsToBlock = self::calculateSecondsToDelay(
            $numRecentFailures
        );
        
        $secondsSinceLastFailure = UtcTime::getSecondsSinceDateTime(
            $mostRecentFailureAt
        );
        
        return UtcTime::getRemainingSeconds(
            $totalSecondsToBlock,
            $secondsSinceLastFailure
        );
    }
    
    /**
     * Get the attributes about the authenticated user.
     *
     * @return array<string,array> The user attributes. Example:<pre>
     *     [
     *         // ...
     *         'mail' => ['someone@example.com'],
     *         // ...
     *     ]
     *     </pre>
     * @throws \Exception
     */
    public function getUserAttributes()
    {
        if ($this->userAttributes === null) {
            throw new \Exception(
                "You cannot get the user's attributes until you have authenticated the user.",
                1482270373
            );
        }
        
        return $this->userAttributes;
    }
    
    /**
     * Get a (user friendly) wait time representing how long the user must wait
     * until they will no longer be blocked by a rate limit (regardless of
     * whether it is their username and/or IP address that is blocked).
     *
     * NOTE: This will always return a WaitTime, even if the given username and
     *       IP addresses aren't blocked (in which case the shortest available
     *       WaitTime will be returned, such as a 5-second wait time).
     *
     * @param string $username The username in question.
     * @param array $ipAddresses The list of relevant IP addresses (related to
     *     this request).
     * @return WaitTime
     */
    protected function getWaitTimeUntilUnblocked($username, array $ipAddresses)
    {
        $durationsInSeconds = [
            FailedLoginUsername::getSecondsUntilUnblocked($username),
        ];
        
        foreach ($ipAddresses as $ipAddress) {
            $durationsInSeconds[] = FailedLoginIpAddress::getSecondsUntilUnblocked($ipAddress);
        }
        
        return WaitTime::getLongestWaitTime($durationsInSeconds);
    }
    
    protected function hasError()
    {
        return ($this->authError !== null);
    }
    
    /**
     * Check whether authentication was successful. If not, call
     * getErrorMessage() and/or getErrorCode() to find out why not.
     * 
     * @return bool
     */
    public function isAuthenticated()
    {
        return ( ! $this->hasError());
    }
    
    protected function isBlockedByRateLimit($username, array $ipAddresses)
    {
        return FailedLoginUsername::isRateLimitBlocking($username) ||
               FailedLoginIpAddress::isRateLimitBlockingAnyOfThese($ipAddresses);
    }
    
    public static function isCaptchaRequired($username, array $ipAddresses)
    {
        return FailedLoginUsername::isCaptchaRequiredFor($username) ||
               FailedLoginIpAddress::isCaptchaRequiredForAnyOfThese($ipAddresses);
    }
    
    public static function isEnoughFailedLoginsToBlock($numFailedLogins)
    {
        return ($numFailedLogins >= self::BLOCK_AFTER_NTH_FAILED_LOGIN);
    }
    
    public static function isEnoughFailedLoginsToRequireCaptcha($numFailedLogins)
    {
        return ($numFailedLogins >= self::REQUIRE_CAPTCHA_AFTER_NTH_FAILED_LOGIN);
    }
    
    protected function recordFailedLoginBy($username, array $ipAddresses)
    {
        FailedLoginUsername::recordFailedLoginBy($username, $this->logger);
        FailedLoginIpAddress::recordFailedLoginBy($ipAddresses, $this->logger);
    }
    
    protected function resetFailedLoginsBy($username, array $ipAddresses)
    {
        FailedLoginUsername::resetFailedLoginsBy($username);
        FailedLoginIpAddress::resetFailedLoginsBy($ipAddresses);
    }
    
    protected function setError($code, $messageParams = [])
    {
        $this->authError = new AuthError($code, $messageParams);
    }
    
    /**
     * @param WaitTime $waitTime
     */
    protected function setErrorBlockedByRateLimit($waitTime)
    {
        $unit = $waitTime->getUnit();
        $number = $waitTime->getFriendlyNumber();
        
        if ($unit === WaitTime::UNIT_SECOND) {
            $errorCode = AuthError::CODE_RATE_LIMIT_SECONDS;
        } else { // = minute
            if ($number === 1) {
                $errorCode = AuthError::CODE_RATE_LIMIT_1_MINUTE;
            } else {
                $errorCode = AuthError::CODE_RATE_LIMIT_MINUTES;
            }
        }
        
        $this->setError($errorCode, ['{number}' => $number]);
    }
    
    protected function setErrorGenericTryLater()
    {
        $this->setError(AuthError::CODE_GENERIC_TRY_LATER);
    }
    
    protected function setErrorInvalidLogin()
    {
        $this->setError(AuthError::CODE_INVALID_LOGIN);
    }
    
    protected function setErrorNeedToSetAcctPassword()
    {
        $this->setError(AuthError::CODE_NEED_TO_SET_ACCT_PASSWORD);
    }
    
    protected function setErrorPasswordRequired()
    {
        $this->setError(AuthError::CODE_PASSWORD_REQUIRED);
    }
    
    protected function setErrorUsernameRequired()
    {
        $this->setError(AuthError::CODE_USERNAME_REQUIRED);
    }
    
    protected function setUserAttributes($attributes)
    {
        $this->userAttributes = $attributes;
    }
}
