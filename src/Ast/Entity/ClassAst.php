<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Entity\Node\MethodNode;
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
     * ClassAst constructor.
     */
    public function __construct(string $namespace, string $className, Node $classRootNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->classRootNode = $classRootNode;

        $this->parseProperties($namespace, $className, $classRootNode);
    }

    private function parseProperties(string $namespace, string $className, Node $classRootNode): void
    {
        $classStatementNodes = $classRootNode->children['stmts']->children ?? [];

        foreach ($classStatementNodes as $node) {
            if ($node->kind === Kind::AST_PROP_GROUP) {
                $this->properties[] = new ClassProperty($namespace, $className, $node);
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
        /** @var Node $statementNode */
        $statementNode = $this->classRootNode->children['stmts'];
        assert($statementNode->kind === Kind::AST_STMT_LIST, 'unexpected node kind');

        foreach ($statementNode->children as $node) {
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
     * TODO this method is required really?
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return ClassAst[]
     */
    public function relatedClasses(): array
    {
        $dependentClassAstResolver = $this->dependentClassAstResolver();

        // property, only need to parse doc comment
        foreach ($this->properties as $classProperty) {
            $classFqcnList = $classProperty->classFqcnListFromDocComment();
            if ($classFqcnList) {
                foreach ($classFqcnList as $classFqcn) {
                    $dependentClassAstResolver->send($classFqcn);
                }
            }
        }

        // annoying to call some methods and loop for every of them...

        $methodDependentClassFqcnList = $this->extractClassFqcnFromMethodNodes();
        foreach ($methodDependentClassFqcnList as $classFqcn) {
            $dependentClassAstResolver->send($classFqcn);
        }

        // parse statements
        $newStatementFqcnList = $this->parseNode($this->classRootNode);
        foreach ($newStatementFqcnList as $classFqcn) {
            $dependentClassAstResolver->send($classFqcn);
        }

        // inherit class

        // send null to call generator return
        $dependentClassAstResolver->next();
        return $dependentClassAstResolver->getReturn();
    }

    /**
     * @return Generator|array
     */
    private function dependentClassAstResolver(): Generator
    {
        // in case wrong class name is passed some times
        $resolved = [];
        $dependencies = [];
        $classAstResolver = ClassAstResolver::getInstance();

        while (true) {
            $classFqcn = yield;
            if ($classFqcn === null) {
                return $dependencies;
            }

            if (isset($resolved[$classFqcn])) {
                continue;
            }

            // fqcn is same with current target class, skip to avoid infinite loop
            if ($this->fqcn() === $classFqcn) {
                $resolved[$classFqcn] = true;
                continue;
            }

            // TODO sometime fqcn is null or broken. check it
            $classAst = $classAstResolver->resolve($classFqcn);
            if ($classAst) {
                $dependencies[$classFqcn] = $classAst;
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
        $classStatementNodes = $this->classRootNode->children['stmts']->children ?? [];
        foreach ($classStatementNodes as $node) {
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
    private function parseNode(Node $rootNode, $fqcnList = []): array
    {
        $kind = $rootNode->kind;

        // has child statements
        if (in_array($kind, [Kind::AST_CLASS, Kind::AST_METHOD, Kind::AST_CLOSURE], true)) {
            $statementNodes = $rootNode->children['stmts']->children ?? [];
            foreach ($statementNodes as $statementNode) {
                $fqcnList = $this->parseNode($statementNode, $fqcnList);
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
                return $this->parseNode($rightStatementNode, $fqcnList);
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
                    $list = $this->parseNode($argumentNode, $fqcnList);
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

        return $fqcnList;
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

    private function extractStaticMethodCallFqcnList(): array
    {

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
        return $this->namespace . '\\' . $this->className;
    }

}
