<?php

namespace SimpleSAML\Module\loginfinalizer;

/**
 * Resolves the entity ID of the SP that actually initiated a login.
 *
 * On a plain IdP, that's simply the direct requester (SPMetadata). On the
 * authoritative IdP behind a Hub/proxy, the direct requester is the Hub
 * itself -- the true originating SP's entity ID instead arrives via the
 * SAML Scoping/RequesterID mechanism, which SimpleSAMLphp's SP module
 * forwards automatically when proxying a request (no patch required) and
 * populates into $state['saml:RequesterID'] on the receiving IdP.
 */
class SpEntityId
{
    public static function resolve(array $state): ?string
    {
        $requesterIds = $state['saml:RequesterID'] ?? [];
        if (!empty($requesterIds)) {
            return $requesterIds[0];
        }

        return $state['SPMetadata']['entityid']
            ?? $state['SPMetadata']['entityID']
            ?? $state['Destination']['entityid']
            ?? $state['Destination']['entityID']
            ?? null;
    }
}
