# Automated Testing

This is done through behat acceptance tests

Once your containers are up, in your VM run ...

`> docker compose run --rm test /data/run-integration-tests.sh`

Or, if you need to run just one of the tests, run ...

`> docker compose run --rm test bash`

then

`$ behat features/mfa.feature:7`

The tests are found in `/features`.  They are similar to the manual tests listed below.

# Manual Testing

See [Local Testing](../README.md#local-testing) for instructions to set up your local development environment.

## Main SP authenticates through Main Idp. Third SP is also authenticated. Second SP must re-authenticate.
### Ensure main SP goes to discovery page and can login through the main IdP
* Kill all your cookies for ssp\*
* Browse to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-hub.local/module.php/sildisco/disco.php?entityID=ssp-hub.local&...
* Select IdP 1
* This should redirect to https://ssp-idp1.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp1 using "sildisco_password" as the password (without the quotation marks).
* This should return you to the main SP at https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure third SP is also authenticated
* Browse to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure second SP is forced to authenticate
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp2.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp2 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

### Ensure third SP is still authenticated
* Browse to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

## Second SP authenticates through Second Idp. Main SP is forced to discovery page but is also authenticated. Third SP must re-authenticate.
### Ensure second SP can login through the second IdP
* Kill all your cookies for ssp\*
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp2.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp2 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

### Ensure main SP goes to discovery page but is authenticated
* Browse to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-hub.local/module.php/sildisco/disco.php?entityID=ssp-hub.local&...
* Select IdP 2
* This should return you to the main SP at https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

### Ensure third SP is forced to authenticate
* Browse to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp1.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp1 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

## Third SP authenticates through Main Idp. Main SP is forced to discovery page but is also authenticated. Second SP must re-authenticate.
### Ensure third SP can login through the main IdP
* Kill all your cookies for ssp\*
* Browse to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp1.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp1 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure main SP goes to discovery page but is authenticated
* Browse to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-hub.local/module.php/sildisco/disco.php?entityID=ssp-hub.local&...
* Select IdP 1
* This should get you to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure second SP is forced to authenticate
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp2.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp2 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

## Main SP authenticates through Second Idp. Second SP is also authenticated. Third SP must re-authenticate.
### Ensure main SP goes to discovery page and can login through the second IdP
* Kill all your cookies for ssp\*
* Browse to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-hub.local/module.php/sildisco/disco.php?entityID=ssp-hub.local&...
* Select IdP 2
* This should redirect to https://ssp-idp2.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp2 using "sildisco_password" as the password (without the quotation marks).
* This should return you to the main SP at https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

### Ensure second SP is also authenticated
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure third SP is forced to authenticate
* Browse to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp1.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp1 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

### Ensure second SP is still authenticated
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.

## Second SP authenticates through Second Idp. Main SP is forced to discovery page, chooses main IdP and must authenticate.
### Ensure second SP can login through the second IdP
* Kill all your cookies for ssp\*
* Browse to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-idp2.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp2 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp2.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes (but there are none).

### Ensure main SP goes to discovery page and must authenticate when choosing the main Idp
* Browse to https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/
* This should redirect to https://ssp-hub.local/module.php/sildisco/disco.php?entityID=ssp-hub.local&...
* Select IdP 1
* This should redirect to https://ssp-idp1.local/module.php/core/loginuserpass.php?AuthState=...
* Login as sildisco_idp1 using "sildisco_password" as the password (without the quotation marks).
* This should get you to https://ssp-sp3.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/ and show your saml attributes.
