<?php

namespace SimpleSAML\Module\silauth\Auth\Source\saml;

class User
{
    public static function convertToSamlFieldNames(
        string  $employeeId,
        string  $firstName,
        string  $lastName,
        string  $username,
        string  $email,
        string  $uuid,
        string  $idpDomainName,
        ?string $passwordExpirationDate,
        array   $mfa,
        array   $method,
        ?string $managerEmail,
        string  $profileReview,
        array   $member,
        string  $displayName
    ): array {

        // eduPersonUniqueId (only alphanumeric allowed)
        $alphaNumericUuid = str_replace('-', '', $uuid);
        $eduPersonUniqueId = $alphaNumericUuid . '@' . $idpDomainName;

        return [
            'eduPersonPrincipalName' => [
                $username . '@' . $idpDomainName,
            ],

            /**
             * Use this for a globally unique, non-human friendly, non-reassignable attribute
             *
             * NOTE: Do NOT include eduPersonTargetedID. If you need it, use the
             * core:TargetedID module (at the Hub, if using one) to generate an
             * eduPersonTargetedID based on this attribute.
             **/
            'eduPersonUniqueId' => (array)$eduPersonUniqueId,

            'sn' => (array)$lastName,
            'givenName' => (array)$firstName,
            'displayName' => (array)$displayName,
            'mail' => (array)$email,
            'employeeNumber' => (array)$employeeId,
            'cn' => (array)$username,
            'schacExpiryDate' => (array)$passwordExpirationDate,
            'mfa' => $mfa,
            'method' => $method,
            'uuid' => (array)$uuid,
            'manager_email' => [$managerEmail ?? ''],
            'profile_review' => [$profileReview],
            'member' => $member,
        ];
    }
}
