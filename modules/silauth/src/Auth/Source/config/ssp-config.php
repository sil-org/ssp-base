<?php

use Sil\PhpEnv\Env;

return [
    'silauth:SilAuth',
    'auth.trustedIpAddresses' => Env::get('TRUSTED_IP_ADDRESSES'),
    'idBroker.accessToken' => Env::get('ID_BROKER_ACCESS_TOKEN'),
    'idBroker.assertValidIp' => Env::get('ID_BROKER_ASSERT_VALID_IP'),
    'idBroker.baseUri' => Env::get('ID_BROKER_BASE_URI'),
    'idBroker.trustedIpRanges' => Env::getArray('ID_BROKER_TRUSTED_IP_RANGES'),
    'idBroker.idpDomainName' => Env::requireEnv('IDP_DOMAIN_NAME'),
    'mysql.host' => Env::get('MYSQL_HOST'),
    'mysql.database' => Env::get('MYSQL_DATABASE'),
    'mysql.user' => Env::get('MYSQL_USER'),
    'mysql.password' => Env::get('MYSQL_PASSWORD'),
    'recaptcha.siteKey' => Env::get('RECAPTCHA_SITE_KEY'),
    'recaptcha.secret' => Env::get('RECAPTCHA_SECRET_KEY'),
    'templateData.profileUrl' => Env::get('PROFILE_URL'),
    'templateData.helpCenterUrl' => Env::get('HELP_CENTER_URL'),
];
