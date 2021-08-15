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
class ColumnLessTestingMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_test';

    /**
     * @var string a comma-separated list of DB column names to retrieve or "*" for all columns
     */
    protected $columns = '';

    /**
     * @var class-string<TestingModel> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TestingModel::class;
}
