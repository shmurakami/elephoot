<?php

namespace shmurakami\Spice\Example\CircularReference;

class CircularReference2
{
    public function __construct()
    {
        new CircularReference1();
    }

}
