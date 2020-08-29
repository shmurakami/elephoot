<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Parser\AstParser;
use shmurakami\Spice\Output\MethodTreeNode;

class MethodAst
{
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
     * @var MethodContext
     */
    private $context;
    /**
     * @var AstParser
     */
    private $astParser;

    /**
     * MethodAst constructor.
     * @param Node $methodRootNode
     * @param MethodContext $context
     * @param ClassProperty[] $classProperties
     */
    public function __construct(Node $methodRootNode, MethodContext $context, array $classProperties, AstParser $astParser)
    {
        $this->methodRootNode = $methodRootNode;
        $this->context = $context;
        $this->classProperties = $classProperties;
        $this->astParser = $astParser;
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
        $fqcn = $this->context->fqcn();
        $methodName = $this->context->methodName();
        return new MethodTreeNode($fqcn, $methodName);
    }

}
