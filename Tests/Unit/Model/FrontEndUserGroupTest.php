<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Model\FrontEndUserGroup;

/**
 * @covers \OliverKlee\Oelib\Model\FrontEndUserGroup
 */
final class FrontEndUserGroupTest extends UnitTestCase
{
    /**
     * @var FrontEndUserGroup
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserGroup();
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

    //////////////////////////////////////
    // Tests concerning getDescription()
    //////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionForNonEmptyGroupDescriptionReturnsGroupDescription(): void
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForEmptyGroupDescriptionReturnsEmptyString(): void
    {
        $this->subject->setData(['description' => '']);

        self::assertSame(
            '',
            $this->subject->getDescription()
        );
    }
}
