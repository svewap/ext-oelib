<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\ParentModel;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\Traits\CachedAssociationCount
 */
final class CachedAssociationCountTest extends UnitTestCase
{
    /**
     * @var ParentModel
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new ParentModel();
    }

    /**
     * @test
     */
    public function getChildrenByDefaultReturnsEmptyStorage(): void
    {
        $newObjectStorage = new ObjectStorage();
        self::assertEquals($newObjectStorage, $this->subject->getChildren());
    }

    /**
     * @test
     */
    public function setChildrenSetsChildren(): void
    {
        /** @var ObjectStorage<ParentModel> $children */
        $children = new ObjectStorage();
        $this->subject->setChildren($children);

        self::assertSame($children, $this->subject->getChildren());
    }

    /**
     * @test
     */
    public function getChildrenCountForLazyChildrenStorageReturnsRawValueFromLazyStorage(): void
    {
        $childrenCount = 7;
        $dataMapper = $this->prophesize(DataMapper::class)->reveal();
        /** @var LazyObjectStorage<ParentModel> $lazyChildrenStorage */
        $lazyChildrenStorage = new LazyObjectStorage($this->subject, 'children', $childrenCount, $dataMapper);
        $this->subject->setChildren($lazyChildrenStorage);

        self::assertSame($childrenCount, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForNoChildrenReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForOneMembershipReturnsOne(): void
    {
        /** @var ObjectStorage<ParentModel> $children */
        $children = new ObjectStorage();
        $children->attach(new ParentModel());
        $this->subject->setChildren($children);

        self::assertSame(1, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForTwoChildrenReturnsTwo(): void
    {
        /** @var ObjectStorage<ParentModel> $children */
        $children = new ObjectStorage();
        $children->attach(new ParentModel());
        $children->attach(new ParentModel());
        $this->subject->setChildren($children);

        self::assertSame(2, $this->subject->getChildrenCount());
    }
}
