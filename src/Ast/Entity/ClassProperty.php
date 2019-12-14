<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;

class ClassProperty
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
     * @var Node
     */
    private $propertyNode;

    public function __construct(string $namespace, string $className, Node $propertyNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->propertyNode = $propertyNode;
    }

    /**
     * TODO check it's callable
     */
    public function parse(): ?ClassAst
    {

    }

    public function isCallable(): bool
    {
        return true;
    }

}
