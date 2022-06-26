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

class FrontEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var FrontEndUserMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
    }

    //////////////////////////////
    // Test concerning getGroups
    //////////////////////////////

    /**
     * @test
     */
    public function getUserGroupsGetsRelatedGroupsAsList(): void
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
    public function getGroupMembersForEmptyStringThrowsException(): void
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
    public function getGroupMembersForNonExistingGroupUidReturnsEmptyList(): void
    {
        self::assertTrue(
            $this->subject->getGroupMembers(1)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithNoMembersReturnsInstanceOfOelibList(): void
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
    public function getGroupMembersForGroupWithNoMembersReturnsEmptyList(): void
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
    public function getGroupMembersForGroupWithOneMemberReturnsOneElement(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);

        self::assertCount(1, $this->subject->getGroupMembers($feUserGroupUid));
    }

    /**
     * @test
     */
    public function getGroupMembersIgnoresDeletedUser(): void
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
    public function getGroupMembersIgnoresDisabledUser(): void
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
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupFirstReturnsOneElement(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertCount(1, $this->subject->getGroupMembers($feUserGroupUid1));
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupLastReturnsOneElement(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertCount(1, $this->subject->getGroupMembers($feUserGroupUid1));
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupInTheMiddleReturnsOneElement(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertCount(1, $this->subject->getGroupMembers($feUserGroupUid1));
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberReturnsFrontEndUserList(): void
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
    public function getGroupMembersForGroupWithTwoMembersReturnsTwoUsers(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $feUserGroupUid]);

        self::assertCount(2, $this->subject->getGroupMembers($feUserGroupUid));
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberDoesNotReturnsUserNotInGivenGroup(): void
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
    public function getGroupMembersForTwoGroupsReturnsUsersOfBothGroups(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $firstGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $secondGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $firstGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $secondGroupUid]);

        self::assertCount(2, $this->subject->getGroupMembers($firstGroupUid . ',' . $secondGroupUid));
    }

    /**
     * @test
     */
    public function getGroupMembersForTwoGroupsReturnsUserInBothGroupsOnlyOnce(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $feUserGroupUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $feUserGroupUid1 . ',' . $feUserGroupUid2;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertCount(1, $this->subject->getGroupMembers($userGroups));
    }

    /**
     * @test
     */
    public function getGroupMembersForTwoGroupsCanReturnThreeUsersInGroups(): void
    {
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $firstGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('fe_groups', []);
        $secondGroupUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $userGroups = $firstGroupUid . ',' . $secondGroupUid;
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $firstGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $secondGroupUid]);
        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $userGroups]);

        self::assertCount(3, $this->subject->getGroupMembers($userGroups));
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance(): void
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
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid(): void
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
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid(): void
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
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
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
    public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
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
    public function findByUserNameWithNameOfNonExistentUserThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $userName = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $userName, 'deleted' => 1]);

        $this->subject->findByUserName($userName);
    }
}
