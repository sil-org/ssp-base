<?php

use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;

$config = [
    // Use the SimpleSAML\Module\silauth\Auth\Source\SilAuth Auth Proc (Authentication Processing Filter)
    'silauth' => ConfigManager::getSspConfig(),
];
