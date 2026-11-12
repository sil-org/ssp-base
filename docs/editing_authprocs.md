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

### LogUser.php

Logs information (common name, eduPrincipalPersonalName, employee number, IdP, SP, time) about each successful login to an AWS Dynamodb table.
```
            97 => [
                'class' =>'sildisco:LogUser',
                'DynamoRegion' => 'us-east-1',
                'DynamoLogTable' => 'sildisco_prod_user-log',
            ],
```
The following config is not needed on AWS, but it is needed locally
'DynamoEndpoint' ex. http://dynamo:8000

Ensure the AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY environment variables are set as shown in the local.env.dist file.

**Note:** `LogUser.php` runs on the Hub, so its `SP` field only sees the Hub's own SP-facing
entity ID unless `saml:sp:State` happens to be populated. `loginfinalizer:LogToDynamo` (in the
`loginfinalizer` module, wired globally in `config.php` rather than per-metadata) does the same
job but runs on the authoritative IdP and resolves the true originating SP correctly whether a
Hub is in front of it or not (see `SpEntityId::resolve()`). When the login came through a Hub,
it also logs that Hub's entity ID in a `Hub` field (see `SpEntityId::resolveHub()`); the field is
omitted for direct SP-to-IdP logins. It's configured via the
`DYNAMO_REGION`/`DYNAMO_LOG_TABLE`/`DYNAMO_ENDPOINT` env vars (see local.env.dist) rather than
per-metadata config, and no-ops if they're unset. `sildisco:LogUser` is still present for now;
a follow-up will retire it once `LogToDynamo` has been validated in practice.

**Note:** `loginfinalizer:MarkLastLogin` (900) and `loginfinalizer:LogToDynamo` (901) run last, after
`core:AttributeLimit` (50) has removed any attributes the SP isn't allowed to receive. So they read the
user's attributes from a copy that `loginfinalizer:SnapshotAttributes` saves at priority 1, before
anything is limited away. Without it, a direct login to an SP whose metadata limits its `attributes`
(e.g. to just `mail`) would skip the last-login update and log no user. If you add a filter that removes
attributes, give it a priority above 1.
