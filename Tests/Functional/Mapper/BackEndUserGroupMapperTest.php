<?php

declare(strict_types=1);

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
     * @var \Tx_Oelib_Mapper_BackEndUserGroup
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Oelib_Mapper_BackEndUserGroup();

        $this->importDataSet(__DIR__ . '/../Fixtures/BackEndUsers.xml');
    }

    /**
     * @test
     */
    public function findReturnsBackEndUserGroupInstance()
    {
        self::assertInstanceOf(\Tx_Oelib_Model_BackEndUserGroup::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData()
    {
        /** @var \Tx_Oelib_Model_FrontEndUserGroup $userGroup */
        $userGroup = $this->subject->find(1);
        $this->subject->load($userGroup);

        self::assertSame('The best!', $userGroup->getTitle());
    }

    /**
     * @test
     */
    public function subgroupRelationIsUserGroupList()
    {
        /** @var \Tx_Oelib_Model_BackEndUserGroup $group */
        $group = $this->subject->find(1);
        self::assertInstanceOf(\Tx_Oelib_Model_BackEndUserGroup::class, $group->getSubgroups()->first());
    }
}
