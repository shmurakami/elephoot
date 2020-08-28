<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Entity\Node\MethodNode;
use shmurakami\Spice\Ast\Parser\ContextParser;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\ClassTreeNode;
use shmurakami\Spice\Stub\Kind;

class ClassAst
{
    /**
     * @var ClassProperty[]
     */
    private $properties = [];
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var Node
     */
    private $classRootNode;
    /**
     * @var string
     */
    private $className;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ContextParser
     */
    private $contextParser;

    /**
     * ClassAst constructor.
     */
    public function __construct(ContextParser $contextParser, Context $context, Node $classRootNode)
    {
        $this->contextParser = $contextParser;
        $this->context = $context;
        $this->classRootNode = $classRootNode;
        $this->namespace = $context->extractNamespace();

        $this->parseProperties($context);
    }

    private function parseProperties(Context $context): void
    {
        foreach ($this->extractNodes(Kind::AST_PROP_GROUP) as $propertyGroupNode) {
            $this->properties[] = new ClassProperty($this->contextParser, $context, $propertyGroupNode);
        }
    }

    /**
     * @param string $method
     * @return MethodAst
     * @throws MethodNotFoundException
     */
    public function parseMethod(string $method): MethodAst
    {
        foreach ($this->extractNodes(Kind::AST_METHOD) as $methodNode) {
            $rootMethod = $methodNode->children['name'];
            if ($rootMethod === $method) {
                return new MethodAst($this->namespace, $this->className, $this->properties, $methodNode);
            }
        }
        throw new MethodNotFoundException();
    }

    /**
     * @return ClassAst[]
     */
    public function relatedClasses(ClassAstResolver $classAstResolver): array
    {
        $dependentClassAstResolver = $this->dependentClassAstResolver($classAstResolver);
        $resolver = function (array $contexts) use ($dependentClassAstResolver) {
            foreach ($contexts as $context) {
                $dependentClassAstResolver->send($context);
            }
        };

        // using trait
        $resolver($this->extractUsingTrait());

        // property, only need to parse doc comment
        foreach ($this->properties as $classProperty) {
            $resolver($classProperty->classContextListFromDocComment());
        }

        // parse method
        $resolver($this->extractContextListFromMethodNodes());

        // parse new statements
        $resolver($this->parseNewStatement($this->classRootNode));

        // send null to call generator return
        $dependentClassAstResolver->next();
        return $dependentClassAstResolver->getReturn();
    }

    /**
     * @return Generator|array
     */
    private function dependentClassAstResolver(ClassAstResolver $classAstResolver): Generator
    {
        // to not search same wrong name class is given sometime
        $resolved = [];
        $dependencies = [];

        $currentContextFqcn = $this->context->fqcn();

        while (true) {
            /** @var Context $context */
            $context = yield;
            // give null to finish
            if ($context === null) {
                return $dependencies;
            }

            $targetFqcn = $context->fqcn();

            if (isset($resolved[$targetFqcn])) {
                continue;
            }

            // fqcn is same with current target class, skip to avoid infinite loop
            if ($currentContextFqcn === $targetFqcn) {
                $resolved[$targetFqcn] = true;
                continue;
            }

            $classAst = $classAstResolver->resolve($context->fqcn());
            if ($classAst) {
                $dependencies[$targetFqcn] = $classAst;
            }

            $resolved[$targetFqcn] = true;
        }
    }

    /**
     * @return Context[]
     */
    private function extractContextListFromMethodNodes(): array
    {
        $dependencyContexts = [];
        // extract method nodes
        foreach ($this->extractNodes(Kind::AST_METHOD) as $methodNode) {
            $methodNode = new MethodNode($this->contextParser, $this->context, $methodNode);
            foreach ($methodNode->parseMethodAttributeToContexts() as $context) {
                $fqcn = $context->fqcn();
                if (isset($dependencyContexts[$fqcn])) {
                    continue;
                }
                $dependencyContexts[$fqcn] = $context;
            }
        }

        return array_values($dependencyContexts);
    }

