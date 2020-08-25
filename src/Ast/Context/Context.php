<?php

namespace shmurakami\Spice\Ast\Context;

class Context
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $className;

    /**
     * Context constructor.
     */
    public function __construct(string $namespace, string $className)
    {
        $this->namespace = $namespace;
        $this->className = $className;
    }

    public function fqcn(): string
    {
        return $this->namespace . '\\' . $this->className;
    }
}