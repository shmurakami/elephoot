<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Entity\Node\MethodNode;
use shmurakami\Spice\Ast\Parser\TypeParser;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\ClassTreeNode;
use shmurakami\Spice\Stub\Kind;

class ClassAst
{
    use TypeParser;

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
     * @var Imports
     */
    private $imports;

    /**
     * ClassAst constructor.
     */
    public function __construct(string $namespace, string $className, Imports $imports, Node $classRootNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->classRootNode = $classRootNode;
        $this->imports = $imports;

        $this->parseProperties($namespace, $className);
    }

    private function parseProperties(string $namespace, string $className): void
    {
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_PROP_GROUP) {
                $this->properties[] = new ClassProperty($namespace, $className, $node);
            }
        }
    }

    /**
     * @param string $methodName
     * @return MethodAst
     * @throws MethodNotFoundException
     */
    public function parseMethod(string $methodName): MethodAst
    {
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_METHOD) {
                $nodeMethodName = $node->children['name'];
                if ($nodeMethodName === $methodName) {
                    $argumentNodes = $this->rootNode->children['params']->children ?? [];
                    $context = new Context($this->namespace, $this->className);
                    $methodContext = new MethodContext($context, $this->properties, $nodeMethodName, $this->imports, $argumentNodes);
                    return new MethodAst($methodContext, $node);
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
        $resolver = function(array $fqcnList) use ($dependentClassAstResolver) {
            foreach ($fqcnList as $classFqcn) {
                $dependentClassAstResolver->send($classFqcn);
            }
        };

        // using trait
        $resolver($this->extractUsingTrait());

        // property, only need to parse doc comment
        foreach ($this->properties as $classProperty) {
            $resolver($classProperty->classFqcnListFromDocComment());
        }

        // parse method
        $resolver($this->extractClassFqcnFromMethodNodes());

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

        while (true) {
            $classFqcn = yield;
            // give null to finish
            if ($classFqcn === null) {
                return $dependencies;
            }

            $originalFqcn = $classFqcn;
            $classFqcn = $this->parseType($this->namespace, $classFqcn);
            if ($classFqcn === null) {
                $resolved[$classFqcn] = true;
                continue;
            }

            if (isset($resolved[$classFqcn])) {
                continue;
            }

            // fqcn is same with current target class, skip to avoid infinite loop
            if ($this->fqcn() === $classFqcn) {
                $resolved[$classFqcn] = true;
                continue;
            }

            $classAst = $classAstResolver->resolve($classFqcn);
            if ($classAst) {
                $dependencies[$classFqcn] = $classAst;
            } else {
                // search global scope class from namespaced class
                $isFqcnCompleted = $classFqcn !== $originalFqcn;
                if ($isFqcnCompleted) {
                    $classAst = $classAstResolver->resolve($originalFqcn);
                    if ($classAst) {
                        $dependencies[$originalFqcn] = $classAst;
                    }
                }
            }

            $resolved[$classFqcn] = true;
        }
    }

    /**
     * @return string[]
     */
    private function extractClassFqcnFromMethodNodes(): array
    {
        /** @var MethodNode[] $methodNodes */
        $methodNodes = [];

        // extract method nodes
        foreach ($this->statementNodes() as $node) {
            if ($node->kind === Kind::AST_METHOD) {
                $methodNodes[] = new MethodNode($this->namespace, $node);
            }
        }

        // retrieve class fqcn from method node
        $classFqcn = [];
        foreach ($methodNodes as $methodNode) {
            $fqcnList = $methodNode->parse();
            foreach ($fqcnList as $fqcn) {
                $classFqcn[] = $fqcn;
            }
        }
        return array_unique($classFqcn);
    }

    /**
     * digging class statement
     * TODO refactoring
     *
     * @return string[]
     */
    private function parseNewStatement(Node $rootNode, $fqcnList = []): array
    {
        $kind = $rootNode->kind;

        // has child statements
        if (in_array($kind, [Kind::AST_CLASS, Kind::AST_METHOD, Kind::AST_CLOSURE], true)) {
            $statementNodes = $this->statementNodes($rootNode);
            foreach ($statementNodes as $statementNode) {
                $fqcnList = $this->parseNewStatement($statementNode, $fqcnList);
            }
            return $fqcnList;
        }

        // see right statement
        if (in_array($kind, [Kind::AST_ASSIGN, Kind::AST_RETURN])) {
            $rightStatementNode = $rootNode->children['expr'];
            if (!($rightStatementNode instanceof Node)) {
                return $fqcnList;
            }
            if ($rightStatementNode->kind === Kind::AST_NEW) {
                $list = $this->parseNewStatementFqcnList($rightStatementNode, []);
                foreach ($list as $f) {
                    $fqcnList[] = $f;
                }
                return $fqcnList;
            }

            if ($kind === Kind::AST_RETURN) {
                return $this->parseNewStatement($rightStatementNode, $fqcnList);
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
                $fqcnList[] = $newClassName;
            }

            $argumentNodes = $rootNode->children['args']->children ?? [];
            foreach ($argumentNodes as $argumentNode) {
                if ($argumentNode instanceof Node) {
                    $list = $this->parseNewStatement($argumentNode, $fqcnList);
                    foreach ($list as $fqcn) {
                        $fqcnList[] = $fqcn;
                    }
                    return $fqcnList;
                }
            }
        }

        // not assigning new, e.g. in argument
        if ($kind === Kind::AST_NEW) {
            $list = $this->parseNewStatementFqcnList($rootNode, []);
            foreach ($list as $f) {
                $fqcnList[] = $f;
            }
            return $fqcnList;
        }

        // nothing to do
        if (in_array($kind, [Kind::AST_PROP_GROUP])) {
            return $fqcnList;
        }

        return array_unique($fqcnList);
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
     * @return string[]
     */
    private function extractUsingTrait(): array
    {
        $traits = [];
        foreach ($this->statementNodes() as $statementNode) {
            if ($statementNode->kind === Kind::AST_USE_TRAIT) {
                $traitNames = array_map(function (Node $traitNode) {
                    return $traitNode->children['name'];
                }, $statementNode->children['traits']->children ?? []);
                foreach ($traitNames as $traitName) {
                    $traits[] = $traitName;
                }
            }
        }
        return $traits;
    }

    public function treeNode(): ClassTreeNode
    {
        return new ClassTreeNode($this->fqcn());
    }

    /**
     * @return string
     */
    public function fqcn(): string
    {
        if ($this->namespace) {
            return $this->namespace . '\\' . $this->className;
        }
        return $this->className;
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
