<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Resolver\AstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\MethodTreeNode;
use shmurakami\Spice\Stub\Kind;

class MethodAst
{
    const RESOLVE_GENERATOR = 'RESOLVE_GENERATOR';

    /**
     * @var Node
     */
    private $rootNode;
    /**
     * @var MethodContext
     */
    private $methodContext;
    /**
     * @var VariableMapTrait
     */
    private $propertyMap;
    /**
     * @var VariableMapTrait
     */
    private $variableMap;

    /**
     * MethodAst constructor.
     * @param MethodContext $methodContext
     * @param Node $rootNode
     * @param VariableMapTrait $propertyMap e.g. change property and call self instance method, need to be traced
     */
    public function __construct(MethodContext $methodContext, Node $rootNode, $propertyMap = null)
    {
        $this->methodContext = $methodContext;
        $this->rootNode = $rootNode;

        $this->propertyMap = $propertyMap ?? new class {
            use VariableMapTrait;
        };
        $this->variableMap =  new class {
            use VariableMapTrait;
        };
        // how to retrieve arg nodes?
    }

    /**
     * @return MethodAst[]
     */
    public function dependentMethodAstList(AstResolver $astResolver): array
    {
        $resolver = $this->resolver($astResolver);
        $this->parseLine($astResolver, $resolver, $this->rootNode, []);

        $resolver->send(self::RESOLVE_GENERATOR);
        return $resolver->getReturn();
    }

    /**
     * @param AstResolver $astResolver
     * @param Generator $resolver
     * @param Node $rootNode
     * @param MethodAst[] $methodAstList
     */
    private function parseLine(AstResolver $astResolver, Generator $resolver, Node $rootNode, array $methodAstList)
    {
        foreach ($this->statementNodes($rootNode) as $statementNode) {
            $statementMethodCallAstNodes = [];
            switch ($statementNode->kind) {
                case Kind::AST_ASSIGN:
                    $this->parseStatement($astResolver, $resolver, $statementNode, $methodAstList);
                    break;
                case Kind::AST_METHOD_CALL:
                    $statementMethodCallAstNodes = $this->methodCallAstNodes($astResolver, $statementNode);
                    break;
                case Kind::AST_STATIC_CALL:
                    $statementMethodCallAstNodes = $this->methodStaticCallAstNodes($astResolver, $statementNode);
                    break;
                case Kind::AST_CALL:
                    // like call unnamed function
                    break;
                case Kind::AST_PROP:
                    // TODO method chain $this->parseLine(); with prop and then method call
                    break;
                default:
                    break;
            }

            foreach ($statementMethodCallAstNodes as $statementMethodCallAstNode) {
                $resolver->send($statementMethodCallAstNode);
            }
        }
    }

    /**
     * @param AstResolver $astResolver
     * @param Generator $resolver
     * @param Node $statementNode
     * @param MethodAst[] $methodAstList
     * @return MethodAst[]
     */
    private function parseStatement(AstResolver $astResolver, Generator $resolver, Node $statementNode, array $methodAstList)
    {
        // TODO update variable map
        $name = $statementNode->children['var']->children['name'];
        $rightStatementNode = $statementNode->children['expr'];

        switch ($rightStatementNode->kind) {
            case Kind::AST_NEW:
                $methodAstList = $this->parseNewStatement($astResolver, $rightStatementNode);
                foreach ($methodAstList as $methodAst) {
                    $resolver->send($methodAst);
                }
                break;
            case Kind::AST_CLOSURE:
                // parse immediately if closure is detected. no need to see call closure
                $this->parseLine($astResolver, $resolver, $rightStatementNode, $methodAstList);
                break;
            // TODO parsLineと同じなので構造を整理して共通化する
            case Kind::AST_METHOD_CALL:
                $statementMethodCallAstNodes = $this->methodCallAstNodes($astResolver, $statementNode);
                foreach ($statementMethodCallAstNodes as $statementMethodCallAstNode) {
                    $resolver->send($statementMethodCallAstNode);
                }
                break;
            case Kind::AST_STATIC_CALL:
                $statementMethodCallAstNodes = $this->methodStaticCallAstNodes($astResolver, $statementNode);
                foreach ($statementMethodCallAstNodes as $statementMethodCallAstNode) {
                    $resolver->send($statementMethodCallAstNode);
                }
                break;
        }
    }

    private function resolver(AstResolver $astResolver): Generator
    {
        $resolved = [];
        while (true) {
            $value = yield;

            if ($value === self::RESOLVE_GENERATOR) {
                return $resolved;
            }

            if ($value instanceof MethodContext) {
                $resolved[] = $astResolver->resolveMethodAst($value->fqcn(), $value->methodName());
            } else if ($value instanceof MethodAst) {
                $resolved[] = $value;
            }
        }
    }

