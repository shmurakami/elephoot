<?php

namespace shmurakami\Spice\Example;

class ExtendApplication extends Application
{
    public function sampleMethod(string $name): string
    {
        return "Howdy $name";
    }

}
