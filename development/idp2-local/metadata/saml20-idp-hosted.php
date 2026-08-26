<?php
/**
 * SAML 2.0 IdP configuration for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-hosted
 */

// Entity ID depends on the port -- see docs/development.md "Why idp1/idp2/idp4 listen on two ports".
$port = $_SERVER['SERVER_PORT'] ?? '80';
$entityId = in_array($port, ['80', '443'], true)
    ? 'http://ssp-idp2.local'
    : "http://ssp-idp2.local:$port";

$metadata[$entityId] = [
    'entityid' => $entityId,
    'name' => ['en' => 'IDP 2'],

    /*
     * The hostname of the server (VHOST) that will use this SAML entity.
     *
     * Can be '__DEFAULT__', to use this entry by default.
     */
    'host' => '__DEFAULT__',

    // X.509 key and certificate. Relative to the cert directory.
    'privatekey' => 'ssp-hub-idp2.pem',
    'certificate' => 'ssp-hub-idp2.crt',

    /*
     * Authentication source to use. Must be one that is configured in
     * 'config/authsources.php'.
     */
    'auth' => 'silauth',
];
