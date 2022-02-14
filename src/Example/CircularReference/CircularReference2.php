<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example\CircularReference;

class CircularReference2
{
    public function __construct()
    {
        new CircularReference1();
    }

}
