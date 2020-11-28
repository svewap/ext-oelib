<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
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
        $this->subject->setData(['usergroup' => new Collection()]);

        self::assertInstanceOf(
            Collection::class,
            $this->subject->getAllGroups()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForNoGroupsReturnsEmptyList()
    {
        $this->subject->setData(['usergroup' => new Collection()]);

        self::assertTrue(
            $this->subject->getAllGroups()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForOneGroupReturnsListWithThatGroup()
    {
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $groups = new Collection();
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
        $group1 = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $group2 = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $groups = new Collection();
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
        $subgroup = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subgroup->getUid()]);
        $groups = new Collection();
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
        $subsubgroup = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel([]);
        $subgroup = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subsubgroup->getUid()]);
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['subgroup' => $subgroup->getUid()]);
        $groups = new Collection();
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
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();
        $subgroups = new Collection();
        $subgroups->add($group);
        $group->setData(['subgroup' => $subgroups]);

        $groups = new Collection();
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
        $group1 = MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();
        $group2 = MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();

        $subgroups1 = new Collection();
        $subgroups1->add($group2);
        $group1->setData(['subgroup' => $subgroups1]);

        $subgroups2 = new Collection();
        $subgroups2->add($group1);
        $group2->setData(['subgroup' => $subgroups2]);

        $groups = new Collection();
        $groups->add($group1);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            2,
            $this->subject->getAllGroups()->count()
        );
    }
}
