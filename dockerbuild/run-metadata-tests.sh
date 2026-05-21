#!/usr/bin/env bash

# echo script commands to stdout
set -x

# exit if any command fails
set -e

cd /data
composer install

./vendor/bin/phpunit --display-all-issues tests/MetadataTest.php
