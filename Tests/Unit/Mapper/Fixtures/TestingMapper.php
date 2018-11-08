<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Testing mapper.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingMapper extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper,
     *             must not be empty in subclasses
     */
    protected $tableName = 'tx_oelib_test';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TestingModel::class;
}
