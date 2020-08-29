<?php

namespace shmurakami\Spice\Output;

class MethodCallTree implements ObjectRelationTree
{
    /**
     * @var MethodTreeNode
     */
    private $rootNode;

    /**
     * @var MethodCallTree[]
     */
    private $childTree = [];

    /**
     * MethodCallTree constructor.
     * @param MethodTreeNode $rootNode
     */
    public function __construct(MethodTreeNode $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function add(ObjectRelationTree $tree)
    {
        $this->childTree[] = $tree;
    }

    public function getChildTrees(): array
    {
        return $this->childTree;
    }

    public function replacementTree(): ObjectRelationTree
    {
        // dump child tree i.e. shallow copy
        return new MethodCallTree($this->rootNode);
    }
}
