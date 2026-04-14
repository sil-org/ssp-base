<?php

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class ExpiryContext extends FeatureContext
{
    /**
     * The browser session, used for interacting with the website.
     *
     * @var Session
     */
    protected $session;


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
     * Assert that the given page does NOT have a form that contains the given
     * text.
     *
     * @param string $text The text (or HTML) to search for.
     * @param DocumentElement $page The page to search in.
     * @return void
     */
    protected function assertFormNotContains($text, $page)
    {
        $forms = $page->findAll('css', 'form');
        foreach ($forms as $form) {
            if (strpos($form->getHtml(), $text) !== false) {
                Assert::fail(sprintf(
                    "Found a form containing %s in this HTML:\n%s",
                    var_export($text, true),
                    $page->getHtml()
                ));
            }
        }
    }

    /**
     * @Given I provide credentials that will expire in the distant future
     */
    public function iProvideCredentialsThatWillExpireInTheDistantFuture()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'distant_future';
        $this->password = 'a';
    }

    /**
     * @Given I provide credentials that will expire very soon
     */
    public function iProvideCredentialsThatWillExpireVerySoon()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'near_future';
        $this->password = 'b';
    }

    /**
     * @Then I should see a warning that my password will expire soon
     */
    public function iShouldSeeAWarningThatMyPasswordWillExpireSoon()
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains('will expire', $page->getHtml());
    }

    /**
     * @Then there should be a way to go change my password now
     */
    public function thereShouldBeAWayToGoChangeMyPasswordNow()
    {
        $page = $this->getSession()->getPage();
        $this->assertFormContains('change', $page);
    }

    /**
     * @Then there should be a way to continue without changing my password
     */
    public function thereShouldBeAWayToContinueWithoutChangingMyPassword()
    {
        $page = $this->getSession()->getPage();
        $this->assertFormContains('continue', $page);
    }

    /**
     * @Given I provide credentials that have expired
     */
    public function iProvideCredentialsThatHaveExpired()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'already_past';
        $this->password = 'c';
    }

    /**
     * @Then I should see a message that my password has expired
     */
    public function iShouldSeeAMessageThatMyPasswordHasExpired()
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains('has expired', $page->getHtml());
    }

    /**
     * @Then there should NOT be a way to continue without changing my password
     */
    public function thereShouldNotBeAWayToContinueWithoutChangingMyPassword()
    {
        $page = $this->getSession()->getPage();
        $this->assertFormNotContains('continue', $page);
    }

    /**
     * @Given I provide credentials that have no password expiration date
     */
    public function iProvideCredentialsThatHaveNoPasswordExpirationDate()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'missing_exp';
        $this->password = 'd';
    }

    /**
     * @Then I should see an error message
     */
    public function iShouldSeeAnErrorMessage()
    {
        $page = $this->getSession()->getPage();
        Assert::assertContains('We could not understand the expiration date', $page->getHtml());
    }

    /**
     * @Given I provide credentials that have an invalid password expiration date
     */
    public function iProvideCredentialsThatHaveAnInvalidPasswordExpirationDate()
    {
        // See `development/idp-local/config/authsources.php` for options.
        $this->username = 'invalid_exp';
        $this->password = 'e';
    }
}
