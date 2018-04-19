<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Mapper_FrontEndUserTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework;
    /**
     * @var Tx_Oelib_Mapper_FrontEndUser the object to test
     */
    private $subject;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = new Tx_Oelib_Mapper_FrontEndUser();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsFrontEndUserInstance()
    {
        $uid = $this->testingFramework->createFrontEndUser();

        self::assertInstanceOf(
            Tx_Oelib_Model_FrontEndUser::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelWithThatUid()
    {
        $uid = $this->testingFramework->createFrontEndUser();

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
        $groupMapper
            = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_FrontEndUserGroup::class);

        $group1 = $groupMapper->getNewGhost();
        $group2 = $groupMapper->getNewGhost();
        $groupUids = $group1->getUid() . ',' . $group2->getUid();

        $uid = $this->testingFramework->createFrontEndUser($groupUids);

        /** @var Tx_Oelib_Model_FrontEndUser $user */
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
        $this->setExpectedException(
            'InvalidArgumentException',
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
            $this->subject->getGroupMembers(
                $this->testingFramework->getAutoIncrement('fe_groups')
            )->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithNoMembersReturnsInstanceOfOelibList()
    {
        self::assertInstanceOf(
            Tx_Oelib_List::class,
            $this->subject->getGroupMembers(
                $this->testingFramework->createFrontEndUserGroup()
            )
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithNoMembersReturnsEmptyList()
    {
        self::assertTrue(
            $this->subject->getGroupMembers(
                $this->testingFramework->createFrontEndUserGroup()
            )->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberReturnsOneElement()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($feUserGroupUid);

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
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser(
            $feUserGroupUid,
            ['deleted' => 1]
        );

        self::assertTrue(
            $this->subject->getGroupMembers($feUserGroupUid)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersIgnoresDisabledUser()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser(
            $feUserGroupUid,
            ['disable' => 1]
        );

        self::assertTrue(
            $this->subject->getGroupMembers($feUserGroupUid)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupFirstReturnsOneElement()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroups = $feUserGroupUid . ',' .
            $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($userGroups);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupLastReturnsOneElement()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroups = $this->testingFramework->createFrontEndUserGroup() . ',' .
            $feUserGroupUid;
        $this->testingFramework->createFrontEndUser($userGroups);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupInTheMiddleReturnsOneElement()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroups = $this->testingFramework->createFrontEndUserGroup() .
            ',' . $feUserGroupUid . ',' .
            $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($userGroups);

        self::assertSame(
            1,
            $this->subject->getGroupMembers($feUserGroupUid)->count()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithOneMemberReturnsFrontEndUserList()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($feUserGroupUid);

        self::assertInstanceOf(
            Tx_Oelib_Model_FrontEndUser::class,
            $this->subject->getGroupMembers($feUserGroupUid)->first()
        );
    }

    /**
     * @test
     */
    public function getGroupMembersForGroupWithTwoMembersReturnsTwoUsers()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($feUserGroupUid);
        $this->testingFramework->createFrontEndUser($feUserGroupUid);

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
        $firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($firstGroupUid);
        $secondUserUid = $this->testingFramework->createFrontEndUser(
            $this->testingFramework->createFrontEndUserGroup()
        );

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
        $firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $secondGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($firstGroupUid);
        $this->testingFramework->createFrontEndUser($secondGroupUid);

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
        $userGroups = $this->testingFramework->createFrontEndUserGroup() . ',' .
            $this->testingFramework->createFrontEndUserGroup();
        $this->testingFramework->createFrontEndUser($userGroups);

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
        $firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $secondGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroups = $firstGroupUid . ',' . $secondGroupUid;
        $this->testingFramework->createFrontEndUser($firstGroupUid);
        $this->testingFramework->createFrontEndUser($secondGroupUid);
        $this->testingFramework->createFrontEndUser($userGroups);

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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $value must not be empty.
     */
    public function findByUserNameForEmptyUserNameThrowsException()
    {
        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance()
    {
        $userName = uniqid('user', true);
        $this->testingFramework->createFrontEndUser('', ['username' => $userName]);

        self::assertInstanceOf(
            Tx_Oelib_Model_FrontEndUser::class,
            $this->subject->findByUserName($userName)
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid()
    {
        $userName = uniqid('user', true);
        self::assertSame(
            $this->testingFramework->createFrontEndUser('', ['username' => $userName]),
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid()
    {
        $userName = uniqid('user', true);
        self::assertSame(
            $this->testingFramework->createFrontEndUser('', ['username' => $userName]),
            $this->subject->findByUserName(strtoupper($userName))->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        $userName = strtoupper(uniqid('user', true));
        self::assertSame(
            $this->testingFramework->createFrontEndUser('', ['username' => $userName]),
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        $userName = uniqid('user', true);
        self::assertSame(
            $this->testingFramework->createFrontEndUser('', ['username' => strtoupper($userName)]),
            $this->subject->findByUserName($userName)->getUid()
        );
    }

    /**
     * @test
     *
     * @expectedException \Tx_Oelib_Exception_NotFound
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException()
    {
        $userName = uniqid('user', true);
        $this->testingFramework->createFrontEndUser('', ['username' => $userName, 'deleted' => 1]);

        $this->subject->findByUserName($userName);
    }
}
