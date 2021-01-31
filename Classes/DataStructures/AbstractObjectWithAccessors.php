<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an object that allows getting and setting its data,
 * but only via protected methods so that encapsulation is retained.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractObjectWithAccessors
{
    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key $key, will be null or an empty string if the key has not been set yet
     */
    abstract protected function get(string $key);

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
     * Checks that $key is not empty.
     *
     * @param string $key the key to check
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $key is empty
     */
    protected function checkForNonEmptyKey(string $key)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1331488963);
        }
    }

    /**
     * Gets the value stored in under the key $key, converted to a string.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return string the string value of the given key, may be empty
     */
    protected function getAsString(string $key): string
    {
        $this->checkForNonEmptyKey($key);

        return trim((string)$this->get($key));
    }

    /**
     * Checks whether a non-empty string is stored under the key $key.
     *
     * @param string $key the key of the element to check, must not be empty
     *
     * @return bool TRUE if the value for the given key is non-empty,
     *                 FALSE otherwise
     */
    protected function hasString(string $key): bool
    {
        return $this->getAsString($key) !== '';
    }

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
     * Gets the value stored in under the key $key, converted to an integer.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return int the integer value of the given key, may be positive,
     *                 negative or zero
     */
    protected function getAsInteger(string $key): int
    {
        $this->checkForNonEmptyKey($key);

        return (int)$this->get($key);
    }

    /**
     * Checks whether a non-zero integer is stored under the key $key.
     *
     * @param string $key the key of the element to check, must not be empty
     *
     * @return bool TRUE if the value for the given key is non-zero,
     *                 FALSE otherwise
     */
    protected function hasInteger(string $key): bool
    {
        return $this->getAsInteger($key) !== 0;
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
     * Gets the value stored in under the key $key, converted to an array of
     * trimmed strings.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return string[] the array value of the given key, may be empty
     */
    protected function getAsTrimmedArray(string $key): array
    {
        return GeneralUtility::trimExplode(',', $this->getAsString($key), true);
    }

    /**
     * Gets the value stored under the key $key, converted to an array of
     * integers.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return int[] the array value of the given key, may be empty
     */
    protected function getAsIntegerArray(string $key): array
    {
        $stringValue = $this->getAsString($key);

        if ($stringValue === '') {
            return [];
        }

        return GeneralUtility::intExplode(',', $stringValue);
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
     * Gets the value stored in under the key $key, converted to a boolean.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return bool the boolean value of the given key
     */
    protected function getAsBoolean(string $key): bool
    {
        $this->checkForNonEmptyKey($key);

        return (bool)$this->get($key);
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
     * Gets the value stored in under the key $key, converted to a float.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return float the float value of the given key, may be positive,
     *               negative or zero
     */
    protected function getAsFloat(string $key): float
    {
        $this->checkForNonEmptyKey($key);

        return (float)$this->get($key);
    }

    /**
     * Checks whether a non-zero float is stored under the key $key.
     *
     * @param string $key the key of the element to check, must not be empty
     *
     * @return bool TRUE if the value for the given key is non-zero,
     *                 FALSE otherwise
     */
    protected function hasFloat(string $key): bool
    {
        return $this->getAsFloat($key) !== 0.00;
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
