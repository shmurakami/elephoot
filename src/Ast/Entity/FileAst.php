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
        $this->classFqcn = trim($classFqcn, '\\');
    }

    /**
     * @param Node|null $rootNode
     * @return ClassAst
     * @throws ClassNotFoundException
     */
    public function parse(?Node $rootNode = null): ClassAst
    {
        $rootNode = $rootNode ?? $this->rootNode;

        $namespace = $this->getNamespace();
        foreach ($rootNode->children as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                // may stmt exist in this time?
                return $this->parse($node);
            }

            if ($node->kind === Kind::AST_CLASS) {
                $nodeClassName =  $node->children['name'];
                $nodeClassFqcn = $nodeClassName;
                if ($namespace) {
                    $nodeClassFqcn = $namespace . '\\' . $nodeClassName;
                }
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
    public function dependentClassAstList(ClassAstResolver $classAstResolver): array
    {
        return array_merge(
            $this->importedClasses($classAstResolver),
            $this->extendClasses($classAstResolver),
            $this->relatedClasses($classAstResolver));
    }

    /**
     * @return ClassAst[]
     */
    private function importedClasses(ClassAstResolver $classAstResolver): array
    {
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
    private function extendClasses(ClassAstResolver $classAstResolver): array
    {
        // extend and implements
        $extends = [];

        $classNode = null;
        foreach ($this->rootNode->children as $node) {
            if ($node->kind === Kind::AST_CLASS) {
                $classNode = $node;
                break;
            }
        }

        if ($classNode) {
            $extendClassName = $classNode->children['extends']->children['name'] ?? '';
            if ($extendClassName) {
                $classAst = $classAstResolver->resolve($extendClassName);
                if ($classAst) {
                    $extends[$extendClassName] = $classAst;
                }
            }

            $implementClassNames = array_map(function (Node $implementNode) {
                return $implementNode->children['name'];
            }, $classNode->children['implements']->children ?? []);
            foreach ($implementClassNames as $className) {
                $classAst = $classAstResolver->resolve($className);
                if ($classAst) {
                    $extends[$className] = $classAst;
                }
            }
        }
        return $extends;
    }

    /**
     * if this works perfectly, import statement is not necessary. it's just redundant
     *
     * @return ClassAst[]
     * @throws ClassNotFoundException
     */
    private function relatedClasses(ClassAstResolver $classAstResolver): array
    {
        return $this->parse()->relatedClasses($classAstResolver);
    }
}
