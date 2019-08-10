<?php

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndUserGroupMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Oelib_Mapper_BackEndUserGroup the object to test
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = new \Tx_Oelib_Mapper_BackEndUserGroup();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    /////////////////////////////////////////
    // Tests concerning the basic functions
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findReturnsBackEndUserGroupInstance()
    {
        $uid = $this->subject->getNewGhost()->getUid();

        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUserGroup::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData()
    {
        /** @var \Tx_Oelib_Model_FrontEndUserGroup $userGroup */
        $userGroup = $this->subject->find(
            $this->testingFramework->createBackEndUserGroup(['title' => 'foo'])
        );

        $this->subject->load($userGroup);

        self::assertSame(
            'foo',
            $userGroup->getTitle()
        );
    }

    ///////////////////////////////////
    // Tests concerning the relations
    ///////////////////////////////////

    /**
     * @test
     */
    public function subgroupRelationIsUserGroupList()
    {
        $subgroup = $this->subject->getNewGhost();
        $group = $this->subject->getLoadedTestingModel(
            ['subgroup' => $subgroup->getUid()]
        );

        /** @var \Tx_Oelib_Model_BackEndUserGroup $group */
        $group = $this->subject->find($group->getUid());
        self::assertInstanceOf(
            \Tx_Oelib_Model_BackEndUserGroup::class,
            $group->getSubgroups()->first()
        );
    }
}
