<?php

use Psr\Log\LoggerInterface;
use Sil\PhpEnv\Env;
use Sil\Psr3Adapters\Psr3EchoLogger;
use SimpleSAML\Module\silauth\Auth\Source\auth\Authenticator;
use SimpleSAML\Module\silauth\Auth\Source\auth\IdBroker;
use SimpleSAML\Module\silauth\Auth\Source\captcha\Captcha;
use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;
use SimpleSAML\Module\silauth\Auth\Source\http\Request;
use SimpleSAML\Module\silauth\Auth\Source\models\FailedLoginIpAddress;
use SimpleSAML\Module\silauth\Auth\Source\models\FailedLoginUsername;
use SimpleSAML\Module\silauth\Auth\Source\tests\fakes\FakeFailedIdBroker;
use SimpleSAML\Module\silauth\Auth\Source\tests\fakes\FakeInvalidIdBroker;
use SimpleSAML\Module\silauth\Auth\Source\tests\fakes\FakeSuccessfulIdBroker;
use SimpleSAML\Module\silauth\Auth\Source\tests\unit\captcha\DummyFailedCaptcha;
use SimpleSAML\Module\silauth\Auth\Source\tests\unit\captcha\DummySuccessfulCaptcha;
use SimpleSAML\Module\silauth\Auth\Source\tests\unit\http\DummyRequest;
use SimpleSAML\Module\silauth\Auth\Source\time\UtcTime;
use Webmozart\Assert\Assert;

//use yii\helpers\ArrayHelper;

/**
 * Defines application features from the specific context.
 */
class LoginContext extends FeatureContext
{
    /** @var Authenticator|null */
    private $authenticator = null;

    /** @var LoggerInterface */
    private $logger;

    /** @var Captcha */
    private $captcha;

    /** @var IdBroker */
    private $idBroker;

    /** @var Request */
    private $request;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $dbAttributes = [];
        $caFile = Env::get('DB_CA_FILE_PATH');
        if (file_exists($caFile)) {
            $dbAttributes = [
                PDO::MYSQL_ATTR_SSL_CA => $caFile,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => 1,
            ];
        }

        ConfigManager::initializeYii2WebApp(['components' => ['db' => [
            'dsn' => sprintf(
                'mysql:host=%s;dbname=%s',
                Env::get('MYSQL_HOST'),
                Env::get('MYSQL_DATABASE')
            ),
            'username' => Env::get('MYSQL_USER'),
            'password' => Env::get('MYSQL_PASSWORD'),
            'attributes' => $dbAttributes,
        ]]]);

        $this->logger = new Psr3EchoLogger();

        $this->captcha = new Captcha();
        $this->idBroker = new IdBroker(
            'http://fake.example.com/api/',
            'FakeAccessToken',
            $this->logger,
            'fake.example.com',
            [],
            false
        );
        $this->request = new Request();

