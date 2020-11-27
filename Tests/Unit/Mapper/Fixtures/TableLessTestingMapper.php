<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Testing mapper without a table name.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TableLessTestingMapper extends AbstractDataMapper
{
    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TestingModel::class;
}
