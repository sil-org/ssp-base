
# Testing the Material Module theme

## Setup

See [Local Testing](../README.md#local-testing) for instructions to set up your local development environment.

## Hub page

1.  Goto [Hub 1](https://ssp-hub.local/module.php/saml/sp/login/hub-discovery?ReturnTo=/)

## Error page

1.  Goto [Hub 1](https://ssp-hub.local)
1.  Click **Federation** tab
1.  Click either **Show metadata** link
1.  Login as hub administrator: `username=`**admin** `password=`**abc123**

## Logout page

1.  Goto [Hub 1](https://ssp-hub.local)
1.  Click **Authentication** tab
1.  Click **Test configured authentication sources**
1.  Click **admin**
1.  Login as hub administrator: `username=`**admin** `password=`**abc123**
1.  Click **Logout**

## Login page

### Without theme in place

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp1** (first one)
1.  login page should **NOT** have material design

### With theme in place

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp2** (second one)
1.  login page **SHOULD** have material design

## Forgot password functionality

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp2** (second one)
1.  Forgot password link should be visible

## Helpful links functionality

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Help link should be visible under login form
1.  Profile link should be visible under login form

## Expiry functionality

### About to expire page (expires in one day)

_Note:  This nag only works once since choosing later will simply set the nag date into the future a little.
If needed, use a new private/incognito browser window to retry.__

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp2** (second one)
1.  Login as an "about to expire" user: `username=`**next_day** `password=`**a**
1.  Click **Later**
1.  Click **Logout**

### About to expire page (expires in three days)

_Note:  This nag only works once since choosing later will simply set the nag date into the future a little.
If needed, use a new private/incognito browser window to retry.__

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp2** (second one)
1.  Login as an "about to expire" user: `username=`**near_future** `password=`**a**
1.  Click **Later**
1.  Click **Logout**

### Expired page

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp2** (second one)
1.  Login as an "expired" user: `username=`**already_past** `password=`**a**

## Multi-factor authentication (MFA) functionality

### Nag about missing MFA setup

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as an "unprotected" user: `username=`**nag_for_mfa** `password=`**a**
1.  The "learn more" link should be visible
1.  Click **Enable**
1.  Click your browser's back button
1.  Click **Remind me later**
1.  Click **Logout**

### Nag about missing password recovery methods

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a user without any methods: `username=`**nag_for_method** `password=`**a**
1.  Enter one of the following codes to verify (`94923279, 82743523, 77802769, 01970541, 37771076`)
1.  Click **Add**
1.  Click your browser's back button
1.  Click **Remind me later**
1.  Click **Logout**

### Force MFA setup

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as an "unsafe" user: `username=`**must_set_up_mfa** `password=`**a**

### Backup code

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "backup code" user: `username=`**has_backupcode** `password=`**a**
1.  Enter one of the following codes to verify (`94923279, 82743523, 77802769, 01970541, 37771076`)
1.  Click **Logout**
1.  In order to see the "running low on codes" page, simply log back in and use another code.
1.  In order to see the "out of codes" page, simply log back in and out repeatedly until there are no more codes.

### TOTP code

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "totp" user: `username=`**has_totp** `password=`**a**
1.  You should see the form to enter a totp code. 
1.  Set up an app using this secret, `JVRXKYTMPBEVKXLS`
1.  Enter code from app to verify
1.  Click **Logout**

### Key (U2F)

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "u2f" user: `username=`**has_u2f** `password=`**a**
1.  Insert key and press
1.  Click **Logout**

### Key (WebAuthn)

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "webauthn" user: `username=`**has_webauthn** `password=`**a**
1.  Insert key and press
1.  Click **Logout**

### Multiple options

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "multiple option" user: `username=`**has_all** `password=`**a**
1.  Click **MORE OPTIONS**

### Multiple options (legacy, with U2F)

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "multiple option" user: `username=`**has_all_legacy** `password=`**a**
1.  Click **MORE OPTIONS**

### Manager rescue

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "multiple option" user: `username=`**has_all** `password=`**a**
1.  Click **MORE OPTIONS**
1.  Click the help option
1.  Choose **Send**

_NOTE: At this time, the correct code is not known and can't be tested locally (it's only available in an email to the manager)_

## Announcements functionality

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  The announcement should be displayed on the hub
1.  Click **idp1** (first one)
1.  The announcement should be displayed at the login screen

## SP name functionality

1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  The sp name should appear in the banner

## Profile review functionality
1.  Goto [SP 1](https://ssp-sp1.local/module.php/saml/sp/login/ssp-hub?ReturnTo=/)
1.  Click **idp4** (third one)
1.  Login as a "Review needed" user: `username=`**needs_review** `password=`**a**
1.  Enter one of the following printable codes to verify (`94923279, 82743523, 77802769, 01970541, 37771076`)
1.  Click the button to update the profile
1.  Click the button to continue
1.  Click **Logout**
