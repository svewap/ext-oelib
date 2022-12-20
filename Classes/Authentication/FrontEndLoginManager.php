<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Authentication;

use OliverKlee\Oelib\Interfaces\LoginManager;
use OliverKlee\Oelib\Model\AbstractModel;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a manager for front-end logins, providing access to the logged-in user.
 *
 * @implements LoginManager<FrontEndLoginManager>
 *
 * @deprecated will be removed in oelib 6.0
 */
class FrontEndLoginManager implements LoginManager
{
    /**
     * @var FrontEndLoginManager|null the Singleton instance
     */
    private static $instance;

    /**
     * the real or simulated logged-in user
     *
     * @var AbstractModel|null
     */
    private $loggedInUser;

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of this class.
     *
     * @return FrontEndLoginManager the current Singleton instance
     */
    public static function getInstance(): FrontEndLoginManager
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    private function getContext(): Context
    {
        return GeneralUtility::makeInstance(Context::class);
    }

    /**
     * Checks whether any front-end user is logged in (and whether a front end exists at all).
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        $isSimulatedLoggedIn = $this->loggedInUser instanceof AbstractModel;
        $sessionExists = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        return $isSimulatedLoggedIn || $sessionExists;
    }

    /**
     * Simulates a login of the user $user.
     *
     * This function is intended to be used for unit test only. Don't use it in the production code.
     *
     * @param AbstractModel|null $user the user to log in, set to NULL for no logged-in user
     */
    public function logInUser(AbstractModel $user = null): void
    {
        $this->loggedInUser = $user;
    }

    /**
     * Returns the UID of the currently logged-in user.
     *
     * @return int will be zero if no user is logged in
     */
    public function getLoggedInUserUid(): int
    {
        if ($this->loggedInUser instanceof AbstractModel) {
            return $this->loggedInUser->getUid();
        }

        return (int)$this->getContext()->getPropertyFromAspect('frontend.user', 'id');
    }
}
