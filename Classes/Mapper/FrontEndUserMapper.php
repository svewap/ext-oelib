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
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_users';

    /**
     * @var class-string<FrontEndUser> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUser::class;

    /**
     * @var array<string, string> the column names of additional combined keys the (possible) relations
     *      of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => FrontEndUserGroupMapper::class,
    ];

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by user name. Hidden user records will be
     * retrieved as well.
     *
     * @param string $userName
     *        user name, case-insensitive, must not be empty
     *
     * @return FrontEndUser
     *         model of the front-end user with the provided user name
     *
     * @throws NotFoundException
     *         if there is no front-end user with the provided user name in the
     *         database
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
     * @param string|int $groupUids
     *        the UIDs of the user groups from which to get the users, must be a
     *        comma-separated list of group UIDs, must not be empty
     *
     * @return Collection<FrontEndUser> the found user models, will be empty if
     *                       no users were found for the given groups
     */
    public function getGroupMembers($groupUids): Collection
    {
        if ((string)$groupUids === '') {
            throw new \InvalidArgumentException('$groupUids must not be an empty string.', 1331488505);
        }

        $tableName = $this->getTableName();
        $where = $this->getUniversalWhereClause() . ' AND usergroup REGEXP \'(^|,)(' .
            \implode('|', GeneralUtility::intExplode(',', (string)$groupUids)) . ')($|,)\'';
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        $statement = $connection->query('SELECT * FROM `' . $tableName . '` WHERE ' . $where);

        return $this->getListOfModels($statement->fetchAll());
    }
}
