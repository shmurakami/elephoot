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
        return array_merge(
            $this->importedClasses(),
            $this->extendClasses(),
            $this->relatedClasses());
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
    private function extendClasses(): array
    {
        $classAstResolver = ClassAstResolver::getInstance();
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
            $implementClassNames = array_map(function (Node $implementNode) {
                return $implementNode->children['name'];
            }, $classNode->children['implements']->children ?? []);

            if ($extendClassName) {
                $classAst = $classAstResolver->resolve($extendClassName);
                if ($classAst) {
                    $extends[$extendClassName] = $classAst;
                }
            }
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
    private function relatedClasses(): array
    {
        return $this->parse()->relatedClasses();
    }
}
