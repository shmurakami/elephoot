<?php

namespace shmurakami\Spice\Output;

class ClassTree
{
    /**
     * @var ClassTreeNode
     */
    private $rootNod;
    /**
     * @var ClassTree[]
     */
    private $childTree = [];

    /**
     * ClassTree constructor.
     */
    public function __construct(ClassTreeNode $rootNod)
    {
        $this->rootNod = $rootNod;
    }

    public function add(ClassTreeNode $node)
    {
        $this->childTree[] = $node;
    }
}
