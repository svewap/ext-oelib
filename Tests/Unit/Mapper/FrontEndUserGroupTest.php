<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Mapper_FrontEndUserGroupTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework;
    /**
     * @var Tx_Oelib_Mapper_FrontEndUserGroup the object to test
     */
    private $subject;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = new Tx_Oelib_Mapper_FrontEndUserGroup();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /////////////////////////////////////////
    // Tests concerning the basic functions
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsFrontEndUserGroupInstance()
    {
        $uid = $this->testingFramework->createFrontEndUserGroup();

        self::assertInstanceOf(
            Tx_Oelib_Model_FrontEndUserGroup::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData()
    {
        /** @var Tx_Oelib_Model_FrontEndUserGroup $userGroup */
        $userGroup = $this->subject->find(
            $this->testingFramework->createFrontEndUserGroup(['title' => 'foo'])
        );

        $this->subject->load($userGroup);

        self::assertSame(
            'foo',
            $userGroup->getTitle()
        );
    }
}
