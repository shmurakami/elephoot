<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
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

        $this->parse($namespace, $className, $classRootNode);
    }

    private function parse(string $namespace, string $className, Node $classRootNode): void
    {
        $classStatements = $classRootNode->children['stmts'] ?? (object)['children' => []];

        // TODO AST should has it? consider to make Ast Parser
        foreach ($classStatements->children as $node) {
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
        /*
         * check property
         *  constructor argument
         *  type hinting
         *  anyway required statement parser
         * see method call
         * see static method call
         *
         * to see method calls
         * parse class statement to get property, constructor, methods, parent class
         * dig each methods
         *   to see method call. no need property class call => it has to be detected by property
         *   check static method call
         *
         * ... and classes which dependent this target
         * once need to dig all files?
         */
        $dependencies = [];

        $classAstResolver = ClassAstResolver::getInstance();

        // property, only need to parse doc comment
        foreach ($this->properties as $classProperty) {
            $classFqcnList = $classProperty->classFqcnListFromDocComment();
            if ($classFqcnList) {
                foreach ($classFqcnList as $classFqcn) {
                    $classAst = $classAstResolver->resolve($classFqcn);
                    if ($classAst) {
                        $dependencies[$classFqcn] = $classAst;
                    }
                }
            }
        }

        // method argument

        // return type

        // new statement

        // method call

        return [];
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
