<?php

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use DMore\ChromeDriver\ChromeDriver;
use Webmozart\Assert\Assert;

class FeatureContext extends MinkContext
{
    private const HUB_BAD_AUTH_SOURCE_URL = 'http://ssp-hub.local/module.php/admin/test/wrong';
    private const HUB_DISCO_URL = 'http://ssp-hub.local/module.php/admin/test/hub-discovery';
    private const HUB_ADMIN_URL = 'http://ssp-hub.local/admin';
    protected const SP1_LOGIN_PAGE = 'http://ssp-sp1.local/module.php/sildisco/authenticate.php?as=ssp-hub';
    protected const SP2_LOGIN_PAGE = 'http://ssp-sp2.local/module.php/sildisco/authenticate.php?as=ssp-hub';
    protected const SP3_LOGIN_PAGE = 'http://ssp-sp3.local/module.php/sildisco/authenticate.php?as=ssp-hub';

    const SCREENSHOTS_PATH = '/data/features/screenshots/';

    /** @var Session */
    protected $session;

    protected $username = null;
    protected $password = null;

    public function __construct()
    {
        $driver = new ChromeDriver('http://test-browser:9222', null, 'http://ssp-hub.local');
        $this->session = new Session($driver);
        $mink = new Mink(['default' => $this->session]);
        $mink->setDefaultSessionName('default');
        $this->setMink($mink);
        // See http://mink.behat.org/en/latest/guides/session.html for docs.
        $this->session->start();
    }

