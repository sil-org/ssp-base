<?php

$metadata['idp-empty'] = [
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://idp-empty/saml2/idp/SSOService.php',
        ],
    ],
    'IDPNamespace' => '',
];

$metadata['idp-bad'] = [
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://idp-bad/saml2/idp/SSOService.php',
        ],
    ],
    'IDPNamespace' => 'ba!d!',
];

$metadata['idp-bare'] = [
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://idp-bare/saml2/idp/SSOService.php',
        ],
    ],
];

$metadata['idp-good'] = [
    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://idp-bare/saml2/idp/SSOService.php',
        ],
    ],
    'IDPNamespace' => 'idpGood',
];
