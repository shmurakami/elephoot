<?php

namespace shmurakami\Spice\Output;

class MethodTree implements Tree
{
    /**
     * @var MethodTreeNode
     */
    private $rootNode;

    /**
     * @var MethodTree[]
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

    public function add(MethodTree $tree)
    {
        $this->childTree[] = $tree;
    }

    public function getRootNodeName(): string
    {
        return $this->rootNode->getName();
    }

    /**
     * @return MethodTree[]
     */
    public function getChildTrees(): array
    {
        return $this->childTree;
    }
}
