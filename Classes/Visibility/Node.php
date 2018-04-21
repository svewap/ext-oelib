<?php

/**
 * This class represents a node for a visibility tree.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Visibility_Node
{
    /**
     * @var \Tx_Oelib_Visibility_Node[] numeric array with all direct children of this node
     */
    private $children = [];

    /**
     * @var \Tx_Oelib_Visibility_Node the parent node of this node
     */
    private $parentNode = null;

    /**
     * @var bool whether this node is visible
     */
    private $isVisible;

    /**
     * Constructor of this class.
     *
     * @param bool $isVisible whether this node should be initially visible
     */
    public function __construct($isVisible = false)
    {
        $this->isVisible = $isVisible;
    }

    /**
     * Destructor of this class. Tries to free as much memory as possible.
     */
    public function __destruct()
    {
        unset($this->parentNode, $this->children);
    }

    /**
     * Adds a child to this node.
     *
     * @param \Tx_Oelib_Visibility_Node $child the child to add to this node
     *
     * @return void
     */
    public function addChild(\Tx_Oelib_Visibility_Node $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Sets the parent node of this node.
     *
     * The parent can only be set once.
     *
     * @param \Tx_Oelib_Visibility_Node $parentNode the parent node to add
     *
     * @return void
     */
    public function setParent(\Tx_Oelib_Visibility_Node $parentNode)
    {
        if ($this->parentNode !== null) {
            throw new \InvalidArgumentException('This node already has a parent node.', 1331488668);
        }

        $this->parentNode = $parentNode;
    }

    /**
     * Returns the visibility status of this node.
     *
     * @return bool TRUE if this node is visible, FALSE otherwise
     */
    public function isVisible()
    {
        return $this->isVisible;
    }

    /**
     * Marks this node as visible and propagates the visibility recursively to
     * the parent up to the root.
     *
     * @return void
     */
    public function markAsVisible()
    {
        $this->isVisible = true;
        if ($this->parentNode) {
            $this->parentNode->markAsVisible();
        }
    }

    /**
     * Returns the children set for the current node.
     *
     * @return \Tx_Oelib_Visibility_Node[] this node's children, will be empty if no children are set
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns the parent node set for this node.
     *
     * @return \Tx_Oelib_Visibility_Node the parent node of this node, will be
     *                                  empty if no parent was set
     */
    public function getParent()
    {
        return $this->parentNode;
    }
}
