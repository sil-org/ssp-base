Four SPs, a hub (a combined IdP and SP) and three IdPs get spun up by docker compose.  In order for this to work, you will need to edit your hosts file to include entries for the following domains ...
* ssp-sp1.local
* ssp-sp2.local
* ssp-sp3.local
* pwmanager.local
* ssp-hub.local
* ssp-idp1.local
* ssp-idp2.local
* ssp-idp3.local

The ./development folder holds various files needed by these containers.  It's the ssp-hub.local container which is the focus and serves as the SimpleSAMLphp hub.

### Who should see what?
* `ssp-sp1.local` should be able to see and authenticate through both `ssp-idp1.local` and `ssp-idp2.local`
* `ssp-sp2.local` should only be able to see and authenticate through `ssp-idp2.local`
* `ssp-sp3.local` should only be able to see and authenticate through `ssp-idp1.local`

If a session authenticated through one of the IdP's that is not permitted for a certain SP, then the hub should force that SP to re-authenticate against the right IdP.
