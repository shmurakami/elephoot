<?php

namespace shmurakami\Spice\Example;

use shmurakami\Spice\Example\Import\ByImport;

class Application extends \shmurakami\Spice\Example\Inherit\InheritClass
    implements
    \shmurakami\Spice\Example\Interfaces\Implement1,
    \shmurakami\Spice\Example\Interfaces\Implement2
{

    use \shmurakami\Spice\Example\Traits\UsingTrait;

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

    private function byReturn(): ?\shmurakami\Spice\Example\ReturnType\ReturnType
    {
        return null;
    }

    /**
     * @return \shmurakami\Spice\Example\ReturnType\ReturnInDocComment|null
     */
    private function byReturnDocComment()
    {
    }

    private function byNewStatement()
    {
        new \shmurakami\Spice\Example\NewStatement\SimplyNew();
        (function () {
            new \shmurakami\Spice\Example\NewStatement\NewInClosure();
        });

        $foo = new \shmurakami\Spice\Example\NewStatement\NewStatement();
        return $foo->foo(new \shmurakami\Spice\Example\NewStatement\NewStatementArgument(
            new \shmurakami\Spice\Example\NewStatement\NewStatementArgumentArgument()
        ));
    }

    private function byStaticMethodCall()
    {
        \shmurakami\Spice\Example\StaticMethod\StaticMethodCall::byStaticMethodCall(new \shmurakami\Spice\Example\StaticMethod\StaticMethodCallArgument());
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
