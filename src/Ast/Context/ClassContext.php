<?php

namespace shmurakami\Elephoot\Ast\Context;

// TODO consider about having class import list in context to resolve some dependency
class ClassContext implements Context
{
    use ContextBehavior;

    /**
     * @var string
     */
    private $fqcn;

    /**
     * Context constructor.
     */
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