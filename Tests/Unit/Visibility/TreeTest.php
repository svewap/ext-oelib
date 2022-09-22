<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use OliverKlee\Oelib\Visibility\Node;
use OliverKlee\Oelib\Visibility\Tree;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Visibility\Tree
 */
class TreeTest extends UnitTestCase
{
    /**
     * @var Tree
     */
    private $subject;

    ////////////////////////////////////////////////////////
    // Tests concerning the building of the tree structure
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function constructWithEmptyArrayCreatesRootNodeWithoutChildren(): void
    {
        $this->subject = new Tree([]);

        self::assertSame(
            [],
            $this->subject->getRootNode()->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructWithOneElementInArrayAddsOneChildToRootNode(): void
    {
        $this->subject = new Tree(['testNode' => false]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertInstanceOf(Node::class, $children[0]);
    }

    /**
     * @test
     */
    public function constructWithTwoElementsInFirstArrayLevelAddsTwoChildrenToRootNode(): void
    {
        $this->subject = new Tree(['testNode' => false, 'testNode2' => false]);

        self::assertCount(
            2,
            $this->subject->getRootNode()->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructWithTwoElementsInArrayOneFirstOneSecondLevelAddsGrandChildToRootNode(): void
    {
        $this->subject = new Tree(['child' => ['grandChild' => false]]);

        $children = $this->subject->getRootNode()->getChildren();
        self::assertCount(
            1,
            $children[0]->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructForOneVisibleElementStoresVisibilityStatus(): void
    {
        $this->subject = new Tree(['visibleNode' => true]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertTrue(
            $children[0]->isVisible()
        );
    }

    /**
     * @test
     */
    public function constructForOneInvisibleElementStoresVisibilityStatus(): void
    {
        $this->subject = new Tree(['hiddenNode' => false]);

        $children = $this->subject->getRootNode()->getChildren();

        self::assertFalse(
            $children[0]->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithoutChildIsInvisible(): void
    {
        $this->subject = new Tree([]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneInvisibleChildIsInvisible(): void
    {
        $this->subject = new Tree(['testNode' => false]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleChildIsVisible(): void
    {
        $this->subject = new Tree(['testNode' => true]);

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleGrandChildIsVisible(): void
    {
        $this->subject = new Tree(['child' => ['grandChild' => true]]);

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function childOfRootNodeWithOneVisibleChildIsVisible(): void
    {
        $this->subject = new Tree(['child' => ['grandChild' => true]]);

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
    public function makeNodesVisibleForEmptyArrayGivenDoesNotMakeRootVisible(): void
    {
        $this->subject = new Tree([]);
        $this->subject->makeNodesVisible([]);

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function makeNodesVisibleForGivenNodeMakesThisNodeVisible(): void
    {
        $this->subject = new Tree(['testNode' => false]);
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
    public function makeNodesVisibleForInexistentNodeGivenDoesNotCrash(): void
    {
        $this->subject = new Tree(['testNode' => false]);
        $this->subject->makeNodesVisible(['foo']);
    }

    /**
     * @test
     */
    public function makeNodesVisibleForInexistentNodeGivenDoesNotMakeExistingNodeVisible(): void
    {
        $this->subject = new Tree(['testNode' => false]);
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
    public function getKeysOfHiddenSubpartsForTreeWithoutNodesReturnsEmptyArray(): void
    {
        $this->subject = new Tree([]);

        self::assertSame(
            [],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithOneHiddenNodeReturnsArrayWithNodeName(): void
    {
        $this->subject = new Tree(['testNode' => false]);

        self::assertSame(
            ['testNode'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithHiddenParentAndHiddenChildReturnsArrayWithBothNodeNames(): void
    {
        $this->subject = new Tree(['child' => ['parent' => false]]);

        self::assertSame(
            ['parent', 'child'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithVisibleParentAndHiddenNodeReturnsArrayWithChildNodeName(): void
    {
        $this->subject = new Tree(['parent' => ['hidden' => false, 'visible' => true]]);

        self::assertSame(
            ['hidden'],
            $this->subject->getKeysOfHiddenSubparts()
        );
    }
}
