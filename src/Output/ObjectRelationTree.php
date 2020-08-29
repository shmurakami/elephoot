<?php

namespace shmurakami\Spice\Output;

interface ObjectRelationTree
{
    /**
     * @return ObjectRelationTree[]
     */
    public function getChildTrees(): array;

    public function replacementTree(): ObjectRelationTree;

    public function add(ObjectRelationTree $classTree);
}
