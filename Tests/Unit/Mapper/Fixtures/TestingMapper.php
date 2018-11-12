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
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_test';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TestingModel::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'friend' => TestingMapper::class,
        'owner' => \Tx_Oelib_Mapper_FrontEndUser::class,
        'children' => TestingMapper::class,
        'related_records' => TestingMapper::class,
        'composition' => \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingChildMapper::class,
        'composition2' => \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingChildMapper::class,
        'bidirectional' => TestingMapper::class,
    ];
}
