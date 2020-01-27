<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
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
     *
     *
     * @return MethodAst[]
     */
    public function methodAstNodes(FileAstResolver $fileAstResolver, ClassAstResolver $classAstResolver): array
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
                    $astNodes = $this->parseNewStatement($fileAstResolver, $rightStatementNode);
                    foreach ($astNodes as $node) {
                        $methodAstNodes[] = $node;
                    }
                }
            }
            if ($statementNode->kind === Kind::AST_METHOD_CALL) {
                $statementMethodCallAstNodes = $this->methodCallAstNodes($fileAstResolver, $classAstResolver, $statementNode);
            } else if ($statementNode->kind === Kind::AST_STATIC_CALL) {
                $statementMethodCallAstNodes = $this->methodCStaticCallAstNodes($fileAstResolver, $classAstResolver, $statementNode);
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
    private function parseNewStatement(FileAstResolver $fileAstResolver, Node $node, $contexts = []): array
    {
        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $contexts[] = $this->methodContext->resolveContextByClassName($newClassName);

        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                array_map(function (string $className) use (&$contexts) {
                    $contexts[] = $this->methodContext->resolveContextByClassName($className);
                }, $this->parseNewStatement($fileAstResolver, $argumentNode, $contexts));
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
                $methodAst = $fileAstResolver->resolve($context->fqcn())->parseMethod('__construct');
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
     * @return MethodAst[]
     */
    private function methodCallAstNodes(FileAstResolver $fileAstResolver, ClassAstResolver $classAstResolver, Node $node, array $nodes = []): array
    {
        if ($node->kind !== Kind::AST_METHOD_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['expr'];

        $methodOwner = $leftStatementNode->children['name'] ?? '';
        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($fileAstResolver, $classAstResolver, $argumentNode, $nodes);
        }

        // TODO extract node is method call or static call and call parser

        $methodName = $node->children['method'];

        try {
            $methodAst = $this->resolveCallMethodAst($fileAstResolver, $methodOwner, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    private function methodCStaticCallAstNodes(FileAstResolver $fileAstResolver, ClassAstResolver $classAstResolver, Node $node, array $nodes = [])
    {
        if ($node->kind !== Kind::AST_STATIC_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['class'];

        $methodOwner = $leftStatementNode->children['name'] ?? '';
        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($fileAstResolver, $classAstResolver, $argumentNode, $nodes);
        }

        $methodName = $node->children['method'];
        try {
            $methodAst = $this->resolveStaticCallMethodAst($fileAstResolver, $methodOwner, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    private function resolveCallMethodAst(FileAstResolver $fileAstResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'this') {
            return $fileAstResolver->resolve($this->methodContext->fqcn())->parse()->parseMethod($methodName);
        }

        // how fqcn happens if instance method?
        if ($this->isFqcn($variableName)) {
            return $fileAstResolver->resolve($variableName)->parseMethod($methodName);
        }

        $context = $this->methodContext->resolveContextByClassName($variableName);
        if ($context) {
            return $fileAstResolver->resolve($context->fqcn())->parseMethod($methodName);
        }
        return null;
    }

    private function resolveStaticCallMethodAst(FileAstResolver $fileAstResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'self') {
            // too long and nullable
            return $fileAstResolver->resolve($this->methodContext->fqcn())->parseMethod($methodName);
        }

        // resolve by FQCN or imported list
        $context = $this->methodContext->resolveContextByClassName($variableName);
        if ($context) {
            return $fileAstResolver->resolve($context->fqcn())->parseMethod($methodName);
        }

        // TODO same namespace class

        return null;
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }
}
