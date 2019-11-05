<?php
declare(strict_types = 1);

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a visibility tree.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Visibility_Tree
{
    /**
     * @var \Tx_Oelib_Visibility_Node[] all nodes within the tree referenced by their keys
     */
    private $nodes = [];

    /**
     * @var \Tx_Oelib_Visibility_Node
     */
    private $rootNode = null;

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
     * @param array $treeStructure the tree structure in a nested array, may be empty
     */
    public function __construct(array $treeStructure)
    {
        $this->rootNode = GeneralUtility::makeInstance(\Tx_Oelib_Visibility_Node::class);

        $this->buildTreeFromArray($treeStructure, $this->rootNode);
    }

    /**
     * Builds the node tree from the given structure.
     *
     * @param array $treeStructure
     *        the tree structure as array, may be empty
     * @param \Tx_Oelib_Visibility_Node $parentNode
     *        the parent node for the current key
     *
     * @return void
     */
    private function buildTreeFromArray(
        array $treeStructure,
        \Tx_Oelib_Visibility_Node $parentNode
    ) {
        foreach ($treeStructure as $nodeKey => $nodeContents) {
            /** @var \Tx_Oelib_Visibility_Node $childNode */
            $childNode = GeneralUtility::makeInstance(\Tx_Oelib_Visibility_Node::class);
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
     * \Tx_Oelib_Template::hideSubpartsArray.
     *
     * @return string[] the key of the subparts which are hidden, will be empty if no elements are hidden
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
     * @return \Tx_Oelib_Visibility_Node the root node
     */
    public function getRootNode(): \Tx_Oelib_Visibility_Node
    {
        return $this->rootNode;
    }

    /**
     * Makes nodes in the tree visible.
     *
     * @param string[] $nodeKeys
     *        the keys of the visible nodes, may be empty
     *
     * @return void
     */
    public function makeNodesVisible(array $nodeKeys)
    {
        foreach ($nodeKeys as $nodeKey) {
            if (isset($this->nodes[$nodeKey])) {
                $this->nodes[$nodeKey]->markAsVisible();
            }
        }
    }
}
