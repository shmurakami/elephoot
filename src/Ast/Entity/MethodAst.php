<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\MethodTreeNode;
use shmurakami\Spice\Stub\Kind;
use const ast\AST_STATIC_CALL;

class MethodAst
{
    /**
     * @var Node
     */
    private $rootNode;
    /**
     * @var MethodContext
     */
    private $methodContext;

    /**
     * MethodAst constructor.
     */
    public function __construct(MethodContext $methodContext, Node $rootNode)
    {
        $this->methodContext = $methodContext;
        $this->rootNode = $rootNode;
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
    public function methodAstNodes(ClassAstResolver $classAstResolver): array
    {
        /**
         * - this method
         * - self static method
         * - external static method
         * - argument instance method
         * - method chain
         * - new instance
         * - in closure, this
         * - in closure use statement
         * - generator
         * - recursive method call
         * - property class method
         *
         * - function call?
         */
        $statementNodes = $this->rootNode->children['stmts']->children ?? [];

        $methodAstNodes = [];
        foreach ($statementNodes as $statementNode) {
            $statementMethodCallAstNodes = $this->methodCallAstNodes($classAstResolver, $statementNode);
            foreach ($statementMethodCallAstNodes as $statementMethodCallAstNode) {
                $methodAstNodes[] = $statementMethodCallAstNode;
            }
        }

        return $methodAstNodes;
    }

    public function treeNode(): MethodTreeNode
    {
        $fqcn = $this->methodContext->fqcn();
        $methodName = $this->rootNode->children['name'];
        return new MethodTreeNode($fqcn, $methodName);
    }

    /**
     * @return MethodAst[]
     */
    private function methodCallAstNodes(ClassAstResolver $classAstResolver, Node $node, array $nodes = []): array
    {
        if ($node->kind === Kind::AST_METHOD_CALL) {
            $leftStatementNode = $node->children['expr'];
            $methodOwner = $leftStatementNode->children['name'] ?? '';
            $argumentNodes = $node->children['args']->children ?? [];
            foreach ($argumentNodes as $argumentNode) {
                $nodes = $this->methodCallAstNodes($classAstResolver, $argumentNode, $nodes);
            }

            $ownerContext = $this->retrieveClassContext($methodOwner);
            $classAst = $classAstResolver->resolve($ownerContext->fqcn());
            if (!$classAst) {
                return $nodes;
            }
            $methodName = $node->children['method'];
            try {
                $nodes[] = $classAst->parseMethod($methodName);
            } catch (MethodNotFoundException $e) {
                return $nodes;
            }
        }

        if ($node->kind === Kind::AST_STATIC_CALL) {

        }

        return $nodes;
    }

    private function retrieveClassContext(string $variableName): Context
    {
        if ($variableName === 'this') {
            return $this->methodContext->classContext();
        }

    }
}
