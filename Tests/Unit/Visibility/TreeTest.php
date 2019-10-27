<?php

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class TreeTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Visibility_Tree
     */
    private $subject;

    ////////////////////////////////////////////////////////
    // Tests concerning the building of the tree structure
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function constructWithEmptyArrayCreatesRootNodeWithoutChildren()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree([]);

        self::assertSame(
            [],
            $this->subject->getRootNode()->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructWithOneElementInArrayAddsOneChildToRootNode()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertInstanceOf(\Tx_Oelib_Visibility_Node::class, $children[0]);
    }

    /**
     * @test
     */
    public function constructWithTwoElementsInFirstArrayLevelAddsTwoChildrenToRootNode()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false, 'testNode2' => false]);

        self::assertCount(
            2,
            $this->subject->getRootNode()->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructWithTwoElementsInArrayOneFirstOneSecondLevelAddsGrandChildToRootNode()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['child' => ['grandChild' => false]]);

        $children = $this->subject->getRootNode()->getChildren();
        self::assertCount(
            1,
            $children[0]->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructForOneVisibleElementStoresVisibilityStatus()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['visibleNode' => true]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertTrue(
            $children[0]->isVisible()
        );
    }

    /**
     * @test
     */
    public function constructForOneInvisibleElementStoresVisibilityStatus()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['hiddenNode' => false]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertFalse(
            $children[0]->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithoutChildIsInvisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree([]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneInvisibleChildIsInvisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleChildIsVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => true]);

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleGrandChildIsVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['child' => ['grandChild' => true]]);

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function childOfRootNodeWithOneVisibleChildIsVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['child' => ['grandChild' => true]]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertTrue(
            $children[0]->isVisible()
        );
    }

    //////////////////////////////////////
    // Tests concerning makeNodesVisible
    //////////////////////////////////////

    /**
     * @test
     */
    public function makeNodesVisibleForEmptyArrayGivenDoesNotMakeRootVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree([]);
        $this->subject->makeNodesVisible([]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function makeNodesVisibleForGivenNodeMakesThisNodeVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);
        $this->subject->makeNodesVisible(['testNode']);

        $this->subject->getRootNode()->getChildren();

        self::assertSame(
            [],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function makeNodesVisibleForInexistentNodeGivenDoesNotCrash()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);
        $this->subject->makeNodesVisible(['foo']);
    }

    /**
     * @test
     */
    public function makeNodesVisibleForInexistentNodeGivenDoesNotMakeExistingNodeVisible()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);
        $this->subject->makeNodesVisible(['foo']);

        $this->subject->getRootNode()->getChildren();

        self::assertSame(
            ['testNode'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /////////////////////////////////////////////
    // Tests concerning getKeysOfHiddenSubparts
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithoutNodesReturnsEmptyArray()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree([]);

        self::assertSame(
            [],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithOneHiddenNodeReturnsArrayWithNodeName()
    {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['testNode' => false]);

        self::assertSame(
            ['testNode'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithOneHiddenParentNodeAndOneHiddenChildNodeReturnsArrayWithBothNodeNames(
    ) {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['child' => ['parent' => false]]);

        self::assertSame(
            ['parent', 'child'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithVisibleParentNodeAndOneHiddenChildNodeReturnsArrayWithChildNodeName(
    ) {
        $this->subject = new \Tx_Oelib_Visibility_Tree(['parent' => ['hidden' => false, 'visible' => true]]);

        self::assertSame(
            ['hidden'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }
}
