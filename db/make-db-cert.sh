#!/usr/bin/env sh

mkdir -p db/certs
cd db/certs || exit

# Generate CA
openssl genrsa 2048 > ca-key.pem
openssl req -new -x509 -nodes -days 3650 -key ca-key.pem -out ca.pem -subj "/CN=My MariaDB CA"


# Create key and cert for 'db' container
openssl req -newkey rsa:2048 -days 3650 -nodes -keyout db-key-pkcs8.pem -out db-req.pem -subj "/CN=db"
openssl x509 -req -in db-req.pem -days 3650 -CA ca.pem -CAkey ca-key.pem -set_serial 01 -out db-cert.pem

# Convert to PKCS#1 format
openssl rsa -in db-key-pkcs8.pem -out db-key.pem -traditional

# Remove intermediate files
rm ca-key.pem db-key-pkcs8.pem db-req.pem

# Set permissions
chmod 644 ./*.pem

ca=$(base64 ca.pem --wrap 0)
echo "SSL_CA_BASE64=$ca" > test.env

cd ../..
