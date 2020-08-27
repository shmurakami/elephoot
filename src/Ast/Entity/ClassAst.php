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
    use ContextParser;

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
     * ClassAst constructor.
     */
    public function __construct(Context $context, Node $classRootNode)
    {
        $this->context = $context;
        $this->classRootNode = $classRootNode;
        $this->namespace = $context->extractNamespace();

        $this->parseProperties($context);
    }

    private function parseProperties(Context $context): void
    {
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_PROP_GROUP) {
                $this->properties[] = new ClassProperty($context, $node);
            }
        }
    }

    /**
     * @param string $method
     * @return MethodAst
     * @throws MethodNotFoundException
     */
    public function parseMethod(string $method): MethodAst
    {
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_METHOD) {
                $rootMethod = $node->children['name'];
                if ($rootMethod === $method) {
                    return new MethodAst($this->namespace, $this->className, $this->properties, $node);
                }
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
            } else {
                // this block for bug. global scope class should be given as global scope context
                // FIXME bug source is SomeClass.toContext

                // search global scope class from namespaced class
                $className = $context->extractClassName();
                if (!isset($dependencies[$className])) {
                    $classAst = $classAstResolver->resolve($className);
                    if ($classAst) {
                        $dependencies[$className] = $classAst;
                    }
                }
            }

            $resolved[$targetFqcn] = true;
        }
    }

    /**
     * FIXME this method may has bug
     * @return Context[]
     */
    private function extractContextListFromMethodNodes(): array
    {
        $dependencyContexts = [];
        // extract method nodes
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_METHOD) {
                $methodNode = new MethodNode($this->context, $node);
                foreach ($methodNode->parseMethodAttributeToContexts() as $context) {
                    $fqcn = $context->fqcn();
                    if (isset($dependencyContexts[$fqcn])) {
                        continue;
                    }
                    $dependencyContexts[$fqcn] = $context;
                }
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
            if ($rightStatementNode->kind === Kind::AST_NEW) {
                $list = $this->parseNewStatementFqcnList($rightStatementNode, []);
                foreach ($list as $f) {
                    $context = $this->toContext($this->namespace, $f);
                    if ($context) {
                        $contextList[] = $context;
                    }
                }
                return $contextList;
            }

            if ($kind === Kind::AST_RETURN) {
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
                $context = $this->toContext($this->namespace, $newClassName);
                if ($context) {
                    $contextList[] = $context;
                }
            }

            $argumentNodes = $rootNode->children['args']->children ?? [];
            foreach ($argumentNodes as $argumentNode) {
                if ($argumentNode instanceof Node) {
                    $list = $this->parseNewStatement($argumentNode, $contextList);
                    foreach ($list as $context) {
                        $contextList[] = $context;
                    }
                    return $contextList;
                }
            }
        }

        // not assigning new, e.g. in argument
        if ($kind === Kind::AST_NEW) {
            $list = $this->parseNewStatementFqcnList($rootNode, []);
            foreach ($list as $f) {
                $context = $this->toContext($this->namespace, $f);
                if ($context) {
                    $contextList[] = $context;
                }
            }
            return $contextList;
        }

        // nothing to do
        if (in_array($kind, [Kind::AST_PROP_GROUP])) {
            return $contextList;
        }

        return $contextList;
    }

    /**
     * new statement(constructor) argument can be another new statement
     * and it's nestable
     */
    private function parseNewStatementFqcnList(Node $node, array $list = []): array
    {
        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $list[] = $newClassName;

        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                array_map(function (string $fqcn) use (&$list) {
                    $list[] = $fqcn;
                }, $this->parseNewStatementFqcnList($argumentNode, $list));
            }
        }
        return $list;
    }

    /**
     * @return Context[]
     */
    private function extractUsingTrait(): array
    {
        $traitContexts = [];
        foreach ($this->statementNodes() as $statementNode) {
            if ($statementNode->kind === Kind::AST_USE_TRAIT) {
                $traitNames = array_map(function (Node $traitNode) {
                    return $traitNode->children['name'];
                }, $statementNode->children['traits']->children ?? []);
                foreach ($traitNames as $traitName) {
                    $traitContexts[] = new ClassContext($traitName);
                }
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

}
