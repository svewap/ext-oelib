<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\BackEndUserGroup;

/**
 * @covers \OliverKlee\Oelib\Model\BackEndUserGroup
 */
class BackEndUserGroupTest extends UnitTestCase
{
    /**
     * @var BackEndUserGroup
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle(): void
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForEmptyGroupTitleReturnsEmptyString(): void
    {
        $this->subject->setData(['title' => '']);

        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    // Tests concerning getSubgroups

    /**
     * @test
     */
    public function getSubgroupsReturnsListFromSubgroupField(): void
    {
        /** @var Collection<BackEndUserGroup> $expectedGroups */
        $expectedGroups = new Collection();

        $this->subject->setData(['subgroup' => $expectedGroups]);

        /** @var Collection<BackEndUserGroup> $actualGroups */
        $actualGroups = $this->subject->getSubgroups();
        self::assertSame($expectedGroups, $actualGroups);
    }
}
