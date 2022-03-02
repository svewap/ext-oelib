<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper that is broken because it has no model name defined.
 *
 * @phpstan-ignore-next-line We explicitly test for a contract violation here.
 */
class ModelLessTestingMapper extends AbstractDataMapper
{
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_test';
}
