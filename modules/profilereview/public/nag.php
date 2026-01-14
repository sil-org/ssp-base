<?php

use SimpleSAML\Auth\ProcessingChain;
use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Module\profilereview\Auth\Process\ProfileReview;
use SimpleSAML\Module\profilereview\LoggerFactory;
use SimpleSAML\XHTML\Template;

$stateId = filter_input(INPUT_GET, 'StateId') ?? null;
if (empty($stateId)) {
    throw new BadRequest('Missing required StateId query parameter.');
}

$state = State::loadState($stateId, ProfileReview::STAGE_SENT_TO_NAG);
$logger = LoggerFactory::getAccordingToState($state);

// If the user has pressed the update button...
if (filter_has_var(INPUT_POST, 'update')) {
    ProfileReview::redirectToProfile($state);
    return;
} elseif (filter_has_var(INPUT_POST, 'continue')) {
    // The user has pressed the continue button.
    unset($state['Attributes']['mfa']);
    unset($state['Attributes']['method']);
    ProcessingChain::resumeProcessing($state);
    return;
}

$globalConfig = Configuration::getInstance();

$t = new Template($globalConfig, 'profilereview:' . $state['template']);
$t->data['profile_url'] = $state['profileUrl'];
$t->data['method_options'] = $state['methodOptions'] ?? [];
$t->data['mfa_options'] = $state['mfaOptions'] ?? [];
$t->data['idp_name'] = $t->getEntityDisplayName($state['IdPMetadata']);
$t->data['mfa_learn_more_url'] = $state['mfaLearnMoreUrl'] ?? '';
$t->send();

$logger->warning(json_encode([
    'module' => 'profilereview',
    'event' => 'presented nag',
    'template' => $state['template'],
    'employeeId' => $state['employeeId'],
]));
