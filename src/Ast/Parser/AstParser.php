<?php

namespace shmurakami\Spice\Ast\Parser;

use ast\Node;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Stub\Kind;

class AstParser
{

    /**
     * @var ClassAstResolver
     */
    private $classAstResolver;

    public function __construct(ClassAstResolver $classAstResolver)
    {
        $this->classAstResolver = $classAstResolver;
    }

    public function parseNamespace(Node $node): string
    {
        /** @var Node $childNode */
        foreach ($node->children as $childNode) {
            if ($childNode->kind === Kind::AST_NAMESPACE) {
                $namespace = $childNode->children['name'];
                return $namespace;
            }
        }
        return '';
    }

    /**
     * @return ClassAst[]
     */
    public function importedClassAsts(Node $node): array
    {
        $imported = [];
        foreach ($node->children as $childNode) {
            if ($childNode->kind === Kind::AST_USE) {
                // support alias?
                $className = $childNode->children[0]->children['name'];
                $classAst = $this->classAstResolver->resolve($className);
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
    public function extendClassAsts(Node $node): array
    {
        // extend and implements
        $extends = [];

        $classNode = null;
        foreach ($node->children as $childNode) {
            if ($childNode->kind === Kind::AST_CLASS) {
                $classNode = $childNode;
                break;
            }
        }

        if ($classNode) {
            $extendClassName = $classNode->children['extends']->children['name'] ?? '';
            if ($extendClassName) {
                $classAst = $this->classAstResolver->resolve($extendClassName);
                if ($classAst) {
                    $extends[$extendClassName] = $classAst;
                }
            }

            $implementClassNames = array_map(function (Node $implementNode) {
                return $implementNode->children['name'];
            }, $classNode->children['implements']->children ?? []);
            foreach ($implementClassNames as $className) {
                $classAst = $this->classAstResolver->resolve($className);
                if ($classAst) {
                    $extends[$className] = $classAst;
                }
            }
        }
        return $extends;
    }
}
