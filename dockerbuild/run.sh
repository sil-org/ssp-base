#!/usr/bin/env bash

echo "starting ssp-base version $GITHUB_REF_NAME"

# echo script commands to stdout
set -x

# exit if any command fails
set -e

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

SSL_DIR=/data/cert
mkdir -p "$SSL_DIR"

CRT="$SSL_DIR/server.pem"
KEY="$SSL_DIR/server.key"

if [ ! -f "$CRT" ] || [ ! -f "$KEY" ]; then
  echo "Generating self-signed certificate..."
  openssl req -x509 -newkey rsa:4096 -keyout "$KEY" -out "$CRT" -sha256 -days 3650 -nodes -subj "/CN=localhost"
fi

if [[ $PARAMETER_STORE_PATH ]]; then
  # exec so Apache replaces this shell as PID 1 and receives the container's stop signal
  exec config-shim -v --path $PARAMETER_STORE_PATH apache2-foreground
elif [[ $APP_ID ]]; then
  exec config-shim --app $APP_ID --config $CONFIG_ID --env $ENV_ID apache2-foreground
else
  exec apache2-foreground
fi
