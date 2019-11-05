<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\ParentModel;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CachedAssociationCountTest extends UnitTestCase
{
    /**
     * @var ParentModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new ParentModel();
    }

    /**
     * @test
     */
    public function getChildrenByDefaultReturnsEmptyStorage()
    {
        $newObjectStorage = new ObjectStorage();
        self::assertEquals($newObjectStorage, $this->subject->getChildren());
    }

    /**
     * @test
     */
    public function setChildrenSetsChildren()
    {
        $Children = new ObjectStorage();
        $this->subject->setChildren($Children);

        self::assertSame($Children, $this->subject->getChildren());
    }

    /**
     * @test
     */
    public function getChildrenCountForLazyChildrenStorageReturnsRawValueFromLazyStorage()
    {
        $childrenCount = 7;
        $lazyChildrenStorage = new LazyObjectStorage($this->subject, 'children', $childrenCount);
        $this->subject->setChildren($lazyChildrenStorage);

        self::assertSame($childrenCount, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForNoChildrenReturnsZero()
    {
        self::assertSame(0, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForOneMembershipReturnsOne()
    {
        $children = new ObjectStorage();
        $children->attach(new ParentModel());
        $this->subject->setChildren($children);

        self::assertSame(1, $this->subject->getChildrenCount());
    }

    /**
     * @test
     */
    public function getChildrenCountForTwoChildrenReturnsTwo()
    {
        $children = new ObjectStorage();
        $children->attach(new ParentModel());
        $children->attach(new ParentModel());
        $this->subject->setChildren($children);

        self::assertSame(2, $this->subject->getChildrenCount());
    }
}
