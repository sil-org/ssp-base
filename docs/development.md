Four SPs, a hub (a combined IdP and SP) and three IdPs get spun up by docker compose.  In order for this to work, you will need to edit your hosts file to include entries for the following domains ...
* ssp-sp1.local   # to be used with port 8081
* ssp-sp2.local   # to be used with port 8082
* ssp-sp3.local   # to be used with port 8083
* pwmanager.local
* ssp-hub.local
* ssp-idp1.local  # to be used with port 8085
* ssp-idp2.local  # to be used with port 8086
* ssp-idp3.local  # to be used with port 8087

The ./development folder holds various files needed by these containers.  It's the ssp-hub.local container which is the focus and serves as the SimpleSAMLphp hub.

### Who should see what?
* `ssp-sp1.local` should be able to see and authenticate through both `ssp-idp1.local` and `ssp-idp2.local`
* `ssp-sp2.local` should only be able to see and authenticate through `ssp-idp2.local`
* `ssp-sp3.local` should only be able to see and authenticate through `ssp-idp1.local`

If a session authenticated through one of the IdP's that is not permitted for a certain SP, then the hub should force that SP to re-authenticate against the right IdP.

### Why idp1/idp2/idp4 listen on two ports

Each of these containers is reached two different ways: automated/headless tests run
inside the Docker network and hit the container's bare hostname on its internal port 80,
while a real browser on your machine hits it via the port published in `compose.yaml`
(e.g. `ssp-idp1.local:8085`). Docker's `8085:80` port mapping only translates traffic
coming from the host machine -- it doesn't change what port Apache itself thinks it's
listening on, so `$_SERVER['SERVER_PORT']` was always `80` regardless of which path a
request came in on. Since SimpleSAMLphp's relative `baseurlpath` derives each hosted
IdP's entity ID from `SERVER_PORT`, this meant a real browser hitting `:8085` still got
issued the bare (no-port) entity ID -- silently mismatching what the Hub's SP-remote
metadata expected for the custom-port flow ("Issuer mismatch" errors).

The fix: each of these containers' `development/<idp>-local/apache/extra-listen.conf` is
volume-mounted to add a second Apache `Listen`/`VirtualHost` on the published port, and
`compose.yaml` publishes that port as `<port>:<port>` instead of `<port>:80`, so Apache
genuinely listens on, and reports, the same port a browser connects to. With
`SERVER_PORT` now accurate for both paths, each container's `saml20-idp-hosted.php`
computes its entity ID from `$_SERVER['SERVER_PORT']` directly, rather than needing two
hardcoded metadata blocks selected by a `'host'` match.

idp2 also needs `TRUSTED_URL_DOMAINS` set: the `silauth` module's own status check
requires either that or a fixed (non-relative) `baseurlpath`, as a guard against
Host-header-based URL spoofing -- idp1 and idp4 already had it for other reasons.

idp3 doesn't need any of this: it has no automated-test traffic and only ever answers on
its one port, so there's no entity-ID ambiguity to resolve.
