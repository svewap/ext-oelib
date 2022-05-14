<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Authentication;

use OliverKlee\Oelib\Interfaces\LoginManager;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
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
        if (!self::$instance instanceof BackEndLoginManager) {
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
     * Gets the currently logged-in user.
     *
     * @template M of AbstractModel
     *
     * @param class-string<AbstractDataMapper<M>> $mapperName mapper to use for getting the user model
     *
     * @return M|null the logged-in user, will be null if no user is logged in
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated will be removed in oelib 5.0
     */
    public function getLoggedInUser(string $mapperName = BackEndUserMapper::class): ?AbstractModel
    {
        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        if ($mapperName === '') {
            throw new \InvalidArgumentException('$mapperName must not be empty.', 1331318483);
        }

        /** @var M|null $loggedInUser */
        $loggedInUser = $this->loggedInUser;
        if ($loggedInUser instanceof AbstractModel) {
            return $loggedInUser;
        }

        if (!$this->isLoggedIn()) {
            return null;
        }

        /** @var M $loggedInUser */
        $loggedInUser = MapperRegistry::get($mapperName)->find($this->getLoggedInUserUid());
        $this->loggedInUser = $loggedInUser;

        return $loggedInUser;
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
