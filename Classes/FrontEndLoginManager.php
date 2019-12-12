<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class represents a manager for front-end logins, providing access to the logged-in user.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_FrontEndLoginManager implements \Tx_Oelib_Interface_LoginManager
{
    /**
     * @var \Tx_Oelib_FrontEndLoginManager the Singleton instance
     */
    private static $instance = null;

    /**
     * the real or simulated logged-in user
     *
     * @var \Tx_Oelib_Model_FrontEndUser
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
    public static function getInstance()
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
        $isSimulatedLoggedIn = $this->loggedInUser instanceof \Tx_Oelib_Model_FrontEndUser;
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9004000) {
            $controller = $this->getFrontEndController();
            $sessionExists = $controller instanceof TypoScriptFrontendController && $controller->loginUser;
        } else {
            $sessionExists = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }

        return $isSimulatedLoggedIn || $sessionExists;
    }

    /**
     * Gets the currently logged-in front-end user.
     *
     * @param string $mapperName the name of the mapper to use for getting the front-end user model, must not be empty
     *
     * @return \Tx_Oelib_Model_FrontEndUser|null the logged-in front-end user
     *                                     will be null if no user is logged in or if there is no front end
     *
     * @throws \InvalidArgumentException
     */
    public function getLoggedInUser(string $mapperName = \Tx_Oelib_Mapper_FrontEndUser::class)
    {
        if ($mapperName === '') {
            throw new \InvalidArgumentException('$mapperName must not be empty.', 1331488730);
        }
        if ($this->loggedInUser instanceof \Tx_Oelib_Model_FrontEndUser) {
            return $this->loggedInUser;
        }
        if (!$this->isLoggedIn()) {
            return null;
        }

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9004000) {
            $uid = (int)$this->getFrontEndController()->fe_user->user['uid'];
        } else {
            $uid = (int)$this->getContext()->getPropertyFromAspect('frontend.user', 'id');
        }
        /** @var \Tx_Oelib_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get($mapperName);
        $this->loggedInUser = $mapper->find($uid);

        return $this->loggedInUser;
    }

    /**
     * Simulates a login of the user $user.
     *
     * This function is intended to be used for unit test only. Don't use it in the production code.
     *
     * @param \Tx_Oelib_Model_FrontEndUser|null $user the user to log in, set to NULL for no logged-in user
     *
     * @return void
     */
    public function logInUser(\Tx_Oelib_Model_FrontEndUser $user = null)
    {
        $this->loggedInUser = $user;
    }

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController|null
     */
    protected function getFrontEndController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
