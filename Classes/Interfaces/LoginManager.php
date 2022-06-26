<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This interface represents a manager for logins, providing access to the logged-in user.
 *
 * @template T of LoginManager
 */
interface LoginManager
{
    /**
     * Returns an instance of this class.
     *
     * @return T the current Singleton instance
     */
    public static function getInstance();

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void;

    /**
     * Checks whether a user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * Gets the currently logged-in user.
     *
     * @template M of AbstractModel
     *
     * @param class-string<AbstractDataMapper<M>> $mapperName mapper to use for getting the user model
     *
     * @return M|null the logged-in user, will be null if no user is logged in
     *
     * @deprecated will be removed in oelib 5.0
     */
    public function getLoggedInUser(string $mapperName): ?AbstractModel;

    /**
     * Returns the UID of the currently logged-in user.
     *
     * @return int will be zero if no user is logged in
     */
    public function getLoggedInUserUid(): int;
}
