<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class Drawer
{

    public function __construct(private Adaptor $adaptor)
    {
    }

    public function draw(ObjectRelationTree $classTree): string
    {
        return $this->adaptor->createDest($classTree);
    }
}