    /** @AfterStep */
    public function afterStep(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() === StepResult::FAILED) {
            $this->showPageDetails();
            $this->takeScreenshot();
        }
    }

    /**
     * Store a screenshot.
     */
    private function takeScreenshot()
    {
        $screenshot = $this->getSession()->getDriver()->getScreenshot();
        if (!is_dir(self::SCREENSHOTS_PATH)) {
            mkdir(self::SCREENSHOTS_PATH);
        }
        if (is_dir(self::SCREENSHOTS_PATH)) {
            $path = self::SCREENSHOTS_PATH . date('Y-m-d_H:i:s_') . uniqid() . '.png';
            file_put_contents($path, $screenshot);
            print "\n\nScreenshot: " . $path;
        }
    }

    protected function showPageDetails()
    {
        echo '[' . $this->session->getStatusCode() . '] ';
        $this->printLastResponse();
    }

    /**
     * @When I go to the Hub's discovery page
     */
    public function iGoToTheHubsDiscoveryPage()
    {
        $this->visit(self::HUB_DISCO_URL);
    }

    /**
     * @Then I should see our material theme
     */
    public function iShouldSeeOurMaterialTheme()
    {
        $page = $this->session->getPage();
        $hasMaterialDesignElement = $page->has('css', '.mdl-layout');
        Assert::true(
            $hasMaterialDesignElement,
            'Failed to find the expected evidence of our material theme'
        );
    }

    /**
     * @When I go to the Hub's home page
     */
    public function iGoToTheHubsHomePage()
    {
        $this->visit(self::HUB_ADMIN_URL);
    }

    /**
     * @When I click on :linkText
     */
    public function iClickOn($linkText)
    {
        $this->clickLink($linkText);
    }

    /**
     * @When I log in as a hub administrator
     */
    public function iLogInAsAHubAdministrator()
    {
        $this->logInAs('admin', 'abc123');
    }

    /**
     * @When I provide a username and an incorrect password
     */
    public function iProvideAUsernameAndAnIncorrectPassword()
    {
        $this->username = "sildisco_idp2";
        $this->password = "not_correct";
    }

    protected function logInAs(string $username, string $password)
    {
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
    }

    /**
     * @When I go to the Hub but specify an invalid authentication source
     */
    public function iGoToTheHubButSpecifyAnInvalidAuthenticationSource()
    {
        $this->visit(self::HUB_BAD_AUTH_SOURCE_URL);
    }

    /**
     * @Then I should see a(n) :title page
     */
    public function iShouldSeeAPage($title)
    {
        $page = $this->session->getPage();
        $titleElement = $page->find('css', 'head > title');
        Assert::notNull($titleElement, "Could not find the page's title");
        Assert::same(
            $titleElement->getText(),
            $title,
            "This does not seem to be a '$title' page"
        );
    }

    /**
     * @When I click on the :idpName tile
     */
    public function iClickOnTheTile($idpName)
    {
        $page = $this->session->getPage();
        $idpTileTitle = sprintf('%s Sign in', $idpName);
        $idpTile = $page->find(
            'css',
            sprintf('div[title="%s"]', $idpTileTitle)
        );
        Assert::notNull($idpTile, 'Failed to find ' . $idpName . ' tile');
        $button = $idpTile->find('css', 'button');
        Assert::notNull($button, 'Failed to find button for ' . $idpName);
        $button->press();
    }

    /**
     * @When I go to the :sp login page
     */
    public function iGoToTheSpLoginPage($sp)
    {
        switch ($sp) {
            case 'SP1':
                $this->visit(self::SP1_LOGIN_PAGE);
                break;
            case 'SP2':
                $this->visit(self::SP2_LOGIN_PAGE);
                break;
            case 'SP3':
                $this->visit(self::SP3_LOGIN_PAGE);
                break;
        }
    }

    protected function assertPageBodyContainsText(string $expectedText)
    {
        $page = $this->session->getPage();
        $body = $page->find('css', 'body');
        Assert::contains($body->getText(), $expectedText);
    }

    private static function ensureFolderExistsForTestFile($filePath)
    {
        $folder = dirname($filePath);
        if (!file_exists($folder)) {
            $result = mkdir($folder, 0777, true);
            Assert::notFalse($result, 'Failed to create test folder: ' . $folder);
        }
    }

    /**
     * @Given a :filePath file containing
     */
    public function aFileContaining($filePath, PyStringNode $json)
    {
        self::ensureFolderExistsForTestFile($filePath);
        $result = file_put_contents($filePath, $json);
        Assert::notFalse($result, 'Failed to write test file at ' . $filePath);
    }

    /**
     * @When I go to the :path folder and apply the dictionary overrides
     */
    public function iGoToTheFolderAndApplyTheDictionaryOverrides($path)
    {
        $previousWorkingDirectory = getcwd();
        Assert::notFalse($previousWorkingDirectory, 'Failed to get current working directory.');
        $cdResult = chdir($path);
        Assert::notFalse($cdResult, 'Failed to cd into ' . $path);
        try {
            require('/data/apply-dictionaries-overrides.php'); // Path within Docker image. See Dockerfile.
        } finally {
            chdir($previousWorkingDirectory);
        }
    }

    /**
     * @Then the :filePath file should contain
     */
    public function theFileShouldContain($filePath, PyStringNode $expectedJson)
    {
        $actualJson = file_get_contents($filePath);
        Assert::notFalse($actualJson, 'Failed to read in ' . $filePath);
        Assert::eq(
            json_decode($actualJson, true),
            json_decode($expectedJson, true)
        );
    }

    /**
     * Get the login button from the given page.
     *
     * @param DocumentElement $page The page.
     * @return NodeElement
     */
    protected function getLoginButton($page)
    {
        $buttons = $page->findAll('css', 'button');
        $loginButton = null;
        foreach ($buttons as $button) {
            $lcButtonText = strtolower($button->getText());
           if (strpos($lcButtonText, 'login') !== false || strpos($lcButtonText, 'sign in') !== false) {
                $loginButton = $button;
                break;
            }
        }
        Assert::notNull($loginButton, 'Failed to find the login button');
        return $loginButton;
    }

    /**
     * @When I log in
     */
    public function iLogIn()
    {
        $page = $this->session->getPage();
        try {
            $page->fillField('username', $this->username);
            $page->fillField('password', $this->password);
            $this->submitLoginForm($page);
        } catch (ElementNotFoundException $e) {
            Assert::true(false, sprintf(
                "Did not find that element in the page.\nError: %s",
                $e->getMessage()
            ));
        }
    }

    /**
     * @Given I have logged in (again)
     */
    public function iHaveLoggedIn()
    {
        $this->iLogin();
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
        Assert::notNull($button, 'Failed to find button named ' . $buttonName);
        $button->click();
        $this->submitSecondarySspFormIfPresent($page);
    }

    /**
     * Submit the login form, including the secondary page's form (if
     * simpleSAMLphp shows another page because JavaScript isn't supported).
     *
     * @param DocumentElement $page The page.
     */
    protected function submitLoginForm($page)
    {
        $loginButton = $this->getLoginButton($page);
        $loginButton->click();
        $this->submitSecondarySspFormIfPresent($page);
    }

    /**
     * Submit the secondary page's form (if simpleSAMLphp shows another page
     * because JavaScript isn't supported).
     *
     * @param DocumentElement $page The page.
     */
    protected function submitSecondarySspFormIfPresent($page)
    {
        // SimpleSAMLphp 1.15 markup for secondary page:
        $postLoginSubmitButton = $page->findButton('postLoginSubmitButton');
        if ($postLoginSubmitButton instanceof NodeElement) {
            $postLoginSubmitButton->click();
        } else {

            // SimpleSAMLphp 1.14 markup for secondary page:
            $body = $page->find('css', 'body');
            if ($body instanceof NodeElement) {
                $onload = $body->getAttribute('onload');
                if ($onload === "document.getElementsByTagName('input')[0].click();") {
                    $body->pressButton('Submit');
                }
            }
        }
    }

    /**
     * @Then I should end up at my intended destination
     */
    public function iShouldEndUpAtMyIntendedDestination()
    {
        $this->assertPageBodyContainsText('Your attributes');
    }
}
