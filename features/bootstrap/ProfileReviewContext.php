<?php

use Behat\Mink\Element\DocumentElement;
use PHPUnit\Framework\Assert;
use Sil\PhpEnv\Env;

/**
 * Defines application features from the specific context.
 */
class ProfileReviewContext extends FeatureContext
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
     * Submit the current form, including the secondary page's form (if
     * simpleSAMLphp shows another page because JavaScript isn't supported) by
     * clicking the specified button.
     *
     * @param string $buttonName The value of the desired button's `name`
     *     attribute.
     */
    protected function submitFormByClickingButtonNamed($buttonName)
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('css', sprintf(
            '[name=%s]',
            $buttonName
        ));
        Assert::assertNotNull($button, 'Failed to find button named ' . $buttonName);
        $button->click();
        $this->getSession()->wait(5000, 'document.readyState === "complete"');
    }

    /**
     * @Given I provide credentials that do not need review
     */
    public function iProvideCredentialsThatDoNotNeedReview()
    {
        // Credentials defined in `development/idp-local/config/authsources.php`
        $this->username = 'no_review';
        $this->password = 'e';
    }

    /**
     * @Given I provide credentials that are due for a(n) :category :nagType reminder
     */
    public function iProvideCredentialsThatAreDueForAReminder($category, $nagType)
    {
        // Credentials defined in `development/idp-local/config/authsources.php`
        $this->username = $category . '_' . $nagType;
        switch ($this->username) {
            case 'mfa_add':
                $this->password = 'f';
                break;

            case 'method_add':
                $this->password = 'g';
                break;
        }
    }

    /**
     * @Given I provide credentials that are due for a profile review
     */
    public function iProvideCredentialsThatAreDueForAProfileReview()
    {
        // Credentials defined in `development/idp-local/config/authsources.php`
        $this->username = 'profile_review';
        $this->password = 'h';
    }


    protected function pageContainsElementWithText($cssSelector, $text)
    {
        $page = $this->getSession()->getPage();
        $elements = $page->findAll('css', $cssSelector);
        foreach ($elements as $element) {
            if (strpos($element->getText(), $text) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @Then there should be a way to continue to my intended destination
     */
    public function thereShouldBeAWayToContinueToMyIntendedDestination()
    {
        $page = $this->getSession()->getPage();
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
     * @When I click the update profile button
     */
    public function iClickTheUpdateProfileButton()
    {
        $this->submitFormByClickingButtonNamed('update');
    }

    /**
     * @When I click the :text link
     */
    public function iClickTheLink($text)
    {
        $this->clickLink($text);
    }

    /**
     * @Then I should end up at the update profile URL
     */
    public function iShouldEndUpAtTheUpdateProfileUrl()
    {
        $profileUrl = Env::get('PROFILE_URL_FOR_TESTS');
        Assert::assertNotEmpty($profileUrl, 'No PROFILE_URL_FOR_TESTS provided');
        $currentUrl = $this->getSession()->getCurrentUrl();
        Assert::assertStringStartsWith(
            $profileUrl,
            $currentUrl,
            'Did NOT end up at the update profile URL'
        );
    }

    /**
     * @Then I should end up at the update profile URL on a new tab
     */
    public function iShouldEndUpAtTheUpdateProfileUrlOnANewTab()
    {
        $profileUrl = Env::get('PROFILE_URL_FOR_TESTS');
        Assert::assertNotEmpty($profileUrl, 'No PROFILE_URL_FOR_TESTS provided');

        $windowNames = $this->getSession()->getWindowNames();
        Assert::assertGreaterThanOrEqual(2, sizeof($windowNames),
            'Expected to see at least 2 windows opened');

        foreach ($windowNames as $windowName) {
            $this->getSession()->switchToWindow($windowName);
            $currentUrl = $this->getSession()->getCurrentUrl();
            if ($currentUrl == $profileUrl) {
                return;
            }
        }

        Assert::fail('Did NOT end up at the update profile URL');
    }

    /**
     * @Then I should see the message: :message
     */
    public function iShouldSeeTheMessage($message)
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains($message, $page->getHtml());
    }

    /**
     * @Then there should be a way to go update my profile now
     */
    public function thereShouldBeAWayToGoUpdateMyProfileNow()
    {
        $page = $this->getSession()->getPage();
        $this->assertFormContains('name="update"', $page);
    }

    /**
     * @Then there should be a way to go review my profile now
     */
    public function thereShouldBeAWayToGoReviewMyProfileNow()
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains('Some of these need updating', $page->getHtml());
    }

    /**
     * @Given I provide credentials for a user that has used the manager mfa option
     */
    public function iProvideCredentialsForAUserThatHasUsedTheManagerMfaOption()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'profile_review';
        $this->password = 'h';
    }

    /**
     * @Then I should not see any manager mfa information
     */
    public function iShouldNotSeeAnyManagerMfaInformation()
    {
        $page = $this->getSession()->getPage();
        $isManagerMfaPresent = $page->hasContent('manager');
        Assert::assertFalse($isManagerMfaPresent, 'found manager mfa data');
    }
}
