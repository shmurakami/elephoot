<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Parser;

use ast\Node;
use shmurakami\Elephoot\Ast\Entity\ClassAst;
use shmurakami\Elephoot\Ast\Resolver\ClassAstResolver;
use shmurakami\Elephoot\Stub\Kind;

class AstParser
{

    public function __construct(private ClassAstResolver $classAstResolver)
    {
        // probably this class should not need to depend on ClassAstResolver
    }

    public function parseNamespace(Node $node): string
    {
        $namespaceNode = $this->childNodesByKind($node, Kind::AST_NAMESPACE)[0] ?? null;
        if (!$namespaceNode) {
            return '';
        }

        $namespace = $namespaceNode->children['name'];
        return $namespace;
    }

    /**
     * @return ClassAst[]
     */
    public function importedClassAsts(Node $node): array
    {
        $imported = [];

        $useNodes = $this->childNodesByKind($node, Kind::AST_USE);
        foreach ($useNodes as $useNode) {
            // support alias?
            $className = $useNode->children[0]->children['name'];
            $classAst = $this->classAstResolver->resolve($className);
            if ($classAst) {
                $imported[$className] = $classAst;
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

        $classNode = $this->childNodesByKind($node, Kind::AST_CLASS)[0] ?? null;
        if (!$classNode) {
            return [];
        }

        $extends = [];
        $extendClassName = $classNode->children['extends']->children['name'] ?? '';
        if ($extendClassName) {
            $classAst = $this->classAstResolver->resolve($extendClassName);
            if ($classAst) {
                $extends[$extendClassName] = $classAst;
            }
        }

        foreach ($classNode->children['implements']->children ?? [] as $implementNode) {
            $interfaceName = $implementNode->children['name'];
            $classAst = $this->classAstResolver->resolve($interfaceName);
            if ($classAst) {
                $extends[$interfaceName] = $classAst;
            }
        }
        return $extends;
    }

    /**
     * @param Node $node
     * @return Node[]
     */
    public function propertyGroupNodes(Node $node)
    {
        return $this->extractNodes($node, Kind::AST_PROP_GROUP);
    }

    /**
     * @return Node[]
     */
    public function extractUsingTrait(Node $node): array
    {
        $traitNodes = [];
        foreach ($this->extractNodes($node, Kind::AST_USE_TRAIT) as $parentTraitNode) {
            foreach ($parentTraitNode->children['traits']->children ?? [] as $traitNode) {
                $traitNodes[] = $traitNode;
            }
        }
        return $traitNodes;
    }

    /**
     * @param Node $node
     * @return Node[]
     */
    public function extractMethodNodes(Node $node)
    {
        return $this->extractNodes($node, Kind::AST_METHOD);
    }

    /**
     * @return Node[]
     */
    private function childNodesByKind(Node $node, int $kind): array
    {
        return array_values(array_filter($node->children, function (Node $node) use ($kind) {
            return $node->kind === $kind;
        }));
    }

    /**
     * @return Node[]
     */
    private function statementNodes(Node $node)
    {
        return $node->children['stmts']->children ?? [];
    }

    /**
     * @return Node[]
     */
    private function extractNodes(Node $node, int $kind)
    {
        return array_filter($this->statementNodes($node), function (Node $node) use ($kind) {
            return $node->kind === $kind;
        });
    }
}
