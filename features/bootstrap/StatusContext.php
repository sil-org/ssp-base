<?php

namespace Sil\SilAuth\features\context;

use Behat\Behat\Context\Context;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Webmozart\Assert\Assert;

class StatusContext implements Context
{
    private $responseCode = null;
    private $responseText = null;

    /**
     * @When I check the status of this module
     * @throws GuzzleException
     */
    public function iCheckTheStatusOfThisModule()
    {
        $client = new Client();
        $response = $client->get('http://testweb/module.php/silauth/status.php');
        $this->responseCode = $response->getStatusCode();
        $this->responseText = $response->getBody()->getContents();
    }

    /**
     * @Then I should get back a(n) :responseText with an HTTP status code of :statusCode
     */
    public function iShouldGetBackAWithAnHttpStatusCodeOf($responseText, $statusCode)
    {
        Assert::same($this->responseText, $responseText);
        Assert::eq($this->responseCode, $statusCode);
    }

    /**
     * @When I request the initial login page of this module
     */
    public function iRequestTheInitialLoginPageOfThisModule()
    {
        $client = new Client([
            'cookies' => true,
            'http_errors' => false,
        ]);
        $response = $client->get('http://testweb/module.php/core/authenticate.php?as=silauth');
        $this->responseCode = $response->getStatusCode();
    }

    /**
     * @Then I should get back an HTTP status code of :statusCode
     */
    public function iShouldGetBackAnHttpStatusCodeOf($statusCode)
    {
        Assert::eq($this->responseCode, $statusCode);
    }
}
