<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\BackEndUserGroup;

/**
 * @extends AbstractDataMapper<BackEndUserGroup>
 */
class BackEndUserGroupMapper extends AbstractDataMapper
{
    protected $tableName = 'be_groups';

    protected $modelClassName = BackEndUserGroup::class;

    protected $relations = [
        'subgroup' => BackEndUserGroupMapper::class,
    ];
}
