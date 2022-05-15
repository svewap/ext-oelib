<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\FrontEnd;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * This XCLASS makes sure no FE login cookies are sent during the unit tests.
 */
class UserWithoutCookies extends FrontendUserAuthentication
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
     */
    protected function setSessionCookie(): void
    {
    }

    /**
     * Unsets no cookie at all.
     *
     * @param mixed $cookieName
     */
    public function removeCookie($cookieName = null): void
    {
    }
}
