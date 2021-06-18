<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * Dummy configuration for usage in tests (in place of any configuration: TypoScript, flexforms, extension manager).
 */
final class DummyConfiguration extends AbstractObjectWithPublicAccessors implements Configuration
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Sets all data.
     *
     * @return void
     */
    public function setAllData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
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
     * Use `setData` if you want to set all data in one step.
     *
     * @param string $key the key of the data item to get, must not be empty
     * @param mixed $value the data for the key $key
     *
     * @return void
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
