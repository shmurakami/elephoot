<?php

namespace shmurakami\Spice\Example\Inherit;

class InheritClass
{
    private $_;

    public function __construct()
    {
        $this->_ = new InheritDependency();
    }
}
