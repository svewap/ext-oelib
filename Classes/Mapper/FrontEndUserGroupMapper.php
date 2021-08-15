<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\FrontEndUserGroup;

/**
 * @extends AbstractDataMapper<FrontEndUserGroup>
 */
class FrontEndUserGroupMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_groups';

    /**
     * @var class-string<FrontEndUserGroup> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUserGroup::class;
}
