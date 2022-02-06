<?php

namespace shmurakami\Elephoot\Output;

interface ObjectRelationTree
{
    /**
     * @return ObjectRelationTree[]
     */
    public function getChildTrees(): array;

    public function replacementTree(): ObjectRelationTree;

    public function add(ObjectRelationTree $classTree);
}
