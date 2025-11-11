<?php

use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Module\mfa\Auth\Process\Mfa;
use SimpleSAML\Module\mfa\LoggerFactory;
use SimpleSAML\XHTML\Template;

$stateId = filter_input(INPUT_GET, 'StateId') ?? null;
if (empty($stateId)) {
    throw new BadRequest('Missing required StateId query parameter.');
}

$state = State::loadState($stateId, Mfa::STAGE_SENT_TO_MFA_NEEDED_MESSAGE);
$logger = LoggerFactory::getAccordingToState($state);

// If the user has pressed the set-up-MFA button...
if (filter_has_var(INPUT_POST, 'setUpMfa')) {
    Mfa::redirectToMfaSetup($state);
    return;
}

$globalConfig = Configuration::getInstance();

$t = new Template($globalConfig, 'mfa:must-set-up-mfa');
$t->data['idp_name'] = $t->getEntityDisplayName($state['IdPMetadata']);
$t->send();

$logger->info(sprintf(
    'mfa: Told Employee ID %s they they must set up MFA.',
    $state['employeeId']
));
