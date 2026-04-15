<?php

use Behat\Mink\Element\DocumentElement;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Sil\PhpEnv\Env;
use SimpleSAML\Module\mfa\Auth\Process\Mfa;

/**
 * Defines application features from the specific context.
 */
class MfaRecoveryContext extends MfaContext
{
    #[Given('I use an IDP that is configured to offer MFA recovery-contacts')]
    public function iUseAnIdpThatIsConfiguredToOfferMfaRecoveryContacts(): void
    {
        $this->iClickOnTheTile('IDP 4');
    }

    #[Then('I should see a link to send a code to a recovery contact')]
    public function iShouldSeeALinkToSendACodeToARecoveryContact(): void
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains(
            '/module.php/mfa/send-recovery-mfa.php',
            $page->getContent()
        );
    }

    #[Then('I should see a way to send an MFA recovery code to my manager')]
    public function iShouldSeeAWayToSendAnMfaRecoveryCodeToMyManager(): void
    {
        $page = $this->getSession()->getPage();
        Assert::assertNotNull(
            $page->findById('option-manager'),
            'Did not find a way to select my manager'
        );

        $this->assertHasSendButton($page);
    }

    protected function assertHasSendButton(DocumentElement $page): void
    {
        Assert::assertTrue(
            $page->hasButton('send'),
            'Did not find a "send" button'
        );
    }

    #[Then('I should see a way to send an MFA recovery code to another recovery contact')]
    public function iShouldSeeAWayToSendAnMfaRecoveryCodeToAnotherRecoveryContact(): void
    {
        $page = $this->getSession()->getPage();
        $foundNonManagerOption = false;
        $contactOptionElements = $page->findAll('css', 'input[name=mfaRecoveryContactID]');
        foreach ($contactOptionElements as $element) {
            $elementID = $element->getAttribute('id');
            if ($elementID !== 'option-manager') {
                $foundNonManagerOption = true;
                break;
            }
        }
        Assert::assertTrue(
            $foundNonManagerOption,
            'Did not find a way to select another recovery contact'
        );

        $this->assertHasSendButton($page);
    }

    #[When('I send the code to the recovery contact')]
    public function iSendTheCodeToTheRecoveryContact(): void
    {
        $this->selectARecoveryContactOption();
        $this->submitFormByClickingButtonNamed('send');
    }

    protected function selectARecoveryContactOption(): void
    {
        $page = $this->getSession()->getPage();
        $contactOptionElements = $page->findAll('css', 'input[name=mfaRecoveryContactID]');
        foreach ($contactOptionElements as $element) {
            $elementID = $element->getAttribute('id');
            if ($elementID !== 'option-manager') {
                $element->click();
                $this->getSession()->wait(5000, 'document.readyState === "complete"');
                return;
            }
        }
        Assert::fail('Failed to find a way to select a (non-manager) recovery contact');
    }

    #[Then('I should not see an error message')]
    public function iShouldNotSeeAnErrorMessage(): void
    {
        $page = $this->getSession()->getPage();
        Assert::assertNotContains(
            'error',
            $page->getContent()
        );
    }

    #[Then('I should see confirmation that the code was sent')]
    public function iShouldSeeConfirmationThatTheCodeWasSent(): void
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains(
            'A temporary code was sent to your recovery contact',
            $page->getContent()
        );
    }

    #[Given('the recovery-contacts API has at least one contact for that account')]
    public function theRecoveryContactsApiHasAtLeastOneContactForThatAccount(): void
    {
        /* This test email address should match the email address in
         * authsources.php for the user this test scenario logged in as. */
        $emailAddress = 'has_backupcode@example.com';

        $recoveryContacts = $this->getRecoveryContactsFromMockApiFor($emailAddress);

        Assert::assertNotEmpty($recoveryContacts, sprintf(
            'No recovery contacts returned by %s for %s',
            Env::requireEnv('MFA_RECOVERY_CONTACTS_API'),
            $emailAddress
        ));
    }

    protected function getRecoveryContactsFromMockApiFor(string $emailAddress): array
    {
        $fakeState = [
            'recoveryConfig' => [
                'api' => Env::requireEnv('MFA_RECOVERY_CONTACTS_API'),
                'apiKey' => Env::requireEnv('MFA_RECOVERY_CONTACTS_API_KEY'),
                'fallbackEmail' => Env::requireEnv('MFA_RECOVERY_CONTACTS_FALLBACK_NAME'),
                'fallbackName' => Env::requireEnv('MFA_RECOVERY_CONTACTS_FALLBACK_EMAIL'),
            ],
            'Attributes' => [
                'mail' => $emailAddress,
            ],
        ];

        return Mfa::getRecoveryContactsByName($fakeState);
    }

    #[Then('I should see :optionText as one of the recovery contact options')]
    public function iShouldSeeAsOneOfTheRecoveryContactOptions($optionText): void
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains(
            'your supervisor',
            $page->getContent()
        );
    }

    #[Then('I should see the abbreviated, not full, name of my recovery contact as an option')]
    public function iShouldSeeTheAbbreviatedNotFullNameOfMyRecoveryContactAsAnOption(): void
    {
        /* This test email address should match the email address in
         * authsources.php for the user this test scenario logged in as. */
        $emailAddress = 'has_backupcode@example.com';

        $recoveryContacts = $this->getRecoveryContactsFromMockApiFor($emailAddress);
        Assert::assertNotEmpty($recoveryContacts, 'API returned no recovery contacts');

        $page = $this->getSession()->getPage();
        foreach (array_keys($recoveryContacts) as $recoveryContactName) {
            Assert::assertNotContains(
                $recoveryContactName,
                $page->getContent()
            );

            $abbreviatedName = Mfa::abbreviateName($recoveryContactName);
            Assert::assertContains(
                $abbreviatedName,
                $page->getContent()
            );
        }
    }

    #[When('I send the code to the manager')]
    public function iSendTheCodeToTheManager(): void
    {
        $page = $this->getSession()->getPage();
        $managerOption = $page->findById('option-manager');
        $managerOption->click();
        $this->getSession()->wait(5000, 'document.readyState === "complete"');
        $page->pressButton('send');
        $this->getSession()->wait(5000, 'document.readyState === "complete"');
    }

    #[Given('I provide credentials that have backup codes and a manager')]
    public function iProvideCredentialsThatHaveBackupCodesAndAManager(): void
    {
        $this->iProvideCredentialsThatHaveBackupCodes();
        $this->theUserHasAManagerEmail();
    }

    #[Given('I provide credentials for a user with a manager but no recovery contacts')]
    public function iProvideCredentialsForAUserWithAManagerButNoRecoveryContacts(): void
    {
        $this->username = 'has_backupcode_mgr_no_recovery_contacts';
        $this->password = 'a';
    }

    #[Then('I should see a way to send an MFA recovery code to the fallback recovery contact')]
    public function iShouldSeeAWayToSendAnMfaRecoveryCodeToTheFallbackRecoveryContact(): void
    {
        $page = $this->getSession()->getPage();
        $fallbackName = Env::requireEnv('MFA_RECOVERY_CONTACTS_FALLBACK_NAME');
        $foundFallbackOption = false;

        $contactOptionElements = $page->findAll('css', 'input[name=mfaRecoveryContactID]');
        foreach ($contactOptionElements as $element) {
            $elementValue = $element->getAttribute('value');
            if ($elementValue === $fallbackName) {
                $foundFallbackOption = true;
                break;
            }
        }
        Assert::assertTrue(
            $foundFallbackOption,
            'Did not find a way to select the fallback recovery contact'
        );

        $this->assertHasSendButton($page);
    }

    #[Given('I provide credentials for a user with a manager and a recovery contact')]
    public function iProvideCredentialsForAUserWithAManagerAndARecoveryContact(): void
    {
        $this->username = 'has_backupcode_mgr_recovery_contact';
        $this->password = 'a';
    }

    #[Then('I should see a way to send an MFA recovery code to this account\'s recovery contact')]
    public function iShouldSeeAWayToSendAnMfaRecoveryCodeToThisAccountsRecoveryContact(): void
    {
        $page = $this->getSession()->getPage();
        $recoveryContactFullName = 'Anne Admin'; // Must match value in `api-mock/mfa-recovery-contacts.json`
        $recoveryContactAbbreviatedName = Mfa::abbreviateName($recoveryContactFullName);
        $foundThatRecoveryContact = false;

        $contactOptionElements = $page->findAll('css', 'input[name=mfaRecoveryContactID]');
        foreach ($contactOptionElements as $element) {
            $elementValue = $element->getAttribute('value');
            if ($elementValue === $recoveryContactAbbreviatedName) {
                $foundThatRecoveryContact = true;
                break;
            }
        }
        Assert::assertTrue(
            $foundThatRecoveryContact,
            "Did not find a way to select this account's recovery contact"
        );

        $this->assertHasSendButton($page);
    }

    #[Given('the recovery-contacts API will return an error for that user')]
    public function theRecoveryContactsApiWillReturnAnErrorForThatUser(): void
    {
        $this->username .= '_error';
    }
}
