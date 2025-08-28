<?php

use Sil\PhpEnv\Env;
use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;

$dbAttributes = [];
$caFile = Env::get('DB_CA_FILE_PATH');
if (file_exists($caFile)) {
    $dbAttributes = [
        PDO::MYSQL_ATTR_SSL_CA => $caFile,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => 1,
    ];
}

ConfigManager::initializeYii2WebApp(['components' => ['db' => [
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s',
        Env::get('MYSQL_HOST'),
        Env::get('MYSQL_DATABASE')
    ),
    'username' => Env::get('MYSQL_USER'),
    'password' => Env::get('MYSQL_PASSWORD'),
    'attributes' => $dbAttributes,
]]]);
