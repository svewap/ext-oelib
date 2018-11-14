<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

/**
 * This class represents a mapper that is broken because it has no model name defined.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ModelLessTestingMapper extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_oelib_test';
}
