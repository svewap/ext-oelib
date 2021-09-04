<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\BackEndUserGroup;

/**
 * @template M of BackEndUserGroup
 * @extends AbstractDataMapper<BackEndUserGroup>
 */
class BackEndUserGroupMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'be_groups';

    /**
     * @var class-string<BackEndUserGroup> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = BackEndUserGroup::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'subgroup' => BackEndUserGroupMapper::class,
    ];
}
