<?php

namespace shmurakami\Spice\Output;

class ClassTree implements ObjectRelationTree
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

    public function add(ObjectRelationTree $classTree)
    {
        $this->childTree[] = $classTree;
    }

    public function getRootNode(): ClassTreeNode
    {
        return $this->rootNode;
    }

    public function getRootNodeClassName(): string
    {
        return $this->rootNode->getClassName();
    }

    /**
     * @return ClassTreeNode[]
     */
    public function getChildNodes(): array
    {
        return array_map(function (ClassTree $classTree) {
            return $classTree->getRootNode();
        }, $this->childTree);
    }

    /**
     * @return ClassTree[]
     */
    public function getChildTrees(): array
    {
        return $this->childTree;
    }

    public function replacementTree(): ObjectRelationTree
    {
        // dump child tree i.e. shallow copy
        return new ClassTree($this->rootNode);
    }
}
