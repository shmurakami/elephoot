<?php

namespace shmurakami\Spice\Output;

class MethodCallTree
{
    /**
     * @var TreeNode
     */
    private $rootNode;

    /**
     * @var MethodCallTree[]
     */
    private $childTree = [];

    /**
     * MethodCallTree constructor.
     * @param TreeNode $rootNode
     */
    public function __construct(TreeNode $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function add(MethodCallTree $tree)
    {
        $this->childTree[] = $tree;
    }
}
