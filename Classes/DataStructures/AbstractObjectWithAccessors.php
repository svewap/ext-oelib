<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

/**
 * This class represents an object that allows getting and setting its data,
 * but only via protected methods so that encapsulation is retained.
 */
abstract class AbstractObjectWithAccessors extends AbstractReadOnlyObjectWithAccessors
{
    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     * @param mixed $value the data for the key $key
     *
     * @return void
     */
    abstract protected function set($key, $value);

    /**
     * Sets a value for the key $key (and converts it to a string).
     *
     * @param string $key the key of the element to set, must not be empty
     * @param mixed $value the value to set, may be empty
     *
     * @return void
     */
    protected function setAsString(string $key, $value)
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (string)$value);
    }

    /**
     * Sets a value for the key $key (and converts it to an integer).
     *
     * @param string $key the key of the element to set, must not be empty
     * @param mixed $value the value to set, may be empty
     *
     * @return void
     */
    protected function setAsInteger(string $key, $value)
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (int)$value);
    }

    /**
     * Sets an array value for the key $key.
     *
     * Note: This function is intended for data that does not contain any
     * commas. Commas in the array elements cause getAsTrimmedArray and
     * getAsIntegerArray to split that element at the comma. This is a known
     * limitation.
     *
     * @param string $key the key of the element to set, must not be empty
     * @param array $value the value to set, may be empty
     *
     * @return void
     *
     * @see getAsIntegerArray
     * @see getAsTrimmedArray
     */
    protected function setAsArray(string $key, array $value)
    {
        $this->setAsString($key, implode(',', $value));
    }

    /**
     * Sets a value for the key $key (and converts it to a boolean).
     *
     * @param string $key the key of the element to set, must not be empty
     * @param mixed $value the value to set, may be empty
     *
     * @return void
     */
    protected function setAsBoolean(string $key, $value)
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (int)(bool)$value);
    }

    /**
     * Sets a value for the key $key (and converts it to a float).
     *
     * @param string $key the key of the element to set, must not be empty
     * @param mixed $value the value to set, may be empty
     *
     * @return void
     */
    protected function setAsFloat(string $key, $value)
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (float)$value);
    }
}
