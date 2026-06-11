<?php

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    // Set up example users for testing expirychecker module.
    'example-userpass' => [
        'exampleauth:UserPass',

        'users' => [
            // expirychecker test user whose password expires in the distant future
            'distant_future:a' => [
                'eduPersonPrincipalName' => ['DISTANT_FUTURE@ssp-idp1.local'],
                'sn' => ['Future'],
                'givenName' => ['Distant'],
                'mail' => ['distant_future@example.com'],
                'employeeNumber' => ['11111'],
                'cn' => ['DISTANT_FUTURE'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')), // Distant future
                ],
            ],

            // expirychecker test user whose password expires in the near future
            'near_future:b' => [
                'eduPersonPrincipalName' => ['NEAR_FUTURE@ssp-idp1.local'],
                'sn' => ['Future'],
                'givenName' => ['Near'],
                'mail' => ['near_future@example.com'],
                'employeeNumber' => ['22222'],
                'cn' => ['NEAR_FUTURE'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+3 days')), // Soon but not tomorrow
                ],
            ],

            // expirychecker test user whose password expires in one day
            'next_day:a' => [
                'eduPersonPrincipalName' => ['NEXT_DAY@ssp-hub-idp2.local'],
                'eduPersonTargetID' => ['22888888-2222-2222-2222-222222222222'],
                'sn' => ['Day'],
                'givenName' => ['Next'],
                'mail' => ['next_day@example.com'],
                'employeeNumber' => ['22888'],
                'cn' => ['NEXT_DAY'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+1 day')), // Very soon
                ],
            ],

            // expirychecker test user whose password expires in the past
            'already_past:c' => [
                'eduPersonPrincipalName' => ['ALREADY_PAST@ssp-idp1.local'],
                'sn' => ['Past'],
                'givenName' => ['Already'],
                'mail' => ['already_past@example.com'],
                'employeeNumber' => ['33333'],
                'cn' => ['ALREADY_PAST'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('-1 day')), // In the past
                ],
            ],

            // expirychecker test user whose password expiry is missing
            'missing_exp:d' => [
                'eduPersonPrincipalName' => ['MISSING_EXP@ssp-idp-1.local'],
                'sn' => ['Expiration'],
                'givenName' => ['Missing'],
                'mail' => ['missing_exp@example.com'],
                'employeeNumber' => ['44444'],
                'cn' => ['MISSING_EXP'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
            ],

            // expirychecker test user whose password expiry is invalid
            'invalid_exp:e' => [
                'eduPersonPrincipalName' => ['INVALID_EXP@ssp-idp-1.local'],
                'sn' => ['Expiration'],
                'givenName' => ['Invalid'],
                'mail' => ['invalid_exp@example.com'],
                'employeeNumber' => ['55555'],
                'cn' => ['INVALID_EXP'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                ],
                'method' => [
                    'add' => 'no',
                ],
                'schacExpiryDate' => [
                    'invalid'
                ],
            ],

            // profilereview test user whose profile is not due for review
            'no_review:e' => [
                'eduPersonPrincipalName' => ['NO_REVIEW@idp'],
                'eduPersonTargetID' => ['11111111-1111-1111-1111-111111111111'],
                'sn' => ['Review'],
                'givenName' => ['No'],
                'mail' => ['no_review@example.com'],
                'employeeNumber' => ['11111'],
                'cn' => ['NO_REVIEW'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => 111,
                            'type' => 'backupcode',
                            'label' => '2SV #1',
                            'created_utc' => '2017-10-24T20:40:47Z',
                            'last_used_utc' => null,
                            'data' => [
                                'count' => 10
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                ],
                'profile_review' => ['no'],
            ],

            // profilereview test user whose profile is flagged for mfa_add review
            'mfa_add:f' => [
                'eduPersonPrincipalName' => ['MFA_ADD@idp'],
                'eduPersonTargetID' => ['22222222-2222-2222-2222-222222222222'],
                'sn' => ['Add'],
                'givenName' => ['Mfa'],
                'mail' => ['mfa_add@example.com'],
                'employeeNumber' => ['22222'],
                'cn' => ['MFA_ADD'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'yes',
                    'options' => [],
                ],
                'method' => [
                    'add' => 'no',
                ],
                'profile_review' => ['no'],
            ],

            // profilereview test user whose profile is flagged for method_add review
            'method_add:g' => [
                'eduPersonPrincipalName' => ['METHOD_ADD@methodidp'],
                'eduPersonTargetID' => ['44444444-4444-4444-4444-444444444444'],
                'sn' => ['Add'],
                'givenName' => ['Method'],
                'mail' => ['method_add@example.com'],
                'employeeNumber' => ['44444'],
                'cn' => ['METHOD_ADD'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => 444,
                            'type' => 'backupcode',
                            'label' => '2SV #1',
                            'created_utc' => '2017-10-24T20:40:47Z',
                            'last_used_utc' => null,
                            'data' => [
                                'count' => 10
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'yes',
                ],
                'profile_review' => ['no'],
            ],

            // profilereview test user whose profile is flagged for profile review
            'profile_review:h' => [
                'eduPersonPrincipalName' => ['METHOD_REVIEW@methodidp'],
                'eduPersonTargetID' => ['55555555-5555-5555-5555-555555555555'],
                'sn' => ['Review'],
                'givenName' => ['Method'],
                'mail' => ['method_review@example.com'],
                'employeeNumber' => ['55555'],
                'cn' => ['METHOD_REVIEW'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => 555,
                            'type' => 'backupcode',
                            'label' => '2SV #1',
                            'created_utc' => '2017-10-24T20:40:47Z',
                            'last_used_utc' => null,
                            'data' => [
                                'count' => 10
                            ],
                        ],
                        [
                            'id' => 556,
                            'type' => 'manager',
                            'label' => '2SV #2',
                            'created_utc' => '2017-10-24T20:40:47Z',
                            'last_used_utc' => '2017-10-24T20:41:57Z',
                            'data' => [
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '55555555555555555555555555555555',
                            'value' => 'method@example.com',
                            'verified' => true,
                            'created' => '2017-10-24T20:40:47Z',
                        ],
                    ],
                ],
                'profile_review' => ['yes']
            ],

            // mfa test user who does not require mfa
            'no_mfa_needed:a' => [
                'eduPersonPrincipalName' => ['NO_MFA_NEEDED@mfaidp'],
                'eduPersonTargetID' => ['11111111-1111-1111-1111-111111111111'],
                'sn' => ['Needed'],
                'givenName' => ['No MFA'],
                'mail' => ['no_mfa_needed@example.com'],
                'employeeNumber' => ['11111'],
                'cn' => ['NO_MFA_NEEDED'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'no',
                    'add' => 'no',
                    'options' => [],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa to be set up
            'must_set_up_mfa:a' => [
                'eduPersonPrincipalName' => ['MUST_SET_UP_MFA@mfaidp'],
                'eduPersonTargetID' => ['22222222-2222-2222-2222-222222222222'],
                'sn' => ['Set Up MFA'],
                'givenName' => ['Must'],
                'mail' => ['must_set_up_mfa@example.com'],
                'employeeNumber' => ['22222'],
                'cn' => ['MUST_SET_UP_MFA'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has backup codes
            'has_backupcode:a' => [
                'eduPersonPrincipalName' => ['HAS_BACKUPCODE@mfaidp'],
                'eduPersonTargetID' => ['33333333-3333-3333-3333-333333333333'],
                'sn' => ['Backupcode'],
                'givenName' => ['Has'],
                'mail' => ['has_backupcode@example.com'],
                'employeeNumber' => ['33333'],
                'cn' => ['HAS_BACKUPCODE'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '7',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has backup codes and a manager email
            'has_backupcode_and_mgr:a' => [
                'eduPersonPrincipalName' => ['HAS_BACKUPCODE@mfaidp'],
                'eduPersonTargetID' => ['33333333-3333-3333-3333-333333333333'],
                'sn' => ['Backupcode'],
                'givenName' => ['Has'],
                'mail' => ['has_backupcode@example.com'],
                'employeeNumber' => ['33333'],
                'cn' => ['HAS_BACKUPCODE'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '7',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has totp
            'has_totp:a' => [
                'eduPersonPrincipalName' => ['HAS_TOTP@mfaidp'],
                'eduPersonTargetID' => ['44444444-4444-4444-4444-444444444444'],
                'sn' => ['TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_totp@example.com'],
                'employeeNumber' => ['44444'],
                'cn' => ['HAS_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '2',
                            'type' => 'totp',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has totp and a manager email
            'has_totp_and_mgr:a' => [
                'eduPersonPrincipalName' => ['HAS_TOTP@mfaidp'],
                'eduPersonTargetID' => ['44444444-4444-4444-4444-444444444444'],
                'sn' => ['TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_totp@example.com'],
                'employeeNumber' => ['44444'],
                'cn' => ['HAS_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '2',
                            'type' => 'totp',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has a webauthn
            'has_webauthn:a' => [
                'eduPersonPrincipalName' => ['HAS_WEBAUTHN@mfaidp'],
                'eduPersonTargetID' => ['55555555-5555-5555-5555-555555555555'],
                'sn' => ['WebAuthn'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn@example.com'],
                'employeeNumber' => ['55555'],
                'cn' => ['HAS_WEBAUTHN'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '3',
                            'type' => 'webauthn',
                            'label' => 'Blue security key (work)',
                            'created_utc' => '2017-10-24T20:40:57Z',
                            'last_used_utc' => null,
                            'data' => [
                                // Response from "POST /webauthn/login" MFA API call.
                                "id" => 88,
                                "label" => "My Webauthn Key",
                                "last_used_utc" => null,
                                "created_utc" => "2022-12-15 19:42:37",
                                "publicKey" => [
                                    "challenge" => "xxxxxxx",
                                ],
                            ],
                        ],
                    ]
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has webauthn and a manager email
            'has_webauthn_and_mgr:a' => [
                'eduPersonPrincipalName' => ['HAS_WEBAUTHN@mfaidp'],
                'eduPersonTargetID' => ['55555555-5555-5555-5555-555555555555'],
                'sn' => ['WebAuthn'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn@example.com'],
                'employeeNumber' => ['55555'],
                'cn' => ['HAS_WEBAUTHN'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '3',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ]
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has all forms of mfa
            'has_all:a' => [
                'eduPersonPrincipalName' => ['has_all@mfaidp'],
                'eduPersonTargetID' => ['77777777-7777-7777-7777-777777777777'],
                'sn' => ['All'],
                'givenName' => ['Has'],
                'mail' => ['has_all@example.com'],
                'employeeNumber' => ['777777'],
                'cn' => ['HAS_ALL'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '1',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 8,
                            ],
                        ],
                        [
                            'id' => '2',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '3',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who has a rate-limited mfa
            'has_rate_limited_mfa:a' => [
                'eduPersonPrincipalName' => ['HAS_RATE_LIMITED_MFA@mfaidp'],
                'eduPersonTargetID' => ['88888888-8888-8888-8888-888888888888'],
                'sn' => ['Rate-Limited MFA'],
                'givenName' => ['Has'],
                'mail' => ['has_rate_limited_mfa@example.com'],
                'employeeNumber' => ['88888'],
                'cn' => ['HAS_RATE_LIMITED_MFA'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => 987, //FakeIdBrokerClient::RATE_LIMITED_MFA_ID,
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 5,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has 4 backup codes
            'has_4_backupcodes:a' => [
                'eduPersonPrincipalName' => ['HAS_4_BACKUPCODES@mfaidp'],
                'eduPersonTargetID' => ['99999999-9999-9999-9999-999999999999'],
                'sn' => ['Backupcodes'],
                'givenName' => ['Has 4'],
                'mail' => ['has_4_backupcodes@example.com'],
                'employeeNumber' => ['99999'],
                'cn' => ['HAS_4_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '90',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 4,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has 1 backup code remaining
            'has_1_backupcode_only:a' => [
                'eduPersonPrincipalName' => ['HAS_1_BACKUPCODE_ONLY@mfaidp'],
                'eduPersonTargetID' => ['00000010-0010-0010-0010-000000000010'],
                'sn' => ['Only, And No Other MFA'],
                'givenName' => ['Has 1 Backupcode'],
                'mail' => ['has_1_backupcode_only@example.com'],
                'employeeNumber' => ['00010'],
                'cn' => ['HAS_1_BACKUPCODE_ONLY'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '100',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 1,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has one backup code plus another option
            'has_1_backupcode_plus:a' => [
                'eduPersonPrincipalName' => ['HAS_1_BACKUPCODE_PLUS@mfaidp'],
                'eduPersonTargetID' => ['00000011-0011-0011-0011-000000000011'],
                'sn' => ['Plus Other MFA'],
                'givenName' => ['Has 1 Backupcode'],
                'mail' => ['has_1_backupcode_plus@example.com'],
                'employeeNumber' => ['00011'],
                'cn' => ['HAS_1_BACKUPCODE_PLUS'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '110',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 1,
                            ],
                        ],
                        [
                            'id' => '112',
                            'type' => 'totp',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has webauthn and totp
            'has_webauthn_totp:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_totp@mfaidp'],
                'eduPersonTargetID' => ['00000012-0012-0012-0012-000000000012'],
                'sn' => ['WebAuthn And TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_totp@example.com'],
                'employeeNumber' => ['00012'],
                'cn' => ['HAS_WEBAUTHN_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '120',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '121',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has webauthn, totp and a manager email
            'has_webauthn_totp_and_mgr:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_totp@mfaidp'],
                'eduPersonTargetID' => ['00000012-0012-0012-0012-000000000012'],
                'sn' => ['WebAuthn And TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_totp@example.com'],
                'employeeNumber' => ['00012'],
                'cn' => ['HAS_WEBAUTHN_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '120',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '121',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has webauthn and backup codes
            'has_webauthn_backupcodes:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000013-0013-0013-0013-000000000013'],
                'sn' => ['WebAuthn And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_backupcodes@example.com'],
                'employeeNumber' => ['00013'],
                'cn' => ['HAS_WEBAUTHN_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '130',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '131',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has backup codes and a manager email
            'has_webauthn_backupcodes_and_mgr:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000013-0013-0013-0013-000000000013'],
                'sn' => ['WebAuthn And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_backupcodes@example.com'],
                'employeeNumber' => ['00013'],
                'cn' => ['HAS_WEBAUTHN_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '130',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '131',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has totp and backup codes
            'has_webauthn_totp_backupcodes:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_totp_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000014-0014-0014-0014-000000000014'],
                'sn' => ['WebAuthn, TOTP, And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_totp_backupcodes@example.com'],
                'employeeNumber' => ['00014'],
                'cn' => ['HAS_WEBAUTHN_TOTP_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '140',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '141',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '142',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has backup codes, totp, and a manager email
            'has_webauthn_totp_backupcodes_and_mgr:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_totp_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000014-0014-0014-0014-000000000014'],
                'sn' => ['WebAuthn, TOTP, And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_totp_backupcodes@example.com'],
                'employeeNumber' => ['00014'],
                'cn' => ['HAS_WEBAUTHN_TOTP_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '140',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '141',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '142',
                            'type' => 'webauthn',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has manager code, webauthn, and a more-recently used totp
            'has_mgr_code_webauthn_and_more_recently_used_totp:a' => [
                'eduPersonPrincipalName' => ['has_mgr_code_webauthn_and_more_recently_used_totp@mfaidp'],
                'eduPersonTargetID' => ['00000114-0014-0014-0014-000000000014'],
                'sn' => ['Manager Code, WebAuthn, More Recently Used TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_mgr_code_webauthn_and_more_recently_used_totp@example.com'],
                'employeeNumber' => ['00114'],
                'cn' => ['HAS_MGR_CODE_WEBAUTHN_AND_MORE_RECENTLY_USED_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '1140',
                            'type' => 'totp',
                            'last_used_utc' => '2011-01-01T00:00:00Z',
                            'data' => '',
                        ],
                        [
                            'id' => '1141',
                            'type' => 'webauthn',
                            'last_used_utc' => '2000-01-01T00:00:00Z',
                            'data' => '',
                        ],
                        [
                            'id' => '1142',
                            'type' => 'manager',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has webauthn and more recently used totp
            'has_webauthn_and_more_recently_used_totp:a' => [
                'eduPersonPrincipalName' => ['has_webauthn_and_more_recently_used_totp@mfaidp'],
                'eduPersonTargetID' => ['00000214-0014-0014-0014-000000000014'],
                'sn' => ['WebAuthn And More Recently Used TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_webauthn_and_more_recently_used_totp@example.com'],
                'employeeNumber' => ['00214'],
                'cn' => ['HAS_WEBAUTHN_AND_MORE_RECENTLY_USED_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '2140',
                            'type' => 'totp',
                            'last_used_utc' => '2011-01-01T00:00:00Z',
                            'data' => '',
                        ],
                        [
                            'id' => '2141',
                            'type' => 'webauthn',
                            'last_used_utc' => '2000-01-01T00:00:00Z',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has totp and more recently used webauthn
            'has_totp_and_more_recently_used_webauthn:a' => [
                'eduPersonPrincipalName' => ['has_totp_and_more_recently_used_webauthn@mfaidp'],
                'eduPersonTargetID' => ['00000314-0014-0014-0014-000000000014'],
                'sn' => ['TOTP And More Recently Used Webauthn'],
                'givenName' => ['Has'],
                'mail' => ['has_totp_and_more_recently_used_webauthn@example.com'],
                'employeeNumber' => ['00314'],
                'cn' => ['HAS_TOTP_AND_MORE_RECENTLY_USED_WEBAUTHN'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '3140',
                            'type' => 'totp',
                            'last_used_utc' => '2000-01-01T00:00:00Z',
                            'data' => '',
                        ],
                        [
                            'id' => '3141',
                            'type' => 'webauthn',
                            'last_used_utc' => '2011-01-01T00:00:00Z',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has totp and more recently-used backup code
            'has_totp_and_more_recently_used_backup_code:a' => [
                'eduPersonPrincipalName' => ['has_totp_and_more_recently_used_backup_code@mfaidp'],
                'eduPersonTargetID' => ['00000414-0014-0014-0014-000000000014'],
                'sn' => ['TOTP And More Recently Used Backup Code'],
                'givenName' => ['Has'],
                'mail' => ['has_totp_and_more_recently_used_backup_code@example.com'],
                'employeeNumber' => ['00414'],
                'cn' => ['HAS_TOTP_AND_MORE_RECENTLY_USED_BACKUP_CODE'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '4140',
                            'type' => 'totp',
                            'last_used_utc' => '2000-01-01T00:00:00Z',
                            'data' => '',
                        ],
                        [
                            'id' => '4141',
                            'type' => 'backupcode',
                            'last_used_utc' => '2011-01-01T00:00:00Z',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has backup code and a more recently used totp
            'has_backup_code_and_more_recently_used_totp:a' => [
                'eduPersonPrincipalName' => ['has_backup_code_and_more_recently_used_totp@mfaidp'],
                'eduPersonTargetID' => ['00000514-0014-0014-0014-000000000014'],
                'sn' => ['Backup Code And More Recently Used TOTP'],
                'givenName' => ['Has'],
                'mail' => ['has_backup_code_and_more_recently_used_totp@example.com'],
                'employeeNumber' => ['00514'],
                'cn' => ['HAS_BACKUP_CODE_AND_MORE_RECENTLY_USED_TOTP'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '5140',
                            'type' => 'backupcode',
                            'last_used_utc' => '2000-01-01T00:00:00Z',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '5141',
                            'type' => 'totp',
                            'last_used_utc' => '2011-01-01T00:00:00Z',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has totp and backup codes
            'has_totp_backupcodes:a' => [
                'eduPersonPrincipalName' => ['has_totp_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000015-0015-0015-0015-000000000015'],
                'sn' => ['TOTP And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_totp_backupcodes@example.com'],
                'employeeNumber' => ['00015'],
                'cn' => ['HAS_TOTP_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '150',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '151',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
            ],

            // mfa test user who requires mfa and has totp, backup codes, and manager email
            'has_totp_backupcodes_and_mgr:a' => [
                'eduPersonPrincipalName' => ['has_totp_backupcodes@mfaidp'],
                'eduPersonTargetID' => ['00000015-0015-0015-0015-000000000015'],
                'sn' => ['TOTP And Backup Codes'],
                'givenName' => ['Has'],
                'mail' => ['has_totp_backupcodes@example.com'],
                'employeeNumber' => ['00015'],
                'cn' => ['HAS_TOTP_BACKUPCODES'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '150',
                            'type' => 'totp',
                            'data' => '',
                        ],
                        [
                            'id' => '151',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // mfa test user who requires mfa and has backup codes and manager code
            'has_mgr_code:a' => [
                'eduPersonPrincipalName' => ['has_mgr_code@mfaidp'],
                'eduPersonTargetID' => ['00000015-0015-0015-0015-000000000015'],
                'sn' => ['Manager Code'],
                'givenName' => ['Has'],
                'mail' => ['has_mgr_code@example.com'],
                'employeeNumber' => ['00015'],
                'cn' => ['HAS_MGR_CODE'],
                'schacExpiryDate' => [
                    gmdate('YmdHis\Z', strtotime('+6 months')),
                ],
                'profile_review' => ['no'],
                'mfa' => [
                    'prompt' => 'yes',
                    'add' => 'no',
                    'options' => [
                        [
                            'id' => '151',
                            'type' => 'backupcode',
                            'data' => [
                                'count' => 10,
                            ],
                        ],
                        [
                            'id' => '152',
                            'type' => 'manager',
                            'data' => '',
                        ],
                    ],
                ],
                'method' => [
                    'add' => 'no',
                    'options' => [],
                ],
                'manager_email' => ['manager@example.com'],
            ],

            // sildisco test user
            'sildisco_idp1:sildisco_password' => [
                'eduPersonPrincipalName' => ['sildisco@idp1'],
                'eduPersonTargetID' => ['57de1930-c5d2-4f6f-9318-d85a939c45d8'],
                'sn' => ['IDP1'],
                'givenName' => ['SilDisco'],
                'mail' => ['sildisco_idp1@example.com'],
                'employeeNumber' => ['50001'],
                'cn' => ['SILDISCO_IDP1'],
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

        ],
    ]
];
