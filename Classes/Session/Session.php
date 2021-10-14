<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Session;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This Singleton class represents a session and its data.
 */
class Session extends AbstractObjectWithPublicAccessors
{
    /**
     * @var int session type for persistent data that is stored for the
     *              logged-in front-end user and will be available when the
     *              user logs in again
     */
    public const TYPE_USER = 1;

    /**
     * @var int session type for volatile data that will be deleted when
     *              the session cookie is dropped (when the browser is closed)
     */
    public const TYPE_TEMPORARY = 2;

    /**
     * @var array<int, string> available type codes for the FE session functions
     */
    private static $types = [
        self::TYPE_USER => 'user',
        self::TYPE_TEMPORARY => 'ses',
    ];

    /**
     * @var int the type of this session (::TYPE_USER or ::TYPE_TEMPORARY)
     */
    private $type = 0;

    /**
     * @var array<int, Session> the instances, using the type as key
     */
    private static $instances = [];

    /**
     * The constructor. Use getInstance() instead.
     *
     * @param int $type the type of the session to use; either TYPE_USER or TYPE_TEMPORARY
     *
     * @throws \BadMethodCallException if there is no front end
     */
    protected function __construct(int $type)
    {
        if (!$this->getFrontEndController() instanceof TypoScriptFrontendController) {
            throw new \BadMethodCallException(
                'This class must not be instantiated when there is no front end.',
                1331489053
            );
        }

        self::checkType($type);
        $this->type = $type;
    }

    /**
     * Returns an instance of this class.
     *
     * @param int $type
     *        the type of the session to use; either TYPE_USER (persistent)
     *        or TYPE_TEMPORARY (only for the lifetime of the session cookie)
     *
     * @return Session the current Singleton instance for the given
     *                          type
     */
    public static function getInstance(int $type): Session
    {
        self::checkType($type);

        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = new Session($type);
        }

        return self::$instances[$type];
    }

    /**
     * Sets the instance for the given type.
     *
     * @param int $type the type to set, must be either TYPE_USER or TYPE_TEMPORARY
     * @param Session $instance the instance to set
     */
    public static function setInstance(int $type, Session $instance): void
    {
        self::checkType($type);

        self::$instances[$type] = $instance;
    }

    /**
     * Checks that a type ID is valid.
     *
     * @param int $type the type ID to check
     *
     * @throws \InvalidArgumentException if $type is neither ::TYPE_USER nor ::TYPE_TEMPORARY
     */
    protected static function checkType(int $type): void
    {
        if (($type !== self::TYPE_USER) && ($type !== self::TYPE_TEMPORARY)) {
            throw new \InvalidArgumentException(
                'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.',
                1331489067
            );
        }
    }

    /**
     * Purges the instances of all types so that getInstance will create new instances.
     */
    public static function purgeInstances(): void
    {
        self::$instances = [];
    }

    /**
     * Gets the value of the data item for the key `$key`.
     *
     * @param string $key the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key `$key`, will be an empty string if the key has not been set yet
     */
    protected function get(string $key)
    {
        $user = $this->getFrontEndUser();
        if (!$user instanceof FrontendUserAuthentication) {
            return '';
        }

        return $user->getKey(self::$types[$this->type], $key);
    }

    /**
     * Sets the value of the data item for the key `$key`.
     *
     * @param string $key the key of the data item to get, must not be empty
     * @param mixed $value the data for the key `$key`
     */
    protected function set(string $key, $value): void
    {
        $user = $this->getFrontEndUser();
        if (!$user instanceof FrontendUserAuthentication) {
            return;
        }

        $user->setKey(self::$types[$this->type], $key, $value);
        $user->storeSessionData();
    }

    protected function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    private function getFrontEndUser(): ?FrontendUserAuthentication
    {
        $frontEndController = $this->getFrontEndController();
        if (!$frontEndController instanceof TypoScriptFrontendController) {
            return null;
        }

        $user = $frontEndController->fe_user;
        return $user instanceof FrontendUserAuthentication ? $user : null;
    }
}
