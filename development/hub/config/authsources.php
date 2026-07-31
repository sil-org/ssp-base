<?php

$config = [

    'hub-discovery' => [
        'saml:SP',

        // The entity ID of this SP.
        // Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.

        'entityID' => 'ssp-hub.local',

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => 'https://ssp-hub.local/module.php/sildisco/disco.php',
        'privatekey' => 'saml.pem',
    ],

    // This is a authentication source which handles admin authentication.
    'admin' => [
        // The default is to use core:AdminPassword, but it can be replaced with
        // any authentication source.

        'core:AdminPassword',
    ],

];
