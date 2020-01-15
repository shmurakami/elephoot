<?php

namespace shmurakami\Spice\Ast\Entity\Node;

use ast\Node;
use shmurakami\Spice\Ast\Parser\DocCommentParser;
use shmurakami\Spice\Ast\Parser\TypeParser;
use shmurakami\Spice\Stub\Kind;

class MethodNode
{
    use DocCommentParser;
    use TypeParser;

    /**
     * @var string
     */
    private $namespace;
    /**
     * @var Node
     */
    private $node;

    public function __construct(string $namespace, Node $node)
    {
        $this->namespace = $namespace;
        $this->node = $node;
    }

    /**
     * @return string[]
     */
    public function parse()
    {
        $dependencyClassFqcnList = [];

        // doc comment
        $doComment = $this->node->children['docComment'] ?? '';
        $typeNames = $this->parseDocComment($doComment, '@param');
        foreach ($typeNames as $typeName) {
            $classFqcn = $this->parseType($this->namespace, $typeName);
            if ($classFqcn) {
                $dependencyClassFqcnList[] = $classFqcn;
            }
        }

        // type hinting
        // consider nullable type?
        $argumentNodes = $this->node->children['params']->children ?? [];
        $typeNames = array_map(function (Node $node) {
            return $node->children['type']->children['name'] ?? '';
        }, $argumentNodes);
        foreach ($typeNames as $typeName) {
            if ($typeName) {
                $classFqcn = $this->parseType($this->namespace, $typeName);
                if ($classFqcn) {
                    $dependencyClassFqcnList[] = $classFqcn;
                }
            }
        }

        // return statement
        // return type
        $returnTypeNode = $this->node->children['returnType'] ?? null;
        if ($returnTypeNode
            && ($returnTypeNode->kind === Kind::AST_TYPE || $returnTypeNode->kind === Kind::AST_NULLABLE_TYPE)
        ) {
            $type = $returnTypeNode->children['type']->children['name'] ?? '';
            if ($type) {
                $classFqcn = $this->parseType($this->namespace, $type);
                if ($classFqcn) {
                    $dependencyClassFqcnList[] = $classFqcn;
                }
            }
        }

        // return type in doc comment
        // redundant to parse doc comment again?
        $doComment = $this->node->children['docComment'] ?? '';
        $typeNames = $this->parseDocComment($doComment, '@return');
        foreach ($typeNames as $typeName) {
            $classFqcn = $this->parseType($this->namespace, $typeName);
            if ($classFqcn) {
                $dependencyClassFqcnList[] = $classFqcn;
            }
        }

        return array_unique($dependencyClassFqcnList);
    }

}
