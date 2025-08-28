#!/usr/bin/env bash

echo "starting ssp-base version $GITHUB_REF_NAME"

# echo script commands to stdout
set -x

# exit if any command fails
set -e

# establish a signal handler to catch the SIGTERM from a 'docker stop'
# reference: https://medium.com/@gchudnov/trapping-signals-in-docker-containers-7a57fdda7d86
term_handler() {
  apache2ctl stop
  exit 143; # 128 + 15 -- SIGTERM
}
trap 'kill ${!}; term_handler' SIGTERM

cd /data

# Send info about this image's O/S and PHP version to our logs.
cat /etc/*release | grep PRETTY
php -v | head -n 1

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

if [[ $PARAMETER_STORE_PATH ]]; then
  config-shim --path $PARAMETER_STORE_PATH apache2ctl -k start -D FOREGROUND
elif [[ $APP_ID ]]; then
  config-shim --app $APP_ID --config $CONFIG_ID --env $ENV_ID apache2ctl -k start -D FOREGROUND
else
  apache2ctl -k start -D FOREGROUND
fi

# endless loop with a wait is needed for the trap to work
while true
do
  tail -f /dev/null & wait ${!}
done
