<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\BackEndUserGroup;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Model\BackEndUserGroup
 */
final class BackEndUserGroupTest extends UnitTestCase
{
    /**
     * @var BackEndUserGroup
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

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
