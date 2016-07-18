<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Visibility_TreeTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_Visibility_Tree
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
        $this->subject = new Tx_Oelib_Visibility_Tree(array());

        self::assertSame(
            array(),
            $this->subject->getRootNode()->getChildren()
        );
    }

    /**
     * @test
     */
    public function constructWithOneElementInArrayAddsOneChildToRootNode()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));

        $children = $this->subject->getRootNode()->getChildren();

        self::assertInstanceOf(Tx_Oelib_Visibility_Node::class, $children[0]);
    }

    /**
     * @test
     */
    public function constructWithTwoElementsInFirstArrayLevelAddsTwoChildrenToRootNode()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false, 'testNode2' => false));

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
        $this->subject = new Tx_Oelib_Visibility_Tree(array('child' => array('grandChild' => false)));

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
        $this->subject = new Tx_Oelib_Visibility_Tree(array('visibleNode' => true));

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
        $this->subject = new Tx_Oelib_Visibility_Tree(array('hiddenNode' => false));

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
        $this->subject = new Tx_Oelib_Visibility_Tree(array());

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneInvisibleChildIsInvisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleChildIsVisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => true));

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function rootNodeWithOneVisibleGrandChildIsVisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('child' => array('grandChild' => true)));

        self::assertTrue(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function childOfRootNodeWithOneVisibleChildIsVisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('child' => array('grandChild' => true)));

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
        $this->subject = new Tx_Oelib_Visibility_Tree(array());
        $this->subject->makeNodesVisible(array());

        self::assertFalse(
            $this->subject->getRootNode()->isVisible()
        );
    }

    /**
     * @test
     */
    public function makeNodesVisibleForGivenNodeMakesThisNodeVisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));
        $this->subject->makeNodesVisible(array('testNode'));

        $this->subject->getRootNode()->getChildren();

        self::assertSame(
            array(),
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function makeNodesVisibleForInexistentNodeGivenDoesNotCrash()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));
        $this->subject->makeNodesVisible(array('foo'));
    }

    /**
     * @test
     */
    public function makeNodesVisibleForInexistentNodeGivenDoesNotMakeExistingNodeVisible()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));
        $this->subject->makeNodesVisible(array('foo'));

        $this->subject->getRootNode()->getChildren();

        self::assertSame(
            array('testNode'),
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
        $this->subject = new Tx_Oelib_Visibility_Tree(array());

        self::assertSame(
            array(),
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithOneHiddenNodeReturnsArrayWithNodeName()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('testNode' => false));

        self::assertSame(
            array('testNode'),
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithOneHiddenParentNodeAndOneHiddenChildNodeReturnsArrayWithBothNodeNames()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('child' => array('parent' => false)));

        self::assertSame(
            array('parent', 'child'),
            $this->subject->getKeysOfHiddenSubparts()
        );
    }

    /**
     * @test
     */
    public function getKeysOfHiddenSubpartsForTreeWithVisibleParentNodeAndOneHiddenChildNodeReturnsArrayWithChildNodeName()
    {
        $this->subject = new Tx_Oelib_Visibility_Tree(array('parent' => array('hidden' => false, 'visible' => true)));

        self::assertSame(
            array('hidden'),
            $this->subject->getKeysOfHiddenSubparts()
        );
    }
}
