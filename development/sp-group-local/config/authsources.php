<?php

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        'core:AdminPassword',
    ],

    // NOTE: there is deliberately no saml20-idp-remote entry for 'https://ssp-idp1.local'
    // (nor a matching saml20-sp-remote entry for 'https://pwmanager.local' on idp1's side).
    // features/mfa.feature's "Following the requirement to go set up MFA" scenario relies on
    // that absence: visiting MFA_SETUP_URL throws METADATANOTFOUND in place (no further
    // redirect), which is the only thing that lets the test's real-browser Mink driver observe
    // the browser landing on that exact URL -- there's no clean way to catch an intermediate
    // redirect hop with this driver otherwise. Wiring up real metadata here makes that visit
    // trigger a genuine SSO round-trip back to idp1 instead (which, since this mock has no real
    // backend to grant an MFA method, just bounces the user straight back to
    // must-set-up-mfa.php) and breaks that assertion. See features/mfa.feature for details.
    'mfa-idp' => [
        'saml:SP',
        'entityID' => 'https://pwmanager.local',
        'idp' => 'https://ssp-idp1.local',
        'discoURL' => null,
        'NameIDPolicy' => [
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'AllowCreate' => true,
        ],
    ],

    'sp1' => [
        'saml:SP',
        'entityID' => 'https://ssp-sp1.local',
        'idp' => 'ssp-hub.local',
        'discoURL' => null,
        'NameIDPolicy' => [
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'AllowCreate' => true,
        ],
        'privatekey' => 'saml-sp.pem',
    ],

    'sp2' => [
        'saml:SP',
        'entityID' => 'https://ssp-sp2.local',
        'idp' => 'ssp-hub.local',
        'discoURL' => null,
        'privatekey' => 'saml-sp.pem',
    ],

    'sp3' => [
        'saml:SP',
        'entityID' => 'https://ssp-sp3.local',
        'idp' => 'ssp-hub.local',
        'discoURL' => null,
        'privatekey' => 'saml-sp.pem',
    ],

    'sp4' => [
        'saml:SP',
        'entityID' => 'https://ssp-sp4.local',
        'idp' => 'ssp-hub.local',
        'discoURL' => null,
        'NameIDPolicy' => [
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'AllowCreate' => true,
        ],
        'privatekey' => 'saml-sp.pem',
    ],


    'sp5' => [
        'saml:SP',
        'entityID' => 'https://ssp-sp5.local',
        'idp' => 'ssp-hub.local',
        'discoURL' => null,
        'NameIDPolicy' => [
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            'AllowCreate' => true,
        ],
        'privatekey' => 'saml-sp.pem',
    ],

];
