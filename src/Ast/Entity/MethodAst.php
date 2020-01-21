<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
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
     * @return MethodAst[]
     */
    public function methodAstNodes(FileAstResolver $fileAstResolver, ClassAstResolver $classAstResolver): array
    {
        $statementNodes = $this->rootNode->children['stmts']->children ?? [];

        $methodAstNodes = [];
        foreach ($statementNodes as $statementNode) {
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
            return $fileAst->parse()->parseMethod($methodName);
        }

        return null;
    }

    private function resolveStaticCallMethodAst(FileAstResolver $fileAstResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'self') {
            // too long and nullable
            return $fileAstResolver->resolve($this->methodContext->fqcn())->parseMethod($methodName);
        }
        if ($this->isFqcn($variableName)) {
            return $fileAstResolver->resolve($variableName)->parseMethod($methodName);
        }
//        return $fileAstResolver->resolve($this->methodContext->classContext())->parseMethod($methodName);
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    private function resolveContext(string $classNameVariable): Context
    {
        if ($this->isFqcn($classNameVariable)) {
            // remove \ prefix
            $parts = explode('\\', trim($classNameVariable, '\\'));

            [$namespace, $className] = ['', ''];
            for ($i = 0, $count = count($parts); $i < $count; $i++) {
                $str = $parts[$i];
                // end
                if ($i === $count - 1) {
                    $className = $str;
                    break;
                }
                $namespace .= '\\' . $str;
            }
            $namespace = trim($namespace, '\\');
            return new Context($namespace, $className);
        }

        // whats to do
        // resolve class from variable name?
    }
}
