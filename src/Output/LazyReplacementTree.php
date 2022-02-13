<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class LazyReplacementTree implements ObjectRelationTree
{

    public function __construct(private string $className)
    {
    }

    /**
     * @return string
     */
    public function nameShouldBeReplaced(): string
    {
        return $this->className;
    }

    public function getChildTrees(): array
    {
        return [];
    }

    public function replacementTree(): ObjectRelationTree
    {
        return $this;
    }

    public function add(ObjectRelationTree $tree)
    {
    }

    public function getRootNodeClassName(): string
    {
        return $this->className;
    }
}
