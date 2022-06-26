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
    protected $tableName = 'be_users';

    protected $modelClassName = BackEndUser::class;

    protected $relations = [
        'usergroup' => BackEndUserGroupMapper::class,
    ];

    protected $additionalKeys = ['username'];

    /**
     * Finds a back-end user by user name. Hidden user records will be retrieved
     * as well.
     *
     * @param non-empty-string $username username, case-insensitive, must not be empty
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
