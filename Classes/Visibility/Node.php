<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Visibility;

/**
 * This class represents a node for a visibility tree.
 */
class Node
{
    /**
     * @var array<int, Node> all direct children of this node
     */
    private $children = [];

    /**
     * @var Node|null the parent node of this node
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
    public function __construct(bool $isVisible = false)
    {
        $this->isVisible = $isVisible;
    }

    /**
     * Adds a child to this node.
     *
     * @param Node $child the child to add to this node
     */
    public function addChild(Node $child): void
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Sets the parent node of this node.
     *
     * The parent can only be set once.
     *
     * @param Node $parentNode the parent node to add
     */
    public function setParent(Node $parentNode): void
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
    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    /**
     * Marks this node as visible and propagates the visibility recursively to
     * the parent up to the root.
     */
    public function markAsVisible(): void
    {
        $this->isVisible = true;
        if ($this->parentNode instanceof Node) {
            $this->parentNode->markAsVisible();
        }
    }

    /**
     * Returns the children set for the current node.
     *
     * @return array<int, Node> this node's children, will be empty if no children are set
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Returns the parent node set for this node.
     */
    public function getParent(): ?Node
    {
        return $this->parentNode;
    }
}
