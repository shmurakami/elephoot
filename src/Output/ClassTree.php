<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class ClassTree implements ObjectRelationTree
{
    /**
     * @var ClassTree[]
     */
    private array $childTree = [];

    public function __construct(private ClassTreeNode $rootNode)
    {
    }

    public function add(ObjectRelationTree $tree): void
    {
        /** @psalm-suppress PropertyTypeCoercion */
        $this->childTree[] = $tree;
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
