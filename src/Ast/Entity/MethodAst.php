<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Output\MethodTreeNode;

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
     * @var ClassProperty[]
     */
    private $classProperties;
    /**
     * @var Node
     */
    private $methodRootNode;
    /**
     * @var array
     */
    private $variables = [];

    /**
     * MethodAst constructor.
     * @param Node $classNode
     * @param Node $methodRootNode
     */
    // TODO change to MethodContext
    public function __construct(string $namespace, string $className, array $classProperties, Node $methodRootNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->classProperties = $classProperties;
        $this->methodRootNode = $methodRootNode;
    }

    public function parse()
    {
        // what it's required?
        // traverse node
        // keep variables if callable
        // detect method call
        // add to method call tree
        // get instance
        // trace method call
        return [];
    }

    /**
     * @return MethodAst[]
     */
    public function methodCallNodes(): array
    {
        return [];
    }

    public function treeNode(): MethodTreeNode
    {
        $fqcn = $this->namespace . '\\' . $this->className;
        $methodName = $this->methodRootNode->children['name'];
        return new MethodTreeNode($fqcn, $methodName);
    }

}
