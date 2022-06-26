<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;

/**
 * This class represents a mapper for a testing child model.
 *
 * @extends AbstractDataMapper<TestingChildModel>
 */
class TestingChildMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_oelib_testchild';

    protected $modelClassName = TestingChildModel::class;

    protected $relations = [
        'parent' => TestingMapper::class,
        'tx_oelib_parent2' => TestingMapper::class,
        'tx_oelib_parent3' => TestingMapper::class,
    ];
}
