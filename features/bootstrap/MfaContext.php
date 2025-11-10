<?php

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert;
use Sil\PhpEnv\Env;
use Sil\SspBase\Features\fakes\FakeIdBrokerClient;

/**
 * Defines application features from the specific context.
 */
class MfaContext extends FeatureContext
{
    /**
     * Assert that the given page has a form that contains the given text.
     *
     * @param string $text The text (or HTML) to search for.
     * @param DocumentElement $page The page to search in.
     * @return void
     */
    protected function assertFormContains($text, $page)
    {
        $forms = $page->findAll('css', 'form');
        foreach ($forms as $form) {
            if (strpos($form->getHtml(), $text) !== false) {
                return;
            }
        }
        Assert::fail(sprintf(
            "No form found containing %s in this HTML:\n%s",
            var_export($text, true),
            $page->getHtml()
        ));
    }

    /**
     * Get the "continue" button.
     *
     * @param DocumentElement $page The page.
     * @return NodeElement
     */
    protected function getContinueButton($page)
    {
        $continueButton = $page->find('css', '[name=continue]');
        return $continueButton;
    }

    /**
     * Get the button for submitting the MFA form.
     *
     * @param DocumentElement $page The page.
     * @return NodeElement
     */
    protected function getSubmitMfaButton($page)
    {
        $submitMfaButton = $page->find('css', '[name=submitMfa]');
        Assert::assertNotNull($submitMfaButton, 'Failed to find the submit-MFA button');
        return $submitMfaButton;
    }

    /**
     * Submit the current form, including the secondary page's form (if
     * simpleSAMLphp shows another page because JavaScript isn't supported) by
     * clicking the specified button.
     *
     * @param string $buttonName The value of the desired button's `name`
     *     attribute.
     */
    protected function submitFormByClickingButtonNamed($buttonName)
    {
        $page = $this->session->getPage();
        $button = $page->find('css', sprintf(
            '[name=%s]',
            $buttonName
        ));
        Assert::assertNotNull($button, 'Failed to find button named ' . $buttonName);
        $button->click();
        $this->submitSecondarySspFormIfPresent($page);
    }

    /**
     * Submit the MFA form, including the secondary page's form (if
     * simpleSAMLphp shows another page because JavaScript isn't supported).
     *
     * @param DocumentElement $page The page.
     */
    protected function submitMfaForm($page)
    {
        $submitMfaButton = $this->getSubmitMfaButton($page);
        $submitMfaButton->click();
        $this->submitSecondarySspFormIfPresent($page);
    }

