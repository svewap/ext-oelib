<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Authentication;

use OliverKlee\Oelib\Interfaces\LoginManager;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\System\Typo3Version;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class represents a manager for front-end logins, providing access to the logged-in user.
 *
 * @implements LoginManager<FrontEndLoginManager>
 */
class FrontEndLoginManager implements LoginManager
{
    /**
     * @var FrontEndLoginManager|null the Singleton instance
     */
    private static $instance = null;

    /**
     * the real or simulated logged-in user
     *
     * @var FrontEndUser|null
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
     * @return FrontEndLoginManager the current Singleton instance
     */
    public static function getInstance(): FrontEndLoginManager
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
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        return $context;
    }

    /**
     * Checks whether any front-end user is logged in (and whether a front end exists at all).
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        $isSimulatedLoggedIn = $this->loggedInUser instanceof FrontEndUser;
        if (Typo3Version::isNotHigherThan(8)) {
            $controller = $this->getFrontEndController();
            // @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
            $sessionExists = $controller instanceof TypoScriptFrontendController && $controller->loginUser;
        } else {
            $sessionExists = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }

        return $isSimulatedLoggedIn || $sessionExists;
    }

    /**
     * Gets the currently logged-in front-end user.
     *
     * @param class-string<AbstractDataMapper> $mapperName
     *        the name of the mapper to use for getting the back-end user model, must not be empty
     *
     * @return FrontEndUser|null the logged-in front-end user
     *                                     will be null if no user is logged in or if there is no front end
     *
     * @throws \InvalidArgumentException
     */
    public function getLoggedInUser(string $mapperName = FrontEndUserMapper::class)
    {
        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        if ($mapperName === '') {
            throw new \InvalidArgumentException('$mapperName must not be empty.', 1331488730);
        }
        if ($this->loggedInUser instanceof FrontEndUser) {
            return $this->loggedInUser;
        }
        if (!$this->isLoggedIn()) {
            return null;
        }

        if (Typo3Version::isNotHigherThan(8)) {
            $uid = (int)$this->getFrontEndController()->fe_user->user['uid'];
        } else {
            $uid = (int)$this->getContext()->getPropertyFromAspect('frontend.user', 'id');
        }
        /** @var FrontEndUserMapper $mapper */
        $mapper = MapperRegistry::get($mapperName);
        $this->loggedInUser = $mapper->find($uid);

        return $this->loggedInUser;
    }

    /**
     * Simulates a login of the user $user.
     *
     * This function is intended to be used for unit test only. Don't use it in the production code.
     *
     * @param FrontEndUser|null $user the user to log in, set to NULL for no logged-in user
     *
     * @return void
     */
    public function logInUser(FrontEndUser $user = null)
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
