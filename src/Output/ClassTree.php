<?php

namespace shmurakami\Spice\Output;

class ClassTree implements Tree
{
    /**
     * @var ClassTreeNode
     */
    private $rootNode;
    /**
     * @var ClassTree[]
     */
    private $childTree = [];

    /**
     * ClassTree constructor.
     */
    public function __construct(ClassTreeNode $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function add(ClassTree $classTree)
    {
        $this->childTree[] = $classTree;
    }

    public function getRootNodeName(): string
    {
        return $this->rootNode->getName();
    }

    /**
     * @return ClassTree[]
     */
    public function getChildTrees(): array
    {
        return $this->childTree;
    }
}
