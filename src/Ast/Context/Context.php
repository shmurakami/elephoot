<?php

namespace shmurakami\Spice\Ast\Context;

use shmurakami\Spice\Ast\Entity\Imports;

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
     * @var Imports
     */
    private $imports;

    /**
     * Context constructor.
     */
    public function __construct(string $namespace, string $className, Imports $imports)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->imports = $imports;
    }

    public function fqcn(): string
    {
        return $this->namespace . '\\' . $this->className;
    }
}
