<?php

/**
 * This class represents a mapper that is broken because it has no table name defined.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_TableLessTestingMapper extends Tx_Oelib_DataMapper
{
    /**
     * @var string a comma-separated list of DB column names to retrieve
     *             or "*" for all columns
     */
    protected $columns = '*';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Tests_Unit_Fixtures_TestingModel::class;
}
