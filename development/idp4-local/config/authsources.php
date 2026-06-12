<?php

$config = [

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

    /**
     * Example users for testing. Users who are here AND in another
     * authsources.php file (e.g. IDP 1's) should have matching attributes.
     */
    'example-userpass' => [
        'exampleauth:UserPass',

        'users' => [

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

            /**
             * A user with MFA configured (in this case, backup codes) and a
             * manager, but no recovery contacts (in the recovery-contacts mock
             * API).
             */
            'has_backupcode_mgr_no_recovery_contacts:a' => [
                'eduPersonPrincipalName' => ['has_backupcode_mgr_no_recovery_contacts@mfaidp'],
                'eduPersonTargetID' => ['2bf8e2c9-d62c-4afa-842e-350c86d5bded'],
                'sn' => ['No Recovery Contacts'],
                'givenName' => ['Has Backupcode Mgr'],
                'mail' => ['has_backupcode_mgr_no_recovery_contacts@example.com'],
                'employeeNumber' => ['33333'],
                'cn' => ['has_backupcode_mgr_no_recovery_contacts'],
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

            /**
             * A user with MFA configured (in this case, backup codes) a
             * manager, and a recovery contact (in the recovery-contacts mock
             * API).
             */
            'has_backupcode_mgr_recovery_contact:a' => [
                'eduPersonPrincipalName' => ['has_backupcode_mgr_recovery_contact@mfaidp'],
                'eduPersonTargetID' => ['4927f678-6d1c-423f-a094-46c6b56f7bc5'], // Make it unique
                'sn' => ['Recovery Contact'],
                'givenName' => ['Has Backupcode Mgr'],
                'mail' => ['has_backupcode_mgr_recovery_contact@example.com'],
                'employeeNumber' => ['49276'], // Make it unique
                'cn' => ['has_backupcode_mgr_recovery_contact'],
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

            /**
             * A user with MFA configured (in this case, backup codes) and a
             * manager, but for whom the recovery-contacts API will return an
             * error.
             */
            'has_backupcode_mgr_recovery_contact_error:a' => [
                'eduPersonPrincipalName' => ['has_backupcode_mgr_recovery_contact_error@mfaidp'],
                'eduPersonTargetID' => ['b04a4461-0560-46c5-a6de-058f7f3a1b13'], // Make it unique
                'sn' => ['Recovery Contact Error'],
                'givenName' => ['Has Backupcode Mgr'],
                'mail' => ['has_backupcode_mgr_recovery_contact_error@example.com'],
                'employeeNumber' => ['44461'], // Make it unique
                'cn' => ['has_backupcode_mgr_recovery_contact_error'],
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
        ],
    ]
];
