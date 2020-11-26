<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Model\BackEndUser;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndUserTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var BackEndUser
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new BackEndUser();
    }

    //////////////////////////////////
    // Tests concerning getAllGroups
    //////////////////////////////////

    /**
     * @test
     */
    public function getAllGroupsForNoGroupsReturnsList()
    {
        $this->subject->setData(['usergroup' => new \Tx_Oelib_List()]);

        self::assertInstanceOf(
            \Tx_Oelib_List::class,
            $this->subject->getAllGroups()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForNoGroupsReturnsEmptyList()
    {
        $this->subject->setData(['usergroup' => new \Tx_Oelib_List()]);

        self::assertTrue(
            $this->subject->getAllGroups()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForOneGroupReturnsListWithThatGroup()
    {
        $group = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            $group,
            $this->subject->getAllGroups()->first()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForTwoGroupsReturnsBothGroups()
    {
        $group1 = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $group2 = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group1->getUid())
        );
        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group2->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupReturnsBothGroups()
    {
        $subgroup = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $group = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subgroup->getUid()]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group->getUid())
        );
        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($subgroup->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubsubgroupContainsSubsubgroup()
    {
        $subsubgroup = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel([]);
        $subgroup = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subsubgroup->getUid()]);
        $group = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subgroup->getUid()]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($subsubgroup->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupSelfReferenceReturnsOnlyOneGroup()
    {
        $group = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();
        $subgroups = new \Tx_Oelib_List();
        $subgroups->add($group);
        $group->setData(['subgroup' => $subgroups]);

        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            1,
            $this->subject->getAllGroups()->count()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupCycleReturnsBothGroups()
    {
        $group1 = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();
        $group2 = \Tx_Oelib_MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();

        $subgroups1 = new \Tx_Oelib_List();
        $subgroups1->add($group2);
        $group1->setData(['subgroup' => $subgroups1]);

        $subgroups2 = new \Tx_Oelib_List();
        $subgroups2->add($group1);
        $group2->setData(['subgroup' => $subgroups2]);

        $groups = new \Tx_Oelib_List();
        $groups->add($group1);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            2,
            $this->subject->getAllGroups()->count()
        );
    }
}
