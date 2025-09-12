The hub will need its certs, `config.php` and `authsources.php` files as a normal simplesamlphp installation. Examples of these can be found in the `./development/hub` folder. (Note the `discoURL` entry in the `authsources.php` file.)

### Metadata files
The hub should use the `saml20-*-remote.php`  files from [ssp-base](https://github.com/sil-org/ssp-base) in `/data/vendor/simplesamlphp/simplesamlphp/metadata/`.  These pull in metadata from all the files named `idp-*.php` and `sp-*.php` respectively, including those in sub-folders.

In order for forced re-authentication to be limited only to situations which warrant it, the `saml20-idp-hosted.php` file should include an authproc as such ...
>  [
>     'class' =>'sildisco:TrackIdps',
>  ]

#### IDP Remote metadata

##### IDPNamespace
Each metadata stanza should include an `IDPNamespace` entry that includes no special characters.  This is intended for namespacing the `NameId` value in the Auth Proc `AddIdp2NameId.php`.
It is also used by the `TagGroup.php` Auth Proc to convert group names into the form ...

`idp|<IDPNamespace>|<group name>`.

##### SPList
In order to limit access to an IdP to only certain SP's, add an `'SPList'` array entry to the metadata for the IdP.  The values of this array should match the `entityid` values from the `sp-remote.php` metadata.

### Forced IdP discovery
The `dockerbuild/ssp-overrides/sp-php.patch` file ensures that if an SP is allowed to access more than one IdP, then the user will be forced back to the IdP discovery page, even if they are already authenticated through one of those IdP's.

The reason for this is to ensure that the user has a chance to decide which of their identities is used for that SP.
