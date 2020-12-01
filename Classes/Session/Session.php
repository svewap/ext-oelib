<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Session;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This Singleton class represents a session and its data.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Session extends AbstractObjectWithPublicAccessors
{
    /**
     * @var int session type for persistent data that is stored for the
     *              logged-in front-end user and will be available when the
     *              user logs in again
     */
    const TYPE_USER = 1;

    /**
     * @var int session type for volatile data that will be deleted when
     *              the session cookie is dropped (when the browser is closed)
     */
    const TYPE_TEMPORARY = 2;

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
     * @var array<int, \Session> the instances, using the type as key
     */
    private static $instances = [];

    /**
     * The constructor. Use getInstance() instead.
     *
     * @throws \BadMethodCallException if there is no front end
     *
     * @param int $type the type of the session to use; either TYPE_USER or TYPE_TEMPORARY
     */
    protected function __construct(int $type)
    {
        if ($this->getFrontEndController() === null) {
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
     *
     * @return void
     */
    public static function setInstance(int $type, Session $instance)
    {
        self::checkType($type);

        self::$instances[$type] = $instance;
    }

    /**
     * Checks that a type ID is valid.
     *
     * @throws \InvalidArgumentException if $type is neither ::TYPE_USER nor ::TYPE_TEMPORARY
     *
     * @param int $type the type ID to check
     *
     * @return void
     */
    protected static function checkType(int $type)
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
     *
     * @return void
     */
    public static function purgeInstances()
    {
        self::$instances = [];
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key $key, will be an empty string
     *               if the key has not been set yet
     */
    protected function get($key)
    {
        return $this->getFrontEndController()->fe_user->getKey(self::$types[$this->type], $key);
    }

    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     * @param mixed $value the data for the key $key
     *
     * @return void
     */
    protected function set($key, $value)
    {
        $this->getFrontEndController()->fe_user->setKey(self::$types[$this->type], $key, $value);
        $this->getFrontEndController()->fe_user->storeSessionData();
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
