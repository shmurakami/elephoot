<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Entity;

use ast\Node;
use shmurakami\Elephoot\Ast\Context\MethodContext;
use shmurakami\Elephoot\Ast\Parser\AstParser;
use shmurakami\Elephoot\Output\MethodTreeNode;

class MethodAst
{
    public function __construct(
        private Node $methodRootNode,
        private MethodContext $context,
        private array $classProperties,
        private AstParser $astParser
    )
    {
    }

    public function parse(): array
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
