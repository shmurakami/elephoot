<?php

namespace shmurakami\Spice\Ast\Context;

class MethodContext implements Context
{
    use ContextBehavior;

    /**
     * @var string
     */
    private $fqcn;
    /**
     * @var string
     */
    private $method;

    /**
     * MethodContext constructor.
     */
    public function __construct(string $fqcn, string $method)
    {
        $this->fqcn = $fqcn;
        $this->method = $method;

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
}
