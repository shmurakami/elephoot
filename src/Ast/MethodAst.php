<?php

namespace shmurakami\Spice\Ast;

use ast\Node;

class MethodAst
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
    private $methodRootNode;

    /**
     * MethodAst constructor.
     */
    public function __construct(string $namespace, string $className, Node $methodRootNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->methodRootNode = $methodRootNode;
    }
}
