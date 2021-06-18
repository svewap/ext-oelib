<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * Test case.
 */
class FrontEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var FrontEndUserMapper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsFrontEndUserInstance()
    {
        $this->getDatabaseConnection()->insertArray('fe_users', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertInstanceOf(
            FrontEndUser::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelWithThatUid()
    {
        $this->getDatabaseConnection()->insertArray('fe_users', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid()
        );
    }

    //////////////////////////////
    // Test concerning getGroups
    //////////////////////////////

    /**
     * @test
     */
    public function getUserGroupsGetsRelatedGroupsAsList()
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);

        $group1 = $groupMapper->getNewGhost();
        $group2 = $groupMapper->getNewGhost();
        $groupUids = $group1->getUid() . ',' . $group2->getUid();

        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $groupUids]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var FrontEndUser $user */
        $user = $this->subject->find($uid);
        self::assertSame(
            $groupUids,
            $user->getUserGroups()->getUids()
        );
    }

    /////////////////////////////////////
    // Tests concerning getGroupMembers
    /////////////////////////////////////

    /**
     * @test
     */
    public function getGroupMembersForEmptyStringThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$groupUids must not be an empty string.'
        );

        $this->subject->getGroupMembers('');
    }

    /**
     * @test
     */
    public function getGroupMembersForNonExistingGroupUidReturnsEmptyList()
    {
        self::assertTrue(
            $this->subject->getGroupMembers(1)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithNoMembersReturnsInstanceOfOelibList()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertInstanceOf(
            Collection::class,
            $this->subject->getGroupMembers($uid)
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithNoMembersReturnsEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertTrue(
            $this->subject->getGroupMembers($uid)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberReturnsOneElement()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersIgnoresDeletedUser()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid, 'deleted' => 1]);

        self::assertTrue(
            $this->subject->getGroupMembers($feUserGroupUid)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersIgnoresDisabledUser()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid, 'disable' => 1]);

        self::assertTrue(
            $this->subject->getGroupMembers($feUserGroupUid)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupFirstReturnsOneElement()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid1)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupLastReturnsOneElement()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid1)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupInTheMiddleReturnsOneElement()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid1)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberReturnsFrontEndUserList()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);

        self::assertInstanceOf(
            FrontEndUser::class,
            $this->subject->getGroupMembers($feUserGroupUid)->first()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithTwoMembersReturnsTwoUsers()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);

        self::assertSame(
            2,
            $this->subject->getGroupMembers($feUserGroupUid)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberDoesNotReturnsUserNotInGivenGroup()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $firstGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $secondGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $firstGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $secondGroupUid]);
        $secondUserUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertFalse(
            $this->subject->getGroupMembers($firstGroupUid)->hasUid(
                $secondUserUid
            )
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForTwoGroupsReturnsUsersOfBothGroups()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $firstGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $secondGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $firstGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $secondGroupUid]);

        self::assertSame(
            2,
            $this->subject->getGroupMembers(
                $firstGroupUid . ',' . $secondGroupUid
            )->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForTwoGroupsReturnsUserInBothGroupsOnlyOnce()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($userGroups)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForTwoGroupsCanReturnThreeUsersInGroups()
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $firstGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $secondGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $firstGroupUid . ',' . $secondGroupUid;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $firstGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $secondGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertSame(
            3,
            $this->subject->getGroupMembers($userGroups)->count()
        );
    }

    ////////////////////////////////////
    // Tests concerning findByUserName
    ////////////////////////////////////

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');

        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance()
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);

        self::assertInstanceOf(
            FrontEndUser::class,
            $this->subject->findByUserName($userName)
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid()
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid()
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName(strtoupper($userName))->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        $userName = 'MAX.DOE';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => strtoupper($userName)]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName, 'deleted' => 1]);

        $this->subject->findByUserName($userName);
    }
}
