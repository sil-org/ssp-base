<?php

/**
 * This "controller" (per MVC) must be called with the following query string
 * parameters:
 * - StateId
 */

use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Module\mfa\Auth\Process\Mfa;
use SimpleSAML\Module\mfa\LoggerFactory;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\XHTML\Template;

$stateId = filter_input(INPUT_GET, 'StateId');
if (empty($stateId)) {
    throw new BadRequest('Missing required StateId query parameter.');
}

$state = State::loadState($stateId, Mfa::STAGE_SENT_TO_MFA_PROMPT);

$logger = LoggerFactory::getAccordingToState($state);

$errorMessage = null;
if (filter_has_var(INPUT_POST, 'send')) {
    try {
        Mfa::sendManagerCode($state, $logger);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
} elseif (filter_has_var(INPUT_POST, 'cancel')) {
    $moduleUrl = SimpleSAML\Module::getModuleURL('mfa/prompt-for-mfa.php', [
        'StateId' => $stateId,
    ]);
    $httpUtils = new HTTP();
    $httpUtils->redirectTrustedURL($moduleUrl);
}

$globalConfig = Configuration::getInstance();

$t = new Template($globalConfig, 'mfa:send-manager-mfa');
$t->data['masked_manager_email'] = $state['maskedManagerEmail'];
$t->data['error_message'] = $errorMessage;
$t->data['idp_name'] = $t->getEntityDisplayName($state['IdPMetadata']);
$t->send();

$logger->notice(json_encode([
    'event' => 'offer to send manager code',
    'employeeId' => $state['employeeId'],
]));
