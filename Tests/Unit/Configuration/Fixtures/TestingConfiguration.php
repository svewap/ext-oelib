<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * Dummy configuration for usage in tests.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
final class TestingConfiguration extends AbstractObjectWithPublicAccessors implements Configuration
{
    /**
     * @var array
     */
    private $data = [];

    protected function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
