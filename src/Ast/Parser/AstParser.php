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
        // probably this class should not need to depend on ClassAstResolver
        $this->classAstResolver = $classAstResolver;
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
     * @return Node[]
     */
    private function childNodesByKind(Node $node, int $kind): array
    {
        return array_values(array_filter($node->children, function (Node $node) use ($kind) {
            return $node->kind === $kind;
        }));
    }
}
