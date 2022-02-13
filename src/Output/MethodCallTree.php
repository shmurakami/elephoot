<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class MethodCallTree implements ObjectRelationTree
{
    /**
     * @var MethodCallTree[]
     */
    private $childTree = [];

    public function __construct(private MethodTreeNode $rootNode)
    {
    }

    public function add(ObjectRelationTree $tree): void
    {
        /** @psalm-suppress PropertyTypeCoercion */
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

    public function getRootNodeClassName(): string
    {
        return $this->rootNode->getClassName();
    }
}
