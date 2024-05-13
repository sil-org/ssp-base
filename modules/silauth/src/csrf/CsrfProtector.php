<?php
namespace Sil\SilAuth\csrf;

use SimpleSAML\Session;

/**
 * Class for implementing CSRF protection, mostly based off of advice here:
 * http://stackoverflow.com/a/31683058/3813891
 */
class CsrfProtector
{
    protected $csrfSessionKey = 'silauth.csrfToken';
    protected $csrfTokenDataType = 'string';
    private $session;
    
    /**
     * Constructor.
     *
     * @param Session $session The session object.
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    
    public function changeMasterToken()
    {
        $newMasterToken = $this->generateToken();
        $this->setTokenInSession($newMasterToken);
    }
    
    protected function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Get the CSRF protection token from the session. If not found, a new one
     * will be generated and stored in the session.
     *
     * @return string The master (aka. authoritative) CSRF token.
     */
    public function getMasterToken()
    {
        $masterToken = $this->getTokenFromSession();
        if (empty($masterToken)) {
            $masterToken = $this->generateToken();
            $this->setTokenInSession($masterToken);
        }
        return $masterToken;
    }
    
    protected function getTokenFromSession()
    {
        return $this->session->getData(
            $this->csrfTokenDataType,
            $this->csrfSessionKey
        );
    }
    
    /**
     * Check the given CSRF token to see if it was correct.
     *
     * @param string $submittedToken The CSRF protection token provided by the
     *     HTTP request.
     * @return bool
     */
    public function isTokenCorrect($submittedToken)
    {
        return hash_equals($this->getMasterToken(), $submittedToken);
    }
    
    protected function setTokenInSession($masterToken)
    {
        $this->session->setData(
            $this->csrfTokenDataType,
            $this->csrfSessionKey,
            $masterToken
        );
    }
}
