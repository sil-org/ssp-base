<?php

use SimpleSAML\Auth\ProcessingChain;
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

$state = State::loadState($stateId, Mfa::STAGE_SENT_TO_OUT_OF_BACKUP_CODES_MESSAGE);
$logger = LoggerFactory::getAccordingToState($state);
$hasOtherMfaOptions = Mfa::hasMfaOptionsOtherThan('backupcode', $state);

if (filter_has_var(INPUT_POST, 'getMore')) {
    // The user pressed the button to create more backup codes.
    Mfa::giveUserNewBackupCodes($state, $logger);
    return;
} elseif (filter_has_var(INPUT_POST, 'continue') && $hasOtherMfaOptions) {
    unset($state['Attributes']['manager_email']);

    // The user pressed the remind-me-later button.
    ProcessingChain::resumeProcessing($state);
    return;
}

$globalConfig = Configuration::getInstance();

$t = new Template($globalConfig, 'mfa:out-of-backup-codes');
$t->data['has_other_mfa_options'] = $hasOtherMfaOptions;
$t->data['idp_name'] = $t->getEntityDisplayName($state['IdPMetadata']);

$t->send();

$logger->info(sprintf(
    'mfa: Told Employee ID %s they are out of backup codes%s.',
    $state['employeeId'],
    $hasOtherMfaOptions ? '' : ' and must set up more'
));
