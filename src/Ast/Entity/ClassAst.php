<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Spice\Ast\Parser\DocCommentParser;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Output\ClassTreeNode;
use shmurakami\Spice\Stub\Kind;

class ClassAst
{
    use DocCommentParser;

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


        // method argument
        // constructor, child method

        // return type

        // new statement

        // method call

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
        $methodNodes = [];

        // extract method nodes
        $classStatementNodes = $this->classRootNode->children['stmts']->children ?? [];
        foreach ($classStatementNodes as $node) {
            if ($node->kind === Kind::AST_METHOD)  {
                $methodNodes[] = $node;
            }
        }

        // retrieve class fqcn from method node
        $classFqcn = [];
        foreach ($methodNodes as $methodNode) {
            // doc comment
            $doComment = $methodNode->children['docComment'] ?? '';
            $methodDocCommentDependencies = $this->parseDocComment($this->namespace, $doComment, '@param');
            foreach ($methodDocCommentDependencies as $dependency) {
                $classFqcn[] = $dependency;
            }

            // type hinting
            // return type
        }
        return array_unique($classFqcn);
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