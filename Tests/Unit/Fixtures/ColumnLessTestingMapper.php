<?php

/**
 * This class represents a mapper that is broken because it has no columns defined.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_ColumnLessTestingMapper extends \Tx_Oelib_DataMapper
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
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Tests_Unit_Fixtures_TestingModel::class;
}
