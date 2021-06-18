<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures\Fixtures;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;

final class TestingObjectWithPublicAccessors extends AbstractObjectWithPublicAccessors
{
    /**
     * @var array<string, mixed> the data for this object
     */
    private $data = [];

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
     * @return mixed the data for the key $key, will be null if the key has not been set yet
     */
    protected function get(string $key)
    {
        return $this->data[$key] ?? null;
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
     * @param string $key
     *        a key to check
     *
     * @return void
     *
     * @throws \InvalidArgumentException if $key is empty
     */
    public function checkForNonEmptyKey(string $key)
    {
        parent::checkForNonEmptyKey($key);
    }
}
