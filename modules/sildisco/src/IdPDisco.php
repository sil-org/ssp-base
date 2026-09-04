<?php

namespace SimpleSAML\Module\sildisco;

use Sil\SspUtils\AnnouncementUtils;
use Sil\SspUtils\Utils;
use SimpleSAML\Auth;
use SimpleSAML\Error\ConfigurationError;
use SimpleSAML\Error\MetadataNotFound;
use SimpleSAML\Error\NoState;
use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\XHTML\IdPDisco as SSPIdPDisco;
use SimpleSAML\XHTML\Template;
use yii\db\Exception;

/**
 * This class implements a custom IdP discovery service, for use with a ssp hub (proxy)
 *
 * This module extends the basic IdP disco handler.
 *
 * @author SIL GTIS
 * @package SimpleSAMLphp
 */
class IdPDisco extends SSPIdPDisco
{

    /* The session type for this class */
    public static string $sessionType = 'sildisco:authentication';

    /**
     * @inheritDoc
     */
    protected function log(string $message): void
    {
        Logger::info(json_encode([
            'class' => self::class,
            'instance' => $this->instance,
            'host' => $_SERVER['HTTP_HOST'] ?? '',
            'message' => $message,
        ]));
    }

    /**
     * @throws NoState
     * @throws Exception|MetadataNotFound
     */
    private function getSPEntityIDAndReducedIdpListAndForceAuthn(): array
    {

        $idpList = $this->getIdPList();
        $idpList = $this->filterList($idpList);

        // Creative solution for getting the EntityID from the SPMetadata entry in the state
        // Source: https://github.com/simplesamlphp/simplesamlphp-module-discopower/blob/5e2e5e9da751104d1553d273cfb2d0bd1e2b57df/src/PowerIdPDisco.php#L231
        // Before the SimpleSAMLphp 2 upgrade, we added it to the state ourselves by overriding the SAML2.php file
        parse_str(parse_url($_GET['return'], PHP_URL_QUERY), $returnState);
        $state = Auth\State::loadState($returnState['AuthID'], 'saml:sp:sso');
        if (!array_key_exists('SPMetadata', $state)) {
            throw new Exception('SPMetadata not found in state');
        }

        $spmd = $state['SPMetadata'];
        $spEntityId = $spmd['entityid'];
        if (empty($spEntityId)) {
            throw new Exception('empty SP entityID');
        }

        $idpList = self::getReducedIdpList($idpList, $spEntityId);

        $forceAuthn = self::getForceAuthn($spEntityId);

        return array($spEntityId, $idpList, $forceAuthn);
    }

    /**
     * @inheritDoc
     * @throws MetadataNotFound|ConfigurationError
     * @throws \Exception
     */
    public function handleRequest(): void
    {
        $this->start();
        list($spEntityId, $idpList, $forceAuthn) = $this->getSPEntityIDAndReducedIdpListAndForceAuthn();

        $httpUtils = new HTTP();

        if (sizeof($idpList) == 1) {
            $idp = array_keys($idpList)[0];
            $idp = $this->validateIdP($idp);
            if ($idp !== null) {

                $this->log(
                    'Choice made [' . $idp . '] (Redirecting the user back. returnIDParam=' .
                    $this->returnIdParam . ')'
                );

                $urlParametersArray = array($this->returnIdParam => $idp);
                if ($forceAuthn == "true") {
                    array_push($urlParametersArray, [Utils::SP_FORCE_AUTHN_KEY => $forceAuthn]);
                }

                $httpUtils->redirectTrustedURL(
                    $this->returnURL,
                    $urlParametersArray
                );
            }
        }

        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $sp = $metadata->getMetaData($spEntityId, 'saml20-sp-remote');

        $t = new Template($this->config, 'selectidp-links');

        $t->data['idp_list'] = $idpList;
        $t->data['return'] = $this->returnURL;
        $t->data['return_id_param'] = $this->returnIdParam;
        $t->data['entity_id'] = $this->spEntityId;
        $t->data['sp'] = $sp;
        $t->data['announcement'] = AnnouncementUtils::getAnnouncement();
        $t->data['help_center_url'] = $this->config->getOptionalString('helpCenterUrl', '');

        $t->send();
    }

