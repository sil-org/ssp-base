<?php

namespace SimpleSAML\Module\silauth\Auth\Source;

use Exception;
use PDO;
use Sil\Psr3Adapters\Psr3StdOutLogger;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\Error;
use SimpleSAML\Module;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Module\silauth\Auth\Source\auth\Authenticator;
use SimpleSAML\Module\silauth\Auth\Source\auth\IdBroker;
use SimpleSAML\Module\silauth\Auth\Source\captcha\Captcha;
use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;
use SimpleSAML\Module\silauth\Auth\Source\http\Request;
use SimpleSAML\Utils\HTTP;
use yii\base\InvalidConfigException;

/**
 * Class SimpleSAML\Module\silauth\Auth\Source\SilAuth.
 *
 * SimpleSAMLphp auth library to support custom business rules
 *
 * Configuration settings defined in src/config/ssp-config.php.
 */
class SilAuth extends UserPassBase
{
    protected array $authConfig;
    protected array $idBrokerConfig;
    protected array $mysqlConfig;
    protected array $recaptchaConfig;
    protected array $templateData;

    /**
     * Constructor for this authentication source.
     *
     * All subclasses who implement their own constructor must call this constructor before
     * using $config for anything.
     *
     * @param array $info Information about this authentication source.
     * @param array $config Configuration for this authentication source.
     * @throws InvalidConfigException
     */
    public function __construct(array $info, array $config)
    {
        parent::__construct($info, $config);

        $this->authConfig = ConfigManager::getConfigFor('auth', $config);
        $this->idBrokerConfig = ConfigManager::getConfigFor('idBroker', $config);
        $this->mysqlConfig = ConfigManager::getConfigFor('mysql', $config);
        $this->recaptchaConfig = ConfigManager::getConfigFor('recaptcha', $config);
        $this->templateData = ConfigManager::getConfigFor('templateData', $config);

        $dbAttributes = [];
        $caFile = getenv('DB_CA_FILE_PATH');
        if (file_exists($caFile)) {
            $dbAttributes = [
                PDO::MYSQL_ATTR_SSL_CA => $caFile,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => 1,
            ];
        }

        ConfigManager::initializeYii2WebApp(['components' => ['db' => [
            'dsn' => sprintf(
                'mysql:host=%s;dbname=%s',
                $this->mysqlConfig['host'],
                $this->mysqlConfig['database']
            ),
            'username' => $this->mysqlConfig['user'],
            'password' => $this->mysqlConfig['password'],
            'attributes' => $dbAttributes,
        ]]]);
    }

    public function authenticate(array &$state): void
    {
        assert('is_array($state)');

        /*
         * Save the identifier of this authentication source, so that we can
         * retrieve it later. This allows us to call the login()-function on
         * the current object.
         */
        $state[self::AUTHID] = $this->authId;

        $state['templateData'] = $this->templateData;

        /* Save the $state-array, so that we can restore it after a redirect. */
        $id = State::saveState($state, self::STAGEID);

        /*
         * Redirect to the login form. We include the identifier of the saved
         * state array as a parameter to the login form.
         */
        $url = Module::getModuleURL('silauth/loginuserpass.php');
        $params = array('AuthState' => $id);
        $httpUtils = new HTTP();
        $httpUtils->redirectTrustedURL($url, $params);

        /* The previous function never returns, so this code is never executed. */
        assert('FALSE');
    }

    protected function getTrustedIpAddresses(): array
    {
        $trustedIpAddresses = [];
        $ipAddressesString = $this->authConfig['trustedIpAddresses'] ?? '';
        $stringPieces = explode(',', $ipAddressesString);
        foreach ($stringPieces as $stringPiece) {
            if (!empty($stringPiece)) {
                $trustedIpAddresses[] = $stringPiece;
            }
        }
        return $trustedIpAddresses;
    }

    /**
     * @throws Error
     * @throws Exception
     */
    protected function login(string $username, string $password): array
    {
        $logger = new Psr3StdOutLogger();
        $captcha = new Captcha($this->recaptchaConfig['secret'] ?? null);
        $idBroker = new IdBroker(
            $this->idBrokerConfig['baseUri'] ?? null,
            $this->idBrokerConfig['accessToken'] ?? null,
            $logger,
            $this->idBrokerConfig['idpDomainName'],
            $this->idBrokerConfig['trustedIpRanges'] ?? [],
            $this->idBrokerConfig['assertValidIp'] ?? true
        );
        $request = new Request($this->getTrustedIpAddresses());
        $userAgent = Request::getUserAgent() ?: '(unknown)';
        $authenticator = new Authenticator(
            $username,
            $password,
            $request,
            $captcha,
            $idBroker,
            $logger
        );

        if (!$authenticator->isAuthenticated()) {
            $authError = $authenticator->getAuthError();
            $logger->warning(json_encode([
                'event' => 'User/pass authentication result: failure',
                'username' => $username,
                'errorCode' => $authError->getCode(),
                'errorMessageParams' => $authError->getMessageParams(),
                'ipAddresses' => join(',', $request->getIpAddresses()),
                'userAgent' => $userAgent,
            ]));
            throw new Error([
                'WRONGUSERPASS',
                $authError->getFullSspErrorTag(),
                $authError->getMessageParams()
            ]);
        }

        $logger->warning(json_encode([
            'event' => 'User/pass authentication result: success',
            'username' => $username,
            'ipAddresses' => join(',', $request->getIpAddresses()),
            'userAgent' => $userAgent,
        ]));

        return $authenticator->getUserAttributes();
    }
}
