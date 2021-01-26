<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;

/**
 * This class represents a set of configuration options within a certain
 * namespace.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TypoScriptConfiguration extends AbstractObjectWithPublicAccessors
{
    /**
     * @var array the data for this configuration
     */
    private $data = [];

    /**
     * Sets the complete data for this configuration.
     *
     * This function can be called multiple times.
     *
     * @param array<string, mixed> $data
     *        the data for this configuration, may be empty
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
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
    public function set($key, $value)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1331318809);
        }

        $this->data[$key] = $value;
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
    protected function get(string $key)
    {
        if (!$this->existsKey($key)) {
            return '';
        }

        return $this->data[$key];
    }

    /**
     * Checks whether a data item with a certain key exists.
     *
     * @param string $key
     *        the key of the data item to check, must not be empty
     *
     * @return bool TRUE if a data item with the key $key exists, FALSE
     *                 otherwise
     */
    protected function existsKey(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Returns the array keys of the data item for the key $key.
     *
     * If $key is an empty string the array keys of $this->data are returned.
     *
     * @param string $key
     *        the key of the data item to get the array keys for, may be empty
     *
     * @return string[] the array keys of the data item for the key $key, may be empty
     */
    public function getArrayKeys(string $key = ''): array
    {
        if ($key === '') {
            return array_keys($this->data);
        }

        if (!$this->existsKey($key) || !is_array($this->data[$key])) {
            return [];
        }

        return array_keys($this->data[$key]);
    }

    /**
     * Returns the data for the key $key as a multidimensional array.
     *
     * The return value will be an empty array:
     * - if the data item is an empty array,
     * - if the data item is not an array,
     * - if the key does not exist in $this->data.
     *
     * @param string $key
     *        the key of the data item to get as a multidimensional array, must
     *        not be empty
     *
     * @return array the data for the key $key as a multidimensional array, may
     *               be empty
     */
    public function getAsMultidimensionalArray(string $key): array
    {
        if (!isset($this->data[$key]) || !is_array($this->data[$key])) {
            return [];
        }

        return $this->data[$key];
    }
}
