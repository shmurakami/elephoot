<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example\Inherit;

class InheritClass
{
    private $_;

    public function __construct()
    {
        $this->_ = new InheritDependency();
    }
}
