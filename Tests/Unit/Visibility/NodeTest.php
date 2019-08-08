<?php

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class NodeTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Visibility_Node
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Visibility_Node();
    }

    //////////////////////////////
    // Tests for the constructor
    //////////////////////////////

    /**
     * @test
     */
    public function isVisibleIfSetToVisibleConstructionReturnsVisibilityFromConstruction()
    {
        $subject = new \Tx_Oelib_Visibility_Node(true);

        self::assertTrue(
            $subject->isVisible()
        );
    }

    /**
     * @test
     */
    public function isVisibleIfSetToHiddenConstructionReturnsVisibilityFromConstruction()
    {
        $subject = new \Tx_Oelib_Visibility_Node(false);

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
        $childNode = new \Tx_Oelib_Visibility_Node();
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
        $this->subject->addChild(new \Tx_Oelib_Visibility_Node());
        $this->subject->addChild(new \Tx_Oelib_Visibility_Node());

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
        $childNode = new \Tx_Oelib_Visibility_Node();
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
        $childNode = new \Tx_Oelib_Visibility_Node();
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
        $childNode = new \Tx_Oelib_Visibility_Node();
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
        $visibleNode = new \Tx_Oelib_Visibility_Node(true);
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
        $childNode = new \Tx_Oelib_Visibility_Node();
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
        $childNode = new \Tx_Oelib_Visibility_Node();
        $grandChildNode = new \Tx_Oelib_Visibility_Node();
        $childNode->setParent($this->subject);
        $grandChildNode->setParent($childNode);
        $grandChildNode->markAsVisible();

        self::assertTrue(
            $this->subject->isVisible()
        );
    }
}
