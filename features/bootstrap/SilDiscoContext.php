<?php

use PHPUnit\Framework\Assert;

class SilDiscoContext extends FeatureContext
{
    protected const SP1_LOGOUT_PAGE = 'https://ssp-sp1.local/module.php/core/logout/ssp-hub';

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
                throw new Exception('credential name not recognized');
        }
        $this->iLogIn();
    }

    /**
     * @Then I should end up at my intended destination on :sp
     */
    public function iShouldEndUpAtMyIntendedDestinationOnSp($sp)
    {
        $this->waitForPage('module.php/core/welcome');
        $this->assertIAmOn($sp);
        $this->assertPageContainsText('This is a landing page for the Identity Provider.');
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

        $this->waitForPage('module.php/core/loginuserpass');

        $this->username = 'sildisco_idp1';
        $this->password = 'sildisco_password';
        $this->iLogIn();

        $this->waitForPage('module.php/core/welcome');
    }

    /**
     * @Given I have authenticated with IDP2 for :sp
     */
    public function iHaveAuthenticatedWithIdp2($sp)
    {
        $this->iGoToTheSpLoginPage($sp);
        if (!in_array($sp, ["SP2", "SP4"])) { // SP2 & SP4 only has IDP2 in its IDPList
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
        $this->visit(self::SP1_LOGOUT_PAGE);

        $this->waitForPage('module.php/core/welcome');
    }

    /**
     * @Given I am visiting :sp
     */
    public function iAmVisiting($sp) {
        $this->waitForPage('module.php/core/welcome');

        $this->assertIAmOn($sp);
    }

    /**
     * @Given I remove session cookies for the current SP
     * 
     * Prerequisite: we have performed a visit to the current SP
     * This removes the two SSP cookies for the site we are currently sitting on
     */
    public function iHaveLoggedOutOfTheCurrentSP()
    {
        $session = $this->getSession();
        $session->setCookie('SSPAUTHTOKEN', null);
        $session->setCookie('SimpleSAML', null);
    }

    /**
     * @Then I should be prompted for a username and password
     */
    public function iShouldBePromptedForAUsernameAndPassword()
    {
        $this->waitForPage('module.php/core/loginuserpass');

        $this->assertPageBodyContainsText('Enter your username and password');
    }

    /**
     * @Then I should be prompted for a username and password on :idp
     */
    public function iShouldBePromptedForAUsernameAndPasswordOn($idp)
    {
        $this->waitForPage('module.php/silauth/loginuserpass');

        // Turn "idp3" to "IDP 3"
        $idpName = substr_replace(strtoupper($idp), " ", 3, 0);

        $this->assertPageBodyContainsText($idpName . ' Sign in');
    }

    /**
     * @Then I should see the metadata in XML format
     */
    public function iShouldSeeTheMetadataInXmlFormat()
    {
        $contentType = $this->getSession()->getResponseHeader('Content-Type');
        Assert::assertEquals('application/xml', $contentType);

        Assert::assertEquals(200, $this->getSession()->getStatusCode());

        $sslContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $xml = file_get_contents($this->getSession()->getCurrentUrl(), false, $sslContext);
        Assert::assertNotFalse($xml, "Could not retrieve metadata XML");
        Assert::assertStringContainsString(
            'entityID="ssp-hub.local"',
            $xml,
            "page doesn't contain entityID"
        );
    }

    /**
     * Asserts if we are on a particular SP's domain
     */
    private function assertIAmOn($sp) {
        $currentUrl = $this->getSession()->getCurrentUrl();
        Assert::assertStringStartsWith(
            'https://ssp-' . strtolower($sp),
            $currentUrl,
            'Did NOT end up at ' . $sp
        );
    }

}
