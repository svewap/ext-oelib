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
    protected $tableName = 'fe_users';

    protected $modelClassName = FrontEndUser::class;

    protected $relations = [
        'usergroup' => FrontEndUserGroupMapper::class,
    ];

    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by username. Hidden user records will be
     * retrieved as well.
     *
     * @param non-empty-string $username username, case-insensitive
     *
     * @return FrontEndUser model of the front-end user with the provided username
     *
     * @throws NotFoundException if there is no front-end user with the provided username in the database
     */
    public function findByUserName(string $username): FrontEndUser
    {
        /** @var FrontEndUser $result */
        $result = $this->findOneByKey('username', $username);

        return $result;
    }

    /**
     * Returns the users which are in the groups with the given UIDs.
     *
     * @deprecated will be removed in oelib 5.0 without replacement
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
        $query = 'SELECT * FROM `' . $tableName . '` WHERE ' . $where;
        if (\method_exists($connection, 'executeQuery')) {
            $statement = $connection->executeQuery($query);
        } else {
            $statement = $connection->query($query);
        }
        if (\method_exists($statement, 'fetchAllAssociative')) {
            $modelData = $statement->fetchAllAssociative();
        } else {
            $modelData = $statement->fetchAll();
        }

        return $this->getListOfModels($modelData);
    }
}
