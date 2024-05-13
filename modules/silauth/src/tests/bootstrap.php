<?php

use Sil\PhpEnv\Env;
use Sil\SilAuth\config\ConfigManager;

ConfigManager::initializeYii2WebApp(['components' => ['db' => [
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s',
        Env::get('MYSQL_HOST'),
        Env::get('MYSQL_DATABASE')
    ),
    'username' => Env::get('MYSQL_USER'),
    'password' => Env::get('MYSQL_PASSWORD'),
]]]);