    /**
     * @Given I provide credentials that do not need MFA
     */
    public function iProvideCredentialsThatDoNotNeedMfa()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'no_mfa_needed';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that need MFA but have no MFA options available
     */
    public function iProvideCredentialsThatNeedMfaButHaveNoMfaOptionsAvailable()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'must_set_up_mfa';
        $this->password = 'a';
    }

    /**
     * @Then I should see a message that I have to set up MFA
     */
    public function iShouldSeeAMessageThatIHaveToSetUpMfa()
    {
        $page = $this->session->getPage();
        Assert::assertContains('must set up 2-', $page->getHtml());
    }

    /**
     * @Then there should be a way to go set up MFA now
     */
    public function thereShouldBeAWayToGoSetUpMfaNow()
    {
        $page = $this->session->getPage();
        $this->assertFormContains('name="setUpMfa"', $page);
    }

    /**
     * @Given I provide credentials that need MFA and have backup codes available
     */
    public function iProvideCredentialsThatNeedMfaAndHaveBackupCodesAvailable()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_backupcode';
        $this->password = 'a';
    }

    /**
     * @Then I should see a prompt for a backup code
     */
    public function iShouldSeeAPromptForABackupCode()
    {
        $page = $this->session->getPage();
        $pageHtml = $page->getHtml();
        Assert::assertContains('Printable code', $pageHtml);
        Assert::assertContains('Enter code', $pageHtml);
    }

    /**
     * @Given I provide credentials that need MFA and have TOTP available
     */
    public function iProvideCredentialsThatNeedMfaAndHaveTotpAvailable()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_totp';
        $this->password = 'a';
    }

    /**
     * @Then I should see a prompt for a TOTP (code)
     */
    public function iShouldSeeAPromptForATotpCode()
    {
        $page = $this->session->getPage();
        $pageHtml = $page->getHtml();
        Assert::assertContains('Enter 6-digit code', $pageHtml);
    }

    /**
     * @Given I provide credentials that need MFA and have WebAuthn available
     */
    public function iProvideCredentialsThatNeedMfaAndHaveUfAvailable()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_webauthn';
        $this->password = 'a';
    }

    /**
     * @Then I should see a prompt for a WebAuthn (security key)
     */
    public function iShouldSeeAPromptForAWebAuthn()
    {
        $page = $this->session->getPage();
        Assert::assertContains('Security key', $page->getHtml());
    }

    protected function submitMfaValue($mfaValue)
    {
        $page = $this->session->getPage();
        $page->fillField('mfaSubmission', $mfaValue);
        $this->submitMfaForm($page);
        return $page->getHtml();
    }

    /**
     * @When I submit a correct backup code
     */
    public function iSubmitACorrectBackupCode()
    {
        if (!$this->pageContainsElementWithText('h1', 'Printable code')) {
            // find image of the backup code option presented in other_mfas.twig
            $printableCodeOption = $this->session->getPage()->find('css', 'img[src=mfa-backupcode\002Esvg]');
            $printableCodeOption->click();
        }
        $this->submitMfaValue(FakeIdBrokerClient::CORRECT_VALUE);
    }

    protected function pageContainsElementWithText($cssSelector, $text)
    {
        $page = $this->session->getPage();
        $elements = $page->findAll('css', $cssSelector);
        foreach ($elements as $element) {
            if (strpos($element->getText(), $text) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @When I submit an incorrect backup code
     */
    public function iSubmitAnIncorrectBackupCode()
    {
        $this->submitMfaValue(FakeIdBrokerClient::INCORRECT_VALUE);
    }

    /**
     * @Then I should see a message that I have to wait before trying again
     */
    public function iShouldSeeAMessageThatIHaveToWaitBeforeTryingAgain()
    {
        $page = $this->session->getPage();
        $pageHtml = $page->getHtml();
        Assert::assertContains(' wait ', $pageHtml);
        Assert::assertContains('try again', $pageHtml);
    }

    /**
     * @Then I should see a message that it was incorrect
     */
    public function iShouldSeeAMessageThatItWasIncorrect()
    {
        $page = $this->session->getPage();
        $pageHtml = $page->getHtml();
        Assert::assertContains('Incorrect 2-step verification code', $pageHtml);
    }

    /**
     * @Given I provide credentials that have a rate-limited MFA
     */
    public function iProvideCredentialsThatHaveARateLimitedMfa()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_rate_limited_mfa';
        $this->password = 'a';
    }

    /**
     * @Then there should be a way to continue to my intended destination
     */
    public function thereShouldBeAWayToContinueToMyIntendedDestination()
    {
        $page = $this->session->getPage();
        $this->assertFormContains('name="continue"', $page);
    }

    /**
     * @When I click the remind-me-later button
     */
    public function iClickTheRemindMeLaterButton()
    {
        $this->submitFormByClickingButtonNamed('continue');
    }

    /**
     * @When I click the set-up-MFA button
     */
    public function iClickTheSetUpMfaButton()
    {
        $this->submitFormByClickingButtonNamed('setUpMfa');
    }

    /**
     * @Then I should end up at the mfa-setup URL
     */
    public function iShouldEndUpAtTheMfaSetupUrl()
    {
        $mfaSetupUrl = Env::get('PROFILE_URL_FOR_TESTS');
        Assert::assertNotEmpty($mfaSetupUrl, 'No PROFILE_URL_FOR_TESTS provided');
        $currentUrl = $this->session->getCurrentUrl();
        Assert::assertStringStartsWith(
            $mfaSetupUrl,
            $currentUrl,
            'Did NOT end up at the MFA-setup URL'
        );
    }

    /**
     * @Then there should NOT be a way to continue to my intended destination
     */
    public function thereShouldNotBeAWayToContinueToMyIntendedDestination()
    {
        $page = $this->session->getPage();
        $continueButton = $this->getContinueButton($page);
        Assert::assertNull($continueButton, 'Should not have found a continue button');
    }

    /**
     * @Then I should NOT be able to get to my intended destination
     */
    public function iShouldNotBeAbleToGetToMyIntendedDestination()
    {
        $this->session->visit(self::SP1_LOGIN_PAGE);
        Assert::assertStringStartsNotWith(
            self::SP1_LOGIN_PAGE,
            $this->session->getCurrentUrl(),
            'Failed to prevent me from getting to SPs other than the MFA setup URL'
        );
    }

    /**
     * @Given I provide credentials that need MFA and have 4 backup codes available
     */
    public function iProvideCredentialsThatNeedMfaAndHave4BackupCodesAvailable()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_4_backupcodes';
        $this->password = 'a';
    }

    /**
     * @Then I should see a message that I am running low on backup codes
     */
    public function iShouldSeeAMessageThatIAmRunningLowOnBackupCodes()
    {
        $page = $this->session->getPage();
        Assert::assertContains(
            'Almost out of printable codes',
            $page->getHtml()
        );
    }

    /**
     * @Then there should be a way to get more backup codes now
     */
    public function thereShouldBeAWayToGetMoreBackupCodesNow()
    {
        $page = $this->session->getPage();
        $this->assertFormContains('name="getMore"', $page);
    }

    /**
     * @Given I provide credentials that need MFA and have 1 backup code available and no other MFA
     */
    public function iProvideCredentialsThatNeedMfaAndHave1BackupCodeAvailableAndNoOtherMfa()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_1_backupcode_only';
        $this->password = 'a';
    }

    /**
     * @Then I should see a message that I have used up my backup codes
     */
    public function iShouldSeeAMessageThatIHaveUsedUpMyBackupCodes()
    {
        $page = $this->session->getPage();
        Assert::assertContains(
            'Last printable code used',
            $page->getHtml()
        );
    }

    /**
     * @Given I provide credentials that need MFA and have 1 backup code available plus some other MFA
     */
    public function iProvideCredentialsThatNeedMfaAndHave1BackupCodeAvailablePlusSomeOtherMfa()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_1_backupcode_plus';
        $this->password = 'a';
    }

    /**
     * @When I click the get-more-backup-codes button
     */
    public function iClickTheGetMoreBackupCodesButton()
    {
        $this->submitFormByClickingButtonNamed('getMore');
    }

    /**
     * @Then I should be told I only have :numRemaining backup codes left
     */
    public function iShouldBeToldIOnlyHaveBackupCodesLeft($numRemaining)
    {
        $page = $this->session->getPage();
        Assert::assertContains(
            'You only have ' . $numRemaining . ' more left',
            $page->getHtml()
        );
    }

    /**
     * @Then I should be given more backup codes
     */
    public function iShouldBeGivenMoreBackupCodes()
    {
        $page = $this->session->getPage();
        Assert::assertContains(
            'New printable codes',
            $page->getContent()
        );
    }

    /**
     * @Given I provide credentials that have WebAuthn
     */
    public function iProvideCredentialsThatHaveUf()
    {
        $this->iProvideCredentialsThatNeedMfaAndHaveUfAvailable();
    }

    /**
     * @Given I provide credentials that have WebAuthn, TOTP
     */
    public function iProvideCredentialsThatHaveUfTotp()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_webauthn_totp';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have WebAuthn, backup codes
     */
    public function iProvideCredentialsThatHaveUfBackupCodes()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_webauthn_backupcodes';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have WebAuthn, TOTP, backup codes
     */
    public function iProvideCredentialsThatHaveUfTotpBackupCodes()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_webauthn_totp_backupcodes';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have TOTP
     */
    public function iProvideCredentialsThatHaveTotp()
    {
        $this->iProvideCredentialsThatNeedMfaAndHaveTotpAvailable();
    }

    /**
     * @Given I provide credentials that have TOTP, backup codes
     */
    public function iProvideCredentialsThatHaveTotpBackupCodes()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_totp_backupcodes';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have backup codes
     */
    public function iProvideCredentialsThatHaveBackupCodes()
    {
        $this->iProvideCredentialsThatNeedMfaAndHaveBackupCodesAvailable();
    }

    /**
     * @Given I provide credentials that have a manager code, a WebAuthn and a more recently used TOTP
     */
    public function IProvideCredentialsThatHaveManagerCodeWebauthnAndMoreRecentlyUsedTotp()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_mgr_code_webauthn_and_more_recently_used_totp';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have a used WebAuthn
     */
    public function IProvideCredentialsThatHaveUsedWebAuthn()
    {
        $this->username = 'has_webauthn_';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have a used TOTP
     */
    public function IProvideCredentialsThatHaveUsedTotp()
    {
        $this->username = 'has_totp_';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that have a used backup code
     */
    public function IProvideCredentialsThatHaveUsedBackupCode()
    {
        $this->username = 'has_backup_code_';
        $this->password = 'a';
    }

    /**
     * @Given and I have a more recently used TOTP
     */
    public function IHaveMoreRecentlyUsedTotp()
    {
        $this->username .= 'and_more_recently_used_totp';
        $this->password = 'a';
    }

    /**
     * @Given and I have a more recently used Webauthn
     */
    public function IHaveMoreRecentlyUsedWebauthn()
    {
        $this->username .= 'and_more_recently_used_webauthn';
        $this->password = 'a';
    }

    /**
     * @Given and I have a more recently used backup code
     */
    public function IHaveMoreRecentlyUsedBackupCode()
    {
        $this->username .= 'and_more_recently_used_backup_code';
        $this->password = 'a';
    }

    /**
     * @Given the user has a manager email
     */
    public function theUserHasAManagerEmail()
    {
        $this->username .= '_and_mgr';
    }

    /**
     * @Then I should see a link to send a code to the user's manager
     */
    public function iShouldSeeALinkToSendACodeToTheUsersManager()
    {
        $page = $this->session->getPage();
        Assert::assertContains(
            '/module.php/mfa/send-manager-mfa.php',
            $page->getContent()
        );
    }

    /**
     * @Given the user does not have a manager email
     */
    public function theUserDoesntHaveAManagerEmail()
    {
        /*
         * No change to username needed.
         */
    }

    /**
     * @Then I should not see a link to send a code to the user's manager
     */
    public function iShouldNotSeeALinkToSendACodeToTheUsersManager()
    {
        $page = $this->session->getPage();
        Assert::assertNotContains(
            '/module.php/mfa/send-manager-mfa.php',
            $page->getContent()
        );
    }

    /**
     * @When I click the Request Assistance link
     */
    public function iClickTheRequestAssistanceLink()
    {
        $page = $this->session->getPage();
        $helpOption = $page->findById('more-options-manager');
        Assert::assertNotNull(
            $helpOption,
            'Could not find the "I need help" option'
        );
        $helpOption->click();
    }

    /**
     * @When I click the Send a code link
     */
    public function iClickTheRequestACodeLink()
    {
        $this->submitFormByClickingButtonNamed('send');
    }

    /**
     * @Then I should see a prompt for a manager rescue code
     */
    public function iShouldSeeAPromptForAManagerRescueCode()
    {
        $page = $this->session->getPage();
        $pageHtml = $page->getHtml();
        Assert::assertContains('Ask Your Recovery Contact for Help', $pageHtml);
        Assert::assertContains('Enter code', $pageHtml);
        Assert::assertContains('m*****r@e******.c**', $pageHtml);
    }

    /**
     * @When I submit the correct manager code
     */
    public function iSubmitTheCorrectManagerCode()
    {
        $this->submitMfaValue(FakeIdBrokerClient::CORRECT_VALUE);
    }

    /**
     * @When I submit an incorrect manager code
     */
    public function iSubmitAnIncorrectManagerCode()
    {
        $this->submitMfaValue(FakeIdBrokerClient::INCORRECT_VALUE);
    }

    /**
     * @Given I provide credentials that have a manager code
     */
    public function iProvideCredentialsThatHaveAManagerCode()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'has_mgr_code';
        $this->password = 'a';
    }

    /**
     * @Then there should be a way to request a manager code
     */
    public function thereShouldBeAWayToRequestAManagerCode()
    {
        $page = $this->session->getPage();
        Assert::assertContains('Ask Your Recovery Contact', $page->getHtml());
        $this->assertFormContains('name="send"', $page);
    }

    /**
     * @When I click the Cancel button
     */
    public function iClickTheCancelButton()
    {
        $this->submitFormByClickingButtonNamed('cancel');
    }
}
