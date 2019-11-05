<?php
declare(strict_types = 1);

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * This XCLASS makes sure no FE login cookies are sent during the unit tests.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_FrontEnd_UserWithoutCookies extends FrontendUserAuthentication
{
    /**
     * @var bool
     */
    public $forceSetCookie = false;

    /**
     * @var bool
     */
    public $dontSetCookie = true;

    /**
     * Sets no session cookie at all.
     *
     * @return void
     */
    protected function setSessionCookie()
    {
    }

    /**
     * Unsets no cookie at all.
     *
     * @param string $cookieName
     *
     * @return void
     */
    public function removeCookie($cookieName)
    {
    }
}
