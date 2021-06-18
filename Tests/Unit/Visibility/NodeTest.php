<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Visibility\Node;

/**
 * Test case.
 */
class NodeTest extends UnitTestCase
{
    /**
     * @var Node
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new Node();
    }

    //////////////////////////////
    // Tests for the constructor
    //////////////////////////////

    /**
     * @test
     */
    public function isVisibleIfSetToVisibleConstructionReturnsVisibilityFromConstruction()
    {
        $subject = new Node(true);

        self::assertTrue(
            $subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function isVisibleIfSetToHiddenConstructionReturnsVisibilityFromConstruction()
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
    public function getChildrenWithoutChildrenSetReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getChildren()
        );
    }

    /**
     * @test
     */
    public function addChildWithOneGivenChildrenAddsOneChildToNode()
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
    public function addChildForNodeWithOneChildAndAnotherChildGivenAddsAnotherChildToNode()
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
    public function addChildAddsParentToChild()
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
    public function getParentForNodeWithoutParentReturnsNull()
    {
        self::assertNull(
            $this->subject->getParent()
        );
    }

    /**
     * @test
     */
    public function setParentWithGivenParentSetsThisNodeAsParent()
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
    public function setParentForNodeWithAlreadySetParentAndGivenParentThrowsException()
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
    public function markAsVisibleForInvisibleNodeSetsVisibilityTrue()
    {
        $this->subject->markAsVisible();

        self::assertTrue(
            $this->subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function markAsVisibleForVisibleNodeSetsVisibilityTrue()
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
    public function markAsVisibleForNodeWithParentMarksParentAsVisible()
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
    public function markAsVisibleForNodeWithParentAndGrandparentMarksGrandparentNodeAsVisible()
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
