<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
use shmurakami\Spice\Ast\Resolver\MethodAstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\MethodTreeNode;
use shmurakami\Spice\Stub\Kind;

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
     * @var Context[]
     * variableName => Context
     */
    private $variableMap = [];

    /**
     * MethodAst constructor.
     */
    public function __construct(MethodContext $methodContext, Node $rootNode)
    {
        $this->methodContext = $methodContext;
        $this->rootNode = $rootNode;

        // how to retrieve arg nodes?
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
    public function methodAstNodes(MethodAstResolver $methodAstResolver): array
    {
        $statementNodes = $this->rootNode->children['stmts']->children ?? [];

        $methodAstNodes = [];
        foreach ($statementNodes as $statementNode) {
            if ($statementNode->kind === Kind::AST_ASSIGN) {
                // TODO update variable map
                $name = $statementNode->children['var']->children['name'];
                $rightStatementNode = $statementNode->children['expr'];
                // new statement
                if ($rightStatementNode->kind === Kind::AST_NEW) {
                    $astNodes = $this->parseNewStatement($methodAstResolver, $rightStatementNode);
                    foreach ($astNodes as $node) {
                        $methodAstNodes[] = $node;
                    }
                }
            }
            if ($statementNode->kind === Kind::AST_METHOD_CALL) {
                $statementMethodCallAstNodes = $this->methodCallAstNodes($methodAstResolver, $statementNode);
            } else if ($statementNode->kind === Kind::AST_STATIC_CALL) {
                $statementMethodCallAstNodes = $this->methodCStaticCallAstNodes($methodAstResolver, $statementNode);
            } else {
                continue;
            }
            foreach ($statementMethodCallAstNodes as $statementMethodCallAstNode) {
                $methodAstNodes[] = $statementMethodCallAstNode;
            }
        }

        return $methodAstNodes;
    }

    /**
     * @param FileAstResolver $fileAstResolver
     * @param Node $node
     * @param Context[] $contexts
     * @return MethodAst[]
     */
    private function parseNewStatement(MethodAstResolver $methodAstResolver, Node $node, $contexts = []): array
    {
        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $contexts[] = $methodAstResolver->resolveContext($newClassName);

        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                array_map(function (string $className) use ($methodAstResolver, &$contexts) {
                    $contexts[] = $methodAstResolver->resolveContext($className);
                }, $this->parseNewStatement($methodAstResolver, $argumentNode, $contexts));
            }
            // method call
            if (false) {
                // TODO
            }
        }

        // remove null
        $contexts = array_values($contexts);
        $methodAsts = [];
        foreach ($contexts as $context) {
            // new statement calls constructor
            try {
                $methodAst = $methodAstResolver->resolve($context->fqcn(), '__construct');
                if ($methodAst) {
                    $methodAsts[] = $methodAst;
                }
            } catch (MethodNotFoundException $exception) {
                continue;
            }
        }
        return $methodAsts;
    }

    public function treeNode(): MethodTreeNode
    {
        $fqcn = $this->methodContext->fqcn();
        $methodName = $this->rootNode->children['name'];
        return new MethodTreeNode($fqcn, $methodName);
    }

    /**
     * @param MethodAstResolver $methodAstResolver
     * @param Node $node
     * @param array $nodes
     * @return MethodAst[]
     */
    private function methodCallAstNodes(MethodAstResolver $methodAstResolver, Node $node, array $nodes = []): array
    {
        if ($node->kind !== Kind::AST_METHOD_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['expr'];

        $methodOwner = $leftStatementNode->children['name'] ?? '';
        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($methodAstResolver, $argumentNode, $nodes);
        }

        // TODO extract node is method call or static call and call parser

        $methodName = $node->children['method'];

        try {
            $methodAst = $this->resolveCallMethodAst($methodAstResolver, $methodOwner, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    private function methodCStaticCallAstNodes(MethodAstResolver $methodAstResolver, Node $node, array $nodes = [])
    {
        if ($node->kind !== Kind::AST_STATIC_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['class'];

        $methodOwner = $leftStatementNode->children['name'] ?? '';
        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($methodAstResolver, $argumentNode, $nodes);
        }

        $methodName = $node->children['method'];
        try {
            $methodAst = $this->resolveStaticCallMethodAst($methodAstResolver, $methodOwner, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    private function resolveCallMethodAst(MethodAstResolver $methodAstResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'this') {
            return $methodAstResolver->resolve($this->methodContext->fqcn(), $methodName);
        }

        // how fqcn happens if instance method?
        if ($this->isFqcn($variableName)) {
            return $methodAstResolver->resolve($variableName, $methodName);
        }

        $context = $methodAstResolver->resolveContext($variableName);
        if ($context) {
            return $methodAstResolver->resolve($context->fqcn(), $methodName);
        }
        return null;
    }

    private function resolveStaticCallMethodAst(MethodAstResolver $methodAstResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'self') {
            // too long and nullable
            return $methodAstResolver->resolve($this->methodContext->fqcn(), $methodName);
        }

        // resolve by FQCN or imported list
        $context = $methodAstResolver->resolveContext($variableName);
        if ($context) {
            return $methodAstResolver->resolve($context->fqcn(), $methodName);
        }

        // TODO same namespace class

        return null;
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    public function fqcn(): string
    {
        return $this->methodContext->fqcn();
    }
}
