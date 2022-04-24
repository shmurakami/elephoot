<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example;

use shmurakami\Elephoot\Example\Import\ByImport;

class Application extends \shmurakami\Elephoot\Example\Inherit\InheritClass
    implements
    \shmurakami\Elephoot\Example\Interfaces\Implement1,
    \shmurakami\Elephoot\Example\Interfaces\Implement2
{

    use \shmurakami\Elephoot\Example\Traits\UsingTrait;

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
     * @param \shmurakami\Elephoot\Example\Method\DocComment $docComment
     */
    private function byDocComment($docComment): void
    {
    }

    private function byTypeHinting(\shmurakami\Elephoot\Example\Method\TypeHinting $typeHinting): void
    {
    }

    private function byReturn(): ?\shmurakami\Elephoot\Example\ReturnType\ReturnType
    {
        return null;
    }

    private function byUnionReturn(): null|\shmurakami\Elephoot\Example\ReturnType\ReturnType|\shmurakami\Elephoot\Example\ReturnType\UnionReturnType
    {
        return null;
    }

    /**
     * @return \shmurakami\Elephoot\Example\ReturnType\ReturnInDocComment|null
     */
    private function byReturnDocComment()
    {
    }

    private function byNewStatement()
    {
        new \shmurakami\Elephoot\Example\NewStatement\SimplyNew();
        (function () {
            new \shmurakami\Elephoot\Example\NewStatement\NewInClosure();
        });

        $_ = fn () => new \shmurakami\Elephoot\Example\NewStatement\NewInShorthandClosure();

        $foo = new \shmurakami\Elephoot\Example\NewStatement\NewStatement();
        return $foo->foo(new \shmurakami\Elephoot\Example\NewStatement\NewStatementArgument(
            new \shmurakami\Elephoot\Example\NewStatement\NewStatementArgumentArgument()
        ));
    }

    private function byStaticMethodCall()
    {
        \shmurakami\Elephoot\Example\StaticMethod\StaticMethodCall::byStaticMethodCall(new \shmurakami\Elephoot\Example\StaticMethod\StaticMethodCallArgument());
    }

    private function breakingPsr()
    {
        return new \BreakingPsr();
    }

    /**
     * @return string
     */
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

    public function animal()
    {
        return \shmurakami\Elephoot\Example\Enum\Animal::CAT;
    }
}