    /**
     * @return Node[]
     */
    private function statementNodes(Node $node): array
    {
        return $node->children['stmts']->children ?? [];
    }

    /**
     * @param AstResolver $astResolver
     * @param Node $node
     * @param Context[] $contexts
     * @return MethodAst[]
     */
    private function parseNewStatement(AstResolver $astResolver, Node $node, $contexts = []): array
    {
        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $methodAsts = [];

        // arguments may have method call
        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                foreach ($this->parseNewStatement($astResolver, $argumentNode, $contexts) as $methodAst) {
                    $methodAsts[] = $methodAst;
                }
            }
            // method call
            if ($argumentNode->kind === Kind::AST_METHOD_CALL) {
                $methodAsts[] = $this->methodCallAstNodes($astResolver, $argumentNode);
            }
            if ($argumentNode->kind === Kind::AST_STATIC_CALL) {
                $methodAsts[] = $this->methodStaticCallAstNodes($astResolver, $argumentNode);
            }
        }

        $contexts[] = $astResolver->resolveContext($this->methodContext->context(), $newClassName);

        foreach ($contexts as $context) {
            if (is_null($context)) {
                continue;
            }
            // new statement calls constructor
            try {
                $methodAst = $astResolver->resolveMethodAst($context->fqcn(), '__construct');
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
     * @param AstResolver $astResolver
     * @param Node $node
     * @param array $nodes
     * @return MethodAst[]
     */
    private function methodCallAstNodes(AstResolver $astResolver, Node $node, array $nodes = []): array
    {
        if ($node->kind !== Kind::AST_METHOD_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['expr'];

        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($astResolver, $argumentNode, $nodes);
        }

        // TODO extract node is method call or static call and call parser

        $methodName = $node->children['method'];

        try {
            $methodAst = $this->resolveCallMethodAst($astResolver, $leftStatementNode, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    /**
     * @param AstResolver $astResolver
     * @param Node $node
     * @param MethodAst $nodes
     * @return MethodAst[]
     */
    private function methodStaticCallAstNodes(AstResolver $astResolver, Node $node, array $nodes = [])
    {
        if ($node->kind !== Kind::AST_STATIC_CALL) {
            return $nodes;
        }
        $leftStatementNode = $node->children['class'];

        $methodOwner = $leftStatementNode->children['name'] ?? '';
        $argumentNodes = $node->children['args']->children ?? [];
        foreach ($argumentNodes as $argumentNode) {
            $nodes = $this->methodCallAstNodes($astResolver, $argumentNode, $nodes);
        }

        $methodName = $node->children['method'];
        try {
            $methodAst = $this->resolveStaticCallMethodAst($astResolver, $methodOwner, $methodName);
            if ($methodAst) {
                $nodes[] = $methodAst;
            }
        } finally {
            return $nodes;
        }
    }

    private function resolveCallMethodAst(AstResolver $astResolver, Node $leftStatementNode, string $methodName): ?MethodAst
    {
        // TODO resolve variable from variable map
        if ($leftStatementNode->kind === Kind::AST_PROP) {
            $context = $this->resolvePropertyContext($leftStatementNode);
        } else {
            // may should check kind
            $variableName = $leftStatementNode->children['name'] ?? '';
            if ($variableName === 'this') {
                // should be parsed at ClassAst parseMethod actually?
                // same name method priority is self, trait, parent method
                // self
                if (method_exists($this->methodContext->fqcn(), $methodName)) {
                    return $astResolver->resolveMethodAst($this->methodContext->fqcn(), $methodName);
                }

                // trait method

                // trait

                // TODO parent

            }
            $context = $this->variableMap[$variableName] ?? $astResolver->resolveContext($this->methodContext->context(), $variableName) ?? null;
        }

        if ($context) {
            return $astResolver->resolveMethodAst($context->fqcn(), $methodName);
        }
        return null;
    }

    private function resolveStaticCallMethodAst(AstResolver $astResolver, string $variableName, string $methodName): ?MethodAst
    {
        if ($variableName === 'self') {
            // too long and nullable
            return $astResolver->resolveMethodAst($this->methodContext->fqcn(), $methodName);
        }

        // resolve by FQCN or imported list
        $context = $astResolver->resolveContext($this->methodContext->context(), $variableName);
        if ($context) {
            return $astResolver->resolveMethodAst($context->fqcn(), $methodName);
        }

        // TODO same namespace class

        return null;
    }

    private function resolvePropertyContext(Node $propertyNode): ?Context
    {
        $variable = $propertyNode->children['expr']->children['name'] ?? '';
        $property = $propertyNode->children['prop'];

        if ($variable === 'this') {
            return $this->propertyMap->get($property);
        }

        // TODO public property method
        // how?
        return null;
    }

    public function fqcn(): string
    {
        return $this->methodContext->fqcn();
    }
}
