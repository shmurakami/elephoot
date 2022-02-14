<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Context;

class MethodContext implements Context
{
    use ContextBehavior;

    public function __construct(private string $fqcn, private string $method)
    {
        $this->extractNamespaceAndClass($fqcn);
    }

    public function fqcn(): string
    {
        // add \\ prefix if global namespace
        return $this->namespace . '\\' . $this->className;
    }

    public function fullName(): string
    {
        return $this->fqcn() . '@' . $this->method;
    }

    public function methodName(): string
    {
        return $this->method;
    }
}
