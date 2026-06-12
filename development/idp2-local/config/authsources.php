<?php

use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    // Use SilAuth
    'silauth' => ConfigManager::getSspConfig(),

    'example-userpass' => [
        'exampleauth:UserPass',

        // sildisco test user
        'sildisco_idp2:sildisco_password' => [
            'eduPersonPrincipalName' => ['sildisco@idp2'],
            'eduPersonTargetID' => ['57de2930-c5d2-4f6f-9328-d85a939c45d8'],
            'sn' => ['IDP2'],
            'givenName' => ['SilDisco'],
            'mail' => ['sildisco_idp2@example.com'],
            'employeeNumber' => ['50002'],
            'cn' => ['SILDISCO_IDP2'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('+6 months')),
            ],
            'mfa' => [
                'prompt' => 'no',
                'add' => 'no',
                'options' => [],
            ],
            'method' => [
                'add' => 'no',
            ],
            'profile_review' => ['no'],
        ],
    ]
];