    /**
     * @inheritDoc
     */
    protected function validateIdP(?string $idp): ?string
    {
        if ($idp === null) {
            return null;
        }
        if (!$this->config->getOptionalBoolean('idpdisco.validate', true)) {
            return $idp;
        }

        list($spEntityId, $idpList, $forceAuthn) = $this->getSPEntityIDAndReducedIdpListAndForceAuthn();

        /*
         * All this complication is for security.
         * Without it a user is able to use his authentication through an
         * IdP to login to an SP that normally shouldn't accept that IdP.
         *
         * With a good process, the current SP's entity ID will appear in the
         * session and in the request's 'return' entry.
         *
         * With a hacked process, the SP in the session will not appear in the
         * request's 'return' entry.
         */
        $returnKey = 'return';
        $requestReturn = array_key_exists($returnKey, $_REQUEST) ?
            urldecode(urldecode($_REQUEST[$returnKey])) : "";

        $spEntityIdParam = 'spentityid=' . $spEntityId;

        if (!str_contains($requestReturn, $spEntityIdParam)) {
            $message = 'Invalid SP entity id [' . $spEntityId . ']. ' .
                'Could not find in return value. ' . PHP_EOL . $requestReturn;
            $this->log($message);
            return null;
        }

        if (array_key_exists($idp, $idpList)) {
            return $idp;
        }
        $this->log('Invalid IdP entity id [' . $idp . '] received from discovery page.');
        // the entity id wasn't valid
        return null;
    }

    /**
     * Takes the original IDP List and reduces it down to the ones the current SP is meant to see.
     *    The relevant entries in saml20-idp-remote.php would be ...
     *      - 'excludeByDefault' (boolean), which when set to True would keep this idp from being
     *        shown to SP's that don't explicitly include it in the 'IDPList' entry of their metadata.
     *      - 'SPList' (array), which when set would only allow this idp to be shown
     *        to SPs whose entity_id is included in this array.
     *
     * @param array $idpList
     * @param string $spEntityId - the current SP's entity id
     * @return array of a subset of the original $startSources.
     * @throws MetadataNotFound
     */
    public static function getReducedIdpList(array $idpList, string $spEntityId): array
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spMetadata = $metadata->getMetaData($spEntityId, 'saml20-sp-remote');

        $reducedIdpList = [];

        $idpListForSp = [];  // The list of IDP's this SP wants to know about
        if (array_key_exists(Utils::IDP_LIST_KEY, $spMetadata)) {
            $idpListForSp = $spMetadata[Utils::IDP_LIST_KEY];
        }

        foreach ($idpList as $idpEntityId => $idpMdEntry) {
            if (Utils::isIdpValidForSp($idpEntityId,
                $idpMdEntry,
                $spEntityId,
                $idpListForSp)
            ) {
                $reducedIdpList[$idpEntityId] = $idpMdEntry;
            }
        }
        return $reducedIdpList;
    }

    /**
     * Retrieve  the SP metadata field "ForceAuthn"
     *
     * @return string A string with forceAuthn, defaults to "false".
     */
    protected static function getForceAuthn(string $spEntityId): string
    {
        $forceAuthn = "false";
        
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $spMetadata = $metadata->getMetaData($spEntityId, 'saml20-sp-remote');

        if (array_key_exists(Utils::SP_FORCE_AUTHN_KEY, $spMetadata)) {
            $forceAuthn = $spMetadata[Utils::SP_FORCE_AUTHN_KEY];
        }

        return $forceAuthn;
    }

    /**
     * Takes the original idp entries and reduces them down to the ones the current SP is meant to see.
     *
     * @param string $spEntityId
     * @return array
     * @throws MetadataNotFound
     */
    public static function getIdpsForSp(string $spEntityId): array
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpEntries = $metadata->getList();

        return self::getReducedIdpList($idpEntries, $spEntityId);
    }
}
