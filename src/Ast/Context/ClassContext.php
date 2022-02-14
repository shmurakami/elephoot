<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Context;

// TODO consider about having class import list in context to resolve some dependency
class ClassContext implements Context
{
    use ContextBehavior;

    private string $fqcn;

    public function __construct(string $fqcn)
    {
        $this->fqcn = $fqcn;
        $this->extractNamespaceAndClass($fqcn);
    }

    public function fqcn(): string
    {
        // add \\ prefix if global namespace
        return $this->namespace . '\\' . $this->className;
    }

    public function fullName(): string
    {
        return $this->fqcn();
    }
}