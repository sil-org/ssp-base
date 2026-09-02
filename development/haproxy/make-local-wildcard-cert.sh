openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
  -keyout wildcard.crt.key \
  -out wildcard.crt \
  -config wildcard.cnf \
  -extensions req_ext
