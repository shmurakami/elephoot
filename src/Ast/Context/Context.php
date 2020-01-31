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
        if ($this->namespace) {
            return $this->namespace . '\\' . $this->className;
        }

        // don't use \ prefix for now
        return $this->className;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
