<?php
/**
 * SAML 2.0 IdP configuration for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-hosted
 */

$metadata['ssp-hub.local'] = [
    'entityid' => 'ssp-hub.local',

    /*
     * The hostname of the server (VHOST) that will use this SAML entity.
     *
     * Can be '__DEFAULT__', to use this entry by default.
     */
    'host' => 'ssp-hub.local',

    // X.509 key and certificate. Relative to the cert directory.
    'privatekey' => 'saml.pem',
    'certificate' => 'saml.crt',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-hub.local/saml2/idp/SSOService.php',
        ],
    ],

    /*
     * Authentication source to use. Must be one that is configured in
     * 'config/authsources.php'.
     */
    'auth' => 'hub-discovery',
    'authproc' => [
        95 => [
            'class' => 'sildisco:TrackIdps',
        ]
    ],
    'attributes.NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
];
