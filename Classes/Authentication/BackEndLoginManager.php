<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Authentication;

use OliverKlee\Oelib\Interfaces\LoginManager;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This class represents a manager for back-end logins, providing access to the logged-in user.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndLoginManager implements LoginManager
{
    /**
     * @var BackEndLoginManager the Singleton instance
     */
    private static $instance = null;

    /**
     * @var BackEndUser a logged-in back-end user (real or faked)
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
     * @return static the current Singleton instance
     */
    public static function getInstance(): BackEndLoginManager
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new
     * instance.
     *
     * @return void
     */
    public static function purgeInstance()
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
        return $this->loggedInUser instanceof BackEndUser
            || $this->getBackEndUserAuthentication() instanceof BackendUserAuthentication;
    }

    /**
     * Gets the currently logged-in back-end user.
     *
     * @param string $mapperName
     *        the name of the mapper to use for getting the back-end user model, must not be empty
     *
     * @return BackEndUser|null the logged-in back-end user, will be null if no user is logged in
     *
     * @throws \InvalidArgumentException
     */
    public function getLoggedInUser(string $mapperName = BackEndUserMapper::class)
    {
        if ($mapperName === '') {
            throw new \InvalidArgumentException('$mapperName must not be empty.', 1331318483);
        }
        if (!$this->isLoggedIn()) {
            return null;
        }
        if ($this->loggedInUser instanceof BackEndUser) {
            return $this->loggedInUser;
        }

        /** @var BackEndUserMapper $mapper */
        $mapper = MapperRegistry::get($mapperName);
        $this->loggedInUser = $mapper->find((int)$this->getBackEndUserAuthentication()->user['uid']);

        return $this->loggedInUser;
    }

    /**
     * Sets the currently logged-in back-end user.
     *
     * This function is for testing purposes only!
     *
     * @param BackEndUser $loggedInUser the fake logged-in back-end user
     *
     * @return void
     */
    public function setLoggedInUser(BackEndUser $loggedInUser)
    {
        $this->loggedInUser = $loggedInUser;
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication|null
     */
    protected function getBackEndUserAuthentication()
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}
