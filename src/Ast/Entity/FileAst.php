<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Stub\Kind;

class FileAst
{
    /**
     * @var Node
     */
    private $rootNode;
    /**
     * @var string
     */
    private $classFqcn;
    /**
     * @var string
     */
    private $namespace;

    public function __construct(Node $rootNode, string $classFqcn)
    {
        $this->rootNode = $rootNode;
        // file may not have class so weird to require
        $this->classFqcn = $classFqcn;
    }

    /**
     * @param Node|null $rootNode
     * @return ClassAst
     * @throws ClassNotFoundException
     */
    public function parse(?Node $rootNode = null): ClassAst
    {
        $rootNode = $rootNode ?? $this->rootNode;

        foreach ($rootNode->children as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                // may stmt exist in this time?
                return $this->parse($node);
            }

            if ($node->kind === Kind::AST_CLASS) {
                $nodeClassName =  $node->children['name'];
                $nodeClassFqcn = $this->getNamespace() . '\\' . $nodeClassName;
                if ($nodeClassFqcn === $this->classFqcn) {
                    return new ClassAst($this->getNamespace(), $nodeClassName, $node);
                }
            }
        }
        throw new ClassNotFoundException();
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        if ($this->namespace === null) {
            $namespace = '';
            /** @var Node $node */
            foreach ($this->rootNode->children as $node) {
                if ($node->kind === Kind::AST_NAMESPACE) {
                    $namespace = $node->children['name'];
                    break;
                }
            }
            $this->namespace = $namespace;
        }
        return $this->namespace;
    }

    /**
     * @return ClassAst[]
     */
    public function dependentClassAstList(): array
    {
        return array_merge($this->importedClasses(), $this->relatedClasses());
    }

    /**
     * @return ClassAst[]
     */
    private function importedClasses(): array
    {
        $classAstResolver = ClassAstResolver::getInstance();

        $imported = [];
        foreach ($this->rootNode->children as $node) {
            if ($node->kind === Kind::AST_USE) {
                // support alias?
                $className = $node->children[0]->children['name'];
                $classAst = $classAstResolver->resolve($className);
                if ($classAst) {
                    $imported[$className] = $classAst;
                }
            }
        }

        return $imported;
    }

    /**
     * @return ClassAst[]
     */
    private function relatedClasses(): array
    {
        /*
         * seek use statement
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
        return [];
        $importedClasses = [];
        foreach ($this->classRootNode->children as $childNode) {
            // TODO support GROUP_USE use, TRAIT
            if ($childNode->kind === Kind::AST_USE) {
                $useStatement = $childNode;
            }
        }

        $dependentClasses = [];
        return [];
    }
}
