<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interface represents a manager for logins, providing access to the logged-in user.
 *
 * @template T of LoginManager
 *
 * @deprecated will be removed in oelib 6.0
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
     * Returns the UID of the currently logged-in user.
     *
     * @return int will be zero if no user is logged in
     */
    public function getLoggedInUserUid(): int;
}