    /**
     * digging class statement
     * TODO refactoring
     *
     * @return Context[]
     */
    private function parseNewStatement(Node $rootNode, $contextList = []): array
    {
        $kind = $rootNode->kind;

        // has child statements
        if (in_array($kind, [Kind::AST_CLASS, Kind::AST_METHOD, Kind::AST_CLOSURE], true)) {
            $statementNodes = $this->statementNodes($rootNode);
            foreach ($statementNodes as $statementNode) {
                $contextList = $this->parseNewStatement($statementNode, $contextList);
            }
            return $contextList;
        }

        // see right statement
        if (in_array($kind, [Kind::AST_ASSIGN, Kind::AST_RETURN])) {
            $rightStatementNode = $rootNode->children['expr'];
            if (!($rightStatementNode instanceof Node)) {
                return $contextList;
            }
            if ($rightStatementNode->kind === Kind::AST_NEW || $kind === Kind::AST_RETURN) {
                return $this->parseNewStatement($rightStatementNode, $contextList);
            }
        }

        // method call
        if (in_array($kind, [Kind::AST_METHOD_CALL, Kind::AST_STATIC_CALL])) {
            // if call method without variable assigning? like (new hogehoge())->foobar()
            // need expr for call instance method? if so, call self recursively
            $exprNode = $rootNode->children['expr'] ?? null;
            // to make method tree
            $method = $rootNode->children['method'];
            // for static method call
            $staticMethodClassNode = $rootNode->children['class'] ?? null;
            if ($staticMethodClassNode) {
                $newClassName = $staticMethodClassNode->children['name'];
                $context = $this->contextParser->toContext($this->namespace, $newClassName);
                if ($context) {
                    $contextList[] = $context;
                }
            }

            $argumentNodes = $rootNode->children['args']->children ?? [];
            foreach ($argumentNodes as $argumentNode) {
                if ($argumentNode instanceof Node) {
                    $contextList = $this->parseNewStatement($argumentNode, $contextList);
                }
            }
            return $contextList;
        }

        // not assigning new, e.g. in argument
        if ($kind === Kind::AST_NEW) {
            return $this->parseNewStatementContextList($rootNode, $contextList);
        }

        // nothing to do
        if (in_array($kind, [Kind::AST_PROP_GROUP])) {
            return $contextList;
        }

        return $contextList;
    }

    /**
     * @return Context[]
     */
    private function parseNewStatementContextList(Node $node, array $contextList = [])
    {
        $fqcnList = $this->parseNewStatementFqcnList($node, []);

        foreach ($this->contextParser->toContextList($this->namespace, $fqcnList) as $context) {
            $contextList[] = $context;
        }
        return $contextList;
    }

    /**
     * new statement(constructor) argument can be another new statement
     * and it's nestable
     */
    private function parseNewStatementFqcnList(Node $node, array $list = []): array
    {
        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                foreach ($this->parseNewStatementFqcnList($argumentNode, $list) as $fqcn) {
                    $list[] = $fqcn;
                }
            }
        }

        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $list[] = $newClassName;
        return $list;
    }

    /**
     * @return Context[]
     */
    private function extractUsingTrait(): array
    {
        $traitContexts = [];
        foreach ($this->extractNodes(Kind::AST_USE_TRAIT) as $traitNodes) {
            foreach ($traitNodes->children['traits']->children ?? [] as $traitNode) {
                $traitName = $traitNode->children['name'];
                $traitContexts[] = new ClassContext($traitName);
            }
        }
        return $traitContexts;
    }

    public function treeNode(): ClassTreeNode
    {
        return new ClassTreeNode($this->context);
    }

    /**
     * @return string
     */
    public function fqcn(): string
    {
        // TODO weird
        return $this->context->fqcn();
    }

    /**
     * @return Node[]
     */
    private function statementNodes(Node $rootNode = null)
    {
        if (!$rootNode) {
            $rootNode = $this->classRootNode;
        }
        return $rootNode->children['stmts']->children ?? [];
    }

    /**
     * @return Node[]
     */
    private function extractNodes(int $kind, Node $rootNode = null)
    {
        return array_filter($this->statementNodes($rootNode), function (Node $node) use ($kind) {
            return $node->kind === $kind;
        });
    }
}
