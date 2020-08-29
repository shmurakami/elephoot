<?php

namespace shmurakami\Spice\Example\CircularReference;

class CircularReference1
{
    public function __construct()
    {
        new CircularReference2();
    }

}
