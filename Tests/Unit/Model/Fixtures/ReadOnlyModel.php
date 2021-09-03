<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

/**
 * This class represents a read-only model for testing purposes.
 */
final class ReadOnlyModel extends TestingModel
{
    /**
     * @var bool whether this model is read-only
     */
    protected $readOnly = true;
}
