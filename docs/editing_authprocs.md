The sildisco module includes a few Auth Procs that can be called from the `config.php` file or **SP or IdP metadata**.

### TagGroup.php

Grabs the values of the `urn:oid:2.5.4.31` (member of) attribute and prepends them with `idp|<the_idp's_name>|`.
The idp's name value is taken from the saml20-idp-remote.php file.  In particular, if the IdP's metadata entry includes a `'IDPNamespace'` value, that is used. Otherwise, if it includes a `'name'` value, that is used. Otherwise, it uses the entity id of the IdP.

### AddIdp2NameId.php

Grabs the value of the saml:sp:NameID and appends `@<IDPNamespace>` to it.
The IdP's metadata needs to include an `'IDPNamespace'` entry with a string value that is alphanumeric with hyphens and underscores.

In order for this to work, the SP needs to include a line in its authsources.php file in the Hub's entry ...

```
    'NameIDPolicy' => [
        'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        'AllowCreate' => true,
    ],
```

In addition, the IDP's sp-remote metadata stanza for the Hub needs to include ...

` 'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',`

### TrackIdps.php

Creates and/or appends to a session value ("sildisco:authentication", "authenticated_idps") the **entity id** of the latest **IdP** to be used for authentication.

### Login audit logging (moved out of sildisco)

Logging each successful login (common name, eduPersonPrincipalName, employee number, IdP,
SP, time) to an AWS DynamoDB table used to be `sildisco:LogUser`, a fourth sildisco AuthProc.
That class has been removed: it ran on the Hub, so its `SP` field only ever saw the Hub's own
SP-facing entity ID unless `saml:sp:State` happened to be populated.

This is now `loginfinalizer:LogToDynamo` (in the `loginfinalizer` module, wired globally in
`config.php`'s `authproc.idp` array rather than per-metadata, so it runs on every IdP without
each downstream deployment having to add it). It runs on the authoritative IdP and resolves
the true originating SP correctly whether a Hub is in front of it or not (see
`SpEntityId::resolve()`). Configure it via the `DYNAMO_REGION`/`DYNAMO_LOG_TABLE`/`DYNAMO_ENDPOINT`
environment variables (see `local.env.dist`); it no-ops if they're unset. `DynamoEndpoint`
(via `DYNAMO_ENDPOINT`) is only needed locally, e.g. `http://dynamo:8000`.

Ensure the `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` environment variables are set as
shown in the `local.env.dist` file.
