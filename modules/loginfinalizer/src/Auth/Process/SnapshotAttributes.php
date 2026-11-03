<?php

namespace SimpleSAML\Module\loginfinalizer\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;

/**
 * Saves a copy of the user's attributes early in the AuthProc chain, for
 * MarkLastLogin and LogToDynamo to read at the end of it.
 *
 * Those two filters must run last (so they only fire once a login has fully
 * succeeded), but by then core:AttributeLimit (priority 50 in config.php) has
 * already cut $state['Attributes'] down to what the SP is allowed to receive.
 * For an SP whose metadata lists only, say, 'mail' -- typical for direct
 * logins to SaaS SPs like Google -- the employee number, eduPersonPrincipalName
 * and cn are gone, so MarkLastLogin silently skipped the user and LogToDynamo
 * logged a record with no user in it.
 *
 * Wire this in at a priority lower than any filter that removes attributes.
 *
 * This deliberately copies ALL attributes rather than just the ones the
 * finalizers read. Trimming it wouldn't keep anything sensitive out of
 * storage: Auth\Source::loginCompleted() already persists the auth source's
 * complete attribute set in the session for the whole session lifetime, and
 * IdP::postAuth() re-seeds every SP's AuthProc chain from that copy. A
 * targeted list, on the other hand, would have to track every attribute
 * LogToDynamo logs plus the employeeNumber MarkLastLogin reads -- and
 * letting it drift would silently reintroduce the bug this filter fixes.
 */
class SnapshotAttributes extends ProcessingFilter
{
    public const STATE_KEY = 'loginfinalizer:Attributes';

    public function process(array &$state): void
    {
        $state[self::STATE_KEY] = $state['Attributes'] ?? [];
    }

    /**
     * The user's attributes as snapshotted before any were limited away, or
     * the current attributes if this filter didn't run (e.g. a downstream
     * config.php that doesn't wire it in).
     */
    public static function get(array $state): array
    {
        return $state[self::STATE_KEY] ?? $state['Attributes'] ?? [];
    }
}
