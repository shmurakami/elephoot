<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class Drawer
{
    /**
     * @var Adaptor
     */
    private $adaptor;

    public function __construct(Adaptor $adaptor)
    {
        $this->adaptor = $adaptor;
    }

    public function draw(ObjectRelationTree $classTree): string
    {
        return $this->adaptor->createDest($classTree);
    }
}
