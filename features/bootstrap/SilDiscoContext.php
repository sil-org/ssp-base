<?php

use PHPUnit\Framework\Assert;

class SilDiscoContext extends FeatureContext
{
    /**
     * @When I log in using my :idp credentials
     */
    public function iLogInUsingMyIdpCredentials($idp)
    {
        switch ($idp) {
            case 'IDP 1':
                $this->username = 'sildisco_idp1';
                $this->password = 'sildisco_password';
                break;

            case 'IDP 2':
                $this->username = 'sildisco_idp2';
                $this->password = 'sildisco_password';
                break;

            case 'IDP 3':
                $this->username = 'admin';
                $this->password = 'c';
                break;

            default:
                throw new \Exception('credential name not recognized');
        }
        $this->iLogIn();
    }

    /**
     * @Then I should see my attributes on :sp
     */
    public function iShouldSeeMyAttributesOnSp($sp)
    {
        $currentUrl = $this->session->getCurrentUrl();
        Assert::assertStringStartsWith(
            'http://ssp-' . strtolower($sp),
            $currentUrl,
            'Did NOT end up at ' . $sp
        );
        $this->assertPageContainsText('Your attributes');
    }

    /**
     * @When I login using password :password
     */
    public function iLoginUsingPassword($password)
    {
        $this->logInAs('admin', $password);
    }

    /**
     * @Given I have authenticated with IDP1 for :sp
     */
    public function iHaveAuthenticatedWithIdp1($sp)
    {
        $this->iGoToTheSpLoginPage($sp);
        $this->iClickOnTheTile('IDP 1');
        $this->username = 'sildisco_idp1';
        $this->password = 'sildisco_password';
        $this->iLogIn();
    }

    /**
     * @Given I have authenticated with IDP2 for :sp
     */
    public function iHaveAuthenticatedWithIdp2($sp)
    {
        $this->iGoToTheSpLoginPage($sp);
        if ($sp != "SP2") { // SP2 only has IDP2 in its IDPList
            $this->iClickOnTheTile('IDP 2');
        }
        $this->username = 'sildisco_idp2';
        $this->password = 'sildisco_password';
        $this->iLogIn();
    }

    /**
     * @When I log out of IDP1
     */
    public function iLogOutOfIdp1()
    {
        $this->iGoToTheSpLoginPage('SP3');
        $this->iClickOnTheTile('IDP 1');
        $this->clickLink('Logout');
        $this->assertPageContainsText('You have now been logged out.');
    }

    /**
     * @When I log out of IDP2
     */
    public function iLogOutOfIdp2()
    {
        $this->iGoToTheSpLoginPage('SP2');
        $this->clickLink('Logout');
        $this->assertPageContainsText('You have now been logged out.');
    }

    /**
     * @Then I should see the metadata in XML format
     */
    public function iShouldSeeTheMetadataInXmlFormat()
    {
        $contentType = $this->session->getResponseHeader('Content-Type');
        Assert::assertEquals('application/xml', $contentType);

        Assert::assertEquals(200, $this->session->getStatusCode());

        $xml = file_get_contents($this->getSession()->getCurrentUrl());
        Assert::assertStringContainsString(
            'entityID="ssp-hub.local"',
            $xml,
            "page doesn't contain entityID"
        );
    }

}
