<?php

namespace shmurakami\Spice\Example;

use shmurakami\Spice\Example\Import\ByImport;

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
        return $this->createForImportedClassInstance()->nest();
    }

    private function createForImportedClassInstance()
    {
        return new ByImport();
    }

    /**
     * @param \shmurakami\Spice\Example\Method\DocComment $docComment
     */
    private function byDocComment($docComment): void
    {
    }

    private function byTypeHinting(\shmurakami\Spice\Example\Method\TypeHinting $typeHinting): void
    {
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
