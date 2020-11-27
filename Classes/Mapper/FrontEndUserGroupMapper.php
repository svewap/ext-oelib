<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\FrontEndUserGroup;

/**
 * This class represents a mapper for front-end user groups.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class FrontEndUserGroupMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_groups';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUserGroup::class;
}
