<?php
/**
 * Borrowed and modified from simplesamlphp/public/saml2/idp/metadata.php
 */

require_once('../public/_include.php');

use SAML2\Constants;
use SimpleSAML\Utils;

// load SimpleSAMLphp, configuration and metadata
$config = \SimpleSAML\Configuration::getInstance();
$metadata = \SimpleSAML\Metadata\MetaDataStorageHandler::getMetadataHandler();

if (!$config->getBoolean('enable.saml20-idp', false)) {
    throw new \SimpleSAML\Error\Error('NOACCESS');
}

// check if valid local session exists
//$authUtils = new Utils\Auth();
//if ($config->getOptionalBoolean('admin.protectmetadata', false)) {
//    $authUtils->requireAdmin();
//}

try {
    $idpentityid = isset($_GET['idpentityid']) ?
        $_GET['idpentityid'] :
        $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
    $idpmeta = $metadata->getMetaDataConfig($idpentityid, 'saml20-idp-hosted');

    $availableCerts = array();

    $cryptoUtils = new Utils\Crypto();

    $keys = array();
    $certInfo = $cryptoUtils->loadPublicKey($idpmeta, false, 'new_');
    if ($certInfo !== null) {
        $availableCerts['new_idp.crt'] = $certInfo;
        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => true,
            'encryption' => true,
            'X509Certificate' => $certInfo['certData'],
        );
        $hasNewCert = true;
    } else {
        $hasNewCert = false;
    }

    $certInfo = $cryptoUtils->loadPublicKey($idpmeta, true);
    $availableCerts['idp.crt'] = $certInfo;
    $keys[] = array(
        'type' => 'X509Certificate',
        'signing' => true,
        'encryption' => ($hasNewCert ? false : true),
        'X509Certificate' => $certInfo['certData'],
    );

    if ($idpmeta->hasValue('https.certificate')) {
        $httpsCert = $cryptoUtils->loadPublicKey($idpmeta, true, 'https.');
        assert('isset($httpsCert["certData"])');
        $availableCerts['https.crt'] = $httpsCert;
        $keys[] = array(
            'type' => 'X509Certificate',
            'signing' => true,
            'encryption' => false,
            'X509Certificate' => $httpsCert['certData'],
        );
    }

    $metaArray = array(
        'metadata-set' => 'saml20-idp-remote',
        'entityid' => $idpentityid,
    );

    $ssob = $metadata->getGenerated('SingleSignOnServiceBinding', 'saml20-idp-hosted');
    $slob = $metadata->getGenerated('SingleLogoutServiceBinding', 'saml20-idp-hosted');
    $ssol = $metadata->getGenerated('SingleSignOnService', 'saml20-idp-hosted');
    $slol = $metadata->getGenerated('SingleLogoutService', 'saml20-idp-hosted');

    if (is_array($ssob)) {
        foreach ($ssob as $binding) {
            $metaArray['SingleSignOnService'][] = array(
                'Binding' => $binding,
                'Location' => $ssol,
            );
        }
    } else {
        $metaArray['SingleSignOnService'][] = array(
            'Binding' => $ssob,
            'Location' => $ssol,
        );
    }

    if (is_array($slob)) {
        foreach ($slob as $binding) {
            $metaArray['SingleLogoutService'][] = array(
                'Binding' => $binding,
                'Location' => $slol,
            );
        }
    } else {
        $metaArray['SingleLogoutService'][] = array(
            'Binding' => $slob,
            'Location' => $slol,
        );
    }

    if (count($keys) === 1) {
        $metaArray['certData'] = $keys[0]['X509Certificate'];
    } else {
        $metaArray['keys'] = $keys;
    }

    $httpUtils = new Utils\HTTP();

    if ($idpmeta->getBoolean('saml20.sendartifact', false)) {
        // Artifact sending enabled
        $metaArray['ArtifactResolutionService'][] = array(
            'index' => 0,
            'Location' => $httpUtils->getBaseURL() . 'saml2/idp/ArtifactResolutionService.php',
            'Binding' => Constants::BINDING_SOAP,
        );
    }

    if ($idpmeta->getBoolean('saml20.hok.assertion', false)) {
        // Prepend HoK SSO Service endpoint.
        array_unshift($metaArray['SingleSignOnService'], array(
            'hoksso:ProtocolBinding' => Constants::BINDING_HTTP_REDIRECT,
            'Binding' => Constants::BINDING_HOK_SSO,
            'Location' => $httpUtils->getBaseURL() . 'saml2/idp/SSOService.php'
        ));
    }

    $metaArray['NameIDFormat'] = $idpmeta->getString(
        'NameIDFormat',
        'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
    );

    if ($idpmeta->hasValue('OrganizationName')) {
        $metaArray['OrganizationName'] = $idpmeta->getLocalizedString('OrganizationName');
        $metaArray['OrganizationDisplayName'] = $idpmeta->getLocalizedString(
            'OrganizationDisplayName',
            $metaArray['OrganizationName']
        );

        if (!$idpmeta->hasValue('OrganizationURL')) {
            throw new \SimpleSAML\Error\Exception('If OrganizationName is set, OrganizationURL must also be set.');
        }
        $metaArray['OrganizationURL'] = $idpmeta->getLocalizedString('OrganizationURL');
    }

    if ($idpmeta->hasValue('scope')) {
        $metaArray['scope'] = $idpmeta->getArray('scope');
    }

    $metadataUtils = Utils\Metadata();

    if ($idpmeta->hasValue('EntityAttributes')) {
        $metaArray['EntityAttributes'] = $idpmeta->getArray('EntityAttributes');

        // check for entity categories
        if ($metadataUtils->isHiddenFromDiscovery($metaArray)) {
            $metaArray['hide.from.discovery'] = true;
        }
    }

    if ($idpmeta->hasValue('UIInfo')) {
        $metaArray['UIInfo'] = $idpmeta->getArray('UIInfo');
    }

    if ($idpmeta->hasValue('DiscoHints')) {
        $metaArray['DiscoHints'] = $idpmeta->getArray('DiscoHints');
    }

    if ($idpmeta->hasValue('RegistrationInfo')) {
        $metaArray['RegistrationInfo'] = $idpmeta->getArray('RegistrationInfo');
    }

    if ($idpmeta->hasValue('validate.authnrequest')) {
        $metaArray['sign.authnrequest'] = $idpmeta->getBoolean('validate.authnrequest');
    }

    if ($idpmeta->hasValue('redirect.validate')) {
        $metaArray['redirect.sign'] = $idpmeta->getBoolean('redirect.validate');
    }

    if ($idpmeta->hasValue('contacts')) {
        $contacts = $idpmeta->getArray('contacts');
        foreach ($contacts as $contact) {
            $metaArray['contacts'][] = $metadataUtils->getContact($contact);
        }
    }

    $technicalContactEmail = $config->getString('technicalcontact_email', false);
    if ($technicalContactEmail && $technicalContactEmail !== 'na@example.org') {
        $techcontact['emailAddress'] = $technicalContactEmail;
        $techcontact['name'] = $config->getString('technicalcontact_name', null);
        $techcontact['contactType'] = 'technical';
        $metaArray['contacts'][] = $metadataUtils->getContact($techcontact);
    }

    $metaBuilder = new \SimpleSAML\Metadata\SAMLBuilder($idpentityid);
    $metaBuilder->addMetadataIdP20($metaArray);
    $metaBuilder->addOrganizationInfo($metaArray);

    $metaxml = $metaBuilder->getEntityDescriptorText();

    $metaflat = '$metadata[' . var_export($idpentityid, true) . '] = ' . var_export($metaArray, true) . ';';

    // sign the metadata if enabled
    $metaxml = \SimpleSAML\Metadata\Signer::sign($metaxml, $idpmeta->toArray(), 'SAML 2 IdP');

    if (array_key_exists('format', $_GET) && $_GET['format'] == 'xml') {
        header('Content-Type: application/xml');

        echo $metaxml;
        exit(0);
    } else {

        header('Content-Type: text/html; charset=utf-8');

        echo '<pre>' . print_r($metaflat, true) . '</pre>';
        exit(0);
    }
} catch (Exception $exception) {
    throw new \SimpleSAML\Error\Error('METADATA', $exception);
}
