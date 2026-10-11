#!/usr/bin/env bash

# echo script commands to stdout
set -x

# exit if any command fails, including the left-hand side of a pipe
set -eo pipefail

# Runs the PHPUnit suite for a custom module's tests/ directory, failing loudly
# (rather than relying on phpunit's own "not found" handling) if that module
# isn't actually present where expected -- e.g. because a new module is
# missing its bind mount in compose.yaml, or the image wasn't rebuilt after
# the module was added.
run_module_tests() {
    local module_tests_dir="vendor/simplesamlphp/simplesamlphp/modules/$1/tests"

    if [[ ! -d "$module_tests_dir" ]]; then
        echo "Expected module tests directory not found: $module_tests_dir" >&2
        echo "(Check that modules/$1 is bind-mounted/copied into this container.)" >&2
        exit 1
    fi

    ./vendor/bin/phpunit --display-all-issues "$module_tests_dir/"
}

/data/run-metadata-tests.sh

./vendor/bin/phpunit --display-all-issues tests/AnnouncementTest.php
./vendor/bin/phpunit --display-all-issues tests/TwigTemplatesTest.php
run_module_tests sildisco
run_module_tests mfa
run_module_tests loginfinalizer

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
