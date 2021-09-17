<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures\Fixtures;

use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;

/**
 * This class represents an object for testing purposes.
 */
final class TestingReadOnlyObjectWithPublicAccessors extends AbstractReadOnlyObjectWithPublicAccessors
{
    /**
     * @var array<string, mixed> the data for this object
     */
    private $data = [];

    /**
     * Sets the data of this object.
     *
     * @param array $data the data to set, may be empty
     */
    public function setData(array $data): void
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
     * Checks that `$key` is not empty.
     *
     * @param string $key a key to check
     *
     * @throws \InvalidArgumentException if $key is empty
     */
    public function checkForNonEmptyKey(string $key): void
    {
        parent::checkForNonEmptyKey($key);
    }
}
