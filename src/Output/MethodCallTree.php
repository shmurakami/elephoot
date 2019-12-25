<?php

namespace shmurakami\Spice\Output;

class MethodCallTree
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

    public function add(MethodCallTree $tree)
    {
        $this->childTree[] = $tree;
    }
}
