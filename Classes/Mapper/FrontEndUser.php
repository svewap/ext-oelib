<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a mapper for front-end users.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Mapper_FrontEndUser extends Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_users';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Model_FrontEndUser::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => Tx_Oelib_Mapper_FrontEndUserGroup::class,
    ];

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by user name. Hidden user records will be
     * retrieved as well.
     *
     * @throws Tx_Oelib_Exception_NotFound
     *         if there is no front-end user with the provided user name in the
     *         database
     *
     * @param string $userName
     *        user name, case-insensitive, must not be empty
     *
     * @return Tx_Oelib_Model_FrontEndUser
     *         model of the front-end user with the provided user name
     */
    public function findByUserName($userName)
    {
        return $this->findOneByKey('username', $userName);
    }

    /**
     * Returns the users which are in the groups with the given UIDs.
     *
     * @param string $groupUids
     *        the UIDs of the user groups from which to get the users, must be a
     *        comma-separated list of group UIDs, must not be empty
     *
     * @return Tx_Oelib_List<Tx_Oelib_Model_FrontEndUser> the found user models, will be empty if
     *                       no users were found for the given groups
     */
    public function getGroupMembers($groupUids)
    {
        if ($groupUids === '') {
            throw new InvalidArgumentException('$groupUids must not be an empty string.', 1331488505);
        }

        return $this->getListOfModels(
            Tx_Oelib_Db::selectMultiple(
                '*',
                $this->getTableName(),
                $this->getUniversalWhereClause() . ' AND ' .
                    'usergroup REGEXP \'(^|,)(' . implode('|', GeneralUtility::intExplode(',', $groupUids)) . ')($|,)\''
            )
        );
    }
}
