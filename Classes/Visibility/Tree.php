<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Visibility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a visibility tree.
 *
 * @deprecated will be removed in oelib 6.0
 */
class Tree
{
    /**
     * @var array<string, Node> all nodes within the tree referenced by their keys
     */
    private $nodes = [];

    /**
     * @var Node
     */
    private $rootNode;

    /**
     * Initializes the tree structure.
     *
     * Example for a tree array:
     *  array(ParentNode => array(
     *   ChildNode1 => TRUE,
     *   ChildNode2 => array(
     *     GrandChildNode1 => TRUE,
     *     GrandChildNode2 => FALSE
     *   ),
     *  ));
     * If an array element has the value TRUE it will be marked as visible, if
     * it has the value FALSE it will be invisible.
     * These elements represent leaves in the visibility tree.
     *
     * @param array<string, array<string, mixed>|bool> $treeStructure the tree structure in a nested array, may be empty
     */
    public function __construct(array $treeStructure)
    {
        $this->rootNode = GeneralUtility::makeInstance(Node::class);

        $this->buildTreeFromArray($treeStructure, $this->rootNode);
    }

    /**
     * Builds the node tree from the given structure.
     *
     * @param array<string, array<string, mixed>|bool> $treeStructure the tree structure as array, may be empty
     * @param Node $parentNode the parent node for the current key
     */
    private function buildTreeFromArray(
        array $treeStructure,
        Node $parentNode
    ): void {
        foreach ($treeStructure as $nodeKey => $nodeContents) {
            $childNode = GeneralUtility::makeInstance(Node::class);
            $parentNode->addChild($childNode);

            if (is_array($nodeContents)) {
                $this->buildTreeFromArray($nodeContents, $childNode);
            } elseif ($nodeContents === true) {
                $childNode->markAsVisible();
            }

            $this->nodes[$nodeKey] = $childNode;
        }
    }

    /**
     * Creates a numeric array of all subparts that still are hidden.
     *
     * The output of this function can be used for
     * `Template::hideSubpartsArray`.
     *
     * @return array<int, string> the keys of the subparts which are hidden, will be empty if no elements are hidden
     */
    public function getKeysOfHiddenSubparts(): array
    {
        $keysToHide = [];

        foreach ($this->nodes as $key => $node) {
            if (!$node->isVisible()) {
                $keysToHide[] = $key;
            }
        }

        return $keysToHide;
    }

    /**
     * Returns the root node.
     *
     * @return Node the root node
     */
    public function getRootNode(): Node
    {
        return $this->rootNode;
    }

    /**
     * Makes nodes in the tree visible.
     *
     * @param string[] $nodeKeys
     *        the keys of the visible nodes, may be empty
     */
    public function makeNodesVisible(array $nodeKeys): void
    {
        foreach ($nodeKeys as $nodeKey) {
            if (isset($this->nodes[$nodeKey])) {
                $this->nodes[$nodeKey]->markAsVisible();
            }
        }
    }
}
