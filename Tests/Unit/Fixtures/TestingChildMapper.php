<?php

/**
 * This class represents a mapper for a testing child model.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_TestingChildMapper extends Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_testchild';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'parent' => \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
        'tx_oelib_parent2' => \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
    ];
}
