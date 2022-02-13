<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example;

class ExtendApplication extends Application
{
    public function sampleMethod(string $name): string
    {
        return "Howdy $name";
    }

}
