<?php

/**
 * This class represents an object for testing purposes.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_Tests_Unit_Fixtures_TestingObject extends \Tx_Oelib_PublicObject
{
    /**
     * @var array the data for this object
     */
    private $data = [];

    /**
     * Frees as much memory that has been used by this object as possible.
     */
    public function __destruct()
    {
        unset($this->data);
    }

    /**
     * Sets the data of this object.
     *
     * @param array $data the data to set, may be empty
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key
     *        the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key $key, will be an empty string
     *               if the key has not been set yet
     */
    protected function get($key)
    {
        if (!isset($this->data[$key])) {
            return '';
        }

        return $this->data[$key];
    }

    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key
     *        the key of the data item to get, must not be empty
     * @param mixed $value
     *        the data for the key $key
     *
     * @return void
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks that $key is not empty.
     *
     * @throws \InvalidArgumentException if $key is empty
     *
     * @param string $key
     *        a key to check
     *
     * @return void
     */
    public function checkForNonEmptyKey($key)
    {
        parent::checkForNonEmptyKey($key);
    }
}
