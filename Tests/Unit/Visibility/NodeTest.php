<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Visibility\Node;

class NodeTest extends UnitTestCase
{
    /**
     * @var Node
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Node();
    }

    //////////////////////////////
    // Tests for the constructor
    //////////////////////////////

    /**
     * @test
     */
    public function isVisibleIfSetToVisibleConstructionReturnsVisibilityFromConstruction(): void
    {
        $subject = new Node(true);

        self::assertTrue(
            $subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function isVisibleIfSetToHiddenConstructionReturnsVisibilityFromConstruction(): void
    {
        $subject = new Node(false);

        self::assertFalse(
            $subject->isVisible()
        );
    }

    //////////////////////////////
    // Tests concerning addChild
    //////////////////////////////

    /**
     * @test
     */
    public function getChildrenWithoutChildrenSetReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getChildren()
        );
    }

    /**
     * @test
     */
    public function addChildWithOneGivenChildrenAddsOneChildToNode(): void
    {
        $childNode = new Node();
        $this->subject->addChild($childNode);

        self::assertSame(
            [$childNode],
            $this->subject->getChildren()
        );
    }

    /**
     * @test
     */
    public function addChildForNodeWithOneChildAndAnotherChildGivenAddsAnotherChildToNode(): void
    {
        $this->subject->addChild(new Node());
        $this->subject->addChild(new Node());

        self::assertCount(
            2,
            $this->subject->getChildren()
        );
    }

    /**
     * @test
     */
    public function addChildAddsParentToChild(): void
    {
        $childNode = new Node();
        $this->subject->addChild($childNode);

        self::assertSame(
            $this->subject,
            $childNode->getParent()
        );
    }

    ///////////////////////////////
    // Tests concerning setParent
    ///////////////////////////////

    /**
     * @test
     */
    public function getParentForNodeWithoutParentReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getParent()
        );
    }

    /**
     * @test
     */
    public function setParentWithGivenParentSetsThisNodeAsParent(): void
    {
        $childNode = new Node();
        $childNode->setParent($this->subject);

        self::assertSame(
            $this->subject,
            $childNode->getParent()
        );
    }

    /**
     * @test
     */
    public function setParentForNodeWithAlreadySetParentAndGivenParentThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'This node already has a parent node.'
        );
        $childNode = new Node();
        $childNode->setParent($this->subject);

        $childNode->setParent($this->subject);
    }

    ///////////////////////////////////
    // Tests concerning markAsVisible
    ///////////////////////////////////

    /**
     * @test
     */
    public function markAsVisibleForInvisibleNodeSetsVisibilityTrue(): void
    {
        $this->subject->markAsVisible();

        self::assertTrue(
            $this->subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function markAsVisibleForVisibleNodeSetsVisibilityTrue(): void
    {
        $visibleNode = new Node(true);
        $visibleNode->markAsVisible();

        self::assertTrue(
            $visibleNode->isVisible()
        );
    }

    /**
     * @test
     */
    public function markAsVisibleForNodeWithParentMarksParentAsVisible(): void
    {
        $childNode = new Node();
        $childNode->setParent($this->subject);
        $childNode->markAsVisible();

        self::assertTrue(
            $this->subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function markAsVisibleForNodeWithParentAndGrandparentMarksGrandparentNodeAsVisible(): void
    {
        $childNode = new Node();
        $grandChildNode = new Node();
        $childNode->setParent($this->subject);
        $grandChildNode->setParent($childNode);
        $grandChildNode->markAsVisible();

        self::assertTrue(
            $this->subject->isVisible()
        );
    }
}
