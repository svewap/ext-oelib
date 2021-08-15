<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\BackEndUser;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

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
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = BackEndUser::class;

    /**
     * @var array<string, string> the (possible) relations of the created models in the format
     *      DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => BackEndUserGroupMapper::class,
    ];

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a back-end user by user name. Hidden user records will be retrieved
     * as well.
     *
     * @param string $userName
     *        user name, case-insensitive, must not be empty
     *
     * @return BackEndUser model of the back-end user with the provided user name
     *
     * @throws NotFoundException if there is no back-end user with the
     *                                     provided user name in the be_user table
     */
    public function findByUserName(string $userName): BackEndUser
    {
        /** @var BackEndUser $result */
        $result = $this->findOneByKey('username', $userName);

        return $result;
    }

    /**
     * Reads a record from the database by UID (from this mapper's table). Also
     * hidden records will be retrieved.
     *
     * @param int $uid the UID of the record to retrieve, must be > 0
     *
     * @return array the record from the database, will not be empty
     *
     * @throws NotFoundException if there is no record in the DB with the UID $uid
     */
    protected function retrieveRecordByUid(int $uid): array
    {
        $authentication = $this->getBackEndUserAuthentication();
        if ((int)$authentication->user['uid'] === $uid && BackEndLoginManager::getInstance()->isLoggedIn()) {
            $data = $authentication->user;
        } else {
            $data = parent::retrieveRecordByUid($uid);
        }

        return $data;
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication
     */
    protected function getBackEndUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
