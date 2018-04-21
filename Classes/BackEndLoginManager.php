<?php

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This class represents a manager for back-end logins, providing access to the logged-in user.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_BackEndLoginManager implements Tx_Oelib_Interface_LoginManager
{
    /**
     * @var Tx_Oelib_BackEndLoginManager the Singleton instance
     */
    private static $instance = null;

    /**
     * @var Tx_Oelib_Model_BackEndUser a fake logged-in back-end user
     */
    private $loggedInUser = null;

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Frees as much memory that has been used by this object as possible.
     */
    public function __destruct()
    {
        $this->loggedInUser = null;
    }

    /**
     * Returns an instance of this class.
     *
     * @return Tx_Oelib_BackEndLoginManager the current Singleton instance
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Tx_Oelib_BackEndLoginManager();
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
     * @return bool TRUE if a back-end user is logged in, FALSE otherwise
     */
    public function isLoggedIn()
    {
        if ($this->loggedInUser) {
            return true;
        }

        return $this->getBackEndUserAuthentication() !== null;
    }

    /**
     * Gets the currently logged-in back-end user.
     *
     * @param string $mapperName
     *        the name of the mapper to use for getting the back-end user model, must not be empty
     *
     * @return Tx_Oelib_Model_BackEndUser the logged-in back-end user, will be NULL if no user is logged in
     */
    public function getLoggedInUser($mapperName = Tx_Oelib_Mapper_BackEndUser::class)
    {
        if ($mapperName === '') {
            throw new InvalidArgumentException('$mapperName must not be empty.', 1331318483);
        }
        if (!$this->isLoggedIn()) {
            return null;
        }
        if ($this->loggedInUser) {
            return $this->loggedInUser;
        }

        /** @var Tx_Oelib_Mapper_BackEndUser $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get($mapperName);

        /** @var Tx_Oelib_Model_BackEndUser $user */
        $user = $mapper->find($this->getBackEndUserAuthentication()->user['uid']);
        return $user;
    }

    /**
     * Sets the currently logged-in back-end user.
     *
     * This function is for testing purposes only!
     *
     * @param Tx_Oelib_Model_BackEndUser $loggedInUser
     *        the fake logged-in back-end user
     *
     * @return void
     */
    public function setLoggedInUser(Tx_Oelib_Model_BackEndUser $loggedInUser)
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
        return isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }
}
