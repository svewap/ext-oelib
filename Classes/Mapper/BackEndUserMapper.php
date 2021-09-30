<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\BackEndUser;

/**
 * @extends AbstractDataMapper<BackEndUser>
 */
class BackEndUserMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'be_users';

    /**
     * @var class-string<BackEndUser> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = BackEndUser::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => BackEndUserGroupMapper::class,
    ];

    /**
     * @var array<int, string> the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a back-end user by user name. Hidden user records will be retrieved
     * as well.
     *
     * @param string $username username, case-insensitive, must not be empty
     *
     * @return BackEndUser model of the back-end user with the provided username
     *
     * @throws NotFoundException if there is no back-end user with the provided username in the be_user table
     */
    public function findByUserName(string $username): BackEndUser
    {
        /** @var BackEndUser $result */
        $result = $this->findOneByKey('username', $username);

        return $result;
    }
}
