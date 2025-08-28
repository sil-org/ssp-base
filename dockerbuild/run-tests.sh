#!/usr/bin/env bash

# echo script commands to stdout
set -x

# exit if any command fails
set -e

/data/run-metadata-tests.sh

./vendor/bin/phpunit -v tests/AnnouncementTest.php
./vendor/bin/phpunit -v vendor/simplesamlphp/simplesamlphp/modules/sildisco/tests/
./vendor/bin/phpunit -v vendor/simplesamlphp/simplesamlphp/modules/mfa/tests/

if [[ -n "$SSL_CA_BASE64" ]]; then
    # Decode the base64 and write to the file
    export DB_CA_FILE_PATH="/data/db_ca.pem"
    echo "$SSL_CA_BASE64" | base64 -d > "$DB_CA_FILE_PATH"
    if [[ $? -ne 0 || ! -s "$DB_CA_FILE_PATH" ]]; then
        echo "Failed to write database SSL certificate file: $DB_CA_FILE_PATH" >&2
        exit 1
    fi
    echo "Wrote cert to $DB_CA_FILE_PATH"
fi

/data/run-integration-tests.sh
