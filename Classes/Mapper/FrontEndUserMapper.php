<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\FrontEndUser;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @extends AbstractDataMapper<FrontEndUser>
 */
class FrontEndUserMapper extends AbstractDataMapper
{
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'fe_users';

    /**
     * @var class-string<FrontEndUser> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUser::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => FrontEndUserGroupMapper::class,
    ];

    /**
     * @var array<int, string> the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by user name. Hidden user records will be
     * retrieved as well.
     *
     * @param non-empty-string $userName user name, case-insensitive
     *
     * @return FrontEndUser model of the front-end user with the provided user name
     *
     * @throws NotFoundException if there is no front-end user with the provided user name in the database
     */
    public function findByUserName(string $userName): FrontEndUser
    {
        /** @var FrontEndUser $result */
        $result = $this->findOneByKey('username', $userName);

        return $result;
    }

    /**
     * Returns the users which are in the groups with the given UIDs.
     *
     * @param string|int $commaSeparatedGroupUids
     *        the UIDs of the user groups from which to get the users, must be a
     *        comma-separated list of group UIDs, must not be empty
     *
     * @return Collection<FrontEndUser> the found user models, will be empty if
     *                       no users were found for the given groups
     */
    public function getGroupMembers($commaSeparatedGroupUids): Collection
    {
        if ((string)$commaSeparatedGroupUids === '') {
            throw new \InvalidArgumentException('$groupUids must not be an empty string.', 1331488505);
        }

        $groupUids = GeneralUtility::intExplode(',', (string)$commaSeparatedGroupUids, true);
        $tableName = $this->getTableName();
        $where = 'deleted = 0 AND disable = 0 AND usergroup REGEXP \'(^|,)(' . \implode('|', $groupUids) . ')($|,)\'';
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        $statement = $connection->query('SELECT * FROM `' . $tableName . '` WHERE ' . $where);

        return $this->getListOfModels($statement->fetchAll());
    }
}
