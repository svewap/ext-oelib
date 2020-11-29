<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Session;

/**
 * This class represents a fake session that doesn't use any real sessions,
 * thus not sending any HTTP headers.
 *
 * It is intended for testing purposes.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FakeSession extends Session
{
    /**
     * @var array the data for this session
     */
    private $sessionData = [];

    /**
     * The constructor.
     *
     * This constructor is public to allow direct instantiation of this class
     * for the unit tests, also bypassing the check for a front end.
     *
     * @param int $type
     */
    public function __construct(int $type = 0)
    {
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
        return $this->sessionData[$key] ?? '';
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
        $this->sessionData[$key] = $value;
    }
}
