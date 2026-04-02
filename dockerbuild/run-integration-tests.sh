#!/usr/bin/env bash

# echo script commands to stdout
set -x

# exit if any command fails
set -e

cd /data

whenavail "ssp-hub.local"  80 15 echo Hub ready
whenavail "ssp-idp1.local" 80 20 echo IDP 1 ready
whenavail "ssp-sp1.local"  80 5 echo SP 1 ready

./vendor/bin/behat \
    --no-interaction \
    --stop-on-failure
