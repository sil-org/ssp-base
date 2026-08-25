<?php

use Sil\PhpEnv\Env;
use Sil\Psr3Adapters\Psr3StdOutLogger;
use SimpleSAML\Module\silauth\Auth\Source\config\ConfigManager;
use SimpleSAML\Module\silauth\Auth\Source\system\System;

try {
    header('Content-Type: text/plain');

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
    $logger = new Psr3StdOutLogger();
    $system = new System($logger);
    $system->reportStatus();

} catch (Throwable $t) {

    echo sprintf(
        '%s (%s)',
        $t->getMessage(),
        $t->getCode()
    );
    http_response_code(500);
}
