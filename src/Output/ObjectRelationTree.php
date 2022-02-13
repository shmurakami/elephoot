<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

interface ObjectRelationTree
{
    /**
     * @return ObjectRelationTree[]
     */
    public function getChildTrees(): array;

    public function replacementTree(): ObjectRelationTree;

    public function add(ObjectRelationTree $tree): void;

    public function getRootNodeClassName(): string;
}
