<?php

use Sil\PhpEnv\Env;
use Sil\Psr3Adapters\Psr3SamlLogger;
use Sil\Psr3Adapters\Psr3StdOutLogger;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;

/**
 * SAML 2.0 IdP configuration for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-hosted
 */

// Entity ID and pwmanager authsource depend on the port -- see docs/development.md "Why idp1/idp2/idp4 listen on two ports".
$port = $_SERVER['SERVER_PORT'] ?? '80';
$isDefaultPort = in_array($port, ['80', '443'], true);
$entityId = $isDefaultPort ? 'http://ssp-idp1.local' : "http://ssp-idp1.local:$port";
$mfaSetupUrl = $isDefaultPort ? Env::get('PROFILE_URL_FOR_TESTS') : Env::get('MFA_SETUP_URL');
$profileUrl = $isDefaultPort ? Env::get('PROFILE_URL_FOR_TESTS') : Env::get('PROFILE_URL');

$metadata[$entityId] = [
    'entityid' => $entityId,
    'name' => ['en' => 'IDP 1'],

    /*
     * The hostname of the server (VHOST) that will use this SAML entity.
     *
     * Can be '__DEFAULT__', to use this entry by default.
     */
    'host' => '__DEFAULT__',

    // X.509 key and certificate. Relative to the cert directory.
    'privatekey' => 'ssp-hub-idp.pem',
    'certificate' => 'ssp-hub-idp.crt',

    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+1',

    /*
     * Authentication source to use. Must be one that is configured in
     * 'config/authsources.php'.
     */
    'auth' => 'example-userpass',

    'authproc' => [
        10 => [
            'class' => 'mfa:Mfa',
            'employeeIdAttr' => 'employeeNumber',
            'idBrokerAccessToken' => Env::get('ID_BROKER_ACCESS_TOKEN'),
            'idBrokerAssertValidIp' => Env::get('ID_BROKER_ASSERT_VALID_IP'),
            'idBrokerBaseUri' => Env::get('ID_BROKER_BASE_URI'),
            'idBrokerClientClass' => FakeIdBrokerClient::class,
            'idBrokerTrustedIpRanges' => Env::get('ID_BROKER_TRUSTED_IP_RANGES'),
            'idpDomainName' => Env::get('IDP_DOMAIN_NAME'),
            'mfaSetupUrl' => $mfaSetupUrl,
            'loggerClass' => Psr3SamlLogger::class,
        ],
        15 => [
            'class' => 'expirychecker:ExpiryDate',
            'accountNameAttr' => 'cn',
            'expiryDateAttr' => 'schacExpiryDate',
            'passwordChangeUrl' => 'http://www.example.com/change-password',
            'warnDaysBefore' => 14,
            'dateFormat' => 'Y-m-d',
            'loggerClass' => Psr3StdOutLogger::class,
        ],
        30 => [
            'class' => 'profilereview:ProfileReview',
            'employeeIdAttr' => 'employeeNumber',
            'mfaLearnMoreUrl' => Env::get('MFA_LEARN_MORE_URL'),
            'profileUrl' => $profileUrl,
            'loggerClass' => Psr3SamlLogger::class,
        ],
    ],
];
