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

$metadata['https://ssp-idp4.local'] = [
    'entityid' => 'https://ssp-idp4.local',
    'name' => ['en' => 'IDP 4'],

    /*
     * The hostname of the server (VHOST) that will use this SAML entity.
     *
     * Can be '__DEFAULT__', to use this entry by default.
     */
    'host' => '__DEFAULT__',

    // X.509 key and certificate. Relative to the cert directory.
    'privatekey' => 'ssp-hub-idp4.pem',
    'certificate' => 'ssp-hub-idp4.crt',

    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+4',

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
            'mfaSetupUrl' => Env::get('MFA_SETUP_URL'),
            'loggerClass' => Psr3SamlLogger::class,
            'recoveryContactsApi' => Env::get('MFA_RECOVERY_CONTACTS_API'),
            'recoveryContactsApiKey' => Env::get('MFA_RECOVERY_CONTACTS_API_KEY'),
            'recoveryContactsFallbackName' => Env::get('MFA_RECOVERY_CONTACTS_FALLBACK_NAME'),
            'recoveryContactsFallbackEmail' => Env::get('MFA_RECOVERY_CONTACTS_FALLBACK_EMAIL'),
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
            'profileUrl' => Env::get('PROFILE_URL'),
            'loggerClass' => Psr3SamlLogger::class,
        ],
    ],
];
