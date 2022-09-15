<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Authentication;

use OliverKlee\Oelib\Interfaces\LoginManager;
use OliverKlee\Oelib\Model\AbstractModel;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This class represents a manager for back-end logins, providing access to the logged-in user.
 *
 * @implements LoginManager<BackEndLoginManager>
 */
class BackEndLoginManager implements LoginManager
{
    /**
     * @var BackEndLoginManager|null the Singleton instance
     */
    private static $instance = null;

    /**
     * @var AbstractModel|null a logged-in back-end user (real or faked)
     */
    private $loggedInUser = null;

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of this class.
     *
     * @return BackEndLoginManager the current Singleton instance
     */
    public static function getInstance(): BackEndLoginManager
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

    /**
     * Checks whether a back-end user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->loggedInUser instanceof AbstractModel
            || $this->getBackEndUserAuthentication() instanceof BackendUserAuthentication;
    }

    /**
     * Sets the currently logged-in back-end user.
     *
     * This function is for testing purposes only!
     *
     * @param AbstractModel $loggedInUser the fake logged-in back-end user
     */
    public function setLoggedInUser(AbstractModel $loggedInUser): void
    {
        $this->loggedInUser = $loggedInUser;
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     */
    protected function getBackEndUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
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

        $user = $this->getBackEndUserAuthentication();
        if (!$user instanceof BackendUserAuthentication) {
            return 0;
        }

        $userData = $user->user;

        return \is_array($userData) ? (int)$userData['uid'] : 0;
    }
}