        $this->resetDatabase();
        parent::__construct();
    }

    protected function addXFailedLoginUsernames(int $number, $username)
    {
        Assert::notEmpty($username);

        for ($i = 0; $i < $number; $i++) {
            $newRecord = new FailedLoginUsername(['username' => $username]);
            Assert::true($newRecord->save());
        }

        Assert::count(
            FailedLoginUsername::getFailedLoginsFor($username),
            $number
        );
    }

    protected function login()
    {
        $this->authenticator = new Authenticator(
            $this->username,
            $this->password,
            $this->request,
            $this->captcha,
            $this->idBroker,
            $this->logger
        );
    }

    protected function loginXTimes($numberOfTimes)
    {
        for ($i = 0; $i < $numberOfTimes; $i++) {
            $this->login();
        }
    }

    protected function resetDatabase()
    {
        FailedLoginIpAddress::deleteAll();
        FailedLoginUsername::deleteAll();
    }

    /**
     * @Given I provide a username
     */
    public function iProvideAUsername()
    {
        $this->username = 'a username';
    }

    /**
     * @Given I provide a password
     */
    public function iProvideAPassword()
    {
        $this->password = 'a password';
    }

    /**
     * @When I try to log in
     */
    public function iTryToLogIn()
    {
        $this->login();
    }

    /**
     * @Then I should not be allowed through
     */
    public function iShouldNotBeAllowedThrough()
    {
        Assert::false(
            $this->authenticator->isAuthenticated()
        );
        $authenticator = $this->authenticator;
        Assert::throws(
            function () use ($authenticator) {
                $authenticator->getUserAttributes();
            },
            Exception::class,
            'The call to getUserAttributes() should have thrown an exception.'
        );
    }

    /**
     * @Given I do not provide a username
     */
    public function iDoNotProvideAUsername()
    {
        $this->username = '';
    }

    /**
     * @Then I should see an error message with :text in it
     */
    public function iShouldSeeAnErrorMessageWithInIt($text)
    {
        $authError = $this->authenticator->getAuthError();
        Assert::notEmpty($authError);
        Assert::contains((string)$authError, $text);
    }

    /**
     * @Given I do not provide a password
     */
    public function iDoNotProvideAPassword()
    {
        $this->password = '';
    }

    /**
     * @Given I fail the captcha
     */
    public function iFailTheCaptcha()
    {
        $this->captcha = new DummyFailedCaptcha();
    }

    /**
     * @Then I should see a generic invalid-login error message
     */
    public function iShouldSeeAGenericInvalidLoginErrorMessage()
    {
        $authError = $this->authenticator->getAuthError();
        Assert::notEmpty($authError);
        Assert::contains((string)$authError, 'invalid_login');
    }

    /**
     * @Given I provide a username of :username
     */
    public function iProvideAUsernameOf($username)
    {
        $this->username = $username;
    }

    /**
     * @Then I should see an error message telling me to wait
     */
    public function iShouldSeeAnErrorMessageTellingMeToWait()
    {
        $authError = $this->authenticator->getAuthError();
        Assert::notEmpty($authError);
        Assert::contains((string)$authError, 'rate_limit');
    }

    /**
     * @Given I provide an incorrect password
     */
    public function iProvideAnIncorrectPassword()
    {
        $this->password = 'dummy incorrect password';
        $this->idBroker = new FakeFailedIdBroker('fake', 'fake', $this->logger);
    }

    /**
     * @Given that username will be rate limited after one more failed attempt
     */
    public function thatUsernameWillBeRateLimitedAfterOneMoreFailedAttempt()
    {
        FailedLoginUsername::resetFailedLoginsBy($this->username);

        $this->addXFailedLoginUsernames(
            Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN - 1,
            $this->username
        );
    }

    /**
     * @Given I (then) provide the correct password for that username
     */
    public function iProvideTheCorrectPasswordForThatUsername()
    {
        Assert::notEmpty($this->username);
        $this->password = 'dummy correct password';
        $this->idBroker = new FakeSuccessfulIdBroker('fake', 'fake', $this->logger);
    }

    /**
     * @Then I should not see an error message
     */
    public function iShouldNotSeeAnErrorMessage()
    {
        $authError = $this->authenticator->getAuthError();
        Assert::isEmpty(
            $authError,
            "Unexpected error: \n- " . $authError
        );
    }

    /**
     * @Then I should be allowed through
     */
    public function iShouldBeAllowedThrough()
    {
        Assert::true(
            $this->authenticator->isAuthenticated()
        );
        $userInfo = $this->authenticator->getUserAttributes();
        Assert::notEmpty($userInfo);
    }

    /**
     * @Then I should have the correct attributes
     */
    public function iShouldHaveTheCorrectAttributes()
    {
        Assert::true(
            $this->authenticator->isAuthenticated()
        );
        $userInfo = $this->authenticator->getUserAttributes();
        Assert::keyExists($userInfo, "eduPersonPrincipalName");
        Assert::keyExists($userInfo, "eduPersonUniqueId");
        Assert::keyExists($userInfo, "sn");
        Assert::keyExists($userInfo, "givenName");
        Assert::keyExists($userInfo, "displayName");
        Assert::keyExists($userInfo, "mail");
        Assert::keyExists($userInfo, "employeeNumber");
        Assert::keyExists($userInfo, "cn");
        Assert::keyExists($userInfo, "schacExpiryDate");
        Assert::keyExists($userInfo, "mfa");
        Assert::keyExists($userInfo, "method");
        Assert::keyExists($userInfo, "uuid");
        Assert::keyExists($userInfo, "manager_email");
        Assert::keyExists($userInfo, "profile_review");
        Assert::keyExists($userInfo, "member");
    }

    /**
     * @When I try to log in enough times to trigger the rate limit
     */
    public function iTryToLogInEnoughTimesToTriggerTheRateLimit()
    {
        $this->loginXTimes(
            Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN
        );
    }

    /**
     * @Given that username has :number more recent failed logins than the limit
     */
    public function thatUsernameHasMoreRecentFailedLoginsThanTheLimit($number)
    {
        Assert::true(is_numeric($number));

        FailedLoginUsername::resetFailedLoginsBy($this->username);

        $this->addXFailedLoginUsernames(
            $number + Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN,
            $this->username
        );
    }

    /**
     * @Then I should see an error message with :text1 and :text2 in it
     */
    public function iShouldSeeAnErrorMessageWithAndInIt($text1, $text2)
    {
        $authError = $this->authenticator->getAuthError();
        Assert::notEmpty($authError);
        $authErrorString = (string)$authError;
        Assert::contains($authErrorString, $text1);
        Assert::contains($authErrorString, $text2);
    }

    /**
     * @Given that username has enough failed logins to require a captcha
     */
    public function thatUsernameHasEnoughFailedLoginsToRequireACaptcha()
    {
        FailedLoginUsername::resetFailedLoginsBy($this->username);

        $this->addXFailedLoginUsernames(
            Authenticator::REQUIRE_CAPTCHA_AFTER_NTH_FAILED_LOGIN,
            $this->username
        );
    }

    /**
     * @Given that username has no recent failed login attempts
     */
    public function thatUsernameHasNoRecentFailedLoginAttempts()
    {
        Assert::notEmpty($this->username);
        FailedLoginUsername::resetFailedLoginsBy($this->username);
        Assert::eq(
            0,
            FailedLoginUsername::countRecentFailedLoginsFor($this->username)
        );
    }

    /**
     * @Then that username should be blocked for awhile
     */
    public function thatUsernameShouldBeBlockedForAwhile()
    {
        Assert::notEmpty($this->username);
        Assert::true(
            FailedLoginUsername::isRateLimitBlocking($this->username)
        );
    }

    /**
     * @Given my request comes from IP address :ipAddress
     */
    public function myRequestComesFromIpAddress($ipAddress)
    {
        if (!$this->request instanceof DummyRequest) {
            $this->request = new DummyRequest();
        }

        $this->request->setDummyIpAddress($ipAddress);
    }

    /**
     * @Then that IP address should be blocked for awhile
     */
    public function thatIpAddressShouldBeBlockedForAwhile()
    {
        $ipAddresses = $this->request->getUntrustedIpAddresses();
        Assert::count($ipAddresses, 1);
        $ipAddress = $ipAddresses[0];

        Assert::true(
            FailedLoginIpAddress::isRateLimitBlocking($ipAddress)
        );
    }

    /**
     * @Then that username's failed login attempts should be at :number
     */
    public function thatUsernameSFailedLoginAttemptsShouldBeAt($number)
    {
        Assert::notEmpty($this->username);
        Assert::true(is_numeric($number));
        Assert::count(
            FailedLoginUsername::getFailedLoginsFor($this->username),
            (int)$number
        );
    }

    /**
     * @Given that username does not have enough failed logins to require a captcha
     */
    public function thatUsernameDoesNotHaveEnoughFailedLoginsToRequireACaptcha()
    {
        Assert::notEmpty($this->username);
        FailedLoginUsername::deleteAll();
        Assert::isEmpty(FailedLoginUsername::getFailedLoginsFor($this->username));
    }

    /**
     * @Given my IP address has enough failed logins to require a captcha
     */
    public function myIpAddressHasEnoughFailedLoginsToRequireACaptcha()
    {
        $ipAddress = $this->request->getMostLikelyIpAddress();
        Assert::notNull($ipAddress, 'No IP address was provided.');
        FailedLoginIpAddress::deleteAll();
        Assert::isEmpty(FailedLoginIpAddress::getFailedLoginsFor($ipAddress));

        $desiredCount = Authenticator::REQUIRE_CAPTCHA_AFTER_NTH_FAILED_LOGIN;

        for ($i = 0; $i < $desiredCount; $i++) {
            $failedLoginIpAddress = new FailedLoginIpAddress([
                'ip_address' => $ipAddress,
            ]);
            Assert::true($failedLoginIpAddress->save());
        }

        Assert::eq(
            Authenticator::REQUIRE_CAPTCHA_AFTER_NTH_FAILED_LOGIN,
            FailedLoginIpAddress::countRecentFailedLoginsFor($ipAddress)
        );
    }

    /**
     * @Given that username has enough failed logins to be blocked by the rate limit
     */
    public function thatUsernameHasEnoughFailedLoginsToBeBlockedByTheRateLimit()
    {
        FailedLoginUsername::resetFailedLoginsBy($this->username);

        $this->addXFailedLoginUsernames(
            Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN,
            $this->username
        );
    }

    /**
     * @Given that IP address has triggered the rate limit
     */
    public function thatIpAddressHasTriggeredTheRateLimit()
    {
        $ipAddresses = $this->request->getUntrustedIpAddresses();
        Assert::count($ipAddresses, 1);
        $ipAddress = $ipAddresses[0];

        FailedLoginIpAddress::deleteAll();
        Assert::isEmpty(FailedLoginIpAddress::getFailedLoginsFor($ipAddress));

        $desiredCount = Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN;

        for ($i = 0; $i < $desiredCount; $i++) {
            $failedLoginIpAddress = new FailedLoginIpAddress([
                'ip_address' => $ipAddress,
            ]);
            Assert::true($failedLoginIpAddress->save());
        }

        Assert::true(
            FailedLoginIpAddress::isRateLimitBlocking($ipAddress)
        );
    }

    /**
     * @Given /^I pass (the|any) captchas?$/
     */
    public function iPassTheCaptcha()
    {
        $this->captcha = new DummySuccessfulCaptcha();
    }

    /**
     * @Given that username has :number more non-recent failed logins than the limit
     */
    public function thatUsernameHasMoreNonRecentFailedLoginsThanTheLimit($number)
    {
        Assert::notEmpty($this->username);
        Assert::true(is_numeric($number));

        $desiredNumber = $number + Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN;

        $numTotalFailures = count(FailedLoginUsername::getFailedLoginsFor($this->username));
        $numRecentFailures = FailedLoginUsername::countRecentFailedLoginsFor($this->username);
        $numNonRecentFailures = $numTotalFailures - $numRecentFailures;

        for ($i = $numNonRecentFailures; $i < $desiredNumber; $i++) {
            $failedLoginUsername = new FailedLoginUsername([
                'username' => $this->username,

                // NOTE: Use some time (UTC) longer ago than we consider "recent".
                'occurred_at_utc' => new UtcTime('-1 month'),
            ]);
            // NOTE: Don't validate, as that would overwrite the datetime field.
            Assert::true($failedLoginUsername->save(false));
        }

        $numTotalFailuresPost = count(FailedLoginUsername::getFailedLoginsFor($this->username));
        $numRecentFailuresPost = FailedLoginUsername::countRecentFailedLoginsFor($this->username);
        $numNonRecentFailuresPost = $numTotalFailuresPost - $numRecentFailuresPost;

        Assert::eq($desiredNumber, $numNonRecentFailuresPost);
    }

    /**
     * @Then I should not have to pass a captcha test for that user
     */
    public function iShouldNotHaveToPassACaptchaTestForThatUser()
    {
        Assert::notEmpty($this->username);
        Assert::false(
            FailedLoginUsername::isCaptchaRequiredFor($this->username)
        );
    }

    /**
     * @Given :ipAddress is a trusted IP address
     */
    public function isATrustedIpAddress($ipAddress)
    {
        $this->request->trustIpAddress($ipAddress);
    }

    /**
     * @Then the IP address :ipAddress should not have any failed login attempts
     */
    public function theIpAddressShouldNotHaveAnyFailedLoginAttempts($ipAddress)
    {
        Assert::true(Request::isValidIpAddress($ipAddress));
        Assert::isEmpty(FailedLoginIpAddress::getFailedLoginsFor($ipAddress));
    }

    /**
     * @Given the ID Broker is returning invalid responses
     */
    public function theIdBrokerIsReturningInvalidResponses()
    {
        $this->idBroker = new FakeInvalidIdBroker('fake', 'fake', $this->logger);
    }

    /**
     * @Then I should see a generic try-later error message
     */
    public function iShouldSeeAGenericTryLaterErrorMessage()
    {
        $authError = $this->authenticator->getAuthError();
        Assert::notEmpty($authError);
        Assert::contains((string)$authError, 'later');
    }

    /**
     * @Given :ipAddressRange is a trusted IP address range
     */
    public function isATrustedIpAddressRange($ipAddressRange)
    {
        $this->request->trustIpAddressRange($ipAddressRange);
    }

    /**
     * @Then the IP address :ipAddress should have a failed login attempt
     */
    public function theIpAddressShouldHaveAFailedLoginAttempt($ipAddress)
    {
        Assert::notEmpty($ipAddress);
        Assert::count(
            FailedLoginIpAddress::getFailedLoginsFor($ipAddress),
            1
        );
    }

    /**
     * @Given :numSeconds seconds ago that username had :numFailuresBeyondLimit more failed logins than the limit
     */
    public function secondsAgoThatUsernameHadMoreFailedLoginsThanTheLimit(
        $numSeconds,
        $numFailuresBeyondLimit
    ) {
        Assert::notEmpty($this->username);
        Assert::true(is_numeric($numSeconds));
        Assert::true(is_numeric($numFailuresBeyondLimit));

        FailedLoginUsername::resetFailedLoginsBy($this->username);

        $numDesiredFailuresTotal = $numFailuresBeyondLimit + Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN;

        for ($i = 0; $i < $numDesiredFailuresTotal; $i++) {
            $failedLoginUsername = new FailedLoginUsername([
                'username' => $this->username,
                'occurred_at_utc' => new UtcTime(sprintf(
                    '-%s seconds',
                    $numSeconds
                )),
            ]);
            // NOTE: Don't validate, as that would overwrite the datetime field.
            Assert::true($failedLoginUsername->save(false));
        }

        $numTotalFailuresPost = count(FailedLoginUsername::getFailedLoginsFor($this->username));

        Assert::eq($numDesiredFailuresTotal, $numTotalFailuresPost);
    }

    /**
     * @Given :numSeconds seconds ago the IP address :ipAddress had :numFailuresBeyondLimit more failed logins than the limit
     */
    public function secondsAgoTheIpAddressHadMoreFailedLoginsThanTheLimit(
        $numSeconds,
        $ipAddress,
        $numFailuresBeyondLimit
    ) {
        Assert::notEmpty($ipAddress);
        Assert::true(is_numeric($numSeconds));
        Assert::true(is_numeric($numFailuresBeyondLimit));

        FailedLoginIpAddress::resetFailedLoginsBy([$ipAddress]);

        $numDesiredFailuresTotal = $numFailuresBeyondLimit + Authenticator::BLOCK_AFTER_NTH_FAILED_LOGIN;

        for ($i = 0; $i < $numDesiredFailuresTotal; $i++) {
            $failedLoginIpAddress = new FailedLoginIpAddress([
                'ip_address' => $ipAddress,
                'occurred_at_utc' => new UtcTime(sprintf(
                    '-%s seconds',
                    $numSeconds
                )),
            ]);
            // NOTE: Don't validate, as that would overwrite the datetime field.
            Assert::true($failedLoginIpAddress->save(false));
        }

        $numTotalFailuresPost = count(FailedLoginIpAddress::getFailedLoginsFor($ipAddress));

        Assert::eq($numDesiredFailuresTotal, $numTotalFailuresPost);
    }
}
