<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * This class represents a mapper that is broken because it has no columns defined.
 *
 * @extends AbstractDataMapper<TestingModel>
 */
final class ColumnLessTestingMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_oelib_test';

    // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
    protected $columns = '';

    protected $modelClassName = TestingModel::class;
}
