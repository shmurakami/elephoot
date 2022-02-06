<?php

namespace shmurakami\Elephoot\Output;

interface Adaptor
{
    /**
     * @param ClassTree $classTree
     * @return string
     */
    public function createDest(ObjectRelationTree $classTree): string;

}
