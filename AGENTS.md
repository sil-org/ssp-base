# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

ssp-base is SIL's base Docker image for SimpleSAMLphp, used to build IdP (Identity Provider) and Hub deployments. It bundles SimpleSAMLphp (`simplesamlphp/simplesamlphp ~2.5.0`) with several custom SimpleSAMLphp modules (under `modules/`) and PHP glue code. Downstream projects use this image as a base and layer in their own metadata (see "SimpleSAMLphp Metadata" in README.md).

The `development/` directory spins up a full local topology via Docker Compose: a Hub (combined IdP+SP), several IdPs, several SPs, and a password-manager mock, so that multi-IdP/multi-SP auth flows (the "SilDisco" discovery flow) can be tested end-to-end.

## Commands

All commands run through Docker Compose; there is no bare-metal PHP workflow. Local dev requires `/etc/hosts` entries (see README.md "Local testing") for `ssp-hub.local`, `ssp-idp1.local`..`ssp-idp4.local`, `ssp-sp1.local`..`ssp-sp3.local`, and `pwmanager.local`, plus `local.env` / `local.broker.env` copied from their `.dist` templates (with a GitHub personal access token in `COMPOSER_AUTH`).

```bash
make deps            # composer install (via the `composer` compose service)
make jsdeps           # npm install for JS deps + copies simplewebauthn browser bundle into modules/mfa/public
make hub              # clean + deps + jsdeps, then bring up just the hub
make dev              # bring up hub + idp1-4 + sp1-3 (full local topology)
make clean            # docker compose kill && rm -f
```

Testing:

```bash
make test             # metadata tests (tests/MetadataTest.php) on hub + idp1, plus the `test` service's PHPUnit suites
make test-integration  # behat acceptance tests (features/*.feature) against the running compose topology
```

To run a single behat scenario, get a shell in the `test` service and target a feature file by line number:

```bash
docker compose run --rm test bash
behat features/mfa.feature:7
```

Dependency updates:

```bash
make depsupdate       # composer update via ./update-composer-deps.sh
make jsdepsupdate      # npm update + re-copy the webauthn browser bundle
make composershow      # regenerate installed-packages.json from locked composer deps
```

`make certs` regenerates local DB certs via `db/make-db-cert.sh`.

## Architecture

### Custom SimpleSAMLphp modules (`modules/`)

Each subdirectory is a self-contained SimpleSAMLphp module (`src/`, `templates/`, `public/`, `locales/`, optionally `tests/`), documented in depth in README.md under "Custom Modules":

- **expirychecker** — AuthProc filter that warns/blocks on password expiry (`ExpiryDate` class), driven by `accountNameAttr`/`expiryDateAttr`/`passwordChangeUrl` params set in `metadata/saml20-idp-hosted.php`.
- **material** — the Material Design theme (`theme.use = material:material`), with branding via `THEME_COLOR_SCHEME`, optional reCAPTCHA, Google Analytics, and an `announcement.php` mechanism for site-wide banners.
- **mfa** — AuthProc filter prompting for MFA (TOTP, WebAuthn via `@simplewebauthn/browser`, recovery contacts). Its JS bundle is vendored into `modules/mfa/public/simplewebauthn/` by `make copyJsLib` — don't hand-edit that file.
- **profilereview** — AuthProc filter for periodic profile-review prompts.
- **silauth** — custom authentication source backed by REST API microservice (ID Broker a/k/a idp-id-broker) and a SQL database for rate limiting; has its own `migrations/` (autoloaded as `Sil\SilAuth\migrations`) and handles rate limiting and a status-check endpoint (see README.md "Rate Limiting" / "Status Check").
- **sildisco** — SAML IdP discovery module that restricts which SPs can authenticate through which IdPs (`SPList` / `IDPNamespace` metadata entries), forces re-discovery when an SP allows multiple IdPs, and namespaces group names as `idp|<IDPNamespace>|<group name>`. See `docs/overview.md` and `docs/the_hub.md` for the discovery design.

Multiple modules use the `authproc` array in a hosted IdP's `metadata/saml20-idp-hosted.php` (or `authproc.idp` in `config.php`) to wire in — order/priority (the numeric key) matters, e.g. expirychecker is recommended to run before other filters.

### Metadata conventions

Downstream images built FROM ssp-base add files to `$SSP_PATH/metadata/` (`SSP_PATH` = `/data/vendor/simplesamlphp/simplesamlphp`). Two metadata formats exist — see README.md "SimpleSAMLphp Metadata": a legacy multi-file format (pre-v10, via `Sil\SspUtils\Metadata::getIdpMetadataEntries`/`getSpMetadataEntries`) and the current standard format (`Metadata::getMetadataFiles($dir, 'idp'|'sp')` + `include`). `tests/MetadataTest.php` enforces structural rules on these files (valid PHP, array-returning, `IDPNamespace` format, no duplicate IdP/SP entity IDs, required `name`/`logoURL` fields, hub-mode SP-list/IDPList consistency, SP cert/signing requirements unless `SkipTests => true`) — see README.md "Metadata Tests Check" for the full rule list.

### Local dev topology (`development/`)

Each of `hub`, `idp-local`, `idp2-local`..`idp4-local`, `sp-local`, `sp2-local`, `sp3-local` has its own `cert/`, `config/`, `metadata/` mounted into the corresponding `compose.yaml` service (`ssp-hub.local`, `ssp-idp1.local`..`ssp-idp4.local`, `ssp-sp1.local`..`ssp-sp3.local`). `docs/the_hub.md` explains the routing intent: `sp1` can reach `idp1`+`idp2`, `sp2` only `idp2`, `sp3` only `idp1`; sessions authenticated through a disallowed IdP for a given SP force re-authentication. `dockerbuild/ssp-overrides/sp-php.patch` is what forces re-discovery when an SP is permitted multiple IdPs.

### Configuration

Runtime config is env-var driven (documented exhaustively in `local.env.dist`). Optionally, config can be pulled from AWS AppConfig (legacy) or AWS Parameter Store (preferred), which overlays/overwrites plain env vars — see README.md "Configuration".

### Tests

- `tests/MetadataTest.php` — PHPUnit, validates metadata file structure (see above); run via `dockerbuild/run-metadata-tests.sh`, which is invoked per-IdP-container.
- `tests/AnnouncementTest.php` plus the SimpleSAMLphp-vendored `sildisco`/`mfa` module test suites — PHPUnit, run via `dockerbuild/run-tests.sh` (which also runs metadata tests and, if `SSL_CA_BASE64` is set, decodes a DB CA cert before running integration tests).
- `features/*.feature` — Behat acceptance tests (one suite per module/concern, wired in `behat.yml` to a matching `*Context` PHP class), run against the live compose topology via `dockerbuild/run-integration-tests.sh`. `docs/functional_testing.md` also documents the equivalent manual click-through flows across the sp/hub/idp containers, useful for understanding what a given feature file is asserting.
