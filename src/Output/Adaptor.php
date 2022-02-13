<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

interface Adaptor
{
    /**
     * @param ObjectRelationTree $classTree
     * @return string
     */
    public function createDest(ObjectRelationTree $classTree): string;

}
