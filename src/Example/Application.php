<?php

namespace shmurakami\Spice\Example;

class Application
{

    /**
     * Application constructor.
     */
    public function __construct()
    {
    }

    public function sampleMethod(string $name): string
    {
        $greeting = $this->internalMethod();
        return "$greeting $name";
    }

    private function internalMethod(): string
    {
        return 'Hello';
    }

    public function callByClosure(string $name): string
    {
        $f = function () use ($name) {
            return $this->sampleMethod($name);
        };
        return $f();
    }
}
