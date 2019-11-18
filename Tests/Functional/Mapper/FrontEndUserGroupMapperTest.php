<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class FrontEndUserGroupMapperTest extends FunctionalTestCase
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
     * @var \Tx_Oelib_Mapper_FrontEndUserGroup the object to test
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = new \Tx_Oelib_Mapper_FrontEndUserGroup();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        parent::tearDown();
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
            \Tx_Oelib_Model_FrontEndUserGroup::class,
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
            $this->testingFramework->createFrontEndUserGroup(['title' => 'foo'])
        );

        $this->subject->load($userGroup);

        self::assertSame(
            'foo',
            $userGroup->getTitle()
        );
    }
}
