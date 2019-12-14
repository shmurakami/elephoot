<?php

namespace shmurakami\Spice\Example;

use shmurakami\Spice\Example\Nest\NestClass;

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

    public function callNest(): string
    {
        return $this->createNestInstance()->nest();
    }

    private function createNestInstance()
    {
        return new NestClass();
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
