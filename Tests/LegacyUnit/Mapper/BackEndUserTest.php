<?php

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Mapper_BackEndUserTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework;

    /**
     * @var \Tx_Oelib_Mapper_BackEndUser the object to test
     */
    private $subject;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = new \Tx_Oelib_Mapper_BackEndUser();
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
    public function findWithUidOfExistingRecordReturnsBackEndUserInstance()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUser::class,
            $this->subject->find($this->testingFramework->createBackEndUser())
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelWithThatUid()
    {
        $uid = $this->testingFramework->createBackEndUser();

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid()
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
        $this->setExpectedException(
            'InvalidArgumentException',
            '$value must not be empty.'
        );

        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsBackEndUserInstance()
    {
        $this->testingFramework->createBackEndUser(['username' => 'foo']);

        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUser::class,
            $this->subject->findByUserName('foo')
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'foo']),
            $this->subject->findByUserName('foo')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'foo']),
            $this->subject->findByUserName('FOO')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'FOO']),
            $this->subject->findByUserName('FOO')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'FOO']),
            $this->subject->findByUserName('foo')->getUid()
        );
    }

    /**
     * @test
     *
     * @expectedException \Tx_Oelib_Exception_NotFound
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException()
    {
        $this->testingFramework->createBackEndUser(
            ['username' => 'foo', 'deleted' => 1]
        );

        $this->subject->findByUserName('foo');
    }

    //////////////////////////////////
    // Tests concerning findByCliKey
    //////////////////////////////////

    /**
     * @test
     */
    public function findByCliKeyForCliKeyDefinedReturnsBackEndUserInstance()
    {
        $this->testingFramework->createBackEndUser(['username' => 'foo']);
        /**
         * fakes the CLI definition
         *
         * @var string
         */
        define('TYPO3_cliKey', 'oelib_mapper_test');
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']
        ['cliKeys'][TYPO3_cliKey][1] = 'foo';

        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUser::class,
            $this->subject->findByCliKey()
        );
    }

    ///////////////////////////////////
    // Tests concerning the relations
    ///////////////////////////////////

    /**
     * @test
     */
    public function userGroupRelationIsUserGroupList()
    {
        /** @var \Tx_Oelib_Model_BackEndUserGroup $group */
        $group = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getNewGhost();
        $groupUid = $group->getUid();
        $userUid = $this->subject->getLoadedTestingModel(['usergroup' => $groupUid])->getUid();

        /** @var \Tx_Oelib_Model_BackEndUser $user */
        $user = $this->subject->find($userUid);
        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUserGroup::class,
            $user->getGroups()->first()
        );
    }
}
