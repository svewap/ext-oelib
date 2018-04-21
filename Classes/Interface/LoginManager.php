<?php

/**
 * This interface represents a manager for logins, providing access to the logged-in user.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Oelib_Interface_LoginManager
{
    /**
     * Returns an instance of this class.
     *
     * @return Tx_Oelib_Interface_LoginManager the current Singleton instance
     */
    public static function getInstance();

    /**
     * Purges the current instance so that getInstance will create a new
     * instance.
     *
     * @return void
     */
    public static function purgeInstance();

    /**
     * Checks whether a user is logged in.
     *
     * @return bool TRUE if a user is logged in, FALSE otherwise
     */
    public function isLoggedIn();

    /**
     * Gets the currently logged-in user.
     *
     * @param string $mapperName
     *        the name of the mapper to use for getting the user model, must not be empty
     *
     * @return Tx_Oelib_Model the logged-in user, will be NULL if no user is
     *                        logged in
     */
    public function getLoggedInUser($mapperName = '');
}
