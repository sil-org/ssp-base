<?php

namespace SimpleSAML\Module\loginfinalizer;

/**
 * Resolves the entity ID of the SP that actually initiated a login, and of
 * the Hub/proxy (if any) that the request came through.
 *
 * On a plain IdP, the SP is simply the direct requester (SPMetadata) and
 * there is no Hub. On the authoritative IdP behind a Hub/proxy, the direct
 * requester is the Hub itself -- the true originating SP's entity ID instead
 * arrives via the SAML Scoping/RequesterID mechanism, which SimpleSAMLphp's
 * SP module forwards automatically when proxying a request (no patch
 * required) and populates into $state['saml:RequesterID'] on the receiving
 * IdP. A non-empty RequesterID is therefore the signal that a Hub sat in
 * between, and the direct requester is that Hub.
 */
class SpEntityId
{
    public static function resolve(array $state): ?string
    {
        $requesterIds = $state['saml:RequesterID'] ?? [];
        if (!empty($requesterIds)) {
            return $requesterIds[0];
        }

        return self::directRequester($state);
    }

    /**
     * The entity ID of the Hub/proxy the request came through, or null if the
     * SP talked to this IdP directly. If Hubs are chained, this is the last
     * one (the one that talked to this IdP).
     */
    public static function resolveHub(array $state): ?string
    {
        if (empty($state['saml:RequesterID'])) {
            return null;
        }

        return self::directRequester($state);
    }

    private static function directRequester(array $state): ?string
    {
        return $state['SPMetadata']['entityid']
            ?? $state['SPMetadata']['entityID']
            ?? $state['Destination']['entityid']
            ?? $state['Destination']['entityID']
            ?? null;
    }
}
