<?php

namespace shmurakami\Spice\Output;

interface Adaptor
{
    /**
     * @param ClassTree $classTree
     * @return string
     */
    public function createDest(ObjectRelationTree $classTree): string;

}
