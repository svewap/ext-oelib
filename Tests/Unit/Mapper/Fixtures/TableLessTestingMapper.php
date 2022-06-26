<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Testing mapper without a table name.
 *
 * @extends AbstractDataMapper<TestingModel>
 */
class TableLessTestingMapper extends AbstractDataMapper
{
    protected $modelClassName = TestingModel::class;
}
