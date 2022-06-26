<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper that is broken because it has no model name defined.
 */
class ModelLessTestingMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_oelib_test';
}
